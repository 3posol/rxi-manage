<?php

	$dev_folder = (isset($_SESSION['dev'])) ? 'dev/' : '';

	//get data from the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/" . $dev_folder . "save_each_application_field.php",
		//CURLOPT_URL => "http://localhost/phope/webservice/get_broker_agent_price_point.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'field_name=' . $_POST['field_name'] . '&field_value=' . $_POST['field_value'] . '&session_id=' . $_POST['session_id'],
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);
	//echo $response;
?>