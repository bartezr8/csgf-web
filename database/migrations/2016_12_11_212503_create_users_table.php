<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\User;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('username');
            $table->string('avatar');
            $table->string('steamid')->unique();
            $table->string('steamid64')->unique();
            $table->string('trade_link');
            $table->string('accessToken');
            $table->float('money');
            $table->float('slimit')->default(1.00);
            $table->integer('ban');
            $table->boolean('is_admin');
            $table->boolean('is_moderator');
            $table->string('remember_token', 100)->nullable();
            $table->integer('banchat');
            $table->integer('refprofit');
            $table->integer('refcount');
            $table->integer('refstatus');
            $table->integer('request');
            $table->string('refkode')->nullable();
            $table->text('password', 65535)->nullable();
            $table->timestamps();
        });
        
        $bonus = User::create(['username' => 'БОНУС БОТ', 'avatar' => 'https://csgf.ru/assets/img/bonus.png', 'steamid' => 'STEAM_0:1:00000000', 'steamid64' => '76561197960265728']);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }

}
