<?php

namespace App;
use Centrifuge;
class CCentrifugo{
    
    private $Centrifuge;
    
    public static function publish($channle, $messageData){
        $Centrifuge = new Centrifuge();
        try {
            $response = $Centrifuge->publish($channle, $messageData);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function unsubscribe($channle, $userId){
        $Centrifuge = new Centrifuge();
        try {
            $response = $Centrifuge->unsubscribe($channle, $userId);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function disconnect($userId){
        $Centrifuge = new Centrifuge();
        try {
            $response = $Centrifuge->disconnect($userId);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function presence($channle){
        $Centrifuge = new Centrifuge();
        try {
            $response = $Centrifuge->presence($channle);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function history($channle){
        $Centrifuge = new Centrifuge();
        try {
            $response = $Centrifuge->history($channle);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
    public static function generateToken($user, $timestamp, $info){
        $Centrifuge = new Centrifuge();
        try {
            $response = $Centrifuge->generateToken($user, $timestamp, $info);
            return $response;
        } catch (CentrifugoException $e){
            return false;
        }
    }
}
