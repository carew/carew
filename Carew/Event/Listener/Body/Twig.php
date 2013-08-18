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
        foreach ($documents as $k => $document) {
            if (false === $document->getLayout()) {
                continue;
            }

            $this->setTwigGlobals($event, $document);

            // Force autoloading of Twig_Extension_StringLoader
            $this->twig->getExtension('string_loader');

            $template = twig_template_from_string($this->twig, $document->getBody());
            $nbItems = $template->getNbItems(array());
            if ($template->getMaxPerPage() >= $nbItems) {
                $document->setBody($template->render(array()));

                continue;
            }

            unset($documents[$k]);

            $nbPages = ceil($nbItems / $template->getMaxPerPage());
            $pagesAsDocument = array();
            for ($page = 1; $page <= $nbPages; $page++) {
                $pageAsDocument = clone $document;

                if (1 < $page) {
                    $pathInfo = pathinfo($pageAsDocument->getPath());
                    $pathInfo['filename'] = sprintf('%s-page-%d', $pathInfo['filename'], $page);
                    $pageAsDocument->setPath(ltrim(sprintf('%s/%s.%s', $pathInfo['dirname'], $pathInfo['filename'], $pathInfo['extension']), './'));
                }

                $pagesAsDocument[$page] = $pageAsDocument;
            }

            foreach ($pagesAsDocument as $page => $pageAsDocument) {
                $pageAsDocument->setBody($template->render(array(
                    '__offset__' => ($page - 1) * $template->getMaxPerPage(),
                    '__pages__' => $pagesAsDocument,
                    '__current_page__' => $page,
                )));
                $documents[] = $pageAsDocument;
            }
        }

        $event->setSubject($documents);
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
