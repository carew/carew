<?php

namespace Carew\Event\Listener\Body;

use Carew\Event\Events;
use Carew\Event\CarewEvent;
use Parsedown as MarkdownParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Markdown implements EventSubscriberInterface
{
    private $markdownParser;

    public function __construct($markdownParser = null)
    {
        $this->markdownParser = $markdownParser ?: new MarkdownParser();
    }

    public function onDocument(CarewEvent $event)
    {
        $document = $event->getSubject();

        if (null === $document->getFile()) {
            return;
        }

        $extension = $document->getFile()->getExtension();
        if ('md' !== $extension) {
            if ('twig' !== $extension) {
                return;
            }

            $extension = pathinfo(str_replace('.twig', '', $document->getFilePath()), PATHINFO_EXTENSION);
            if ('md' !== $extension) {
                return;
            }
        }

        $document->setBody($this->markdownParser->parse($document->getBody()));
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT_BODY => array(
                array('onDocument', 512),
            ),
        );
    }
}
