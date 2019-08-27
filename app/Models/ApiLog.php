<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method $this whereUser(array $user_hashes)
 * @method $this whereMethod(array $methods)
 * @method $this search($search)
 */
class ApiLog extends AbstractEntity
{
    protected $fillable = [
        'user_id', 'admin_id', 'request_method', 'api_method', 'request', 'response_code', 'response', 'user_agent', 'ip'
    ];

    public function admin()
    {
        return $this->belongsTo(Administrator::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWhereUser(Builder $builder, array $user_hashes): Builder
    {
        if (\count($user_hashes)) {
            $builder->whereIn('user_id', getIdsByHashes($user_hashes));
        }
        return $builder;
    }
    public function scopeWhereMethod(Builder $builder, array $methods): Builder
    {
        if (\count($methods)) {
            $builder->whereIn('api_method', $methods);
        }
        return $builder;
    }
}
