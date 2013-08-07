<?php

namespace Carew\Tests\Event\Listener\Body;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Body\Twig;
use Carew\Twig\Globals;

class TwigTest extends \PHPUnit_Framework_TestCase
{
    private $twigListenner;
    private $twigLoader;

    public function setUp()
    {
        $this->twigLoader = new \Twig_Loader_Array(array('pre_render_template.html.twig' => '{{ include(template_from_string(body)) }}'));
        $twig = new \Twig_Environment($this->twigLoader, array(
            'base_template_class' => 'Carew\Twig\Template',
        ));
        $twig->addExtension(new \Twig_Extension_StringLoader());
        $twig->addGlobal('carew', new Globals());

        $this->twigListenner = new Twig($twig);
    }

    public function getPreRenderTests()
    {
        return array(
            array('some text', 'some text'),
            array('', '{# some comment #}'),
            array('', '{{ foo }}'),
            array('foo', '{{ foo|default("foo") }}'),
            array('.', '{{ carew.relativeRoot }}'),
            array('index.html', '{{ carew.currentPath }}'),
            array('title', '{{ carew.document.title }}'),
        );
    }

    /**
     * @dataProvider getPreRenderTests
     */
    public function testPreRender($expected, $body)
    {
        $document = new Document();
        $document->setLayout('default');
        $document->setTitle('title');
        $document->setPath('index.html');
        $document->setBody($body);

        $event = new CarewEvent(array($document));

        $this->twigListenner->preRender($event);

        $this->assertSame($expected, $document->getBody());
    }

    public function testPreRenderWithoutLayout()
    {
        $document = new Document();
        $document->setLayout(false);
        $document->setBody('{{ foo }}');

        $event = new CarewEvent(array($document));
        $this->twigListenner->preRender($event);

        $this->assertSame('{{ foo }}', $document->getBody());
    }

    public function testPreRenderWithGlobalVars()
    {
        $document = new Document();
        $document->setLayout('default');
        $document->setBody('{{ carew.extra.foo }}');

        $event = new CarewEvent(array($document));
        $event['globalVars'] = array('foo' => 'bar');

        $this->twigListenner->preRender($event);

        $this->assertSame('bar', $document->getBody());
    }

    public function getPostRenderWithoutLayoutTests()
    {
        return array(
            array('foo', 'foo'),
            array('{{ foo }}', '{{ foo }}'),
        );
    }

    /**
     * @dataProvider getPostRenderWithoutLayoutTests
     */
    public function testPostRenderWithoutLayout($expected, $body)
    {
        $document = new Document();
        $document->setBody($body);

        $event = new CarewEvent(array($document));
        $this->twigListenner->postRender($event);

        $this->assertSame($expected, $document->getBody());
    }

    public function getPostRenderWithLayoutTests()
    {
        return array(
            array('default'),
            array('default.html.twig'),
        );
    }

    /**
     * @dataProvider getPostRenderWithLayoutTests
     */
    public function testPostRenderWithLayout($layout)
    {
        $document = new Document();
        $document->setBody('Hello {{ name }}');
        $document->setLayout($layout);
        $document->setVars(array('key' => 'value', 'currentPath' => 'should not appear'));

        $template = <<<EOL
"{{ carew.document.body }}"
"{{ carew.relativeRoot }}"
"{{ carew.currentPath }}"
"{{ carew.extra.foo }}"
"{{ carew.extra.key }}"
EOL;
        $this->twigLoader->setTemplate('default.html.twig', $template);

        $event = new CarewEvent(array($document));
        $event['globalVars'] = array('foo' => 'bar', 'relativeRoot' => 'should not appear');
        $this->twigListenner->postRender($event);

        $expected = <<<EOL
"Hello {{ name }}"
"."
""
"bar"
"value"
EOL;
        $this->assertSame($expected, $document->getBody());
    }

    public function tearDown()
    {
        $this->twigListenner = null;
        $this->twigLoader    = null;
    }
}
