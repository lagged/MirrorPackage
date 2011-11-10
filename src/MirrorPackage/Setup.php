<?php
namespace Lagged\PEAR\MirrorPackage;

/**
 * Wrap basic setup operations before we can parse the package.xml file.
 *
 * @category File
 * @package  MirrorPackage
 * @author   Till Klampaeckel <till@php.net>
 */
class Setup
{
    /**
     * @var string $baseDir Directory name in 'temp'.
     */
    protected $baseDir = 'pear-mirror';

    /**
     * @var string $file Package to download.
     */
    protected $file;

    protected $url;

    /**
     * __construct
     *
     * @param string $url   The URL of the PEAR package.
     * @param string $pirum Path of your local pirum checkout.
     *
     * @return $this
     */
    public function __construct($url, $pirum)
    {
        $this->url   = $url;
        $this->file  = basename($url);
        $this->pirum = $pirum;
    }

    public function checkPirum()
    {
        if (file_exists("{$this->pirum}/{$this->file}")) {
            throw new \LogicException("Package seems to already exist in pirum's directory. Maybe you forgot to update your channel?");
        }
        return $this;
    }

    public function createDirectories()
    {
        $tmpFile = $this->getTempFile();
        $tmpDir  = $this->getTempDir();

        if (!file_exists($tmpDir)) {
            if (!mkdir($tmpDir)) {
                throw new \RuntimeException("Could not create temporary directory: {$tmpDir}");
            }
        }
        return $this;
    }

    public function downloadPackage()
    {
        $tmpFile = $this->getTempFile();
        if (!file_exists($tmpFile)) {
            if (!file_put_contents($tmpFile, file_get_contents($this->url))) {
                throw new \RuntimeException("Could not download package: {$this->url}");
            }
        }
        return $this;
    }

    public function extractPackage()
    {
        $tar = "tar"; // FIXME: maybe 'detect' this which which or whereis?

        $tmpFile = $this->getTempFile();
        $tmpDir  = $this->getTempDir();

        \chdir($tmpDir);

        $cmd = "{$tar} zxf {$tmpFile}";
        exec($cmd, $output, $ret);
        if ($ret != 0) {
            throw new \RuntimeException("Could not untar: {$output}");
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getTempFile()
    {
        $tmpFile = sys_get_temp_dir() . "/{$this->baseDir}/" . $this->file;
        return $tmpFile;
    }

    /**
     * @return string
     */
    protected function getTempDir()
    {
        $tmpFile = $this->getTempFile();
        return dirname($tmpFile);
    }
}
