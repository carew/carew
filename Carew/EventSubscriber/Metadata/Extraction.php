<?php

namespace Carew\EventSubscriber\Metadata;

use Carew\EventSubscriber\EventSubscriber;
use Symfony\Component\Yaml\Yaml;

class Extraction extends EventSubscriber
{
    public function onDocumentProcess($event)
    {
        $subject = $event->getSubject();
        $file    = $subject['file'];

        $content = file_get_contents($file);

        preg_match('#^---\n(.+)---\n(.+)$#sU', $content, $matches);

        if (!$matches) {
            if (!$event['allowEmptyHeader']) {
                throw new \RuntimeException('Could not parse front matter in current document');
            }

            $metadata['layout'] = false;
            $body = $content;
        } else {
            list(, $metadata, $body) = $matches;
            $metadata = Yaml::parse($metadata);
        }

        $subject['metadata'] = $metadata;
        $subject['body']     = $body;

        $event->setSubject($subject);
    }

    public static function getPriority()
    {
        return 1024;
    }
}
