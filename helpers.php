<?php

if (! function_exists('console')) {
    /**
     * @return \Nano7\Console\Kernel
     */
    function console()
    {
        return app('kernel.console');
    }
}