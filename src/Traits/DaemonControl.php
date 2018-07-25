<?php namespace Nano7\Console\Traits;

trait DaemonControl
{
    use DaemonFiles;

    /**
     * @param $callback
     * @param int $sleep
     * @param int $memoryLimit
     */
    protected function runDaemon($callback, $sleep = 3, $memoryLimit = 128)
    {
        $this->resetFlags();

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
        return $this->hasFlag('pause');
    }

    /**
     * Pause daemon.
     */
    protected function pause()
    {
        $this->setFlag('pause', true);
    }

    /**
     * Remove pause.
     */
    protected function start()
    {
        $this->setFlag('pause', false);
    }

    /**
     * @return bool
     */
    private function inShouldQuit()
    {
        return ! $this->hasFlag('daemon');
    }

    /**
     * Quit deamon.
     */
    protected function quit()
    {
        $this->setFlag('daemon', false);
    }
}