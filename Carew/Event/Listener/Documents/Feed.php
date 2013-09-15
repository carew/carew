<?php

namespace Carew\Event\Listener\Documents;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Feed implements EventSubscriberInterface
{
    public function onDocuments(CarewEvent $event)
    {
        $document = new Document();
        $document
            ->setLayout('index.atom.twig')
            ->setPath('feed/atom.xml')
            ->setFilePath('feed/atom')
        ;

        $documents = $event->getSubject();
        $documents[$document->getFilePath()] = $document;
        $event->setSubject($documents);

        $globals = $event['globals'];
        $globals->documents = array_replace($globals->documents, $documents);
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENTS => array(
                array('onDocuments'),
            ),
        );
    }
}
