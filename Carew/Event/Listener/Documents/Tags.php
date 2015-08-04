<?php

namespace Carew\Event\Listener\Documents;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use HtmlTools\Inflector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Tags implements EventSubscriberInterface
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

        $tags = array();

        foreach ($globals->tags as $tagName => $documentList) {
            $tagDocument = new Document();
            $tagDocument
                ->setLayout('default.html.twig')
                ->setBody('{{ render_documents(carew.documents) }}')
                ->setPath(sprintf('tags/%s.html', $this->inflector->urlize($tagName)))
                ->setTitle('Tag #'.$tagName)
                ->setFilePath('tags/'.$tagName)
                ->setNavigations(array('tags', 'sub-tags'))
                ->setVar('documents', $documentList);
            $documents[$tagDocument->getFilePath()] = $tagDocument;
            $tags[$tagDocument->getFilePath()] = $tagDocument;
        }

        $tagsDocument = new Document();
        $tagsDocument
            ->setLayout('default.html.twig')
            ->setBody('{{ render_documents(carew.tags) }}')
            ->setPath('tags/index.html')
            ->setTitle('Tags')
            ->setFilePath('tags')
            ->setNavigations('tags')
            ->setVar('tags', $tags);
        $documents[$tagsDocument->getFilePath()] = $tagsDocument;

        $globals->documents = array_replace($globals->documents, $documents);

        $event->setSubject($documents);
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENTS => 'onDocuments',
        );
    }
}
