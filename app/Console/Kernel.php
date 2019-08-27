<?php
declare(strict_types = 1);

namespace App\Console;

use App\Console\Commands\DeleteExpiredTokens;
use App\Integrations\Approveninja\ApproveninjaOrderHistory;
use App\Integrations\Approveninja\ApproveninjaUpdateOrderList;
use App\Integrations\Fetchr\FetchrUpdateOrderList;
use App\Integrations\Finaro\FinaroUpdateOrderList;
use App\Integrations\LoremIpsuma\LoremIpsumaUpdateOrderList;
use App\Integrations\Monsterleads\MonsterleadsUpdateOrderList;
use App\Integrations\Mountainspay\MountainspaysUpdateOrderList;
use App\Integrations\Weblab\WeblabUpdateOrderList;
use App\Integrations\Webvork\WebvorkUpdateOrderList;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Middleware\Timezones;

class Kernel extends ConsoleKernel
{
    /**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		ApproveninjaUpdateOrderList::class,
		ApproveninjaOrderHistory::class,
		FetchrUpdateOrderList::class,
		FinaroUpdateOrderList::class,
		MountainspaysUpdateOrderList::class,
		MonsterleadsUpdateOrderList::class,
		LoremIpsumaUpdateOrderList::class,
		WeblabUpdateOrderList::class,
        DeleteExpiredTokens::class,
        WebvorkUpdateOrderList::class
    ];

    public function bootstrap()
    {
        parent::bootstrap();

        /**
         * @var Timezones $timezones
         */
        $timezones = app(Timezones::class);
        $timezones->initTimezones();
    }

    /**
     * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		if (!app()->isLocal()) {
			$schedule->command('approveninja:update_order_list')->everyMinute();
//            $schedule->command('approveninja:order.history')->everyMinute();

			$schedule->command('monsterleads:update_order_list new')->everyFiveMinutes();
			$schedule->command('monsterleads:update_order_list week')->daily();

			$schedule->command('loremipsuma:update_order_list new')->everyFiveMinutes();
			$schedule->command('loremipsuma:update_order_list 2_weeks')->daily();

			$schedule->command('weblab:update_order_list new')->everyFiveMinutes();
			$schedule->command('weblab:update_order_list week')->daily();

            $schedule->command('webvork:update_order_list new')->everyFiveMinutes();

            $schedule->command('target_geo:calc_coefficients today')->hourly();
			$schedule->command('target_geo:calc_coefficients yesterday')->daily();
			$schedule->command('target_geo:calc_coefficients week')->daily();
			$schedule->command('target_geo:calc_coefficients month')->daily();

			$schedule->command('landing:calc_coefficients today')->hourly();
			$schedule->command('landing:calc_coefficients yesterday')->daily();
			$schedule->command('landing:calc_coefficients week')->daily();
			$schedule->command('landing:calc_coefficients month')->daily();

			$schedule->command('transit:calc_coefficients today')->hourly();
			$schedule->command('transit:calc_coefficients yesterday')->daily();
			$schedule->command('transit:calc_coefficients week')->daily();
			$schedule->command('transit:calc_coefficients month')->daily();
		}

		$schedule->command('visitor:sync')->everyMinute();
		$schedule->command('currency:cache_rates')->daily();
		$schedule->command('lead:create_from_temp')->everyMinute();
		$schedule->command('leads:unhold')->everyMinute();
        $schedule->command('auth_tokens:delete_expired')->daily();
        $schedule->command('offer_labels:delete_expired')->everyTenMinutes();
        $schedule->command('api_methods:index')->dailyAt('04:00');
    }

	/**
	 * Register the Closure based commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		$this->load(__DIR__ . '/Commands');

		require base_path('routes/console.php');
	}
}
