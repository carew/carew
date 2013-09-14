<?php

namespace Carew\Command;

use Cocur\Slugify\Slugify;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratePost extends BaseCommand
{
    private $slugify;
    private $defaultDate;

    public function __construct($slugify = null, $defaultDate = null)
    {
        if (null == $defaultDate) {
            $this->defaultDate = date('Y-m-d');
        }

        $this->slugify = $slugify ?: new Slugify();

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('generate:post')
            ->setDescription('Generate a new post')
            ->setDefinition(array(
                new InputArgument('title', InputArgument::REQUIRED, 'The title'),
                new InputOption('--date', null, InputOption::VALUE_REQUIRED, 'Date (format: YYYY-MM-DD', $this->defaultDate),
            ))
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $baseDir = $input->getOption('base-dir');
        $date    = $input->getOption('date');
        $title   = $input->getArgument('title');

        $content = <<<EOL
---
layout: post
title:  {{ title }}
---


EOL;

        $content = strtr($content, array(
            '{{ title }}' => $title,
        ));

        $slug = $this->slugify->slugify($title);

        $postDir = "$baseDir/posts";
        if (!file_exists($postDir)) {
            mkdir($postDir);
        }

        $filePath = sprintf('%s/%s-%s.md', $postDir, $date, $slug);

        if (file_exists($filePath)) {
            $output->writeln('<error>A blog post already exists</error>');

            return 1;
        }

        file_put_contents($filePath, $content);

        $output->writeln(sprintf('<info>New blog post: %s</info>', $filePath));
    }
}
