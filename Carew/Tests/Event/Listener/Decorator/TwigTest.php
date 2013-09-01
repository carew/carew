<?php

namespace Carew\Tests\Event\Listener\Decorator;

use Carew\Document;
use Carew\Event\CarewEvent;
use Carew\Event\Listener\Decorator\Twig;
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
                $href = 1 == $i ? 'index.html' : sprintf('index-page-%s.html', $i);
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

    public function testPreRenderWithMultiplePagination()
    {
        $posts = array();
        for ($i = 1; $i <= 20; $i++) {
            $document = new Document();
            $document->setTitle('Post #'.$i);
            $posts[] = $document;
        }

        $posts2 = array();
        for ($i = 1; $i <= 12; $i++) {
            $document = new Document();
            $document->setTitle('Other Post #'.$i);
            $posts2[] = $document;
        }

        $document = new Document();
        $document->setLayout('default');
        $document->setPath('index.html');
        $document->setTitle('Index');
        $document->setBody(<<<'EOL'
{{ render_documents(paginate([carew.document], 4)) }}
{{ render_documents(paginate(carew.posts, 4)) }}
{{ render_documents(paginate(carew.extra.posts2, 6)) }}
EOL
        );

        $event = new CarewEvent(array($document));
        $this->getTwigGlobals()->fromArray(array('posts' => $posts, 'posts2' => $posts2));

        $this->twigListenner->preRender($event);
        $documents = $event->getSubject();
        $this->assertCount(6, $documents);

        $lis1 = $lis2 = $lis3 = array();
        foreach (array_values($documents) as $key => $document) {
            $page = $key + 1;
            if (1 == $page) {
                $path = 'index.html';
            } elseif (in_array($page, array(2, 3, 4, 5))) {
                $path = sprintf('index-page-%s.html', $page);
            } elseif (6 == $page) {
                $path = 'index-2-page-6.html';
            }

            $crawler = new Crawler($document->getBody());
            $this->assertCount(5, $crawler->filter('ul'), 'key is: '.$key);

            // First pagination (1 item by pages, 0 page (no need to paginate))
            $this->assertCount(1, $crawler->filter('ul')->eq(0)->filter('li'));
            foreach ($crawler->filter('ul')->eq(0)->filter('li') as $li) {
                $lis1[] = trim($li->textContent);
            }

            // Seconde pagination (4 items by pages, 5 pages)
            $this->assertCount(4, $crawler->filter('ul')->eq(1)->filter('li'));
            foreach ($crawler->filter('ul')->eq(1)->filter('li') as $li) {
                $lis2[] = trim($li->textContent);
            }
            $this->assertCount(5, $crawler->filter('ul')->eq(2)->filter('li'));
            for ($i = 1; $i <= 5; $i++) {
                $this->assertSame('page '.$i, $crawler->filter('ul')->eq(2)->filter('li')->eq($i - 1)->text(), sprintf('($i = %s, $page = %s)', $i, $page));
                $href = 1 == $i ? 'index.html' : sprintf('index-1-page-%s.html', $i);
                $this->assertSame($href, $crawler->filter('ul')->eq(2)->filter('li')->eq($i - 1)->filter('a')->attr('href'), sprintf('($i = %s, $page = %s)', $i, $page));
                if (6 == $page) {
                    continue;
                }
                $class = $page === $i ? 'active' : '';
                $this->assertSame($class, $crawler->filter('ul')->eq(2)->filter('li')->eq($i - 1)->attr('class'), sprintf('Class "active" is present only when $i == $page, ($i = %s, $page = %s, class = "%s")', $i, $page, $class));
            }

            // Third pagination (6 items by pages, 2 pages)
            $this->assertCount(6, $crawler->filter('ul')->eq(3)->filter('li'));
            foreach ($crawler->filter('ul')->eq(3)->filter('li') as $li) {
                $lis3[] = trim($li->textContent);
            }
            $this->assertCount(2, $crawler->filter('ul')->eq(4)->filter('li'));
            if (1 == $page) {
                $this->assertSame('active', $crawler->filter('ul')->eq(4)->filter('li')->eq(0)->attr('class'));
            } elseif (6 == $page) {
                $this->assertSame('', $crawler->filter('ul')->eq(4)->filter('li')->eq(0)->attr('class'));
                $this->assertSame('active', $crawler->filter('ul')->eq(4)->filter('li')->eq(1)->attr('class'));
            }
            $this->assertSame('page 1', $crawler->filter('ul')->eq(4)->filter('li')->eq(0)->text());
            $this->assertSame('index.html', $crawler->filter('ul')->eq(4)->filter('li')->eq(0)->filter('a')->attr('href'));
            $this->assertSame('page 2', $crawler->filter('ul')->eq(4)->filter('li')->eq(1)->text());
            $this->assertSame('index-2-page-2.html', $crawler->filter('ul')->eq(4)->filter('li')->eq(1)->filter('a')->attr('href'));
        }

        $lis1 = array_unique($lis1);
        sort($lis1);
        $lis1Expected = array('Index');
        $this->assertSame($lis1Expected, $lis1);

        $lis2 = array_unique($lis2);
        sort($lis2);
        $lis2Expected = array (
            'Post #1', 'Post #10', 'Post #11', 'Post #12', 'Post #13',
            'Post #14', 'Post #15', 'Post #16', 'Post #17', 'Post #18',
            'Post #19', 'Post #2', 'Post #20', 'Post #3', 'Post #4', 'Post #5',
            'Post #6', 'Post #7', 'Post #8', 'Post #9',
        );
        $this->assertSame($lis2Expected, $lis2);
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
