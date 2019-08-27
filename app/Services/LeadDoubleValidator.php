<?php
declare(strict_types=1);

namespace App\Services;

use Hashids;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Класс для проверки заказа на дубль по указанному телефону, имени и гео цели за определенный промежуток времени.
 */
class LeadDoubleValidator
{
    public const ORDER_TIMEOUT_MINUTES = 30;

    public static function getLeadForParams(string $phone, string $target_geo_hash): ?string
    {
        return (new self)->validate($phone, $target_geo_hash);
    }

    private function validate(string $phone, string $target_geo_hash): ?string
    {
        $target_geo_id = Hashids::decode($target_geo_hash)[0] ?? 0;

        $created_at = Carbon::now()->subMinutes(self::ORDER_TIMEOUT_MINUTES)->toDateTimeString();

        return Lead::where('target_geo_id', $target_geo_id)
                ->whereHas('order', function (Builder $builder) use ($phone) {
                    return $builder->where('phone', $phone);
                })
                ->createdFrom($created_at)
                ->first()['hash'] ?? null;
    }

    public static function validateApiLead(int $flow_id, int $target_geo_id, string $phone)
    {
        $created_at = Carbon::now()->subMinutes(self::ORDER_TIMEOUT_MINUTES)->toDateTimeString();

        return Lead::where('target_geo_id', $target_geo_id)
                ->where('flow_id', $flow_id)
                ->whereHas('order', function (Builder $builder) use ($phone) {
                    return $builder->where('phone', $phone);
                })
                ->createdFrom($created_at)
                ->first()['hash'] ?? null;
    }
}