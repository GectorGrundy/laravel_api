<?php
declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PublisherStatistic extends AbstractEntity
{
    protected $fillable = [
        'publisher_id', 'flow_id', 'currency_id', 'hosts', 'payout', 'leads', 'approved_leads', 'datetime'
    ];
    protected $hidden = ['id', 'publisher_id', 'flow_id', 'datetime'];
    public $timestamps = false;

    public static function getDatetime(?string $datetime = null): string
    {
        $datetime = $datetime ? Carbon::createFromFormat('Y-m-d H:i:s', $datetime) : Carbon::now();
        $date = $datetime->format('Y-m-d');
        $hour = $datetime->format('H');

        $minutes = floor($datetime->format('i') / 10) * 10;

        if ($minutes < 10) {
            $minutes = 0 . $minutes;
        }
        $seconds = '00';

        return "$date $hour:$minutes:$seconds";
    }

    public function scopeCurrency(Builder $builder, int $currency_id)
    {
        return $builder->where('currency_id', $currency_id);
    }

    public function scopePublisher(Builder $builder, User $user)
    {
        return $builder->where('publisher_id', $user->id);
    }

    public function scopeCreatedBetween(Builder $builder, Carbon $date_from, Carbon $date_to)
    {
        return $builder->whereBetween('datetime', [$date_from->toDateTimeString(), $date_to->toDateTimeString()]);
    }
}
