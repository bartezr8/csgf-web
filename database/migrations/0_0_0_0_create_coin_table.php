<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('coin'))Schema::create('coin', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->float('money');
			$table->integer('creator')->unsigned();
			$table->integer('player')->unsigned();
			$table->integer('winner')->unsigned();
			$table->integer('status')->unsigned();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('coin');
	}

}
