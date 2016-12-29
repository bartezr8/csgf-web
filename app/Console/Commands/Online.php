<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use LRedis;

class Online extends Command
{
    const COURSE = 65;
    const BANK_URL = 'http://www.cbr.ru/scripts/XML_daily.asp';
    
    const BP_URL = 'http://backpack.tf/api/IGetMarketPrices/v1/?key=';
    const FAST_URL = 'https://api.csgofast.com/price/all';
    
    protected $signature = 'online:change';

    protected $description = 'Changes online on site';

    public $redis;

    public function __construct()
    {
        parent::__construct();
        $this->redis = LRedis::connection();
    }

    public function  __destruct()
    {
        $this->redis->disconnect();
    }

    public function handle()
    {
        $this->add();
        sleep(rand(1,10));
        $this->del();
        sleep(rand(1,10));
        $this->add();
        sleep(rand(1,10));
        $this->del();
        sleep(rand(1,10));
        $this->add();
        sleep(rand(1,10));
        $this->del();
        sleep(rand(1,10));
        $this->add();
    }
    private function add(){
        $max = \DB::table('users')->max('id'); $id = rand(1, $max); $user = User::find($id);
        if(!is_null($user)) $this->redis->publish('fuser_add', json_encode($user->steamid64));
    }
    private function del(){
        $this->redis->publish('fuser_delone', json_encode(0));
    }
}