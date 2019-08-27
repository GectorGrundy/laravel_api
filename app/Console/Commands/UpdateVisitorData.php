<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Visitor;
use App\Models\Database;
use Cache;
use Redis;

class UpdateVisitorData extends Command
{
	protected $signature = 'visitor:sync';
	protected $description = 'Sync db visitor data from cache';

	public function handle()
	{
		// Получаем ключ, по которому хранятся все session_id посетителей
		$key = Visitor::$session_ids_cache_key;

		while ($session_id = Redis::connection('system')->spop($key)) {

			$decoded_data = \Hashids::connection('visitor')->decode($session_id);
			if (count($decoded_data) < 1) {
				continue;
			}

			$visitor_id = $decoded_data[0];

			$visitor_info = Visitor::find($visitor_id);
			if (is_null($visitor_info)) {
				continue;
			}

			$visitor_cached_data = Cache::get("visitor:getInfoBySessionId:{$session_id}");

			$visitor_info->update(['data' => $visitor_cached_data]);
		}
	}
}
