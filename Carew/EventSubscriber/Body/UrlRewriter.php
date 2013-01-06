<?php

namespace Carew\EventSubscriber\Body;

use Carew\EventSubscriber\EventSubscriber;

class UrlRewriter extends EventSubscriber
{

    public function onDocumentProcess($event)
    {
        $subject = $event->getSubject();

        $subject->setBody(strtr($subject->getBody(), array(
            '{{ relativeRoot }}' => $subject->getRootPath(),
        )));
    }

    public static function getPriority()
    {
        return 400;
    }
}
