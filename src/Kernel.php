<?php namespace Nano7\Console;

use Nano7\Foundation\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as ConsoleApp;

class Kernel
{
    /**
     * @var Application
     */
    public $app;

    /**
     * @var ConsoleApp
     */
    protected $console;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->console = new ConsoleApp('Nano7', $app->getVersion());
        $this->console->setAutoExit(false);
        $this->console->setCatchExceptions(false);
    }

    /**
     * Handle console.
     */
    public function handle(InputInterface $input = null, OutputInterface $output = null)
    {
        try {
            // Set running mode
            $this->app->instance('mode', 'console');

            // App boot
            $this->app->boot();

            $exitCode = $this->console->run($input, $output);

            return $exitCode;

        } catch (\Exception $e) {
            $this->console->renderException($e, $output);

            return 1;
        }
    }

    /**
     * @param $command
     * @return null|\Symfony\Component\Console\Command\Command
     */
    public function command($command)
    {
        if (is_string($command)) {
            $command = $this->app->make($command);
        }

        $command->setConsole($this);

        return $this->console->add($command);
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->console->setName($name);
    }

    /**
     * @param $version
     */
    public function setVersion($version)
    {
        $this->console->setVersion($version);
    }
}