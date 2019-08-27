<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static active
 */
class Server extends AbstractEntity
{
    public const SLAVE = 'slave';
    public const MASTER = 'master';

    protected $fillable = ['title', 'user', 'host', 'is_active', 'type'];

    public function scopeActive(Builder $builder)
    {
        return $builder->where('is_active', 1);
    }

    public function scopeSlave(Builder $builder)
    {
        return $builder->where('type', self::SLAVE);
    }
}
