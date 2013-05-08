<?php

namespace Carew\Event\Listener\Metadata;

use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;

class Extraction implements EventSubscriberInterface
{
    private $extensionsToRewrite = array(
        'md',
        'rst',
    );

    public function onDocument(CarewEvent $event)
    {
        $document = $event->getSubject();
        $file = $document->getFile();

        $document->setPath($this->extractPath($file->getRelativePathName()));

        preg_match('#^---\n(.+)---\n(.+)$#sU', $document->getBody(), $matches);
        if ($matches) {
            list(, $metadatas, $body) = $matches;

            $metadatas = Yaml::parse($metadatas);

            $document->setLayout('default');
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

            $document->addMetadatas($metadatas);
            $document->setBody($body);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::DOCUMENT_HEADER => array(
                array('onDocument', 1024),
            ),
        );
    }

    private function extractPath($path)
    {
        if ('/' === substr($path, -1)) {
            return ltrim($path, '/').'index.html';
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ('twig' === $extension) {
            $path = substr($path, 0, - (strlen($extension) + 1));
            $extension = pathinfo($path, PATHINFO_EXTENSION);
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
