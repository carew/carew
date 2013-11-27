<?php

namespace Carew\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends BaseCommand
{
    private $container;

    public function __construct(\Pimple $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Builds static html files from markdown source')
            ->setDefinition(array(
                new InputOption('web-dir', null, InputOption::VALUE_REQUIRED, 'Where to write generated content', getcwd().'/web'),
            ))
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $baseDir = $this->container['base_dir'];
        $webDir = $input->getOption('web-dir');

        $this->container['builder']->build($output, $input, $baseDir, $webDir);
    }
}
