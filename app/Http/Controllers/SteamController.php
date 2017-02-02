<?php

namespace App\Http\Controllers;

use App\User;
use Auth;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Invisnik\LaravelSteamAuth\SteamAuth;

class SteamController extends Controller
{
    private $steamAuth;
    public function __construct(SteamAuth $auth)
    {
        parent::__construct();
        $this->steamAuth = $auth;
    }

    public function login()
    {
        if ($this->steamAuth->validate()) {
            $info = $this->steamAuth->getUserInfo();
            if (!is_null($info)) {
                $user = User::where('steamid64', $info->steamID64)->first();
                if (is_null($user)) {
                    $nick = $info->personaname;
                    $nick = ChatController::censrepl($nick);
                    $user = User::create([
                        'username' => $nick,
                        'avatar' => $info->avatarfull,
                        'steamid' => $info->steamID,
                        'steamid64' => $info->steamID64,
                        'slimit' => config('mod_game.slimit_default')
                    ]);
                } else {
                    $nick = $info->personaname;
                    $nick = ChatController::censrepl($nick);
                    \DB::table('users')->where('steamid64', $info->steamID64)->update(['username' => $nick, 'avatar' => $info->avatarfull]);
                }
                Auth::login($user, true);
                return redirect('/'); // redirect to site
            }
        }
        return $this->steamAuth->redirect();
    }
    
    public function auth(Request $request){
        $steamid64 = $request->get('steamid64');
        $password = $request->get('password');
        if (\Cache::has('auth.user.' . $steamid64)) return redirect()->back()->with('error', 'Подождите...');
        \Cache::put('auth.user.' . $steamid64, '', 1);
        $user = User::where('refkode', $steamid64)->first();
        if (!is_null($user)){
            if (!is_null($user->password)){
                if ($user->password == $password){
                    Auth::login($user, true);
                } else return redirect()->back()->with('error', 'Неверный Пароль');
            } else return redirect()->back()->with('error', 'Пароль не установлен');
        } else return redirect()->back()->with('error', 'Неверный SteamID');
        return redirect('/');
    }
    public function updatepassword(Request $request){
        if ($request->get('value')!=''){
            \DB::table('users')->where('steamid64', $this->user->steamid64)->update(['password' => $request->get('value')]);
            return response()->json(['success' => true, 'value' => 'Пароль успешно обновлен']);
        }
        return response()->json(['error' => true, 'value' => 'Ошибка']);
    }
    
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function updateSettings(Request $request)
    {
        $user = $this->user;
        if(!$request->ajax()){
            $steamInfo = $this->_getSteamInfo($user->steamid64);
            $nick = $steamInfo->getNick();
            $nick = ChatController::censrepl($nick);
            $user->username = $nick;
            $user->avatar = $steamInfo->getProfilePictureFull();
        }
        if($token = $this->_parseTradeLink($link = $request->get('trade_link'))){
            $user->trade_link = $link;
            $user->accessToken = $token;
            $user->save();
            if($request->ajax()) return response()->json(['msg' => 'Ссылка успешно сохранена', 'status' => 'success']);
            return redirect()->back()->with('success', 'Ссылка успешно сохранена');
        }else{
            if($request->ajax()) return response()->json(['msg' => 'Неверный формат ссылки', 'status' => 'error']);
            return redirect()->back()->with('error', 'Неверный формат ссылки');
        }
    }

    public static function _parseTradeLink($tradeLink)
    {
        $query_str = parse_url($tradeLink, PHP_URL_QUERY);
        parse_str($query_str, $query_params);
        return isset($query_params['token']) ? $query_params['token'] : false;
    }
}
