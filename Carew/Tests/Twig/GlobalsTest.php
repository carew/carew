<?php

namespace Carew\Tests\Twig;

use Carew\Twig\Globals;

class GlobalsTest extends \PHPUnit_Framework_TestCase
{
    public function testFromArray()
    {
        $globals = new Globals();

        $globals->fromArray(array(
            'relativeRoot' => '../',
            'foo' => 'bar',
        ));

        $this->assertSame($globals->relativeRoot, '../');
        $this->assertSame($globals->extra['foo'], 'bar');
    }
}
