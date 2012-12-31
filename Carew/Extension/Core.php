<?php

namespace Carew\Extension;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Carew\EventSubscriber;

class Core implements ExtensionInterface
{
    public function register(\Pimple $container)
    {
        $container['default.date'] = $container->protect(function() {
            return date('Y-m-d');
        });

        $container['event_dispatcher'] = $container->share(function() {
            $dispatcher =  new EventDispatcher();
            $dispatcher->addSubscriber(new EventSubscriber\Metadata\Extraction());
            $dispatcher->addSubscriber(new EventSubscriber\Metadata\Optimization());
            $dispatcher->addSubscriber(new EventSubscriber\Body\Markdown());
            $dispatcher->addSubscriber(new EventSubscriber\Body\Toc());

            return $dispatcher;
        });

        $container['web_dir'] = $container->share(function() {
            return getcwd().'/web';
        });

        $container['base_dir'] = $container->share(function() {
            return getcwd();
        });

        $container['config'] = array(
            'site' => array(),
            'enginre' => array(),
        );

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

        $container['twig'] = $container->share(function($container) {
            $twig = new Twig_Environment($container['twig.loader'], array('strict_variables' => true, 'debug' => true));
            $twig->addExtension(new \Twig_Extension_Debug());

            return $twig;
        });

    }
}
