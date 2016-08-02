<?php
/*
 * Author: Mahmoud Eskandari
 * Email: eskandari@nekatelecom.com
 * Copyright 2016 Neka Telecom
 * Website: http://3g4u.ir
   SIMPLE CONFIG FILE
     ### define("api_username","ApiCodeHere...");
     ### define("api_password","Password Here...");
     ### define("api_url","http://ws.3g4u.ir/");
 */
namespace Neka;

class Pin{
    /**
     * @var string
     */
    public static  $transaction = '';

    /**
     * @var bool
     */
    public static  $connection  = true;

    /**
     * @var string
     */
    public static  $response    = "";

    /**
     * @var array
     */
    public static $request_list = [];

    /**
     * @return array|bool|int
     */
    public static  function send_request(){
        Pin::new_transid();
        return Pin::request();
    }

    /**
     * New Transaction Id
     */
    static function new_transid($reset = false){
        if($reset)
            Pin::$request_list = [];
        //
        Pin::$transaction = md5(microtime(true));
    }

    /**
     * @param $operator
     * @param $amount
     * @param int $count
     * @return mixed
     */
     static function add($operator,$amount,$count = 1){
        if($operator < 1 OR $operator > 3)
            return false;

        if($amount < 10000)
            return false;

        if($count < 1)
            return false;

        Pin::$request_list[] = [$operator,$amount,$count];
        return true;
    }

    /**
     * @param int $try
     * @return array|bool|int
     */
    private static function request($try = 1){
        //Reset Response
        Pin::$response = '';
        //Check Request List
        $request = Pin::create_requst();
        if($request == false)
            return ['status'=>false,'scode'=> -2,'message'=>"order basket is empty"];
        //Send Request parameters By Json Raw Body AND Basic Authentication
        Pin::$response = \Httpful\Request::post(api_url ."pin?format=json")
                    ->authenticateWith(api_username, api_password)
                    ->sendsJson()
                    ->body(json_encode(['list' => $request,'transaction'=>Pin::$transaction]))
                    ->send();
        $response = @json_decode(Pin::$response,true);
        //Connection Ok
        if(!empty($response)){
            Pin::$connection = true;
            Pin::new_transid(true);
           return $response;
        }
        //When 3th try Failed
        if(empty($response) AND $try == 3){
            Pin::$connection = false;
            return false;
        }
        //Check transaction status
        if($try == 1){
            $check = Pin::check_transaction();
            if(is_array($check)){
                Pin::new_transid(true);
                return $check;
            }
            //When Check proccess failed too
            if($check == -1){
                return false;
            }
        }
        //Retry new request
        $try++;
        return Pin::request($try);
    }

    /**
     * @return string|boolean
     */
    static function create_requst(){
        if(empty(Pin::$request_list))
            return false;
        return Pin::$request_list;
    }

    /**
     * @param int $try
     * @return bool|int|array
     */
    static function check_transaction($try = 1){
        $response = \Httpful\Request::post(api_url ."check_transaction?format=json")
            ->authenticateWith(api_username, api_password)
            ->sendsJson()
            ->body(json_encode(['transaction' => Pin::$transaction]))
            ->send();
        //Waiting for new request
        sleep(3);
        //When 3th try Failed
        if(strlen($response) < 1 AND $try == 3){
            return -1;
        }
        if(strlen($response) < 1){
            $try++;
            return Pin::check_transaction($try);
        }
        //If connection is Ok
        $response = @json_decode($response,true);
        //If Transaction defined
        if(!isset($response['status']) OR $response['status'] == false)
            return -2;

        return $response;
    }
}
