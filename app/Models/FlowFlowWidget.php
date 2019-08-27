<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\EloquentHashids;

class FlowFlowWidget extends AbstractEntity
{
    use EloquentHashids;

    protected $fillable = ['hash', 'flow_id', 'flow_widget_id', 'attributes', 'is_moderated'];
    protected $hidden = ['id', 'flow_id'];
    protected $appends = ['attributes_array'];
    protected $table = 'flow_flow_widget';

    public function getAttributesArrayAttribute()
    {
        return json_decode($this->getAttribute('attributes'), true);
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(FlowWidget::class, 'flow_widget_id', 'id');
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }
}
