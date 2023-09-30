<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

//get data

$data = array(
	'command'		=> 'get_patient_data',
	'patient' 		=> $_SESSION[$session_key]['data']['id'],
	'access_code'	=> $_SESSION[$session_key]['access_code']
);

$rxi_data = api_command($data);
$_SESSION[$session_key]['data'] = decode_patient_data($_SESSION[$session_key]['access_code'], $rxi_data->patient->iv, (array) $rxi_data->patient);

if ($_SESSION[$session_key]['data']['submitted_as_account'] == 1) {
	header('Location: success.php?redirect=1');
}

//
// old register page - preloading code
//

function get_returned_patient ($code) {
	$dev_folder = (isset($_SESSION['dev'])) ? 'dev/' : '';

	//get data from the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/" . $dev_folder . "get_returned_patient.php",
		//CURLOPT_URL => "http://localhost/phope/webservice/get_broker_agent_name.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'code=' . $code,
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);

	return ($response != '') ? (array) json_decode($response) : null;
}

function get_agent_name ($agent_code) {
	$dev_folder = (isset($_SESSION['dev'])) ? 'dev/' : '';

	//get data from the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/" . $dev_folder . "get_broker_agent_name.php",
		//CURLOPT_URL => "http://localhost/phope/webservice/get_broker_agent_name.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'agent=' . $agent_code,
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);

	return ($response != '') ? ' - ' . $response : '';
}

function get_agent_price_point ($agent_code) {
	$dev_folder = (isset($_SESSION['dev'])) ? 'dev/' : '';

	//get data from the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/" . $dev_folder . "get_broker_agent_price_point.php",
		//CURLOPT_URL => "http://localhost/phope/webservice/get_broker_agent_price_point.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'agent=' . $agent_code,
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);

	return ($response != '') ? $response : '50.00';
}

function get_agent_details ($agent_code) {
	$dev_folder = (isset($_SESSION['dev'])) ? 'dev/' : '';

	//get data from the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/" . $dev_folder . "get_broker_agent_details.php",
		//CURLOPT_URL => "http://localhost/phope/webservice/get_broker_agent_name.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'agent=' . $agent_code,
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);

	return ($response != '') ? (array) json_decode($response) : null;
}

//source
if (isset($_GET['source'])) {
	$_SESSION['my_source'] = $_GET['source'];
	$_SESSION['register_data']['p_application_source'] = $_GET['source'];
	$_SESSION['register_data']['p_hear_about'] = $_GET['source'];

	//price point
	$_SESSION['rate'] = get_agent_price_point(substr($_SESSION['my_source'], 0, 9));
}

//agent details
$agent_details = null;
if (isset($_SESSION['my_source'])) {
	$agent_details = get_agent_details(substr($_SESSION['my_source'], 0, 9));

	if (!is_array($agent_details)) {
		unset($_SESSION['my_source']);
		unset($_SESSION['register_data']['p_application_source']);
		unset($_SESSION['register_data']['p_hear_about']);
		unset($_SESSION['rate']);

		unset($_GET['source']);
		unset($_GET['website']);
	}
}

//price point
if (!isset($_SESSION['rate']) || (isset($_SESSION['rate']) && (int) $_SESSION['rate'] == 0)) {
	$_SESSION['rate'] = '50.00';
}

//set rate cookie
setcookie("rate", $_SESSION['rate'], time()+86400, "/", "prescriptionhope.com");

if (isset($_GET['website'])) {
	if ($_GET['website'] == 1) {
		header("Location: https://prescriptionhope.com?hide_pdf=1&test=" . $te . '&a=' . $_SERVER["HTTP_REFERER"]);
	} else {
		header("Location: https://prescriptionhope.com");
	}

	die();
}

if (!isset($_GET['source']) && isset($_SESSION['my_source'])) {
	if (!isset($_SESSION['register_data']['p_hear_about']) || $_SESSION['register_data']['p_hear_about'] == '') {
		header("Location: https://manage.prescriptionhope.com/register.php?source=" . $_SESSION['my_source']);
		die();
	}
}

//
// [END] old register page - preloading code
//

//initialize data
$data = array_fill_keys(array_keys($data_request), '');
$data['p_income_salary'] = 0.00;
$data['p_income_unemployment'] = 0.00;
$data['p_income_pension'] = 0.00;
$data['p_income_annuity'] = 0.00;
$data['p_income_ss_retirement'] = 0.00;
$data['p_income_ss_disability'] = 0.00;
$data['p_income_annual_income'] = 0.00;

//
// LOAD patient data saved in RXI
//

$data_map = get_data_mapping();
foreach ($data as $key => $value) {
	$data[$key] = (isset($data_map[$key]) && isset($_SESSION[$session_key]['data'][$data_map[$key]])) ? $_SESSION[$session_key]['data'][$data_map[$key]] : $data[$key];

	if ($key == 'p_dob' && $data[$key] == '0000-00-00') {
		$data[$key] = "";
	} elseif ($key == 'p_dob' && $data[$key] != '0000-00-00') {
		$data[$key] = date('m/d/Y', strtotime($data[$key]));
	}
}

$radios_submitted = explode(',', $_SESSION['PHEnroll']['data']['submitted_data']);

//load providers
$saved_doctors = 0;
if (isset($_SESSION[$session_key]['data']['doctors']) && count((array) $_SESSION[$session_key]['data']['doctors']) > 0) {
	$doctors_ids = array();
	$data['doctors'] = array();
	foreach ((array) $_SESSION[$session_key]['data']['doctors'] as $dkey => $provider) {
		$data['doctors'][$dkey + 1] = array(
			'doctor_first_name'	=> $provider->first_name,
			'doctor_last_name'	=> $provider->last_name,
			'doctor_facility'	=> $provider->facility,
			'doctor_address'	=> $provider->address,
			'doctor_address2'	=> $provider->address2,
			'doctor_city'		=> $provider->city,
			'doctor_state'		=> $provider->state,
			'doctor_zip'		=> $provider->zipcode,
			'doctor_phone'		=> $provider->phone,
			'doctor_fax'		=> $provider->fax
		);
		$doctors_ids[$provider->id] = $dkey + 1;
	}

	$saved_doctors = count($data['doctors']);
} else {
	$doctor_data = array('doctor_first_name', 'doctor_last_name', 'doctor_facility', 'doctor_address', 'doctor_address2', 'doctor_city', 'doctor_state', 'doctor_zip', 'doctor_phone', 'doctor_fax');
	$data['doctors'] = array(array_fill_keys($doctor_data, ''));
}

//load medication
if (isset($_SESSION[$session_key]['data']['medication']) && count($_SESSION[$session_key]['data']['medication']) > 0) {
	$data['medication'] = array();
	foreach ((array) $_SESSION[$session_key]['data']['medication'] as $dkey => $med) {
		$med_dr_id = '';
		if (isset($doctors_ids)) {
			reset($doctors_ids);
			$med_dr_id = (isset($doctors_ids[$med->doctor_id])) ? $doctors_ids[$med->doctor_id] : key($doctors_ids);
		}

		$data['medication'][$dkey + 1] = array(
			'medication_doctor'		=> $med_dr_id,
			'medication_name'		=> $med->name,
			'medication_strength'	=> $med->strength,
			'medication_frequency'	=> $med->frequency
		);
	}
} else {
	$medication_data = array('medication_doctor', 'medication_name', 'medication_strength', 'medication_frequency');
	//$data['medication'] = array(array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''));
	$data['medication'] = array(array_fill_keys($medication_data, ''));
}


$invalid_cc = false;
$valid_form = true;
$form_submitted = false;
$response_msg = '';

if (!isset($_POST['bSubmit'])) {
	//save blank data into session
	//$data['p_hear_about'] = (isset($_GET['source'])) ? trim(urldecode($_GET['source'])) : '';
	$data['p_hear_about'] = (isset($_SESSION['my_source']) && is_array($agent_details)) ? trim(urldecode($_SESSION['my_source'])) . $agent_details['agent_full_name'] : $data['p_hear_about'];
	$data['p_application_source'] = (isset($_SESSION['my_source'])) ? trim(urldecode($_SESSION['my_source'])) : $data['p_application_source'];
	
	if($data['p_application_source'] == '')
	{
		$data['p_application_source'] = $_COOKIE['url_code'];
	}

	//patient returns, preload his/her data
	if (isset($_GET['return'])) {
		$existent_patient_data = get_returned_patient(addslashes(trim($_GET['return'])));
		if (isset($existent_patient_data['patient'])) {
			//var_dump((array) $existent_patient_data['patient']);
			foreach ((array) $existent_patient_data['patient'] as $key => $value) {
				$data[$key] = $value;
			}
		}
	}
} else {
	//form submitted

	//
	//get form data
	//

	$tmp_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_MAGIC_QUOTES);
	foreach ($tmp_data as $tmp_data_key => $tmp_data_value) {
		$data[$tmp_data_key] = (is_string($tmp_data_value)) ? trim($tmp_data_value) : $tmp_data_value;
	}

	if (!isset($_POST['p_medicare_part_d'])) {
		$data['p_medicare_part_d'] = '';
	}

	if (!isset($_POST['p_medicaid_denial'])) {
		$data['p_medicaid_denial'] = '';
	}

	if (!isset($_POST['p_lis_denial'])) {
		$data['p_lis_denial'] = '';
	}

	//doctors
	$data['doctors'] = array();
	//
	$tmp['doctor_first_name'] 	= filter_var_array($_POST['doctor_first_name'], FILTER_SANITIZE_MAGIC_QUOTES);
	$tmp['doctor_last_name'] 	= filter_var_array($_POST['doctor_last_name'], 	FILTER_SANITIZE_MAGIC_QUOTES);
	$tmp['doctor_facility']		= filter_var_array($_POST['doctor_facility'], 	FILTER_SANITIZE_MAGIC_QUOTES);
	$tmp['doctor_address'] 		= filter_var_array($_POST['doctor_address'], 	FILTER_SANITIZE_MAGIC_QUOTES);
	$tmp['doctor_address2'] 	= filter_var_array($_POST['doctor_address2'], 	FILTER_SANITIZE_MAGIC_QUOTES);
	$tmp['doctor_city'] 		= filter_var_array($_POST['doctor_city'], 		FILTER_SANITIZE_MAGIC_QUOTES);
	$tmp['doctor_state'] 		= filter_var_array($_POST['doctor_state'], 		FILTER_SANITIZE_MAGIC_QUOTES);
	$tmp['doctor_zip'] 			= filter_var_array($_POST['doctor_zip'], 		FILTER_SANITIZE_MAGIC_QUOTES);
	$tmp['doctor_phone'] 		= filter_var_array($_POST['doctor_phone'], 		FILTER_SANITIZE_MAGIC_QUOTES);
	$tmp['doctor_fax'] 			= filter_var_array($_POST['doctor_fax'], 		FILTER_SANITIZE_MAGIC_QUOTES);
	//
	foreach ($tmp['doctor_first_name'] as $key => $values) {
		$data['doctors'][$key]['doctor_first_name'] = trim($tmp['doctor_first_name'][$key]);
		$data['doctors'][$key]['doctor_last_name'] 	= trim($tmp['doctor_last_name'][$key]);
		$data['doctors'][$key]['doctor_facility']	= trim($tmp['doctor_facility'][$key]);
		$data['doctors'][$key]['doctor_address'] 	= trim($tmp['doctor_address'][$key]);
		$data['doctors'][$key]['doctor_address2'] 	= trim($tmp['doctor_address2'][$key]);
		$data['doctors'][$key]['doctor_city'] 		= trim($tmp['doctor_city'][$key]);
		$data['doctors'][$key]['doctor_state'] 		= trim($tmp['doctor_state'][$key]);
		$data['doctors'][$key]['doctor_zip'] 		= trim($tmp['doctor_zip'][$key]);
		$data['doctors'][$key]['doctor_phone'] 		= trim($tmp['doctor_phone'][$key]);
		$data['doctors'][$key]['doctor_fax'] 		= trim($tmp['doctor_fax'][$key]);
	}

	//meds
	$medsCount = 0;
	//
	if (isset($_POST['medication_doctor']) && isset($_POST['medication_name']) && isset($_POST['medication_strength']) && isset($_POST['medication_frequency'])) {
		//get form data
		$tmp['medication_doctor'] 		= filter_var_array($_POST['medication_doctor'], 	FILTER_SANITIZE_MAGIC_QUOTES);
		$tmp['medication_name'] 		= filter_var_array($_POST['medication_name'], 		FILTER_SANITIZE_MAGIC_QUOTES);
		$tmp['medication_strength'] 	= filter_var_array($_POST['medication_strength'], 	FILTER_SANITIZE_MAGIC_QUOTES);
		$tmp['medication_frequency'] 	= filter_var_array($_POST['medication_frequency'], 	FILTER_SANITIZE_MAGIC_QUOTES);

		foreach ($tmp['medication_doctor'] as $key => $values) {
			$data['medication'][$key]['medication_doctor'] 		= trim($tmp['medication_doctor'][$key]);
			$data['medication'][$key]['medication_name']		= trim($tmp['medication_name'][$key]);
			$data['medication'][$key]['medication_strength'] 	= trim($tmp['medication_strength'][$key]);
			$data['medication'][$key]['medication_frequency'] 	= trim($tmp['medication_frequency'][$key]);

			if ($data['medication'][$key]['medication_doctor'] != '' && $data['medication'][$key]['medication_name'] != '' && $data['medication'][$key]['medication_strength'] != '' && $data['medication'][$key]['medication_frequency'] != '') {
				$medsCount++;
			}
		}
	}

	unset($data['p_has_salary']);
	unset($data['p_has_unemployment']);
	unset($data['p_has_pension']);
	unset($data['p_has_annuity']);
	unset($data['p_has_ss_retirement']);
	unset($data['p_has_ss_disability']);
	unset($data['doctor_first_name']);
	unset($data['doctor_last_name']);
	unset($data['doctor_facility']);
	unset($data['doctor_address']);
	unset($data['doctor_address2']);
	unset($data['doctor_city']);
	unset($data['doctor_state']);
	unset($data['doctor_zip']);
	unset($data['doctor_phone']);
	unset($data['doctor_fax']);
	unset($data['medication_name']);
	unset($data['medication_strength']);
	unset($data['medication_frequency']);
	unset($data['medication_doctor']);

	//save data into session
	$_SESSION['register_data'] = $data;

	//
	//validate data
	//

	foreach ($data_request as $data_key => $data_info) {
		if ($data_info[0] && $data[$data_key] == '') {
			//missing data
			$valid_form = false;
			//echo (!$valid_form) ? '<!-- ' . $data_key . ' - '  . $data[$data_key] . ' - ' . ' -->' : '';
		}
	}

	//doctors
	if (count($data['doctors']) == 0) {
		$valid_form = false;
	} else {
		foreach ($data['doctors'] as $doctor) {
		    // ryank  || $doctor['doctor_phone'] == '' removed 4/25
			//if ($doctor['doctor_first_name'] == '' || $doctor['doctor_last_name'] == '' || $doctor['doctor_address'] == '' || $doctor['doctor_city'] == '' || $doctor['doctor_state'] == '' || $doctor['doctor_zip'] == '') {
			if ($doctor['doctor_first_name'] == '' || $doctor['doctor_last_name'] == '' || $doctor['doctor_address'] == '' || $doctor['doctor_city'] == '' || $doctor['doctor_state'] == '' || $doctor['doctor_zip'] == '' || $doctor['doctor_phone'] == '') {
				//missing data
				$valid_form = false;
			}
		}
	}

	//meds
	if ($medsCount == 0) {
		$valid_form = false;
	} else {
		foreach ($data['medication'] as $med) {
			if (($med['medication_doctor'] != '' || $med['medication_name'] != '' || $med['medication_strength'] != '' || $med['medication_frequency'] != '') && ($med['medication_doctor'] == '' || $med['medication_name'] == '' || $med['medication_strength'] == '' || $med['medication_frequency'] == '')) {
				//missing data
				$valid_form = false;
			}
		}
	}

	//payment validation
	if ($data['p_payment_method'] == 'cc' && !(isset($agent_details['use_payment']) && (bool) $agent_details['use_payment'])) {
		if ($data['p_cc_type'] == '' || $data['p_cc_number'] == '' || $data['p_cc_exp_month'] == '' || $data['p_cc_exp_year'] == '' || $data['p_cc_cvv'] == '') {
			$valid_form = false;
		}
		//card expiration date should be in the future
		elseif (sprintf('%d-%02d-01', $data['p_cc_exp_year'], $data['p_cc_exp_month']) < date('Y-m-01')) {
			$valid_form = false;
		}
		//validate credit card
		elseif ($data['p_cc_number'] != '4111111111111111') {
			$cc_data = array(
				'PatientCCName' 	=> $data['p_first_name'] . ' ' . $data['p_last_name'],
				'PatientCCType' 	=> $data['p_cc_type'],
				'PatientCCNumber' 	=> $data['p_cc_number'],
				'PatientCCExpMonth'	=> $data['p_cc_exp_month'],
				'PatientCCExpYear' 	=> $data['p_cc_exp_year'],
				'PatientCCCVV'		=> $data['p_cc_cvv']
			);

			$cu = curl_init();

			curl_setopt_array($cu, array(
				CURLOPT_URL => "http://64.233.245.241:43443/ccvalidation2.php",
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($cc_data),
				CURLOPT_RETURNTRANSFER => true
			));

			$response = curl_exec($cu);
			curl_close($cu);

			if (strpos($response, 'ERROR') !== false) {
				$invalid_cc = true;
				$valid_form = false;
			}
		}
	} elseif ($data['p_payment_method'] == 'ach') {
		if ($data['p_ach_holder_name'] == '' || $data['p_ach_routing'] == '' || $data['p_ach_account'] == '') {
			$valid_form = false;
		}
	} elseif (isset($agent_details['use_payment']) && (bool) $agent_details['use_payment']) {
		$valid_form = true;
	} else {
		$valid_form = false;
	}

	//
	if ($valid_form) {
		//if we're here, all is good => send data to the webservice and load the confirmation page

		//clean the empty data
		foreach ($data['doctors'] as $key => $doctor) {
			if ($doctor['doctor_first_name'] == '' || $doctor['doctor_last_name'] == '') {
				unset($data['doctors'][$key]);
			}
		}
		foreach ($data['medication'] as $key => $medication) {
			if ($medication['medication_name'] == '') {
				unset($data['medication'][$key]);
			}
		}

		//prepare to encrypt all data
		$encoding_key = pack('H*', $_SESSION[$session_key]['access_code']);
		$encoding_iv = base64_decode($_SESSION[$session_key]['data']['iv']);

		$patient_data = array();
		foreach ($data as $key => $value) {
			if ($key == 'doctors' || $key == 'medication') {
				$patient_data[$key] = $value;
			} else {
				$patient_data[((isset($data_request[$key][2])) ? $data_request[$key][2] : $key)] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encoding_key, $value, MCRYPT_MODE_CFB, $encoding_iv));
			}
		}

		$api_data = array(
			'command'		=> 'submit_enrollment',
			'patient' 		=> $_SESSION[$session_key]['data']['id'],
			'access_code'	=> $_SESSION[$session_key]['access_code'],
			'data'			=> $patient_data
		);

		$response = api_command($api_data);

		//
		//log the success apps
		//
		$file = '../apps/' . date('Y-m-d-') . preg_replace('/[^a-z0-9]+/', '-', strtolower($data['p_email']));
		$data_tmp = $data;
		foreach (array('p_payment_method', 'p_cc_type', 'p_cc_number', 'p_cc_exp_month', 'p_cc_exp_year', 'p_cc_cvv', 'p_ach_holder_name', 'p_ach_routing', 'p_ach_account', 'register_step', 'bNextStep', 'session_id') as $skip_key) {
			unset($data_tmp[$skip_key]);
		}
		$data_tmp['response'] = $response;
		file_put_contents($file, json_encode($data_tmp));
		//

		$response_success = false;

		if (isset($response->success)) {
			switch ($response->success) {
				case 1:
					$response_success = true;
					$response_msg = '<p class="moduleSubheader">Enrollment Form Submitted Successfully.</p>';

					if (isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') {
						//agent
						$broker_name = trim(substr(urldecode($_SESSION['register_data']['p_application_source']), 9));
				        $broker_name = ($broker_name == 'Access Health Insurance, Inc') ? 'JibeHealth' : $broker_name;
						$broker_rate = (isset($_SESSION['rate']) && $_SESSION['rate'] > 0) ? $_SESSION['rate'] : 50;
						//$response_msg .= '<p class="moduleSubheader">Thank you for submitting an Enrollment Form to Prescription Hope.  We are proud to have partnered with ' . $broker_name . ' to obtain your medications for only ' . number_format($broker_rate, 2). ' per month per medication.<br><br>If you have any questions about your account with Prescription Hope, or any questions in general about the program, please visit our website at <a href="https://www.prescriptionhope.com">www.prescriptionhope.com</a> or call us at 1-877-296-4673.<br><br>A Patient Advocate will review your form and begin your Enrollment process.  You can expect a phone call and Welcome Packet from Prescription Hope soon.<br><br><strong>Please Note:</strong> If you opted for a Checking Account payment, please submit a voided check to us through mail or through fax.</p>';
						$response_msg .= '<p class="moduleSubheader">Thank you for submitting an Enrollment Form to Prescription Hope.  We are proud to have partnered with ' . $broker_name . ' to obtain your medications for only ' . number_format($broker_rate, 2). ' per month per medication.<br><br>If you have any questions about your account with Prescription Hope, or any questions in general about the program, please visit our website at <a href="https://www.prescriptionhope.com">www.prescriptionhope.com</a> or call us at 1-877-296-4673.<br><br>A Patient Advocate will review your form and begin your Enrollment process.  You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>';
					} else {
						//direct
						$response_msg .= '<p class="moduleSubheader">Thank you for submitting an Enrollment Form to Prescription Hope. A Patient Advocate will review your form and begin your Enrollment process.  You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>';
					}

					$response_msg .= '<br><p class="moduleSubheader">To print a copy of your Enrollment Form for your records.<br><br><span class="small-button-orange"><a href="save_application.php" class="skipLeave" target="_blank">Click here</a></span></p>';
					//$response_msg .= '<br><p class="moduleSubheader">To get a copy of your Enrollment Form for your records through email.<br><br><span class="small-button-orange" id="app_email_link"><a href="save_application.php?method=email" id="app_email_send" class="skipLeave">Click here</a></span></p>';
					//$response_msg .= '<br><p class="moduleSubheader"><a href="save_application.php">.</a><a href="save_application.php?method=email">.</a></p>';

					//send PDF app through email
					if (isset($data['p_email']) && trim($data['p_email']) != '') {
						$data['p_payment_method']	= $_SESSION['register_data']['p_payment_method'];
						$data['p_cc_type']			= $_SESSION['register_data']['p_cc_type'];
						$data['p_cc_number']		= $_SESSION['register_data']['p_cc_number'];

						require_once('includes/fpdf/fpdf.php');
						require_once('includes/fpdf/fpdi/fpdi.php');
						require_once('includes/phpmailer/class.phpmailer.php');
						require_once('pdf_application.php');

						$mail = new PHPMailer(); // defaults to using php "mail()"
						$mail->CharSet = 'UTF-8';
						$mail->Encoding = "base64";
						$mail->isHTML(true);

						//$mail->IsSMTP();
						//$mail->SMTPDebug = 3;
						//$mail->SMTPAuth = true;
						//$mail->Host = $smtp_server;
						//$mail->Port = 25;
						//$mail->Username = $smtp_user;
						//$mail->Password = $smtp_pass;

						$mail->SetFrom('DoNotReply@prescriptionhope.com', 'Prescription Hope');
						$mail->AddReplyTo("DoNotReply@prescriptionhope.com","Prescription Hope");

						$mail->AddAddress($data['p_email'], (isset($data['p_first_name']) && isset($data['p_last_name'])) ? ($data['p_first_name'] . ' ' . $data['p_last_name']) : '');
						//if ($data['p_email'] == "georgep@wowbrands.com") {
						//	$mail->AddAddress("jake@prescriptionhope.com");
						//	$mail->AddAddress("bryan@prescriptionhope.com");
						//}

						$mail->Subject = "Online Application Confirmation";

						if (isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') {
							//agent enrollment
							$broker_name = trim(substr(urldecode($_SESSION['register_data']['p_application_source']), 9));
					        $broker_name = ($broker_name == 'Access Health Insurance, Inc') ? 'JibeHealth' : $broker_name;
							$broker_rate = (isset($_SESSION['rate']) && $_SESSION['rate'] > 0) ? $_SESSION['rate'] : 50;

							$mail->AddEmbeddedImage('images/agents_header.jpg', 'ph_agents_header');
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'><img src='cid:ph_agents_header' width='899' alt='Prescription Hope'></p>";

							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 14px;'><strong>Thank you for submitting your enrollment form.</strong></p>";
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>THIS IS AN AUTOMATED EMAIL - PLEASE DO NOT RESPOND TO THIS MESSAGE AS IT IS NOT CHECKED</p>";

							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Thank you for submitting an Enrollment Form to Prescription Hope. We are proud to have partnered with " . $broker_name . " to obtain your medications for only $" . number_format($broker_rate, 2) . " per month per medication.</p>";
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>If you have any questions about your account with Prescription Hope, or any questions in general about the program, please visit our website at <a href='https://www.prescriptionhope.com'>www.prescriptionhope.com</a> or call us at 1-877-296-4673.</p>";
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>A Patient Advocate will review your form and begin your Enrollment process. You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>";

							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Attached to this email you'll find a copy of your Enrollment Form for your records.</p>";
							//$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'><strong>Please Note:</strong> If you opted for a Checking Account payment, please submit a voided check to us through mail or through fax.</p>";

							$mail->Body .= "<br>";
							$mail->Body .= "<table style='font-family: Arial, sans-serif; font-size: 12px;'><tr><td valign='top'><strong>Mail:</strong><br>Prescription Hope, Inc.<br>PO Box 2700<br>Westerville, OH 43086</td><td width='50'>&nbsp;</td><td valign='top'><strong>Fax:</strong><br>1-877-298-1012</td></tr></table>";
							$mail->Body .= "<br>";

							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Sincerely,</p>";
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Prescription Hope, Inc.<br>P: 877.296.4673<br><a href='http://www.prescriptionhope.com'>www.prescriptionhope.com</a></p>";

							$mail->AddEmbeddedImage('images/logo_41.png', 'ph_logo');
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'><img src='cid:ph_logo' width='150' alt='Prescription Hope Logo'></p>";

							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>CONFIDENTIALITY NOTICE: This is an e-mail transmission and the information is privileged and/or confidential. It is intended only for the use of the individual or entity to which it is addressed. If you have received this communication in error, please notify the sender at the reply e-mail address and delete it from your system without copying or forwarding it. If you are not the intended recipient, you are hereby notified that any retention, distribution, or dissemination of this information is strictly prohibited. Thank you.</p>";
						} else {
							//direct enrollment
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>THIS IS AN AUTOMATED EMAIL - PLEASE DO NOT RESPOND TO THIS MESSAGE AS IT IS NOT CHECKED</p>";
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Thank you for submitting an Enrollment Form to Prescription Hope. A Patient Advocate will review your form and begin your Enrollment process. You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>";
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Attached to this email you'll find a copy of your Enrollment Form for your records.</p>";
							//$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'><strong>Please Note:</strong> If you opted for a Checking Account payment, please submit a voided check to us through mail or through fax.</p>";

							$mail->Body .= "<br>";
							$mail->Body .= "<table style='font-family: Arial, sans-serif; font-size: 12px;'><tr><td valign='top'><strong>Mail:</strong><br>Prescription Hope, Inc.<br>PO Box 2700<br>Westerville, OH 43086</td><td width='50'>&nbsp;</td><td valign='top'><strong>Fax:</strong><br>1-877-298-1012</td></tr></table>";
							$mail->Body .= "<br>";

							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Sincerely,</p>";
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Prescription Hope, Inc.<br>P: 877.296.4673<br>F: 877.298.1012<br><a href='http://www.prescriptionhope.com'>www.prescriptionhope.com</a></p>";

							$mail->AddEmbeddedImage('images/logo_41.png', 'ph_logo');
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'><img src='cid:ph_logo' width='150' alt='Prescription Hope Logo'></p>";

							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>CONFIDENTIALITY NOTICE: This is an e-mail transmission and the information is privileged and/or confidential. It is intended only for the use of the individual or entity to which it is addressed. If you have received this communication in error, please notify the sender at the reply e-mail address and delete it from your system without copying or forwarding it. If you are not the intended recipient, you are hereby notified that any retention, distribution, or dissemination of this information is strictly prohibited. Thank you.</p>";
						}

						$mail->AddStringAttachment(pdf_application($data, 'S'), 'Prescription_Hope_Application.pdf');

						//	stopped when started SalesForce
						//$rs = $mail->Send();
						//echo (!$rs) ? 'email error:' . $mail->ErrorInfo : 'email sent';
					}

					header('Location: success.php');

					//clear the session data
					//unset($_SESSION['register_data']);
					break;

				case 2:
					$response_msg = '<p class="moduleSubheader">ERROR - there is already a patient with the same SSN in our system.</p><br>';
					break;

				case 9:
					$response_msg = '<p class="moduleSubheader">Operation failed - database server error.</p>';
					break;

				default:
					$response_msg = '<p class="moduleSubheader">Operation failed.</p>';
					$response_msg .= '<p class="moduleSubheader">Server Message: ' . var_export($response, true) . '</p>';
					break;
			}
		}

		if ($response_success === false) {
			//save data locally
			//$file = 'apps/' . $data['session_id'];
			//foreach (array('register_step', 'bNextStep', 'session_id') as $skip_key) {
			//	unset($data[$skip_key]);
			//}
			//file_put_contents($file, json_encode($data));
		}

		$form_submitted = true;
	} else {
		;//echo '<!-- something was not valid -->';
	}
}

?>
<?php
	$JobID 				= (isset($_GET['j'])) ? $_GET['j'] : '';
	$SubscriberID 		= (isset($_GET['sfmc_sub'])) ? $_GET['sfmc_sub'] : '';
	$ListID 			= (isset($_GET['l'])) ? $_GET['l'] : '';
	$UrlID 				= (isset($_GET['u'])) ? $_GET['u'] : '';
	$MemberID 			= (isset($_GET['mid'])) ? $_GET['mid'] : '';
	$sub_id				= (isset($_GET['sub_id'])) ? $_GET['sub_id'] : '';	//email id
	$batch_id			= (isset($_GET['jb'])) ? $_GET['jb'] : '';
	
	setcookie('JobID', $JobID);
	setcookie('SubscriberID', $SubscriberID);
	setcookie('ListID', $ListID);
	setcookie('UrlID', $UrlID);
	setcookie('MemberID', $MemberID);
	setcookie('SUBID', $sub_id);
	setcookie('BatchID', $batch_id);

	$JobID_c 		= (isset($_COOKIE['JobID'])) ? $_COOKIE['JobID'] : '';
	$SubscriberID_c = (isset($_COOKIE['SubscriberID'])) ? $_COOKIE['SubscriberID'] : '';
	$ListID_c 		= (isset($_COOKIE['ListID'])) ? $_COOKIE['ListID'] : '';
	$UrlID_c 		= (isset($_COOKIE['UrlID'])) ? $_COOKIE['UrlID'] : '';
	$MemberID_c 	= (isset($_COOKIE['MemberID'])) ? $_COOKIE['MemberID'] : '';
	$sub_id_c 		= (isset($_COOKIE['SUBID'])) ? $_COOKIE['SUBID'] : '';
	$batch_id_c		= (isset($_COOKIE['BatchID'])) ? $_COOKIE['BatchID'] : '';
	
	if ($JobID_c !='' && $SubscriberID_c !='' && $ListID_c !='' && $UrlID_c !='' && $MemberID_c !='' && $sub_id_c !='' && $batch_id_c !=''){
		$strTP = '<img src=\'http://click.s10.exacttarget.com/conversion.aspx?xml=';
		$strTP .= '<system><system_name>tracking</system_name>';
		$strTP .= '<action>conversion</action>';
		$strTP .= '<member_id>'.$MemberID_c.'</member_id>';
		$strTP .= '<job_id>'.$JobID_c.'</job_id>';
		$strTP .= '<sub_id>'.$SubscriberID_c.'</sub_id>';
		$strTP .= '<list>'.$ListID_c.'</list>';
		$strTP .= '<original_link_id>'.$UrlID_c.'</original_link_id>';
		$strTP .= '<BatchID>'.$batch_id_c.'</BatchID>';
		$strTP .= '<conversion_link_id>1</conversion_link_id>';
		$strTP .= '<link_alias>Conversion Tracking</link_alias>';
		$strTP .= '<display_order>1</display_order>';
		$strTP .= '<email>'.$sub_id_c.'</email>';
		$strTP .= '<data_set></data_set></system>\'';
		$strTP .= ' width="1" height="1">';
		print $strTP;
	}
?>
<?php include('_header.php'); ?>

<div class="container-fluid">
	<div id="enroll-now" class="row row-1 one_column white-bg text-left" style='z-index:1000;'>
		<div class="content createAccountBox align-center">
			<div id='applicationForm'>
				<?php if (!$form_submitted) { ?>
					<!-- ENROLLMENT FORM -->

					<script type="text/javascript">

						jQuery().ready(function() {
							jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
							jQuery.validator.addMethod("custom_date",function(t,e){return t=t.replace(/\s+/g,""),td=t.split("/"),td=td[2]+"-"+td[0]+"-"+td[1],this.optional(e)||t.length>8&&t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/)&&td<=new Date().toISOString().substring(0,10)},"Please specify a valid date (mm/dd/yyyy)"),
							jQuery.validator.addMethod("SSN",function(t,e){return t=t.replace(/\s+/g,""),this.optional(e)||t.length>8&&t.match(/^\d{3}-?\d{2}-?\d{4}$/)},"Please specify a valid SSN number"),
							jQuery.validator.addMethod("lettersonly", function(value, element) { return this.optional(element) || /^[a-z'. ]+$/i.test(value); }, "Please insert only letters.");
							jQuery.validator.addMethod("valid_exp_date", function(value, element) { return ("20" + value.substr(3, 2) + value.substr(0, 2)) > <?=date('Ym')?> && parseInt(value.substr(0, 2)) > 0 && parseInt(value.substr(0, 2)) <= 12; }, "Please specify a valid date (MM/YY).");

							//show doctors if any exists
							//UpdateDoctorsList();
							updateMedicationDoctorsDropdown();

							//load radio buttons and select objects with the correct value
							preloadSpecialFormValues();

							//show the complete patient profile, if preloaded
							showSelectedPatientProfile();

							//show income section?
							showIncomeSection();

							//show/hide the 2nd-level insurance questions
							show2ndLevelInsuranceQuestions(false);

							//update the medication list in the payment information section
							syncMedicationData();

							//show the payment method details, if preloaded
							showSelectedPaymentMethods();

							//activate form validation
							form_validator = jQuery("#register_form").validate({
								rules: {
									p_first_name:				  { required: true, lettersonly: true },
									p_middle_initial: 			  { required: false, ascii: true, maxlength: 1 },
									p_last_name:				  { required: true, lettersonly: true },
									p_is_minor:					  { required: true, ascii: true },
									p_parent_first_name:		  { required: jQuery("#p_is_minor_yes"), ascii: true },
									p_parent_middle_initial: 	  { required: jQuery("#p_is_minor_yes"), ascii: true , maxlength: 1 },
									p_parent_last_name:			  { required: jQuery("#p_is_minor_yes"), ascii: true },
									p_parent_phone:				  { required: jQuery("#p_is_minor_yes"), phoneUS: true },
									p_address: 					  { required: true, ascii: true },
									p_city: 					  { required: true, ascii: true },
									p_state: 					  { required: true },
									p_zip: 						  { required: true, digits: true, minlength: 5, maxlength: 5 },
									p_phone: 					  { required: true, phoneUS: true },
									p_fax: 						  { required: false, ascii: true/*, phoneUS: true*/ },
									p_email: 					  { required: true, email: true },
									p_alternate_contact_name: 	  { required: false, ascii: true },
									p_alternate_phone: 			  { required: false, ascii: true/*, phoneUS: true*/ },

									p_dob: 						  { required: true, custom_date: true },
									p_gender: 					  { required: true },
									p_ssn: 						  { required: true, SSN: true, minlength: 9, maxlength: 11 },
									//p_ssn_masked: 				  { required: true, minlength: 9, maxlength: 11 }, //SSN: true,
									p_has_income: 				  { required: true },
									p_household: 				  { required: true, digits: true },
									p_married: 					  { required: true },
									p_employment_status: 		  { required: jQuery("#p_has_income") },
									p_uscitizen: 				  { required: jQuery("#p_has_income") },
									p_disabled_status: 			  { required: jQuery("#p_has_income") },
									p_medicare:					  { required: jQuery("#p_has_income") },
									p_medicare_part_d: 			  { required: jQuery("#p_medicare_yes") },
									p_medicaid: 				  { required: jQuery("#p_has_income") },
									p_medicaid_denial:			  { required: jQuery("#p_medicaid_yes") },
									p_lis: 						  { required: jQuery("#p_has_income") },
									p_lis_denial: 				  { required: jQuery("#p_lis_yes") },
									p_hear_about: 				  { required: true, ascii: true },
									//p_hear_about_1: 			  { required: false },
									//p_hear_about_2: 			  { required: false },
									//p_hear_about_3: 			  { required: false },

									p_income_salary:			  { required: false, number: true },
									p_income_unemployment:		  { required: false, number: true },
									p_income_pension:			  { required: false, number: true },
									p_income_annuity:			  { required: false, number: true },
									p_income_ss_retirement:		  { required: false, number: true },
									p_income_ss_disability:		  { required: false, number: true },
									p_income_file_tax_return_yes: { required: jQuery("#p_has_income") },
									p_income_zero:				  { required: { depends: function(element) {
																	                    return ((jQuery('input[name=p_income_salary]').val() == '' || jQuery('input[name=p_income_salary]').val() == "") &&
																	                            (jQuery('input[name=p_income_unemployment]').val() == 0 || jQuery('input[name=p_income_unemployment]').val() == "") &&
																	                            (jQuery('input[name=p_income_pension]').val() == 0 || jQuery('input[name=p_income_pension]').val() == "") &&
																	                            (jQuery('input[name=p_income_annuity]').val() == 0 || jQuery('input[name=p_income_annuity]').val() == "") &&
																	                            (jQuery('input[name=p_income_ss_retirement]').val() == 0 || jQuery('input[name=p_income_ss_retirement]').val() == "") &&
																	                            (jQuery('input[name=p_income_ss_disability]').val() == 0 || jQuery('input[name=p_income_ss_disability]').val() == ""));
					                							  }}},

									p_payment_agreement:		  { required: true },
									p_service_agreement:		  { required: true },
									p_guaranty_agreement: 		  { required: true },

									p_payment_method:			  { required: true },

									p_cc_type:					  { required: jQuery("#p_payment_method_cc") },
									p_cc_number:				  { required: jQuery("#p_payment_method_cc"), creditcardtypes: function(element) {return {visa: (jQuery('input[name=p_cc_type]').val() == "Visa"), mastercard: (jQuery('input[name=p_cc_type]').val() == "Mastercard"), amex: (jQuery('input[name=p_cc_type]').val() == "American Express"), discover: (jQuery('input[name=p_cc_type]').val() == "Discover")};}},
									p_cc_exp_date:				  { required: jQuery("#p_payment_method_cc"), valid_exp_date: true},
									//p_cc_exp_month:				  { required: jQuery("#p_payment_method_cc"), min: function(element) {return (jQuery('select[name=p_cc_exp_year]').val() != '<?php echo date('Y');?>') ? '01' : '<?php echo date('m');?>';}},
									//p_cc_exp_year:				  { required: jQuery("#p_payment_method_cc"), min: function(element) {return (jQuery('select[name=p_cc_exp_month]').val() < '<?php echo date('m');?>') ? '<?php echo (int)date('Y')+1;?>' : '<?php echo date('Y');?>';}},
									p_cc_cvv:					  { required: jQuery("#p_payment_method_cc"), digits: true, minlength: 3, maxlength: 4 },

									p_ach_holder_name:			  { required: jQuery("#p_payment_method_ach"), ascii: true },
									p_ach_routing:				  { required: jQuery("#p_payment_method_ach"), digits: true, maxlength: 9 },
									p_ach_account:				  { required: jQuery("#p_payment_method_ach"), digits: true }

									//p_acknowledge_agreement: 	  { required: true }
								},

					 			messages: {
									p_income_zero: "Please check this if you currently have no income.",
									p_cc_exp_month: {
										min: 'Please enter an expiration date (Month / Year) that it\'s not in the past.'
									},
									p_ach_routing: {
										digits: 'Please insert a valid number.'
									},
									p_ach_account: {
										digits: 'Please insert a valid number.'
									}
					 			},

								highlight: jQueryValidation_Highlight,

								unhighlight: jQueryValidation_Unhighlight,

								errorPlacement: jQueryValidation_ShowErrors,
								//errorPlacement: function() {},

								invalidHandler: refreshRadioGroupsValidationIcons,

								onkeyup: false
							});

							jQuery("input[type='radio']").keypress(function(e){
							    if(e.keyCode === 13) {
						    	    jQuery(this).attr("checked", 'checked');
							        return false;
							    }
							});

							/*
							jQuery('input[name=p_dob]').change(function() {
								dob = jQuery(this).val();
								if (getAge(dob) < 18) {
									jQuery('input#p_is_minor_yes').trigger('click');
								} else {
									jQuery('input#p_is_minor_no').trigger('click');
								}
							})
							*/

							jQuery("input[name=p_is_minor]").change(showSelectedPatientProfile);

							jQuery('input[name=p_has_income]').change(showIncomeSection);

							jQuery("input[name='p_medicare'], input[name=p_medicaid], input[name=p_lis]").change(function(e) {show2ndLevelInsuranceQuestions(e);});
							jQuery('input[type="radio"]').change(refreshRadioGroupsValidationIcons);

							jQuery('input,select').focus(function (e) {
								if (jQuery(this).hasClass('field-error-only')) {
									refreshRadioGroupsValidationIcons();
								}

								//make sure the form scrolls to the correct spot even for the iOS devices
								document.body.scrollTop = jQuery(this).offset().top;
							});

							//save email
							//jQuery('input[name="p_email"]').blur(function (){
							//	if (jQuery(this).val() != '' && jQuery(this).valid() == true) {
							//		jQuery.ajax({
							//			method: 'POST',
							//			url: '_save_email.php',
							//			data: {
							//				p_first_name: 		jQuery('input[name="p_first_name"]').val(),
							//				p_middle_initial: 	jQuery('input[name="p_middle_initial"]').val(),
							//				p_last_name: 		jQuery('input[name="p_last_name"]').val(),
							//				p_email: 			jQuery(this).val()
							//			}
							//		}).done(function(response) {
										//console.log(response);
							// 		});
							//	}
							//});

							jQuery('input#p_income_zero').change(function() {
								if (jQuery(this).is(':checked')) {
									//jQuery('input.input_zero').val('0.00');
									jQuery('input.input_zero').val('');
								}
							});

							jQuery('input.input_zero').on('change, blur', function() {
								updateZeroIncome();
								updateTotalAnnualIncome();

							    jQuery(this).val((jQuery(this).val() != '') ? parseFloat(jQuery(this).val().replace(/[^0-9.]/g, '')).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2, useGrouping: true}) : ''); //0.00

							    //eliminate any wrong formatted number error
							    if (jQuery('label[for="' + jQuery(this).attr('name') + '"]').length > 0) {
								    if (jQuery('label[for="' + jQuery(this).attr('name') + '"]').html() == 'Please enter a valid number.') {
								    	jQuery('label[for="' + jQuery(this).attr('name') + '"]').remove();
								    }
							    }

								//check values
								if (jQuery(this).val() != '') {
									if (jQuery(this).val().replace(',', '') < 100 || jQuery(this).val().replace(',', '') > 9999.99) {
										if (!jQuery(this).hasClass("error")) {
											jQuery(this).removeClass("correct");
											jQuery(this).addClass("error");
										}

										if (jQuery('label.' + jQuery(this).attr('id')).length == 0) {
											errorLabel = jQuery('<label>').addClass(jQuery(this).attr('id')).addClass('error').addClass('invalid-field').addClass(jQuery(this).attr('name')).text('Are you sure this is your monthly income from this source?');
											errorLabel.insertAfter(jQuery(this));
										}
									} else {
										jQuery(this).removeClass("error");
										jQuery('label.' + jQuery(this).attr('id')).remove();
										if (jQuery(this).val() != '') {
											jQuery(this).addClass("correct");
										}
									}
								}
							});
							//
							jQuery('input.input_zero').focus(function() {
								val = jQuery(this).val().replace(',', '') - 0;
								if (val == 0) {
									jQuery(this).val('');
								} else {
								    jQuery(this).val(val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2, useGrouping: false})); //0.00
									jQuery(this).prop('selectionStart', 0).prop('selectionEnd', 0);
								}
							});
							//
							jQuery('input.input_zero').blur(function() {
								val = jQuery(this).val() - 0;
								if (val == 0) {
									jQuery(this).val(''); // 0.00
								}
							});
							//
							jQuery('input.input_zero').on('keyup', function() {
						    	//jQuery('label.' + jQuery(this).attr('id')).remove();
							});
							//
							jQuery('input.income_checkbox_field').change(function() {
								elemName = jQuery(this).attr('name').replace('p_has_', 'p_income_');
								if (jQuery(this).is(':checked')) {
									jQuery('div.' + elemName).removeClass('hidden');
								} else {
									jQuery('div.' + elemName).addClass('hidden');
								}
							});

							//mask ssn
							//jQuery("input[name='p_ssn_masked']").focus(function (e) {
							//	var unmasked_value = jQuery("input[name='p_ssn']").val();
							//	if (unmasked_value) {
							//		jQuery(this).val(unmasked_value);
							//	}
							//});
							//jQuery("input[name='p_ssn_masked']").blur(function (e) {
							//	jQuery("input[name='p_ssn']").val(jQuery(this).val());
							//	jQuery(this).val(jQuery(this).val().replace(/^\d{3}-\d{2}/, 'xxx-xx'));
							//});

							//add masks
							jQuery("input[name='p_phone']").mask("999-999-9999", {clearIfNotMatch: true});
							jQuery("input[name='p_fax']").mask("999-999-9999", {clearIfNotMatch: true});
							jQuery("input[name='p_alternate_phone']").mask("999-999-9999", {clearIfNotMatch: true});
							jQuery("input[name='p_parent_phone']").mask("999-999-9999", {clearIfNotMatch: true});
							jQuery("input[name='p_dob']").mask("99/99/9999", {clearIfNotMatch: true});
							jQuery("input[name='p_ssn']").mask("999-99-9999", {clearIfNotMatch: true});
							//jQuery("input[name='p_ssn_masked']").mask("***-**-9999");
							//jQuery("input[name='p_ssn']").inputCloak({type: 'ssn'});
							jQuery("input.dr-zip").mask("99999", {clearIfNotMatch: true});
							jQuery("input.dr-phone").mask("999-999-9999", {clearIfNotMatch: true});
							jQuery("input.dr-fax").mask("999-999-9999", {clearIfNotMatch: true});
							jQuery("input[name='p_cc_exp_date']").mask("99/99", {clearIfNotMatch: true});

							/*
							jQuery('.doctors_dropdown').change(function(e) {
								if (jQuery(this).find(':selected').text() == 'Add new doctor') {
									medication_line = parseInt(jQuery(this).attr('class').split(' ')[1].replace('medication_line_', ''));

									showAddDoctorForm(medication_line);
								} else if (jQuery(this).val() != '') {
									//hide any existent add new doctor form
									hideAddDoctorForm(e);
								}
							});
							*/

							jQuery('#bAddANewDoctor').click(AddNewDoctorForm); //AddANewDoctor
							jQuery('.dr-data').change(UpdateDoctor);
							jQuery('.dr-data').blur(UpdateDoctor);

							jQuery('#bAddANewMedication').click(AddANewMedication);
							jQuery('.med-data').change(syncMedicationData);
							jQuery('select.med-data').keyup(syncMedicationData);
							//jQuery('.med-data').change(UpdateMedication);

							//
							jQuery("input[name=p_payment_method]").change(showSelectedPaymentMethods);
							//jQuery('#p_payment_method_cc').trigger('click');

							//update cc exp date
							jQuery('input[name="p_cc_exp_date"]').change(function (e) {
								if (jQuery(this).val().length == 5) {
									jQuery('input[name="p_cc_exp_month"]').val(jQuery(this).val().substr(0, 2));
									jQuery('input[name="p_cc_exp_year"]').val("20" + jQuery(this).val().substr(3, 2));
								}
							});

							//form submit
							//jQuery('form#register_form').submit(scrollToInvalidFormElements);

							//jQuery("#register_form input, #register_form select").each(function(index) {
							//	if (jQuery(this).is('input') || jQuery(this).is('select')) {
							//		if (form_validator.check(((jQuery(this).is('input')) ? 'input' : 'select') + '[name=' + jQuery(this).attr('name') + ']')) {
							//			jQueryValidation_Unhighlight(this);
							//		}
							//	}
							//});

							jQuery("#register_form input, #register_form select").change(function(e) {
								if (jQuery(this).is('input') || jQuery(this).is('select')) {
									if (jQuery(this).valid()) {
										jQueryValidation_Unhighlight(this);
									} else {
										jQueryValidation_Highlight(this);
									}
								}
							});

							jQuery("form#register_form").submit(function(e) {
								//if (jQuery(this).valid()) {

								//check doctors and meds
								hasDoctors = false;
								for (dr_id = 1; dr_id <= jQuery('input.doctor-fields').length; dr_id++) {
									if (jQuery('input[name="doctor_first_name[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_last_name[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_address[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_city[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_state[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_zip[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_phone[' + dr_id + ']"]').val() != '') {
										hasDoctors = true;

										//flag dr. fields as correct
										jQuery('input[name="doctor_first_name[' + dr_id + ']"]').removeClass('error').removeClass('field-error-only').addClass('correct');
										jQuery('input[name="doctor_last_name[' + dr_id + ']"]').removeClass('error').removeClass('field-error-only').addClass('correct');
										jQuery('input[name="doctor_address[' + dr_id + ']"]').removeClass('error').removeClass('field-error-only').addClass('correct');
										jQuery('input[name="doctor_city[' + dr_id + ']"]').removeClass('error').removeClass('field-error-only').addClass('correct');
										jQuery('input[name="doctor_state[' + dr_id + ']"]').removeClass('error').removeClass('field-error-only').addClass('correct');
										jQuery('input[name="doctor_zip[' + dr_id + ']"]').removeClass('error').removeClass('field-error-only').addClass('correct');
										jQuery('input[name="doctor_phone[' + dr_id + ']"]').removeClass('error').removeClass('field-error-only').addClass('correct');
									}
								}

								//validate medication rows
								hasMeds = false;
								medsWithErrors = false;
								first_invalid_row = false;

								for (i = 1; i <= parseInt(jQuery('.med-data').length / 4); i++) {
									m_medication = jQuery("input[name='medication_name[" + i + "]']").val();
									m_strength = jQuery("input[name='medication_strength[" + i + "]']").val();
									m_frequency = jQuery("input[name='medication_frequency[" + i + "]']").val();
									m_doctor = jQuery("select[name='medication_doctor[" + i + "]']").val();

									//if there is any info on this line, then check if we have all the required information
									if (m_medication || m_strength || m_frequency || m_doctor) {
										mn_error = (!m_medication || !isAscii(m_doctor)) ? "<label class='error invalid-field nopad-error'>" + ((!m_medication) ? "Required field" : ((!isAscii(m_medication)) ? "Invalid characters" : "&nbsp;")) + "</label>" : "";
										ms_error = (!m_medication || !isAscii(m_doctor)) ? "<label class='error invalid-field nopad-error'>" + ((!m_strength) ? "Required field" : ((!isAscii(m_strength)) ? "Invalid characters" : "&nbsp;")) + "</label>" : "";
										mf_error = (!m_medication || !isAscii(m_doctor)) ? "<label class='error invalid-field nopad-error'>" + ((!m_frequency) ? "Required field" : ((!isAscii(m_frequency)) ? "Invalid characters" : "&nbsp;")) + "</label>" : "";
										md_error = (!m_medication || !isAscii(m_doctor)) ? "<label class='error invalid-field nopad-error'>" + ((!m_doctor) ? "Required field" : ((!isAscii(m_doctor)) ? "Invalid characters" : "&nbsp;")) + "</label>" : "";

										validMed = (m_doctor && isAscii(m_doctor) && m_medication && isAscii(m_medication) && m_strength && isAscii(m_strength) && m_frequency && isAscii(m_frequency)) ? true : false;

										if (validMed) {
											hasMeds = true;
										} else {
											jQuery("input[name='medication_name[" + i + "]']").after(mn_error);
											jQuery("input[name='medication_strength[" + i + "]']").after(ms_error);
											jQuery("input[name='medication_frequency[" + i + "]']").after(mf_error);
											jQuery("select[name='medication_doctor[" + i + "]']").after(md_error);

											medsWithErrors = true;

											if (first_invalid_row === false) {
												first_invalid_row = jQuery("input[name='medication_name[" + i + "]']");
											}
										}
									}
								}

								//hasMeds = false;
								//for(med_id = 1; med_id <= jQuery('input.medication-fields').length; med_id++) {
								//	if (jQuery('input[name="medication_name[' + med_id + ']"]').val() != '' && jQuery('input[name="medication_strength[' + med_id + ']"]').val() != '' && jQuery('input[name="medication_frequency[' + med_id + ']"]').val() != '' && jQuery('input[name="medication_doctor[' + med_id + ']"]').val() != '') {
								//		hasMeds = true;
								//	}
								//}

								if (jQuery(this).valid() && hasDoctors && hasMeds && !medsWithErrors) {
									jQuery('input#bSubmit').css('background', '#c0c0c0');
									jQuery('input#bSubmit').val('Submitting ...');
									//jQuery('input#bSubmit').prop('disabled', true);

									if (!submitConfirmed) {
										//preventEnrollmentSubmit();
										return true;
									} else {
										return true;
									}
								} else {
									//determine if an income is need it
									var income_needed = false;
									if (jQuery('input[name="p_has_income"]').is(':checked')) {
										income_needed = (jQuery('input[name="p_has_income"]:checked').val() == 1 && jQuery('input[name="p_income_annual_income"]').val() == 0);
										if (jQuery('input[name="p_has_income"]:checked').val() == 1 && jQuery('input[name="p_income_annual_income"]').val() == 0 && jQuery('.missing-income-source').length == 0) {
											jQuery("#first_income_source_row").before("<p class='red align-left missing-income-source'><label class='nopad-error'>Please provide at least one income source.<br><br></label></p>");
										}
									}

									//show doctors & meds errors
									if (!hasMeds || medsWithErrors) {
										//jQuery("#medication_form").before("<label class='error' style='width: auto !important; padding: 0; font-size: 16px;'>Please add at least one medication.<br><br></label>");
										//
										//jQuery(window).scrollTop(jQuery("#medication_form").position().top - 250);
										//jQuery(window).scrollLeft(0);

										//if (!medsWithErrors) {
											jQuery("#medication_form").before("<label class='error nopad-error'>Please add at least one medication, all fields are required.<br><br></label>");
											jQuery("input.med-data,select.med-data").each(function (index, elem) {
												if (jQuery(elem).val() == '') {
													//show errors
													jQuery(elem).removeClass('correct');
													jQuery(elem).error('correct');
													jQuery(elem).addClass('field-error-only');
												}
											});
										//}
									}

									if (!hasDoctors) {
										//jQuery("#doctor_form").before("<label class='error' style='width: auto !important; padding: 0; font-size: 16px;'>Please add at least one doctor.<br><br></label>");
										jQuery(".dr-form").eq(0).before("<label class='error' style='width: auto !important; padding: 0; font-size: 16px;'>Please add at least one doctor.<br><br></label>");
										jQuery(".dr-form").eq(0).find("input.dr-required,select.dr-required").each(function (index, elem) {
											if (jQuery(elem).val() == '') {
												//show errors
												jQuery(elem).removeClass('correct');
												jQuery(elem).error('correct');
												jQuery(elem).addClass('field-error-only');
											} else {
												jQuery(elem).removeClass('error').removeClass('field-error-only').addClass('correct');
											}
										});
									} else {

									}

									//
									//scroll to the first error
									//

									first_invalid_element = jQuery('input.error, select.error, textarea.error').eq(0);
jQuery('#ttt').html(first_invalid_element.attr('name'));
									if ((jQuery('input.error, select.error, textarea.error').length > 0 && first_invalid_element.attr('name') != 'p_payment_agreement' && first_invalid_element.attr('name') != 'p_service_agreement' && first_invalid_element.attr('name') != 'p_guaranty_agreement' && first_invalid_element.attr('name') != 'p_cc_number' && first_invalid_element.attr('name') != 'p_cc_exp_date' && first_invalid_element.attr('name') != 'p_cc_cvv') || income_needed) {
jQuery('#ttt').html(jQuery('#ttt').html() + ' - 1 ');
										if (jQuery('input.error, select.error, textarea.error').length > 0 && first_invalid_element.attr('name') != 'p_payment_agreement' && first_invalid_element.attr('name') != 'p_service_agreement' && first_invalid_element.attr('name') != 'p_guaranty_agreement' && first_invalid_element.attr('name') != 'p_cc_number' && first_invalid_element.attr('name') != 'p_cc_exp_date' && first_invalid_element.attr('name') != 'p_cc_cvv') {
jQuery('#ttt').html(jQuery('#ttt').html() + ' - 2 ');
											first_invalid_element.focus();
											adjustScroll(first_invalid_element);
										} else {
jQuery('#ttt').html(jQuery('#ttt').html() + ' - 3 ');
											if (income_needed) {
												jQuery('input[name="p_has_salary"]').focus();
jQuery('#ttt').html(jQuery('#ttt').html() + ' - 4 ');
												adjustScroll(first_invalid_element);
											}
										}
									} else {
jQuery('#ttt').html(jQuery('#ttt').html() + ' - 5 ');
										if (!hasMeds || medsWithErrors) {
jQuery('#ttt').html(jQuery('#ttt').html() + ' - 6 ');
											//if (!medsWithErrors) {
												first_invalid_row = jQuery("input[name='medication_name[1]']");
											//}

											if (first_invalid_row !== false) {
jQuery('#ttt').html(jQuery('#ttt').html() + ' - 7 ');
												jQuery(window).scrollTop(first_invalid_row.position().top - 250);
												jQuery(window).scrollLeft(0);
												first_invalid_row.focus();
												adjustScroll(first_invalid_element);
											}
										}

										if (!hasDoctors) {
jQuery('#ttt').html(jQuery('#ttt').html() + ' - 8 ');
											//jQuery(window).scrollTop(jQuery("#doctor_form").position().top - 250);
											jQuery(window).scrollTop(jQuery(".dr-form").eq(0).position().top - 250);
											jQuery(window).scrollLeft(0);
										}

										if (hasMeds && hasMeds && !medsWithErrors && jQuery('input.error, select.error, textarea.error').length > 0) {
jQuery('#ttt').html(jQuery('#ttt').html() + ' - 9 ');
											first_invalid_element.focus();
											adjustScroll(first_invalid_element);
										}
									}

									return false;
								}

								//} else {
								//	scrollToInvalidFormElements();
								//}
							});

							//tooltips
							jQuery("a[rel],input[rel]").hover(
								function(e) {
									if (jQuery(this).attr("rel") != "") {
										pos = jQuery(this).position();

										if (jQuery(this).attr("rel").substring(0, 7) == 'images/') {
											jQuery("#register_form").append("<p class='tooltips'><img src='"+ jQuery(this).attr("rel") +"' class='tooltip-img' /></p>");
										} else {
											jQuery("#register_form").append("<p class='tooltips'>" + jQuery(this).attr("rel") + "</p>");
										}

										jQuery(".tooltips")
											.css("top", (pos.top + 40) + "px")
											.css("left", (((pos.left - 70) > 0) ? (pos.left - 70) : 0) + "px")
											.fadeIn("fast");

										if (jQuery(this).attr('name') == 'dr_address') {
											setTimeout(function(){
												jQuery(".tooltips").remove();
											}, 5000);
										}
									}
								},
								function() {
									if (jQuery(this).attr("rel") != "") {
										jQuery(".tooltips").remove();
									}
								}
							);
							jQuery("input[rel]").focus(
								function(e) {
									if (jQuery(this).attr("rel") != "") {
										pos = jQuery(this).position();

										if (jQuery(this).attr("rel").substring(0, 7) == 'images/') {
											jQuery("#register_form").append("<p class='tooltips'><img src='"+ jQuery(this).attr("rel") +"' class='tooltip-img' /></p>");
										} else {
											jQuery("#register_form").append("<p class='tooltips'>" + jQuery(this).attr("rel") + "</p>");
										}

										jQuery(".tooltips")
											.css("top", (pos.top + 40) + "px")
											.css("left", (((pos.left - 70) > 0) ? (pos.left - 70) : 0) + "px")
											.fadeIn("fast");

										setTimeout(function(){
											jQuery(".tooltips").remove();
										}, 5000);
									}
								}
							);

							//reminder
							jQuery("#btReminderClose").click(function(event) {
								event.preventDefault();
								jQuery('#reminderPopup').addClass("no-show");
							});

							//disable "Go" button on Android to submit the form
							jQuery("input[type='text'],input[type='tel']").keypress(function(e){
								if(e.keyCode === 13 && /Android/.test(navigator.userAgent)) {
									e.preventDefault();
								}
							});

							$ = jQuery;

							jQuery("input[type='text'],input[type='tel'],input[type='checkbox'],input[type='radio'],select").blur(syncPatientData);

							/*
							jQuery("input[type='text'],select").blur(function(e){
								if ($(this).val() != ""){
									$.post( "save_field.php", { field_name: $(this).attr("name"), field_value: $(this).val(), session_id: "<?=session_id()?>" })
									.done(function( data ) {
										console.log(data);
									});
								}
							});
							*/

							//GOOGLE Analytics
							//ga('send', 'event', 'steps', 'formload', '20150806, enrollment, form, step1, personal-info', {'nonInteraction': 1})
						});

					</script>

					<!-- Facebook Pixel -->
					<script>
						fbq('track', 'Lead');
					</script>

					<h2 class=" dblue-text no-text-transformation">Watch The Prescription Hope Process</h2>
					<div class="video-panel"><div class="intrinsic-container intrinsic-container-16x9"><iframe src="https://player.vimeo.com/video/235383572" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div></div>

					<br>
					<h2 class=" dblue-text no-text-transformation">Enrollment Form</h2>

					<?php if (isset($agent_details['use_payment']) && (bool) $agent_details['use_payment']) { ?>
						<p class="subhead2-blue left-alignment" style="color: #dd0000; text-transform: none;">
							Your benefits provider (<?=$agent_details['corporation_name']?>) has offered the Prescription Hope Pharmacy Program at no cost to you. Please complete the enrollment form below and submit the enrollment form to begin the process.
							<br><br>
							In the event your benefits provider does not cover the cost of the $<?=$_SESSION['rate']?> per month per medication service fee in the future, you will be asked to provide payment information at that time.
						</p>
					<?php } ?>

					<p class="align-left moduleSubheader">
						All of the information requested below is required by the pharmaceutical company to process your medication orders. All of your information will be kept confidential and protected.
					</p><br>

					<p class="align-left moduleSubheader">
						One form per person.
					</p><br>

					<p class="align-left moduleSubheader">
						Fields with asterisks (<span class="red">*</span>) are required.
					</p>

					<form id="register_form" method="post" action="enroll.php" autocomplete="nope">

					<br>
					<h2 class="dblue-text no-text-transformation">Patient Information</h2>
					<br>

					<div>
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_first_name" value="<?php echo htmlspecialchars(stripslashes($data['p_first_name']));?>" class="LoNotSensitive <?=(($data['p_first_name'] != '') ? 'correct' : '')?>" placeholder="First Name *"></div>
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['p_middle_initial']));?>" maxlength="1" class="LoNotSensitive <?=(($data['p_middle_initial'] != '') ? 'correct' : '')?>" placeholder="Middle Initial"></div>
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_last_name" value="<?php echo htmlspecialchars(stripslashes($data['p_last_name']));?>" class="LoNotSensitive <?=(($data['p_last_name'] != '') ? 'correct' : '')?>" placeholder="Last Name *"></div>
					</div>
					<div class="clear"></div>

					<div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_email" value="<?php echo $data['p_email'];?>" class="LoNotSensitive <?=(($data['p_first_name'] != '') ? 'correct' : '')?>" placeholder="Email Address *" data-hints="By providing your email address ..." readonly></div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_phone" value="<?php echo $data['p_phone'];?>" class="LoNotSensitive" placeholder="Phone *"></div>
					</div>

					<div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_address" id="p_address" value="<?php echo htmlspecialchars(stripslashes($data['p_address']));?>" class="LoNotSensitive" placeholder="Street Address *"></div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_address2" value="<?php echo htmlspecialchars(stripslashes($data['p_address2']));?>" class="LoNotSensitive" placeholder="Apartment, Suite, Unit, etc."></div>
					</div>
					<div class="clear"></div>

					<div>
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_city" value="<?php echo htmlspecialchars(stripslashes($data['p_city']));?>" class="LoNotSensitive" placeholder="City *"></div>
						<div class="third-width">
							<select data-type="patient" name="p_state" preload="<?php echo $data['p_state'];?>" class="full-width LoNotSensitive" placeholder="State *">
								<option value=""></option>
								<option value="AL">Alabama</option>
								<option value="AK">Alaska</option>
								<option value="AZ">Arizona</option>
								<option value="AR">Arkansas</option>
								<option value="CA">California</option>
								<option value="CO">Colorado</option>
								<option value="CT">Connecticut</option>
								<option value="DE">Delaware</option>
								<option value="DC">District Of Columbia</option>
								<option value="FL">Florida</option>
								<option value="GA">Georgia</option>
								<option value="HI">Hawaii</option>
								<option value="ID">Idaho</option>
								<option value="IL">Illinois</option>
								<option value="IN">Indiana</option>
								<option value="IA">Iowa</option>
								<option value="KS">Kansas</option>
								<option value="KY">Kentucky</option>
								<option value="LA">Louisiana</option>
								<option value="ME">Maine</option>
								<option value="MD">Maryland</option>
								<option value="MA">Massachusetts</option>
								<option value="MI">Michigan</option>
								<option value="MN">Minnesota</option>
								<option value="MS">Mississippi</option>
								<option value="MO">Missouri</option>
								<option value="MT">Montana</option>
								<option value="NE">Nebraska</option>
								<option value="NV">Nevada</option>
								<option value="NH">New Hampshire</option>
								<option value="NJ">New Jersey</option>
								<option value="NM">New Mexico</option>
								<option value="NY">New York</option>
								<option value="NC">North Carolina</option>
								<option value="ND">North Dakota</option>
								<option value="OH">Ohio</option>
								<option value="OK">Oklahoma</option>
								<option value="OR">Oregon</option>
								<option value="PA">Pennsylvania</option>
								<option value="RI">Rhode Island</option>
								<option value="SC">South Carolina</option>
								<option value="SD">South Dakota</option>
								<option value="TN">Tennessee</option>
								<option value="TX">Texas</option>
								<option value="UT">Utah</option>
								<option value="VT">Vermont</option>
								<option value="VA">Virginia</option>
								<option value="WA">Washington</option>
								<option value="WV">West Virginia</option>
								<option value="WI">Wisconsin</option>
								<option value="WY">Wyoming</option>
							</select>
						</div>
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_zip" value="<?php echo $data['p_zip'];?>" maxlength="5" class="LoNotSensitive" placeholder="ZIP Code *"></div>
					</div>
					<div class="clear"></div>

					<!--div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_fax" value="<?php echo $data['p_fax'];?>" class="not_required_phone" placeholder="Fax Number"></div>
					</div>
					<div class="clear"></div-->

					<div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" id="p_dob" name="p_dob" value="<?php echo $data['p_dob'];?>" class="LoNotSensitive" placeholder="Date of Birth * (mm/dd/yyyy)" data-hint="Your Date of Birth is required by the pharmaceutical company to process your medication orders."></div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_ssn" value="<?php echo $data['p_ssn'];?>" maxlength="11" class="" placeholder="Social Security Number *" data-hint="Your Social Security Number is required by the pharmaceutical company to process your medication orders."><!--input autocomplete="nope" type="hidden" data-type="patient" name="p_ssn_masked" value="<?php echo preg_replace('/^\d{3}-\d{2}/', 'xxx-xx', $data['p_ssn']);?>" maxlength="11" class="" placeholder="Social Security Number *" data-hintsss="Your Social Security Number is required by the pharmaceutical company to process your medication orders."--></div>
					</div>
					<div class="clear"></div>

					<div class="form-row">
						<div class="half-width align-left">
							<label for="p_gender">Gender *</label>
						</div>
						<div class="half-width align-right">
							<label for="p_gender_m" class='rb-container no_width'>Male
								<input autocomplete="nope" type="radio" data-type="patient" id="p_gender_m" name="p_gender" value="M" class="LoNotSensitive" preload="<?php echo $data['p_gender'];?>">
								<span class="rb-checkmark"></span>
							</label>
							<label for="p_gender_f" class='rb-container no_width'>Female
								<input autocomplete="nope" type="radio" data-type="patient" id="p_gender_f" name="p_gender" value="F" class="LoNotSensitive">
								<span class="rb-checkmark"></span>
							</label>
						</div>
					</div>
					<div class="clear"></div>

					<!--div class="form-group-spacer"></div-->

					<div class="form-row">
						<div class="half-width align-left">
							<label for="p_is_minor">Is this application on behalf of a minor? *</label>
						</div>
						<div class="half-width align-right">
							<label for="p_is_minor_yes" class='rb-container no_width'>Yes
								<input autocomplete="nope" type="radio" data-type="patient" id="p_is_minor_yes" name="p_is_minor" value="1" class="LoNotSensitive" <?php echo ((in_array('is_minor', $radios_submitted) && $data['p_is_minor'] != '') ? 'preload="' . (int)$data['p_is_minor'] . '"' : ''); ?>>
								<span class="rb-checkmark"></span>
							</label>
							<label for="p_is_minor_no" class='rb-container no_width'>No
								<input autocomplete="nope" type="radio" data-type="patient" id="p_is_minor_no" name="p_is_minor" value="0" class="LoNotSensitive">
								<span class="rb-checkmark"></span>
							</label>
						</div>
					</div>
					<div class="clear"></div>

					<div class="patient_parent_profile no-show">
						<div>
							<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_parent_first_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_first_name']));?>" class="LoNotSensitive" placeholder="Parent/Guardian First Name *"></div>
							<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_parent_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_middle_initial']));?>" maxlength="1" class="LoNotSensitive" placeholder="Parent/Guardian Middle Initial *"></div>
							<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_parent_last_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_last_name']));?>" class="LoNotSensitive" placeholder="Parent/Guardian Last Name *"></div>
						</div>
						<div class="clear"></div>

						<input autocomplete="nope" type="text" data-type="patient" name="p_parent_phone" value="<?php echo $data['p_parent_phone'];?>" class="LoNotSensitive" placeholder="Parent/Guardian Phone *">
					</div>

					<div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_alternate_contact_name" value="<?php echo htmlspecialchars(stripslashes($data['p_alternate_contact_name']));?>" class="LoNotSensitive" placeholder="Alternate Contact Name"></div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_alternate_phone" value="<?php echo $data['p_alternate_phone'];?>" class="LoNotSensitive not_required_phone" placeholder="Alternate Contact Phone"></div>
					</div>
					<div class="clear"></div>

					<?php if($data['p_hear_about'] == '2685-4694 Access Health Insurance, Inc') $data['p_hear_about'] = '2685-4694 JibeHealth'; ?>
					<?php if (isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') { ?>
						<input autocomplete="nope" type="text" data-type="patient" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about']));?>" class="full-width" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>>
					<?php } else { ?>
						<div class="full-width">
							<select data-type="patient" name="p_hear_about" id="p_hear_about" data-value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about']));?>" class="full-width LoNotSensitive" placeholder="How did you hear about Prescription Hope? *">
								<option value=""></option>
								<option value="Facebook">Facebook</option>
								<option value="Instagram">Instagram</option>
								<option value="Internet">Internet</option>
								<option value="Insurance">Insurance</option>
								<option value="Healthcare Provider">Healthcare Provider</option>
								<option value="Pharmacy">Pharmacy</option>
								<option value="Diabetes Educator">Diabetes Educator</option>
								<option value="Social Worker">Social Worker</option>
								<option value="Family Member">Family Member</option>
								<option value="Friend">Friend</option>
								<option value="Co-Worker">Co-Worker</option>
								<option value="Previous Patient">Previous Patient</option>
								<option value="Referral By Current Member of the Prescription Hope Program">Referral By Current Member of the Prescription Hope Program</option>
								<option value="Television">Television</option>
								<option value="Paper Mailing">Paper Mailing</option>
								<!--option value="Social Media">Social Media</option-->
								<option value="Linkedin">Linkedin</option>
								<option value="Twitter">Twitter</option>
								<option value="WPBF25 Health and Safety with Dr. Oz">WPBF25 Health and Safety with Dr. Oz</option>
								<option value="Other">Other</option>
							</select>
						</div>
					<?php } ?>
					<input autocomplete="nope" type="hidden" data-type="patient" name="p_application_source" value="<?=((isset($data['p_application_source'])) ? $data['p_application_source'] : $_COOKIE['url_code'])?>">

					<!--div class="form-group-spacer"></div-->

					<br>
					<div class="h-line">&nbsp;</div>

					<h2 class=" dblue-text no-text-transformation">Monthly Household Income Information</h2>

					<p class="normal align-left">
						Your monthly household income verification is required by the pharmaceutical company to process your medication orders. It will not be used for any other purposes and will remain confidential.
					</p>

					<div class="form-group-spacer"></div>

					<!--p class="normal align-left">
						Your Date of Birth and Social Security Number are required by the pharmaceutical companies for completing the application process to begin filling your medication order(s).<br>Your personal information is always kept confidential.
					</p-->

					<div class="">
						<div class="three-quarters-width align-left">
							<label for="p_has_income">Do you currently have income?</label>
						</div>
						<div class="one-quarter-width align-right">
							<label for="p_has_income_yes" class='rb-container no_width'>Yes
								<input autocomplete="nope" type="radio" data-type="patient" id="p_has_income_yes" name="p_has_income" value="1" class="LoNotSensitive" <?php echo ((in_array('has_income', $radios_submitted) && $data['p_has_income'] != '') ? 'preload="' . (int)$data['p_has_income'] . '"' : ''); ?>>
								<span class="rb-checkmark"></span>
							</label>
							<label for="p_has_income_no" class='rb-container no_width'>No
								<input autocomplete="nope" type="radio" data-type="patient" id="p_has_income_no" name="p_has_income" value="0" class="LoNotSensitive">
								<span class="rb-checkmark"></span>
							</label>
						</div>
					</div>
					<div class="clear"></div>
					<br>

					<div id="patient_income_section" class="hidden">
						<p class="normal align-left">
							Please answer all of these questions to the best of your ability.
						</p>
						<br>

						<div class="patient_income_yes_only">
							<div class="half-width align-left"><label for="p_employment_status" class="line-height-48">Are you currently employed? <span class="red">*</span></label></div>
							<div class="half-width">
								<select data-type="patient" name="p_employment_status" preload="<?php echo $data['p_employment_status'];?>" class="full-width no-float-label LoNotSensitive" placeholder="">
										<option value=''>Select ...</option>
										<option value='F'>Full-Time</option>
										<option value='P'>Part-Time</option>
										<option value='R'>Retired</option>
										<option value='U'>Unemployed</option>
										<option value='S'>Self-Employed</option>
								</select>
							</div>
						</div>

						<div>
							<div class="half-width align-left"><label for="p_married" class="line-height-48">Are you married? <span class="red">*</span></label></div>
							<div class="half-width">
								<select data-type="patient" name="p_married" preload="<?php echo $data['p_married'];?>" class="full-width no-float-label LoNotSensitive" placeholder="">
									<option value=''>Select ...</option>
									<option value='S'>Single</option>
									<option value='M'>Married</option>
									<option value='D'>Separated</option>
									<option value='W'>Widowed</option>
								</select>
							</div>
						</div>

						<div>
							<div class="half-width align-left"><label for="p_household" class="line-height-48">How many people live in your household?<span class="red">*</span></label></div>
							<div class="half-width">
								<select data-type="patient" name="p_household" preload="<?php echo $data['p_household'];?>" class="full-width no-float-label LoNotSensitive" placeholder="">
									<option value=''>Select ...</option>
									<?php for ($i = 1; $i < 11; $i++) { ?>
										<option value='<?=$i?>'><?=$i?></option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="">
							<div class="three-quarters-width align-left">
								<label for="p_income_file_tax_return">Do you currently file tax returns? *</label>
							</div>
							<div class="one-quarter-width align-right">
								<label for="p_income_file_tax_return_yes" class='rb-container no_width'>Yes
									<input autocomplete="nope" type="radio" data-type="patient" id="p_income_file_tax_return_yes" name="p_income_file_tax_return" value="1" class="LoNotSensitive" <?php echo ((in_array('file_tax_return', $radios_submitted) && $data['p_income_file_tax_return'] != '') ? 'preload="' . (int)$data['p_income_file_tax_return'] . '"' : ''); ?>>
									<span class="rb-checkmark"></span>
								</label>
								<label for="p_income_file_tax_return_no" class='rb-container no_width'>No
									<input autocomplete="nope" type="radio" data-type="patient" id="p_income_file_tax_return_no" name="p_income_file_tax_return" value="0" class="LoNotSensitive">
									<span class="rb-checkmark"></span>
								</label>
							</div>
							<br>
						</div>
						<div class="clear"></div>

						<div class="">
							<div class="three-quarters-width align-left">
								<label for="p_uscitizen">Are you a US Citizen? *</label>
							</div>
							<div class="one-quarter-width align-right">
								<label for="p_uscitizen_yes" class='rb-container no_width'>Yes
									<input autocomplete="nope" type="radio" data-type="patient" id="p_uscitizen_yes" name="p_uscitizen" value="1" class="LoNotSensitive" <?php echo ((in_array('us_citizen', $radios_submitted) && $data['p_uscitizen'] != '') ? 'preload="' . (int)$data['p_uscitizen'] . '"' : ''); ?>>
									<span class="rb-checkmark"></span>
								</label>
								<label for="p_uscitizen_no" class='rb-container no_width'>No
									<input autocomplete="nope" type="radio" data-type="patient" id="p_uscitizen_no" name="p_uscitizen" value="0" class="LoNotSensitive">
									<span class="rb-checkmark"></span>
								</label>
							</div>
							<br>
						</div>
						<div class="clear"></div>

						<div class="patient_income_yes_only">
							<div class="">
								<div class="three-quarters-width align-left">
									<label for="p_medicaid">Have you applied for Medicaid? *</label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_medicaid_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicaid_yes" name="p_medicaid" value="1" class="radio-groups LoNotSensitive" <?php echo ((in_array('medicaid', $radios_submitted) && $data['p_medicaid'] != '') ? 'preload="' . (int)$data['p_medicaid'] . '"' : ''); ?>>
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_medicaid_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicaid_no" name="p_medicaid" value="0" class="radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div>

							<div class="radio-group-row-2 p_medicaid_2nd">
								<div class="three-quarters-width align-left">
									<label for="p_medicaid_denial" class="">If yes, did you receive a denial letter? *</label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_medicaid_denial_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicaid_denial_yes" name="p_medicaid_denial" value="1" <?php echo ((in_array('medicaid_denial', $radios_submitted) && $data['p_medicaid_denial'] != '') ? 'preload="' . (int)$data['p_medicaid_denial'] . '"' : ''); ?> class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_medicaid_denial_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicaid_denial_no" name="p_medicaid_denial" value="0" class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div>

							<div class="">
								<div class="three-quarters-width align-left">
									<label for="p_medicare">Are you on Medicare? *</label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_medicare_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicare_yes" name="p_medicare" value="1" class="radio-groups LoNotSensitive" <?php echo ((in_array('medicare', $radios_submitted) && $data['p_medicare'] != '') ? 'preload="' . (int)$data['p_medicare'] . '"' : ''); ?>>
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_medicare_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicare_no" name="p_medicare" value="0" class="radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div>

							<div class="radio-group-row-2 p_medicare_2nd">
								<div class="three-quarters-width align-left">
									<label for="p_medicare_part_d" class="">Do you have Medicare Part D? *</label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_medicare_part_d_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicare_part_d_yes" name="p_medicare_part_d" value="1" <?php echo ((in_array('medicare_part_d', $radios_submitted) && $data['p_medicare_part_d'] != '') ? 'preload="' . (int)$data['p_medicare_part_d'] . '"' : ''); ?> class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_medicare_part_d_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicare_part_d_no" name="p_medicare_part_d" value="0" class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div>

							<div class="p_medicaid_2nd">
								<div class="three-quarters-width align-left">
									<label for="p_lis">Have you applied for Low Income Subsidy (LIS)? *</label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_lis_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_lis_yes" name="p_lis" value="1" class="radio-groups LoNotSensitive" <?php echo ((in_array('lis', $radios_submitted) && $data['p_lis'] != '') ? 'preload="' . (int)$data['p_lis'] . '"' : ''); ?>>
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_lis_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_lis_no" name="p_lis" value="0" class="radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div>

							<div class="radio-group-row-2 p_medicaid_2nd p_lis_2nd">
								<div class="three-quarters-width align-left">
									<label for="p_lis_denial" class="">If yes, did you receive a denial letter? *</label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_lis_denial_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_lis_denial_yes" name="p_lis_denial" value="1" <?php echo ((in_array('lis_denial', $radios_submitted) && $data['p_lis_denial'] != '') ? 'preload="' . (int)$data['p_lis_denial'] . '"' : ''); ?> class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_lis_denial_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_lis_denial_no" name="p_lis_denial" value="0" class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div>

							<div class="">
								<div class="three-quarters-width align-left">
									<label for="p_disabled_status">Are you disabled as determined by Social Security? *</label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_disabled_status_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_disabled_status_yes" name="p_disabled_status" value="1" class="radio-groups LoNotSensitive" <?php echo ((in_array('disabled', $radios_submitted) && $data['p_disabled_status'] != '') ? 'preload="' . (int)$data['p_disabled_status'] . '"' : ''); ?>>
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_disabled_status_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_disabled_status_no" name="p_disabled_status" value="0" class="radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div>

							<br>
							<p class="normal align-left">
								Check the box for the type(s) of income you receive monthly.<br><br>
								Then put the correct number in the box.
							</p>

							<div id="first_income_source_row" class="align-left">
								<label for="p_has_salary" class="cb-container checkbox-label">Monthly Gross Salary/Wages Income
									<input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_salary" name="p_has_salary" value="1" <?php echo (($data['p_income_salary'] != '' && $data['p_income_salary'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
									<span class="cb-checkmark"></span>
								</label>
								<div class="clear"></div>
							</div>
							<div class="p_income_salary full-width relative hidden"><div class="inline inline-div-for-inputs">$</div><input autocomplete="nope" type="text" data-type="patient" name="p_income_salary" id="p_income_salary" value="<?=(($data['p_income_salary'] != '' && $data['p_income_salary'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_salary']), 2, '.', ',') : '')?>" placeholder="" class="dollar-amount input_zero no-float-label LoNotSensitive"></div>

							<div class="align-left">
								<label for="p_has_unemployment" class="cb-container checkbox-label">Monthly Unemployment Income
									<input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_unemployment" name="p_has_unemployment" value="1" <?php echo (($data['p_income_unemployment'] != '' && $data['p_income_unemployment'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
									<span class="cb-checkmark"></span>
								</label>
								<div class="clear"></div>
							</div>
							<div class="p_income_unemployment full-width relative hidden"><div class="inline inline-div-for-inputs">$</div><input autocomplete="nope" type="text" data-type="patient" name="p_income_unemployment" id="p_income_unemployment" value="<?=(($data['p_income_unemployment'] != '' && $data['p_income_unemployment'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_unemployment']), 2, '.', ',') : '')?>" placeholder="" class="dollar-amount input_zero no-float-label LoNotSensitive"></div>

							<div class="align-left">
								<label for="p_has_pension" class="cb-container checkbox-label">Monthly Pension Income
									<input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_pension" name="p_has_pension" value="1" <?php echo (($data['p_income_pension'] != '' && $data['p_income_pension'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
									<span class="cb-checkmark"></span>
								</label>
								<div class="clear"></div>
							</div>
							<div class="p_income_pension full-width relative hidden"><div class="inline inline-div-for-inputs">$</div><input autocomplete="nope" type="text" data-type="patient" name="p_income_pension" id="p_income_pension" value="<?=(($data['p_income_pension'] != '' && $data['p_income_pension'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_pension']), 2, '.', ',') : '')?>" placeholder="" class="dollar-amount input_zero no-float-label LoNotSensitive"></div>

							<div class="align-left">
								<label for="p_has_annuity" class="cb-container checkbox-label">Monthly Annuity/IRA Income
									<input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_annuity" name="p_has_annuity" value="1" <?php echo (($data['p_income_annuity'] != '' && $data['p_income_annuity'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
									<span class="cb-checkmark"></span>
								</label>
								<div class="clear"></div>
							</div>
							<div class="p_income_annuity full-width relative hidden"><div class="inline inline-div-for-inputs">$</div><input autocomplete="nope" type="text" data-type="patient" name="p_income_annuity" id="p_income_annuity" value="<?=(($data['p_income_annuity'] != '' && $data['p_income_annuity'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_annuity']), 2, '.', ',') : '')?>" placeholder="" class="dollar-amount input_zero no-float-label LoNotSensitive"></div>

							<div class="align-left">
								<label for="p_has_ss_retirement" class="cb-container checkbox-label">Monthly Social Security Retirement Income
									<input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_ss_retirement" name="p_has_ss_retirement" value="1" <?php echo (($data['p_income_ss_retirement'] != '' && $data['p_income_ss_retirement'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
									<span class="cb-checkmark"></span>
								</label>
								<div class="clear"></div>
							</div>
							<div class="p_income_ss_retirement full-width relative hidden"><div class="inline inline-div-for-inputs">$</div><input autocomplete="nope" type="text" data-type="patient" name="p_income_ss_retirement" id="p_income_ss_retirement" value="<?=(($data['p_income_ss_retirement'] != '' && $data['p_income_ss_retirement'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_ss_retirement']), 2, '.', ',') : '')?>" placeholder="" class="dollar-amount input_zero no-float-label LoNotSensitive"></div>

							<div class="align-left">
								<label for="p_has_ss_disability" class="cb-container checkbox-label">Monthly Social Security Disability Income
									<input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_ss_disability" name="p_has_ss_disability" value="1" <?php echo (($data['p_income_ss_disability'] != '' && $data['p_income_ss_disability'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
									<span class="cb-checkmark"></span>
								</label>
								<div class="clear"></div>
							</div>
							<div class="p_income_ss_disability full-width relative hidden"><div class="inline inline-div-for-inputs">$</div><input autocomplete="nope" type="text" data-type="patient" name="p_income_ss_disability" id="p_income_ss_disability" value="<?=(($data['p_income_ss_disability'] != '' && $data['p_income_ss_disability'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_ss_disability']), 2, '.', ',') : '')?>" placeholder="" class="dollar-amount input_zero no-float-label LoNotSensitive"></div>

							<div class="">
								<div class="half-width align-left">
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="p_income_annual_income" class="hspaced">Total Annual Income</label>
								</div>
								<div class="half-width relative align-right">
									<div class="inline inline-div-for-inputs">$</div><input autocomplete="nope" type="text" data-type="patient" name="p_income_annual_income" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_annual_income']));?>" class="dollar-amount no-float-label" readonly data-hint="Your total monthly income is required by the pharmaceutical company to process your medication orders.">
								</div>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!--div style="padding: 10px 0;">
						<div class="half-width" style="padding: 7px 0;">
							<input autocomplete="nope" type="checkbox" data-type="patient" id="p_income_zero" name="p_income_zero" value="1" <?php echo (($data['p_income_zero'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal">&nbsp;&nbsp;
							<label for="p_income_zero" class="big">I currently have no income</label>
						</div>

						<div class="clear"></div>
					</div-->

					<!--p class="normal align-left">
						Please enter all monthly income amounts from sources that apply to you. This information is necessary to complete your enrollment and is a requirement by the pharmaceutical company who will be supplying your medication.
					</p-->

					<div class="h-line">&nbsp;</div>

					<h2 class="dblue-text no-text-transformation">Health Care Provider Information</h2>

					<p class="normal align-left">
						<strong>Healthcare Providers:</strong> Only list the healthcare provider prescribing your medication.
					</p>

					<p class="normal align-left">
						If this information is not correct, your first medication delivery will be delayed.
					</p>
					<br>

					<div old-id="new_doctor_form" id="doctors_forms_list">
						<?php $valid_drs = 0; ?>
						<?php foreach ($data['doctors'] as $dr_key => $doctor) { ?>
							<?php if ((count($data['doctors']) > 1 && $doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') || count($data['doctors']) == 1) { ?>
								<?php $valid_drs++; ?>

								<div old-id="doctor_form" class="dr-form">
									<!--input autocomplete="nope" type="hidden" name="dr_id" id="dr_id" value="<?=($saved_doctors + 1)?>"-->

									<div>
										<div class="doctor-no-field p20-width align-left bold">Provider <?=($valid_drs)?>:</div>
										<div class="p40-width"><input autocomplete="nope" type="text" name="doctor_first_name[<?=$valid_drs?>]" value="<?=$doctor['doctor_first_name']?>" class="dr-required dr-data doctor-fields LoNotSensitive" placeholder="Healthcare Provider First Name *"></div>
										<div class="p40-width"><input autocomplete="nope" type="text" name="doctor_last_name[<?=$valid_drs?>]" value="<?=$doctor['doctor_last_name']?>" class="dr-required dr-data LoNotSensitive" placeholder="Healthcare Provider Last Name *"></div>
									</div>
									<div class="clear"></div>

									<div>
										<div class="p20-width align-left bold noMobile">&nbsp;</div>
										<div class="p40-width"><input autocomplete="nope" type="text" name="doctor_facility[<?=$valid_drs?>]" value="<?=$doctor['doctor_facility']?>" class="dr-data LoNotSensitive" placeholder="Facility Name"></div>
										<div class="p40-width"><input autocomplete="nope" type="text" name="doctor_address[<?=$valid_drs?>]" value="<?=$doctor['doctor_address']?>" class="dr-required dr-data dr-address LoNotSensitive" placeholder="Address *" rel="Some health care providers have multiple locations they work from, please provide the address for the location you visit your health care provider at."></div>
									</div>
									<div class="clear"></div>

									<div>
										<div class="p20-width align-left bold noMobile">&nbsp;</div>
										<div class="p40-width"><input autocomplete="nope" type="text" name="doctor_address2[<?=$valid_drs?>]" value="<?=$doctor['doctor_address2']?>" class="dr-data LoNotSensitive" placeholder="Suite Number"></div>
										<div class="p40-width"><input autocomplete="nope" type="text" name="doctor_city[<?=$valid_drs?>]" value="<?=$doctor['doctor_city']?>" class="dr-required dr-data LoNotSensitive" placeholder="City *"></div>
									</div>
									<div class="clear"></div>

									<div>
										<div class="p20-width align-left bold noMobile">&nbsp;</div>
										<div class="p40-width">
											<select name="doctor_state[<?=$valid_drs?>]" preload="<?=$doctor['doctor_state']?>" class="dr-required dr-data full-width LoNotSensitive" placeholder="State *">
												<option value="" selected="selected"></option>
												<option value="AL">Alabama</option>
												<option value="AK">Alaska</option>
												<option value="AZ">Arizona</option>
												<option value="AR">Arkansas</option>
												<option value="CA">California</option>
												<option value="CO">Colorado</option>
												<option value="CT">Connecticut</option>
												<option value="DE">Delaware</option>
												<option value="DC">District Of Columbia</option>
												<option value="FL">Florida</option>
												<option value="GA">Georgia</option>
												<option value="HI">Hawaii</option>
												<option value="ID">Idaho</option>
												<option value="IL">Illinois</option>
												<option value="IN">Indiana</option>
												<option value="IA">Iowa</option>
												<option value="KS">Kansas</option>
												<option value="KY">Kentucky</option>
												<option value="LA">Louisiana</option>
												<option value="ME">Maine</option>
												<option value="MD">Maryland</option>
												<option value="MA">Massachusetts</option>
												<option value="MI">Michigan</option>
												<option value="MN">Minnesota</option>
												<option value="MS">Mississippi</option>
												<option value="MO">Missouri</option>
												<option value="MT">Montana</option>
												<option value="NE">Nebraska</option>
												<option value="NV">Nevada</option>
												<option value="NH">New Hampshire</option>
												<option value="NJ">New Jersey</option>
												<option value="NM">New Mexico</option>
												<option value="NY">New York</option>
												<option value="NC">North Carolina</option>
												<option value="ND">North Dakota</option>
												<option value="OH">Ohio</option>
												<option value="OK">Oklahoma</option>
												<option value="OR">Oregon</option>
												<option value="PA">Pennsylvania</option>
												<option value="RI">Rhode Island</option>
												<option value="SC">South Carolina</option>
												<option value="SD">South Dakota</option>
												<option value="TN">Tennessee</option>
												<option value="TX">Texas</option>
												<option value="UT">Utah</option>
												<option value="VT">Vermont</option>
												<option value="VA">Virginia</option>
												<option value="WA">Washington</option>
												<option value="WV">West Virginia</option>
												<option value="WI">Wisconsin</option>
												<option value="WY">Wyoming</option>
											</select>
										</div>
										<div class="p40-width"><input autocomplete="nope" type="text" name="doctor_zip[<?=$valid_drs?>]" value="<?=$doctor['doctor_zip']?>" maxlength="5" class="dr-required dr-data dr-zip LoNotSensitive" placeholder="Zip Code *"></div>
									</div>
									<div class="clear"></div>

									<div>
										<div class="p20-width align-left bold noMobile">&nbsp;</div>
										<div class="p40-width"><input autocomplete="nope" type="text" name="doctor_phone[<?=$valid_drs?>]" value="<?=$doctor['doctor_phone']?>" class="dr-required dr-data dr-phone LoNotSensitive" placeholder="Phone Number *"></div>
										<div class="p40-width"><input autocomplete="nope" type="text" name="doctor_fax[<?=$valid_drs?>]" value="<?=$doctor['doctor_fax']?>" class="dr-data dr-fax LoNotSensitive" placeholder="Fax Number"></div>
									</div>
									<div class="clear"></div>
								</div>
							<?php } ?>
						<?php } ?>
					</div>

					<div><input autocomplete="nope" type="button" name="bAddANewDoctor" id="bAddANewDoctor" value="ADD ANOTHER DOCTOR" class="cancel small-button-orange button-auto-width"></div>

					<div class="form-group-spacer"></div>

					<div class="h-line">&nbsp;</div>

					<h2 class="dblue-text no-text-transformation">Medication Information</h2>

					<div class="medication_list">
						<p class="normal align-left">
							Please list all the medications you are requesting through Prescription Hope.
							<a href="#" class='disable-click inline-tooltip-icon' data-tooltip="Reminder: Prescription Hope is $<?=$_SESSION['rate']?> per month for each medication you are requesting"></a>
						</p>
						<br>

						<div class="no-margin" id="medication_form">

						<?php
						$valid_meds = 0;
						foreach ($data['medication'] as $med_key => $medication) { ?>
							<?php if ($medication['medication_name'] != '' && $medication['medication_strength'] != '' && $medication['medication_frequency'] != '' && (int) $medication['medication_doctor'] != '') {
								$valid_meds++;
								?>
								<div class="medication-row">
									<div>
										<div class="medication-no-field p20-width align-left bold">Medication <?=($valid_meds)?>:</div>

										<div class="medication-name-field p40-width">
											<input autocomplete="nope" type="text" name="medication_name[<?=$valid_meds?>]" value="<?=$medication['medication_name']?>" placeholder="Medication Name *" class="med-data LoNotSensitive">
										</div>

										<div class="medication-strength-field p40-width">
											<input autocomplete="nope" type="text" name="medication_strength[<?=$valid_meds?>]" value="<?=$medication['medication_strength']?>" placeholder="Medication Strength *" class="med-data LoNotSensitive">
										</div>
									</div>
									<div class="clear"></div>

									<div>
										<div class="medication-blank-field p20-width noMobile">&nbsp;</div>

										<div class="medication-frequency-field p40-width">
											<input autocomplete="nope" type="text" name="medication_frequency[<?=$valid_meds?>]" value="<?=$medication['medication_frequency']?>" placeholder="Medication Frequency (ex. daily) *" class="med-data LoNotSensitive">
										</div>

										<div class="medication-doctor-field p40-width">
											<select name="medication_doctor[<?=$valid_meds?>]" preload="<?=$medication['medication_doctor']?>" class="doctors_dropdown med-data full-width LoNotSensitive" placeholder="Prescribing Healthcare Provider *">
												<option value=""></option>
											</select>
										</div>
									</div>
									<div class="clear"></div>
								</div>
							<?php } ?>
						<?php } ?>

						<?php if ($valid_meds == 0) { ?>
							<!--input type="hidden" name="med_id" id="med_id" value="1"-->

							<div class="medication-row">
								<div>
									<div class="medication-no-field p20-width align-left bold">Medication 1:</div>

									<div class="medication-name-field p40-width">
										<input autocomplete="nope" type="text" name="medication_name[1]" value="" placeholder="Medication Name *" class="med-data LoNotSensitive">
									</div>

									<div class="medication-strength-field p40-width">
										<input autocomplete="nope" type="text" name="medication_strength[1]" value="" placeholder="Medication Strength *" class="med-data LoNotSensitive">
									</div>
								</div>
								<div class="clear"></div>

								<div>
									<div class="medication-blank-field p20-width noMobile">&nbsp;</div>

									<div class="medication-frequency-field p40-width">
										<input autocomplete="nope" type="text" name="medication_frequency[1]" value="" placeholder="Medication Frequency (ex. daily) *" class="med-data LoNotSensitive">
									</div>

									<div class="medication-doctor-field p40-width">
										<select name="medication_doctor[1]" class="doctors_dropdown med-data full-width LoNotSensitive" placeholder="Prescribing Healthcare Provider *">
											<option value=""></option>
											<?php /*foreach ($data['doctors'] as $dr_key => $doctor) { ?>
												<?php if ($doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') { ?>
													<option value="<?php echo ($dr_key);?>"><?php echo 'Doctor ' . ($dr_key) . ' (' . $doctor['doctor_first_name'] . ' ' . $doctor['doctor_last_name'] . ')';?></option>
												<?php } ?>
											<?php } */ ?>
										</select>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						<?php } ?>

						</div>

						<input autocomplete="nope" type="button" name="bAddANewMedication" id="bAddANewMedication" value="ADD ANOTHER MEDICATION" class="cancel small-button-orange button-auto-width">
					</div>

					<div class="form-group-spacer"></div>

					<div class="h-line">&nbsp;</div>

					<h2 class="dblue-text no-text-transformation">Terms And Conditions</h2>
 
					<br>

					
					<p class="moduleSubheader terms_scroll_box align-justify">
						<span class="policy_subtitle">Service:</span>
						Prescription Hope, Inc. is a fee-based medication advocacy service that assists patients in enrolling in applicable pharmaceutical companies’ patient assistance programs. You hereby authorize Prescription Hope, Inc. to act on your behalf and to sign applications for patient assistance programs by hereby granting to Prescription Hope, Inc. a limited power of attorney for the specific purposes of enrolling you in patient assistance programs and any related activities to process your enrollment. You understand this authorization can be revoked at any time by you by providing a signed letter of cancellation to Prescription Hope, Inc. as described in the Fees section. You hereby authorize your healthcare provider’s office to discuss/release medical information to Prescription Hope, Inc. relating to your applications for patient assistance programs that Prescription Hope, Inc. is processing on your behalf. You understand that Prescription Hope, Inc. does not ship, prescribe, purchase, sell, handle, or dispense prescription medication 
 of any kind. The pharmaceutical companies offer the medication through patient assistance programs at no cost. You hereby acknowledge that you are not paying for medication(s) through the Prescription Hope, Inc. service; rather you are paying for the administrative service of ordering, managing, tracking, and refilling medications received through the Prescription Hope, Inc. medication advocacy service. You also understand and acknowledge that it is each individual pharmaceutical manufacturer who makes the final decision as to whether you qualify for their patient assistance programs.
<br/><br/>
You understand Prescription Hope, Inc. does not guarantee your approval for patient assistance programs; it is up to each applicable drug manufacturer 
 to make the eligibility determination. You will be provided details in writing for each of your eligible medications. The medication is shipped directly from the pharmaceutical company and is delivered either to your home or healthcare provider’s office, depending upon the manufacturer delivery guidelines. You agree that you may be contacted via telephone, cellular phone, text message or email through all numbers and/or addresses provided by you and authorize receipt of pre-recorded and/or artificial voice messages and/or use of an automated dialing service by Prescription Hope, Inc. and/or its affiliates. By signing below, you further agree to release Prescription Hope, Inc., its agents, employees, successors and assigns from any and all liability including legal fees and costs arising from medication(s) taken by you which were procured through the Prescription Hope, Inc. medication advocacy service and/or your reliance upon the program in general. You further agree to indemnify and hold Prescription Hope, Inc., its agents, employees, successor and assigns harmless against any and all damages including legal fees and costs arising from third persons ingesting any medication procured for you through Prescription Hope, Inc. Medications covered are subject to change at any time. Prescription Hope, Inc. reserves the right to rescind, revoke, or amend its services at any time.

 
					<p>

					<div class="right-padding-35 bottom-padding-15 align-left">
						<label for="p_service_agreement" class="cb-container big">I have read and agree with the above statements *
							<input autocomplete="nope" type="checkbox" data-type="patient" id="p_service_agreement"  name="p_service_agreement" value="1" <?php echo (($data['p_service_agreement'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal LoNotSensitive">
							<span class="cb-checkmark"></span>
						</label>
					</div>

					<p class="moduleSubheader terms_scroll_box align-justify">
						<span class="policy_subtitle">Guarantee:</span> 
If you do not receive medication because you were determined to be ineligible for a patient assistance program and you have a letter of denial by the applicable pharmaceutical manufacturer, Prescription Hope, Inc. will refund the monthly administrative service fee for the medication determined to be ineligible. All Prescription Hope, Inc. will need from you is a copy of the denial letter sent to you from the applicable drug manufacturer explaining why you are ineligible.
<br/><br/>
		<span class="policy_subtitle">Privacy:</span> 
We value our patients and make extreme efforts to protect the privacy of our patients’ personal information. Patient information is processed for order fulfillment only and for no other purpose. Patient information, including all patient health information and personal information, will never be disclosed to any third party under any circumstances. All information given to Prescription Hope, Inc., its agents, employees, successors and assigns (collectively, “Prescription Hope, Inc.”) will be held in the strictest confidence.


						</p>

					<div class="right-padding-35 bottom-padding-15 align-left">
						<label for="p_guaranty_agreement" class="cb-container big">I have read and agree with the above statements *
							<input autocomplete="nope" type="checkbox" data-type="patient" id="p_guaranty_agreement"  name="p_guaranty_agreement" value="1" <?php echo (($data['p_guaranty_agreement'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal LoNotSensitive">
							<span class="cb-checkmark"></span>
						</label>
					</div>
<p class="moduleSubheader terms_scroll_box align-justify">
						<span class="policy_subtitle">Fees:</span> 
						Prescription Hope, Inc. charges a service fee of $50 per month for each medication. The monthly service fee covers 100% of the medication cost, as well as the services provided by Prescription Hope, Inc. There are no additional costs for the medication(s). If we find that we are unable to access at least one of your medication(s) during the initial enrollment process, there will be no charges to your account. If we can access your medication, the initial service fee will be debited immediately so we 
 can begin processing the paperwork required to order each eligible medication. The initial processing of your medication order(s) ranges from an average of 4 to 6 weeks and is contingent upon prompt responses to information that we request from you and your healthcare provider(s).  Prescription Hope, Inc. will process your monthly service fee on the same day each month corresponding to your enrollment date. This monthly transaction will appear on your statement as “PRESCRIPTION HOPE”. You also agree to pay any associated fees should your EFT (electronic fund transfer) be returned unpaid by your financial institution. Due to the servicebased nature of Prescription Hope, Inc., there are no refunds other than what 
 is explained in the Prescription Hope, Inc. Guarantee above.  
 <br/><br/>
 <span class="policy_subtitle">Eligibility:</span> 
	 You are experiencing a hardship with affording your medication and/or you currently do not have coverage that reimburses or pays for your prescription medications. You affirm that the information provided on this form is complete and accurate. If you determine the information was not correct at the time you provided it to Prescription Hope, Inc., or if the information was accurate but is no longer accurate, you will immediately notify Prescription Hope, Inc.
	
					
					</p>

					<div class="right-padding-35 bottom-padding-15 align-left">
						<label for="p_payment_agreement" class="cb-container big">I have read and agree with the above statements *
							<input autocomplete="nope" type="checkbox" data-type="patient" id="p_payment_agreement" name="p_payment_agreement" value="1" <?php echo (($data['p_payment_agreement'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal LoNotSensitive">
							<span class="cb-checkmark"></span>
						</label>
					</div> 

					
					<?php if (!(isset($agent_details['use_payment']) && (bool) $agent_details['use_payment'])) { ?>
						<br>

						<div class="h-line">&nbsp;</div>

						<h2 class="dblue-text no-text-transformation">Payment Information</h2>

						<div id="payment-information-medication-list" class="hidden width-500 centered bm-20"></div>
						<div id="payment-information-medication-list-text" class="hidden"></div>

						<p class="normal align-left">
							Rest assured, we will not charge your card until we verify we can access one or more of your medications.
						</p>

						<input autocomplete="nope" type="hidden" data-type="patient" id="p_payment_method_cc" name="p_payment_method" value="cc">

						<div id="payment_cc" class="">
							<div id="cc_number_box">
								<!--div class="half-width">
									<select data-type="patient" name="p_cc_type" preload="<?php echo $data['p_cc_type'];?>" class="full-width" placeholder="Credit Card Type *">
										<option value=""></option>
										<option value="Visa">Visa</option>
										<option value="Mastercard">Mastercard</option>
										<option value="American Express">American Express</option>
										<option value="Discover">Discover</option>
									</select>
								</div-->
								<input autocomplete="nope" type="hidden" name="p_cc_type" value="<?php echo $data['p_cc_type'];?>">
								<div class="full-width"><input autocomplete="nope" type="text" data-type="patient" name="p_cc_number" value="<?php echo $data['p_cc_number'];?>" placeholder="Credit Card Number *" class=""></div>
							</div>

							<div class="clear"></div>

							<div>
								<div class="half-width">
									<input autocomplete="nope" type="hidden" name="p_cc_exp_month" value="<?php echo $data['p_cc_exp_month'];?>">
									<input autocomplete="nope" type="hidden" name="p_cc_exp_year" value="<?php echo $data['p_cc_exp_year'];?>">
									<input autocomplete="nope" type="text" data-type="patient" name="p_cc_exp_date" value="<?=$data['p_cc_exp_month'] . '/' . substr($data['p_cc_exp_year'], 2, 2)?>" maxlength="5" class="" placeholder="MM/YY *">
									<!--select data-type="patient" name="p_cc_exp_month" preload="<?php echo $data['p_cc_exp_month'];?>" class="full-width" placeholder="Credit Card Expiration Month *">
										<option value=""></option>
										<option value="01">1 - January</option>
										<option value="02">2 - February</option>
										<option value="03">3 - March</option>
										<option value="04">4 - April</option>
										<option value="05">5 - May</option>
										<option value="06">6 - June</option>
										<option value="07">7 - July</option>
										<option value="08">8 - August</option>
										<option value="09">9 - September</option>
										<option value="10">10 - October</option>
										<option value="11">11 - November</option>
										<option value="12">12 - December</option>
									</select-->
								</div>
								<div class="half-width">
									<input autocomplete="nope" type="password" data-type="patient" name="p_cc_cvv" value="<?php echo $data['p_cc_cvv'];?>" maxlength="4" placeholder="CVC *" class="">
									<!--select data-type="patient" name="p_cc_exp_year" preload="<?php echo $data['p_cc_exp_year'];?>" class="full-width" placeholder="Credit Card Expiration Year *">
										<option value=""></option>
										<?php for ($i = 0; $i < 20; $i++) { ?>
											<option value="<?php echo ((int) date('Y') + $i); ?>"><?php echo ((int) date('Y') + $i); ?></option>
										<?php } ?>
									</select-->
								</div>
							</div>

							<div class="clear align-left">
								<div class="full-width align-left onlyMobile"><a href="#" data-tooltip="images/cvv4.jpg" class="skipLeave disable-click">What is this?</a><br><br><br></div>
							</div>

							<div class="align-left">
								<div class="half-width align-left"><img src="images/cc_visa.png" border="0" height="43" class="payment_images_" style='padding-left: 21px;' /> &nbsp;<img src="images/cc_mastercard.png" height="43" border="0" /> &nbsp;<img src="images/cc_amex.png" height="43" border="0" /> &nbsp;<img src="images/cc_discover.png" height="43" border="0" /></div>
								<div class="half-width align-left noMobile"><a href="#" data-tooltip="images/cvv4.jpg" class="skipLeave disable-click">What is this?</a></div>
							</div>
							<div class="clear"></div>
						</div>

						<?php /* ?>
						<br>

						<p class="moduleSubheader align-justify">
							<strong>
								I agree to the terms and conditions of Prescription Hope, including the Fees, Delivery, Cancellation, Service, Guarantee, Privacy, and Eligibility policies. I authorize Prescription Hope to charge my account $50.00 per month, per medication. I understand there are no refunds other than what is explained in the "Guarantee" policy - If I do not receive medication because I am determined to be ineligible for the patient assistance program by the applicable pharmaceutical company(s) and I have a letter of denial, I acknowledge Prescription Hope will refund my monthly administrative service fee(s) for the medication(s) determined to be ineligible only after Prescription Hope has exhausted all avenues of appeal. To receive a refund, I will provide Prescription Hope a copy of the denial letter(s) I receive from the applicable pharmaceutical company(s) explaining why I am ineligible. This agreement is in effect starting on this day of my application, until I rescind my authorization in writing.
							</strong>
						</p>

						<p>
							<input autocomplete="nope" type="checkbox" id="p_acknowledge_agreement"  name="p_acknowledge_agreement" value="1" <?php echo (($data['p_acknowledge_agreement'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
							<label for="p_acknowledge_agreement" class="no_width big">I have read and agree with the above statements *</label>
						</p>
						<?php */ ?>

					<?php } ?>

					<div class="form-group-spacer"></div>

					<p class="">
						<br>
						<input autocomplete="nope" type="submit" name="bSubmit" id="bSubmit" value="Submit" class="small-button-orange" style="width: 100%; font-size: 25px !important;">
					</p>

					<div id="ttt" style="color: #fff;"></div>

					</form>
				<?php } else { ?>
					<!-- CONFIRMATION -->
					<script type="text/javascript">

						jQuery().ready(function() {
							//GOOGLE Analytics
							//ga('send', 'event', 'conversion', 'submission', '20150806, enrollment, form, step5, successful-sub', {'nonInteraction': 1})
						});

					</script>

					<h2 class=" dblue-text no-text-transformation">Prescription Hope Enrollment<br><?php echo (($response_success) ? 'Confirmation' : 'Error'); ?></h2>

					<br>

					<div class=""><?php echo $response_msg;?></div>

					<form id="register_form" method="post" action="enroll.php">
						<?php if (!$response_success) { ?>
							<p class="">
								<input autocomplete="nope" type="submit" id="bPrevStep" name="bPrevStep" value="Back" class="cancel small-button-orange">
							</p>
						<?php } ?>
					</form>

					<script>

					jQuery(function() {
						jQuery.ajax({
							url: 'save_application.php?method=email';
						})

						jQuery('#app_email_send').click(function (event){
							jQuery('#app_email_send').text("SENDING EMAIL ...");
							event.preventDefault();
							jQuery.ajax({
								url: jQuery(this).attr('href'),
								success: function(response) {
									jQuery('#app_email_link').removeClass("small-button-orange");
									jQuery('#app_email_link').html("Email sent succesfully.");
								}
					 		})
							return false; //for good measure
						});
					});

					</script>
				<?php } ?>

				<p class="">
					<?php if ($form_submitted) { ?>
						<br>
						Protecting your personal information is our highest priority.<br>
						We have the same secured software used by banks in place<br>
						to ensure your personal information is always safe.
						<br /><br />
					<?php } ?>
				</p>
			</div>
		</div>
	</div>
</div>

<div id="spamPopup" class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent spamPopupContent">
			    <div class="leavePopupContentMain">
			    	<p class="subhead2-blue " style="font-size: 32px; font-weight: 700;">Important Message:</p>

			    	<p class="subhead2-blue ">Email is a critical form of communication about the progress of your enrollment and medication orders.</p>

			    	<p class="subhead2-blue ">The first few emails may land in your spam folder.</p>

			    	<p class="subhead2-blue ">Please check your spam folder if you have not received any emails from us.<br><br></p>

					<p><a href="#" id="btSpamClose" class="skipLeave big-button orangeButton inline text-center" style="text-transform: none;">I Will Check My Spam Folder</a></p>
		    	</div>
		    </div>
		</div>
	</div>
</div>

<div id="leavePopup" class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<div class="leavePopupContentMain">
		    		<p class="subhead2-blue  no-margin-bottom" style="color: #ff5555; text-transform: none;"><!--The Prescription Hope Pharmacy Program over the last decade has helped thousands of people nationwide obtain their prescription medication for only $<?=$_SESSION['rate']?> per month per medication.--> Are you sure you want to leave this page?<br><br>All your information will be lost.</p>
				</div>
		    	<br>

				<div class="" id="leaveButtons">
					<a href="" id="btLeaveNo" class="skipLeave popup-button-big">No</a>
					<br>
					<a href="" id="btLeaveYes" class="skipLeave popup-button-small">Yes</a>
				</div>
		    </div>
		</div>
	</div>
</div>


<div id="leavePopup2" class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<form id="fmLeavePage" method="post" action="">
		    	<div class="leavePopupContentMain">
			    	<p class="subhead2-blue " style="color: #ff5555; text-transform: none;">Please help us improve our services by taking a moment to tell us the reason that you do not want to complete the enrollment form</p>
			    	<br>

			    	<input autocomplete="nope" type="hidden" name="id" value="<? echo session_id(); ?>">

					<p class=" no-margin-bottom">
						<textarea name="leave_reason" class="no-margin"></textarea>
					</p>
			    </div>
		    	<br>

				<div class="" id="leaveButtons2">
					<input autocomplete="nope" type="submit" name="bSubmitLeavePage" id="btLeaveSubmit" value="Submit" class="popup-button-big">
					<br>
					<a href="" id="btLeaveCancel" class="skipLeave popup-button-small">Cancel</a>
				</div>
				</form>
		    </div>
		</div>
	</div>
</div>

<div id="reminderPopup" class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
			    <div class="leavePopupContentMain">
			    	<p class="subhead2-blue " style="color: #ff5555; text-transform: none;">YOU'RE ALMOST FINISHED!</p>

			    	<p class="subhead2-blue " style="color: #ff5555; text-transform: none;">Reminder: If we find that we are unable to approve you, there will be no charges. If you are approved, the ONLY charge is $<?=$_SESSION['rate']?>/month/medication. If the payment section is not complete, your enrollment form <span style="text-decoration: underline;">will not</span> be processed. If you have any questions, please contact a Patient Advocate at 1-877-296-HOPE (4673).</p>

			    	<br>

			    	<p class=" no-margin-bottom" style="font-size: 12px;">Press OK to Continue</p>
		    	</div>
		    	<br>
				<div class=" no-margin-bottom" id="btReminderOK">
					<a href="#" id="btReminderClose" class="skipLeave popup-button-big">OK</a>
				</div>
		    </div>
		</div>
	</div>
</div>

<div id="submitPopup"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
			    <div class="leavePopupContentMain">
			    	<p class="subhead2-blue " style="color: #ff5555; text-transform: none;">
				    	Upon submitting your enrollment form your first payment will be processed using the account you provided so we can begin processing your enrollment.<br><br>
				    	We will mail a paper request for proof of income documentation to you. This is required once per year by the pharmaceutical company that will be shipping your medication.<br><br>
				    	We will not be able to order your medication until you submit all requested documents. Please be on the lookout for our envelopes, we never send "Junk" mail so it is important to open and read all documents from Prescription Hope.
			    	</p>
		    	</div>
		    	<br>
				<div class="" id="submitButtons">
					<a href="" id="btConfirmSubmit" class="skipLeave popup-button-big">I understand and<br>agree - Submit</a>
					<br>
					<a href="" id="btCancelSubmit" class="skipLeave popup-button-small">Please cancel the<br>enrollment process</a>
				</div>
		    </div>
		</div>
	</div>
</div>

<div id="submitPopup2"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
			    <div class="leavePopupContentMain">
			    	<p class="subhead2-blue " style="color: #ff5555; text-transform: none;">Are you sure you want to cancel your enrollment process? This will delete your enrollment form.</p>
		    	</div>
		    	<br>
				<div class="" id="submitButtons">
					<a href="" id="btResetForm" class="skipLeave popup-button-big">Delete</a>
					<br>
					<a href="" id="btHideSubmitConfrmation" class="skipLeave popup-button-small">Return to<br>Enrollment Form</a>
				</div>
		    </div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var med_rate = <?=$_SESSION['rate']?>;

	var preventLeave = <?=((!$form_submitted) ? 'true' : 'false') ?>;
	var dataEntered = false;
	var lastClickedObject = null;
	var lastEventType = null;
	var submitConfirmed = true;

	var hear_about_extra_1_value = "<?=htmlspecialchars(stripslashes($data['p_hear_about_1']));?>";
	var hear_about_extra_2_value = "<?=htmlspecialchars(stripslashes($data['p_hear_about_2']));?>";
	var hear_about_extra_3_value = "<?=htmlspecialchars(stripslashes($data['p_hear_about_3']));?>";

	jQuery().ready(function() {
		//submit confirmation
		jQuery('#btConfirmSubmit').click(confirmEnrollmentSubmit);
		jQuery('#btCancelSubmit').click(cancelEnrollmentSubmit);
		jQuery('#btResetForm').click(resetEnrollmentForm);
		jQuery('#btHideSubmitConfrmation').click(hideEnrollmentSubmitConfirmation);

		//spam popup
		<?php if (isset($_SERVER["HTTP_REFERER"]) && basename($_SERVER["HTTP_REFERER"]) == 'register.php') { ?>
			jQuery('#spamPopup').removeClass("no-show");
			jQuery('#spamPopup .leavePopupContent').center();
		<?php } ?>
		//
		jQuery('#btSpamClose').click(function (e) {
			e.preventDefault();
			jQuery('#spamPopup').addClass('no-show');
		})

		jQuery("#fmLeavePage").validate({
			rules: {
				leave_reason:	{ required: true }
			},

			errorPlacement: jQueryValidation_PlaceErrorLabels
		});

		jQuery('table#seals a').each(function() {
			jQuery(this).addClass('skipLeave');
		});

		jQuery('a').click(preventPageLeave);
		jQuery('form#searchform').submit(preventPageLeave);

		jQuery('#btLeaveNo').click(cancelPageLeave);
		jQuery('#btLeaveYes').click(continuePageLeave);
		jQuery('#btLeaveCancel').click(cancelPageLeave);
		jQuery('#btLeaveSubmit').click(submitPageLeaveReason);

		jQuery(window).resize(function(){
			jQuery('.leavePopupContent:visible').center();
		});

 		jQuery('.disable-click').click(function(e) {
 			e.preventDefault();
 		});

 		jQuery('input,select,textarea').change(function (e) {
			//dataEntered = true
 		});

 		//
		jQuery('input,select').jvFloat();

		//detect CC type
		jQuery('input[name="p_cc_number"]').keyup(updateCardType);
		jQuery('input[name="p_cc_number"]').trigger('keyup');

		//show tooltips icons
		jQuery("input[data-hint]").each(showTooltipsIcons);

		//
 		jQuery('#p_hear_about').change(updateHearAboutExtras);
		if (jQuery('#p_hear_about').is("select") && (jQuery('#p_hear_about').val() != '' || jQuery('#p_hear_about').data('value') != '')) {
			jQuery('#p_hear_about').val(jQuery('#p_hear_about').data('value'));
			jQuery('#p_hear_about').trigger('change');
		}

		jQuery('#register_form input,#register_form select').each(function (index, elem) {
			//elemSelector = jQuery(elem).prop('nodeName') + '[name="' + jQuery(elem).attr('name') + '"]';

			if (jQuery(elem).is(':visible') && form_validator.check(elem) && jQuery(elem).val() != '' && jQuery(elem).val() != 0) {
				/*
				if (jQuery(elem).prop('nodeName') == 'SELECT' || (jQuery(elem).prop('nodeName') == 'INPUT' && jQuery(elem).attr('type') != 'checkbox' && jQuery(elem).attr('type') != 'radio')) {
					jQuery(elem).removeClass('correct');
					jQuery(elem).addClass('correct');
				}

				if (jQuery(elem).prop('nodeName') == 'INPUT') {
					switch (jQuery(element).attr('type')) {
						case 'checkbox':
							break;

						case 'radio':
							break;
					}
				}
				*/
				jQueryValidation_Unhighlight(elem);
			}

			refreshRadioGroupsValidationIcons();
		});

		<?php if ($invalid_cc) { ?>InvalidCreditCard();<?php } ?>

	});

	// Remove navigation prompt
	//window.onbeforeunload = function(e) {

</script>

<br><br>

<?php include('_footer.php'); ?>
