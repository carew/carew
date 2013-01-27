<?php

namespace Carew\Event\Listener\Metadata;

use Carew\Event\Events;;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Optimization implements EventSubscriberInterface
{
    public function onPostProcess($event)
    {
        $subject  = $event->getSubject();

        list($year, $month, $day, $slug) = explode('-', $subject->getFile()->getBasename('.md'), 4);

        $subject->setMetadatas(array(
            'date' => new \DateTime("$year-$month-$day"),
        ));

        $metadatas = $subject->getMetadatas();

        if (!isset($metadatas['permalink'])) {
            $subject->setPath("$year/$month/$day/$slug.html");
        }
    }

    public function onApiProcess($event)
    {
        $subject = $event->getSubject();

        $subject->setPath(sprintf('api/%s', $subject->getPath()));
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::POST => array(
                array('onPostProcess', 1024),
            ),
            Events::API => array(
                array('onApiProcess', 1024),
            ),
        );
    }
}
