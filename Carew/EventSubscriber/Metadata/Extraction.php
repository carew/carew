<?php

namespace Carew\EventSubscriber\Metadata;

use Carew\EventSubscriber\EventSubscriber;
use Symfony\Component\Yaml\Yaml;

class Extraction extends EventSubscriber
{
    public function onDocumentProcess($event)
    {
        $document = $event->getSubject();

        $document->setTitle($document->getFile()->getBasename('.md'));

        $document->setPath(ltrim(sprintf('%s/%s.html', $document->getFile()->getRelativePath(), $document->getFile()->getBasename('.md')), '/'));

        $content = file_get_contents($document->getFile());
        preg_match('#^---\n(.+)---\n(.+)$#sU', $content, $matches);
        if ($matches) {
            list(, $metadatas, $body) = $matches;

            $metadatas = Yaml::parse($metadatas);
            foreach (array('title', 'layout') as $key) {
                if (isset($metadatas[$key])) {
                    $method = 'set'.ucfirst($key);
                    $document->{$method}($metadatas[$key]);
                    unset($metadatas[$key]);
                }
            }
            foreach (array('tags', 'navigation') as $value) {
                if (isset($metadatas[$value]) && !is_array($metadatas[$value])) {
                    $metadatas[$value] = array($metadatas[$value]);
                }
            }
            $document->setMetadatas($metadatas);

            $document->setBody($body);
        } else {
            if (!$event['allowEmptyHeader']) {
                throw new \RuntimeException('Could not parse front matter');
            }

            $document->setLayout(false);
            $document->setBody($content);
        }
    }

    public static function getPriority()
    {
        return 1024;
    }
}
