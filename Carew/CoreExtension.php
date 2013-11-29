<?php

namespace Carew;

use Carew\Event\Listener;
use Carew\Helper\Path;
use Carew\Twig\CarewExtension;
use Carew\Twig\Globals;
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

        $container['builder'] = $container->share(function ($container) {
            return new Builder($container['processor'], $container['config'], $container['themes'], $container['twig'], $container['filesystem'], $container['finder']);
        });

        $container['processor'] = $container->share(function ($container) {
            return new Processor($container['event_dispatcher'], $container['filesystem']);
        });

        $container['helper.path'] = $container->share(function ($container) {
            return new Path();
        });

        $container['filesystem'] = $container->share(function ($container) {
            return new Filesystem();
        });

        $container['finder'] = function ($container) {
            return new Finder();
        };
    }

    private function registerConfig(\Pimple $container)
    {
        $container['base_dir'] = getcwd();

        $container['config'] = $container->share(function ($container) {
            $config = array(
                'site' => array(),
                'engine' => array(
                    'post_permalink_format' => '%year%/%month%/%day%/%slug%.html'
                ),
                'folders' => array(
                    'posts' => Document::TYPE_POST,
                    'pages' => Document::TYPE_PAGE,
                    'api' => Document::TYPE_API,
                ),
            );

            if (file_exists($container['base_dir'].'/config.yml')) {
                $config = array_replace_recursive($config, Yaml::parse($container['base_dir'].'/config.yml') ?: array());
            }

            return $config;
        });

        $container['themes'] = $container->share(function ($container) {
            $themesPath = array($container['base_dir']);

            $config = $container['config'];
            if (isset($config['engine']['themes'])) {
                $themes = (array) $config['engine']['themes'];
                foreach ($themes as $theme) {
                    $themesPath[] = str_replace('%dir%', $container['base_dir'], $theme);
                }
            }

            return $themesPath;
        });
    }

    private function registerEventDispatcher(\Pimple $container)
    {
        $container['listener.twig'] = $container->share(function ($container) {
            return new Listener\Decorator\Twig($container['twig']);
        });
        $container['event_dispatcher'] = $container->share(function ($container) {
            $dispatcher =  new EventDispatcher();

            $dispatcher->addSubscriber(new Listener\Metadata\Extraction($container['helper.path']));
            $dispatcher->addSubscriber(new Listener\Metadata\Optimization($container['config']['engine']['post_permalink_format'], $container['helper.path']));
            $dispatcher->addSubscriber(new Listener\Body\Markdown());
            $dispatcher->addSubscriber(new Listener\Body\Toc());
            $dispatcher->addSubscriber(new Listener\Documents\Tags());
            $dispatcher->addSubscriber(new Listener\Documents\Feed());
            $dispatcher->addSubscriber(new Listener\Terminate\Assets($container['themes'], $container['filesystem']));
            $dispatcher->addSubscriber($container['listener.twig']);

            return $dispatcher;
        });
    }

    private function registerTwig(\Pimple $container)
    {
        $container['twig.loader'] = $container->share(function ($container) {
            $loader = new Twig_Loader_Filesystem(array());

           foreach ($container['themes'] as $theme) {
                if (is_dir($path = $theme.'/layouts')) {
                    $loader->addPath($path);
                }
            }
            $loader->addPath(__DIR__.'/Twig/Resources/layouts');
            $loader->addPath(__DIR__.'/Twig/Resources/layouts', 'default_theme');
            if (isset($container['config']['engine']['theme_base_dir'])) {
                if (!is_string($container['config']['engine']['theme_base_dir'])) {
                    throw new \InvalidArgumentException('The config.engine.theme_base_dir is not a string');
                }
                $loader->addPath(str_replace('%dir%', $container['base_dir'], $container['config']['engine']['theme_base_dir']));
            } else {
                $loader->addPath($container['base_dir']);
            }

            return $loader;
        });

        $container['twig'] = $container->share(function ($container) {
            $twig = new Twig_Environment($container['twig.loader'], array(
                'strict_variables' => true,
                'debug' => true,
                'base_template_class' => 'Carew\Twig\Template',
            ));

            // We will not be able to add new global in Twig 2.0, so we should declare everything now;
            $twig->addGlobal('carew', new Globals($container['config']));

            $twig->addExtension(new \Twig_Extension_Debug());
            $twig->addExtension(new \Twig_Extension_StringLoader());
            // We have to inject the container to avoid a cyclic dependency;
            $twig->addExtension(new CarewExtension($container));

            return $twig;
        });
    }
}
