<?php

namespace Carew\Event\Listener\Metadata;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Carew\Helper\Path;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Optimization implements EventSubscriberInterface
{
    private $permalinkFormat;
    private $path;

    public function __construct($permalinkFormat = '%year%/%month%/%day%/%slug%.html', Path $path = null)
    {
        $this->permalinkFormat = $permalinkFormat;
        $this->path = $path ?: new Path();
    }

    public function onDocument(CarewEvent $event)
    {
        $document = $event->getSubject();

        if (Document::TYPE_POST == $document->getType()) {
            $this->onPost($document);
        } elseif (Document::TYPE_API == $document->getType()) {
            $this->onApi($document);
        }
    }

    private function onPost(Document $document)
    {
        list($year, $month, $day, $slug) = explode('-', $document->getFile()->getBasename('.md'), 4);

        $document->addMetadatas(array(
            'date' => new \DateTime("$year-$month-$day"),
        ));

        $metadatas = $document->getMetadatas();

        if (!isset($metadatas['permalink'])) {
            $path = strtr($this->permalinkFormat, array(
                '%year%' => $year,
                '%month%' => $month,
                '%day%' => $day,
                '%slug%' => $slug,
            ));
            $document->setPath($this->path->generatePath($path));
        }
    }

    private function onApi(Document $document)
    {
        $document->setPath(sprintf('api/%s', $document->getPath()));
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT_HEADER => array(
                array('onDocument', 992),
            ),
        );
    }
}
