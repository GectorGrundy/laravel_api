<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\DynamicHiddenVisibleTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroup extends AbstractEntity
{
    use SoftDeletes;
    use DynamicHiddenVisibleTrait;

    public const DEFAULT_ID = 1;

    protected $fillable = ['title', 'description', 'color', 'is_default'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'pivot'];

    public function users()
    {
        return $this->hasMany(User::class, 'group_id');
    }
}
