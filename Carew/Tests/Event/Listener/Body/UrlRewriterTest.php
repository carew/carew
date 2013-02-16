<?php

namespace Carew\Tests\Event\Listener\Metadata\Extraction;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Body\UrlRewriter;

class UrlRewriterTest extends \PHPUnit_Framework_TestCase
{
    public function getOnDocumentTests()
    {
        return array(
            array('', ''),
            array('hello {{relativeRoot} !', 'hello {{relativeRoot} !'),
            array('hello . !', 'hello {{relativeRoot}} !'),
            array('hello . !', 'hello {{ relativeRoot}} !'),
            array('hello . !', 'hello {{relativeRoot }} !'),
            array('hello . !', 'hello {{ relativeRoot }} !'),
            array('hello . !', 'hello {{  relativeRoot  }} !'),
            array('hello . !', "hello {{\nrelativeRoot}} !"),
            array('hello . !', "hello {{relativeRoot\n}} !"),
            array('hello . !', "hello {{\nrelativeRoot\n}} !"),
        );
    }

    /**
     * @dataProvider getOnDocumentTests
     */
    public function testOnDocument($expected, $body)
    {
        $document = new Document();

        $document->setBody($body);
        $event = new CarewEvent($document);


        $extraction = new UrlRewriter();
        $extraction->onDocument($event);

        $this->assertSame($expected, $document->getBody());

    }
}

