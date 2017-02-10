<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BBet extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function game()
    {
        return $this->belongsTo('App\BGame');
    }
}
