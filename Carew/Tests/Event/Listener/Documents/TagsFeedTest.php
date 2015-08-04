<?php

namespace Carew\Tests\Event\Listener\Documents;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Documents\TagsFeed;
use Carew\Processor;

class TagsFeedTest extends \PHPUnit_Framework_TestCase
{
    public function testOnDocuments()
    {
        $documents = array();

        for ($i = 1; $i <= 20; ++$i) {
            $document = new Document(null, null, Document::TYPE_POST);
            $document->setTitle('Post #'.$i);
            $document->setTags('Tag #'.$i % 5);
            $documents[] = $document;
        }

        for ($i = 1; $i <= 20; ++$i) {
            $document = new Document(null, null, Document::TYPE_PAGE);
            $document->setTitle('Post #'.$i);
            $document->setTags('Tag #'.$i % 5);
            $documents[] = $document;
        }

        $processor = new Processor();
        $globals = $processor->processGlobals($documents);

        $event = new CarewEvent($documents, array('globals' => $globals));

        $tagFeed = new TagsFeed();
        $tagFeed->onDocuments($event);

        $documents = $event->getSubject();

        // 20 posts, 20 pages, 5 tags index
        $this->assertCount(45, $documents);

        $this->assertSame('tags/tag-1/feed/atom.xml', $documents['tags/Tag #1/feed/atom']->getPath());
    }
}
