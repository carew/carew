<?php

namespace Carew;

use Carew\Command as Commands;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Carew extends Application
{
    const VERSION = '1.4.0-dev';

    private $container;
    private $application;

    public function __construct()
    {
        $this->container = new \Pimple();
        $this->container['carew'] = $this;

        parent::__construct('Carew', static::VERSION);

        $this->add(new Commands\GeneratePost());
        $this->add(new Commands\Build($this->container));
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
                $this->registerExtension($extension);
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

    public function registerExtension(ExtensionInterface $extension)
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

    // Kept for BC
    public function addCommand(Command $command)
    {
        $this->add($command);

        return $this;
    }

    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();

        $inputDefinition->addOptions(array(
            new InputOption('--base-dir', null, InputOption::VALUE_REQUIRED, 'Where locate your content', getcwd()),
        ));

        return $inputDefinition;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $baseDir = realpath($input->getParameterOption('--base-dir'));

        if (!file_exists($baseDir) || !is_dir($baseDir)) {
            throw new \InvalidArgumentException(sprintf('Base dir doest not exists or it is not a directory: "%s"', $baseDir));
        }

        $this->container['base_dir'] = $baseDir;

        $this->registerExtension(new CoreExtension());

        $this->loadThemes();
        $this->loadExtensions();

        parent::doRun($input, $output);
    }
}
