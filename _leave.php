<?php

$data = array();
$data['session_id'] = (isset($_POST['id']) && trim($_POST['id']) != '') ? trim($_POST['id']) : '';
$data['leave_reason'] = (isset($_POST['leave_reason']) && trim($_POST['leave_reason']) != '') ? trim($_POST['leave_reason']) : '';

//send data to the server
if ($data['session_id'] != '' && $data['session_id'] != '') {
	$cu = curl_init();

	curl_setopt_array($cu, array(
		//CURLOPT_URL => "http://64.233.245.241:43443/save_incomplete_application.php",
		CURLOPT_URL => "http://64.233.245.241:43443/save_abandon_reason.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => http_build_query($data),
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);
}

?>