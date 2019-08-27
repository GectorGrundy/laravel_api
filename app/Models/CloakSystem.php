<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CloakSystem extends AbstractEntity
{
    public const TEST = 1;
    public const FRAUDFILTER = 2;
    public const KEITARO = 3;

    protected $fillable = ['title', 'schema'];
    protected $appends = ['schema_array'];

    public function getSchemaArrayAttribute()
    {
        return json_decode($this->getAttribute('schema'), true);
    }

    public function scopeWithoutTest(Builder $builder)
    {
        return $builder->where('id', '!=', self::TEST);
    }
}
