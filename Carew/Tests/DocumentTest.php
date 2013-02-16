<?php

namespace Carew\Tests;

use Carew\Document;

class DocumentTestTest extends \PHPUnit_Framework_TestCase
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

}
