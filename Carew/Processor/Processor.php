<?php

namespace Carew\Processor;

use Carew\Event\Events;
use Carew\Model\Document;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Finder\Finder;

class Processor
{
    private $eventDispatcher;
    private $input;
    private $output;

    public function __construct(EventDispatcherInterface $eventDispatcher, InputInterface $input = null, OutputInterface $output = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->input           = $input;
        $this->output          = $output;
    }

    public function process($dir, $filenamePattern = '.md', array $extraEvents = array(), $allowEmptyHeader = false)
    {
        if (!is_dir($dir)) {
            return array();
        }

        $documents = array();
        $finder = new Finder();
        foreach ($finder->in($dir)->files()->name($filenamePattern) as $file) {
            if ($this->input && $this->output && $this->input->getOption('verbose')) {
                $this->output->writeln(sprintf('Processing <info>%s</info>', $file->getRelativePathName()));
            }

            $document = new Document($file);

            $event = new GenericEvent($document, array('allowEmptyHeader' => $allowEmptyHeader));

            try {
                $event = $this->eventDispatcher->dispatch(Events::DOCUMENT, $event);
                foreach ($extraEvents as $eventName) {
                    $event = $this->eventDispatcher->dispatch($eventName, $event);
                }

                $document = $event->getSubject();
            } catch (\Exception $e) {
                throw new \LogicException(sprintf('Could not process: "%s". Error: "%s"', (string) $file, $e->getMessage()));
            }

            $documents[$document->getPath()] = $document;
        }

        return $documents;
    }
}
