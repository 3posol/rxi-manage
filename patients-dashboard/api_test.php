<?php 
//echo 'test';die();
$RXI_API_URL =  "http://172.31.47.116/webservice/patients/api2.php";
//$RXI_API_URL =  "http://3.21.146.177/webservice/patients/api2.php";
$data = array(
			'command'		=> 'api_test',
			'email_address' => 'krathore@raveinfosys.com'
		);

/*
$url = 'http://3.21.146.177/webservice/patients/api2.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_TIMEOUT,10);
$output = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo 'HTTP code: ' . $httpcode;*/

$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => $RXI_API_URL,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => http_build_query($data),
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	$error_no = curl_errno($cu);
	$error_msg = curl_error($cu);

	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	if (curl_errno($cu)) {
			echo 'Error no: '.$error_no.'<br/>';
			echo 'Error msg: '.$error_msg.'<br/>';
		}else{
			echo "<pre>";print_r($response);		
		}
	curl_close($cu);
	
	
	
		
?>