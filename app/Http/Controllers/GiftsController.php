<?php

namespace App\Http\Controllers;
use DB;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Cache;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Storage;

class GiveOutController extends Controller {
    public function out_index()
    {
        $display = DB::table('gifts')->where('sold', 1)->orderBy('id', 'desc')->limit(20)->get();
        return view('pages.out', compact('display'));
    }
    private function _responseMessageToSite($message, $userid)
    {
        return $this->redis->publish(GameController::INFO_CHANNEL, json_encode([
            'steamid' => $userid,
            'message' => $message
        ]));
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
            DB::table('gifts')->where('game_type', '<', 4)->get();
        }
    }
    
}