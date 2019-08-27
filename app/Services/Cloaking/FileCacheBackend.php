<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use File;

class FileCacheBackend implements CacheBackendInterface
{
    public function fileExists($filename): bool
    {
        $filename = $this->getFilename($filename);

        return file_exists($filename) || file_exists($filename . '.html');
    }

    private function getFilename($file_path, $toGetFile = true)
    {
        $paths = array_map('rawurldecode', explode('/', $file_path));

        $full_path = implode('/', array_map('rawurlencode', $paths));

        $parser = app(Parser::class);
        if ($full_path{0} !== '/') {
            $full_path = "/{$full_path}";
        }
        $full_path = './' . $parser->cache_dir . "$full_path";

        if ((!file_exists($full_path) || is_dir($full_path)) && $toGetFile) {
            $full_path = "{$full_path}.html";
        }

        return $full_path;
    }

    public function saveFile($filename, $data, $mimeType = null, $url = null)
    {
        try {
            file_put_contents($this->getFilename($filename, false), $data);
        } catch (\Exception $e) {
            dd($e->getMessage(), 99);
        }
    }

    public function getFile($filename)
    {
        return file_get_contents($this->getFilename($filename));
    }

    public function createDir($path)
    {
        if (!File::isDirectory($path)) {
            mkdir($path, 0777, true);
        }
    }

    public function getPages()
    {
        $fileName = $this->getFilename(Parser::PAGES_FILE);
        if (file_exists($fileName)) {
            return file($fileName);
        }

        return array();
    }

    public function updateFile($fileName, $data)
    {
        $this->saveFile($fileName, $data);
    }

    public function clearCache($dir = null, $type = 'all')
    {
        switch ($type) {
            case 'all':
                $dir = ($dir) ? $dir : Parser::getCacheDir();
                $files = glob($dir . '/{.??*,.[!.],*}', GLOB_BRACE);
                if ($files) {
                    foreach ($files as $file) {
                        (is_dir($file)) ? CacheBackend::clearCache($file) : unlink($file);
                    }
                }
                unlink($dir . '/.html');

                rmdir($dir);
                $baseDirPath = dirname(__FILE__) . '/' . Parser::getCacheDir();
                rmdir($baseDirPath);

                break;

            case 'pages':
                function getFiles($parent = '/', &$out = array())
                {
                    $pages = file($parent . Parser::PAGES_FILE);
                    if ($pages) foreach ($pages as $page) {
                        $page = trim($page);
                        if ($page{strlen($page) - 1} === '/') {
                            getFiles($parent . $page, $out);
                        } else {
                            $out[] = $parent . $page;
                        }
                    }
                }

                ;
                $out = array();
                getFiles(Parser::$CACHE_DIR . '/', $out);

                foreach ($out as $file) {
                    unlink($file);
                }
                break;
        }

    }
}
