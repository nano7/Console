<?php namespace Nano7\Console;

use Nano7\Foundation\Support\Filesystem;

trait DaemonControl
{
    /**
     * @var string
     */
    public $flowControlName = '';

    /**
     * @var Filesystem
     */
    private $flowFile;

    /**
     * @param $callback
     * @param int $sleep
     * @param int $memoryLimit
     */
    protected function runDaemon($callback, $sleep = 3, $memoryLimit = 128)
    {
        $this->flowFile = new Filesystem();

        $this->resetSignals();

        while (true) {
            if ($this->inPaused()) {
                $this->sleep($sleep);
                $this->stopIfNecessary($memoryLimit);

                continue;
            }

            $return = $callback();

            // Se não processou nada, deve executar o sleepprogramado
            if ($return === false) {
                $this->sleep($sleep);
            }

            // Verificar se deve derrupar processo
            $this->stopIfNecessary($memoryLimit);
        }
    }

    /**
     * Sleep the script for a given number of seconds.
     *
     * @param  int|float   $seconds
     * @return void
     */
    private function sleep($seconds)
    {
        if ($seconds < 1) {
            usleep($seconds * 1000000);
        } else {
            sleep($seconds);
        }
    }

    /**
     * Determine if the memory limit has been exceeded.
     *
     * @param  int   $memoryLimit
     * @return bool
     */
    private function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * Stop the process if necessary.
     *
     * @param  int  $memory
     */
    protected function stopIfNecessary($memory)
    {
        if ($this->inShouldQuit()) {
            $this->kill();
        }

        if ($this->memoryExceeded($memory)) {
            $this->kill(12);
        }
    }

    /**
     * Kill the process.
     *
     * @param  int  $status
     * @return void
     */
    public function kill($status = 0)
    {
        exit($status);
    }

    /**
     * @return bool
     */
    private function inPaused()
    {
        $file = $this->flowFile->combine($this->getPathControlFlow(), 'pause.flow');

        return $this->flowFile->exists($file);
    }

    /**
     * @return bool
     */
    private function inShouldQuit()
    {
        $file = $this->flowFile->combine($this->getPathControlFlow(), 'daemon.flow');

        return ! $this->flowFile->exists($file);
    }

    /**
     * Reset controls de fluxo.
     */
    private function resetSignals()
    {
        $path = $this->getPathControlFlow();
        $this->flowFile->force($path);

        $this->flowFile->deleteFiles($path, '*.flow');

        $file = $this->flowFile->combine($this->getPathControlFlow(), 'daemon.flow');
        $this->flowFile->put($file, '.');
    }

    /**
     * @return string
     */
    private function getPathControlFlow()
    {
        return realpath(__DIR__ . '/../flows') . '/' . $this->flowControlName;
    }
}