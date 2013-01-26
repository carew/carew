<?php

namespace Carew\EventSubscriber\Metadata;

use Carew\EventSubscriber\EventSubscriber;

class Permalink extends EventSubscriber
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
        $metadatas = $event->getSubject()->getMetadatas();
        if (isset($metadatas['permalink'])) {
            $event->getSubject()->setPath(trim($metadatas['permalink'], '/').'.html');
        }
    }

    public static function getPriority()
    {
        return 2;
    }
}
