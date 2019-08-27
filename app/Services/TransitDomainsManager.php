<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Domain;
use App\Models\Transit;
use Illuminate\Support\Str;

/**
 * Методы управления доменами прелендинга
 */
class TransitDomainsManager
{
    public function create(Transit $transit, string $realpath)
    {
        $service_transit_domains = Domain::service()->transit()->get();

        foreach ($service_transit_domains as $system_domain) {

            $domain = $transit->subdomain . '.' . $system_domain->domain;

            Domain::create([
                'domain' => $domain,
                'type' => Domain::SYSTEM_TYPE,
                'entity_type' => Domain::TRANSIT_ENTITY_TYPE,
                'entity_id' => $transit->id,
                'realpath' => $realpath,
            ]);
        }
    }

    public function edit(Transit $transit, string $old_subdomain, string $realpath): void
    {
        $domains = Domain::transit()->whereEntity($transit->id)->get();

        foreach ($domains as $domain) {

            if ($domain->isSystem()) {
                // replace subdomain
                $domain->domain = Str::replaceLast($old_subdomain, $transit->subdomain, $domain->domain);
            }

            $domain->realpath = $realpath;
            $domain->save();
        }
    }
}
