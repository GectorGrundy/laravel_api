<?php

use Illuminate\Database\Migrations\Migration;

class AddFieldsToBalanceTransactionsTable extends Migration
{

	public function up()
	{
		Schema::table('balance_transactions', function ($table) {
			$table->string('entity_type')->after('user_id')->comment = 'lead';
			$table->integer('entity_id')->unsigned()->after('entity_type');
			$table->decimal('hold_sum', 12, 2)->after('sum');
			$table->renameColumn('sum', 'balance_sum');
		});
	}

	public function down()
	{
		Schema::table('balance_transactions', function ($table) {
			$table->renameColumn('balance_sum', 'sum');
			$table->dropColumn(['entity_type', 'entity_id', 'hold_sum']);
		});
	}
}
