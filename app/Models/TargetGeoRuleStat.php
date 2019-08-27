<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{
    Builder, Model
};

class TargetGeoRuleStat extends AbstractEntity
{
    protected $table = 'target_geo_rule_leads_stat';
    protected $fillable = ['target_geo_rule_id', 'leads_count', 'date'];
    public $timestamps = false;

    public function scopeToday(Builder $builder)
    {
        return $builder->where('date', date('Y-m-d', time()));
    }
}
