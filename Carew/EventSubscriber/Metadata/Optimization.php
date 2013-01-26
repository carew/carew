<?php

namespace Carew\EventSubscriber\Metadata;

use Carew\EventSubscriber\EventSubscriber;

class Optimization extends EventSubscriber
{
    public function onPostProcess($event)
    {
        $subject  = $event->getSubject();

        list($year, $month, $day, $slug) = explode('-', $subject->getFile()->getBasename('.md'), 4);

        $subject->setMetadatas(array(
            'date' => new \DateTime("$year-$month-$day"),
        ));

        $subject->setPath("$year/$month/$day/$slug.html");
    }

    public function onApiProcess($event)
    {
        $subject = $event->getSubject();

        $subject->setPath(preg_replace('/(.html)$/', '', sprintf('api/%s', $subject->getPath())));
    }

    public static function getPriority()
    {
        return 1024;
    }

}
