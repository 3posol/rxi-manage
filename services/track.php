<?php

$dev_folder = (isset($_GET['dev'])) ? 'dev/' : '';

$subject = trim(filter_input(INPUT_GET, 'subject', FILTER_SANITIZE_MAGIC_QUOTES, array('options' => array('default' => ''))));
$track = trim(filter_input(INPUT_GET, 'track', FILTER_SANITIZE_MAGIC_QUOTES, array('options' => array('default' => ''))));

if ($subject > 0) {
	//send data to the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/" . $dev_folder . "email_tracking.php",
		//CURLOPT_URL => "http://localhost/phope/webservice/get_broker_logo.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'track=' . $track . '&subject=' . $subject,
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);
}

header('Content-Type: image/png');
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
