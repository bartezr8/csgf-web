<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePerevodTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('perevod'))Schema::create('perevod', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->float('money_amount');
			$table->string('money_to');
			$table->string('money_from');
			$table->string('money_id_to');
			$table->string('money_id_from');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('perevod');
	}

}
