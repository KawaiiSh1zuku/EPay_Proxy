<?php
require_once("config.php");

$pid = $_GET['pid'];
$out_trade_no = $_GET['out_trade_no'];
$notify_url = $_GET['notify_url'];
$return_url = $_GET['return_url'];
$name = $_GET['name'];
$money = $_GET['money'];
$sign_type = $_GET['sign_type'];

$sign = getSign($_GET, $key);
if($sign === $_GET['sign']){
    //$return_url = str_replace("/#/order/", "/index.php#/stage/order/info?id=", $return_url);
	$return_url = str_replace("/#/order/" . $out_trade_no, "/pay_proxy/confirm.html", $return_url);
    $notify_url = $internal_notify_url;
	$params = array(
	    "money" => $money,
		"name" => $name,
		"notify_url" => $notify_url,
		"out_trade_no" => $out_trade_no,
		"pid" => $pid,
		"return_url" => $return_url,
		"sign_type" => $sign_type,
		);
	$newsign = getSign($params, $key);
	$paramstr = "";
	foreach($params as $k => $v){
		$paramstr .= urlencode($k).'='.urlencode($v).'&';
	}
	$paramstr .= "sign=".$newsign;
	$payapi_url = $apiurl."/submit.php?".$paramstr;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $payapi_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
        echo 'cURL错误: ' . curl_error($ch);
    } else {
		if ($response === false) {
            echo 'cURL请求失败.';
        } else {
            $redirect = "";
			preg_match("/<script>(.*?)<\/script>/si", $response, $matches);
			if (count($matches) > 0) {
                $redirect = $matches[1];
				$redirect = str_replace("window.location.replace('.", $apiurl, $redirect);
				$redirect = str_replace("');", "", $redirect);	
                header("Location: ".$redirect);
				exit();
            } else {
                echo "未找到匹配项";
            }
        }
	}
	curl_close($ch);
}
?>