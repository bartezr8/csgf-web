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
        Schema::table('double_bets', function ($table) {
            $table->float('price')->change();
        });
        Schema::table('double_games', function ($table) {
            $table->float('price')->change();
            $table->float('am')->change();
        });
        Schema::table('shop', function ($table) {
            if (Schema::hasColumn('shop', 'sale2')) {
                $table->dropColumn('sale2');
            }
        });
        Schema::drop('parser_keys');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){}

}
