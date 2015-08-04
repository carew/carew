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

        $twig = array();

        // hack to keep twig statements in local $twig variable because MD parser does not work with that
        $body = preg_replace_callback('/(?<twig>{{\s*[^}}]*\s*}})/', function ($matches) use (&$twig) {
            $twig[] = $matches['twig'];

            return 'http://%%%%%%%%%%%%%%%%%%%%';
        }, $document->getBody());

        $body = $this->markdownParser->text($body);

        $i = 0;
        $body = preg_replace_callback('/(http:\/\/%%%%%%%%%%%%%%%%%%%%)/', function ($matches) use (&$i, $twig) {
            return $twig[$i++];
        }, $body);

        $document->setBody($body);
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
