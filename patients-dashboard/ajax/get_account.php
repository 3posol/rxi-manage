<?php

require_once('../includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

//get data

$data = array(
	'command'		=> 'get_patient_data',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);
$_SESSION['PLP']['patient'] = $rxi_data->patient;
clear_patient_last_name();

$data = array(
	'PatientLastName' 			=> $_SESSION['PLP']['patient']->PatientLastName,
	'PatientAddress1' 			=> $_SESSION['PLP']['patient']->PatientAddress1,
	'PatientCity_1' 			=> $_SESSION['PLP']['patient']->PatientCity_1,
	'PatientState_1' 			=> $_SESSION['PLP']['patient']->PatientState_1,
	'PatientZip_1' 				=> $_SESSION['PLP']['patient']->PatientZip_1,
	'PatHomePhoneWACFmt_1' 		=> $_SESSION['PLP']['patient']->PatHomePhoneWACFmt_1,
	'EmergencyContactName' 		=> $_SESSION['PLP']['patient']->EmergencyContactName,
	'EmergencyContactPhone' 	=> $_SESSION['PLP']['patient']->EmergencyContactPhone,
	'EmergencyContact2Name' 	=> $_SESSION['PLP']['patient']->EmergencyContact2Name,
	'EmergencyContact2Phone'	=> $_SESSION['PLP']['patient']->EmergencyContact2Phone,
	'EmergencyContact3Name' 	=> $_SESSION['PLP']['patient']->EmergencyContact3Name,
	'EmergencyContact3Phone' 	=> $_SESSION['PLP']['patient']->EmergencyContact3Phone
);

$success = true;
$message = '';
if ((isset($_POST['PatientLastName']))) {
	$data = array(
		'PatientLastName' 			=> (isset($_POST['PatientLastName'])) ? trim($_POST['PatientLastName']) : '',
		'PatientAddress1' 			=> (isset($_POST['PatientAddress1'])) ? trim($_POST['PatientAddress1']) : '',
		'PatientCity_1' 			=> (isset($_POST['PatientCity_1'])) ? trim($_POST['PatientCity_1']) : '',
		'PatientState_1' 			=> (isset($_POST['PatientState_1'])) ? trim($_POST['PatientState_1']) : '',
		'PatientZip_1' 				=> (isset($_POST['PatientZip_1'])) ? trim($_POST['PatientZip_1']) : '',
		'PatHomePhoneWACFmt_1' 		=> (isset($_POST['PatHomePhoneWACFmt_1'])) ? trim($_POST['PatHomePhoneWACFmt_1']) : '',
		'EmergencyContactName' 		=> (isset($_POST['EmergencyContactName'])) ? trim($_POST['EmergencyContactName']) : '',
		'EmergencyContactPhone' 	=> (isset($_POST['EmergencyContactPhone'])) ? trim($_POST['EmergencyContactPhone']) : '',
		'EmergencyContact2Name' 	=> (isset($_POST['EmergencyContact2Name'])) ? trim($_POST['EmergencyContact2Name']) : '',
		'EmergencyContact2Phone' 	=> (isset($_POST['EmergencyContact2Phone'])) ? trim($_POST['EmergencyContact2Phone']) : '',
		'EmergencyContact3Name' 	=> (isset($_POST['EmergencyContact3Name'])) ? trim($_POST['EmergencyContact3Name']) : '',
		'EmergencyContact3Phone' 	=> (isset($_POST['EmergencyContact3Phone'])) ? trim($_POST['EmergencyContact3Phone']) : ''
	);

	if ($data['PatientLastName'] != '' && $data['PatientAddress1'] != '' && $data['PatientCity_1'] != '' && $data['PatientState_1'] != '' && $data['PatientZip_1'] != '' && $data['PatHomePhoneWACFmt_1'] != '') {
		//for encoding
		$encode_key = pack('H*', md5($_SESSION['PLP']['patient']->PatientID));
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		//encode data
		$patient_data = array();
		foreach ($data as $key => $value) {
			$patient_data[$key] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encode_key, $value, MCRYPT_MODE_CFB, $iv));
		}
		$patient_data['iv'] = base64_encode($iv);

		$api_data = array(
			'command'		=> 'update_patient_data',
			'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
			'access_code'	=> $_SESSION['PLP']['access_code'],
			'data'			=> $patient_data,
			'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
		);

		$response = api_command($api_data);

		if (isset($response->success) && $response->success == 1) {
			//success
			$success = true;
			$message = 'You\'re new information was saved successfully.<br/><br/><br/>';
			header('Location: account.php?success=1');
		} else {
			//fail
			$success = false;
			$message = 'Action failed for unknown reasons, please try submitting the form again.<br/><br/>';
		}
	} else {
		//invalid form
		$success = false;
		$message = 'Some data is missing, please fill all required fields and try again.<br/><br/>';
	}
}

echo json_encode($data);

?>