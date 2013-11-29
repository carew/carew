<?php

namespace Carew\Tests;

use Carew\Document;
use Carew\Event\Events;
use Carew\Processor;
use Carew\Twig\Globals;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\SplFileInfo;

class ProcessorTest extends AbstractTest
{
    private $processor;
    private $eventDispatcher;

    public function setUp()
    {
        $this->eventDispatcher = new EventDispatcher();
        $this->processor = new Processor($this->eventDispatcher);
    }

    public function testProcessFile()
    {
        $file = new SplFileInfo(__FILE__, '', basename(__FILE__));

        $i = 0;
        $this->eventDispatcher->addListener(Events::DOCUMENT_HEADER, function () use (&$i) { $i++; });

        $document = $this->processor->processFile($file, 'simulate-a-path');

        $this->assertSame(1, $i);

        $this->assertSame('ProcessorTest.php', $document->getPath());
        $this->assertSame('simulate-a-path/ProcessorTest.php', $document->getFilePath());
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Could not process "ProcessorTest.php": "Exception message".
     */
    public function testProcessFileWithException()
    {
        $file = new SplFileInfo(__FILE__, '', basename(__FILE__));

        $this->eventDispatcher->addListener(Events::DOCUMENT_HEADER, function () {
            throw new \Exception('Exception message');
        });

        $document = $this->processor->processFile($file);
    }

    public function testProcessDocuments()
    {
        $documents = array(
            new Document(),
            new Document(),
        );

        $i = 0;

        $this->eventDispatcher->addListener(Events::DOCUMENTS, function () use (&$i) { $i++; });

        $documents = $this->processor->processDocuments($documents, new Globals());

        $this->assertSame(1, $i);
        $this->assertCount(2, $documents);
    }

    public function testProcessGlobals()
    {
        $documents = array(
            new Document(),
            new Document(),
            new Document(),
            new Document(null, null, Document::TYPE_POST),
            new Document(null, null, Document::TYPE_POST),
            new Document(null, null, Document::TYPE_POST),
            new Document(null, null, Document::TYPE_POST),
            new Document(null, null, Document::TYPE_POST),
            new Document(null, null, Document::TYPE_POST),
        );
        $documents[0]->setFilePath('a');
        $documents[0]->setTags('tag1');
        $documents[1]->setFilePath('b');
        $documents[1]->setTags('tag2');
        $documents[2]->setFilePath('c');
        $documents[2]->setTags('tag2');
        $documents[3]->setFilePath('d');
        $documents[3]->setNavigations('nav1');
        $documents[3]->setMetadata('date', new \DateTime('2000-01-01'));
        $documents[4]->setFilePath('e');
        $documents[4]->setNavigations('nav1');
        $documents[4]->setMetadata('date', new \DateTime('2000-01-10'));
        $documents[5]->setFilePath('f');
        $documents[6]->setFilePath('g');
        $documents[6]->setMetadata('date', new \DateTime('2000-01-10'));
        $documents[7]->setFilePath('h');
        $documents[8]->setFilePath('i');
        $documents[8]->setMetadata('date', new \DateTime('2000-01-01'));

        $globalVars = $this->processor->processGlobals($documents);

        $this->assertCount(9, $globalVars->documents);
        $this->assertSame($documents, $globalVars->documents);

        $this->assertCount(2, $globalVars->tags);
        $this->assertCount(1, $globalVars->tags['tag1']);
        $this->assertContains($documents[0], $globalVars->tags['tag1']);
        $this->assertCount(2, $globalVars->tags['tag2']);
        $this->assertContains($documents[1], $globalVars->tags['tag2']);
        $this->assertContains($documents[2], $globalVars->tags['tag2']);

        $this->assertCount(1, $globalVars->navigations);
        $this->assertCount(2, $globalVars->navigations['nav1']);
        $this->assertContains($documents[3], $globalVars->navigations['nav1']);
        $this->assertContains($documents[4], $globalVars->navigations['nav1']);

        $this->assertCount(3, $globalVars->unknowns);
        $this->assertContains($documents[0], $globalVars->unknowns);
        $this->assertContains($documents[1], $globalVars->unknowns);
        $this->assertContains($documents[2], $globalVars->unknowns);

        $this->assertCount(6, $globalVars->posts);
        $this->assertContains($documents[3], $globalVars->posts);
        $this->assertContains($documents[4], $globalVars->posts);
        $this->assertContains($documents[5], $globalVars->posts);
        $this->assertContains($documents[6], $globalVars->posts);

        $this->assertSame(array('h', 'f', 'd', 'i', 'g', 'e'), array_keys($globalVars->posts));
    }

    public function testProcessDocument()
    {
        $i = 0;
        $this->eventDispatcher->addListener(Events::DOCUMENT_BODY, function () use (&$i) { $i++; });

        $this->processor->processDocument(new Document());

        $this->assertSame(1, $i);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Could not process "ProcessorTest.php": "Exception message".
     */
    public function testProcessDocumentWithExceptionAndFile()
    {
        $this->eventDispatcher->addListener(Events::DOCUMENT_BODY, function () {
            throw new \Exception('Exception message');
        });

        $this->processor->processDocument(new Document(new SplFileInfo(__FILE__, '', basename(__FILE__))));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Could not process "Body": "Exception message".
     */
    public function testProcessDocumentWithExceptionWithoutFile()
    {
        $this->eventDispatcher->addListener(Events::DOCUMENT_BODY, function () {
            throw new \Exception('Exception message');
        });

        $document = new Document();
        $document->setBody('Body');
        $this->processor->processDocument($document);
    }

    public function testProcessDocumentDecoration()
    {
        $i = 0;
        $this->eventDispatcher->addListener(Events::DOCUMENT_DECORATION, function () use (&$i) { $i++; });

        $this->processor->processDocumentDecoration(new Document());

        $this->assertSame(1, $i);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Could not process "ProcessorTest.php": "Exception message".
     */
    public function testProcessDocumentDecorationWithExceptionAndFile()
    {
        $this->eventDispatcher->addListener(Events::DOCUMENT_DECORATION, function () {
            throw new \Exception('Exception message');
        });

        $this->processor->processDocumentDecoration(new Document(new SplFileInfo(__FILE__, '', basename(__FILE__))));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Could not process "Body": "Exception message".
     */
    public function testProcessDocumentDecorationWithExceptionWithoutFile()
    {
        $this->eventDispatcher->addListener(Events::DOCUMENT_DECORATION, function () {
            throw new \Exception('Exception message');
        });

        $document = new Document();
        $document->setBody('Body');
        $this->processor->processDocumentDecoration($document);
    }

    public function testWrite()
    {
        $tmp = sys_get_temp_dir().'/carew-test';

        static::deleteDir($tmp);

        $document = new Document(new SplFileInfo(__FILE__, '', ''));
        $document->setPath('foo/bar/file.html');

        $this->processor->write($document, $tmp);
        $this->assertFileEquals(__FILE__, $tmp.'/foo/bar/file.html');

        static::deleteDir($tmp);
    }

    public function tearDown()
    {
        $this->eventDispatcher = null;
        $this->processor = null;
    }
}
