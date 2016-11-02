<?php

namespace App\Http\Controllers;

use App\Bet;
use App\Game;
use App\Item;
use App\Services\SteamItem;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class BotController extends Controller
{

	const TITLE_UP = "ПY | ";
	const botdir = '/var/bot/';
	const admindir = '/var/www/html/storage/admin/';
	const file = 'app.js';
	const lines = 200;


	public function index() {
		parent::setTitle('ПУ БОТ | ');
		return view('pages.admin.bot');
	}
	
	public function status() {
		exec("pgrep -lf node", $out);
		if(count($out)>0){
			foreach($out as $v){
				$poss = strpos($v, self::botdir.substr(self::file,0,0));
				if($poss !== false){
					return response()->json(true);
				}
			}
		}
		return response()->json(false);
	}
	
	public function enbl() {
		exec("pgrep -lf node", $out);
		if(count($out)>0){
			foreach($out as $v){
				$poss = strpos($v, self::botdir.substr(self::file,0,0));
				if($poss !== false){
					return true;
				}
			}
		}
		return false;
	}
	
	public function start() {
		if (!self::enbl()){
			exec("sudo pm2 start ". substr(self::file, 0, strlen(self::file) - 3), $out);
			return response()->json(true);
		} else {
			return response()->json(false);
		}
	}
	public function stop() {
		if (self::enbl()){
			exec("sudo pm2 stop ". substr(self::file, 0, strlen(self::file) - 3), $out);
			return response()->json($out);
		} else {
			return response()->json(false);
		}
	}
	public function restart() {
		exec("sudo pm2 restart ". substr(self::file, 0, strlen(self::file) - 3), $out);
		return response()->json(true);
	}
	public function reload() {
		$file = self::getLastFile(self::admindir.'log/bot');
		exec("sudo rm -f ". self::admindir."log/bot/".$file, $out);
		exec("sudo pm2 reload ". substr(self::file, 0, strlen(self::file) - 3), $out);
		return response()->json(true);
	}
	public function mysql() {
		exec("sudo redis-cli flushall", $out);
		return response()->json(true);
	}
	public function log() {
		$file = self::getLastFile(self::admindir.'log/bot');
		$log = file_get_contents(self::admindir.'log/bot/'.$file);
		$log = explode("\n",$log);
		$i = 0;
		foreach($log as $key=>$line) {
			if($key > (count($log) - self::lines)){
				if(!empty($line)) echo "<div>".self::log_color_parse(htmlspecialchars($line))."</div>";
			}
		}
	}
	public static function log_color_parse($line) {
		preg_match_all('/\[([0-9]{2})m/',$line,$matches);
		foreach($matches[0] as $key=>$match) {
			$line = str_replace($match, '<span class="color'.$matches[1][$key].'">', $line);
			$line.='</span>';
		}
		$line = str_replace('[;','</span><span>',$line);
		return $line;
	}
	public static function getLastFile($dir) {
		$files = array();
		$yesDir = opendir($dir); // открываем директорию
		if (!$yesDir) return false;
		// идем по элементам директории
		while (false !== ($filename = readdir($yesDir))) {
			// пропускаем вложенные папки
			if ($filename == '.' || $filename == '..')
			continue;
			// получаем время последнего изменения файла, заносим в массивы
			$lastModified = filemtime("$dir/$filename");
			$lm[] = $lastModified;
			$fn[] = $filename;
		}
		// сортируем массивы имен файлов и времен изменения по возрастанию последнего
		$files = array_multisort($lm,SORT_NUMERIC,SORT_ASC,$fn);
		$last_index = count($lm)-1;
		return $fn[$last_index];	
	}
}