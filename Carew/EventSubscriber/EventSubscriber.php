<?php

namespace Carew\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Carew\Event\Events;

class EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT => array(
                array('onDocumentProcess', static::getPriority()),
            ),
            Events::PAGE => array(
                array('onPageProcess', static::getPriority()),
            ),
            Events::POST => array(
                array('onPostProcess', static::getPriority()),
            ),
            Events::API => array(
                array('onApiProcess', static::getPriority()),
            ),
        );
    }

    public function onDocumentProcess($event)
    {
    }

    public function onPageProcess($event)
    {
    }

    public function onPostProcess($event)
    {
    }

    public function onApiProcess($event)
    {
    }

    public static function getPriority()
    {
        return 0;
    }
}
