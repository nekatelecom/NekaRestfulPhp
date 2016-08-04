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

class AuthenticationTest{

    public static  function test(){
       $response = \Httpful\Request::post(api_url ."auth_test?format=json")
		->authenticateWith(api_username, api_password)
		->send();
        return @json_decode($response,true);
    }
}