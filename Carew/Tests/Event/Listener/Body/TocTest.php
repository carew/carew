<?php

namespace Carew\Tests\Event\Listener\Body;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Body\Toc;

class TocTest extends \PHPUnit_Framework_TestCase
{

    public function testOnDocument()
    {
        $document = new Document();
        $document->setPath('index.html');
        $document->setBody(<<<EOL
<h1>Hello</h1>
<h2>TLDR</h2>
EOL
        );
        $event = new CarewEvent(array($document));

        $toc = new Toc();
        $toc->onDocument($event);

        $bodyExpected = <<<EOL
<h1 id="hello">Hello<a href="#hello" class="anchor">#</a></h1>
<h2 id="tldr">TLDR<a href="#tldr" class="anchor">#</a></h2>
EOL;

        $this->assertSame($bodyExpected, $document->getBody());

        $tocExpected = array(
            1 => array(
                'title' => 'Hello',
                'id' => 'hello',
                'children' => array(
                    1 => array(
                        'title' => 'TLDR',
                        'id' => 'tldr',
                        'children' => array(
                        ),
                    ),
                ),
            ),
        );

        $this->assertSame($tocExpected, $document->getToc());
    }
}
