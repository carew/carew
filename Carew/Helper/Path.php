<?php

namespace Carew\Helper;

class Path
{
    private static $extensionsToRewrite = array('md', 'rst');

    public function generatePath($path)
    {
        if ('/' === substr($path, -1)) {
            return ltrim($path, '/').'index.html';
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ('twig' === $extension) {
            $path = substr($path, 0, - (strlen($extension) + 1));
            $extension = pathinfo($path, PATHINFO_EXTENSION);
        }

        if ('' === $extension) {
            return ltrim($path, '/').'.html';
        }

        if (in_array(strtolower($extension), static::$extensionsToRewrite)) {
            $path = substr($path, 0, - (strlen($extension) + 1));

            return ltrim($path, '/').'.html';
        }

        return ltrim($path, '/');
    }
}
