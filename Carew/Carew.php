<?php

namespace Carew;

use Carew\Command as Commands;
use Symfony\Component\Console\Command\Command;

class Carew
{
    const VERSION = '1.3.0-DEV';

    private $container;
    private $application;

    public function __construct()
    {
        $this->container = new \Pimple();
        $this->container['carew'] = $this;

        $this->register(new CoreExtension());

        $this->application = new Application($this->container);

        $this->loadExtensions();
    }

    public function loadExtensions()
    {
        $config = $this->container['config'];

        if (isset($config['engine']['extensions'])) {
            if (!is_array($extensions = $config['engine']['extensions'])) {
                $extensions = array($extensions);
            }
            foreach ($extensions as $extension) {
                if (!class_exists($extension)) {
                    throw new \LogicException(sprintf('The class (in your config.yml) "%s" does not exists', $extension));
                }
                $extension = new $extension();
                if (!$extension instanceof ExtensionInterface) {
                    throw new \LogicException(sprintf('The class "%s" does not implements ExtensionInterface', get_class($extension)));
                }
                $this->register($extension);
            }
        }
    }

    public function loadThemes()
    {
        $this->container['themes'] = $this->container->share($this->container->extend('themes', function($themesPath, $container) {
            $config = $container['config'];
            if (isset($config['engine']['themes'])) {
                if (!is_array($themes = $config['engine']['themes'])) {
                    $themes = array($themes);
                }
                foreach ($themes as $theme) {
                    $themesPath[] = str_replace('%dir%', $container['base_dir'], $theme);
                }
            }

            return $themesPath;
        }));
    }

    public function register(ExtensionInterface $extension)
    {
        $extension->register($this);

        return $this;
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
