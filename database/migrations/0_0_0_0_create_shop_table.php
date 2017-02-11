<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShopTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('shop'))Schema::create('shop', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('classid');
			$table->integer('bot_id');
			$table->string('inventoryId');
			$table->string('rarity')->nullable();
			$table->string('type')->nullable();
			$table->string('quality')->nullable();
			$table->integer('buyer_id')->unsigned()->nullable()->index('shop_buyer_id_foreign');
			$table->integer('status');
			$table->integer('sale');
			$table->float('steam_price');
			$table->float('price');
			$table->dateTime('buy_at')->default('0000-00-00 00:00:00');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shop');
	}

}
