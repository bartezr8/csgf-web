<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['market_hash_name', 'price'];
    
    public static function pchk($item){
        if(is_null($item)) return false;
        if(!isset($item->price)) return false;
        if(!$item->price) return false;
        return true;
    }
}
