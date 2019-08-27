<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method $this hasUncompletedLeads()
 */
class AdvertiserProfile extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;

    protected $fillable = [
        'manager_id', 'full_name', 'skype', 'telegram', 'phone', 'whatsapp', 'info', 'unpaid_leads_count',
    ];
    protected $hidden = ['id', 'user_id', 'unpaid_leads_count', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function scopeHasUncompletedLeads(Builder $builder)
    {
        return $builder->where('unpaid_leads_count', '>', 0);
    }

    public function updateUnpaidLeadsCount(int $count)
    {
        $this->update([
            'unpaid_leads_count' => DB::raw("`unpaid_leads_count` + {$count}")
        ]);
    }
}
