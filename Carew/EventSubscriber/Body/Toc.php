<?php

namespace Carew\EventSubscriber\Body;

use Carew\EventSubscriber\EventSubscriber;

class Toc extends EventSubscriber
{
    public function onPageProcess($event)
    {
        $this->buildToc($event);
    }

    public function onPostProcess($event)
    {
        $this->buildToc($event);
    }

    public function buildToc($event)
    {
        $subject = $event->getSubject();
        $body = $subject->getBody();

        if (preg_match('/^(\s|\n)+$/', $body)) {
            return;
        }

        $dom = new \DOMDocument();
        $dom->loadHtml($body);
        $xpath = new \DOMXPath($dom);

        $toc = array();
        $ids = array();

        $isSpan = function ($node) {
            return XML_ELEMENT_NODE === $node->nodeType && 'span' === $node->tagName;
        };

        $genId = function ($node) use (&$ids, $isSpan) {
            $count = 0;
            do {
                if ($isSpan($node->lastChild)) {
                    $node = clone $node;
                    $node->removeChild($node->lastChild);
                }

                $id = preg_replace('{[^a-z0-9]}i', '-', strtolower(trim($node->nodeValue)));
                $id = preg_replace('{-+}', '-', $id);
                if ($count) {
                    $id .= '-'.($count+1);
                }
                $count++;
            } while (isset($ids[$id]));
            $ids[$id] = true;

            return $id;
        };

        $getDesc = function ($node) use ($isSpan) {
            if ($isSpan($node->lastChild)) {
                return $node->lastChild->nodeValue;
            }

            return null;
        };

        $getTitle = function ($node) use ($isSpan) {
            if ($isSpan($node->lastChild)) {
                $node = clone $node;
                $node->removeChild($node->lastChild);
            }

            return $node->nodeValue;
        };

        // build TOC & deep links
        $h1 = $h2 = $h3 = $h4 = 0;
        $nodes = $xpath->query('//*[self::h1 or self::h2 or self::h3 or self::h4]');
        foreach ($nodes as $node) {
            // set id and add anchor link
            $id = $genId($node);
            $title = $getTitle($node);

            $desc = $getDesc($node);
            $node->setAttribute('id', $id);
            $link = $dom->createElement('a', '#');
            $link->setAttribute('href', '#'.$id);
            $link->setAttribute('class', 'anchor');
            $node->appendChild($link);

            // parse into a tree
            switch ($node->nodeName) {
                case 'h1':
                    $toc[++$h1] = array('title' => $title, 'id' => $id, 'desc' => $desc);
                break;

                case 'h2':
                    $toc[$h1][++$h2] = array('title' => $title, 'id' => $id, 'desc' => $desc);
                break;

                case 'h3':
                    $toc[$h1][$h2][++$h3] = array('title' => $title, 'id' => $id, 'desc' => $desc);
                break;

                case 'h4':
                    $toc[$h1][$h2][$h3][++$h4] = array('title' => $title, 'id' => $id, 'desc' => $desc);
                break;
            }
        }

        // save new body with IDs
        $body = $dom->saveHtml();
        $body = preg_replace('{.*<body>(.*)</body>.*}is', '$1', $body);

        $subject->setToc($toc);
        $subject->setBody($body);
    }

    public static function getPriority()
    {
        return 128;
    }
}
