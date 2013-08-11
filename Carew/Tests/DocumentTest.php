<?php

namespace Carew\Tests;

use Carew\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
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
