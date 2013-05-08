<?php

namespace Carew\Event\Listener\Body;

use Carew\Event\Events;
use Carew\Event\CarewEvent;
use Michelf\MarkdownExtra as MarkdownParser;
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
        $subject = $event->getSubject();

        $extension = $subject->getFile()->getExtension();
        if ('md' !== $extension) {
            if ('twig' !== $extension) {
                return;
            }

            $extension = pathinfo(str_replace('.twig', '', $subject->getFilePath()), PATHINFO_EXTENSION);
            if ('md' !== $extension) {
                return;
            }
        }

        $subject->setBody($this->markdownParser->transform($subject->getBody()));
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
