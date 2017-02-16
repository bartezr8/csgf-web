<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BBet extends Model
{
    protected $table = 'b_bets';
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function game()
    {
        return $this->belongsTo('App\BGame');
    }
}
