<?php

namespace Carew\Event\Listener\Terminate;

use Carew\Event\CarewEvent;
use Carew\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

class Assets implements EventSubscriberInterface
{
    private $themes;
    private $filesystem;

    public function __construct(array $themes = array(), Filesystem $filesystem)
    {
        $this->themes = $themes;
        $this->filesystem = $filesystem;
    }

    public function onTerminate(CarewEvent $event)
    {
        foreach ($this->themes as $theme) {
            $path = $theme.'/assets/';
            if (is_dir($path)) {
                $this->filesystem->mirror($path, $event['webDir'].'/', null, array('override' => true));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::TERMINATE => 'onTerminate',
        );
    }
}
