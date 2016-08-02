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

class Bill{
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
     * @param int $try
     * @return array|bool|int
     */
    public static function Pay_bill($bill_number,$payment_number,$try = 1){
        if($try == 1)
            Bill::$transaction = md5(microtime(true));
        //Reset Response
        Bill::$response = '';

        if(empty($bill_number) or strlen($bill_number) < 13 or  empty($payment_number))
            return ['status'=>false,'scode'=> -2,'message'=>"Payment Number Or Bill Number is empty"];

        //Send Request parameters By Json Raw Body AND Basic Authentication
        Bill::$response = \Httpful\Request::post(api_url ."bill?format=json")
            ->authenticateWith(api_username, api_password)
            ->sendsJson()
            ->body(json_encode(['payment_number' => $payment_number,'bill_number' => $bill_number,'transaction'=>Bill::$transaction]))
            ->send();
        $response = @json_decode(Bill::$response,true);
        //Connection Ok
        if(!empty($response)){
            Bill::$connection = true;
            return $response;
        }
        //When 3th try Failed
        if(empty($response) AND $try == 3){
            Bill::$connection = false;
            return false;
        }
        //Check transaction status
        if($try == 1){
            $check = Bill::check_transaction();
            if(is_array($check)){
                return $check;
            }
            //When Check proccess failed too
            if($check == -1){
                return false;
            }
        }
        //Retry new request
        $try++;
        return Bill::Pay_bill($bill_number,$payment_number,$try);
    }


    /**
     * @param int $try
     * @return bool|int|array
     */
    static function check_transaction($try = 1){
        $response = \Httpful\Request::post(api_url ."check_transaction?format=json")
            ->authenticateWith(api_username, api_password)
            ->sendsJson()
            ->body(json_encode(['transaction' => Bill::$transaction]))
            ->send();
        //Waiting for new request
        sleep(3);
        //When 3th try Failed
        if(strlen($response) < 1 AND $try == 3){
            return -1;
        }
        if(strlen($response) < 1){
            $try++;
            return Bill::check_transaction($try);
        }
        //If connection is Ok
        $response = @json_decode($response,true);
        //If Transaction defined
        if(!isset($response['status']) OR $response['status'] == false)
            return -2;

        return $response;
    }
}