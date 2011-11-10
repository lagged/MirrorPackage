<?php
/**
 * MirrorPackage
 *
 * PHP Version 5.3
 *
 * @category Management
 * @package  MirrorPackage
 * @author   Till Klampaeckel <till@lagged.biz>
 * @license  http://www.opensource.org/licenses/bsd-license.php The New BSD License
 * @version  Release: @package_version@
 * @link     http://www.lagged.biz/
 */

namespace Lagged\PEAR;

/**
 * MirrorPackage
 *
 * @category Management
 * @package  MirrorPackage
 * @author   Till Klampaeckel <till@lagged.biz>
 * @license  http://www.opensource.org/licenses/bsd-license.php The New BSD License
 * @version  Release: @package_version@
 * @link     http://www.lagged.biz/
 */
class MirrorPackage
{
    /**
     * @var array $opts from getopt()
     */
    protected $opts;

    /**
     * @var string $package The URL of the package.
     * @see self::setUp()
     */
    protected $package;

    /**
     * @var string $pearcmd The location of the pearcmd on the system.
     */
    protected $pearcmd = '/usr/bin/pear';

    /**
     * @var string $pirum The location of pirum on this system.
     * @see self::setUp()
     */
    protected $pirum;

    /**
     * @var array $server From $_SERVER
     */
    protected $server;

    /**
     * __construct()
     *
     * @param array $opts
     * @param array $server
     * @param array $config
     *
     * @return $this
     */
    public function __construct(array $opts, array $server, array $config)
    {
        $this->opts   = $opts;
        $this->server = $server;
        $this->config = $config;
    }

    /**
     * Writes into {@link self::$package}, {@link self::$pearcmd} and {@link self::$pirum}.
     *
     * @return $this
     * @uses   self::$opts
     * @uses   self::$server
     */
    public function setUp()
    {
        $this->checkHelp();
        $this->checkPackage();
        $this->checkPear();
        $this->checkPirum();

        return $this;
    }

    /**
     * Start cloning the package!
     *
     * @return $this
     */
    public function clonePackage()
    {
        $packageFile = basename($this->package);

        $setup = new \Lagged\PEAR\MirrorPackage\Setup($this->package, $this->pirum);
        $setup->checkPirum()
            ->createDirectories()
            ->downloadPackage()
            ->extractPackage();

        $tmpDir = $setup->getTempDir();


        /**
         * @desc Once the setup is done, we replace the current channel in the package.xml.
         */
        $packageXML = new MirrorPackage\PackageXML("{$tmpDir}/package.xml");

        $pirum   = new MirrorPackage\PirumXML("{$this->pirum}/pirum.xml");
        $channel = $pirum->getChannelUrl();

        $packageXML->setChannel($channel);

        $deps = $packageXML->getDependencies();

        /**
         * @desc Overwrite extracted package.xml.
         */
        if (@file_put_contents("{$tmpDir}/package.xml", (string) $packageXML) === false) {
            throw new \RuntimeException("Could not write package.xml.");
        }

        /**
         * @desc Finally, we re-create the package - poor man's "pear package".
         */
        $release = new \Lagged\PEAR\MirrorPackage\Release($this->package, $tmpDir);
        $release->run();


        \chdir($tmpDir);
        rename($packageFile, "{$this->pirum}/{$packageFile}");

        return $this;
    }

    /**
     * Small autoloader!
     *
     * @param string $className The name of the class to load!
     *
     * @return boolean
     * @see    spl_autoload_register()
     */
    public static function autoload($className)
    {
        if (substr($className, 0, 25) !== 'Lagged\PEAR\MirrorPackage') {
            return false;
        }

        $file = substr($className, 12);
        $file = str_replace('\\', '/', $file) . '.php';
        return include $file;
    }

    /**
     * Display help/usage when -h is given.
     *
     * @return void
     */
    protected function checkHelp()
    {
        if (isset($this->opts['h'])) {
            echo "Usage: ./" . basename(__FILE__) . " -options /path/to/pirum" . PHP_EOL . PHP_EOL;
            echo "Available options:" . PHP_EOL;
            foreach ($this->config as $_opt => $_help) {
                echo " -" . substr($_opt, 0, 1) . ": " . $_help . PHP_EOL;
            }
            exit(2);
        }
    }

    protected function checkPackage()
    {
        if (!isset($this->opts['p'])) {
            echo "You need to supply the url of the package to mirror." . PHP_EOL;
            exit(1);
        }
        $this->package = $this->opts['p'];
    }

    /**
     * Find PEAR on the system. Maybe we refactor this to something
     * better and less annoying.
     *
     * @return void
     */
    protected function checkPear()
    {
        if (isset($this->opts['c'])) {
            $this->pearcmd = $this->opts['c'];
        }
        if (!file_exists($this->pearcmd)) {
            echo "PEAR command '{$this->pearcmd}' does not exit." . PHP_EOL;
            exit(3);
        }
        if (!is_executable($this->pearcmd)) {
            echo "{$opts['c']} is not executable." . PHP_EOL;
            exit(3);
        }
    }

    /**
     * Check the location of the pirum folder: must be readable, writable
     * and contain a pirum.xml.
     *
     * @return void
     * @uses   self::$server
     * @uses   self::$pirum
     */
    protected function checkPirum()
    {
        $path = end($this->server['argv']);
        if (substr($path, 0, 1) == '-') {
            echo "You need to supply the path to pirum." . PHP_EOL;
            exit(3);
        }

        $this->pirum = realpath($path);
        if ($this->pirum === false) {
            echo "Could not resolv path to Pirum: {$path}" . PHP_EOL;
            exit(3);
        }
        if (!is_readable($this->pirum) || !is_dir($this->pirum) || !is_writable($this->pirum)) {
            echo "Could not read/write to Pirum: {$this->pirum}" . PHP_EOL;
            exit(3);
        }
        if (!file_exists($this->pirum . '/pirum.xml')) {
            echo "Could not find pirum.xml in {$this->pirum}." . PHP_EOL;
            exit(3);
        }
    }

    protected function shellexecute($cmd)
    {
        exec($cmd, $output, $return);
    }
}
