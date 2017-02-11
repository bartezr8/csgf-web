<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGdonatePaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('gdonate_payments'))Schema::create('gdonate_payments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('gdonateId');
			$table->string('account');
			$table->float('sum', 10, 0);
			$table->integer('itemsCount')->default(1);
			$table->timestamp('dateCreate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->dateTime('dateComplete')->nullable();
			$table->boolean('status')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('gdonate_payments');
	}

}
