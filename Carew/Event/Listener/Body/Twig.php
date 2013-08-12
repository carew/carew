<?php

namespace Carew\Event\Listener\Body;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Twig implements EventSubscriberInterface
{
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function preRender(CarewEvent $event)
    {
        $documents = $event->getSubject();
        $documentsTmp = array();
        foreach ($documents as $k => $document) {
            $documentsTmp[] = $document;
            if (false === $document->getLayout()) {
                continue;
            }

            $this->setTwigGlobals($event, $document);

            // Force autoloading of Twig_Extension_StringLoader
            $stringLoader = $this->twig->getExtension('string_loader');

            $template = twig_template_from_string($this->twig, $document->getBody());
            $nbItems = $template->getNbItems(array());
            if ($template->getMaxPerPage() >= $nbItems) {
                $document->setBody($template->render(array()));

                continue;
            }

            unset($documentsTmp[$k]);

            $nbPages = ceil($nbItems / $template->getMaxPerPage());

            for ($i = 1; $i <= $nbPages; $i++) {
                $documentTmp = clone $document;

                if (1 < $i) {
                    $pathInfo = pathinfo($documentTmp->getPath());
                    $pathInfo['filename'] = sprintf('%s-page-%d', $pathInfo['filename'], $i);
                    $documentTmp->setPath(sprintf('%s/%s.%s', $pathInfo['dirname'], $pathInfo['filename'], $pathInfo['extension']));
                }

                $documentTmp->setBody($template->render(array(
                    '__offset__' => ($i - 1) * $template->getMaxPerPage(),
                )));
                $documentsTmp[] = $documentTmp;
            }
        }

        $event->setSubject($documentsTmp);
    }

    public function postRender(CarewEvent $event)
    {
        $documents = $event->getSubject();

        foreach ($documents as $document) {
            if (false === $document->getLayout()) {
                continue;
            }

            $this->setTwigGlobals($event, $document);

            $layout = $document->getLayout();
            if (false === strpos($layout, '.twig')) {
                $layout .= '.html.twig';
            }

            $document->setBody($this->twig->render($layout));
        }

        $event->setSubject($documents);
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT_BODY => array(
                array('preRender', 2),
                array('postRender', 0),
            ),
        );
    }

    private function setTwigGlobals(CarewEvent $event, Document $document)
    {
        $twigGlobals = $this->twig->getGlobals();
        $globals = $twigGlobals['carew'];

        $globals->fromArray($document->getVars());

        $globals->relativeRoot = $document->getRootPath();
        $globals->currentPath = $document->getPath();
        $globals->document = $document;

        return $this;
    }
}
