<?php

namespace Carew\EventSubscriber\Metadata;

use Carew\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Permalink implements EventSubscriberInterface
{
    public function process($event)
    {
        $metadatas = $event->getSubject()->getMetadatas();
        if (isset($metadatas['permalink'])) {
            $event->getSubject()->setPath(trim($metadatas['permalink'], '/').'.html');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::PAGE => array(
                array('process', 0),
            ),
            Events::POST => array(
                array('process', 0),
            ),
            Events::API => array(
                array('process', 0),
            ),
        );
    }
}
