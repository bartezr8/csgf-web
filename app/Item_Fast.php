<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item_Fast extends Model
{
    protected $table = 'items_fast';
    protected $fillable = ['market_hash_name', 'price'];

}
