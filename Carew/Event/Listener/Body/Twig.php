<?php

namespace Carew\Event\Listener\Body;

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
        $document = $event->getSubject();

        if (false === $document->getLayout()) {
            return;
        }

        $this->setTwigGlobals($event);

        $document->setBody($this->twig->render('pre_render_template.html.twig', array('body' => $document->getBody())));
    }

    public function postRender(CarewEvent $event)
    {
        $document = $event->getSubject();

        if (false === $document->getLayout()) {
            return;
        }

        $this->setTwigGlobals($event);

        $layout = $document->getLayout();
        if (false === strpos($layout, '.twig')) {
            $layout .= '.html.twig';
        }
        $document->setBody($this->twig->render($layout));
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

    private function setTwigGlobals($event)
    {
        $globals = $event->hasArgument('globalVars') ? $event->getArgument('globalVars') : array();

        $document = $event->getSubject();
        $globals['relativeRoot'] = $document->getRootPath();
        $globals['currentPath'] = $document->getPath();
        $globals['document'] = $document;

        $globals = array_replace($document->getVars(), $globals);

        $current = $this->twig->getGlobals();
        $this->twig->addGlobal('carew', $current['carew']->fromArray($globals));

        return $this;
    }
}
