<?php

namespace Carew\Event\Listener\Body;

use Carew\Event\Events;
use Carew\Event\CarewEvent;
use dflydev\markdown\MarkdownExtraParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Markdown implements EventSubscriberInterface
{
    private $markdownParser;

    public function __construct($markdownParser = null)
    {
        $this->markdownParser = $markdownParser ?: new MarkdownExtraParser();
    }

    public function onDocument(CarewEvent $event)
    {
        $subject = $event->getSubject();

        if ('md' !== $subject->getFile()->getExtension()) {
            return;
        }

        $subject->setBody($this->markdownParser->transformMarkdown($subject->getBody()));
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
