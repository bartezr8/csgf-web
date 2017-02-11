<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWinnerRandsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('winner_rands'))Schema::create('winner_rands', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('randn');
			$table->integer('game_id');
			$table->string('steamid64')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('winner_rands');
	}

}
