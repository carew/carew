<?php

namespace Carew;

use Carew\Event\Listener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Twig_Environment;
use Twig_Loader_Filesystem;

class CoreExtension implements ExtensionInterface
{
    public function register(Carew $carew)
    {
        $container = $carew->getContainer();

        $this->registerConfig($container);
        $this->registerEventDispatcher($container);
        $this->registerTwig($container);

        $container['processor'] = $container->share(function($container) {
            return new Processor($container['event_dispatcher']);
        });

        $container['builder'] = $container->share(function($container) {
            return new Builder($container['twig'], $container['web_dir'], $container['filesystem']);
        });

        $container['filesystem'] = $container->share(function($container) {
            return new Filesystem();
        });

        $container['finder'] = function($container) {
            return new Finder();
        };
    }

    private function registerConfig(\Pimple $container)
    {
        $container['default.date'] = $container->protect(function() {
            return date('Y-m-d');
        });

        $container['web_dir'] = $container['base_dir'].'/web';

        $container['config'] = $container->share(function($container) {
            $config = array(
                'site'   => array(),
                'engine' => array(),
                'post'   => array(
                    'path_format' => '%year%/%month%/%day%/%slug%.html',
                ),
            );

            if (file_exists($container['base_dir'].'/config.yml')) {
                $config = array_replace_recursive($config, Yaml::parse($container['base_dir'].'/config.yml'));
            }

            return $config;
        });

        $container['themes'] = $container->share(function($container) {
            return array($container['base_dir']);
        });
    }

    private function registerEventDispatcher(\Pimple $container)
    {
        $container['event_dispatcher'] = $container->share(function($container) {
            $dispatcher =  new EventDispatcher();
            $dispatcher->addSubscriber(new Listener\Metadata\Extraction());
            $dispatcher->addSubscriber(new Listener\Metadata\Optimization($container['config']['post']['path_format']));
            $dispatcher->addSubscriber(new Listener\Body\UrlRewriter());
            $dispatcher->addSubscriber(new Listener\Body\Markdown());

            return $dispatcher;
        });
    }

    private function registerTwig(\Pimple $container)
    {
        $container['twig.loader'] = $container->share(function($container) {
            $loader = new Twig_Loader_Filesystem(array());

            foreach ($container['themes'] as $theme) {
                $path = $theme.'/layouts';
                if (is_dir($path)) {
                    $loader->addPath($path);
                }
            }
            $loader->addPath($container['base_dir']);

            return $loader;
        });

        $container['twig'] = $container->share(function($container) {
            $twig = new Twig_Environment($container['twig.loader'], array('strict_variables' => false, 'debug' => true));
            $twig->addExtension(new \Twig_Extension_Debug());

            $twigGlobales = array(
                'currentPath'  => '.',
                'document'     => new Document(),
                'documents'    => array(),
                'latest'       => false,
                'navigation'   => array(),
                'posts'        => array(),
                'relativeRoot' => '.',
                'site'         => $container['config']['site'],
                'tags'         => array(),
            );

            foreach ($twigGlobales as $key => $value) {
                $twig->addGlobal($key, $value);
            }

            return $twig;
        });
    }
}
