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
        $event = new CarewEvent($document);

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

    public function testOnDocumentDoesNotAlterLink()
    {
        $document = new Document();
        $document->setPath('index.html');
        $body = <<<EOL
<a href="{{ path(\'foobar\') }}">foobar</a>
AAAAA
<a href="{{ path(\'baba\') }}">baba</a>
BBBBB
<a href="{{ path(\'bar\') }}">bar</a>
CCCCC
<a href="{{ path(\'foobar\') }}">foobar</a>
EOL;
        $document->setBody($body);

        $event = new CarewEvent($document);

        $toc = new Toc();
        $toc->onDocument($event);

        $this->assertSame($body, $document->getBody());
    }

    public function testOnDocumentDoesNotAlterSrc()
    {
        $document = new Document();
        $document->setPath('index.html');
        $body = <<<EOL
<img src="{{ path(\'foobar\') }}" alt="Foo Bar">
AAAAA
<script src="{{ path(\'baba\') }}"></script>
BBBBB
<!--[if lt IE 9]><script src="{{ path(\'bar\') }}"></script><![endif]-->
CCCCC
<img src="{{ path(\'foobar\') }}">
EOL;
        $document->setBody($body);

        $event = new CarewEvent($document);

        $toc = new Toc();
        $toc->onDocument($event);

        $this->assertSame($body, $document->getBody());
    }
}
