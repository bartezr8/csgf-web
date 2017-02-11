<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDiceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('dice'))Schema::create('dice', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->float('money');
			$table->integer('user_id')->unsigned();
			$table->string('bet_v');
			$table->integer('value')->unsigned();
			$table->float('am');
			$table->float('win');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('dice');
	}

}
