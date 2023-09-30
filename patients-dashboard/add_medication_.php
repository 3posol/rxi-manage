<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

//get providers data
$data = array(
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);

$data = array(
	'DrugAppliedFor' 		=> $_POST['DrugAppliedFor'],
	'Dosage' 				=> $_POST['Dosage'],
	'Directions' 			=> $_POST['Directions'],
	'ProviderId' 			=> $_POST['ProviderId'],
	'PrvFirstName'			=> '',
	'PrvLastName'			=> '',
	'PrvAddress1'			=> '',
	'PrvAddress2'			=> '',
	'PrvCity'				=> '',
	'PrvState'				=> '',
	'PrvZip'				=> '',
	'PrvWorkPhone'			=> '',
	'PrvFaxNumber'			=> ''
);


	//prepare & validate data
$success = true;
$message = '';



		//for encoding
		$encode_key = pack('H*', md5($_SESSION['PLP']['patient']->PatientID));
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		//encode data
		$meds_data = array();
		$meds_data[0]['MedAssistDetailID'] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encode_key, '0', MCRYPT_MODE_CFB, $iv));
		foreach ($data as $property => $value) {
			$meds_data[0][$property] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encode_key, $value, MCRYPT_MODE_CFB, $iv));
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
			$message = 'You\'re new medication was saved successfully.<br/><br/><br/>';
			header('Location: medication.php?success=1');
		} else {
			//fail
			$success = false;
			$message = 'Action failed for unknown reasons, please try submitting the form again.<br/><br/>';
		}

