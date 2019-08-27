<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Domain;
use Illuminate\Http\Response;
use App\Http\GoDataContainer;
use App\Models\CloakDomainPath;
use App\Services\Cloaking\Parser;
use App\Factories\CloakSystemFactory;

class CloakingService
{
    public const FOREIGN_PARAM = 'lh';
    public const PATH_PARAM = 'path';
    public const FOREIGN_LANDING_TYPE = 'lt';
    public const COOKIE_NAME = 'safepage';
    public const COOKIE_TTL = 60;

    private $parser;
    private $data_container;

    public function __construct(Parser $parser, GoDataContainer $data_container)
    {
        $this->parser = $parser;
        $this->data_container = $data_container;
    }

    public function isSafePage(CloakDomainPath $path): bool
    {
        if ($path->isSafepage()) {
            return true;
        }

        if ($path->isMoneypageFor() && $this->getIsSafePageForMoneypageForStatus($path)) {
            return true;
        }

        if ((bool)config('env.force_moneypage', false)) {
            return false;
        }

        return $this->getIsSafePage($path);
    }

    private function getIsSafePage(CloakDomainPath $path)
    {
        if ($path->cloak->cacheEnabled() && request()->hasCookie(self::COOKIE_NAME)) {
            return request()->cookie(self::COOKIE_NAME) === 'true';
        }

        $job = CloakSystemFactory::getInstance($path->cloak->cloak_system['title'], $path->cloak['attributes_array']);

        return dispatch_now($job);
    }

    public function showPage(string $path, ?Domain $domain = null): Response
    {
        $current_domain = $this->data_container->getCurrentDomain();
        $replacements = $domain ? $domain->replacements->each->only(['from', 'to'])->toArray() : [];

        $this->parser->configure([
            'donor_charset' => $current_domain->donor_charset,
            'donor_url' => $current_domain->donor_url,
            'current_domain' => $current_domain->host,
            'replacements' => $replacements,
        ]);

        return $this->parser->parse($path);
    }

    private function getIsSafePageForMoneypageForStatus(CloakDomainPath $path): bool
    {
        $data_parameter = request()->input($path['data_parameter']);
        if (empty($data_parameter)) {
            return true;
        }

        $identifiers = explode("\n", $path['identifiers']);

        return !\in_array($data_parameter, $identifiers);
    }
}
