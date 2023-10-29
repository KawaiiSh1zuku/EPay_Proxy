<?php
require_once("config.php");
$sign = getSign($_GET, $key);
if ($sign === $_GET['sign']) {
    $paramstr = "";
	foreach($_GET as $k => $v) {
        $paramstr .= urlencode($k).'='.urlencode($v).'&';
    }
    $paramstr .= "sign=".$sign;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $true_notify_url."?".$paramstr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, "Sh1zuku-API-System");
	$i = 0;
	$success = false;
    while($i < 3 && $success === false){
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			$success = false;
		} else {
			if ($response === false) {
				$success = false;
			} else {
				if($response != "success"){
					$success = false;
				} else {
					$success = true;
				}
			}
		}
		$i++;
	}
	if($success){
		echo "success";
	} else {
		echo "fail";
	}
}
?>