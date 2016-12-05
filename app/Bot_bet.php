<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{

    public function game()
    {
        return $this->belongsTo('App\Game');
    }
}
