<?php

namespace Carew\Tests\Event\Listener\Body;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Body\Markdown;
use Symfony\Component\Finder\SplFileInfo;

class MarkdownTest extends \PHPUnit_Framework_TestCase
{
    public function getTestOnDocument()
    {
        return array(
            array(1, 'simple.md'),
            array(1, 'simple.md.twig'),
            array(0, 'simple.js'),
            array(0, 'simple.js.twig'),
        );
    }

    /**
     * @dataProvider getTestOnDocument
     */
    public function testOnDocument($expected, $file)
    {
        $document = $this->createDocument($file);
        $event = new CarewEvent($document);

        $markdownParser = $this->getMock('Parsedown');
        $markdownParser
            ->expects($this->exactly($expected))
            ->method('parse')
        ;

        $extraction = new Markdown($markdownParser);
        $extraction->onDocument($event);

    }

    public function testParseTwigLink()
    {
        $document = $this->createDocument('simple.md.twig');
        $document->setBody('[homepage]({{ carew.relativeRoot }})');
        $event = new CarewEvent($document);

        $extraction = new Markdown();
        $extraction->onDocument($event);

        $this->assertSame('<p><a href="{{ carew.relativeRoot }}">homepage</a></p>', $document->getBody());

    }

    private function createDocument($file)
    {
        return new Document(new SplFileInfo(__DIR__.'/../../../fixtures/'.$file, $file, ''), $file);
    }
}
