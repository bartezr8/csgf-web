<?php

namespace App\Http\Controllers;
use App\Bet;
use App\Game;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class BotController extends Controller
{

	const TITLE_UP = "ПY | ";
	const botdir = '/var/bot/';
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
	
	public function start() {
        exec("sudo pm2 start all", $out);
        return response()->json(true);
	}
	public function stop() {
        exec("sudo pm2 stop all", $out);
        return response()->json(true);
	}
	public function restart() {
		exec("sudo pm2 restart all", $out);
		return response()->json(true);
	}
	public function reload() {
		exec("sudo pm2 reload all", $out);
		return response()->json(true);
	}
	public function mysql() {
		exec("sudo redis-cli flushall", $out);
		return response()->json(true);
	}
	public function log() {
		/*$history = file_get_contents(self::botdir.'logs/history.json');
        $history = json_decode($history,1);
        $lastfile = $history['dates'][max(array_keys($history['dates']))];*/
        $lastfile = self::getLastFile(self::botdir.'logs/2016/Nov/');
        $logs = file_get_contents(self::botdir.'logs/2016/Nov/'.$lastfile);
		//$logs = file_get_contents(self::botdir.$lastfile[0]);
        $logs = explode("\n",$logs);
        foreach($logs as $key=>$line) {
			if($key > (count($logs) - self::lines)){
				if(!empty($line)){
                    $line = json_decode($line);
                    if($line->message!='')echo '<span style="width:1010px">'.$line->message."</span><br>";
                }
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