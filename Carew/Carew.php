<?php

namespace Carew;

use Carew\Command as Commands;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Carew extends Application
{
    const VERSION = '2.0.0-dev';

    private $container;

    public function __construct()
    {
        parent::__construct('Carew', static::VERSION);

        $this->container = new \Pimple();
        $this->container['carew'] = $this;

        $this->add(new Commands\Serve());
        $this->add(new Commands\GeneratePost());
        $this->add(new Commands\Build($this->container));
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

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $baseDir = realpath($input->getParameterOption('--base-dir'));

        if (!is_dir($baseDir)) {
            throw new \InvalidArgumentException(sprintf('Base dir doest not exists or it is not a directory: "%s"', $baseDir));
        }

        $this->container['base_dir'] = $baseDir;

        $this->registerExtension(new CoreExtension());

        $this->loadThemes();
        $this->loadExtensions();

        return parent::doRun($input, $output);
    }

    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();

        $inputDefinition->addOptions(array(
            new InputOption('base-dir', null, InputOption::VALUE_REQUIRED, 'Where locate your content', getcwd()),
        ));

        return $inputDefinition;
    }

    private function loadExtensions()
    {
        $config = $this->container['config'];

        if (isset($config['engine']['extensions'])) {
            if (!is_array($extensions = $config['engine']['extensions'])) {
                $extensions = array($extensions);
            }
            foreach ($extensions as $extension) {
                if (!class_exists($extension)) {
                    throw new \LogicException(sprintf('The class "%s" does not exists. See "config.yml".', $extension));
                }
                $extension = new $extension();
                if (!$extension instanceof ExtensionInterface) {
                    throw new \LogicException(sprintf('The class "%s" does not implements ExtensionInterface. See "config.yml".', get_class($extension)));
                }
                $this->registerExtension($extension);
            }
        }
    }

    private function loadThemes()
    {
        $this->container['themes'] = $this->container->share($this->container->extend('themes', function ($themesPath, $container) {
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
}
