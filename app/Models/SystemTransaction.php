<?php
declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EloquentHashids;

class SystemTransaction extends AbstractEntity
{
    use EloquentHashids;

    public const ADVERTISER_PROFIT_TYPE = 'advertiser.profit';
    public const LEAD_ENTITY_TYPE = 'lead';

    protected $fillable = [
        'user_id', 'user_role', 'type', 'sum', 'currency_id', 'entity_id', 'entity_type', 'description', 'profit_at'
    ];
    protected $dates = ['profit_at'];

    public static function createProfit(Lead $lead, float $profit, ?Carbon $profit_at = null): ?self
    {
        if (\is_null($profit_at)) {
            $profit_at = $lead['processed_at'];
        } else {
            $profit_at = $profit_at->toDateTimeString();
        }

        return self::create([
            'user_id' => $lead->advertiser['id'],
            'user_role' => $lead->advertiser['role'],
            'type' => self::ADVERTISER_PROFIT_TYPE,
            'sum' => $profit,
            'currency_id' => $lead['advertiser_currency_id'],
            'entity_id' => $lead['id'],
            'entity_type' => self::LEAD_ENTITY_TYPE,
            'description' => '',
            'profit_at' => $profit_at,
        ]);
    }

    public static function cancelLeadProfit(Lead $lead): ?self
    {
        $profit = -(float)$lead['profit'];

        return self::create([
            'user_id' => $lead->advertiser['id'],
            'user_role' => $lead->advertiser['role'],
            'type' => self::ADVERTISER_PROFIT_TYPE,
            'sum' => $profit,
            'currency_id' => $lead['advertiser_currency_id'],
            'entity_id' => $lead['id'],
            'entity_type' => self::LEAD_ENTITY_TYPE,
            'description' => '',
            'profit_at' => $lead['processed_at'],
        ]);
    }
}
