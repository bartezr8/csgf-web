<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBotBetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('bot_bets'))Schema::create('bot_bets', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('botid')->unsigned()->nullable();
			$table->integer('game_id')->unsigned()->nullable();
			$table->text('items', 65535);
			$table->timestamps();
			$table->integer('status');
			$table->text('items_won', 65535);
			$table->integer('enum');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bot_bets');
	}

}
