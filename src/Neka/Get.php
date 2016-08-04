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

class InternetPackages{
    public static  function internet_packages(){
    	$response = \Httpful\Request::post(api_url ."packages/internet?format=json")->authenticateWith(api_username, api_password)->send();
		return @json_decode($response,true);
    }
}