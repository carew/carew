<?php

namespace Carew\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Carew\Processor;
use Carew\Events;

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
                new InputOption('web-dir', null, InputOption::VALUE_REQUIRED, 'Where to write generated content', $this->container['web_dir']),
                new InputOption('base-dir', null, InputOption::VALUE_REQUIRED, 'Where locate your content', $this->container['base_dir']),
            ))
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $startAt = microtime(true);
        $memoryUsage = memory_get_usage();

        $this->container['base_dir'] = $baseDir = $input->getOption('base-dir');
        $this->container['web_dir'] = $webDir = $input->getOption('web-dir');

        $processor = $this->container['processor'];

        $input->getOption('verbose') and $output->writeln('Processing <comment>Posts</comment>');
        $posts = $processor->process($baseDir.'/posts', '*-*-*-*.md', array(Events::POST));
        $posts = $processor->sortByDate($posts);

        $input->getOption('verbose') and $output->writeln('Processing <comment>Pages</comment>');
        $pages = $processor->process($baseDir.'/pages', '*.md', array(Events::PAGE));

        $input->getOption('verbose') and $output->writeln('Processing <comment>Api</comment>');
        $api = $processor->process($baseDir.'/api', '*', array(Events::API), true);

        $documents = array_merge($posts, $pages, $api);

        $tags       = $processor->buildCollection($documents, 'tags');
        $navigation = $processor->buildCollection($documents, 'navigation');

        $input->getOption('verbose') and $output->writeln('Processing <comment>Tags page</comment>');
        $documents = array_merge($documents, $processor->processTags($tags, $baseDir));

        $input->getOption('verbose') and $output->writeln('Processing <comment>Index page</comment>');
        $documents = array_merge($documents, $processor->processIndex($baseDir));

        $input->getOption('verbose') and $output->writeln('<comment>Cleaned target folder</comment>');
        $this->container['filesystem']->remove($this->container['finder']->in($webDir)->exclude(basename(realpath($baseDir))));

        $this->container['twigGlobales'] = array_replace($this->container['twigGlobales'], array(
            'latest'     => reset($posts),
            'navigation' => $navigation,
            'documents'  => $documents,
            'posts'      => $posts,
            'tags'       => $tags,
        ));

        $builder = $this->container['builder'];
        foreach ($documents as $document) {
            $input->getOption('verbose') and $output->writeln(sprintf('Render <comment>%s</comment>', $document->getPath()));
            $builder->buildDocument($document);
        }

        if (isset($this->container['config']['engine']['theme_path'])) {
            $input->getOption('verbose') and $output->writeln('<comment>Copy theme assets</comment>');
            $themePath = str_replace('%dir%', $baseDir, $this->container['config']['engine']['theme_path']);
            if (isset($themePath) && is_dir($themePath.'/assets')) {
                $this->container['filesystem']->mirror($themePath.'/assets/', $webDir.'/');
            }
        }

        if (is_dir($baseDir.'/assets')) {
            $input->getOption('verbose') and $output->writeln('<comment>Copy assets</comment>');
            $this->container['filesystem']->mirror($baseDir.'/assets/', $webDir.'/', null, array('override' => true));
        }

        $output->writeln('<info>Build finished</info>');
        $input->getOption('verbose') and $output->writeln(sprintf('Time: %.2f seconds, Memory: %.2fMb', (microtime(true) - $startAt), (memory_get_usage() - $memoryUsage)/(1024 * 1024)));
    }
}
