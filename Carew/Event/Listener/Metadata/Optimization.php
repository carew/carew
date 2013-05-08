<?php

namespace Carew\Event\Listener\Metadata;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Optimization implements EventSubscriberInterface
{
    public function onDocument(CarewEvent $event)
    {
        $document  = $event->getSubject();

        if (Document::TYPE_POST == $document->getType()) {
            $this->onPost($document);
        } elseif (Document::TYPE_API == $document->getType()) {
            $this->onApi($document);
        }
    }

    private function onPost($document)
    {
        list($year, $month, $day, $slug) = explode('-', $document->getFile()->getBasename('.md'), 4);

        $document->addMetadatas(array(
            'date' => new \DateTime("$year-$month-$day"),
        ));

        $metadatas = $document->getMetadatas();

        if (!isset($metadatas['permalink'])) {
            $document->setPath("$year/$month/$day/$slug.html");
        }
    }

    private function onApi($document)
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
