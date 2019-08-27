<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasTranslations;
use Cache;
use App\Models\Traits\DynamicHiddenVisibleTrait;

class Country extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;
    use HasTranslations;

    public const IMAGE_PATH = 'storage/images/countries/';
    public const IT = 19;

    protected $fillable = [
        'title', 'code', 'timezone', 'currency_id', 'first_phone', 'mask', 'is_active'
    ];
    protected $hidden = ['first_phone', 'mask', 'is_active', 'timezone', 'created_at', 'updated_at', 'pivot'];
    protected $appends = ['thumb_path'];
    public $timestamps = false;

    public function getTitleAttribute($value)
    {
        return $this->getTranslatedAtribute('title', $value);
    }

    public function getThumbPathAttribute(): string
    {
        return '/' . $this->getThumbPath();
    }

    public function getThumbPath(): string
    {
        return self::IMAGE_PATH . strtolower($this->getAttribute('code')) . '.png';
    }

    public function translations()
    {
        return $this->hasMany(CountryTranslation::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function scopeActive($query)
    {
        return $query->where('countries.is_active', 1);
    }

    public function getByCode(string $code): Country
    {
        $key = __CLASS__ . __METHOD__ . $code;

        return Cache::get($key, function () use ($code, $key) {

            $country = Country::where('code', strtoupper($code))->firstOrFail();

            Cache::put($key, $country, 1440);

            return $country;
        });
    }

    public function getById(int $id): Country
    {
        $key = __CLASS__ . __METHOD__ . $id;

        return Cache::get($key, function () use ($id, $key) {

            $country = Country::findOrFail($id);

            Cache::put($key, $country, 1440);

            return $country;
        });
    }
}
