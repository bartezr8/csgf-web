<?php

namespace App\Http\Controllers;
use DB;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Cache;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CCentrifugo;

class GiftsController extends Controller {
    public function gift_admin()
    {
        parent::setTitle('ПУ гифтами | ');
        $gifts = DB::table('gifts')->get();
        return view('pages.gifts', compact('gifts'));
    }
    public function checkWinners(Request $request)
    {
        if(config('mod_game.gifts')){
            $users = [];
            $online = CCentrifugo::presence('online')->getBody()['data'];
            foreach ($online as $data){
                if($data['user'] == config('mod_game.bonus_bot_steamid64')) continue;
                $user = User::where('steamid64', $data['user'])->first();
                if(in_array($user, $users)) continue;
                $lastBet = DB::table('bets')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
                if(is_null($lastBet)) continue;
                if((Carbon::parse($lastBet->created_at)->timestamp + 600) < Carbon::now()->timestamp) continue;
                $lastGift = DB::table('gifts')->where('user_id', $user->id)->first();
                if(!is_null($lastGift)) continue;
                $users[] = $user;
            }
            if(count($users) > 0){
                $giftsdb = DB::table('gifts')->where('game_type', '<', 3)->where('sold', 0)->get();
                if(count($giftsdb) > 0){
                    $gifts = []; foreach($giftsdb as $gift) $gifts[] = $gift;
                    $gift = $gifts[rand(0, (count($gifts) - 1))];
                    $user = $users[rand(0, (count($user) - 1))];
                    DB::table('gifts')->where('id', $gift->id)->update(['user_id' => $user->id, 'sold' => 1, 'sold_at' => Carbon::now()->toDateTimeString()]);
                    $value = [
                        'id' => $gift->id,
                        'steamid' => $user->steamid64,
                        'game_name' => $gift->game_name,
                        'store_price' => $gift->store_price,
                        'user_ava' => $user->avatar
                    ];
                    CCentrifugo::publish('gifts' , $value);
                }
            }
        }
        return response()->json(['sucsses']);
    }
    public function receiveGift()
    {
        $gift = DB::table('gifts')->where('user_id', $this->user->id)->where('received', 0)->first();
        if(!is_null($gift)){
            DB::table('gifts')->where('id', $gift->id)->update(['received' => 1]);
            return redirect($gift->gift_link);
        }
        return redirect('/');
    }
    public function selectGiftWinner(Request $request)
    {
        //if(config('mod_game.gifts')){
            $user = User::find($request->get('user'));
            $gift = DB::table('gifts')->where('id', $request->get('id'))->first();
            if(is_null($user) || is_null($gift)) return redirect('/gifts/admin');
            DB::table('gifts')->where('id', $gift->id)->update(['user_id' => $user->id, 'sold' => 1, 'sold_at' => Carbon::now()->toDateTimeString()]);
            $value = [
                'id' => $gift->id,
                'steamid' => $user->steamid64,
                'game_name' => $gift->game_name,
                'store_price' => $gift->store_price,
                'user_ava' => $user->avatar
            ];
            CCentrifugo::publish('gifts' , $value);
        //}
        return redirect('/gifts/admin');
    }
}