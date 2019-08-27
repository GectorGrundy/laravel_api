<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\{
    Domain, Landing
};
use Illuminate\Support\Str;

/**
 * Управление системными доменами лендинга
 */
class LandingDomainsManager
{
    public function create(Landing $landing, ?string $realpath, ?string $url): void
    {
        if ($landing['is_external']) {
            $this->createExternalDomain($landing, $url);
            return;
        }
        $this->createServiceDomains($landing, $realpath);
    }

    /**
     * Создание сервисных доменов для лендингов на нашей стороне
     *
     * @param Landing $landing
     * @param string $realpath
     */
    private function createServiceDomains(Landing $landing, string $realpath): void
    {
        $service_landing_domains = Domain::service()->landing()->get();

        foreach ($service_landing_domains as $service_domain) {
            $domain = $landing->subdomain . '.' . $service_domain->domain;

            Domain::create([
                'domain' => $domain,
                'type' => Domain::SYSTEM_TYPE,
                'entity_type' => Domain::LANDING_ENTITY_TYPE,
                'entity_id' => $landing->id,
                'realpath' => $realpath
            ]);
        }
    }

    /**
     * Создание домена для внешнего лендинга
     *
     * @param Landing $landing
     * @param string $url
     */
    private function createExternalDomain(Landing $landing, string $url): void
    {
        $url_info = parse_url($url);

        $query = $url_info['query'] ?? '' ? '?' . $url_info['query'] : '';

        Domain::create([
            'is_https' => (int)($url_info['scheme'] === 'https'),
            'domain' => $url_info['host'] . ($url_info['path'] ?? '') . $query,
            'type' => Domain::SYSTEM_TYPE,
            'entity_type' => Domain::LANDING_ENTITY_TYPE,
            'entity_id' => $landing['id'],
        ]);
    }

    public function edit(Landing $landing, string $old_subdomain, ?string $realpath, ?string $url): void
    {
        if ($landing['is_external']) {
            $this->editExternalDomain($landing, $url);
            return;
        }
        $this->editServiceDomains($landing, $realpath, $old_subdomain);
    }

    /**
     * Редактирование домена для внешнего лендинга
     *
     * @param Landing $landing
     * @param string $url
     */
    private function editExternalDomain(Landing $landing, string $url): void
    {
        $url_info = parse_url($url);

        $query = $url_info['query'] ?? '' ? '?' . $url_info['query'] : '';

        Domain::whereEntity($landing['id'])->update([
            'is_https' => (int)($url_info['scheme'] === 'https'),
            'domain' => $url_info['host'] . ($url_info['path'] ?? '') . $query,
        ]);
    }

    /**
     * Редактирование сервисных доменов для лендингов на нашей стороне
     *
     * @param Landing $landing
     * @param string $realpath
     */
    private function editServiceDomains(Landing $landing, string $realpath, string $old_subdomain): void
    {
        $domains = Domain::landing()->whereEntity($landing['id'])->get();

        foreach ($domains as $domain) {
            if ($domain->isSystem()) {
                $domain->domain = Str::replaceLast($old_subdomain, $landing->subdomain, $domain->domain);
            }

            $domain->realpath = $realpath;
            $domain->save();
        }
    }
}
