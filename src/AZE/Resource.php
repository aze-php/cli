<?php
namespace AZE;


class Resource
{
    private static $path = __DIR__ . "/../resources";

    static function setPath($path)
    {
        if (!file_exists($path)) {
            throw new \Exception('Resource path doesn\'t exist');
        }

        if (!is_readable($path)) {
            throw new \Exception('Resource path isn\'t readable');
        }

        self::$path = $path;
    }

    static function get($relativePath)
    {
        $fullPath = self::$path . DIRECTORY_SEPARATOR . $relativePath;

        if (!file_exists($fullPath)) {
            throw new \Exception('Resource file doesn\'t exist');
        }

        if (!is_readable($fullPath)) {
            throw new \Exception('Resource path isn\'t readable');
        }

        return file_get_contents($fullPath);
    }
}