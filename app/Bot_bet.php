<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bot_bet extends Model
{

    protected $table = 'bot_bets';
    protected $fillable = ['botid', 'game_id', 'items'];
    
    public function game()
    {
        return $this->belongsTo('App\Game');
    }
}
