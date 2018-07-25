<?php namespace Nano7\Console\Traits;

use Nano7\Foundation\Support\Filesystem;

trait DaemonFiles
{
    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var string
     */
    protected $flowControlName = '';

    /**
     * @var bool
     */
    private $preparedFiles = false;

    /**
     * Prepare files.
     */
    protected function prepareFiles()
    {
        if ($this->preparedFiles) {
            return;
        }

        $this->files = new Filesystem();
        $this->preparedFiles = true;
    }

    /**
     * Reset flags.
     */
    protected function resetFlags()
    {
        $this->prepareFiles();

        $path = $this->getPathDaemonFlow();

        $this->files->force($path);
        $this->files->deleteFiles($path, '*.flow');

        $this->setFlag('daemon');
    }

    /**
     * @return string
     */
    private function getPathDaemonFlow()
    {
        return realpath(__DIR__ . '/../flows') . '/' . $this->flowControlName;
    }

    /**
     * @param $flag
     * @param $value
     * @return bool
     */
    protected function setFlag($flag, $value = true)
    {
        $file = $this->files->combine($this->getPathDaemonFlow(), $flag . '.flow');

        if ($value) {
            $this->files->put($file, '.');
        } else {
            $this->files->delete($file);
        }

        return $value;
    }

    /**
     * @param $flag
     * @return bool
     */
    protected function hasFlag($flag)
    {
        $file = $this->files->combine($this->getPathDaemonFlow(), $flag . '.flow');

        return $this->files->exists($file);
    }
}