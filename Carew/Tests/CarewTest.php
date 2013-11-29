<?php

namespace Carew\Tests;

use Carew\Carew;
use Symfony\Component\Console\Tester\ApplicationTester;

class CarewTest extends \PHPUnit_Framework_TestCase
{
    private $carew;

    public function setUp()
    {
        $this->carew = new Carew();
        $this->carew->setAutoExit(false);
    }

    public function testInitialization()
    {
        $this->assertInstanceOf('Pimple', $this->carew->getContainer());
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventDispatcher', $this->carew->getEventDispatcher());
        $this->assertInstanceOf('Carew\Builder', $this->carew->getBuilder());
    }

    public function testRegisterExtension()
    {
        $extension = $this->getMock('Carew\ExtensionInterface');
        $extension
            ->expects($this->once())
            ->method('register')
            ->with($this->carew)
        ;

        $this->carew->registerExtension($extension);
    }

    public function testRunWithoutConfiguration()
    {
        $application = new ApplicationTester($this->carew);
        $statusCode = $application->run(array());
        $this->assertSame(0, $statusCode);
    }

    public function testRunWithWrongBaseDir()
    {
        $application = new ApplicationTester($this->carew);
        $statusCode = $application->run(array('--base-dir' => '/foo/bar/baz'));
        $this->assertSame(1, $statusCode);
        $this->assertContains('Base directory does not exist or it is not a readable directory: "/foo/bar/baz".', $application->getDisplay());
    }

    public function testRunWithExtension()
    {
        $application = new ApplicationTester($this->carew);
        $statusCode = $application->run(array(
            'command' => 'list',
            '--base-dir' => __DIR__.'/Command/fixtures/extension',
        ));
        $this->assertSame(0, $statusCode);
        $this->assertTrue(\Carew\Tests\Command\fixtures\extension\MyExtension::isCalled());
    }

    public function testRunWithConfigThrowExceptionIfExtensionClassDoesNotExists()
    {
        $application = new ApplicationTester($this->carew);
        $statusCode = $application->run(array(
            'command' => 'list',
            '--base-dir' => __DIR__.'/Command/fixtures/config-exception-class-not-exists',
        ));
        $this->assertSame(1, $statusCode);
        $this->assertContains('The class "FooBar" does not exists. See "config.yml".', $application->getDisplay());
    }

    public function testRunWithConfigThrowExceptionIfExtensionClassDoesNotImplementsInterface()
    {
        $application = new ApplicationTester($this->carew);
        $statusCode = $application->run(array(
            'command' => 'list',
            '--base-dir' => __DIR__.'/Command/fixtures/config-exception-class-not-implements',
        ));
        $this->assertSame(1, $statusCode);
        $this->assertContains('The class "stdClass" does not implements ExtensionInterface. See "config.yml".', $application->getDisplay());
    }

    public function tearDown()
    {
        $this->carew = null;
    }
}
