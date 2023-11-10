<?php
header('Content-Type: application/json; charset=utf-8');
require_once("config.php");
$sign = getSign($_GET, $key);
function request($url){
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, "Sh1zuku-API-System");
	$response = curl_exec($ch);
	return $response;
}
if ($sign === $_GET['sign']) {
    $pay_order_api = $apiurl . "/api.php?act=order&pid=" . $_GET['pid'] . "&key=" . $key . "&out_trade_no=" . $_GET['out_trade_no'];
	$response = request($pay_order_api);
	$pay_result = json_decode($response, true);
	$pay_result_msg = $pay_result["msg"];
	$api_trade_no = $pay_result["trade_no"];
	$money = $pay_result["money"];
	$type = $pay_result["type"];
	$ret = array();
	if ($pay_result_msg === "succ"){
		$params = array(
			"pid" => $_GET['pid'],
			"trade_no" => $api_trade_no,
			"out_trade_no" => $_GET['out_trade_no'],
			"type" => $type,
			"name" => "product",
			"money" => $money,
			"trade_status" => "TRADE_SUCCESS",
			"sign_type" => "MD5",
		);
		$newsign = getSign($params, $key);
		foreach($params as $k => $v){
		    $paramstr .= urlencode($k).'='.urlencode($v).'&';
	    }
		$paramstr .= "sign=".$newsign;
		$response = request($true_notify_url . "?" . $paramstr);
		if ($response === "success"){
			$ret = array(
				"code" => 200,
				"msg" => "订单确认成功，正在跳转...",
			);
		} else {
			$ret = array(
				"code" => 403,
				"msg" => "订单已支付，但因未知原因回调失败，请开工单",
			);
		}
	} else {
		$ret = array(
			"code" => 403,
			"msg" => "订单未支付",
		);
    }
	echo json_encode($ret);
} else {
	$ret = array(
		"code" => 403,
		"msg" => "签名校验未通过",
	);
	echo json_encode($ret);
}
?>