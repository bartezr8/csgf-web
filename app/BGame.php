<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BGame extends Model
{
    const STATUS_NOT_STARTED = 0;
    const STATUS_PLAYING = 1;
    const STATUS_FINISHED = 3;

    const STATUS_PRIZE_WAIT_TO_SENT = 0;
    const STATUS_PRIZE_SEND = 1;
    const STATUS_PRIZE_SEND_ERROR = 2;

    protected $table = 'b_games';
    protected $fillable = ['rand_number'];

    public function winner()
    {
        return $this->belongsTo('App\User');
    }

    public function users()
    {
        return \DB::table('b_games')
            ->join('b_bets', 'b_games.id', '=', 'b_bets.b_game_id')
            ->join('users', 'b_bets.user_id', '=', 'users.id')
            ->where('b_games.id', $this->id)
            ->groupBy('users.id')
            ->select('users.*')
            ->get();
    }

    public static function gamesToday()
    {
        return self::where('status', self::STATUS_FINISHED)->where('created_at', '>=', Carbon::today())->count();
    }

    public static function lastGame()
    {
        $game = self::orderBy('id', 'desc')->first();
        $lastgame = $game->id-1;

        return $lastgame;
    }

    public static function usersToday()
    {
        return count(\DB::table('b_games')
            ->join('b_bets', 'b_games.id', '=', 'b_bets.b_game_id')
            ->join('users', 'b_bets.user_id', '=', 'users.id')
            ->where('b_games.created_at', '>=', Carbon::today())
            ->groupBy('users.username')
            ->select('users.username')->get());
    }

    public static function maxPriceToday()
    {
        return ($price = self::where('created_at', '>=', Carbon::today())->max('price')) ? $price : 0;
    }

    public static function maxPrice()
    {
        return round(self::max('price'));
    }

    public static function sumFW()
    {
        return ($price = self::where('created_at', '>=', Carbon::today()->subWeek())->sum('price')) ? $price : 0;
    }
    public static function sumFAT()
    {
        return ($price = self::sum('price')) ? $price : 0;
    }
    public function bets()
    {
        return $this->hasMany('App\BBet');
    }
}
