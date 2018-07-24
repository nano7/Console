<?php namespace Nano7\Console;

trait DaemonControl
{
    /**
     * Indicates if the worker should exit.
     *
     * @var bool
     */
    public $shouldQuit = false;

    /**
     * Indicates if the worker is paused.
     *
     * @var bool
     */
    public $paused = false;

    /**
     * @param $callback
     * @param int $sleep
     * @param int $memoryLimit
     */
    protected function runDaemon($callback, $sleep = 3, $memoryLimit = 128)
    {
        if ($this->supportsAsyncSignals()) {
            $this->listenForSignals();
        }

        while (true) {
            if ($this->paused) {
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
        if ($this->shouldQuit) {
            $this->kill();
        }

        if ($this->memoryExceeded($memory)) {
            $this->stop(12);
        }
    }

    /**
     * Determine if "async" signals are supported.
     *
     * @return bool
     */
    private function supportsAsyncSignals()
    {
        return extension_loaded('pcntl');
    }

    /**
     * Enable async signals for the process.
     *
     * @return void
     */
    private function listenForSignals()
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, function () {
            $this->shouldQuit = true;
        });

        pcntl_signal(SIGUSR2, function () {
            $this->paused = true;
        });

        pcntl_signal(SIGCONT, function () {
            $this->paused = false;
        });
    }

    /**
     * Kill the process.
     *
     * @param  int  $status
     * @return void
     */
    public function kill($status = 0)
    {
        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }

        exit($status);
    }

    /**
     * Stop listening and bail out of the script.
     *
     * @param  int  $status
     * @return void
     */
    public function stop($status = 0)
    {
        exit($status);
    }
}