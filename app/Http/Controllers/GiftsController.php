<?php

namespace App\Http\Controllers;
use DB;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Cache;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class GiftsController extends Controller {
    public function gift_admin()
    {
        $gifts = DB::table('gifts')->get();
        return view('pages.out', compact('gifts'));
    }
    public function checkWinners(Request $request)
    {
        $ids = $request->get('users'); $users = [];
        foreach($ids as $id){
            $user = User::find($id);
            if(in_array($user,$users)) continue;
            $lastBet = $user->lastBet();
            if(is_null($lastBet)) continue;
            if((Carbon::parse($lastBet->created_at)->timestamp + 600) < Carbon::now()->timestamp) continue;
            $lastGift = DB::table('gifts')->where('user_id', $user)->first();
            if(!is_null($lastGift)) continue;
            $users[] = $user;
        }
        if(count($users) > 0){
            $giftsdb = DB::table('gifts')->where('game_type', '<', 4)->where('sold', 0)->get();
            if(count($giftsdb) > 0){
                $gifts = []; foreach($giftsdb as $gift) $gifts[] = $gift;
                $gift = $gifts[rand(0, (count($gifts) - 1))];
                $user = $user[rand(0, (count($user) - 1))];
                DB::table('gifts')->where('id', $gift->id)->update(['user_id' => $user->id, 'sold' => 1, 'sold_at' => Carbon::now()->toDateTimeString()]);
                $value = [
                    'steamid' => $user->steamid64,
                    'game_name' => $gift->game_name,
                    'store_price' => $gift->store_price,
                    'user_ava' => $user->avatar
                ];
                $this->redis->publish('gifts', json_encode($value));
            }
        }
    }
    public function receiveGift()
    {
        $gift = DB::table('gifts')->where('user_id', $this->user->id)->first();
        if(!is_null($gift)){
            DB::table('gifts')->where('id', $gift->id)->update(['received' => 1]);
            return redirect($gift->gift_link);
        }
        return redirect('/');
    }
}