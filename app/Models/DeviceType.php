<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Cache;

class DeviceType extends AbstractEntity
{
    public const DESKTOP = 1;
    public const MOBILE = 2;
    public const TABLET = 3;

    protected $fillable = ['title'];

	public $timestamps = false;

	/**
	 * Получение инфо о типе устройства по названию типа устройства
	 *
	 * @param $title
	 * @param $no_cache
	 * @return bool|string
	 */
	public function getInfoByTitle($title, $no_cache = false)
	{
		$key = "device_type:getInfoByTitle:{$title}";

		if ($no_cache) {
            Cache::forget($key);
        }

		$info = Cache::get($key, function () use ($title, $key) {

			$info = self::where('title', $title)->first();

			if (is_null($info)) {
				throw new ModelNotFoundException('Failed to get device type');
			}

			$info = json_encode($info);

			Cache::forever($key, $info);

			return $info;
		});

		return json_decode($info, TRUE);
	}
}
