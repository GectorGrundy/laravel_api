<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use simplehtmldom_1_5\simple_html_dom;

class ParseFiles extends Plugin
{
    public function run(simple_html_dom &$dom)
    {
        $this->_parseCssFiesAndReplaceLinks($dom);
        $this->_parseScriptsFiesAndReplaceLinks($dom);
        $this->_replaceImagesSrcUrls($dom);
        $dom->save();

    }

    protected function _parseCssFiesAndReplaceLinks(&$page)
    {
        $css = $page->find('link[rel="stylesheet"]');
        foreach ($css as $style) {
            if (!$style->href) {
                continue;
            }

            $old = $style->href;

            $return = $this->_parseFile($style->href, $old, 'css');

            if (ParserEquals::isRelativePath($return[0])) {

                $href = $this->_getNewFileNameFromFilesWithExtension($return[0], 'css');
                $style->href = $href;
            } else {
                $style->href = $return[0];
            }
            // $style->href = str_replace('./', '/', $style->href);
            $style->type = 'text/css';
        }
    }

    public function _parseFile(&$href, &$old, $type = 'css')
    {
        $domain = parse_url($href, PHP_URL_HOST);
        $domain = explode('.', $domain);
        $urlHasEndSlash = ($this->donor_url{strlen($this->donor_url) - 1} === '/');
        $baseUrl = ($urlHasEndSlash) ? substr($this->donor_url, 0, -1) : $this->donor_url;
        $baseDomain = parse_url($this->donor_url, PHP_URL_HOST);
        $baseDomain = explode('.', $baseDomain);
        $old = $href;
        if (ParserEquals::isSubdomain($domain, $baseDomain, $href)) {
            $href = Paths::subdomainPath($domain, $href);
        } else if (strpos($href, $baseUrl) === 0) {
            $href = Paths::rusToLat(str_replace($baseUrl, '', $href));
        } else if (strpos($href, 'http://') === false and strpos($href, 'https://') === false) {
            if ($this->subdomain) {
                $tempUrl = explode('//', $baseUrl);
                $domainHasWww = (strpos($tempUrl[1], 'www') === 0 AND stripos($baseUrl, 'www') === false);
                $tempUrl[1] = ($domainHasWww) ? str_replace('www.', substr($this->subdomain, 4) . '.', $tempUrl[1])
                    : substr($this->subdomain, 4) . '.' . $tempUrl[1];
                $old = "{$tempUrl[0]}//{$tempUrl[1]}{$href}";
            } else {
                $old = $baseUrl . '/' . $href;
            }
            $href = $this->subdomain . Paths::rusToLat($href);
            $href = ($href{0} === '.' and strpos($href, '../') === false) ? substr($href, 1) : $href;
        } else {
            if (strpos($href, '//') === 0) {
                $old = 'http:' . $href;
                $href = Paths::rusToLat($old);
            }
            if (!$this->subdomain AND ParserEquals::isNotGoogleFiles($href)) {
                if (ParserSettings::get('otherCss') AND in_array($type, array('css',
                        'js'))
                ) {
                    $href = Paths::getSitesFilesUrlForSubdomain($href);
                }
                if (ParserSettings::get('otherImg') AND $type == 'img') {
                    $href = Paths::getSitesFilesUrlForSubdomain($href);
                }
            }
        }

        return array($href, Paths::clearStartSlash($old));
    }

    protected function _getNewFileNameFromFilesWithExtension($oldHref, $extension)
    {
        $href = Paths::replaceSpecialChars($oldHref);
        $href = trim($href);

        $extensionInHref = (strrpos($href, ".{$extension}") > 0);
        $extensionInHrefEnd = (strpos($href, ".{$extension}") == (strlen($href) - (strlen($extension) + 1)));
        $fileHasExtension = ($extensionInHref AND $extensionInHrefEnd);
        $href = ($fileHasExtension) ? $href : "{$href}.{$extension}";

        return $href;
    }

    // @todo Что это делает? Почему и что пишется в _scripts?
    protected function _parseScriptsFiesAndReplaceLinks(&$page)
    {
        $js = $page->find('script');
        foreach ($js as $script) {

//            if (ParserSettings::get('cacheBackend') == 'File') {
            if (true) {
                $scr = $script->outertext;
                $fileName = Parser::$CACHE_DIR . '/_scripts';
                if (!file_exists($fileName)) {
                    file_put_contents($fileName, ' ');
                }
                $file = file_get_contents($fileName);

                if (strpos($file, $scr) === false) {
                    file_put_contents($fileName, PHP_EOL . $scr . '#D_END_SCRIPTS#', FILE_APPEND);
                }
            }
            if (!$script->src) {
                continue;
            }
            $old = $script->src;
            $return = $this->_parseFile($script->src, $old, 'js');
            if (ParserEquals::isRelativePath($return[0])) {
                $script->src = $this->_getNewFileNameFromFilesWithExtension($return[0], 'js');
            } else {
                continue;
            }
        }
    }

    protected function _replaceImagesSrcUrls(&$page)
    {
        $images = $page->find('img');
        foreach ($images as $image) {
            if (!$image->src) {
                continue;
            }
            $old = $image->src;
            $return = $this->_parseFile($image->src, $old, 'img');
            if (ParserEquals::isRelativePath($return[0])) {
                $image->src = $return[0];
            } else {
                continue;
            }
        }
        return $page;
    }
}
