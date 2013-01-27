<?php

namespace Carew\Event\Listener\Metadata;

use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;

class Extraction implements EventSubscriberInterface
{
    public function onDocumentProcess($event)
    {
        $document = $event->getSubject();
        $file = $document->getFile();

        $document->setTitle($file->getBasename('.'.$file->getExtension()));
        $document->setPath(ltrim(sprintf('%s/%s.html', $file->getRelativePath(), $document->getTitle()), '/'));

        $content = file_get_contents($file);
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

            if (isset($metadatas['permalink'])) {
                $document->setPath(trim($metadatas['permalink'], '/').'.html');
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

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT => array(
                array('onDocumentProcess', 1024),
            ),
        );
    }
}
