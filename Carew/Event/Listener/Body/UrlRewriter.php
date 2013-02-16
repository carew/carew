<?php

namespace Carew\Event\Listener\Body;

use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UrlRewriter implements EventSubscriberInterface
{
    public function onDocument(CarewEvent $event)
    {
        $subject = $event->getSubject();

        $subject->setBody(preg_replace('/{{\s*relativeRoot\s*}}/mU', $subject->getRootPath(), $subject->getBody()));
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT => array(
                array('onDocument', 960),
            ),
        );
    }
}
