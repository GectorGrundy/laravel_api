<?php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class LocaleServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot(Request $request)
	{
		if (is_null($request->get('locale'))) {
		    return;
		}

		switch ($request->get('locale')){
			case 'ru':
				app()->setLocale('ru');
				break;
		}
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
}
