<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item_BP extends Model
{
    protected $table = 'items_backpack';
    protected $fillable = ['market_hash_name', 'price'];

}
