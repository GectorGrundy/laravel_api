<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\DynamicHiddenVisibleTrait;
use Illuminate\Database\Eloquent\{
    Model, ModelNotFoundException, SoftDeletes
};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Models\Traits\EloquentHashids;

class Integration extends AbstractEntity
{
    use SoftDeletes;
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    public const APPROVENINJA = 'Approveninja';

    protected $fillable = ['title', 'jobs', 'integration_data', 'info', 'is_active', 'is_works_all_time', 'schema'];
    protected $dates = ['deleted_at'];
    protected $appends = ['integration_data_array', 'schema_array'];
    protected $hidden = [
        'id', 'is_active', 'is_works_all_time', 'internal_api_key', 'integration_data', 'integration_data_array',
        'info', 'schema', 'schema_array', 'updated_at', 'deleted_at'
    ];
    public static $rules = [
        'title' => 'required|string|min:1',
        'is_active' => 'required|in:0,1',
        'is_works_all_time' => 'required|in:0,1',
        'integration_data' => 'required|json',
        'schema' => 'required|json',
        'info' => 'present|string',
    ];

    public function getIntegrationDataArrayAttribute()
    {
        if (isset($this->attributes['integration_data'])) {
            return json_decode($this->attributes['integration_data'], true);
        }
    }

    public function getSchemaArrayAttribute()
    {
        if (isset($this->attributes['schema'])) {
            return json_decode($this->attributes['schema'], true);
        }
    }

    public static function getHashidConnection(Model $model): string
    {
        return 'integration';
    }

    public static function getHashidColumn()
    {
        return 'internal_api_key';
    }

    public function worktime()
    {
        return $this->hasMany(CcIntegrationWorkTime::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeWorks(Builder $query)
    {
        return $query->leftJoin('cc_integration_work_time as wt', 'wt.integration_id', '=', 'integrations.id')
            ->where('wt.day', date('N', time()))
            ->where('wt.hour', date('G', time()))
            ->orWhere('is_works_all_time', 1);
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->active()->works();
    }

    public function scopeApiKey(Builder $query, string $internal_api_key)
    {
        return $query->where('internal_api_key', $internal_api_key);
    }

    public function getById(int $id): Integration
    {
        return self::findOrFail($id);
    }

    /**
     * Получение интеграции по internal_api_key
     *
     * @param $api_key
     * @return mixed
     */
    public function getByInternalApiKey($api_key)
    {
        return self::where('internal_api_key', $api_key)->firstOrFail();
    }

    public function getActiveByTitle(string $title): Collection
    {
        $integrations = self::where('title', $title)->active()->get();

        if ($integrations === null) {
            throw new ModelNotFoundException();
        }

        return $integrations;
    }
}
