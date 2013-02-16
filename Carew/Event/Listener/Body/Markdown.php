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

        if ('md' !== $subject->getFile()->getExtension()) {
            return;
        }

        $subject->setBody($this->markdownParser->transform($subject->getBody()));
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT => array(
                array('onDocument', 512),
            ),
        );
    }
}
