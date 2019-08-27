<?php
declare(strict_types=1);

namespace App\Listeners;

use DB;
use App\Events\Go\SiteVisited;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class InsertDataParameters implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;

	public function handle(SiteVisited $event)
	{
		if (!empty($event->data_container->getData1())) {

			DB::insert(
				"INSERT INTO `data_stats`
                    SET `publisher_id`  = :publisher_id,
                        `title`         = :title,
                        `type`          = 'data1',
                        `hits`          = 1
                    ON DUPLICATE KEY UPDATE
                        `hits`          = (`hits` + 1);",
				[
					'publisher_id' => $event->data_container->getFlow()['publisher_id'],
					'title' => $event->data_container->getData1()
				]
			);
		}

		if (!empty($event->data_container->getData2())) {

			DB::insert(
				"INSERT INTO `data_stats`
                    SET `publisher_id`  = :publisher_id,
                        `title`         = :title,
                        `type`          = 'data2',
                        `hits`          = 1
                    ON DUPLICATE KEY UPDATE
                        `hits`          = (`hits` + 1);",
				[
					'publisher_id' => $event->data_container->getFlow()['publisher_id'],
					'title' => $event->data_container->getData2()
				]
			);
		}

		if (!empty($event->data_container->getData3())) {

			DB::insert(
				"INSERT INTO `data_stats`
                    SET `publisher_id`  = :publisher_id,
                        `title`         = :title,
                        `type`          = 'data3',
                        `hits`          = 1
                    ON DUPLICATE KEY UPDATE
                        `hits`          = (`hits` + 1);",
				[
					'publisher_id' => $event->data_container->getFlow()['publisher_id'],
					'title' => $event->data_container->getData3()
				]
			);
		}

		if (!empty($event->data_container->getData4())) {

			DB::insert(
				"INSERT INTO `data_stats`
                    SET `publisher_id`  = :publisher_id,
                        `title`         = :title,
                        `type`          = 'data4',
                        `hits`          = 1
                    ON DUPLICATE KEY UPDATE
                        `hits`          = (`hits` + 1);",
				[
					'publisher_id' => $event->data_container->getFlow()['publisher_id'],
					'title' => $event->data_container->getData4()
				]
			);
		}
	}
}
