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

class Topup{
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
     * @param string $sim_number
     * @param integer $amount
     * @param string $operator
     * @param string $seller_name
     * @param string $sell_type
     * @param bool $amazing
     * @return bool
     */
    public static  function buy_request($sim_number,$amount,$operator = 'None',$seller_name = null,$sell_type = null,$amazing = false){
        Topup::$transaction = md5(microtime(true));
        return Topup::request($sim_number,$amount,$operator,$seller_name,$sell_type,$amazing);
    }

    /**
     * @param string $sim_number
     * @param integer $amount
     * @param string $operator
     * @param string $seller_name
     * @param string $sell_type
     * @param bool $amazing
     * @param int $try
     * @return bool|array
     */
    private static function request($sim_number,$amount,$operator = 'None',$seller_name = null,$sell_type = null,$amazing = false,$try = 1){
        //Reset Response
        Topup::$response = '';
        //Request Array
        $request = ['mobile'=>$sim_number,'amount'=>$amount,"transaction"=>Topup::$transaction];
        //
        if($amazing)
            $request['extra']['amazing'] = true;
        //
        if(!empty($seller_name))
            $request['extra']['seller_name'] = $seller_name;
        //
        if(!empty($sell_type))
            $request['extra']['sell_type'] = $sell_type;

        if($operator != 'None')
            $request['operator'] = $operator;

        //Send Request parameters By Json Raw Body AND Basic Authentication
        Topup::$response = \Httpful\Request::post(api_url ."topup?format=json")
                    ->authenticateWith(api_username, api_password)
                    ->sendsJson()
                    ->body(json_encode($request))
                    ->send();
        $response = @json_decode(Topup::$response,true);

        //Connection Ok
        if(!empty($response)){
            Topup::$connection = true;
           return $response;
        }
        //When 3th try Failed
        if(empty($response) AND $try == 3){
            Topup::$connection = false;
            return false;
        }
        //Check transaction status
        if($try == 1){
            $check = Topup::check_transaction();
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
        return Topup::request($sim_number,$amount,$operator,$seller_name,$sell_type,$amazing,$try);
    }

    /**
     * @param int $try
     * @return bool|int|array
     */
    private static function check_transaction($try = 1){
        $response = \Httpful\Request::post(api_url ."check_transaction?format=json")
            ->authenticateWith(api_username, api_password)
            ->sendsJson()
            ->body(json_encode(['transaction' => Topup::$transaction]))
            ->send();
        //Waiting for new request
        sleep(3);
        //When 3th try Failed
        if(strlen($response) < 1 AND $try == 3){
            return -1;
        }
        if(strlen($response) < 1){
            $try++;
            return Topup::check_transaction($try);
        }
        //If connection is Ok
        $response = @json_decode($response,true);
        //If Transaction defined
        if(!isset($response['status']) OR $response['status'] == false)
            return -2;

        return $response;
    }
}
