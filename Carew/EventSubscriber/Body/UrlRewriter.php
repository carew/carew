<?php

namespace Carew\EventSubscriber\Body;

use Carew\EventSubscriber\EventSubscriber;

class UrlRewriter extends EventSubscriber
{

    public function onPageProcess($event)
    {
        $this->onProcess($event);
    }

    public function onPostProcess($event)
    {
        $this->onProcess($event);
    }

    public function onApiProcess($event)
    {
        $this->onProcess($event);
    }

    public function onProcess($event)
    {
        $subject = $event->getSubject();

        $subject->setBody(strtr($subject->getBody(), array(
            '{{ relativeRoot }}' => $subject->getRootPath(),
        )));
    }

    public static function getPriority()
    {
        return 0;
    }
}
