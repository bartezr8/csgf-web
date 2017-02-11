<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGiftsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('gifts'))Schema::create('gifts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable();
			$table->text('game_name', 65535);
			$table->text('game_link', 65535);
			$table->text('gift_link', 65535);
			$table->float('store_price');
			$table->float('buy_price');
			$table->integer('game_type');
			$table->dateTime('sold_at')->default('0000-00-00 00:00:00');
			$table->boolean('sold');
			$table->boolean('received');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('gifts');
	}

}
