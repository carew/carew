<?php

namespace Carew;

use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;

class Processor
{
    private $target;
    private $eventDispatcher;
    private $filesystem;

    public function __construct($target, EventDispatcherInterface $eventDispatcher = null, Filesystem $filesystem = null)
    {
        $this->target = $target;
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function processFile($file, $folder = '', $type = Document::TYPE_UNKNOWN)
    {
        $internalPath = trim($folder.'/'.$file->getRelativePathname(), '/');
        $document = new Document($file, $internalPath, $type);

        $event = new CarewEvent($document);

        try {
            $document = $this->eventDispatcher->dispatch(Events::DOCUMENT_HEADER, $event)->getSubject();
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('Could not process: "%s".', (string) $file), 0 , $e);
        }

        return $document;
    }

    public function processDocuments($documents)
    {
        $event = new CarewEvent($documents);
        $this->eventDispatcher->dispatch(Events::DOCUMENTS, $event);

        $globals = $this->buildCollectionsWithType($documents);
        $globals['documents'] = $documents;

        // @TODO: Move this to configuration
        foreach (array('tags', 'navigation') as $key) {
            $globals[$key] = $this->buildCollectionWithMetadatas($documents, $key);
        }

        $globals = $this->mergeDefaultGlobals($globals);

        return array($event->getSubject(), $globals);
    }

    public function processDocument($document, array $globalVars = array())
    {
        $event = new CarewEvent($document, array('globalVars' => $globalVars));
        try {
            $document = $this->eventDispatcher->dispatch(Events::DOCUMENT_BODY, $event)->getSubject();
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('Could not process: "%s".', (string) $document->getFile()), 0 , $e);
        }
    }

    public function write($document)
    {
        $target = $this->target.'/'.$document->getPath();
        $this->filesystem->mkdir(dirname($target));
        file_put_contents($target, $document->getBody());
    }

    private function sortByDate($documents)
    {
        uasort($documents, function ($a, $b) {
            $aMetadatas = $a->getMetadatas();
            $bMetadatas = $b->getMetadatas();
            if (!array_key_exists('date', $aMetadatas) || !array_key_exists('date', $bMetadatas)) {
                return 0;
            }

            if ($aMetadatas['date'] == $bMetadatas['date']) {
                return 0;
            }

            return ($aMetadatas['date'] < $bMetadatas['date']) ? -1 : 1;
        });

        return $documents;
    }

    private function buildCollectionWithMetadatas($documents, $key)
    {
        $collection = array();
        foreach ($documents as $document) {
            $metadatas = $document->getMetadatas();
            if (isset($metadatas[$key])) {
                $items = $metadatas[$key];
                if (!is_array($items)) {
                    $items = array($items);
                }
                foreach ($items as $item) {
                    $item = $item;
                    if (!array_key_exists($item, $collection)) {
                        $collection[$item] = array();
                    }

                    $collection[$item][$document->getFilePath()] = $document;
                }
            }
        }

        return $collection;
    }

    private function buildCollectionsWithType($documents)
    {
        $collections = array();
        foreach ($documents as $document) {
            $type = $document->getType().'s';
            if (!array_key_exists($type, $collections)) {
                $collections[$type] = array();
            }

            $collections[$type][$document->getFilePath()] = $document;
        }

        if (isset($collections['posts'])) {
            $collections['posts'] = $this->sortByDate($collections['posts']);
        }

        return $collections;
    }

    private function mergeDefaultGlobals($globals)
    {
       return array_replace(
            array(
                'documents'  => array(),
                'pages'      => array(),
                'apis'       => array(),
                'posts'      => array(),
                'unknown'    => array(),
                'tags'       => array(), // @TODO: Move to conf
                'navigation' => array(), // @TODO: Move to conf
            ),
            $globals
        );
    }
}
