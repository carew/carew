<?php

namespace Carew\Tests\Event\Listener\Metadata;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Metadata\Extraction;
use Symfony\Component\Finder\SplFileInfo;

class ExtractionTest extends \PHPUnit_Framework_TestCase
{
    public function getRewriteUrlTests()
    {
        return array(
            array('simple.html', 'simple'),
            array('simple.html', 'simple.twig'),
            array('simple.html', 'simple.html'),
            array('simple.html', 'simple.html.twig'),
            array('simple.html', 'simple.md'),
            array('simple.html', 'simple.md.twig'),
            array('simple.js',   'simple.js'),
            array('simple.js',   'simple.js.twig'),
            array('folder/simple.html', 'folder/simple'),
            array('folder/simple.html', 'folder/simple.twig'),
            array('folder/simple.html', 'folder/simple.html'),
            array('folder/simple.html', 'folder/simple.html.twig'),
            array('folder/simple.html', 'folder/simple.md'),
            array('folder/simple.html', 'folder/simple.md.twig'),
            array('folder/simple.js',   'folder/simple.js'),
            array('folder/simple.js',   'folder/simple.js.twig'),
        );
    }

    /**
     * @dataProvider getRewriteUrlTests
     */
    public function testRewriteUrl($expected, $file)
    {
        $document = $this->createDocument($file);
        $event = new CarewEvent($document);

        $extraction = new Extraction();
        $extraction->onDocument($event);

        $this->assertSame($expected, $document->getPath());
    }

    public function testOnDocumentWithSimpleFile()
    {
        $document = $this->createDocument('extra-metadatas.md');
        $event = new CarewEvent($document);

        $extraction = new Extraction();
        $extraction->onDocument($event);

        $this->assertSame('title', $document->getTitle());
        $this->assertSame('body', $document->getBody());
        $this->assertSame('default', $document->getLayout());
        $this->assertSame('extra-metadatas.html', $document->getPath());
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

        $this->assertSame($expected, $document->getTags());
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

    public function testOnDocumentWithNoHeader()
    {
        $document = $this->createDocument('scripts.js');
        $event = new CarewEvent($document);

        $extraction = new Extraction();
        $extraction->onDocument($event);

        $this->assertSame("some js\n", $document->getBody());
        $this->assertSame(false, $document->getLayout());
        $this->assertSame('scripts.js', $document->getPath());
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

    private function createDocument($file)
    {
        if ('.' == dirname($file)) {
            $file = new SplFileInfo(__DIR__.'/../../../fixtures/'.$file, '', basename($file));
        } else {
            $file = new SplFileInfo(__DIR__.'/../../../fixtures/'.$file, dirname($file), dirname($file).'/'.basename($file));
        }

        return new Document($file);
    }
}
