<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserActivityLog extends AbstractEntity
{
    protected $fillable = ['user_id', 'entity_id', 'entity_type', 'ip', 'user_agent', 'request'];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
