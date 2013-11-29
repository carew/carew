<?php

namespace Carew\Event\Listener\Metadata;

use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Carew\Helper\Path;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;

class Extraction implements EventSubscriberInterface
{
    private $path;

    public function __construct(Path $path = null)
    {
        $this->path = $path ?: new Path();
    }

    public function onDocument(CarewEvent $event)
    {
        $document = $event->getSubject();
        $file = $document->getFile();

        $document->setPath($this->path->generatePath($file->getRelativePathName()));

        preg_match('#^---\n(.+)---\n(.+)$#sU', $document->getBody(), $matches);
        if ($matches) {
            list(, $metadatas, $body) = $matches;

            $metadatas = Yaml::parse($metadatas);

            $document->setLayout('default');
            foreach ($metadatas as $key => $value) {
                $method = 'set'.ucfirst($key);
                if (method_exists($document, $method)) {
                    $document->{$method}($value);
                    unset($metadatas[$key]);
                }
            }

            if (isset($metadatas['permalink'])) {
                $document->setPath($this->path->generatePath($metadatas['permalink']));
            }

            $document->addMetadatas($metadatas);
            $document->setBody($body);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT_HEADER => array(
                array('onDocument', 1024),
            ),
        );
    }
}
