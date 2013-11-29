<?php

namespace Carew;

use Carew\Processor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Builder
{
    private $processor;
    private $config;
    private $themes;
    private $twig;
    private $filesystem;
    private $finder;

    public function __construct(Processor $processor, array $config, array $themes, \Twig_Environment $twig, Filesystem $filesystem, Finder $finder)
    {
        $this->processor = $processor;
        $this->config = $config;
        $this->themes = $themes;
        $this->twig = $twig;
        $this->filesystem = $filesystem;
        $this->finder = $finder;
    }

    public function build(OutputInterface $output, InputInterface $input, $baseDir, $webDir)
    {
        $startAt = microtime(true);
        $memoryUsage = memory_get_usage();

        $this->filesystem->mkdir($webDir);

        $documents = array();
        foreach ($this->config['folders'] as $folderRaw => $type) {
            $folder = sprintf('%s/%s', $baseDir, $folderRaw);
            if (!is_dir($folder)) {
                continue;
            }
            $input->getOption('verbose') and $output->writeln(sprintf('<info>Reading</info> <comment>%s</comment>', $folder));
            $files = $this->finder->create()->in($folder)->files();
            foreach ($files as $file) {
                $input->getOption('verbose') and $output->writeln(sprintf('  >> <info>Reading</info> <comment>%s</comment>', (string) $file));
                $document = $this->processor->processFile($file, $folderRaw, $type);
                $documents[$document->getFilePath()] = $document;
            }
        }

        $input->getOption('verbose') and $output->writeln('<info>Processing globals</info>');
        $globals = $this->twig->getGlobals();
        $this->processor->processGlobals($documents, $globals['carew']);

        $input->getOption('verbose') and $output->writeln('<info>Processing all documents</info>');
        $documents = $this->processor->processDocuments($documents, $globals['carew']);

        $input->getOption('verbose') and $output->writeln('<info>Cleaning target folder</info>');
        $this->filesystem->remove($this->finder->create()->in($webDir)->exclude(basename(realpath($baseDir))));

        $input->getOption('verbose') and $output->writeln('<info>Compiling</info>');
        foreach ($documents as $document) {
            $input->getOption('verbose') and $output->writeln(sprintf('  >> <info>Compiling</info> <comment>%s</comment>', $document->getPath()));
            $this->processor->processDocument($document);
        }

        $input->getOption('verbose') and $output->writeln('<info>Writing</info>');
        foreach ($documents as $document) {
            $documentsTmps = $this->processor->processDocumentDecoration($document);
            foreach ($documentsTmps as $documentTmp) {
                $input->getOption('verbose') and $output->writeln(sprintf('  >> <info>Writing</info> <comment>%s</comment>', $documentTmp->getPath()));
                $this->processor->write($documentTmp, $webDir);
            }
        }

        $input->getOption('verbose') and $output->writeln('<info>Finishing</info>');
        $this->processor->terminate($webDir);

        $output->writeln('<info>Build finished</info>');
        $input->getOption('verbose') and $output->writeln(sprintf('Time: %.2f seconds, Memory: %.2fMb', (microtime(true) - $startAt), (memory_get_usage() - $memoryUsage)/(1024 * 1024)));
    }
}
