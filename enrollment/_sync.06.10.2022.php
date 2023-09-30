<?php

require_once('includes/functions.php');

session_start();
if (isset($_POST['type']) && $_POST['type'] != 'email') {
	//check login
	$patient_logged_in = is_patient_logged_in();
	if (!$patient_logged_in) {
		header('Location: login.php');
	}

	$key = pack('H*', $_SESSION[$session_key]['access_code']);
	$iv = base64_decode($_SESSION[$session_key]['data']['iv']);
}
//patient data
if (isset($_POST['type']) && $_POST['type'] == 'patient' && isset($_POST['key']) && $_POST['key'] != '' && isset($data_request[$_POST['key']][2]) && isset($_POST['value'])) {
	$field = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data_request[$_POST['key']][2], MCRYPT_MODE_CFB, $iv));
	$value = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $_POST['value'], MCRYPT_MODE_CFB, $iv));
	$type = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $_POST['type'], MCRYPT_MODE_CFB, $iv));

	$data = array(
		'command'		=> 'sync_enrollment_data',
		'patient' 		=> $_SESSION[$session_key]['data']['id'],
		'access_code'	=> $_SESSION[$session_key]['access_code'],
		'data'			=> $field,
		'value'			=> $value,
		'type'			=> $type
	);
	$response = api_command($data);
}

//providers data
if (isset($_POST['type']) && $_POST['type'] == 'provider' && isset($_POST['data'])) {
	$providers_data = array();
	foreach ($_POST['data'] as $pkey => $provider) {
		if (isset($provider['first_name']) && $provider['first_name'] != '' && isset($provider['last_name']) && $provider['last_name'] != '' && isset($provider['address']) && $provider['address'] != '' && isset($provider['city']) && $provider['city'] != '' && isset($provider['state']) && $provider['state'] != '' && isset($provider['zip']) && $provider['zip'] != '' && isset($provider['phone']) && $provider['phone'] != '') {
			$providers_data[$pkey] = array(
				'first_name' 	=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['first_name'], MCRYPT_MODE_CFB, $iv)),
				'last_name' 	=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['last_name'], MCRYPT_MODE_CFB, $iv)),
				'facility' 		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['facility'], MCRYPT_MODE_CFB, $iv)),
				'address' 		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['address'], MCRYPT_MODE_CFB, $iv)),
				'address2' 		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['address2'], MCRYPT_MODE_CFB, $iv)),
				'city' 			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['city'], MCRYPT_MODE_CFB, $iv)),
				'state' 		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['state'], MCRYPT_MODE_CFB, $iv)),
				'zip' 			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['zip'], MCRYPT_MODE_CFB, $iv)),
				'phone' 		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['phone'], MCRYPT_MODE_CFB, $iv)),
				'fax' 			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $provider['fax'], MCRYPT_MODE_CFB, $iv))
			);
		}
	}

	$data = array(
		'command'		=> 'sync_enrollment_provider_data',
		'patient' 		=> $_SESSION[$session_key]['data']['id'],
		'access_code'	=> $_SESSION[$session_key]['access_code'],
		'data'			=> $providers_data
	);

	$response = api_command($data);
}

//medication data
if (isset($_POST['type']) && $_POST['type'] == 'medication' && isset($_POST['data'])) {
	$medication_data = array();
	foreach ($_POST['data'] as $med) {
		if (isset($med['name']) && $med['name'] != '' && isset($med['strength']) && $med['strength'] != '' && isset($med['frequency']) && $med['frequency'] != '' && isset($med['provider']) && $med['provider'] != '') {
			$medication_data[] = array(
				'name' 			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $med['name'], MCRYPT_MODE_CFB, $iv)),
				'strength' 		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $med['strength'], MCRYPT_MODE_CFB, $iv)),
				'frequency' 	=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $med['frequency'], MCRYPT_MODE_CFB, $iv)),
				'provider' 		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $med['provider'], MCRYPT_MODE_CFB, $iv))
			);
		}
	}

	$data = array(
		'command'		=> 'sync_enrollment_medication_data',
		'patient' 		=> $_SESSION[$session_key]['data']['id'],
		'access_code'	=> $_SESSION[$session_key]['access_code'],
		'data'			=> $medication_data
	);

	$response = api_command($data);
}

//email data
if (isset($_POST['type']) && $_POST['type'] == 'email' && isset($_POST['first_name']) && isset($_POST['middle_initial']) && isset($_POST['last_name']) && isset($_POST['email']) && $_POST['email'] != '') {
	$data = array(
		'command'			=> 'sync_email_data',
		'first_name' 		=> $_POST['first_name'],
		'middle_initial' 	=> $_POST['middle_initial'],
		'last_name' 		=> $_POST['last_name'],
		'email' 			=> $_POST['email']
	);

	//$response = $data;
	$response = api_command($data);
}

echo json_encode($response);