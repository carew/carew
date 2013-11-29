<?php

namespace Carew\Tests\Command\fixtures\extension;

use Carew\Carew;
use Carew\ExtensionInterface;

class MyExtension implements ExtensionInterface
{
    private static $called;

    public function register(Carew $container)
    {
        static::$called = true;
    }

    public static function isCalled()
    {
        return static::$called;
    }
}
