<?php
declare(strict_types=1);

namespace App\Models;

class LeadStatusLog extends AbstractEntity
{
    protected $fillable = [
        'lead_id', 'integration_id', 'status', 'sub_status_id', 'external_key', 'foreign_changed_at', 'integration_type',
    ];
    protected $hidden = ['id', 'lead_id', 'integration_id', 'integration_type', 'updated_at'];

    public function integration()
    {
        return $this->morphTo();
    }

    public static function findLastForLead(Lead $lead)
    {
        return LeadStatusLog::where('lead_id', $lead['id'])
            ->latest('id')
            ->first();
    }
}
