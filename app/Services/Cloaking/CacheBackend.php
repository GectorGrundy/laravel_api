<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class CacheBackend
{
    protected static $_instance;

    public static function saveFile($fileName, $data, $mimeType = null, $url = null)
    {
        self::getInstance()->saveFile($fileName, $data, $mimeType, $url);
    }

    public static function getInstanceType()
    {
        return FileCacheBackend::class;
    }

    public static function getInstance()
    {
        if (!self::$_instance) {
            $adapterName = self::getInstanceType();
            self::$_instance = new $adapterName();
        }
        return self::$_instance;
    }

    public static function fileExists($fileName)
    {
        return self::getInstance()->fileExists($fileName);
    }

    public static function getFile($fileName)
    {

        return self::getInstance()->getFile($fileName);
    }

    public static function createDir($path)
    {
        self::getInstance()->createDir($path);
    }

    public static function getPages()
    {
        return self::getInstance()->getPages();
    }

    public static function updateFile($fileName, $data)
    {
        self::getInstance()->updateFile($fileName, $data);
    }

    public static function clearCache($dir = null, $type = 'all')
    {
        self::getInstance()->clearCache($dir, $type);
    }
}
