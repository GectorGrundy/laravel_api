<?php
declare(strict_types=1);

namespace App\Models;

class FlowTransit extends AbstractEntity
{
    protected $table = 'flow_transit';
    protected $hidden = ['flow_id', 'transit_id'];
    protected $appends = ['transit_hash'];

    public function getTransitHashAttribute()
    {
        return $this->attributes['transit_hash'] = \Hashids::encode($this->attributes['transit_id']);
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }

    public function transit()
    {
        return $this->belongsTo(Transit::class);
    }
}
