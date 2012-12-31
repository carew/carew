<?php

namespace Carew;

use Carew\Console\Command as Commands;
use Carew\Extension\ExtensionInterface;
use Carew\Extension\Core as CoreExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class Carew
{
    const VERSION = '0.1-dev';

    private $container;
    private $application;

    public function __construct()
    {
        $this->container = new \Pimple();

        $this->register(new CoreExtension());

        $this->application = new Application('Carew', static::VERSION);

        $this->addCommand(new Commands\GeneratePost());
        $this->addCommand(new Commands\Build($this->container));
    }

    public function register(ExtensionInterface $extension)
    {
        $extension->register($this->getContainer());
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getEventDispatcher()
    {
        return $this->container['event_dispatcher'];
    }

    public function addCommand(Command $command)
    {
        $this->application->add($command);

        return $this;
    }

    public function run()
    {
        return $this->application->run();
    }
}
