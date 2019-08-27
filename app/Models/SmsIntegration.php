<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{
    Model, SoftDeletes
};
use App\Models\Traits\EloquentHashids;

class SmsIntegration extends AbstractEntity
{
    use SoftDeletes;
    use EloquentHashids;

    protected $fillable = ['is_active', 'title', 'info', 'extra'];
    protected $appends = ['extra_array'];
    protected $dates = ['deleted_at'];

    public static $rules = [
        'is_active' => 'required|in:0,1',
        'title' => 'required|string|max:255',
        'info' => 'present|string',
        'extra' => 'required|json',
    ];

    public function getExtraArrayAttribute()
    {
        return $this->attributes['extra_array'] = json_decode($this->attributes['extra'], true);
    }

    public static function getHashidConnection()
    {
        return 'integration';
    }

    public static function getHashidColumn(Model $model)
    {
        return 'internal_api_key';
    }
}
