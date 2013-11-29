<?php

namespace Carew\Tests;

use Carew\Document;
use Symfony\Component\Finder\SplFileInfo;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $document = new Document(new SplFileInfo(__FILE__, '.', '.'));

        $this->assertSame(file_get_contents(__FILE__), $document->getBody());
        $this->assertSame(file_get_contents(__FILE__), $document->getBodyDecorated());
        $this->assertSame('DocumentTest.php', $document->getPath());
    }

    public function testType()
    {
        $document = new Document(null, null, Document::TYPE_UNKNOWN);
        $this->assertSame(Document::TYPE_UNKNOWN, $document->getType());
        $this->assertFalse($document->isTypePost());
        $this->assertFalse($document->isTypePage());
        $this->assertFalse($document->isTypeApi());

        $document = new Document(null, null, Document::TYPE_PAGE);
        $this->assertSame(Document::TYPE_PAGE, $document->getType());
        $this->assertFalse($document->isTypePost());
        $this->assertTrue($document->isTypePage());
        $this->assertFalse($document->isTypeApi());

        $document = new Document(null, null, Document::TYPE_POST);
        $this->assertSame(Document::TYPE_POST, $document->getType());
        $this->assertTrue($document->isTypePost());
        $this->assertFalse($document->isTypePage());
        $this->assertFalse($document->isTypeApi());

        $document = new Document(null, null, Document::TYPE_API);
        $this->assertSame(Document::TYPE_API, $document->getType());
        $this->assertFalse($document->isTypePost());
        $this->assertFalse($document->isTypePage());
        $this->assertTrue($document->isTypeApi());
    }

    public function getGetRootPathTests()
    {
        return array(
            array('.', ''),
            array('.', '/'),
            array('.', 'index.html'),
            array('.', '/index.html'),
            array('..', '/foo/'),
            array('..', 'foo/index.html'),
            array('..', '/foo/index.html'),
            array('../..', '/foo/bar/'),
            array('../..', 'foo/bar/index.html'),
            array('../..', '/foo/bar/index.html'),
            array('../../..', '/foo/bar/baz/'),
        );
    }

    /**
     * @dataProvider getGetRootPathTests
     */
    public function testGetRootPath($expected, $path)
    {
        $document = new Document();
        $document->setPath($path);
        $this->assertSame($expected, $document->getRootPath());
    }

    public function testGetSetVar()
    {
        $document = new Document();

        $this->assertSame(null, $document->getVar('foo'));
        $this->assertSame('default', $document->getVar('foo', 'default'));

        $document->setVar('foo', 'bar');
        $this->assertSame('bar', $document->getVar('foo'));
        $this->assertSame('bar', $document->getVar('foo', 'default'));
    }

    public function testAddMetadatas()
    {
        $document = new Document();

        $document->addMetadatas(array('foo' => 'bar'));
        $this->assertSame(array('foo' => 'bar'), $document->getMetadatas());

        $document->addMetadatas(array('foo2' => 'bar2'));
        $this->assertSame(array('foo' => 'bar', 'foo2' => 'bar2'), $document->getMetadatas());
    }

    public function testGetSetMetadata()
    {
        $document = new Document();

        $this->assertSame(null, $document->getMetadata('foo'));
        $this->assertSame('default', $document->getMetadata('foo', 'default'));

        $document->setMetadata('foo', 'bar');
        $this->assertSame('bar', $document->getMetadata('foo'));
        $this->assertSame('bar', $document->getMetadata('foo', 'default'));
    }
}
