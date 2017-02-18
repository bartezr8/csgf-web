<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\User;

class CreateMigrateTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('shop_offers', function ($table) {
            $table->integer('ecount');
        });

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){}

}
