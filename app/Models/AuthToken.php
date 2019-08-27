<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\EloquentHashids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

/**
 * @method $this whereNotAdmin()
 * @method $this whereUser(int $id)
 */
class AuthToken extends AbstractEntity
{
    use EloquentHashids;

    protected $fillable = ['token', 'user_id', 'admin_id', 'ip', 'user_agent', 'last_activity'];
    protected $hidden = ['id', 'user_id', 'admin_id', 'token', 'updated_at'];
    public $timestamps = ['last_activity'];
    protected $appends = ['is_current'];

    public function updateLastActivity()
    {
        $this->last_activity = Carbon::now();
        $this->save();
    }

    public function getIsCurrentAttribute()
    {
        return (int)(\request('auth_token')['hash'] === $this->attributes['hash']);
    }

    public function scopeWhereNotAdmin(Builder $builder)
    {
        return $builder->where('admin_id', 0);
    }

    public function scopeWhereUser(Builder $builder, int $id)
    {
        return $builder->where('user_id', $id);
    }
}
