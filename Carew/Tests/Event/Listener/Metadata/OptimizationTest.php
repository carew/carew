<?php

namespace Carew\Tests\Event\Listener\Metadata;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Metadata\Optimization;

class OptimizationTest extends \PHPUnit_Framework_TestCase
{
    public function testOnApi()
    {
        $document = new Document(null, null, Document::TYPE_API);
        $document->setPath('foobar.html');

        $event = new CarewEvent($document);

        $extraction = new Optimization();
        $extraction->onDocument($event);

        $this->assertSame('api/foobar.html', $document->getPath());
    }

    public function getOnPostTests()
    {
        return array(
            array('2010/09/09/foobar-bar.html', '%year%/%month%/%day%/%slug%.html'),
            array('2010/foobar-bar-toto.html', '%year%/%slug%-toto.html'),
            array('foobar-bar.html', '%slug%.html'),
            array('foobar-bar.html', '%slug%'),
            array('foobar-bar/index.html', '%slug%/'),
        );
    }

    /**
     * @dataProvider getOnPostTests
     */
    public function testOnPost($expected, $format)
    {
        $file = $this->prophesize('Symfony\Component\Finder\SplFileInfo');
        $file->getBasename('.md')->willReturn('2010-09-09-foobar-bar');
        $file->__toString()->willReturn('');

        $document = new Document($file->reveal(), null, Document::TYPE_POST);

        $event = new CarewEvent($document);

        $extraction = new Optimization($format);
        $extraction->onDocument($event);
        $this->assertSame($expected, $document->getPath());
    }
}
