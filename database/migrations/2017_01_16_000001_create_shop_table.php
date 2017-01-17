<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShopTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('shop')) {
            Schema::table('shop', function(Blueprint $table)
            {
                if (!Schema::hasColumn('shop', 'id')) $table->increments('id');
                if (!Schema::hasColumn('shop', 'name'))$table->string('name');
                if (!Schema::hasColumn('shop', 'classid'))$table->string('classid');
                if (!Schema::hasColumn('shop', 'bot_id'))$table->integer('bot_id');
                if (!Schema::hasColumn('shop', 'inventoryId'))$table->string('inventoryId');
                if (!Schema::hasColumn('shop', 'rarity'))$table->string('rarity')->nullable();
                if (!Schema::hasColumn('shop', 'type'))$table->string('type')->nullable();
                if (!Schema::hasColumn('shop', 'quality'))$table->string('quality')->nullable();
                if (!Schema::hasColumn('shop', 'buyer_id'))$table->integer('buyer_id')->unsigned()->nullable()->index('shop_buyer_id_foreign');
                if (!Schema::hasColumn('shop', 'status'))$table->integer('status');
                if (!Schema::hasColumn('shop', 'sale'))$table->integer('sale');
                if (!Schema::hasColumn('shop', 'steam_price'))$table->float('steam_price');
                if (!Schema::hasColumn('shop', 'price'))$table->float('price');
                if (!Schema::hasColumn('shop', 'buy_at'))$table->dateTime('buy_at')->default('0000-00-00 00:00:00');
            });
        } else {
            Schema::create('shop', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('name');
                $table->string('classid');
                $table->integer('bot_id');
                $table->string('inventoryId');
                $table->string('rarity')->nullable();
                $table->string('type')->nullable();
                $table->string('quality')->nullable();
                $table->integer('buyer_id')->unsigned()->nullable()->index('shop_buyer_id_foreign');
                $table->integer('status');
                $table->integer('sale');
                $table->float('steam_price');
                $table->float('price');
                $table->dateTime('buy_at')->default('0000-00-00 00:00:00');
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
        Schema::drop('shop');
    }

}
