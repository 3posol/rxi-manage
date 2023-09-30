<?php

$data = array();
$data['p_first_name'] = (isset($_POST['p_first_name'])) ? trim($_POST['p_first_name']) : '';
$data['p_middle_initial'] = (isset($_POST['p_middle_initial'])) ? trim($_POST['p_middle_initial']) : '';
$data['p_last_name'] = (isset($_POST['p_last_name'])) ? trim($_POST['p_last_name']) : '';
$data['p_email'] = (isset($_POST['p_email'])) ? trim($_POST['p_email']) : '';

//send data to the server
if ($data['p_email'] != '') {
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/save_enrollment_email.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => http_build_query($data),
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);

	echo $response;
}

?>