<?php

namespace App\Http\Controllers;
use Illuminate\SupportCache;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use PhpParser\Node\Expr\Cast\Object_;
use App\CCentrifugo;
use App\User;
use LRedis;

class ChatController extends Controller
{

    const CHAT_CHANNEL = 'chat.message';

    public function __construct(){
        parent::__construct();
    }
    
    public function add_message(Request $request){
        if (\Cache::has('addmsg.user.' . $this->user->id)) return response()->json(['message' => 'Вы слишком часто отправляете сообщения!', 'status' => 'error']);
        \Cache::put('addmsg.user.' . $this->user->id, 'message', 1);
        $bet = \DB::table('bets')->where('user_id', '=', $this->user->id)->get();
        if ($this->user->banchat == 1) return response()->json(['message' => 'Вы забанены в чате !', 'status' => 'error']);
        $userid = $this->user->steamid64;
        $admin = $this->user->is_admin;
        $moder = $this->user->is_moderator;
        $vip = 0;
        if (strpos(strtolower(' '.$this->user->username),  strtolower(config('app.sitename'))) != false) $vip = 1;
        $username = htmlspecialchars($this->user->username);
        $avatar = $this->user->avatar;
        $time = date('H:i', time());
        $messages = strtolower($this->_validateMessage($request));
        $words = mb_strtolower(file_get_contents(dirname(__FILE__) . '/words.json'));
        $words = GameController::object_to_array(json_decode($words));
        $messages = mb_strtolower($messages);
        foreach ($words as $key => $value) $messages = str_ireplace($key, $value, $messages);
        /*if ($this->user->is_admin == 0) {
            if ($bet == null) {
               return response()->json(['message' => 'Вы должны поставить ставку чтобы писать в чате', 'status' => 'error']);
            }
        }*/
        if ($this->user->is_admin == 1) {
            if (substr_count($messages, '/clear')) {
                $this->redis->del(self::CHAT_CHANNEL);
                return response()->json(['message' => 'Вы отчистили чат !', 'status' => 'success']);
            }
        }
        $id = $this->redis->get('chat_id');
        $returnValue = [
            'id' => $id,
            'userid' => $userid, 
            'avatar' => $avatar, 
            'time' => $time, 
            'messages' => self::replaceSmile(htmlspecialchars($messages)), 
            'username' => htmlspecialchars($username), 
            'admin' => $admin, 
            'vip' => $vip, 
            'moder' => $moder
        ];
        $this->redis->set('chat_id', $id++);
        $this->redis->rpush(self::CHAT_CHANNEL, json_encode($returnValue));
        $llen = $this->redis->llen(self::CHAT_CHANNEL);
        if($llen > config('mod_game.chat_history_length')) $this->redis->lpop(self::CHAT_CHANNEL);
        CCentrifugo::publish('chat_add' , [$returnValue]);
        return response()->json(['message' => 'OK !', 'status' => 'success']);
    }
    private function _validateMessage($request){
        $val = \Validator::make($request->all(), [
            'messages' => 'required|string|max:255'
        ], [
            'required' => 'Сообщение не может быть пустым!',
            'string' => 'Сообщение должно быть строкой!',
            'max' => 'Максимальный размер сообщения 255 символов.',
        ]);
        if ($val->fails()) $this->throwValidationException($request, $val);
        return $request->get('messages');
    }
    private function replaceSmile($a){
        $a = str_replace("skoniks", " <br><ins>:sm104:<a href='http://natribu.org/'><b>SKONIKS</b></a>:sm104:</ins><br> ", $a);
        $a = str_replace(":b:", "<b>", $a);
        $a = str_replace(":/b:", "</b>", $a);
        $a = str_replace(":br:", "<br>", $a);
        $a = preg_replace("/:sm(\d+):/", "<a id='smile' class='smile-smile-_\$1_'></a>", $a);
        return $a;
    }
    public function chat(Request $request){
        $returnValue = [];
        $value = $this->redis->lrange(self::CHAT_CHANNEL, 0, -1);
        $i = 0;
        foreach ($value as $key => $newchat[$i]) {
            $a = json_decode($newchat[$i], true);
            $a['username'] = htmlspecialchars($a['username']);
            $a["messages"] = self::replaceSmile($a["messages"]);
            $returnValue[$i] = [
                'id' => $i,
                'userid' => $a['userid'],
                'avatar' => $a['avatar'],
                'time' => $a['time'],
                'messages' => $a['messages'],
                'username' => $a['username'],
                'moder' => $a['moder'],
                'vip' => $a['vip'],
                'admin' => $a['admin']
            ];
            $i++;
        }
        return $returnValue;
    }
    
    public function delmsg(Request $request){
        $id = $request->get('id');
        $value = $this->redis->lrange(self::CHAT_CHANNEL, $id, $id);
        $i = 0;
        foreach ($value as $key => $newchat) {
            $a = json_decode($newchat, true);
            $msg = $newchat;
            $userid = $a['userid'];
            $i++;
        }
        if($i > 0){
            if ($userid == $this->user->steamid64 || $this->user->is_admin == 1 || $this->user->is_moderator == 1 ){
                $this->redis->lrem(self::CHAT_CHANNEL, -1, $msg);
                CCentrifugo::publish('chat_del' , ['id' => $id]);
                return response()->json(['message' => 'OK !', 'status' => 'success']);
            } else {
                return response()->json(['message' => 'Вы не можете удалять чужие сообщения', 'status' => 'error']);
            }
        } else {
            return response()->json(['message' => 'Сообщение не существует', 'status' => 'error']);
        }
    }
}
