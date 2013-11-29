<?php

namespace Carew\Tests\Event\Listener\Documents;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Documents\Tags;
use Carew\Processor;
use Carew\Twig\Globals;

class TagTest extends \PHPUnit_Framework_TestCase
{
    public function testOnDocuments()
    {
        $tags = new Tags();

        $documents = array();
        for ($i = 1; $i <= 20; $i++) {
            $document = new Document();
            $document->setTitle('Post #'.$i);
            $document->setTags('Tag #'.$i % 5);
            $documents[] = $document;
        }

        $processor = new Processor();
        $globals = $processor->processGlobals($documents);

        $event = new CarewEvent($documents, array('globals' => $globals));
        $tags->onDocuments($event);

        $documents = $event->getSubject();
        // 20 post, 1 page for all tags, 5 tags
        $this->assertCount(26, $documents);
    }
}
