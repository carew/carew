<?php

namespace Carew\EventSubscriber\Body;

use Carew\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UrlRewriter implements EventSubscriberInterface
{
    public function process($event)
    {
        $subject = $event->getSubject();

        $subject->setBody(strtr($subject->getBody(), array(
            '{{ relativeRoot }}' => $subject->getRootPath(),
        )));
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT => array(
                array('process', 0),
            ),
        );
    }
}
