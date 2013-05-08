<?php

namespace Carew\Tests;

use Carew\Document;
use Carew\Processor;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\SplFileInfo;

class ProcessorTest extends AbstractTest
{
    private $processor;
    private $eventDispatcher;
    private $target;

    public function setUp()
    {
        $this->target = static::createTempDir();
        $this->eventDispatcher = new EventDispatcher();
        $this->processor = new Processor($this->target, $this->eventDispatcher);
    }

    public function testProcess()
    {
        $file = new SplFileInfo(__FILE__, '', basename(__FILE__));

        $i = 0;
        $this->eventDispatcher->addListener(Events::DOCUMENT_HEADER, function() use (&$i) { $i++; });

        $document = $this->processor->processFile($file, 'simulate-a-path');

        $this->assertSame(1, $i);

        $this->assertSame('ProcessorTest.php', $document->getPath());
        $this->assertSame('simulate-a-path/ProcessorTest.php', $document->getFilePath());
    }

    public function testProcessDocuments()
    {
        $documents = array(
            new Document(),
            new Document(),
            new Document(),
            new Document(null, null, Document::TYPE_POST),
            new Document(null, null, Document::TYPE_POST),
        );
        $documents[0]->addMetadatas(array('tags' => 'tag1'));
        $documents[0]->setFilePath('a');
        $documents[1]->addMetadatas(array('tags' => 'tag2'));
        $documents[1]->setFilePath('b');
        $documents[2]->addMetadatas(array('tags' => 'tag2'));
        $documents[2]->setFilePath('c');
        $documents[3]->addMetadatas(array('navigation' => 'nav1', 'date' => new \DateTime('2000-01-01')));
        $documents[3]->setFilePath('d');
        $documents[4]->addMetadatas(array('navigation' => 'nav1', 'date' => new \DateTime('2000-01-10')));
        $documents[4]->setFilePath('e');

        $i = 0;
        $this->eventDispatcher->addListener(Events::DOCUMENTS, function() use (&$i) { $i++; });

        list($documents, $globalVars) = $this->processor->processDocuments($documents);

        $this->assertSame(1, $i);
        $this->assertCount(5, $documents);
        $this->assertCount(5, $globalVars['documents']);
        $this->assertSame($documents, $globalVars['documents']);

        $this->assertCount(2, $globalVars['tags']);
        $this->assertCount(1, $globalVars['tags']['tag1']);
        $this->assertContains($documents[0], $globalVars['tags']['tag1']);
        $this->assertCount(2, $globalVars['tags']['tag2']);
        $this->assertContains($documents[1], $globalVars['tags']['tag2']);
        $this->assertContains($documents[2], $globalVars['tags']['tag2']);

        $this->assertCount(1, $globalVars['navigation']);
        $this->assertCount(2, $globalVars['navigation']['nav1']);
        $this->assertContains($documents[3], $globalVars['navigation']['nav1']);
        $this->assertContains($documents[4], $globalVars['navigation']['nav1']);

        $this->assertCount(3, $globalVars['unknowns']);
        $this->assertContains($documents[0], $globalVars['unknowns']);
        $this->assertContains($documents[1], $globalVars['unknowns']);
        $this->assertContains($documents[2], $globalVars['unknowns']);
        $this->assertCount(2, $globalVars['posts']);
        $this->assertContains($documents[3], $globalVars['posts']);
        $this->assertContains($documents[4], $globalVars['posts']);

        $this->assertSame($documents[3], reset($globalVars['posts']));
        $this->assertSame($documents[4], end($globalVars['posts']));
    }

    public function testProcessBody()
    {
        $i = 0;
        $this->eventDispatcher->addListener(Events::DOCUMENT_BODY, function() use (&$i) { $i++; });

        $this->processor->processDocument(new Document());

        $this->assertSame(1, $i);
    }

    public function tearDown()
    {
        $this->processor = null;
        static::deleteDir($this->target);
        $this->target = null;
    }
}
