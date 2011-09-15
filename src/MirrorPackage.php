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
     * @return void
     */
    public function clonePackage()
    {
        $packageFile = basename($this->package);
        $tmpFile     = sys_get_temp_dir() . '/pear-mirror/' . $packageFile;
        var_dump($this->package, $this->pearcmd, $tmpFile, $this->pirum); exit;

        $tmpDir = dirname($tmpFile);
        if (!@mkdir($tmpDir)) {
            echo "Could not create temporary directory: {$tmpDir}" . PHP_EOL;
            exit(3);
        }

        if (!file_put_contents($tmpFile, file_get_contents($this->package))) {
            echo "Could not download package..." . PHP_EOL;
            exit(3);
        }

        $packageDir  = substr($packageFile, 0, -7); // .tar.gz

        cwd($tmpDir);
        $cmd = "tar -zxvf $tmpFile"; // assuming 'tar' is in the path

        $cmd = "mv package.xml ./{$packageDir}";

        cwd("./{$packageDir}"); // go into extracted dir

        $cmd = "replace package.xml";

        $cmd = "pear package";

        $cmd = "cp $packageFile $pirum";
    }

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
            echo "Could not read/write to Pirum: $this->pirum" . PHP_EOL;
            exit(3);
        }
    }

    protected function shellexecute($cmd)
    {
        exec($cmd, $output, $return);
    }
}