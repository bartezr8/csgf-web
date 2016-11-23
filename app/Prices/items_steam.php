<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item_Steam extends Model
{
    protected $table = 'items_steam';
    protected $fillable = ['market_hash_name', 'price'];

}
