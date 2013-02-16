<?php

namespace Carew\Tests\Event\Listener\Metadata\Extraction;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Metadata\Extraction;
use Symfony\Component\Finder\SplFileInfo;

class ExtractionTest extends \PHPUnit_Framework_TestCase
{
    public function getTestOnDocumentWithSimpleFile()
    {
        return array(
            array('simple.html', 'simple.md', ''),
            array('folder/simple.html', 'simple.md', 'folder'),
        );
    }

    /**
     * @dataProvider getTestOnDocumentWithSimpleFile
     */
    public function testOnDocumentWithSimpleFile($expected, $file, $relativePath)
    {
        $document = $this->createDocument($file, $relativePath);
        $event = new CarewEvent($document);

        $extraction = new Extraction();
        $extraction->onDocument($event);

        $this->assertSame('title', $document->getTitle());
        $this->assertSame('body', $document->getBody());
        $this->assertSame('layout', $document->getLayout());
        $this->assertSame($expected, $document->getPath());
    }

    public function getOnDocumentWithTagsTests()
    {
        return array(
            array(array(), 'tags-empty.md'),
            array(array('foo'), 'tags-one.md'),
            array(array('foo', 'bar'), 'tags-multiple.md'),
        );
    }

    /**
     * @dataProvider getOnDocumentWithTagsTests
     */
    public function testOnDocumentWithTags($expected, $file)
    {
        $document = $this->createDocument($file);
        $event = new CarewEvent($document);

        $extraction = new Extraction();
        $extraction->onDocument($event);

        $metadatas = $document->getMetadatas();
        $this->assertSame($expected, $metadatas['tags']);
    }

    public function testOnDocumentWithExtraMetadatas()
    {
        $document = $this->createDocument('extra-metadatas.md');
        $event = new CarewEvent($document);

        $extraction = new Extraction();
        $extraction->onDocument($event);

        $metadatas = $document->getMetadatas();
        $this->assertSame('v1', $metadatas['k1']);
        $this->assertSame(array('v2.1', 'v2.2'), $metadatas['k2']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not parse front matter
     */
    public function testOnDocumentWithNoHeaderAndException()
    {
        $document = $this->createDocument('other-format.js');
        $event = new CarewEvent($document);

        $extraction = new Extraction();
        $extraction->onDocument($event);
    }

    public function testOnDocumentWithNoHeader()
    {
        $document = $this->createDocument('other-format.js');
        $event = new CarewEvent($document, array('allowEmptyHeader' => true));

        $extraction = new Extraction();
        $extraction->onDocument($event);

        $this->assertSame("body\n", $document->getBody());
        $this->assertSame(false, $document->getLayout());
        $this->assertSame('other-format.js', $document->getPath());
    }

    public function getOnDocumentWithPermalink()
    {
        return array(
            array('foo.html', 'permalink-with-html-extension.md'),
            array('foo.js', 'permalink-with-js-extension.md'),
            array('foo.html', 'permalink-without-extension.md'),
            array('a-blog-post/index.html', 'permalink-without-filename.md'),
        );
    }

    /**
     * @dataProvider getOnDocumentWithPermalink
     */
    public function testOnDocumentWithPermalink($expected, $file)
    {
        $document = $this->createDocument($file);
        $event = new CarewEvent($document);

        $extraction = new Extraction();
        $extraction->onDocument($event);

        $this->assertSame($expected, $document->getPath());
    }

    private function createDocument($file, $relativePath = '')
    {
        if ($relativePath) {
            $file = $relativePath.'/'.$file;
        }
        $file = new SplFileInfo(__DIR__.'/../../../fixtures/extraction/'.$file, $relativePath, $file);

        return new Document($file);
    }
}

