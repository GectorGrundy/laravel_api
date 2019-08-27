<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Cache;
use Config;
use DB;
use Crypt;

class Database extends AbstractEntity
{

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'host', 'database', 'username', 'password', 'port', 'type'];


	/**
	 * Получаем инфо о БД
	 *
	 * @param $id
	 * @param bool $no_cache
	 * @return mixed
	 */
	public function getDbConfig($id, $no_cache = false)
	{
		$key = "database:getDbConfig:{$id}";

		if ($no_cache) {
            Cache::forget($key);
        }

		$config = Cache::get($key, function () use ($key, $id) {

			$config = Database::find($id);

			if (is_null($config)) {
				throw new ModelNotFoundException('Failed to get database configuration');
			}

			$config = json_encode($config);

			Cache::forever($key, $config);

			return $config;
		});

		return json_decode($config, true);
	}

	/**
	 * Установка коннекта для кастромного соединения mysql
	 *
	 * @param $config
	 */
	public function setDbConnectionConfig($config)
	{
		Config::set('database.connections.mysql_custom.host', $config['host']);
		Config::set('database.connections.mysql_custom.database', $config['database']);
		Config::set('database.connections.mysql_custom.username', $config['username']);
		Config::set('database.connections.mysql_custom.password', Crypt::decrypt($config['password']));
		Config::set('database.connections.mysql_custom.driver', 'mysql');
		Config::set('database.connections.mysql_custom.charset', 'utf8');
		Config::set('database.connections.mysql_custom.collation', 'utf8_unicode_ci');
		Config::set('database.connections.mysql_custom.prefix', '');
		Config::set('database.connections.mysql_custom.strict', false);
		Config::set('database.connections.mysql_custom.engine', null);
	}
}
