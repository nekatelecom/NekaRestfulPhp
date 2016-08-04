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

class ConnectionTest{
	
    public static  function test($rand_params){
       $response = \Httpful\Request::post(api_url ."test?format=json")
		->sendsJson()
		->body(json_encode($rand_params))
		->send();
        if($rand_params == @json_decode($response,true)){
			return true;
		}
		return false;
    }
}