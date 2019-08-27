<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StaticFile extends AbstractEntity
{
    public const DEPOSIT = 'deposit';

    protected $fillable = ['entity_type', 'entity_id', 'path', 'info', 'content'];
    protected $hidden = ['content'];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
