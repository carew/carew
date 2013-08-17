<?php

namespace Carew\Tests\Command;

use Carew\Carew;
use Symfony\Component\Console\Tester\ApplicationTester;
use Carew\Tests\AbstractTest;
use Symfony\Component\DomCrawler\Crawler;

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
        $this->assertSame('index', trim($crawler->filter('ul')->eq(1)->filter('li')->eq(0)->text()));
        // api list
        $this->assertCount(1, $crawler->filter('ul')->eq(2)->filter('li'));
        $this->assertSame('api', trim($crawler->filter('ul')->eq(2)->filter('li')->eq(0)->text()));

        // Api
        $this->assertTrue(file_exists($webDir.'/api/api.html'));
        $this->assertSame("api\n", file_get_contents($webDir.'/api/api.html'));

        // Assets
        $this->assertTrue(file_exists($webDir.'/styles.css'));
        $this->assertSame("css\n", file_get_contents($webDir.'/styles.css'));

        $this->deleteDir($webDir);
    }

    public function testExecuteWithSiteAndPagination()
    {
        $this->deleteDir($webDir = __DIR__.'/fixtures/site2/web');
        list($application, $statusCode) = $this->runApplication(dirname($webDir));

        $this->assertSame(0, $statusCode);

        $lis = array();

        $this->assertTrue(file_exists($webDir.'/index.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/index.html'));
        $this->assertCount(2, $crawler->filter('ul'));
        $this->assertCount(4, $crawler->filter('ul')->eq(0)->filter('li'));
        foreach ($crawler->filter('ul')->eq(0)->filter('li') as $li) {
            $lis[] = trim($li->textContent);
        }
        $this->assertPagination(1, 4, $crawler);

        $this->assertTrue(file_exists($webDir.'/index-page-2.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/index-page-2.html'));
        $this->assertCount(2, $crawler->filter('ul'));
        $this->assertCount(4, $crawler->filter('ul')->eq(0)->filter('li'));
        foreach ($crawler->filter('ul')->eq(0)->filter('li') as $li) {
            $lis[] = trim($li->textContent);
        }
        $this->assertPagination(2, 4, $crawler);

        $this->assertTrue(file_exists($webDir.'/index-page-3.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/index-page-3.html'));
        $this->assertCount(2, $crawler->filter('ul'));
        $this->assertCount(4, $crawler->filter('ul')->eq(0)->filter('li'));
        foreach ($crawler->filter('ul')->eq(0)->filter('li') as $li) {
            $lis[] = trim($li->textContent);
        }
        $this->assertPagination(3, 4, $crawler);

        $this->assertTrue(file_exists($webDir.'/index-page-4.html'));
        $crawler = new Crawler(file_get_contents($webDir.'/index-page-4.html'));
        $this->assertCount(2, $crawler->filter('ul'));
        $this->assertCount(3, $crawler->filter('ul')->eq(0)->filter('li'));
        foreach ($crawler->filter('ul')->eq(0)->filter('li') as $li) {
            $lis[] = trim($li->textContent);
        }
        $this->assertPagination(4, 4, $crawler);

        $this->assertFalse(file_exists($webDir.'/index-page-5.html'));

        sort($lis);

        $expected = array (
            'Page1',
            'Page10',
            'Page11',
            'Page12',
            'Page13',
            'Page14',
            'Page2',
            'Page3',
            'Page4',
            'Page5',
            'Page6',
            'Page7',
            'Page8',
            'Page9',
            'index',
        );

        $this->assertSame($expected, $lis);

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
        $this->assertSame('title:default-extends', $crawler->filter('h1')->text());
        $this->assertSame('Should be wrapped into a div', trim($crawler->filter('div.body')->text()));
    }

    private function runApplication($fixturesPath)
    {
        $carew = new Carew();
        $carew->setAutoExit(false);

        $application = new ApplicationTester($carew);

        $input = array(
            'command' => 'carew:build',
            '--base-dir' => $fixturesPath,
            '--web-dir' => $fixturesPath.'/web',
            '--verbose' => true,
        );

        $statusCode = $application->run($input);

        return array($application, $statusCode);
    }

    private function assertPagination($current, $size, Crawler $crawler)
    {
        $this->assertCount($size, $crawler->filter('ul')->eq(1)->filter('li'));

        for ($i = 1; $i <= $size; $i++) {
            $class = $current == $i ? 'active' : '';
            $this->assertSame($class, $crawler->filter('ul')->eq(1)->filter('li')->eq($i - 1)->attr('class'), sprintf('Class "active" is present only when $i == $current, ($i = %s, $current = %s)', $i, $current));
            $this->assertSame('page '.$i, $crawler->filter('ul')->eq(1)->filter('li')->eq($i - 1)->text(), sprintf('($i = %s, $current = %s)', $i, $current));
            $href = 1 == $i ? './index.html' : sprintf('./index-page-%s.html', $i);
            $this->assertSame($href, $crawler->filter('ul')->eq(1)->filter('li')->eq($i - 1)->filter('a')->attr('href'), sprintf('($i = %s, $current = %s)', $i, $current));
        }
    }
}
