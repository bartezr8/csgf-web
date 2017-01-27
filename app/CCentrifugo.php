<?php

namespace App;
use Centrifugo\Centrifugo;
class CCentrifugo{
    
    private $centrifugo;
    
    public static function publish($channle, $messageData){
        $centrifugo = new Centrifugo(env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), env('CENT_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->publish($channle, $messageData);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function broadcast($channle, $messageData){
        $centrifugo = new Centrifugo(env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), env('CENT_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->broadcast($channle, $messageData);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function unsubscribe($channle, $userId){
        $centrifugo = new Centrifugo(env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), env('CENT_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->unsubscribe($channle, $userId);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function disconnect($userId){
        $centrifugo = new Centrifugo(env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), env('CENT_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->disconnect($userId);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function presence($channle){
        $centrifugo = new Centrifugo(env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), env('CENT_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->presence($channle);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function history($channle){
        $centrifugo = new Centrifugo(env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), env('CENT_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->history($channle);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function channels(){
        $centrifugo = new Centrifugo(env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), env('CENT_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->channels();
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function stats(){
        $centrifugo = new Centrifugo(env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), env('CENT_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->stats();
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function generateClientToken($user, $timestamp, $info){
        $centrifugo = new Centrifugo(env('CENT_SCHEME_API').'://'.env('CENT_HOST').env('CENT_URL_API'), env('CENT_SECRET'), ['redis' => ['host'=>'localhost','port'=>6379,'db'=>1,'timeout'=>1.0],'http'=>[CURLOPT_TIMEOUT=>5]]);
        try {
            $response = $centrifugo->generateClientToken($user, $timestamp, $info);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
}
