<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

$modified_items = filter_input(INPUT_POST, "modified_items", FILTER_SANITIZE_MAGIC_QUOTES, array('options' => array('default' => '')));

//get data
$data = array(
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);

$data = array();
foreach ($rxi_data->meds as $med) {
	$data[] = (array) $med;
}

$success = true;
$message = '';
$fields_to_update = array('Dosage', 'Directions', 'ProviderId');
if ((isset($_POST['Dosage']))) {
	$form_valid = true;

	/*
	$modified_items_arr = array_filter(explode(',', $modified_items), create_function('$value', 'return $value !== "";'));

	//prepare & validate data
	foreach ($data as $key => $med) {
		if (in_array($key, $modified_items_arr)) {
			foreach ($fields_to_update as $field_name) {
				$data[$key][$field_name] = (isset($_POST[$field_name][$key])) ? trim($_POST[$field_name][$key]) : '';

				//check if valid
				$form_valid = ($form_valid) ? ($data[$key][$field_name] != '') : $form_valid;
			}
		} else {
			unset($data[$key]);
		}
	}
	*/

	//prepare & validate data
	foreach ($data as $key => $value) {
		$data[$key] = (isset($_POST[$key])) ? trim($_POST[$key]) : '';

		//check if valid
		if (strpos($key, 'Prv') === false || (strpos($key, 'Prv') !== false && $data['ProviderId'] == -1 && $key != 'PrvAddress2' && $key != 'PrvFaxNumber')) {
			$form_valid = ($form_valid) ? ($data[$key] != '') : $form_valid;
		}
	}

	if ($form_valid) {
		//for encoding
		$encode_key = pack('H*', md5($_SESSION['PLP']['patient']->PatientID));
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		//encode data
		$meds_data = array();
		foreach ($data as $key => $med) {
			foreach ($med as $property => $value) {
				$meds_data[$key][$property] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encode_key, $value, MCRYPT_MODE_CFB, $iv));
			}
		}

		$meds_data['iv'] = base64_encode($iv);

		//send new data to RxI
		$api_data = array(
			'command'		=> 'update_patient_medication',
			'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
			'access_code'	=> $_SESSION['PLP']['access_code'],
			'data'			=> $meds_data,
			'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
		);

		$response = api_command($api_data);

		if (isset($response->success) && $response->success == 1) {
			//success
			$success = true;
			$message = 'You\'re medication updated information was saved successfully.<br/><br/><br/>';
			#header('Location: medication.php?success=1');
		} else {
			//fail
			$success = false;
			$message = 'Action failed for unknown reasons, please try submitting the form again.<br/><br/>';
		}
	} else {
		//invalid form
		$success = false;
		$message = 'Some data is missing, please fill all the fields and try again.<br/><br/>';
	}
}

$arrReturn = array(
	'success' 	=> $success,
	'message' 	=> $message
);
echo json_encode($arrReturn);

die();

