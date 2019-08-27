<?php
declare(strict_types=1);

namespace App\Models;

use Cache;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method $this search($search)
 * @method $this whereIds(array $os_platform_ids)
 */
class OsPlatform extends AbstractEntity
{
	protected $fillable = ['title'];

	public $timestamps = false;

	public const ON_DUPLICATE_KEY_ERROR_CODE = 23000;

	/**
	 * Получение инфо о ОС устройства по ее названию
	 *
	 * @param $title
	 * @param $no_cache
	 * @return bool|string
	 */
	public function getInfoByTitleOrCreate($title, $no_cache = false)
	{
		$key = "os_platform:getInfoByTitleOrCreate:{$title}";

//        if ($no_cache) {
            Cache::forget($key);
//        }

		$info = Cache::get($key, function () use ($title, $key) {

			$info = self::where('title', $title)->first();

			if (is_null($info)) {

				try {

					$info = self::create(['title' => $title]);

				} catch (\PDOException $e) {

					if ($e->getCode() == self::ON_DUPLICATE_KEY_ERROR_CODE) {

						$info = self::where('title', $title)->first();
					}
				}
			}

			$info = json_encode($info);

			Cache::forever($key, $info);

			return $info;
		});

		return json_decode($info, TRUE);
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
