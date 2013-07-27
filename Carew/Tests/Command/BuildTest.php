<?php

namespace Carew\Tests\Command;

use Carew\Carew;
use Symfony\Component\Console\Tester\ApplicationTester;
use Carew\Tests\AbstractTest;
use Symfony\Component\DomCrawler\Crawler;

class BuildTest extends AbstractTest
{
    public function testExecute()
    {
        $this->deleteDir($webDir = __DIR__.'/fixtures/site1/web');
        list(, $statusCode) = $this->runApplication(dirname($webDir));

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

    public function testConfigFolder()
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

    public function testConfigThrowExceptionIfExtensionClassDoesNotExists()
    {
        list($application, $statusCode) = $this->runApplication(__DIR__.'/fixtures/config-exception-class-not-exists');

        $this->assertSame(1, $statusCode);
        $this->assertContains('The class "FooBar" does not exists. See "config.yml".', $application->getDisplay());
    }

    public function testConfigThrowExceptionIfExtensionClassDoesNotImplementsInterface()
    {
        list($application, $statusCode) = $this->runApplication(__DIR__.'/fixtures/config-exception-class-not-implements');

        $this->assertSame(1, $statusCode);
        $this->assertContains('The class "stdClass" does not implements ExtensionInterface. See "config.yml".', $application->getDisplay());
    }

    public function testConfigTheme()
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

    public function runApplication($fixturesPath)
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
}
