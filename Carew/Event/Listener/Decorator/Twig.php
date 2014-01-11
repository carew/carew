<?php

namespace Carew\Event\Listener\Decorator;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\Console\Formatter\OutputFormatter;
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
        foreach ($documents as $document) {
            if (false === $document->getLayout()) {
                continue;
            }

            $this->preRenderDocument($document, $documents);
        }

        $event->setSubject($documents);
    }

    public function preRenderDocument(Document $document, array &$documents = array())
    {
        $this->setTwigGlobals($document);
        // Force autoloading of Twig_Extension_StringLoader
        $this->twig->getExtension('string_loader');

        try {
            $template = twig_template_from_string($this->twig, $document->getBody() ?: '');
        } catch (\Twig_Error $e) {
            throw $this->enhanceTwigException($e, $document);
        }

        $nbsItems = $template->getNbsItems(array());
        $maxesPerPage = $template->getMaxesPerPage();

        if (!$this->haveToPaginate($nbsItems, $maxesPerPage)) {
            $parameters = array();
            foreach ($nbsItems as $key => $nbItems) {
                $parameters[sprintf('__offset_%d__', $key)] = 0;
                $parameters[sprintf('__pages_%d__', $key)] = array($document);
                $parameters[sprintf('__current_page_%d__', $key)] = 1;
            }

            try {
                $document->setBody($template->render($parameters));
            } catch (\Twig_Error $e) {
                throw $this->enhanceTwigException($e, $document, $template);
            }
        }

        $parameters = array();
        $paginations = array();
        foreach ($nbsItems as $key => $nbItems) {
            $nbPages = ceil($nbItems / $maxesPerPage[$key]);

            $paginations[$key] = $this->generatePages($document, $nbPages, 0 == $key, $key);

            $parameters[sprintf('__offset_%d__', $key)] = 0;
            $parameters[sprintf('__pages_%d__', $key)] = $paginations[$key];
            $parameters[sprintf('__current_page_%d__', $key)] = 1;
        }

        try {
            $body = $template->render($parameters);
        } catch (\Twig_Error $e) {
            throw $this->enhanceTwigException($e, $document, $template);
        }

        $document->setBody($body);

        foreach ($paginations as $key => $pages) {
            $parametersTmp = $parameters;
            foreach ($pages as $nbPage => $page) {
                if (1 == $nbPage) {
                    continue;
                }

                $parametersTmp[sprintf('__offset_%d__', $key)] =  ($nbPage - 1) * $maxesPerPage[$key];
                $parametersTmp[sprintf('__current_page_%d__', $key)] =  $nbPage;
                try {
                    $body = $template->render($parametersTmp);
                } catch (\Twig_Error $e) {
                    throw $this->enhanceTwigException($e, $document, $template);
                }
                $page->setBody($body);

                $documents[] = $page;
            }
        }
    }

    public function postRender(CarewEvent $event)
    {
        $documents = $event->getSubject();

        foreach ($documents as $document) {
            if (false === $document->getLayout()) {
                $document->setBodyDecorated($document->getBody());

                continue;
            }

            $this->setTwigGlobals($document);

            $layout = $document->getLayout();
            if (false === strpos($layout, '.twig')) {
                $layout .= '.html.twig';
            }

            $document->setBodyDecorated($this->twig->render($layout));
        }

        $event->setSubject($documents);
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT_DECORATION => array(
                array('preRender', 8),
                array('postRender', 0),
            ),
        );
    }

    private function enhanceTwigException(\Twig_Error $e, Document $document, $template = null)
    {
        if (-1 === $e->getTemplateLine()) {
            return new \RuntimeException($e->getRawMessage());
        }

        $lines = explode(PHP_EOL, $document->getBody());

        if (!$template || $template->getTemplateName() == $e->getTemplateFile())  {
            return new \RuntimeException(OutputFormatter::escape(sprintf(
                '%s near "%s" near line %d.',
                $e->getRawMessage(),
                $lines[$e->getTemplateLine() - 1],
                $e->getTemplateLine()
            )));
        }

        return new \RuntimeException(OutputFormatter::escape(sprintf(
            '%s in a string template line %d.',
            $e->getRawMessage(),
            $e->getTemplateLine()
        )));

    }

    private function setTwigGlobals(Document $document)
    {
        $twigGlobals = $this->twig->getGlobals();
        $globals = $twigGlobals['carew'];

        $globals->fromArray($document->getVars());

        $globals->relativeRoot = $document->getRootPath();
        $globals->currentPath = $document->getPath();
        $globals->document = $document;

        return $this;
    }

    private function haveToPaginate($nbsItems, $maxesPerPage)
    {
        if (!$nbsItems && !$maxesPerPage) {
            return false;
        }

        $nbPages = array(0);
        foreach ($nbsItems as $key => $nbItems) {
            $nbPages[$key] = ceil($nbItems / $maxesPerPage[$key]);
        }

        if (1 == count($nbPages)) {
            return 1 < reset($nbPages);
        }

        $realNbPages = call_user_func_array('max', $nbPages);

        return 1 < $realNbPages;
    }

    private function generatePages(Document $originalDocument, $nbPages, $isFirstPagination = true, $collectionNb = 1)
    {
        $pages = array();
        for ($pageNb = 1; $pageNb <= $nbPages; $pageNb++) {
            $currentPage = $pages[$pageNb] = clone $originalDocument;

            if (1 == $pageNb) {
                continue;
            }

            $pathInfo = pathinfo($currentPage->getPath());
            if ($isFirstPagination) {
                $pathInfo['filename'] = sprintf('%s-page-%d', $pathInfo['filename'], $pageNb);
            } else {
                $pathInfo['filename'] = sprintf('%s-%d-page-%d', $pathInfo['filename'], $collectionNb, $pageNb);
            }
            $currentPage->setPath(ltrim(sprintf('%s/%s.%s', $pathInfo['dirname'], $pathInfo['filename'], $pathInfo['extension']), './'));

        }

        return $pages;
    }
}
