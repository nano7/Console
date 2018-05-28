<?php namespace Nano7\Console;

use Nano7\Foundation\Support\ServiceProvider;

class ConsoleServiceProviders extends ServiceProvider
{
    /**
     * Register objetos para web.
     */
    public function register()
    {
        $this->registerKernel();
    }

    /**
     * Register kernel console.
     */
    public function registerKernel()
    {
        $this->app->singleton('kernel.console', function ($app) {
            $console = new Kernel($app);

            // Carregar definicoes do console via evento
            event()->fire('register.commands', [$console]);

            // Carregar definicoes do console
            $console_file = app_path('console.php');
            if (file_exists($console_file)) {
                require $console_file;
            }

            return $console;
        });
    }
}