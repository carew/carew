<?php

namespace Carew\Tests;

use Carew\Processor;
use Symfony\Component\Finder\SplFileInfo;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    private $processor;
    private $finder;
    private $eventDispatcher;

    public function setUp()
    {
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->finder          = $this->getMock('Symfony\Component\Finder\Finder', [], [], '', false);
        $this->processor       = new Processor($this->eventDispatcher, $this->finder);
    }

    public function testProcess()
    {
        $this->finder
            ->staticExpects($this->once())
            ->method('create')
            ->will($this->returnValue($this->finder))
        ;
        foreach (array('in', 'files') as $method) {
            $this->finder
                ->expects($this->once())
                ->method($method)
                ->will($this->returnValue($this->finder))
            ;
        }
        $this->finder
            ->expects($this->once())
            ->method('name')
            ->will($this->returnValue(array(
                new SplFileInfo(__DIR__.'/fixtures/extraction/simple.md', '', 'simple.md')
            )))
        ;

        $this->eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with('carew.document', $this->isInstanceOf('Carew\Event\CarewEvent'))
        ;

        $this->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with('carew.documents', $this->isInstanceOf('Carew\Event\CarewEvent'))
        ;

        $documents = $this->processor->process(__DIR__);

        $this->assertCount(1, $documents);
    }

    public function tearDown()
    {
        $this->eventDispatcher = null;
        $this->finder          = null;
        $this->processor       = null;
    }
}
