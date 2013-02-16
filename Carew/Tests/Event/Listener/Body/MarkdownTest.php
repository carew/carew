<?php

namespace Carew\Tests\Event\Listener\Metadata\Extraction;

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
            array(0, 'other-format.js'),
        );
    }

    /**
     * @dataProvider getTestOnDocument
     */
    public function testOnDocument($expected, $file)
    {
        $document = $this->createDocument($file);
        $event = new CarewEvent($document);

        $markdownParser = $this->getMock('Michelf\Markdown');
        $markdownParser
            ->expects($this->exactly($expected))
            ->method('transform')
        ;

        $extraction = new Markdown($markdownParser);
        $extraction->onDocument($event);

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

