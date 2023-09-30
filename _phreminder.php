<?php

$hash = (isset($_GET['hash'])) ? addslashes(trim($_GET['hash'])) : '';
if ($hash != '') {
	//send data to the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/incomplete_application_email_reminder.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'action=view&hash=' . $hash,
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);
}

header('Content-Type: image/png');
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');

