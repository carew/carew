<?php

namespace Carew\Event\Listener\Documents;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use HtmlTools\Inflector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TagsFeed implements EventSubscriberInterface
{
    private $inflector;

    public function __construct(Inflector $inflector = null)
    {
        $this->inflector = $inflector ?: new Inflector();
    }

    public function onDocuments(CarewEvent $event)
    {
        $documents = $event->getSubject();
        $globals = $event['globals'];

        foreach ($globals->tags as $tagName => $documentList) {
            $documentList = array_filter($documentList, function (Document $document) {
                return $document->isTypePost() ;
            });

            $document = new Document();
            $document
                ->setLayout('index.atom.twig')
                ->setPath(sprintf('tags/%s/feed/atom.xml', $this->inflector->urlize($tagName)))
                ->setFilePath(sprintf('tags/%s/feed/atom', $tagName))
                ->setVar('post', $documentList)
            ;

            $documents[$document->getFilePath()] = $document;
        }

        $event->setSubject($documents);

        $globals->documents = array_replace($globals->documents, $documents);
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENTS => 'onDocuments',
        );
    }
}
