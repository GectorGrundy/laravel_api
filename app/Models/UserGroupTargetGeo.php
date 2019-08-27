<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @method $this whereGroup(int $user_group_id)
 * @method $this whereTargetGeo(array $target_geo_ids)
 */
class UserGroupTargetGeo extends AbstractEntity
{
    protected $fillable = ['user_group_id', 'target_geo_id', 'currency_id', 'payout',
                            'today_epc', 'yesterday_epc', 'week_epc', 'month_epc',
                            'today_cr', 'yesterday_cr', 'week_cr', 'month_cr',];
    protected $table = 'user_group_target_geo';

    public function scopeWhereGroup(Builder $builder, int $user_group_id): Builder
    {
        return $builder->where('user_group_id', $user_group_id);
    }

    public function scopeWhereTargetGeo(Builder $builder, array $target_geo_ids): Builder
    {
        return $builder->whereIn('target_geo_id', $target_geo_ids);
    }

    /**
     * Поиск кастомной ставки для гео цели со списка.
     *
     * @param Collection $custom_stakes
     * @param TargetGeo $target_geo
     * @return UserGroupTargetGeo|null
     */
    public static function findStakeByTargetGeo(Collection $custom_stakes, TargetGeo $target_geo): ?self
    {
        return $custom_stakes->where('target_geo_id', $target_geo['id'])->first();
    }
}
