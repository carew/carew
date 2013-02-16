<?php

namespace Carew\Event\Listener\Metadata;

use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;

class Extraction implements EventSubscriberInterface
{
    private $extensionsToRewrite = array(
        'html',
        'md',
        'rst',
    );

    public function onDocumentProcess(CarewEvent $event)
    {
        $document = $event->getSubject();
        $file = $document->getFile();

        $document->setPath($this->extractPath($file->getRelativePathName()));

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
                $document->setPath($this->extractPath($metadatas['permalink']));
            }

            $document->setMetadatas($metadatas);
            $document->setBody($body);
        } else {
            if (!$event->hasArgument('allowEmptyHeader') || !$event['allowEmptyHeader']) {
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

    private function extractPath($path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ('/' === substr($path, -1)) {
            return ltrim($path, '/').'index.html';
        }

        if ('' === $extension) {
            return ltrim($path, '/').'.html';
        }

        if (in_array(strtolower($extension), $this->extensionsToRewrite)) {
            $path = substr($path, 0, - (strlen($extension) + 1));

            return ltrim($path, '/').'.html';
        }

        return ltrim($path, '/');
    }
}
