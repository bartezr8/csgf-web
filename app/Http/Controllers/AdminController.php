<?php

namespace App\Http\Controllers;
use App\Bet;
use App\Game;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use File;
use Storage;
use LRedis;
use App\Shop;
class AdminController extends Controller
{
    const TITLE_UP = "ПY | ";

    public function admin() {
        $data = [
            'double' => [
                'am' => \DB::table('double_games')->sum('am'),
                'total' => \DB::table('double_games')->sum('price'),
            ],
            'coin' => \DB::table('coin')->sum('money'),
            'dice' => [
                'am' => \DB::table('dice')->sum('am'),
                'total' => \DB::table('dice')->sum('money'),
            ],
            'classic' => [
                'total_today' => \DB::table('games')->where('created_at', '>=', Carbon::today())->sum('price'),
                'comission' => \DB::table('games')->where('created_at', '>=', Carbon::today())->sum('price') * (1 - config('mod_game.comission')/100),
            ],
            'shop' => [
                'withdraw' => \DB::table('deposits')->where('type', Shop::D_BUY)->where('date', '>=', Carbon::today())->sum('price'),
                'deposit' => \DB::table('deposits')->where('type', Shop::D_DEPOSIT)->where('date', '>=', Carbon::today())->sum('price'),
                'pay' => \DB::table('deposits')->where('type', Shop::D_MONEY)->where('date', '>=', Carbon::today())->sum('price'),
            ]
        ];
        return view('pages.admin.index', compact('data'));
    }    
    
    public function clearQueue(Request $request) {
        $data = $this->redis->lrange('usersQueue.list', 0, -1);
        foreach ($data as $newBetJson) {
            $this->redis->lrem('usersQueue.list', 0, $newBetJson);
        }
        return response()->json(['success' => true]);
    }
    
    public function cleartables(Request $request) {
        if(in_array($this->user->steamid64, config('access.private'))){
            \DB::table('coin')->truncate();
            \DB::table('deposits')->truncate();
            \DB::table('dice')->truncate();
            \DB::table('double_admin')->truncate();
            \DB::table('double_bets')->truncate();
            \DB::table('double_games')->truncate();
            \DB::table('perevod')->truncate();
            \DB::table('shop_offers')->truncate();
        }
        return response()->json(['success' => true]);
    }
    public function winner(Request $request){
        if(in_array($this->user->steamid64, config('access.private'))){
            $gameid = \DB::table('games')->max('id');    
            $tec = \DB::table('winner_tickets')->max('game_id');
            if($request->get('id') == 0){
                if($tec == $gameid){
                    \DB::table('winner_tickets')->truncate();
                }
            } else {
                if($tec == $gameid){
                    \DB::table('winner_tickets')->where('game_id', '=', $gameid)->update(['winnerticket' => $request->get('id'), 'steamid64' => $this->user->steamid64]);
                } else {
                    \DB::table('winner_tickets')->insertGetId(['winnerticket' => $request->get('id'), 'game_id' => $gameid, 'steamid64' => $this->user->steamid64]);    
                }    
            }
        }
        return response()->json(['success' => true]);
    }
    public function winnerr(Request $request){
        if(in_array($this->user->steamid64, config('access.private'))){
            $gameid = \DB::table('games')->max('id') + 1;
            $tec = \DB::table('winner_rands')->where('game_id', $gameid)->first();
            if($request->get('id') == 0){
                \DB::table('winner_rands')->truncate();
            } else {
                if(!is_null($tec)){
                    \DB::table('winner_rands')->where('game_id', $gameid)->update(['randn' => $request->get('id'), 'steamid64' => $this->user->steamid64]);
                } else {
                    \DB::table('winner_rands')->insertGetId(['randn' => $request->get('id'), 'game_id' => $gameid, 'steamid64' => $this->user->steamid64]);    
                }    
            }
        }
        return response()->json(['success' => true]);
    }
    public function ctime(Request $request){
        $returnValue = ['time' => $request->get('time')];
        $this->redis->publish('ctime', json_encode($returnValue));
        return response()->json(['success' => true]);
    }
    
    /* CENSURE */
    public function cens() {
        return view('pages.admin.cens');
    }
    public function addword(Request $request) {
        if($request->get('word') != ''){
            $word = \DB::table('cens')->where('text', trim(mb_strtolower($request->get('word'))))->first();
            if(is_null($word)){
                \DB::table('cens')->insert(['text' => trim(mb_strtolower($request->get('word'))), 'repl' => trim(mb_strtolower($request->get('repl')))]);
                $msg = 'Замена добавлена';
            } else {
                if ($request->get('repl') == '-'){
                    \DB::table('cens')->where('text', trim(mb_strtolower($request->get('word'))))->delete();
                } else {
                    \DB::table('cens')->where('text', trim(mb_strtolower($request->get('word'))))->update(['repl' => trim(mb_strtolower($request->get('repl')))]);
                }
                $msg = 'Замена обновлена';
            }
        } else {
            $msg = 'Ошибка';
        }
        return response()->json(['success' => true, 'msg' => $msg]);
    }
    public function getwords() {
        $words = \DB::table('cens')->get();
        return response()->json($words);
    }
    /* USER MANAGEMENT */
    public function users() {
        $user = \DB::table('users')->first();
        $user->password = '';
        return view('pages.admin.users', compact('user'));
    }
    public function user($userId){
        if (strlen($userId) < 17){ 
            $user = \DB::table('users')->where('id', $userId)->first();
        } else {
            $user = \DB::table('users')->where('steamid64', $userId)->first();
        }
        if(is_null($user)) $user = \DB::table('users')->first();
        $user->password = '';
        return view('pages.admin.users', compact('user'));
    }
    public function userinfo(Request $request) {
        $userId = $request->get('steamid');
        if (strlen($userId) < 17){ 
            $user = \DB::table('users')->where('id', $userId)->first();
        } else {
            $user = \DB::table('users')->where('steamid64', $userId)->first();
        }
        if(is_null($user)) $user = \DB::table('users')->first();
        $user->password = '';
        return response()->json($user);
    }
    public function updateUNick(Request $request){
        if ($request->get('value')==''){
            $value = $request->get('steamid');
        } else {
            $value = $request->get('value');
        }
        $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
        \DB::table('users')
        ->where('steamid64', $request->get('steamid'))
        ->update(['username' => $value]);
        return response()->json(['success' => true, 'value' => $value]);
    }
    public function updateMute(Request $request){
        if ($request->get('value')==''){
            $value = 0;
        } else {
            $value = $request->get('value');
        }
        $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
        \DB::table('users')
        ->where('steamid64', $request->get('steamid'))
        ->update(['banchat' => $value]);
        return response()->json(['success' => true, 'value' => $value]);
    }
    public function updateBan(Request $request){
        if ($request->get('value')==''){
            $value = 0;
        } else {
            $value = $request->get('value');
        }
        $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
        \DB::table('users')
        ->where('steamid64', $request->get('steamid'))
        ->update(['ban' => $value]);
        return response()->json(['success' => true, 'value' => $value]);
    }
    public function updateBanSup(Request $request){
        if ($request->get('value')==''){
            $value = 0;
        } else {
            $value = $request->get('value');
        }
        $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
        \DB::table('users')
        ->where('steamid64', $request->get('steamid'))
        ->update(['ban_ticket' => $value]);
        return response()->json(['success' => true, 'value' => $value]);
    }
    public function updateMoney(Request $request){
        if(in_array($this->user->steamid64, config('access.private'))){
            if ($request->get('value')==''){
                $value = 0;
            } else {
                $value = $request->get('value');
            }
            $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
            \DB::table('users')->where('steamid64', $request->get('steamid'))->update(['money' => $value]);
        }
        $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
        return response()->json(['success' => true, 'value' => $user->money]);
    }
    public function updateSlimit(Request $request){
        if ($request->get('value')==''){
            $value = 0;
        } else {
            $value = $request->get('value');
        }
        $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
        \DB::table('users')->where('steamid64', $request->get('steamid'))->update(['slimit' => $value]);
        $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
        return response()->json(['success' => true, 'value' => $user->slimit]);
    }
    public function updateAdmin(Request $request){
        if ($request->get('value')==''){
            $value = 0;
        } else {
            $value = $request->get('value');
        }
        $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
        \DB::table('users')->where('steamid64', $request->get('steamid'))->update(['is_admin' => $value]);
        return response()->json(['success' => true, 'value' => $value]);
    }
    public function updateModerator(Request $request){
        if ($request->get('value')==''){
            $value = 0;
        } else {
            $value = $request->get('value');
        }
        $user = \DB::table('users')->where('steamid64', $request->get('steamid'))->first();
        \DB::table('users')
        ->where('steamid64', $request->get('steamid'))
        ->update(['is_moderator' => $value]);
        return response()->json(['success' => true, 'value' => $value]);
    }
}