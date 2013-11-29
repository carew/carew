<?php

namespace Carew\Tests;

use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    public static function deleteDir($dir)
    {
        $fs = new Filesystem();
        $fs->remove($dir);
    }
}
