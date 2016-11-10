<?php

use App\Game;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGamesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('games', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('winner_id')->unsigned()->nullable()->index('games_winner_id_foreign');
			$table->integer('status');
			$table->integer('items');
			$table->float('price');
			$table->dateTime('started_at')->default('0000-00-00 00:00:00');
			$table->dateTime('finished_at')->default('0000-00-00 00:00:00');
			$table->text('won_items', 65535);
			$table->integer('status_prize');
			$table->string('rand_number');
			$table->timestamps();
			$table->float('chance');
			$table->text('comission', 65535);
			$table->text('msg', 65535);
		});
        
        $rand_number = "0.";
        $firstrand = mt_rand(20, 80);
        if (mt_rand(0, config('mod_game.game_low_chanse')) == 0) $firstrand = mt_rand(3, 96);
        if (mt_rand(0, (config('mod_game.game_low_chanse') * 2)) == 0) $firstrand = mt_rand(0, 9) . mt_rand(0, 9);
        if(strlen($firstrand) < 2) $firstrand = "0" . $firstrand;
        $rand_number .= $firstrand;
        for($i = 1; $i < 15; $i++) {
            $rand_number .= mt_rand(0, 9);
        }
        $rand_number .= mt_rand(1, 9);
		
        $game = Game::create(['rand_number' => $rand_number]);
        $game->hash = md5($game->rand_number);
        $game->rand_number = 0;
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('games');
	}

}
