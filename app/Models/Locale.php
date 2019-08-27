<?php
declare(strict_types=1);

namespace App\Models;

use Cache;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\DynamicHiddenVisibleTrait;

class Locale extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;

    public const IMAGE_PATH = 'storage/images/locales/';
    public const RU = 1;
    public const EN = 2;

    protected $fillable = ['title', 'title_ru', 'code', 'code3'];
    protected $appends = ['thumb_path'];
    public $timestamps = false;

    public function getThumbPathAttribute(): string
    {
        return '/' . $this->getThumbPath();
    }

    public function getThumbPath(): string
    {
        return self::IMAGE_PATH . $this->getAttribute('code') . '.png';
    }

    public function getById($id): self
    {
        $key = __CLASS__ . __METHOD__ . $id;

        return Cache::get($key, function () use ($id, $key) {

            $locale = self::findOrFail($id);

            Cache::forever($key, $locale);

            return $locale;
        });
    }

    public static function getByCode(string $code)
    {
        $key = __CLASS__ . __METHOD__ . $code;

        return Cache::get($key, function () use ($code, $key) {

            $locale = self::where('code', strtolower($code))->firstOrFail();

            Cache::forever($key, $locale);

            return $locale;
        });
    }
}