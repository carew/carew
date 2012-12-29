<?php

namespace Carew\EventSubscriber\Metadata;

use Carew\EventSubscriber\EventSubscriber;

class Normalization extends EventSubscriber
{
    public function onDocumentProcess($event)
    {
        $subject = $event->getSubject();
        $metadata = $subject['metadata'];

        foreach (array('tags', 'navigation') as $value) {
            if (isset($metadata[$value]) && !is_array($metadata[$value])) {
                $metadata[$value] = array($metadata[$value]);
            }
        }

        $metadata = array_replace(array(
            'title'      => $subject['file']->getBasename('.md'),
            'navigation' => array(),
            'tags'       => array(),
            'layout'     => 'default',
        ), $metadata);

        $subject = array_replace_recursive(array(
            'metadata' => $metadata,
            'body'     => '',
            'layout'   => $metadata['layout'],
            'path'     => '.',
        ), $subject);

        $event->setSubject($subject);
    }

    public static function getPriority()
    {
        return 512;
    }
}
