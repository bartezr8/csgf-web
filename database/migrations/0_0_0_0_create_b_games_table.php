<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBGamesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('b_games'))Schema::create('b_games', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('winner_id')->unsigned()->nullable()->index('games_winner_id_foreign');
			$table->integer('status');
			$table->integer('items');
			$table->float('price');
			$table->dateTime('started_at')->default('0000-00-00 00:00:00');
			$table->dateTime('finished_at')->default('0000-00-00 00:00:00');
			$table->text('won_items', 65535);
			$table->integer('status_prize');
			$table->string('rand_number');
			$table->timestamps();
			$table->float('chance');
			$table->text('comission', 65535);
			$table->text('msg', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('b_games');
	}

}
