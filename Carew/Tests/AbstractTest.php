<?php

namespace Carew\Tests;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Created an empty directory and return path to it.
     *
     * @return string a fullpath
     */
    public static function createTempDir()
    {
        $tmpDir = tempnam(sys_get_temp_dir(), 'carew_');
        unlink($tmpDir);
        mkdir($tmpDir);

        return $tmpDir;
    }

    /**
     * Deletes a directory recursively.
     *
     * @param string $dir directory to delete
     */
    public static function deleteDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS);
        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            if (is_dir($file)) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dir);
    }
}
