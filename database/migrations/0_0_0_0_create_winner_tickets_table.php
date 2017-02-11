<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWinnerTicketsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('winner_tickets'))Schema::create('winner_tickets', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->bigInteger('winnerticket');
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
		Schema::drop('winner_tickets');
	}

}
