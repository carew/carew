<?php

use Carew\Helper\Path;

class PathTest extends PHPUnit_Framework_TestCase
{
    public function getGeneratePathTests()
    {
        return array(
            array('simple.html', 'simple'),
            array('simple.html', 'simple.twig'),
            array('simple.html', 'simple.html'),
            array('simple.html', 'simple.html.twig'),
            array('simple.html', 'simple.md'),
            array('simple.html', 'simple.md.twig'),
            array('simple.js',   'simple.js'),
            array('simple.js',   'simple.js.twig'),
            array('folder/simple.html', 'folder/simple'),
            array('folder/simple.html', 'folder/simple.twig'),
            array('folder/simple.html', 'folder/simple.html'),
            array('folder/simple.html', 'folder/simple.html.twig'),
            array('folder/simple.html', 'folder/simple.md'),
            array('folder/simple.html', 'folder/simple.md.twig'),
            array('folder/simple.js',   'folder/simple.js'),
            array('folder/simple.js',   'folder/simple.js.twig'),
        );
    }

    /**
     * @dataProvider getGeneratePathTests
     */
    public function testGeneratePath($expected, $path)
    {
        $pathHelper = new Path();
        $this->assertSame($expected, $pathHelper->generatePath($path));
    }
}


