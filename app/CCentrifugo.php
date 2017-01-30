<?php

namespace App;
use Centrifuge;
class CCentrifugo{
    
    private $Centrifuge;
    
    public static function publish($channle, $messageData){
<<<<<<< HEAD
        $Centrifuge = new Centrifuge();
        try {
            $response = $Centrifuge->publish($channle, $messageData);
=======
        $centrifugo = new Centrifugo(env('CENTR_SCHEME_API').'://'.env('CENTR_HOST').env('CENTR_URL_API'), env('CENTR_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->publish($channle, $messageData);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function broadcast($channle, $messageData){
        $centrifugo = new Centrifugo(env('CENTR_SCHEME_API').'://'.env('CENTR_HOST').env('CENTR_URL_API'), env('CENTR_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->broadcast($channle, $messageData);
>>>>>>> e42a151beed98d169dbfd86ad081ff5e5e3ed7c7
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function unsubscribe($channle, $userId){
<<<<<<< HEAD
        $Centrifuge = new Centrifuge();
=======
        $centrifugo = new Centrifugo(env('CENTR_SCHEME_API').'://'.env('CENTR_HOST').env('CENTR_URL_API'), env('CENTR_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
>>>>>>> e42a151beed98d169dbfd86ad081ff5e5e3ed7c7
        try {
            $response = $Centrifuge->unsubscribe($channle, $userId);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function disconnect($userId){
<<<<<<< HEAD
        $Centrifuge = new Centrifuge();
=======
        $centrifugo = new Centrifugo(env('CENTR_SCHEME_API').'://'.env('CENTR_HOST').env('CENTR_URL_API'), env('CENTR_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
>>>>>>> e42a151beed98d169dbfd86ad081ff5e5e3ed7c7
        try {
            $response = $Centrifuge->disconnect($userId);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function presence($channle){
<<<<<<< HEAD
        $Centrifuge = new Centrifuge();
=======
        $centrifugo = new Centrifugo(env('CENTR_SCHEME_API').'://'.env('CENTR_HOST').env('CENTR_URL_API'), env('CENTR_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
>>>>>>> e42a151beed98d169dbfd86ad081ff5e5e3ed7c7
        try {
            $response = $Centrifuge->presence($channle);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function history($channle){
<<<<<<< HEAD
        $Centrifuge = new Centrifuge();
=======
        $centrifugo = new Centrifugo(env('CENTR_SCHEME_API').'://'.env('CENTR_HOST').env('CENTR_URL_API'), env('CENTR_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->history($channle);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function channels(){
        $centrifugo = new Centrifugo(env('CENTR_SCHEME_API').'://'.env('CENTR_HOST').env('CENTR_URL_API'), env('CENTR_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->channels();
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function stats(){
        $centrifugo = new Centrifugo(env('CENTR_SCHEME_API').'://'.env('CENTR_HOST').env('CENTR_URL_API'), env('CENTR_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
>>>>>>> e42a151beed98d169dbfd86ad081ff5e5e3ed7c7
        try {
            $response = $Centrifuge->history($channle);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
<<<<<<< HEAD
    public static function generateToken($user, $timestamp, $info){
        $Centrifuge = new Centrifuge();
=======
    public static function generateClientToken($user, $timestamp, $info){
        $centrifugo = new Centrifugo(env('CENTR_SCHEME_API').'://'.env('CENTR_HOST').env('CENTR_URL_API'), env('CENTR_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
>>>>>>> e42a151beed98d169dbfd86ad081ff5e5e3ed7c7
        try {
            $response = $Centrifuge->generateToken($user, $timestamp, $info);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
}
