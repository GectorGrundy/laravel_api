<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{
    Model, SoftDeletes
};
use App\Models\Traits\EloquentHashids;

class CloakDomainPathCloakSystem extends AbstractEntity
{
    use EloquentHashids;
    use SoftDeletes;

    protected $fillable = ['cloak_domain_path_id', 'cloak_system_id', 'is_cache_result', 'attributes'];
    protected $hidden = ['id', 'cloak_domain_path_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['attributes_array'];
    protected $table = 'cloak_domain_path_cloak_system';

    public function getAttributesArrayAttribute()
    {
        return json_decode($this->getAttribute('attributes'), true);
    }

    public function cloak_system()
    {
        return $this->belongsTo(CloakSystem::class);
    }

    public function cacheEnabled()
    {
        return (bool)$this->is_cache_result;
    }
}
