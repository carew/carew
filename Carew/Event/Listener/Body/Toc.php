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

    public function __construct(HtmlHelpers $htmlTools = null)
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

        // hack to keep twig statements in local $urls variable because DOMDocument encode attributes value
        $body = preg_replace_callback('/(?P<attr>href|src)="(?P<url>[^"]*)"/', function ($matches) use (&$urls) {
            $urls[] = $matches['url'];

            return sprintf('%s="%s"', $matches['attr'], '%%%%%%%%%%%%%%%%%%%%');
        }, $document->getBody());

        try {
            $level = error_reporting(0);
            $document->setToc($this->htmlTools->buildTOC($document->getBody()));
            $body = $this->htmlTools->addHeadingsId($body, 'h1, h2, h3, h4, h5, h6', true);
            error_reporting($level);
        } catch (\Exception $e) {
            // TODO: add a message when failing.
            return;
        }

        // restore url value in href and src attribute
        $i = 0;
        $body = preg_replace_callback('/(?P<attr>href|src)="(?P<url>%%%%%%%%%%%%%%%%%%%%)"/', function ($matches) use (&$i, $urls) {
            return sprintf('%s="%s"', $matches['attr'], $urls[$i++]);
        }, $body);

        // Decode only encoded UTF-8 chars
        $document->setBody(preg_replace_callback("/(&#[0-9]+;)/", function ($found) {
            return html_entity_decode($found[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
        }, $body));
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
