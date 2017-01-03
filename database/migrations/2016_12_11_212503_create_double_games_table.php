<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDoubleGamesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('double_games', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('num')->unsigned()->nullable();
            $table->integer('status')->unsigned();
            $table->bigInteger('price');
            $table->integer('am');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('double_games');
    }

}
