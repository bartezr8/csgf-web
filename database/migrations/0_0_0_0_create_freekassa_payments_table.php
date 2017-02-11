<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFreekassaPaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('freekassa_payments'))Schema::create('freekassa_payments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('intid');
			$table->string('account');
			$table->string('P_EMAIL');
			$table->string('P_PHONE');
			$table->float('AMOUNT', 10, 0);
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
		Schema::drop('freekassa_payments');
	}

}
