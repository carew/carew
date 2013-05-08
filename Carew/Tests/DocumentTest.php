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

    public function testSetMetadatas()
    {
        $document = new Document();

        $document->addMetadatas(array('foo' => 'bar'));
        $this->assertSame(array('tags' => array(), 'navigation' => array(), 'foo' => 'bar'), $document->getMetadatas());

        $document->addMetadatas(array('foo2' => 'bar2'));
        $this->assertSame(array('tags' => array(), 'navigation' => array(), 'foo' => 'bar', 'foo2' => 'bar2'), $document->getMetadatas());
    }


}
