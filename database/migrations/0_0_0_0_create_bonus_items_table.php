<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBonusItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('bonus_items'))Schema::create('bonus_items', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('item', 65535);
			$table->float('price');
			$table->integer('bot_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bonus_items');
	}

}
