<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBotBetsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('bot_bets')) {
            Schema::table('bot_bets', function(Blueprint $table)
            {
                if (!Schema::hasColumn('bot_bets', 'id')) $table->increments('id');
                if (!Schema::hasColumn('bot_bets', 'id')) $table->integer('botid')->unsigned()->nullable();
                if (!Schema::hasColumn('bot_bets', 'id')) $table->integer('game_id')->unsigned()->nullable();
                if (!Schema::hasColumn('bot_bets', 'id')) $table->text('items', 65535);
                if (!Schema::hasColumn('bot_bets', 'id')) $table->timestamps();
                if (!Schema::hasColumn('bot_bets', 'id')) $table->integer('status');
                if (!Schema::hasColumn('bot_bets', 'id')) $table->integer('enum');
                if (!Schema::hasColumn('bot_bets', 'id')) $table->text('items_won', 65535);
            });
        } else {
            Schema::create('bot_bets', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('botid')->unsigned()->nullable();
                $table->integer('game_id')->unsigned()->nullable();
                $table->text('items', 65535);
                $table->timestamps();
                $table->integer('status');
                $table->integer('enum');
                $table->text('items_won', 65535);
            });
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bot_bets');
    }

}
