<?php

namespace Carew\Event\Listener\Body;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use HtmlTools\Helpers as HtmlHelpers;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Toc implements EventSubscriberInterface
{
    private $htmlTools;

    public function __construct(HtmlTools $htmlTools = null)
    {
        $this->htmlTools = $htmlTools ?: new HtmlHelpers();
    }

    public function onDocument(CarewEvent $event)
    {
        $documents = $event->getSubject();

        foreach ($documents as $document) {
            if (Document::TYPE_API == $document->getType()) {
                continue;
            }
            $extension = pathinfo($document->getPath(), PATHINFO_EXTENSION);
            if ('html' !== $extension) {
                continue;
            }
            $document->setToc($this->htmlTools->buildTOC($document->getBody()));
            $document->setBody($this->htmlTools->addHeadingsId($document->getBody(), 'h1, h2, h3, h4, h5, h6', true));
        }

        $event->setSubject($documents);
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT_BODY => array(
                array('onDocument', 32),
            ),
        );
    }
}
