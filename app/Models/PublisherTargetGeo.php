<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Traits\DynamicHiddenVisibleTrait;

class PublisherTargetGeo extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;

    protected $fillable = [
        'target_geo_id', 'publisher_id', 'payout', 'price', 'old_price', 'hold_time',
    ];
    protected $hidden = ['id', 'target_geo_id', 'publisher_id'];
    public $table = 'publisher_target_geo';
    public $timestamps = false;

    public static $rules = [
        'publisher_id' => 'required|exists:users,id',
        'payout' => 'required|numeric|min:0',
        'price' => 'required|numeric|min:0',
        'old_price' => 'required|numeric|min:0',
        'hold_time' => 'required|numeric|min:0',
    ];

    public function target_geo()
    {
        return $this->hasOne(TargetGeo::class, 'id', 'target_geo_id');
    }

    public function publisher()
    {
        return $this->hasOne(User::class, 'id', 'publisher_id');
    }

    public function scopePublisherIds(Builder $builder, array $publisher_ids)
    {
        return $builder->when($publisher_ids, function (Builder $builder) use ($publisher_ids) {
            $builder->whereIn('publisher_id', $publisher_ids);
        });
    }

    public function scopeOfferIds(Builder $builder, array $offer_ids)
    {
        return $builder->when($offer_ids, function (Builder $builder) use ($offer_ids) {
            return $builder->whereHas('target_geo.offer', function (Builder $builder) use ($offer_ids) {
                return $builder->whereIn('id', $offer_ids);
            });
        });
    }
}
