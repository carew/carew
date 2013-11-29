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

        $this->registerExtension(new CoreExtension());

        $this->add(new Commands\Serve());
        $this->add(new Commands\GeneratePost());
        $this->add(new Commands\Build());
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

    public function getBuilder()
    {
        return $this->container['builder'];
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption('--base-dir')) {
            $baseDir = realpath($input->getParameterOption('--base-dir'));

            if (!is_dir($baseDir)) {
                throw new \InvalidArgumentException(sprintf('Base directory does not exist or it is not a readable directory: "%s".', $input->getParameterOption('--base-dir')));
            }

            // We have to load extension after the override of `base_dir`
            // configuration; because extension can rely on configuration inside the
            // `config.yml` file.

            $this->container['base_dir'] = $baseDir;
        }

        $this->loadExtensions();

        return parent::doRun($input, $output);
    }

    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();

        $inputDefinition->addOptions(array(
            new InputOption('base-dir', null, InputOption::VALUE_REQUIRED, 'The location of your content', getcwd()),
        ));

        return $inputDefinition;
    }

    private function loadExtensions()
    {
        $config = $this->container['config'];

        if (isset($config['engine']['extensions'])) {
            $extensions = (array) $config['engine']['extensions'];
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
}
