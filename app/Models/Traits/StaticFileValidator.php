<?php
declare(strict_types=1);

namespace App\Models\Traits;

trait StaticFileValidator
{
    public static $extensions = [
        'png', 'gif', 'ico', 'jpg', 'jpeg', 'css', 'js', 'ttf', 'woff', 'woff2', 'eot', 'otf', 'svg', 'map',
        'mp4', 'avi', 'pdf', 'flw', 'swf'
    ];

    public function isStaticFile(string $filename)
    {
        [$extension] = explode('?', \File::extension($filename));
        if (str_contains($extension, '&')) {
            $extension = substr($extension, 0, strpos($extension, '&'));
        }

        return in_array($extension, self::$extensions);
    }

    public function getContentType(string $filename)
    {
        $mime_types = [
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'xhtml' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'less' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'xsl' => 'application/xslt+xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'zip' => 'application/zip',
            'tar' => 'application/x-tar',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'cur' => 'text/html',
            'woff' => 'application/font-woff',
            'ttf' => 'application/font-ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'otf' => 'application/font-otf',
            'torrent' => 'application/x-bittorrent',
        ];

        foreach ($mime_types as $key => $value) {
            if (strpos($filename, ".{$key}")) {
                return $value;
            }
        }

        return 'text/html';
    }
}
