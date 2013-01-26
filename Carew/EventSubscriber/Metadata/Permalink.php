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
            Events::DOCUMENT => array(
                array('process', 1022),
            ),
        );
    }
}
