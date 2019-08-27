<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EloquentHashids;

class FlowGroup extends AbstractEntity
{
    use EloquentHashids;

    protected $fillable = ['publisher_id', 'title', 'color'];
    protected $hidden = ['id', 'publisher_id', 'created_at', 'updated_at'];

    public static function getHashidEncodingValue(Model $model)
    {
        return [$model->id, $model->publisher_id];
    }

    public function scopePublisher(Builder $builder, User $user)
    {
        return $builder->where('publisher_id', $user['id']);
    }
}
