<?php

namespace Carew\Tests;

use Carew\Carew;
use Carew\CoreExtension;
use Carew\Document;
use Carew\Event\Events;

class CoreExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $carew;
    private $coreExtension;

    public function setUp()
    {
        $this->carew = new Carew();
        $this->core = new CoreExtension();
    }

    public function testRegister()
    {
        $this->core->register($this->carew);

        $container = $this->carew->getContainer();
        $container['base_dir'] = __DIR__;

        $this->assertInstanceOf('Carew\Builder', $container['builder']);
        $this->assertInstanceOf('Carew\Processor', $container['processor']);
        $this->assertInstanceOf('Carew\Helper\Path', $container['helper.path']);
        $this->assertInstanceOf('Symfony\Component\Filesystem\Filesystem', $container['filesystem']);
        $this->assertInstanceOf('Symfony\Component\Finder\Finder', $container['finder']);
        $this->assertSame(array(
            'site' => array(),
            'engine' => array(
                'post_permalink_format' => '%year%/%month%/%day%/%slug%.html',
                'core_extensions' => array(
                    'toc' => true,
                    'feed' => true,
                ),
            ),
            'folders' => array(
                'posts' => Document::TYPE_POST,
                'pages' => Document::TYPE_PAGE,
                'api' => Document::TYPE_API,
            ),
        ), $container['config']);
        $this->assertSame(array(__DIR__), $container['themes']);
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventDispatcher', $container['event_dispatcher']);
        $this->assertInstanceOf('Carew\Event\Listener\Decorator\Twig', $container['listener.twig']);
    }

    public function testRegisterWithConfigDotYml()
    {
        $this->core->register($this->carew);

        $container = $this->carew->getContainer();
        $container['base_dir'] = __DIR__.'/fixtures';

        $this->assertSame(array(
            'site' => array(
                'author' => 'gregoire pineau',
            ),
            'engine' => array(
                'post_permalink_format' => '%slug%-%month%-%year%-%format%-foobar',
                'core_extensions' => array(
                    'toc' => false,
                    'feed' => true,
                ),
                'themes' => '%dir%/vendor',
            ),
            'folders' => array(
                'posts' => Document::TYPE_POST,
                'pages' => Document::TYPE_PAGE,
                'api' => Document::TYPE_API,
            ),
        ), $container['config']);
        $this->assertSame(array(__DIR__.'/fixtures', __DIR__.'/fixtures/vendor'), $container['themes']);
    }

    public function testRegisterTwigWithConfigDotYml()
    {
        $this->core->register($this->carew);

        $container = $this->carew->getContainer();
        $container['base_dir'] = __DIR__.'/fixtures';

        $loader = $container['twig.loader'];
        $this->assertInstanceOf('Twig_Loader_Filesystem', $loader);

        $this->assertSame(array(
            __DIR__.'/fixtures/vendor/layouts',
            realpath(__DIR__.'/../Twig/Resources/layouts'),
            __DIR__.'/fixtures',
        ), $loader->getPaths());

        $twig = $container['twig'];
        $this->assertInstanceOf('Twig_Environment', $twig);
        $globals = $twig->getGlobals();
        $this->assertInstanceOf('Carew\Twig\Globals', $globals['carew']);
        $this->assertTrue($twig->hasExtension('carew'));
    }

    public function testRegisterAndDisableSomeCorePlugins()
    {
        $this->core->register($this->carew);

        $container = $this->carew->getContainer();
        $container['base_dir'] = __DIR__.'/fixtures';

        $eventDispatcher = $container['event_dispatcher'];
        foreach ($eventDispatcher->getListeners(Events::DOCUMENT_BODY) as $listener) {
            $this->assertNotInstanceOf('Carew\Event\Listener\Body\Toc', $listener[0]);
        };
    }

    public function tearDown()
    {
        $this->carew = null;
        $this->core = null;
    }
}
