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
            ->setName('carew:build')
            ->setDescription('Builds static html files from markdown source')
            ->setDefinition(array(
                new InputOption('--web-dir', null, InputOption::VALUE_REQUIRED, 'Where to write generated content', getcwd().'/web'),
            ))
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $startAt = microtime(true);
        $memoryUsage = memory_get_usage();

        $baseDir = $this->container['base_dir'];
        $webDir = $this->container['web_dir'] = $input->getOption('web-dir');

        $this->container['filesystem']->mkdir($webDir);

        $documents = array();
        foreach ($this->container['config']['folders'] as $folderRaw => $type) {
            $folder = "$baseDir/$folderRaw";
            if (!is_dir($folder)) {
                continue;
            }
            $input->getOption('verbose') and $output->writeln(sprintf('<info>Reading</info> <comment>%s</comment>', $folder));
            $files = $this->container['finder']->create()->in($folder)->files();
            foreach ($files as $file) {
                $input->getOption('verbose') and $output->writeln(sprintf('  >> <comment>%s</comment>', (string) $file));
                $document = $this->container['processor']->processFile($file, $folderRaw, $type);
                $documents[$document->getFilePath()] = $document;
            }
        }

        $input->getOption('verbose') and $output->writeln('<info>Processing all documents</info>');
        $documents = $this->container['processor']->processDocuments($documents);
        $globalVars = $this->container['processor']->processGlobals($documents);

        $input->getOption('verbose') and $output->writeln('<info>Cleaning target folder</info>');
        $this->container['filesystem']->remove($this->container['finder']->in($webDir)->exclude(basename(realpath($baseDir))));

        $input->getOption('verbose') and $output->writeln('<info>Compiling and Writing</info>');
        foreach ($documents as $document) {
            $input->getOption('verbose') and $output->writeln(sprintf('  >> <info>Compiling</info> <comment>%s</comment>', $document->getPath()));
            $documentsTmp = $this->container['processor']->processDocument($document, $globalVars);
            if (!is_array($documentsTmp)) {
                $documentsTmp = array($documentsTmp);
            }
            foreach ($documentsTmp as $documentTmp) {
                $input->getOption('verbose') and $output->writeln(sprintf('  >> <info>Writing</info> <comment>%s</comment>', $documentTmp->getPath()));
                $this->container['processor']->write($documentTmp);
            }
        }

        $input->getOption('verbose') and $output->writeln('<info>Copy assets</info>');
        foreach ($this->container['themes'] as $theme) {
            $path = $theme.'/assets/';
            if (is_dir($path)) {
                $this->container['filesystem']->mirror($path, $webDir.'/', null, array('override' => true));
            }
        }

        $output->writeln('<info>Build finished</info>');
        $input->getOption('verbose') and $output->writeln(sprintf('Time: %.2f seconds, Memory: %.2fMb', (microtime(true) - $startAt), (memory_get_usage() - $memoryUsage)/(1024 * 1024)));
    }
}
