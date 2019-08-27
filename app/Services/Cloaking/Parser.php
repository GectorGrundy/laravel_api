<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use App\Http\GoDataContainer;
use App\Models\CloakDomainPath;
use App\Models\DomainReplacement;
use App\Models\Domain;
use App\Models\Traits\StaticFileValidator;
use App\Services\Cloaking\Ms\MSConfig;
use App\Services\Cloaking\Ms\MSURL;
use App\Services\Cloaking\Ms\MSURLTransform;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use simplehtmldom_1_5\simple_html_dom;
use App\Services\Cloaking\Http\HTTP;

/**
 * Во всех классах, которые используются для работы клоакинга, нельзя использовать модели, коллекции и любой другой функционал,
 * который относится к конкретному проекту или фреймворку.
 *
 * Для конфигурации парсера используется метод configure().
 * Для хранения настроек парсера используется класс ParserSettings, который должен быть singleton.
 */
class Parser
{
    use StaticFileValidator;

    public const PAGES_FILE = 'dolly_pages';
    public const PAGES_CHARSET = 'UTF-8';
    public static $CACHE_DIR = 'storage/sites';
    public $cache_dir = 'storage/sites';
    public $equals = '';
    private $url = '';
    public $dom_parser;
    public $main_url = '';
    public $url_info = [];
    private $page_url;
    private $page_name;
    private $paths_class;
    private static $bad_url_params = [
        'utm_source', 'utm_medium', 'gclid', 'utm_campaing', 'yclid', 'utm_term', 'utm_content', 'utm_campaign',
    ];
    private $file_content;
    private $file_mime_type;
    private $http;
    private $slug;

    public function __construct(simple_html_dom $parser)
    {
        $this->dom_parser = $parser;
        $this->equals = new ParserEquals();

        $options = $this->getInitialOptions();

        $this->enableProxyOptions($options);
        $this->enableCookieOptions($options);
//        $this->enableOnRedirectOptions($options);

        $this->http = new HTTP($options);
    }

    public function configure(array $options): void
    {
        ParserSettings::setDonorUrl($options['donor_url']);
        ParserSettings::setDonorCharset($options['donor_charset']);
        ParserSettings::setCurrentDomain($options['current_domain']);
        ParserSettings::setReplacements($options['replacements'] ?? []);
    }

    public function parse($path): Response
    {
        if ($path === 'index.html') {
            $path = '/';
        }

        $this->slug = !starts_with($path, '/') ? '/' . $path : $path;

        $donor_url = ParserSettings::getDonorUrl() . $this->slug;
        $current_domain = ParserSettings::getCurrentDomain() . $this->slug;

        try {
            $this->handlePagePathAndUrl($current_domain, $donor_url);
            $this->createDirs();

            if (CacheBackend::fileExists($this->page_name)) {
                $this->file_content = CacheBackend::getFile($this->page_name);
                $this->file_mime_type = $this->getContentType($current_domain);
//                $this->file_content = $this->handlePage();

                return $this->returnFile();
            }

            $this->file_content = (string)$this->http->get($this->page_url, [], []);

            $this->file_mime_type = $this->http->getMime() ?: 'text/html';

            return $this->handleFileWithGoodHttpStatus();
        } catch (\Exception $e) {
            if (app()->isLocal()) {
                dd($e);
            }
            \Log::debug([
                'donor' => ParserSettings::getDonorUrl(),
                'REQUEST_URI' => $_SERVER['REQUEST_URI'],
                'HTTP_HOST' => $_SERVER['HTTP_HOST'],
            ]);

            return response('');
        }
    }

    private function getInitialOptions(): array
    {
        return [
            'accept_encoding' => 'gzip,deflate',
            'follow_location' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
            'cookie_file' => []
        ];
    }

    private function enableProxyOptions(&$options): void
    {
        $proxy = new FileSystemStorage('storage/proxyservers.php', [
            'readonly' => true,
            'root' => app_path('Services/Cloaking')
        ]);

        if (count($proxy)) {
            $options['proxy'] = $proxy;
        }
    }

    private function enableCookieOptions(&$options): void
    {
        foreach (['write', 'read'] as $i => $s) {
            $options['cookie_file'][$i] = "http_cookies_$s";

            if ($options['cookie_file'][$i] !== '' && '/' !== $options['cookie_file'][$i]) {
                if (strpos($options['cookie_file'][$i], 'file://') === 0) {
                    $options['cookie_file'][$i] = substr($options['cookie_file'][$i], 7);
                } else {
                    $options['cookie_file'][$i] = ParserSettings::get('sites_path')
                        . '/' . $options['cookie_file'][$i];// 7 = strlen('file://')
                }
            }
        }
    }

    private function enableOnRedirectOptions(&$options): void
    {
        $options['on_redirect'] = function ($r, $code, $new_url) {
            $url = $r->url;
            if ($url->path !== $new_url->path) {
                if ($url->host === $new_url->host || MSURL::IsSubdomainOf('www', $url->host, $new_url->host)) {
                    $transform = $this->getTransformUrl();
                } else {// !!! если хосты совсем не равны.
                    $transform = $this->getTransformUrl('*');
                }
                $transform->__invoke($new_url);
                HTTP::Redirect("$new_url", $code, false);
            }
        };
    }

    final public function getTransformUrl(...$hosts)
    {
        $config = ['scheme' => MSConfig::GetProtocol(''), 'host' => $_SERVER['HTTP_HOST']];
        $url = new MSURL(ParserSettings::getDonorUrl());
        $conf = [$url->host => $config];

        if ($host = MSURL::ToggleSubdomain('www', $url->host)) {
            $conf[$host] = $config;
        }

        foreach ($hosts as $host) {
            $conf[$host] = $config;
        }

        return new MSURLTransform($conf, $_SERVER['HTTP_HOST']);
    }

    public function getUrl()
    {
        return $this->page_url;
    }

    public function getPagePath()
    {
        return $this->page_name;
    }

    public static function getCacheDir()
    {
        return self::$CACHE_DIR;
    }

    public function getBaseDomain($pageUrl)
    {
        $parsed = parse_url($pageUrl);
        return $parsed['scheme'] . '://' . $parsed['host'];
    }

    public function createDirs()
    {
        if (strpos($this->page_name, '/')) {

            $dir = $this->deleteFileNameFromPath($this->page_name);

            CacheBackend::createDir("./{$this->cache_dir}/{$dir}");
        }

        return $this;
    }

    private function deleteFileNameFromPath($page)
    {
        $dir = explode('/', $page);
        unset($dir[count($dir) - 1]);

        $dir = implode('/', $dir);
        $dir = urldecode($dir);

        return $dir;
    }

    final public static function PageURL2Path($url)
    {
        $s = "$url";
        if ('' === $s || '/' === $s) {
            return 'index.html';
        }

        if ($s{0} === '/') {
            $s = substr($s, 1);
        }

        $s = urldecode($s);
        if ('/' === $s{strlen($s) - 1}) {
            $s .= 'index';
        } elseif (strpos($s, '.') === false && 'index' !== $s && substr($s, -6) !== '/index') {
            $s .= '/index';
        }

        if (false !== strpos($s, '?')) {
            $urlArray = explode('?', $s);
            $urlParamsArray = array();
            parse_str($urlArray[1], $urlParamsArray);

            foreach (self::$bad_url_params as $param) {
                unset($urlParamsArray[$param]);
            }

            if (count($urlParamsArray)) {
                $s = $urlArray[0] . '?' . http_build_query($urlParamsArray);
            }
        }

        return Paths::replaceSpecialChars($s);
    }

    public function handlePagePathAndUrl($page = null, $donor_url)
    {
        $this->page_name = Paths::clearStartSlash($page);
        $this->page_url = Paths::clearStartSlash($donor_url);

        $this->page_name = $this->paths()->handleEndSlashInPath($this->page_name);
        $this->page_name = urldecode($this->page_name);

        if (strpos($this->page_name, '.') === false
            && strpos($this->page_name, '/index') === false
            && $this->page_name !== 'index'
        ) {
            $this->page_name .= '/index';
        }

        $this->addIndexFileNameForEmptyPaths();
        $this->deleteBadParamsWithUrls();
        $this->replaceSpecialCharsInPathAndUrl();
        $this->handleOutAndSubDomains();
        $this->addIndexFileNameIfPathIsDir();
        $this->handleRelativePathInUrls();

        if (!$this->page_name) {
            $this->page_name = 'index.html';
        }

        return $this;
    }

    public function paths()
    {
        if (!isset($this->paths_class)) {
            $this->paths_class = new Paths();
        }
        return $this->paths_class;
    }

    private function addIndexFileNameForEmptyPaths()
    {
        if (!$this->page_name) {
            $this->page_name = 'index';
        }
    }

    private function deleteBadParamsWithUrls()
    {
        $url_array = explode('?', $this->page_name);
        if (count($url_array) > 1) {
            $url_params = [];
            parse_str($url_array[1], $url_params);

            foreach (self::$bad_url_params as $param) {
                unset($url_params[$param]);
            }

            $good_url_params = count($url_params) ? '?' . http_build_query($url_params) : '';
            $this->page_name = $url_array[0] . $good_url_params;
        }

        return $this;
    }

    private function replaceSpecialCharsInPathAndUrl()
    {
        $this->page_name = Paths::replaceSpecialChars($this->page_name);
        $this->page_url = Paths::replaceSpecialChars(urldecode($this->page_url), true);
        $this->page_url = str_replace(' ', '%20', $this->page_url);
    }

    private function handleOutAndSubDomains()
    {
        $base_domain = Paths::getBaseDomainArray($this->page_url);

        $this->page_url = $this->handleSubDomains($this->page_url, $base_domain);
        $this->page_url = $this->handleOutDomains($this->page_url);
    }

    private function handleSubDomains($pageUrl, $baseDomain)
    {
        if (0 === stripos($pageUrl, 's__')) {
            list($pageUrl) = Paths::getPageUrlForSubdomains($pageUrl, $baseDomain);
            return $pageUrl;
        }
        return $pageUrl;
    }

    private function handleOutDomains($pageUrl)
    {
        if (0 === strpos($pageUrl, 'o__')) {
            $pageUrl = substr_replace($pageUrl, 'http://', 0, 3);
            return $pageUrl;
        }
        return $pageUrl;
    }

    private function addIndexFileNameIfPathIsDir()
    {
        $lastCharNum = strlen($this->page_name) - 1;

        if ($this->page_name{$lastCharNum} === '/') {
            $this->page_name = $this->page_name . 'index';
        }
    }

    private function handleRelativePathInUrls(): void
    {
        if (ParserEquals::isRelativePath($this->page_url)) {
            if (!starts_with($this->page_url, '/')) {
                $this->page_url = '/' . $this->page_url;
            }
            $this->page_url = urldecode(ParserSettings::getDonorUrl() . $this->page_url);
        }
    }

    private function handleFileWithGoodHttpStatus(): Response
    {
        if ($this->file_mime_type === 'text/html') {
            return $this->processHtmlPage();
        }

        if ($this->file_mime_type === 'text/css') {
            $this->file_content = $this->parseCss();
        }

        return $this->processFile();
    }

    private function processHtmlPage(): Response
    {
        $this->handleHtmlPageBeforeSave();

        $this->saveHtmlPageToFile();

        $this->file_content = $this->handlePage();

        return $this->returnFile($this->http->getHttpCode());
    }

    private function processFile(): Response
    {
        $file_path = Paths::replaceSpecialChars($this->page_name);

        $this->saveFileIfIsNotIgnored($file_path);

        $this->file_content = $this->handlePage();

        return $this->returnFile($this->http->getHttpCode());
    }

    public function parseCss()
    {
        $css = new ParseCssPlugin($this);
        $css->setParams($this->page_url, $this->page_name, '');
        $content = new simple_html_dom();
        $content = $content->load("$this->file_content");
        $css->run($content);

        return (string)$content;

    }

    private function saveFileIfIsNotIgnored($filePath)
    {
        CacheBackend::saveFile($filePath, $this->file_content, $this->file_mime_type, $this->page_url);
    }

    private function handleImages($filePath)
    {
        $images = new Images();
        $images->fileName = $filePath;
//        $images->handleIfImage($this->file_content, $this->file_mime_type);
    }

    public function genUrlsForParse()
    {
        $this->url = ParserSettings::getDonorUrl();
        $base_domain = Paths::getBaseDomainArray($this->url);

        $subDomain = $this->genPageNameForSubAndOutDomainsAndReturnSubdomain($base_domain);
        $this->genPageUrl($subDomain);

        $this->replaceSlashesInPageName();

        return array($base_domain,
            $subDomain);
    }

    /**
     * @param $baseDomain
     * @return mixed
     */
    private function genPageNameForSubAndOutDomainsAndReturnSubdomain($baseDomain)
    {
        $subDomain = null;
        if ($this->equals->_thisPathIsSubdomain($this->page_name)) {
            list($this->page_name, $subDomain) = Paths::getPageUrlForSubdomains($this->page_name, $baseDomain);
        }
        $this->page_name = $this->paths()->_getUrlForOutDomain($this->page_name);

        return $subDomain;
    }

    private function genPageUrl($subDomain)
    {
        if (isset($subDomain)) {
            $this->_getPageForSubdomain($subDomain);
        } else if (ParserEquals::isRelativePath($this->page_name)) {
            $this->page_url = ParserSettings::getDonorUrl() . urldecode($this->page_name);
        } else {
            $this->page_url = urldecode($this->page_name);
        }
    }

    private function _getPageForSubdomain($subDomain)
    {
        $page = explode('/', $this->page_name);
        unset($page[0]);
        unset($page[1]);
        unset($page[2]);
        $page = trim(implode('/', $page));
        if (!$page or $page == '/') {
            $page = "index.html";
        }

        $this->page_url = $this->page_name;
        $this->page_name = "{$subDomain}/$page";
    }

    private function replaceSlashesInPageName()
    {
        if (@$this->page_name{0} === '/') {
            $this->page_name = substr($this->page_name, 1);
        }

        $this->page_name = str_replace('//', '/', $this->page_name);
    }

    private function handleHtmlPageBeforeSave()
    {
        $this->convertFileCharset();
        $this->replaceQuotesInFileContent();

        $this->file_content = Parser::_loadSimpleHtmlDom($this->file_content);
    }

    private function convertFileCharset()
    {
        $charset = ParserSettings::getDonorCharset();

        if (strtoupper($charset) !== self::PAGES_CHARSET) {
            $this->file_content = iconv($charset, self::PAGES_CHARSET, $this->file_content);
        }
    }

    private function replaceQuotesInFileContent()
    {
        $replaces = array('#=[\s]?"([^"]+)"#' => '="$1"',
            '#=[\s]?\'([^\']+)\'#' => '=\'$1\'');
        $this->file_content = preg_replace(array_keys($replaces), array_values($replaces), $this->file_content);
    }

    public static function _loadSimpleHtmlDom($str)
    {
        $dom = app(simple_html_dom::class);

        $dom->load($str);
        return $dom;
    }

    private function handlePage()
    {
        if (!\in_array($this->file_mime_type, [
            'text/html', 'text/css', 'text/plain', 'application/javascript'
        ])) {
            return $this->file_content;
        }

        $dom = Parser::_loadSimpleHtmlDom($this->file_content);

        $container = new PluginsContaier();
        $container
            ->addPlugin(new ReplaceLinks($this))
            ->addPlugin(new ParseCssPlugin($this))
            ->addPlugin(new ParseFiles($this));

        $container->setParams($this->page_url, $this->page_name);
        $container->run($dom);

        return $dom;
    }

    private function saveHtmlPageToFile(): void
    {
        $filename = $this->getOutputFileName();

        $this->replaceWords();

        CacheBackend::saveFile($filename, $this->file_content);
    }

    private function replaceWords()
    {
        $replacements = ParserSettings::getReplacements();

        foreach ($replacements as $replacement) {
            $this->file_content = str_replace($replacement['from'], $replacement['to'], $this->file_content);
        }
    }

    private function getOutputFileName(): string
    {
        $mime = $this->http->getMime() ?: 'text/html';
        $htmlExtension = (ParserEquals::needAddHtmlToFileName($mime, $this->page_name)) ? '.html' : '';
        $path = urldecode($this->page_name);
        $pathWithReplacedSpecialChars = Paths::replaceSpecialChars($path);

        return $pathWithReplacedSpecialChars . $htmlExtension;
    }

    private function returnFile($status = 200): Response
    {
        return response($this->file_content, $status, ['Content-Type' => $this->file_mime_type,]);
    }

    public function cacheDir($dir = null)
    {
        if ($dir) {
            $this->cache_dir = $dir;
        }
        return $this->cache_dir;
    }

    public function deletePage($host)
    {
        $path_to_cache_dir = Paths::replaceSpecialChars($host);

        Storage::deleteDirectory('public/sites/' . $path_to_cache_dir);
    }

    public function deleteCacheDir($host)
    {
        $path_to_cache_dir = Paths::replaceSpecialChars($host);

        Storage::deleteDirectory('public/sites/' . $path_to_cache_dir);
    }
}
