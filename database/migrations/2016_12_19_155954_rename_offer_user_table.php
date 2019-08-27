<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameOfferUserTable extends Migration
{
	public function up()
	{
		Schema::rename('offer_user', 'offer_publisher');
	}

	public function down()
	{
		Schema::rename('offer_publisher', 'offer_user');
	}
}
