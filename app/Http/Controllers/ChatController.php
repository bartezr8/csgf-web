<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use PhpParser\Node\Expr\Cast\Object_;
use App\User;
use LRedis;

class ChatController extends Controller
{

    const CHAT_CHANNEL = 'chat.message';
    const NEW_MSG_CHANNEL = 'new.msg';

    public function __construct(){
        parent::__construct();
    }
    
    public function add_message(Request $request){
        if (\Cache::has('addmsg.user.' . $this->user->id)) {
            return response()->json(['message' => 'Вы слишком часто отправляете сообщения!', 'status' => 'error']);
        }
        \Cache::put('addmsg.user.' . $this->user->id, 'message', 1);
        $bet = \DB::table('bets')->where('user_id', '=', $this->user->id)->get();
        if ($this->user->banchat == 1) {
            return response()->json(['message' => 'Вы забанены в чате ! Срок : Навсегда', 'status' => 'error']);
        }
        $userid = $this->user->steamid64;
        $admin = $this->user->is_admin;
		$moder = $this->user->is_moderator;
		$vip = 0;
		if (strpos(strtolower(' '.$this->user->username),  strtolower(config('app.sitename'))) != false) $vip = 1;
        $username = htmlspecialchars($this->user->username);
        $avatar = $this->user->avatar;
        $time = date('H:i', time());
        $messages = strtolower($this->_validateMessage($request));
		function object_to_array($data){
            if (is_array($data) || is_object($data)) {
                $result = array();
                foreach ($data as $key => $value) {
                    $result[$key] = object_to_array($value);
                }
                return $result;
            }
            return $data;
        }
		$words = mb_strtolower(file_get_contents(dirname(__FILE__) . '/words.json'));
        $words = object_to_array(json_decode($words));
		$messages = mb_strtolower($messages);
        foreach ($words as $key => $value) {
            $messages = str_ireplace($key, $value, $messages);
        }
        if ($this->user->is_admin == 0) {
            if ($bet == null) {
               return response()->json(['message' => 'Вы должны поставить ставку чтобы писать в чате', 'status' => 'error']);
            }
        }
        if ($this->user->is_admin == 1) {
            if (substr_count($messages, '/clear')) {
                $this->redis->del(self::CHAT_CHANNEL);
                return response()->json(['message' => 'Вы отчистили чат !', 'status' => 'success']);
            }
        }
		$data = $this->redis->rpop(self::CHAT_CHANNEL);
		if(!is_null($data)){
			$data = json_decode($data, true);
			if($data['userid'] == $userid){
				$data['messages'] = $data['messages'] . ' ' . htmlspecialchars($messages);
				$this->redis->rpush(self::CHAT_CHANNEL, json_encode($data));
			} else{
				$returnValue = [
					'userid' => $userid, 
					'avatar' => $avatar, 
					'time' => $time, 
					'messages' => htmlspecialchars($messages), 
					'username' => $username, 
					'admin' => $admin, 
					'vip' => $vip, 
					'moder' => $moder
				];
				$this->redis->rpush(self::CHAT_CHANNEL, json_encode($data));
				$this->redis->rpush(self::CHAT_CHANNEL, json_encode($returnValue));
			}
		} else {
			$returnValue = [
				'userid' => $userid, 
				'avatar' => $avatar, 
				'time' => $time, 
				'messages' => htmlspecialchars($messages), 
				'username' => $username, 
				'admin' => $admin, 
				'vip' => $vip, 
				'moder' => $moder
			];
			$this->redis->rpush(self::CHAT_CHANNEL, json_encode($returnValue));
		}
        $this->redis->publish(self::NEW_MSG_CHANNEL, json_encode(['status' => 'success']));
        return response()->json(['message' => 'Ваше сообщение успешно отправлено !', 'status' => 'success']);
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
        $max = $this->redis->llen(self::CHAT_CHANNEL);
		$min = 0;
		if ($max > config('mod_game.chat_history_length')) $min = $max - config('mod_game.chat_history_length');
        $value = $this->redis->lrange(self::CHAT_CHANNEL, $min, $max);
        $i = 0;
        foreach ($value as $key => $newchat[$i]) {
            $a = json_decode($newchat[$i], true);
            $a['username'] = htmlspecialchars($a['username']);
            $a["messages"]  = self::replaceSmile($a["messages"]);
            $returnValue[$i] = [
				'id' => $min + $i,
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
				$this->redis->publish(self::NEW_MSG_CHANNEL, json_encode(1));
				return response()->json(['message' => 'Сообщение успешно удалено', 'status' => 'success']);
			} else {
				return response()->json(['message' => 'Вы не можете удалять чужие сообщения', 'status' => 'error']);
			}
		} else {
			return response()->json(['message' => 'Сообщение не существует', 'status' => 'error']);
		}
    }

    private function _responseMessageToSite($message, $userid){
        return $this->redis->publish(GameController::INFO_CHANNEL, json_encode([
            'steamid' => $userid,
            'message' => $message
        ]));
    }
}
