<?php

namespace Carew\Tests\Command;

use Carew\Carew;
use Symfony\Component\Console\Tester\ApplicationTester;
use Carew\Tests\AbstractTest;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @group functional
 */
class BuildTest extends AbstractTest
{
    public function testExecuteWithSite1()
    {
        $this->deleteDir($webDir = __DIR__.'/fixtures/site1/web');
        list($application, $statusCode) = $this->runApplication(dirname($webDir));

        $this->assertSame(0, $statusCode);

        // Posts
        $this->assertTrue(file_exists($webDir.'/2010/01/01/hello.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/2010/01/01/hello.html'));
        $this->assertSame('Hello', $crawler->filter('title')->text());
        $this->assertSame('Hello', $crawler->filter('h1')->text());

        $this->assertTrue(file_exists($webDir.'/2010/01/02/post2.html'));
        $this->assertTrue(file_exists($webDir.'/2010/01/03/post3.html'));
        $this->assertTrue(file_exists($webDir.'/2010/01/04/good-bye.html'));

        // Pages
        $this->assertTrue(file_exists($webDir.'/index.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/index.html'));
        $this->assertSame('index', $crawler->filter('title')->text());
        $this->assertSame('index', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('ul'));
        // posts list
        $this->assertCount(4, $crawler->filter('ul')->eq(0)->filter('li'));
        $this->assertSame('Good bye', trim($crawler->filter('ul')->eq(0)->filter('li')->eq(0)->text()));
        $this->assertSame('Hello', trim($crawler->filter('ul')->eq(0)->filter('li')->eq(3)->text()));
        // pages list
        $this->assertCount(1, $crawler->filter('ul')->eq(1)->filter('li'));
        $this->assertSame('Index', trim($crawler->filter('ul')->eq(1)->filter('li')->eq(0)->text()));
        // api list
        $this->assertCount(1, $crawler->filter('ul')->eq(2)->filter('li'));
        $this->assertSame('Api', trim($crawler->filter('ul')->eq(2)->filter('li')->eq(0)->text()));

        // Api
        $this->assertTrue(file_exists($webDir.'/api/api.html'));
        $this->assertSame("api\n", file_get_contents($webDir.'/api/api.html'));

        // Assets
        $this->assertTrue(file_exists($webDir.'/styles.css'));
        $this->assertSame("css\n", file_get_contents($webDir.'/styles.css'));

        $this->deleteDir($webDir);
    }

    public function testExecuteWithSiteAndToc()
    {
        $this->deleteDir($webDir = __DIR__.'/fixtures/site-with-toc/web');
        list($application, $statusCode) = $this->runApplication(dirname($webDir));

        $this->assertSame(0, $statusCode);

        $this->assertTrue(file_exists($webDir.'/index.html'));
        $expected = <<<EOL
<p><ul><li><a href="#h1">H1</a><ul><li><a href="#h2">H2</a><ul><li><a href="#h3">H3</a><ul><li><a href="#h4">H4</a><ul><li><a href="#h5">H5</a><ul><li><a href="#h6">H6</a></li></ul></li></ul></li></ul></li></ul></li><li><a href="#h2-2">H2</a><ul><li><a href="#h3-2">H3</a><ul><li><a href="#h4-2">H4</a><ul><li><a href="#h5-2">H5</a><ul><li><a href="#h6-2">H6</a></li></ul></li></ul></li></ul></li></ul></li></ul></li></ul></p>
<p>Good Night</p>
<h1 id="h1">H1<a href="#h1" class="anchor">#</a></h1>
<h2 id="h2">H2<a href="#h2" class="anchor">#</a></h2>
<h3 id="h3">H3<a href="#h3" class="anchor">#</a></h3>
<h4 id="h4">H4<a href="#h4" class="anchor">#</a></h4>
<h5 id="h5">H5<a href="#h5" class="anchor">#</a></h5>
<h6 id="h6">H6<a href="#h6" class="anchor">#</a></h6>
<h2 id="h2-2">H2<a href="#h2-2" class="anchor">#</a></h2>
<h3 id="h3-2">H3<a href="#h3-2" class="anchor">#</a></h3>
<h4 id="h4-2">H4<a href="#h4-2" class="anchor">#</a></h4>
<h5 id="h5-2">H5<a href="#h5-2" class="anchor">#</a></h5>
<h6 id="h6-2">H6<a href="#h6-2" class="anchor">#</a></h6>
EOL;

        $this->assertContains($expected, file_get_contents($webDir.'/index.html'));

        $this->deleteDir($webDir);
    }

    public function testExecuteWithSiteAndPagination()
    {
        $this->deleteDir($webDir = __DIR__.'/fixtures/site-with-pagination/web');
        list($application, $statusCode) = $this->runApplication(dirname($webDir));

        $this->assertSame(0, $statusCode);

        $lis = array();

        for ($page = 1; $page <= 4; $page++) {
            $path = 1 == $page ? 'index.html' : sprintf('index-page-%s.html', $page);
            $this->assertTrue(file_exists($webDir.'/'.$path), $path);

            $crawler = new Crawler(file_get_contents($webDir.'/'.$path));

            // One ul for each document, one ul for each pages
            $this->assertCount(2, $crawler->filter('ul'));

            // 14 pages + 1 index = 15 = 4 (item by page) * 3 (pages) + 3 (item on the last page)
            $this->assertCount(4 == $page ? 3 : 4, $crawler->filter('ul')->eq(0)->filter('li'), "\$page == $page");
            foreach ($crawler->filter('ul')->eq(0)->filter('li') as $li) {
                $lis[] = trim($li->textContent);
            }

            // There is 4 pages
            $this->assertCount(4, $crawler->filter('ul')->eq(1)->filter('li'));

            // Check of pagination
            for ($i = 1; $i <= 4; $i++) {
                $class = $page == $i ? 'active' : '';
                $this->assertSame($class, $crawler->filter('ul')->eq(1)->filter('li')->eq($i - 1)->attr('class'), sprintf('Class "active" is present only when $i == $page, ($i = %s, $page = %s)', $i, $page));
                $this->assertSame('page '.$i, $crawler->filter('ul')->eq(1)->filter('li')->eq($i - 1)->text(), sprintf('($i = %s, $page = %s)', $i, $page));
                $href = 1 == $i ? 'index.html' : sprintf('index-page-%s.html', $i);
                $this->assertSame($href, $crawler->filter('ul')->eq(1)->filter('li')->eq($i - 1)->filter('a')->attr('href'), sprintf('($i = %s, $page = %s)', $i, $page));
            }
        }

        $this->assertFalse(file_exists($webDir.'/index-page-5.html'));

        sort($lis);

        $expected = array(
            'Index', 'Page1', 'Page10', 'Page11', 'Page12', 'Page13', 'Page14',
            'Page2', 'Page3', 'Page4', 'Page5', 'Page6', 'Page7', 'Page8',
            'Page9',

        );

        $this->assertSame($expected, $lis);

        $this->deleteDir($webDir);
    }

    public function testExecuteWithSiteAndMultiplePagination()
    {
        $this->deleteDir($webDir = __DIR__.'/fixtures/site-with-multiple-pagination/web');
        list($application, $statusCode) = $this->runApplication(dirname($webDir));

        $this->assertSame(0, $statusCode);

        $this->deleteDir($webDir);
    }

    public function testExecuteWithSiteAndTag()
    {
        $this->deleteDir($webDir = __DIR__.'/fixtures/site-with-tags/web');
        list($application, $statusCode) = $this->runApplication(dirname($webDir));

        $this->assertSame(0, $statusCode);

        $this->assertTrue(file_exists($webDir.'/tags/index.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/tags/index.html'));
        $this->assertSame('Tags', $crawler->filter('title')->text());
        $this->assertSame('Tags', $crawler->filter('h1')->text());
        $this->assertCount(1, $crawler->filter('ul'));
        $this->assertCount(3, $crawler->filter('li'));

        $this->assertTrue(file_exists($webDir.'/tags/tag1.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/tags/tag1.html'));
        $this->assertSame('Tag #tag1', $crawler->filter('title')->text());
        $this->assertSame('Tag #tag1', $crawler->filter('h1')->text());
        $this->assertCount(1, $crawler->filter('ul'));
        $this->assertCount(5, $crawler->filter('li'));

        $this->assertTrue(file_exists($webDir.'/tags/tag2.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/tags/tag2.html'));
        $this->assertSame('Tag #tag2', $crawler->filter('title')->text());
        $this->assertSame('Tag #tag2', $crawler->filter('h1')->text());
        $this->assertCount(1, $crawler->filter('ul'));
        $this->assertCount(5, $crawler->filter('li'));

        $this->assertTrue(file_exists($webDir.'/tags/tag3.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/tags/tag3.html'));
        $this->assertSame('Tag #tag3', $crawler->filter('title')->text());
        $this->assertSame('Tag #tag3', $crawler->filter('h1')->text());
        $this->assertCount(1, $crawler->filter('ul'));
        $this->assertCount(4, $crawler->filter('li'));

        $this->deleteDir($webDir);
    }

    public function testExecuteWithConfigFolder()
    {
        $this->deleteDir($webDir = __DIR__.'/fixtures/config-folder/web');
        list(, $statusCode) = $this->runApplication(dirname($webDir));

        $this->assertSame(0, $statusCode);

        $this->assertTrue(file_exists($webDir.'/2010/01/01/hello.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/2010/01/01/hello.html'));
        $this->assertSame('Hello', $crawler->filter('title')->text());
        $this->assertSame('Hello', $crawler->filter('h1')->text());

        $this->assertTrue(file_exists($webDir.'/2010/01/02/post2.html'));
        $this->assertTrue(file_exists($webDir.'/2010/01/03/post3.html'));
        $this->assertTrue(file_exists($webDir.'/2010/01/04/good-bye.html'));

        $this->deleteDir($webDir);
    }

    public function testExecuteWithConfigThrowExceptionIfExtensionClassDoesNotExists()
    {
        list($application, $statusCode) = $this->runApplication(__DIR__.'/fixtures/config-exception-class-not-exists');

        $this->assertSame(1, $statusCode);
        $this->assertContains('The class "FooBar" does not exists. See "config.yml".', $application->getDisplay());
    }

    public function testExecuteWithConfigThrowExceptionIfExtensionClassDoesNotImplementsInterface()
    {
        list($application, $statusCode) = $this->runApplication(__DIR__.'/fixtures/config-exception-class-not-implements');

        $this->assertSame(1, $statusCode);
        $this->assertContains('The class "stdClass" does not implements ExtensionInterface. See "config.yml".', $application->getDisplay());
    }

    public function testExecuteWithConfigTheme()
    {
        $this->deleteDir($webDir = __DIR__.'/fixtures/theme/web');
        list(, $statusCode) = $this->runApplication(dirname($webDir));

        $this->assertSame(0, $statusCode);

        $this->assertTrue(file_exists($webDir.'/2010/01/01/local-layout.html'));
        $this->assertSame('local:local-layout', trim(file_get_contents($webDir.'/2010/01/01/local-layout.html')));

        $this->assertTrue(file_exists($webDir.'/2010/01/02/vendor-layout.html'));
        $this->assertSame('vendor:vendor-layout', trim(file_get_contents($webDir.'/2010/01/02/vendor-layout.html')));

        $this->assertTrue(file_exists($webDir.'/2010/01/03/default-extends.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/2010/01/03/default-extends.html'));
        $this->assertSame('default-extends', $crawler->filter('title')->text());
        $this->assertSame('title:Default-extends', $crawler->filter('h1')->text());
        $this->assertSame('Should be wrapped into a div', trim($crawler->filter('div.body')->text()));
    }

    private function runApplication($fixturesPath)
    {
        $carew = new Carew();
        $carew->setAutoExit(false);

        $application = new ApplicationTester($carew);

        $input = array(
            'command' => 'build',
            '--base-dir' => $fixturesPath,
            '--web-dir' => $fixturesPath.'/web',
            '--verbose' => true,
        );

        $statusCode = $application->run($input);

        return array($application, $statusCode);
    }
}
