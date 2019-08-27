<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Domain;
use App\Models\Transit;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransitUrlResolver
{
    public const INDEX_PAGE = 'index.html';

    public function getUrl(int $transit_id, string $path = '', array $params = [], int $domain_id = 0): string
    {
        $query_string = isset($path[0]) && $path[0] !== '/' ? '/' . $path : $path;

        if (count($params) > 0) {
            $query_string .= '?' . http_build_query($params);
        }

        if ($domain_id > 0) {

            try {
                $domain_info = Domain::findOrFail($domain_id);
                return $domain_info->host . $query_string;
            } catch (ModelNotFoundException $e) {
                // Domain is not unknown or configured
            }
        }

        $transit = Transit::with(['custom_domains'])->findOrFail($transit_id);
        if ($transit->custom_domains->count() > 0) {
            $custom_domain = $transit->custom_domains->random();
            return $custom_domain->host . $query_string;
        }

        // @todo Cache this
        $system_transit_domain = Domain::getTransitSystem($transit_id);

        return $system_transit_domain->host . $query_string;
    }
}