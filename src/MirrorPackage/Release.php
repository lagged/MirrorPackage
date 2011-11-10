<?php
namespace Lagged\PEAR\MirrorPackage;

/**
 * Wrap basic file operations before to re-create the PEAR package.
 *
 * @category File
 * @package  MirrorPackage
 * @author   Till Klampaeckel <till@php.net>
 */
class Release
{
    protected $file;
    protected $tmpDir;

    /**
     * @param string $file   The location of the original download.
     * @param string $tmpDir The working directory.
     */
    public function __construct($file, $tmpDir)
    {
        $this->file   = $file;
        $this->tmpDir = $tmpDir;
    }

    /**
     * 1. Change working directory.
     * 2. Run tar command to re-create .tgz
     *
     * @return $this
     * @throws \RuntimeException When tar command fails.
     */
    public function run()
    {
        \chdir($this->tmpDir);

        unlink($this->file);
        $newName = substr(basename($this->file), 0, -4);

        $cmd = "tar -czf {$newName}.tgz {$newName} package.xml";
        exec($cmd, $output, $ret);
        if ($ret != 0) {
            throw new \RuntimeException("Could not re-create PEAR package.");
        }
        return $this;
    }
}
