<?php

namespace Carew\Tests\Event\Listener\Body;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Body\Twig;
use Carew\Twig\CarewExtension;
use Carew\Twig\Globals;
use Symfony\Component\DomCrawler\Crawler;

class TwigTest extends \PHPUnit_Framework_TestCase
{
    private $twigLoader;
    private $twig;
    private $twigListenner;

    public function setUp()
    {
        $this->twigLoader = new \Twig_Loader_Array(array(
            'blocks.html.twig' => file_get_contents(__DIR__.'/../../../../Twig/Resources/layouts/blocks.html.twig')
        ));
        $this->twig = new \Twig_Environment($this->twigLoader, array(
            'base_template_class' => 'Carew\Twig\Template',
        ));
        $this->twig->addExtension(new \Twig_Extension_StringLoader());
        $this->twig->addExtension(new CarewExtension());
        $this->twig->addGlobal('carew', new Globals());

        $this->twigListenner = new Twig($this->twig);
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
        $this->getTwigGlobals()->fromArray(array('foo' => 'bar'));

        $this->twigListenner->preRender($event);

        $this->assertSame('bar', $document->getBody());
    }

    public function testPreRenderWithPagination()
    {
        $posts = array();
        for ($i = 1; $i <= 20; $i++) {
            $document = new Document();
            $document->setTitle('Post #'.$i);
            $posts[] = $document;
        }

        $document = new Document();
        $document->setLayout('default');
        $document->setPath('index.html');
        $document->setBody('{{ render_documents(paginate(carew.posts, 4)) }}');

        $event = new CarewEvent(array($document));
        $this->getTwigGlobals()->fromArray(array('posts' => $posts));

        $this->twigListenner->preRender($event);
        $documents = $event->getSubject();

        $this->assertCount(5, $documents);

        $lis = array();
        foreach (array_values($documents) as $key => $document) {
            $page = $key + 1;
            $path = 1 == $page ? 'index.html' : sprintf('index-page-%s.html', $page);
            $this->assertSame($path, $document->getPath());
            $crawler = new Crawler($document->getBody());
            $this->assertCount(2, $crawler->filter('ul'));
            $this->assertCount(4, $crawler->filter('ul')->eq(0)->filter('li'));
            foreach ($crawler->filter('ul')->eq(0)->filter('li') as $li) {
                $lis[] = trim($li->textContent);
            }

            $this->assertCount(5, $crawler->filter('ul')->eq(1)->filter('li'));

            for ($i = 1; $i <= 5; $i++) {
                $class = $page == $i ? 'active' : '';
                $this->assertSame($class, $crawler->filter('ul')->eq(1)->filter('li')->eq($i - 1)->attr('class'), sprintf('Class "active" is present only when $i == $page, ($i = %s, $page = %s)', $i, $page));
                $this->assertSame('page '.$i, $crawler->filter('ul')->eq(1)->filter('li')->eq($i - 1)->text(), sprintf('($i = %s, $page = %s)', $i, $page));
                $href = 1 == $i ? './index.html' : sprintf('./index-page-%s.html', $i);
                $this->assertSame($href, $crawler->filter('ul')->eq(1)->filter('li')->eq($i - 1)->filter('a')->attr('href'), sprintf('($i = %s, $page = %s)', $i, $page));
            }
        }

        sort($lis);
        $expected = array (
            'Post #1', 'Post #10', 'Post #11', 'Post #12', 'Post #13',
            'Post #14', 'Post #15', 'Post #16', 'Post #17', 'Post #18',
            'Post #19', 'Post #2', 'Post #20', 'Post #3', 'Post #4', 'Post #5',
            'Post #6', 'Post #7', 'Post #8', 'Post #9',
        );

        $this->assertSame($expected, $lis);
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
        $this->getTwigGlobals()->fromArray(array('foo' => 'bar', 'relativeRoot' => 'should not appear'));
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
        $this->twig = null;
        $this->twigLoader = null;
    }

    private function getTwigGlobals()
    {
        $all = $this->twig->getGlobals();

        return $all['carew'];
    }
}
