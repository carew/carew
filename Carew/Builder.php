<?php

namespace Carew;

use Carew\Processor;
use Psr\Log\LoggerInterface;
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

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function build($baseDir, $webDir, $all = false)
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
            $this->logger and $this->logger->info(sprintf('Reading %s', $folder));
            $files = $this->finder->create()->in($folder)->files();
            foreach ($files as $file) {
                $this->logger and $this->logger->debug(sprintf('  >> Reading %s', (string) $file));
                $document = $this->processor->processFile($file, $folderRaw, $type);
                if (!$document->isPublished() && !$all) {
                    continue;
                }
                $documents[$document->getFilePath()] = $document;
            }
        }

        $this->logger and $this->logger->info('Processing globals');
        $globals = $this->twig->getGlobals();
        $this->processor->processGlobals($documents, $globals['carew']);

        $this->logger and $this->logger->info('Processing all documents');
        $documents = $this->processor->processDocuments($documents, $globals['carew']);

        $this->logger and $this->logger->info('Cleaning target folder');
        $this->filesystem->remove($this->finder->create()->in($webDir)->exclude(basename(realpath($baseDir))));

        $this->logger and $this->logger->info('Compiling');
        foreach ($documents as $document) {
            $this->logger and $this->logger->debug(sprintf('  >> Compiling %s', $document->getPath()));
            $this->processor->processDocument($document);
        }

        $this->logger and $this->logger->info('Writing');
        foreach ($documents as $document) {
            $documentsTmps = $this->processor->processDocumentDecoration($document);
            if (!$documentsTmps) {
                continue;
            }
            foreach ($documentsTmps as $documentTmp) {
                $this->logger and $this->logger->debug(sprintf('  >> Writing %s', $documentTmp->getPath()));
                $this->processor->write($documentTmp, $webDir);
            }
        }

        $this->logger and $this->logger->info('Finishing');
        $this->processor->terminate($webDir);

        $this->logger and $this->logger->notice('Build finished');
        $this->logger and $this->logger->debug(sprintf('Time: %.2f seconds, Memory: %.2fMb', (microtime(true) - $startAt), (memory_get_usage() - $memoryUsage)/(1024 * 1024)));
    }
}
