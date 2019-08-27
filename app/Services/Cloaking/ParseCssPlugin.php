<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use simplehtmldom_1_5\simple_html_dom;

class ParseCssPlugin extends Plugin
{
    private $_fileContent = '';

    public function run(simple_html_dom &$dom)
    {
        $this->_fileContent = (string)$dom;
        $matches = $this->_getFilesInCss($this->_fileContent);
        $file = $this->_downloadFilesInCss($matches);

        $dom = Parser::_loadSimpleHtmlDom($file);
    }

    public function _getFilesInCss($content)
    {
        $re = "/[url]\([\"|\']*(.+)[\"|\']*\)/Uim";
        preg_match_all($re, $content, $matches);

        return $matches;
    }

    public function _downloadFilesInCss($matches)
    {
        $file = str_replace(ParserSettings::getDonorUrl(), '/', $this->_fileContent);

        foreach ($matches[1] as $url) {
            $url = str_replace(['\'', '"'], '', $url);

            if (ParserEquals::isNotIgnoredFiles($url)) {
                continue;
            }

            $isFile = (strpos($url, ';') === false OR
                strpos($url, ')') === false OR
                strpos($url, ' ') === false OR
                strpos($url, ',') === false OR
                strpos($url, '{') === false OR
                strpos($url, '}') === false);

            if ($isFile) {
                $file = $this->_replaceFilesPathInCssFiles($url, $file);
            }
        }
        $file = str_replace('_replaced_', '', $file);
        return $file;
    }

    private function _replaceFilesPathInCssFiles($url, $file)
    {
        if (ParserEquals::isSubdomain(Paths::getBaseDomainArray($url),
            Paths::getBaseDomainArray(ParserSettings::getDonorUrl()),
            $url)
        ) {
            $file = $this->handleDownloadedCssFromSubDomain($file, $url);
        } else if (ParserEquals::isRelativePath($url)) {
            if ($url{0} !== '/') {
                $url = "/{$url}";
            }
            $file = $this->_handleDownloadedCssFromRelativePath($file, $this->page_name, $url);
        } else {
            if (ParserSettings::get('otherImg')) {
                $file = str_replace($url, Paths::getSitesFilesUrlForSubdomain($url), $file);
            }
        }
        return $file;
    }

    private function handleDownloadedCssFromSubDomain($file, $url)
    {
        $file = str_replace($url, Paths::subdomainPath(Paths::getBaseDomainArray($url), $url), $file);
        return $file;
    }


    private function _handleDownloadedCssFromRelativePath($file, $href, $url)
    {
        if (strpos(" {$url}", '../')) {
            return $this->_handleCssWithUpLevelFile($file, $href, $url);
        }

        return $this->_handleCssWithThisLevelFile($file, $href, $url);
    }

    private function _handleCssWithUpLevelFile($file, $href, $url)
    {
        $hrefArray = $this->deleteDirsInPathForUpLevelCssFile($href, $url);
        $newPath = $this->getNewPathForUpLevelCssFile($url, $hrefArray);

        $file = str_replace($url, $newPath, $file);
        return $file;
    }

    private function deleteDirsInPathForUpLevelCssFile($href, $url)
    {
        $upLevelsCount = substr_count($url, '../');

        $hrefNewPathOnly = '/' . str_replace(ParserSettings::getDonorUrl(), '', $href);
        $hrefArray = explode('/', $hrefNewPathOnly);
        unset($hrefArray[count($hrefArray) - 1]);

        for ($i = 0; $i < $upLevelsCount; ++$i) {
            unset($hrefArray[count($hrefArray) - 1]);
        }
        return $hrefArray;
    }

    private function getNewPathForUpLevelCssFile($url, $hrefArray)
    {
        $hrefNew = implode('/', $hrefArray);
        $tempUrl = str_replace('../', '', $url);

        return "{$hrefNew}/{$tempUrl}";
    }

    private function _handleCssWithThisLevelFile($file, $href, $url)
    {
        if ($url{0} !== '/') {
            $hrefArray = $this->deleteFileNameForThisLevelCssFile($href);
            $newPath = $this->getNewPathForThisLevelCssFile($url, $hrefArray);

            $file = str_replace($url, $newPath, $file);
            return $file;
        }
        return $file;
    }

    private function deleteFileNameForThisLevelCssFile($href)
    {
        $hrefNewPathOnly = '/' . str_replace(ParserSettings::getDonorUrl(), '', $href);
        $hrefArray = explode('/', $hrefNewPathOnly);
        unset($hrefArray[sizeof($hrefArray) - 1]);
        return $hrefArray;
    }

    private function getNewPathForThisLevelCssFile($url, $hrefArray)
    {
        $replacedUrl = substr($url, 0, 4);
        $replacedUrl .= '_replaced_';
        $replacedUrl .= substr($url, 4);

        $newUrl = implode('/', $hrefArray);

        return "{$newUrl}/{$replacedUrl}";
    }
}
