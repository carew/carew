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
        $document = $event->getSubject();

        if (Document::TYPE_API == $document->getType()) {
            return;
        }
        $extension = pathinfo($document->getPath(), PATHINFO_EXTENSION);
        if ('html' !== $extension) {
            return;
        }

        $urls = array();

        $body = preg_replace_callback('/href="(?P<url>.*)"/', function($matches) use (&$urls) {
            $urls[] = $matches['url'];

            return sprintf('href="%s"', '%%%%%%%%%%%%%%%%%%%%');
        }, $document->getBody());

        try {
            $document->setToc($this->htmlTools->buildTOC($document->getBody()));
            $body = $this->htmlTools->addHeadingsId($body, 'h1, h2, h3, h4, h5, h6', true);
        } catch (\Exception $e) {
            // TODO: add a message when failing.
            return;
        }

        $i = 0;
        $body = preg_replace_callback('/href="(?P<url>%%%%%%%%%%%%%%%%%%%%)"/', function($matches) use (&$i, $urls) {
            return sprintf('href="%s"', $urls[$i++]);
        }, $body);

        $document->setBody($body);
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
