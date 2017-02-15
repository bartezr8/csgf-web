<?php

namespace App\Http\Controllers;

use Auth;
use LRedis;
use App\User;
use App\CCentrifugo;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    public $user;
    public $redis;
    public $title;

    public function __construct()
    {
        $this->setTitle('Title not stated');
        if(Auth::check()){
            $this->user = Auth::user();
        } else {
            $this->user = User::find(1);
            if(is_null($this->user)){
                $this->user = User::create(['username' => 'БОНУС БОТ', 'avatar' => 'https://csgf.ru/assets/img/bonus.png', 'steamid' => 'STEAM_0:1:00000000', 'steamid64' => '76561197960265728']);
            }
        }
        $time = time();
        
        $token = CCentrifugo::generateToken($this->user->steamid64, $time, '');
        
        $this->redis = LRedis::connection();
        $this->redis->publish('new_user', $this->user->steamid64);
        
        $classic =  \DB::table('games')->orderBy('id', 'desc')->first()->price;
        $double =   \DB::table('double_games')->orderBy('id', 'desc')->where('status', 1)->first()->price ?? 0;
        $coin =     \DB::table('coin')->where('status', 0)->sum('money') ?? 0;
        
        $prices = [
            'classic' => $classic,
            'double' => $double,
            'coin' => $coin,
        ];
        
        view()->share('ctoken', $token);
        view()->share('ctime', $time);
        view()->share('prices', $prices);
        view()->share('u', $this->user);
        view()->share('steam_status', $this->getSteamStatus());
    }

    public function  __destruct()
    {
        $this->redis->disconnect();
    }

    public function setTitle($title)
    {
        $this->title = $title;
        view()->share('title', $this->title);
    }

    public function getSteamStatus()
    {
        $inventoryStatus = $this->redis->get('steam.inventory.status');
        $communityStatus = $this->redis->get('steam.community.status');

        if($inventoryStatus == 'normal' && $communityStatus == 'normal') return 'good';
        if($inventoryStatus == 'normal' && $communityStatus == 'delayed') return 'normal';
        if($inventoryStatus == 'critical' || $communityStatus == 'critical') return 'bad';
        return 'good';
    }
}
