<?php

namespace Carew\Command;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Builds static html files from markdown source')
            ->setDefinition(array(
                new InputOption('web-dir', null, InputOption::VALUE_REQUIRED, 'Where to write generated content.', getcwd().'/web'),
                new InputOption('all', null, InputOption::VALUE_NONE, 'Build all document, even if they are not published yet.'),
            ))
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = $this->getApplication()->getBuilder();

        $verbosityLevelMap = array(
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_VERBOSE,
        );
        $builder->setLogger(new ConsoleLogger($output, $verbosityLevelMap));

        $builder->build($input->getOption('base-dir'), $input->getOption('web-dir'), $input->getOption('all'));
    }
}
