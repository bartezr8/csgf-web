<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShopOffersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('shop_offers'))Schema::create('shop_offers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('tradeid');
			$table->integer('bot_id');
			$table->integer('user_id')->unsigned()->nullable()->index('shop_depositor_id_foreign');
			$table->integer('status');
			$table->float('price');
            $table->integer('ecount');
			$table->dateTime('date')->default('0000-00-00 00:00:00');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shop_offers');
	}

}
