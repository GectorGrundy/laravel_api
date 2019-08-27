<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHoldFieldsToAdvertiserProfilesTable extends Migration
{
	public function up()
	{
		Schema::table('advertiser_profiles', function ($table) {
			$table->decimal('hold_usd', 12, 2)->after('balance_eur');
			$table->decimal('hold_rub', 12, 2)->after('hold_usd');
			$table->decimal('hold_eur', 12, 2)->after('hold_rub');
		});
	}

	public function down()
	{
		Schema::table('advertiser_profiles', function ($table) {
			$table->dropColumn(['hold_usd', 'hold_rub', 'hold_eur']);
		});
	}
}
