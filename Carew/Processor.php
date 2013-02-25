<?php

namespace Carew;

use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Finder;

class Processor
{
    private $eventDispatcher;
    private $finder;

    public function __construct(EventDispatcherInterface $eventDispatcher, Finder $finder = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->finder          = $finder ?: new Finder();
    }

    public function process($baseDir, $filenamePattern = '.md', $type = Document::TYPE_UNKNOWN, $allowEmptyHeader = false)
    {
        if (!is_dir($baseDir)) {
            return array();
        }

        $documents = array();
        $finder = $this->finder->create();
        foreach ($finder->in($baseDir)->files()->name($filenamePattern) as $file) {
            $document = new Document($file, basename($baseDir).'/'.$file->getRelativePathname(), $type);

            $event = new CarewEvent($document, array('allowEmptyHeader' => $allowEmptyHeader));

            try {
                $this->eventDispatcher->dispatch(Events::DOCUMENT, $event);

                $document = $event->getSubject();
            } catch (\Exception $e) {
                throw new \LogicException(sprintf('Could not process: "%s". Error: "%s"', (string) $file, $e->getMessage()));
            }

            $documents[$document->getFilePath()] = $document;
        }

        $event = new CarewEvent($documents);
        $this->eventDispatcher->dispatch(Events::DOCUMENTS, $event);
        $documents = $event->getSubject();

        return $documents;
    }

    public function processTags($tags, $baseDir)
    {
        if (!is_dir($baseDir.'/layouts/')) {
            return array();
        }

        $documents = array();
        $finder = $this->finder->create();
        foreach ($finder->in($baseDir.'/layouts/')->files()->name('tags.*.twig') as $file) {
            $file = $file->getBasename();

            preg_match('#tags\.(.+?)\.twig$#', $file, $match);
            $format = $match[1];

            foreach ($tags as $tag => $posts) {
                $document = new Document();
                $document->setLayout((string) $file);
                $document->setPath(sprintf('tags/%s.%s', $tag, $format));
                $document->setTitle('Tags: '.$tag);
                $document->setVars(array(
                    'tag'   => $tag,
                    'posts' => $posts,
                ));

                $event = new CarewEvent($document);
                $this->eventDispatcher->dispatch(Events::TAG, $event);
                $document = $event->getSubject();

                $documents[$document->getPath()] = $document;
            }
        }

        $event = new CarewEvent($documents);
        $this->eventDispatcher->dispatch(Events::TAGS, $event);
        $documents = $event->getSubject();

        return $documents;
    }

    public function processIndex($pages, $posts, $baseDir)
    {
        if (!is_dir($baseDir.'/layouts/')) {
            return array();
        }

        $documents = array();
        $finder = $this->finder->create();
        foreach ($finder->in($baseDir.'/layouts/')->files()->name('index.*.twig') as $file) {
            $file = $file->getBasename();

            preg_match('#index\.(.+?)\.twig$#', $file, $match);
            $format = $match[1];

            $document = new Document();
            $document->setLayout((string) $file);
            $document->setPath(sprintf('index.%s', $format));
            $document->setTitle(false);
            $document->setVars(array('pages' => $pages, 'posts' => $posts));

            $event = new CarewEvent($document);
            $this->eventDispatcher->dispatch(Events::INDEX, $event);
            $document = $event->getSubject();

            $documents[$document->getPath()] = $document;
        }

        $event = new CarewEvent($documents);
        $this->eventDispatcher->dispatch(Events::INDEXES, $event);
        $documents = $event->getSubject();

        return $documents;
    }

    public function sortByDate($documents)
    {
        uasort($documents, function ($a, $b) {
            $aMetadatas = $a->getMetadatas();
            $bMetadatas = $b->getMetadatas();
            if ($aMetadatas['date'] == $bMetadatas['date']) {
                return 0;
            }

            return ($aMetadatas['date'] > $bMetadatas['date']) ? -1 : 1;
        });

        return $documents;
    }

    public function buildCollection($documents, $key)
    {
        $collection = array();
        foreach ($documents as $document) {
            $metadatas = $document->getMetadatas();
            if (isset($metadatas[$key]) && is_array($metadatas[$key])) {
                foreach ($metadatas[$key] as $item) {
                    if (!array_key_exists($item, $collection)) {
                        $collection[$item] = array();
                    }

                    $collection[$item][] = $document;
                }
            }
        }

        return $collection;
    }
}
