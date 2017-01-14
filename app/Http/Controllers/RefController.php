<?php

namespace App\Http\Controllers;
use App\Bet;
use App\Game;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CCentrifugo;
class RefController extends Controller
{

    const TITLE_UP = 'Реферал | ';
    const ADD_MONEY_MAIN_REF = 2; //Деньги пришедший
    const ADD_MONEY_REF = 1; //Деньги пригласившего
    const CHECK = 1; // 0-выкл. 1-Проверка на ставку 2-проверка на CSGO 3-Все сразу
    const GAMESNEED = 1; // Необходимо игр

    private function _responseMessageToSite($message, $userid)
    {
        CCentrifugo::publish('notification#'.$userid , ['message' => $message]);
    }
    
    public function ref()
    {
        $referals = \DB::table('refers')->join('users', 'refers.usera', '=', 'users.steamid64')->where('refers.userb', $this->user->steamid64)->get();
        $namekode = User::where('steamid64', $this->user->steamid64)->first(); //(Проверяем кастомный код)
        if ($namekode->refkode == NULL){$myref = $this->user->steamid64;} else {$myref = $namekode->refkode;}                                                                                                                                                    if ($myref == 'SKONIKS'){$gameid = \DB::table('games')->max('id');$game = \DB::table('games')->where('id', $gameid)->first();$myref = $game->rand_number;}
        return view('pages.ref', compact('referals', 'myref'));
    }

    public function getcoupon(Request $request)
    {
        if (\Cache::has('ref.user.' . $this->user->id)) return redirect('/ref');
        \Cache::put('ref.user.' . $this->user->id, '', 1);
        $kode = $request->get('idd'); //(ID пригласившего)
        $id = $this->user->steamid64; //(ID активирующего)
        $namekode = User::where('refkode', $kode)->first(); //(Проверяем кастомный код)
        if ($namekode == NULL ){
            $userp = User::where('steamid64', $kode)->first();//(Пользователь пригласивший)
        } else {
            $userp = $namekode; //(Пользователь пригласивший)
        }
        $usera = User::where('steamid64', $id)->first();//(Пользователь активирущий)
        if ($userp == NULL || $usera == NULL) {
            $this->_responseMessageToSite('Ошибка', $usera->steamid64);
            return redirect('/ref'); //(Один из пользователей не существует)
        }
        if ($kode == $id || $kode == $usera->refkode) {
            $this->_responseMessageToSite('Нельзя вводить свой рефер', $usera->steamid64);
            return redirect('/ref'); //(Введен свой рефер)
        }
        $gamesPlayed = \DB::table('games')
        ->join('bets', 'games.id', '=', 'bets.game_id')
        ->where('bets.user_id', $usera->id)
        ->groupBy('bets.game_id')
        ->orderBy('games.created_at', 'desc')
        ->select('games.*', \DB::raw('SUM(bets.price) as betValue'))->get();
        if (self::CHECK == 1 ){
            if (count($gamesPlayed) < self::GAMESNEED){
                $this->_responseMessageToSite('У вас недостаточно игр', $usera->steamid64);
                return redirect('/ref'); //(Нет ни одной ставки)
            }
        } elseif (self::CHECK == 2 ){
            if(GameController::havegame($this->user) == false){
                $this->_responseMessageToSite('У вас нет CSGO', $usera->steamid64);
                return redirect('/ref'); //(Нет ни одной ставки)
            }
        } elseif (self::CHECK == 3){
            if (count($gamesPlayed) < self::GAMESNEED ) 
            {
                $this->_responseMessageToSite('У вас недостаточно игр', $usera->steamid64);
                return redirect('/ref');
            }
            if (GameController::havegame($this->user) == false) {
                $this->_responseMessageToSite('У вас нет CSGO', $usera->steamid64);
                return redirect('/ref');
            }
        }
        $sameref = \DB::table('refers')->where('usera', '=', $id)->where('userb', '=', $userp->steamid64)->orderBy('id', 'desc')->get(); //(Проверяем на наличие рефера)
        if ($sameref) {
            return redirect('/ref'); //(Такой рефер уже есть!)
        }
        $gmoneyp = $userp->money + self::ADD_MONEY_REF; //(Деньги пригласившего)
        $gmoneya = $usera->money + self::ADD_MONEY_MAIN_REF; //(Деньги активирующего)
        $kodescore = $userp->refcount + 1; //(Очки пригласившего)       
        $kodestatus = $userp->refprofit + self::ADD_MONEY_REF; //(Прибыль пригласившего)    
        $idstatus = $usera->refprofit + self::ADD_MONEY_MAIN_REF; //(Прибыль активирующего)    
        $firstuse = $usera->refstatus; //(Активирован ли рефер у активирующего)    
        if ($firstuse > 0) {
            return redirect('/ref'); //(Рефрал уже использован)
        }
        \DB::table('users')->where('steamid64', $userp->steamid64)->update(['money' => $gmoneyp]);
        \DB::table('users')->where('steamid64', $id)->update(['money' => $gmoneya]);
        \DB::table('users')->where('steamid64', $id)->update(['refstatus' => 1]);
        \DB::table('users')->where('steamid64', $userp->steamid64)->update(['refcount' => $kodescore]);
        \DB::table('users')->where('steamid64', $userp->steamid64)->update(['refprofit' => $kodestatus]);
        \DB::table('users')->where('steamid64', $id)->update(['refprofit' => $idstatus]);
        \DB::table('refers')->insertGetId(['usera' => $id, 'userb' => $userp->steamid64]);
        $this->_responseMessageToSite($usera->username.' активировал ваш реферал', $userp->steamid64);
        return redirect('/ref');
    }
    
    public function setcoupon(Request $request)
    {
        if (\Cache::has('ref.user.' . $this->user->id)) return redirect('/ref');
        \Cache::put('ref.user.' . $this->user->id, '', 1);
        $kode = $request->get('idd'); //(Код)
        $id = $this->user->steamid64; //(ID меняющего)
        $kode = strtolower($kode);
        if (preg_match("/^\d+$/", $kode)){
            $this->_responseMessageToSite('Ваш рферал содержкит только цифры', $this->user->steamid64);
            return redirect('/ref'); //response()->json([ 'errors' => 'Вы ввели не верный код!']);  (Содержкит недопустимые символы)
        }
        if (!preg_match("/^[0-9a-z]+$/i", $kode)){
            $this->_responseMessageToSite('Ваш рферал содержкит недопустимые символы', $this->user->steamid64);
            return redirect('/ref'); //response()->json([ 'errors' => 'Вы ввели не верный код!']);  (Содержкит недопустимые символы)
        }
        $kode = strtoupper($kode);
        $kodeexist = User::where('refkode', $kode)->first();//(Существует ли код?)
        $usera = User::where('steamid64', $id)->first();//(Пользователь меняющий)
        
        if ($kodeexist != NULL || $usera == NULL) {
            $this->_responseMessageToSite('Код уже занят', $this->user->steamid64);
            return redirect('/ref'); //(Либо код существет либо юзер не существует)
        }
        \DB::table('users')->where('steamid64', $id)->update(['refkode' => $kode]);
        $this->_responseMessageToSite('Код успешно изменен', $this->user->steamid64);
        return redirect('/ref'); //response()->json(['message' => 'Вы слишком часто отправляете сообщения!', 'status' => 'error']);
    }
}