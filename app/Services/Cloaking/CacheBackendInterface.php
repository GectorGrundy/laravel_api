<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

interface CacheBackendInterface
{
    public function fileExists($filename);

    public function saveFile($filename, $data, $mimeType = null);

    public function getFile($filename);

    public function createDir($path);

    public function getPages();

    public function updateFile($fileName, $data);

    public function clearCache($dir = null);
}
