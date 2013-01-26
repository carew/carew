<?php

namespace Carew;

use Carew\Command as Commands;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

class Application extends BaseApplication
{
    private $container;

    public function __construct(\Pimple $container)
    {
        $this->container = $container;

        parent::__construct('Carew', Carew::VERSION);

        $this->add(new Commands\GeneratePost());
        $this->add(new Commands\Build($container));
    }

    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();

        $inputDefinition->addOptions(array(
            new InputOption('--base-dir', null, InputOption::VALUE_REQUIRED, 'Where locate your content', $this->container['base_dir']),
        ));

        return $inputDefinition;
    }
}
