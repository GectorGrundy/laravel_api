<?php
declare(strict_types=1);

namespace App\Models;

class FlowLanding extends AbstractEntity
{
    protected $table = 'flow_landing';
    protected $hidden = ['flow_id', 'landing_id'];
    protected $appends = ['landing_hash'];

    public function getLandingHashAttribute()
    {
        return $this->attributes['landing_hash'] = \Hashids::encode($this->attributes['landing_id']);
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }
}
