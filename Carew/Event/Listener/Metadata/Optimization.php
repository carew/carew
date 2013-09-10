<?php

namespace Carew\Event\Listener\Metadata;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Optimization implements EventSubscriberInterface
{
    protected $postPathFormat;

    public function __construct($postPathFormat)
    {
        $this->postPathFormat = $postPathFormat;
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
            $document->setPath(str_replace(
                array('%year%', '%month%', '%day%', '%slug%'),
                array($year, $month, $day, $slug),
                $this->postPathFormat
            ));
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
