<?php

namespace Carew;

use Carew\EventSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Twig_Environment;
use Twig_Loader_Filesystem;

class CoreExtension implements ExtensionInterface
{
    public function register(\Pimple $container)
    {
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
        $container['web_dir'] = $container->share(function() {
            return getcwd().'/web';
        });

        $container['base_dir'] = $container->share(function() {
            return getcwd();
        });

        $container['default.date'] = $container->protect(function() {
            return date('Y-m-d');
        });

        $container['config'] = $container->share(function($container) {
            $config = array(
                'site'   => array(),
                'engine' => array(),
            );

            if (file_exists($container['base_dir'].'/config.yml')) {
                $config = array_replace_recursive($config, Yaml::parse($container['base_dir'].'/config.yml'));
            }

            return $config;
        });
    }

    private function registerEventDispatcher(\Pimple $container)
    {
        $container['event_dispatcher'] = $container->share(function() {
            $dispatcher =  new EventDispatcher();
            $dispatcher->addSubscriber(new EventSubscriber\Metadata\Extraction());
            $dispatcher->addSubscriber(new EventSubscriber\Metadata\Optimization());
            $dispatcher->addSubscriber(new EventSubscriber\Body\Markdown());
            $dispatcher->addSubscriber(new EventSubscriber\Body\Toc());

            return $dispatcher;
        });
    }

    private function registerTwig(\Pimple $container)
    {
        $container['twig.loader'] = $container->share(function($container) {
            $loader = new Twig_Loader_Filesystem($container['base_dir'].'/layouts');

            $config = $container['config'];

            if (isset($config['engine']['theme_path'])) {
                $themePath = str_replace('%dir%', $container['base_dir'], $config['engine']['theme_path']);
                $loader->addPath($themePath.'/layouts');
                $loader->addPath($container['base_dir']);
            }

            return $loader;
        });

        $container['twigGlobales'] = $container->share(function($container) {
            return array(
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
        });

        $container['twig'] = $container->share(function($container) {
            $twig = new Twig_Environment($container['twig.loader'], array('strict_variables' => false, 'debug' => true));
            $twig->addExtension(new \Twig_Extension_Debug());

            foreach ($container['twigGlobales'] as $key => $value) {
                $twig->addGlobal($key, $value);
            }

            return $twig;
        });
    }
}
