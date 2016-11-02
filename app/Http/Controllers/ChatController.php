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
	const MSG_COUNT = 50;

    public function __construct(){
        parent::__construct();
        $this->redis = LRedis::connection();
    }

    public function  __destruct(){
        $this->redis->disconnect();
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
		if (strpos(strtolower(' '.$this->user->username),  strtolower(config('mod_game.sitename'))) != false) $vip = 1;
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
        /*if (preg_match("/href|url|http|www|.ru|.com|.net|.info|.org/i", $messages)) {
            return response()->json(['message' => 'Ссылки запрещены !', 'status' => 'error']);
        }*/
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
        $a = str_replace(":b:", "<b>", $a);
        $a = str_replace(":/b:", "</b>", $a);
        $a = str_replace(":br:", "<br>", $a);
        $a = str_replace("skoniks", " :sm104:<a href='http://vk.com/skoniks'><b>SKONIKS</b></a>:sm104: ", $a);
        $a = str_replace(":csgf:", "<img style=\"background:none;\" id=smile src=\"/assets/img/logo-ru.png\">", $a);
        $a = str_replace(":sm1:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (1).png\">", $a);
        $a = str_replace(":sm2:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (2).png\">", $a);
        $a = str_replace(":sm3:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (3).png\">", $a);
        $a = str_replace(":sm4:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (4).png\">", $a);
        $a = str_replace(":sm5:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (5).png\">", $a);
        $a = str_replace(":sm6:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (6).png\">", $a);
        $a = str_replace(":sm7:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (7).png\">", $a);
        $a = str_replace(":sm8:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (8).png\">", $a);
        $a = str_replace(":sm9:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (9).png\">", $a);
        $a = str_replace(":sm10:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (10).png\">", $a);
        $a = str_replace(":sm11:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (11).png\">", $a);
        $a = str_replace(":sm12:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (12).png\">", $a);
        $a = str_replace(":sm13:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (13).png\">", $a);
        $a = str_replace(":sm14:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (14).png\">", $a);
        $a = str_replace(":sm15:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (15).png\">", $a);
        $a = str_replace(":sm16:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (16).png\">", $a);
        $a = str_replace(":sm17:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (17).png\">", $a);
        $a = str_replace(":sm18:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (18).png\">", $a);
        $a = str_replace(":sm19:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (19).png\">", $a);
        $a = str_replace(":sm20:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (20).png\">", $a);
        $a = str_replace(":sm21:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (21).png\">", $a);
        $a = str_replace(":sm22:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (22).png\">", $a);
        $a = str_replace(":sm23:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (23).png\">", $a);
        $a = str_replace(":sm24:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (24).png\">", $a);
        $a = str_replace(":sm25:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (25).png\">", $a);
        $a = str_replace(":sm26:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (26).png\">", $a);
        $a = str_replace(":sm27:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (27).png\">", $a);
        $a = str_replace(":sm28:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (28).png\">", $a);
        $a = str_replace(":sm29:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (29).png\">", $a);
        $a = str_replace(":sm30:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (30).png\">", $a);
        $a = str_replace(":sm31:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (31).png\">", $a);
        $a = str_replace(":sm32:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (32).png\">", $a);
        $a = str_replace(":sm33:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (33).png\">", $a);
        $a = str_replace(":sm34:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (34).png\">", $a);
        $a = str_replace(":sm35:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (35).png\">", $a);
        $a = str_replace(":sm36:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (36).png\">", $a);
        $a = str_replace(":sm37:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (37).png\">", $a);
        $a = str_replace(":sm38:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (38).png\">", $a);
        $a = str_replace(":sm39:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (39).png\">", $a);
        $a = str_replace(":sm40:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (40).png\">", $a);
        $a = str_replace(":sm41:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (41).png\">", $a);
        $a = str_replace(":sm42:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (42).png\">", $a);
        $a = str_replace(":sm43:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (43).png\">", $a);
        $a = str_replace(":sm44:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (44).png\">", $a);
        $a = str_replace(":sm45:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (45).png\">", $a);
        $a = str_replace(":sm46:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (46).png\">", $a);
        $a = str_replace(":sm47:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (47).png\">", $a);
        $a = str_replace(":sm48:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (48).png\">", $a);
        $a = str_replace(":sm49:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (49).png\">", $a);
        $a = str_replace(":sm50:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (50).png\">", $a);
        $a = str_replace(":sm51:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (51).png\">", $a);
        $a = str_replace(":sm52:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (52).png\">", $a);
        $a = str_replace(":sm53:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (53).png\">", $a);
        $a = str_replace(":sm54:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (54).png\">", $a);
        $a = str_replace(":sm55:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (55).png\">", $a);
        $a = str_replace(":sm56:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (56).png\">", $a);
        $a = str_replace(":sm57:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (57).png\">", $a);
        $a = str_replace(":sm58:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (58).png\">", $a);
        $a = str_replace(":sm59:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (59).png\">", $a);
        $a = str_replace(":sm60:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (60).png\">", $a);
        $a = str_replace(":sm61:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (61).png\">", $a);
        $a = str_replace(":sm62:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (62).png\">", $a);
        $a = str_replace(":sm63:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (63).png\">", $a);
        $a = str_replace(":sm64:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (64).png\">", $a);
        $a = str_replace(":sm65:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (65).png\">", $a);
        $a = str_replace(":sm66:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (66).png\">", $a);
        $a = str_replace(":sm67:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (67).png\">", $a);
        $a = str_replace(":sm68:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (68).png\">", $a);
        $a = str_replace(":sm69:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (69).png\">", $a);
        $a = str_replace(":sm70:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (70).png\">", $a);
        $a = str_replace(":sm71:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (71).png\">", $a);
        $a = str_replace(":sm72:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (72).png\">", $a);
        $a = str_replace(":sm73:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (73).png\">", $a);
        $a = str_replace(":sm74:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (74).png\">", $a);
        $a = str_replace(":sm75:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (75).png\">", $a);
        $a = str_replace(":sm76:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (76).png\">", $a);
        $a = str_replace(":sm77:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (77).png\">", $a);
        $a = str_replace(":sm78:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (78).png\">", $a);
        $a = str_replace(":sm79:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (79).png\">", $a);
        $a = str_replace(":sm80:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (80).png\">", $a);
        $a = str_replace(":sm81:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (81).png\">", $a);
        $a = str_replace(":sm82:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (82).png\">", $a);
        $a = str_replace(":sm83:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (83).png\">", $a);
        $a = str_replace(":sm84:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (84).png\">", $a);
        $a = str_replace(":sm85:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (85).png\">", $a);
        $a = str_replace(":sm86:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (86).png\">", $a);
        $a = str_replace(":sm87:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (87).png\">", $a);
        $a = str_replace(":sm88:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (88).png\">", $a);
        $a = str_replace(":sm89:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (89).png\">", $a);
        $a = str_replace(":sm90:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (90).png\">", $a);
        $a = str_replace(":sm91:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (91).png\">", $a);
        $a = str_replace(":sm92:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (92).png\">", $a);
        $a = str_replace(":sm93:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (93).png\">", $a);
        $a = str_replace(":sm94:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (94).png\">", $a);
        $a = str_replace(":sm95:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (95).png\">", $a);
        $a = str_replace(":sm96:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (96).png\">", $a);
        $a = str_replace(":sm97:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (97).png\">", $a);
        $a = str_replace(":sm98:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (98).png\">", $a);
        $a = str_replace(":sm99:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (99).png\">", $a);
        $a = str_replace(":sm100:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (100).png\">", $a);
        $a = str_replace(":sm101:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (101).png\">", $a);
        $a = str_replace(":sm102:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (102).png\">", $a);
        $a = str_replace(":sm103:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (103).png\">", $a);
        $a = str_replace(":sm104:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (104).png\">", $a);
        $a = str_replace(":sm105:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (105).png\">", $a);
        $a = str_replace(":sm106:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (106).png\">", $a);
        $a = str_replace(":sm107:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (107).png\">", $a);
        $a = str_replace(":sm108:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (108).png\">", $a);
        $a = str_replace(":sm109:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (109).png\">", $a);
        $a = str_replace(":sm110:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (110).png\">", $a);
        $a = str_replace(":sm111:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (111).png\">", $a);
        $a = str_replace(":sm112:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (112).png\">", $a);
        $a = str_replace(":sm113:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (113).png\">", $a);
        $a = str_replace(":sm114:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (114).png\">", $a);
        $a = str_replace(":sm115:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (115).png\">", $a);
        $a = str_replace(":sm116:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (116).png\">", $a);
        $a = str_replace(":sm117:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (117).png\">", $a);
        $a = str_replace(":sm118:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (118).png\">", $a);
        $a = str_replace(":sm119:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (119).png\">", $a);
        $a = str_replace(":sm120:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (120).png\">", $a);
        $a = str_replace(":sm121:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (121).png\">", $a);
        $a = str_replace(":sm122:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (122).png\">", $a);
        $a = str_replace(":sm123:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (123).png\">", $a);
        $a = str_replace(":sm124:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (124).png\">", $a);
        $a = str_replace(":sm125:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (125).png\">", $a);
        $a = str_replace(":sm126:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (126).png\">", $a);
        $a = str_replace(":sm127:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (127).png\">", $a);
        $a = str_replace(":sm128:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (128).png\">", $a);
        $a = str_replace(":sm129:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (129).png\">", $a);
        $a = str_replace(":sm130:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (130).png\">", $a);
        $a = str_replace(":sm131:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (131).png\">", $a);
        $a = str_replace(":sm132:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (132).png\">", $a);
        $a = str_replace(":sm133:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (133).png\">", $a);
        $a = str_replace(":sm134:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (134).png\">", $a);
        $a = str_replace(":sm135:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (135).png\">", $a);
        $a = str_replace(":sm136:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (136).png\">", $a);
        $a = str_replace(":sm137:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (137).png\">", $a);
        $a = str_replace(":sm138:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (138).png\">", $a);
        $a = str_replace(":sm139:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (139).png\">", $a);
        $a = str_replace(":sm140:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (140).png\">", $a);
        $a = str_replace(":sm141:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (141).png\">", $a);
        $a = str_replace(":sm142:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (142).png\">", $a);
        $a = str_replace(":sm143:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (143).png\">", $a);
        $a = str_replace(":sm144:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (144).png\">", $a);
        $a = str_replace(":sm145:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (145).png\">", $a);
        $a = str_replace(":sm146:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (146).png\">", $a);
        $a = str_replace(":sm147:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (147).png\">", $a);
        $a = str_replace(":sm148:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (148).png\">", $a);
        $a = str_replace(":sm149:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (149).png\">", $a);
        $a = str_replace(":sm150:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (150).png\">", $a);
        $a = str_replace(":sm151:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (151).png\">", $a);
        $a = str_replace(":sm152:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (152).png\">", $a);
        $a = str_replace(":sm153:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (153).png\">", $a);
        $a = str_replace(":sm154:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (154).png\">", $a);
        $a = str_replace(":sm155:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (155).png\">", $a);
        $a = str_replace(":sm156:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (156).png\">", $a);
        $a = str_replace(":sm157:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (157).png\">", $a);
        $a = str_replace(":sm158:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (158).png\">", $a);
        $a = str_replace(":sm159:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (159).png\">", $a);
        $a = str_replace(":sm160:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (160).png\">", $a);
        $a = str_replace(":sm161:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (161).png\">", $a);
        $a = str_replace(":sm162:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (162).png\">", $a);
        $a = str_replace(":sm163:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (163).png\">", $a);
        $a = str_replace(":sm164:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (164).png\">", $a);
        $a = str_replace(":sm165:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (165).png\">", $a);
        $a = str_replace(":sm166:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (166).png\">", $a);
        $a = str_replace(":sm167:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (167).png\">", $a);
        $a = str_replace(":sm168:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (168).png\">", $a);
        $a = str_replace(":sm169:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (169).png\">", $a);
        $a = str_replace(":sm170:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (170).png\">", $a);
        $a = str_replace(":sm171:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (171).png\">", $a);
        $a = str_replace(":sm172:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (172).png\">", $a);
        $a = str_replace(":sm173:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (173).png\">", $a);
        $a = str_replace(":sm174:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (174).png\">", $a);
        $a = str_replace(":sm175:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (175).png\">", $a);
        $a = str_replace(":sm176:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (176).png\">", $a);
        $a = str_replace(":sm177:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (177).png\">", $a);
        $a = str_replace(":sm178:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (178).png\">", $a);
        $a = str_replace(":sm179:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (179).png\">", $a);
        $a = str_replace(":sm180:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (180).png\">", $a);
        $a = str_replace(":sm181:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (181).png\">", $a);
        $a = str_replace(":sm182:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (182).png\">", $a);
        $a = str_replace(":sm183:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (183).png\">", $a);
        $a = str_replace(":sm184:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (184).png\">", $a);
        $a = str_replace(":sm185:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (185).png\">", $a);
        $a = str_replace(":sm186:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (186).png\">", $a);
        $a = str_replace(":sm187:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (187).png\">", $a);
        $a = str_replace(":sm188:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (188).png\">", $a);
        $a = str_replace(":sm189:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (189).png\">", $a);
        $a = str_replace(":sm190:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (190).png\">", $a);
        $a = str_replace(":sm191:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (191).png\">", $a);
        $a = str_replace(":sm192:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (192).png\">", $a);
        $a = str_replace(":sm193:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (193).png\">", $a);
        $a = str_replace(":sm194:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (194).png\">", $a);
        $a = str_replace(":sm195:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (195).png\">", $a);
        $a = str_replace(":sm196:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (196).png\">", $a);
        $a = str_replace(":sm197:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (197).png\">", $a);
        $a = str_replace(":sm198:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (198).png\">", $a);
        $a = str_replace(":sm199:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (199).png\">", $a);
        $a = str_replace(":sm200:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (200).png\">", $a);
        $a = str_replace(":sm201:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (201).png\">", $a);
        $a = str_replace(":sm202:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (202).png\">", $a);
        $a = str_replace(":sm203:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (203).png\">", $a);
        $a = str_replace(":sm204:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (204).png\">", $a);
        $a = str_replace(":sm205:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (205).png\">", $a);
        $a = str_replace(":sm206:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (206).png\">", $a);
        $a = str_replace(":sm207:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (207).png\">", $a);
        $a = str_replace(":sm208:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (208).png\">", $a);
        $a = str_replace(":sm209:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (209).png\">", $a);
        $a = str_replace(":sm210:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (210).png\">", $a);
        $a = str_replace(":sm211:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (211).png\">", $a);
        $a = str_replace(":sm212:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (212).png\">", $a);
        $a = str_replace(":sm213:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (213).png\">", $a);
        $a = str_replace(":sm214:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (214).png\">", $a);
        $a = str_replace(":sm215:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (215).png\">", $a);
        $a = str_replace(":sm216:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (216).png\">", $a);
        $a = str_replace(":sm217:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (217).png\">", $a);
        $a = str_replace(":sm218:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (218).png\">", $a);
        $a = str_replace(":sm219:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (219).png\">", $a);
        $a = str_replace(":sm220:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (220).png\">", $a);
        $a = str_replace(":sm221:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (221).png\">", $a);
        $a = str_replace(":sm222:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (222).png\">", $a);
        $a = str_replace(":sm223:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (223).png\">", $a);
        $a = str_replace(":sm224:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (224).png\">", $a);
        $a = str_replace(":sm225:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (225).png\">", $a);
        $a = str_replace(":sm226:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (226).png\">", $a);
        $a = str_replace(":sm227:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (227).png\">", $a);
        $a = str_replace(":sm228:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (228).png\">", $a);
        $a = str_replace(":sm229:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (229).png\">", $a);
        $a = str_replace(":sm230:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (230).png\">", $a);
        $a = str_replace(":sm231:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (231).png\">", $a);
        $a = str_replace(":sm232:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (232).png\">", $a);
        $a = str_replace(":sm233:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (233).png\">", $a);
        $a = str_replace(":sm234:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (234).png\">", $a);
        $a = str_replace(":sm235:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (235).png\">", $a);
        $a = str_replace(":sm236:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (236).png\">", $a);
        $a = str_replace(":sm237:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (237).png\">", $a);
        $a = str_replace(":sm238:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (238).png\">", $a);
        $a = str_replace(":sm239:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (239).png\">", $a);
        $a = str_replace(":sm240:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (240).png\">", $a);
        $a = str_replace(":sm241:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (241).png\">", $a);
        $a = str_replace(":sm242:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (242).png\">", $a);
        $a = str_replace(":sm243:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (243).png\">", $a);
        $a = str_replace(":sm244:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (244).png\">", $a);
        $a = str_replace(":sm245:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (245).png\">", $a);
        $a = str_replace(":sm246:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (246).png\">", $a);
        $a = str_replace(":sm247:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (247).png\">", $a);
        $a = str_replace(":sm248:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (248).png\">", $a);
        $a = str_replace(":sm249:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (249).png\">", $a);
        $a = str_replace(":sm250:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (250).png\">", $a);
        $a = str_replace(":sm251:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (251).png\">", $a);
        $a = str_replace(":sm252:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (252).png\">", $a);
        $a = str_replace(":sm253:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (253).png\">", $a);
        $a = str_replace(":sm254:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (254).png\">", $a);
        $a = str_replace(":sm255:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (255).png\">", $a);
        $a = str_replace(":sm256:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (256).png\">", $a);
        $a = str_replace(":sm257:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (257).png\">", $a);
        $a = str_replace(":sm258:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (258).png\">", $a);
        $a = str_replace(":sm259:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (259).png\">", $a);
        $a = str_replace(":sm260:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (260).png\">", $a);
        $a = str_replace(":sm261:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (261).png\">", $a);
        $a = str_replace(":sm262:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (262).png\">", $a);
        $a = str_replace(":sm263:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (263).png\">", $a);
        $a = str_replace(":sm264:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (264).png\">", $a);
        $a = str_replace(":sm265:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (265).png\">", $a);
        $a = str_replace(":sm266:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (266).png\">", $a);
        $a = str_replace(":sm267:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (267).png\">", $a);
        $a = str_replace(":sm268:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (268).png\">", $a);
        $a = str_replace(":sm269:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (269).png\">", $a);
        $a = str_replace(":sm270:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (270).png\">", $a);
        $a = str_replace(":sm271:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (271).png\">", $a);
        $a = str_replace(":sm272:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (272).png\">", $a);
        $a = str_replace(":sm273:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (273).png\">", $a);
        $a = str_replace(":sm274:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (274).png\">", $a);
        $a = str_replace(":sm275:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (275).png\">", $a);
        $a = str_replace(":sm276:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (276).png\">", $a);
        $a = str_replace(":sm277:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (277).png\">", $a);
        $a = str_replace(":sm278:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (278).png\">", $a);
        $a = str_replace(":sm279:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (279).png\">", $a);
        $a = str_replace(":sm280:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (280).png\">", $a);
        $a = str_replace(":sm281:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (281).png\">", $a);
        $a = str_replace(":sm282:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (282).png\">", $a);
        $a = str_replace(":sm283:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (283).png\">", $a);
        $a = str_replace(":sm284:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (284).png\">", $a);
        $a = str_replace(":sm285:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (285).png\">", $a);
        $a = str_replace(":sm286:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (286).png\">", $a);
        $a = str_replace(":sm287:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (287).png\">", $a);
        $a = str_replace(":sm288:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (288).png\">", $a);
        $a = str_replace(":sm289:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (289).png\">", $a);
        $a = str_replace(":sm290:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (290).png\">", $a);
        $a = str_replace(":sm291:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (291).png\">", $a);
        $a = str_replace(":sm292:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (292).png\">", $a);
        $a = str_replace(":sm293:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (293).png\">", $a);
        $a = str_replace(":sm294:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (294).png\">", $a);
        $a = str_replace(":sm295:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (295).png\">", $a);
        $a = str_replace(":sm296:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (296).png\">", $a);
        $a = str_replace(":sm297:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (297).png\">", $a);
        $a = str_replace(":sm298:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (298).png\">", $a);
        $a = str_replace(":sm299:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (299).png\">", $a);
        $a = str_replace(":sm300:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (300).png\">", $a);
        $a = str_replace(":sm301:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (301).png\">", $a);
        $a = str_replace(":sm302:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (302).png\">", $a);
        $a = str_replace(":sm303:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (303).png\">", $a);
        $a = str_replace(":sm304:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (304).png\">", $a);
        $a = str_replace(":sm305:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (305).png\">", $a);
        $a = str_replace(":sm306:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (306).png\">", $a);
        $a = str_replace(":sm307:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (307).png\">", $a);
        $a = str_replace(":sm308:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (308).png\">", $a);
        $a = str_replace(":sm309:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (309).png\">", $a);
        $a = str_replace(":sm310:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (310).png\">", $a);
        $a = str_replace(":sm311:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (311).png\">", $a);
        $a = str_replace(":sm312:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (312).png\">", $a);
        $a = str_replace(":sm313:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (313).png\">", $a);
        $a = str_replace(":sm314:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (314).png\">", $a);
        $a = str_replace(":sm315:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (315).png\">", $a);
        $a = str_replace(":sm316:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (316).png\">", $a);
        $a = str_replace(":sm317:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (317).png\">", $a);
        $a = str_replace(":sm318:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (318).png\">", $a);
        $a = str_replace(":sm319:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (319).png\">", $a);
        $a = str_replace(":sm320:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (320).png\">", $a);
        $a = str_replace(":sm321:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (321).png\">", $a);
        $a = str_replace(":sm322:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (322).png\">", $a);
        $a = str_replace(":sm323:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (323).png\">", $a);
        $a = str_replace(":sm324:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (324).png\">", $a);
        $a = str_replace(":sm325:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (325).png\">", $a);
        $a = str_replace(":sm326:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (326).png\">", $a);
        $a = str_replace(":sm327:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (327).png\">", $a);
        $a = str_replace(":sm328:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (328).png\">", $a);
        $a = str_replace(":sm329:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (329).png\">", $a);
        $a = str_replace(":sm330:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (330).png\">", $a);
        $a = str_replace(":sm331:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (331).png\">", $a);
        $a = str_replace(":sm332:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (332).png\">", $a);
        $a = str_replace(":sm333:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (333).png\">", $a);
        $a = str_replace(":sm334:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (334).png\">", $a);
        $a = str_replace(":sm335:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (335).png\">", $a);
        $a = str_replace(":sm336:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (336).png\">", $a);
        $a = str_replace(":sm337:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (337).png\">", $a);
        $a = str_replace(":sm338:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (338).png\">", $a);
        $a = str_replace(":sm339:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (339).png\">", $a);
        $a = str_replace(":sm340:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (340).png\">", $a);
        $a = str_replace(":sm341:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (341).png\">", $a);
        $a = str_replace(":sm342:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (342).png\">", $a);
        $a = str_replace(":sm343:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (343).png\">", $a);
        $a = str_replace(":sm344:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (344).png\">", $a);
        $a = str_replace(":sm345:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (345).png\">", $a);
        $a = str_replace(":sm346:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (346).png\">", $a);
        $a = str_replace(":sm347:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (347).png\">", $a);
        $a = str_replace(":sm348:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (348).png\">", $a);
        $a = str_replace(":sm349:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (349).png\">", $a);
        $a = str_replace(":sm350:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (350).png\">", $a);
        $a = str_replace(":sm351:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (351).png\">", $a);
        $a = str_replace(":sm352:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (352).png\">", $a);
        $a = str_replace(":sm353:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (353).png\">", $a);
        $a = str_replace(":sm354:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (354).png\">", $a);
        $a = str_replace(":sm355:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (355).png\">", $a);
        $a = str_replace(":sm356:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (356).png\">", $a);
        $a = str_replace(":sm357:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (357).png\">", $a);
        $a = str_replace(":sm358:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (358).png\">", $a);
        $a = str_replace(":sm359:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (359).png\">", $a);
        $a = str_replace(":sm360:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (360).png\">", $a);
        $a = str_replace(":sm361:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (361).png\">", $a);
        $a = str_replace(":sm362:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (362).png\">", $a);
        $a = str_replace(":sm363:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (363).png\">", $a);
        $a = str_replace(":sm364:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (364).png\">", $a);
        $a = str_replace(":sm365:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (365).png\">", $a);
        $a = str_replace(":sm366:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (366).png\">", $a);
        $a = str_replace(":sm367:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (367).png\">", $a);
        $a = str_replace(":sm368:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (368).png\">", $a);
        $a = str_replace(":sm369:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (369).png\">", $a);
        $a = str_replace(":sm370:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (370).png\">", $a);
        $a = str_replace(":sm371:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (371).png\">", $a);
        $a = str_replace(":sm372:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (372).png\">", $a);
        $a = str_replace(":sm373:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (373).png\">", $a);
        $a = str_replace(":sm374:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (374).png\">", $a);
        $a = str_replace(":sm375:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (375).png\">", $a);
        $a = str_replace(":sm376:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (376).png\">", $a);
        $a = str_replace(":sm377:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (377).png\">", $a);
        $a = str_replace(":sm378:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (378).png\">", $a);
        $a = str_replace(":sm379:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (379).png\">", $a);
        $a = str_replace(":sm380:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (380).png\">", $a);
        $a = str_replace(":sm381:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (381).png\">", $a);
        $a = str_replace(":sm382:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (382).png\">", $a);
        $a = str_replace(":sm383:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (383).png\">", $a);
        $a = str_replace(":sm384:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (384).png\">", $a);
        $a = str_replace(":sm385:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (385).png\">", $a);
        $a = str_replace(":sm386:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (386).png\">", $a);
        $a = str_replace(":sm387:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (387).png\">", $a);
        $a = str_replace(":sm388:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (388).png\">", $a);
        $a = str_replace(":sm389:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (389).png\">", $a);
        $a = str_replace(":sm390:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (390).png\">", $a);
        $a = str_replace(":sm391:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (391).png\">", $a);
        $a = str_replace(":sm392:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (392).png\">", $a);
        $a = str_replace(":sm393:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (393).png\">", $a);
        $a = str_replace(":sm394:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (394).png\">", $a);
        $a = str_replace(":sm395:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (395).png\">", $a);
        $a = str_replace(":sm396:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (396).png\">", $a);
        $a = str_replace(":sm397:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (397).png\">", $a);
        $a = str_replace(":sm398:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (398).png\">", $a);
        $a = str_replace(":sm399:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (399).png\">", $a);
        $a = str_replace(":sm400:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (400).png\">", $a);
        $a = str_replace(":sm401:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (401).png\">", $a);
        $a = str_replace(":sm402:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (402).png\">", $a);
        $a = str_replace(":sm403:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (403).png\">", $a);
        $a = str_replace(":sm404:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (404).png\">", $a);
        $a = str_replace(":sm405:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (405).png\">", $a);
        $a = str_replace(":sm406:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (406).png\">", $a);
        $a = str_replace(":sm407:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (407).png\">", $a);
        $a = str_replace(":sm408:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (408).png\">", $a);
        $a = str_replace(":sm409:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (409).png\">", $a);
        $a = str_replace(":sm410:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (410).png\">", $a);
        $a = str_replace(":sm411:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (411).png\">", $a);
        $a = str_replace(":sm412:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (412).png\">", $a);
        $a = str_replace(":sm413:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (413).png\">", $a);
        $a = str_replace(":sm414:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (414).png\">", $a);
        $a = str_replace(":sm415:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (415).png\">", $a);
        $a = str_replace(":sm416:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (416).png\">", $a);
        $a = str_replace(":sm417:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (417).png\">", $a);
        $a = str_replace(":sm418:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (418).png\">", $a);
        $a = str_replace(":sm419:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (419).png\">", $a);
        $a = str_replace(":sm420:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (420).png\">", $a);
        $a = str_replace(":sm421:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (421).png\">", $a);
        $a = str_replace(":sm422:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (422).png\">", $a);
        $a = str_replace(":sm423:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (423).png\">", $a);
        $a = str_replace(":sm424:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (424).png\">", $a);
        $a = str_replace(":sm425:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (425).png\">", $a);
        $a = str_replace(":sm426:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (426).png\">", $a);
        $a = str_replace(":sm427:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (427).png\">", $a);
        $a = str_replace(":sm428:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (428).png\">", $a);
        $a = str_replace(":sm429:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (429).png\">", $a);
        $a = str_replace(":sm430:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (430).png\">", $a);
        $a = str_replace(":sm431:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (431).png\">", $a);
        $a = str_replace(":sm432:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (432).png\">", $a);
        $a = str_replace(":sm433:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (433).png\">", $a);
        $a = str_replace(":sm434:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (434).png\">", $a);
        $a = str_replace(":sm435:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (435).png\">", $a);
        $a = str_replace(":sm436:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (436).png\">", $a);
        $a = str_replace(":sm437:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (437).png\">", $a);
        $a = str_replace(":sm438:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (438).png\">", $a);
        $a = str_replace(":sm439:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (439).png\">", $a);
        $a = str_replace(":sm440:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (440).png\">", $a);
        $a = str_replace(":sm441:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (441).png\">", $a);
        $a = str_replace(":sm442:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (442).png\">", $a);
        $a = str_replace(":sm443:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (443).png\">", $a);
        $a = str_replace(":sm444:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (444).png\">", $a);
        $a = str_replace(":sm445:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (445).png\">", $a);
        $a = str_replace(":sm446:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (446).png\">", $a);
        $a = str_replace(":sm447:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (447).png\">", $a);
        $a = str_replace(":sm448:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (448).png\">", $a);
        $a = str_replace(":sm449:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (449).png\">", $a);
        $a = str_replace(":sm450:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (450).png\">", $a);
        $a = str_replace(":sm451:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (451).png\">", $a);
        $a = str_replace(":sm452:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (452).png\">", $a);
        $a = str_replace(":sm453:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (453).png\">", $a);
        $a = str_replace(":sm454:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (454).png\">", $a);
        $a = str_replace(":sm455:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (455).png\">", $a);
        $a = str_replace(":sm456:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (456).png\">", $a);
        $a = str_replace(":sm457:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (457).png\">", $a);
        $a = str_replace(":sm458:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (458).png\">", $a);
        $a = str_replace(":sm459:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (459).png\">", $a);
        $a = str_replace(":sm460:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (460).png\">", $a);
        $a = str_replace(":sm461:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (461).png\">", $a);
        $a = str_replace(":sm462:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (462).png\">", $a);
        $a = str_replace(":sm463:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (463).png\">", $a);
        $a = str_replace(":sm464:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (464).png\">", $a);
        $a = str_replace(":sm465:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (465).png\">", $a);
        $a = str_replace(":sm466:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (466).png\">", $a);
        $a = str_replace(":sm467:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (467).png\">", $a);
        $a = str_replace(":sm468:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (468).png\">", $a);
        $a = str_replace(":sm469:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (469).png\">", $a);
        $a = str_replace(":sm470:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (470).png\">", $a);
        $a = str_replace(":sm471:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (471).png\">", $a);
        $a = str_replace(":sm472:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (472).png\">", $a);
        $a = str_replace(":sm473:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (473).png\">", $a);
        $a = str_replace(":sm474:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (474).png\">", $a);
        $a = str_replace(":sm475:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (475).png\">", $a);
        $a = str_replace(":sm476:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (476).png\">", $a);
        $a = str_replace(":sm477:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (477).png\">", $a);
        $a = str_replace(":sm478:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (478).png\">", $a);
        $a = str_replace(":sm479:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (479).png\">", $a);
        $a = str_replace(":sm480:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (480).png\">", $a);
        $a = str_replace(":sm481:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (481).png\">", $a);
        $a = str_replace(":sm482:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (482).png\">", $a);
        $a = str_replace(":sm483:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (483).png\">", $a);
        $a = str_replace(":sm484:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (484).png\">", $a);
        $a = str_replace(":sm485:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (485).png\">", $a);
        $a = str_replace(":sm486:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (486).png\">", $a);
        $a = str_replace(":sm487:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (487).png\">", $a);
        $a = str_replace(":sm488:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (488).png\">", $a);
        $a = str_replace(":sm489:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (489).png\">", $a);
        $a = str_replace(":sm490:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (490).png\">", $a);
        $a = str_replace(":sm491:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (491).png\">", $a);
        $a = str_replace(":sm492:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (492).png\">", $a);
        $a = str_replace(":sm493:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (493).png\">", $a);
        $a = str_replace(":sm494:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (494).png\">", $a);
        $a = str_replace(":sm495:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (495).png\">", $a);
        $a = str_replace(":sm496:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (496).png\">", $a);
        $a = str_replace(":sm497:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (497).png\">", $a);
        $a = str_replace(":sm498:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (498).png\">", $a);
        $a = str_replace(":sm499:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (499).png\">", $a);
        $a = str_replace(":sm500:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (500).png\">", $a);
        $a = str_replace(":sm501:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (501).png\">", $a);
        $a = str_replace(":sm502:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (502).png\">", $a);
        $a = str_replace(":sm503:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (503).png\">", $a);
        $a = str_replace(":sm504:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (504).png\">", $a);
        $a = str_replace(":sm505:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (505).png\">", $a);
        $a = str_replace(":sm506:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (506).png\">", $a);
        $a = str_replace(":sm507:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (507).png\">", $a);
        $a = str_replace(":sm508:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (508).png\">", $a);
        $a = str_replace(":sm509:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (509).png\">", $a);
        $a = str_replace(":sm510:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (510).png\">", $a);
        $a = str_replace(":sm511:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (511).png\">", $a);
        $a = str_replace(":sm512:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (512).png\">", $a);
        $a = str_replace(":sm513:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (513).png\">", $a);
        $a = str_replace(":sm514:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (514).png\">", $a);
        $a = str_replace(":sm515:", "<img style=\"background:none;\" id=smile src=\"/assets/img/smiles/smile (515).png\">", $a);
        return $a;
    }
    public function chat(Request $request){
		$returnValue = [];
        $max = $this->redis->llen(self::CHAT_CHANNEL);
		$min = 0;
		if ($max > self::MSG_COUNT) $min = $max - self::MSG_COUNT;
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
