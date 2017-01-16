<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShopOffersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('shop_offers')) {
            Schema::table('shop_offers', function(Blueprint $table)
            {
                if (!Schema::hasColumn('shop_offers', 'id')) $table->increments('id');
                if (!Schema::hasColumn('shop_offers', 'tradeid')) $table->string('tradeid');
                if (!Schema::hasColumn('shop_offers', 'bot_id')) $table->integer('bot_id');
                if (!Schema::hasColumn('shop_offers', 'user_id')) $table->integer('user_id')->unsigned()->nullable()->index('shop_depositor_id_foreign');
                if (!Schema::hasColumn('shop_offers', 'status')) $table->integer('status');
                if (!Schema::hasColumn('shop_offers', 'price')) $table->float('price');
                if (!Schema::hasColumn('shop_offers', 'date')) $table->dateTime('date')->default('0000-00-00 00:00:00');
            });
        } else {
            Schema::create('shop_offers', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('tradeid');
                $table->integer('bot_id');
                $table->integer('user_id')->unsigned()->nullable()->index('shop_depositor_id_foreign');
                $table->integer('status');
                $table->float('price');
                $table->dateTime('date')->default('0000-00-00 00:00:00');
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
        Schema::drop('shop_offers');
    }

}
