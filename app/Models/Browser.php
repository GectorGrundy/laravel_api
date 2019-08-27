<?php
declare(strict_types=1);

namespace App\Models;

use Cache;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method $this search($search)
 * @method $this whereIds(array $browser_ids)
 */
class Browser extends AbstractEntity
{
    protected $fillable = ['title'];
    public $timestamps = false;

    public function getInfoByTitleOrCreate(string $title, $no_cache = false): self
    {
        $key = "browser:getInfoByTitleOrCreate:{$title}";

//        if ($no_cache) {
            Cache::forget($key);
//        }

        return Cache::get($key, function () use ($title, $key) {

            $browser = self::where('title', $title)->first();

            if (\is_null($browser)) {
                try {
                    $browser = self::create(['title' => $title]);
                } catch (\PDOException $e) {
                    if ((int)$e->getCode() === self::ON_DUPLICATE_KEY_ERROR_CODE) {
                        $browser = self::where('title', $title)->first();
                    }
                }
            }

            Cache::forever($key, $browser);

            return $browser;
        });
    }

    public function scopeSearch(Builder $builder, $search){

        $search = strtolower($search);
        return $builder->where(\DB::raw('LOWER(title)'), 'like', "%{$search}%");
    }

    public function scopeWhereIds(Builder $builder, array $ids)
    {

        if (!empty($ids)) {
            return $builder->wherein('id', $ids);
        }

        return $builder;
    }
}
