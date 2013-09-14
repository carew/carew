<?php

namespace Carew;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Carew\Twig\Globals;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

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

    public function processFile(SplFileInfo $file, $folder = '', $type = Document::TYPE_UNKNOWN)
    {
        $internalPath = trim($folder.'/'.$file->getRelativePathname(), '/');
        $document = new Document($file, $internalPath, $type);

        $event = new CarewEvent($document);

        try {
            return $this->eventDispatcher->dispatch(Events::DOCUMENT_HEADER, $event)->getSubject();
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('Could not process: "%s".', (string) $file), 0 , $e);
        }
    }

    public function processDocuments($documents, $globals)
    {
        $event = new CarewEvent($documents, array('globals' => $globals));

        return $this->eventDispatcher->dispatch(Events::DOCUMENTS, $event)->getSubject();
    }

    public function processGlobals($documents, Globals $globals = null)
    {
        $globals = $globals ?: new Globals();

        $globalsData = $this->buildCollectionsWithType($documents);
        $globalsData['documents'] = $documents;

        foreach (array('tags', 'navigations') as $key) {
            $globalsData[$key] = $this->buildCollectionWithDocumentMethod($documents, 'get'.ucfirst($key));
        }

        return $globals->fromArray($globalsData);
    }

    public function processDocument(Document $document)
    {
        $event = new CarewEvent($document);
        try {
            return $this->eventDispatcher->dispatch(Events::DOCUMENT_BODY, $event)->getSubject();
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('Could not process: "%s".', (string) $document->getFile()), 0 , $e);
        }
    }

    public function processDocumentDecoration(Document $document)
    {
        $event = new CarewEvent(array($document));
        try {
            return $this->eventDispatcher->dispatch(Events::DOCUMENT_DECORATION, $event)->getSubject();
        } catch (\Exception $e) {
            throw new \LogicException(sprintf('Could not write: "%s".', (string) $document->getFile()), 0 , $e);
        }
    }

    public function write(Document $document)
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

    private function buildCollectionWithDocumentMethod($documents, $method)
    {
        $collection = array();
        foreach ($documents as $document) {
            $items = (array) $document->{$method}();
            foreach ($items as $item) {
                if (!array_key_exists($item, $collection)) {
                    $collection[$item] = array();
                }
                $collection[$item][$document->getFilePath()] = $document;
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
}
