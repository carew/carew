<?php

namespace Carew\Tests\Twig;

use Carew\Document;
use Carew\Processor;
use Carew\Twig\CarewExtension;
use Carew\Twig\Globals;

class CarewExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $documents;
    private $globals;
    private $twig;
    private $container;
    private $extension;

    public function setUp()
    {
        $this->container = new \Pimple();
        $this->extension = new CarewExtension($this->container);

        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem(array(
            __DIR__.'/fixtures/theme1',
            __DIR__.'/fixtures/theme2',
            __DIR__.'/../../Twig/Resources/layouts',
        )));

        $documents = array();
        for ($i = 1; $i <= 20; ++$i) {
            $document = new Document();
            $document->setTitle('Doc #'.$i);
            $document->setTags('Tag #'.$i % 5);
            $document->setFilePath('pages/page-'.$i);
            $document->setPath('pages/page-'.$i.'.html');
            $documents[$document->getFilePath()] = $document;
        }
        $processor = new Processor();
        $this->globals = $processor->processGlobals($documents);
        $this->documents = $documents;

        $this->twig->addGlobal('carew', $this->globals);

        $this->twig->addExtension($this->extension);
    }

    public function testRenderToc()
    {
        $this->markTestIncomplete();
    }

    public function testRenderDocumentAttributeWithDefaultDocument()
    {
        $this->globals->document = $this->documents['pages/page-2'];
        $this->assertSame('Doc #2', $this->extension->renderDocumentAttribute($this->twig, 'title'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The document with path "foobar" does not exist.
     */
    public function testRenderDocumentAttributeWithUnknowDocumentThrowException()
    {
        $this->extension->renderDocumentAttribute($this->twig, 'title', 'foobar');
    }

    public function testRenderDocumentAttributeWithFilePath()
    {
        $this->assertSame('Doc #2', $this->extension->renderDocumentAttribute($this->twig, 'title', 'pages/page-2'));
    }

    public function testRenderDocumentAttributeWithDocument()
    {
        $this->assertSame('Doc #2', $this->extension->renderDocumentAttribute($this->twig, 'title', $this->documents['pages/page-2']));
    }

    public function testRenderDocument()
    {
        $this->markTestIncomplete();
    }

    public function testRenderDocuments()
    {
        $this->markTestIncomplete();
    }

    public function testRenderPagination()
    {
        $this->markTestIncomplete();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The document with path "foobar" does not exist.
     */
    public function testPathWithUnknowDocumentThrowException()
    {
        $this->extension->path($this->twig, 'foobar');
    }

    public function testPathWithFilePath()
    {
        $this->assertSame('/pages/page-2.html', $this->extension->path($this->twig, 'pages/page-2'));
    }

    public function testPathWithDocument()
    {
        $this->assertSame('/pages/page-2.html', $this->extension->path($this->twig, $this->documents['pages/page-2']));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The document with path "foobar" does not exist.
     */
    public function testLinkWithUnknowDocumentThrowException()
    {
        $this->extension->link($this->twig, 'foobar');
    }

    public function testLinkWithFilePath()
    {
        $this->assertSame('<a href="/pages/page-2.html" class="foobar">title</a>', $this->extension->link($this->twig, 'pages/page-2', 'title', array('class' => 'foobar')));
    }

    public function testLinkWithDocument()
    {
        $this->assertSame('<a href="/pages/page-2.html">Doc #2</a>', $this->extension->link($this->twig, $this->documents['pages/page-2']));
    }

    public function testBlockHierarchy()
    {
        $this->markTestIncomplete();
    }

    public function tearDown()
    {
        $this->container;
        $this->extension;
    }
}
