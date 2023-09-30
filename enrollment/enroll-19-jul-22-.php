<?php
require_once('includes/functions.php');
session_start();
//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: ../patients-dashboard/login.php');
}
//get data
$data = array(
	'command'		=> 'get_patient_data',
	'patient' 		=> $_SESSION[$session_key]['data']['id'],
	'access_code'	=> $_SESSION[$session_key]['access_code']
);
$rxi_data = api_command($data); 
//Get updated price 
$patient_price_calculate = array(
	'command'		=> 'get_patient_price_calculate',
	'patient' 		=> $_SESSION[$session_key]['data']['id'],
	'access_code'	=> $_SESSION[$session_key]['access_code']
);
$rxi_price_calculate = api_command($patient_price_calculate);
//Ended Get updated price 
$_SESSION[$session_key]['data'] = decode_patient_data($_SESSION[$session_key]['access_code'], $rxi_data->patient->iv, (array) $rxi_data->patient);
if ($_SESSION[$session_key]['data']['submitted_as_account'] == 1) {
	header('Location: success.php?redirect=1');
	exit();
}
function get_returned_patient ($code) {
	$dev_folder = (isset($_SESSION['dev'])) ? 'dev/' : '';
	if ($_SERVER['SERVER_NAME'] == 'prescriptionhope.staging-box.net') {
		//$cUrl = "http://prescriptionhope.staging-box.net/rxi/webservice/";
		$cUrl = "http://rxirebuild.staging-box.net/webservice/";
	}
	else{
		$cUrl = "http://172.31.47.116/webservice/" . $dev_folder ;		
	}
	
	//get data from the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => $cUrl . "get_returned_patient.php",
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
	if ($_SERVER['SERVER_NAME'] == 'prescriptionhope.staging-box.net') {
		//$cUrl = "http://prescriptionhope.staging-box.net/rxi/webservice/";
		$cUrl = "http://rxirebuild.staging-box.net/webservice/";
	}
	else{
		$cUrl = "http://172.31.47.116/webservice/" . $dev_folder ;
	}
	//get data from the server
	$cu = curl_init();
	curl_setopt_array($cu, array(
		CURLOPT_URL => $cUrl . "get_broker_agent_name.php",
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

	if ($_SERVER['SERVER_NAME'] == 'prescriptionhope.staging-box.net') {
		//$cUrl = "http://prescriptionhope.staging-box.net/rxi/webservice/";
		$cUrl = "http://rxirebuild.staging-box.net/webservice/";
	}
	else{
		$cUrl = "http://172.31.47.116/webservice/" . $dev_folder;
	}	
	//get data from the server
	$cu = curl_init();
	curl_setopt_array($cu, array(
		CURLOPT_URL => $cUrl . "get_broker_agent_price_point.php",
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

	if ($_SERVER['SERVER_NAME'] == 'prescriptionhope.staging-box.net') {
		//$cUrl = "http://prescriptionhope.staging-box.net/rxi/webservice/";
		$cUrl = "http://rxirebuild.staging-box.net/webservice/";
	}
	else{
		$cUrl = "http://172.31.47.116/webservice/" . $dev_folder;
	}	
	//get data from the server
	$cu = curl_init();
	curl_setopt_array($cu, array(
		CURLOPT_URL => $cUrl . "get_broker_agent_details.php",
		//CURLOPT_URL => "http://localhost/phope/webservice/get_broker_agent_name.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'agent=' . $agent_code,
		CURLOPT_RETURNTRANSFER => true
	));
	$response = curl_exec($cu);
	curl_close($cu);
	return ($response != '') ? (array) json_decode($response) : null;
}
function truncateMedication($string, $length, $dots = "...") {
    return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
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
		exit();
	} else {
		header("Location: https://prescriptionhope.com");
		exit();
	}
	die();
}
if (!isset($_GET['source']) && isset($_SESSION['my_source'])) {
	if (!isset($_SESSION['register_data']['p_hear_about']) || $_SESSION['register_data']['p_hear_about'] == '') {
		header("Location: https://manage.prescriptionhope.com/register.php?source=" . $_SESSION['my_source']);
		exit();
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
	// echo "Load Medication in session";
	// //echo "<pre>";
	// print_r($_SESSION[$session_key]['data']['medication']);
} else {
	$medication_data = array('medication_doctor', 'medication_name', 'medication_strength', 'medication_frequency');
	//$data['medication'] = array(array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''));
	$data['medication'] = array(array_fill_keys($medication_data, ''));

	// 	echo "Load Medication else condition";
	// //echo "<pre>";
	// print_r($medication_data);
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
	$ck_url_cd = isset($_COOKIE['url_code']) ? $_COOKIE['url_code'] : "" ;
	if($data['p_application_source'] == '')
	{
		$data['p_application_source'] = $ck_url_cd;
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
	// echo "<pre>";
	// print_r($tmp_data);
	// echo "--------------------";
	 //die('print input data');
	foreach ($tmp_data as $tmp_data_key => $tmp_data_value) {
		$data[$tmp_data_key] = (is_string($tmp_data_value)) ? trim($tmp_data_value) : $tmp_data_value;
	}	
	if (!isset($_POST['p_medicare_part_d'])) {
		$data['p_medicare_part_d'] = '0';
	}
	if (!isset($_POST['p_medicaid_denial'])) {
		$data['p_medicaid_denial'] = '0';
	}
	if (!isset($_POST['p_lis_denial'])) {
		$data['p_lis_denial'] = '0';
	}
	if ( !isset($_POST['p_income_salary']) || (isset($_POST['p_income_salary']) && $_POST['p_income_salary']=='') ) {
		$data['p_income_salary'] = 0.00;
	}
	if ( !isset($_POST['p_pocketmoney']) || (isset($_POST['p_pocketmoney']) && $_POST['p_pocketmoney']=='') ) {
		$data['p_pocketmoney'] = 0.00;
	}
	if ( !isset($_POST['p_income_annuity']) || (isset($_POST['p_income_annuity']) && $_POST['p_income_annuity']=='') ) {
		$data['p_income_annuity'] = 0.00;
	}
	if ( !isset($_POST['p_income_unemployment']) || (isset($_POST['p_income_unemployment']) && $_POST['p_income_unemployment']=='') ) {
		$data['p_income_unemployment'] = 0.00;
	}
	if ( !isset($_POST['p_income_ss_retirement']) || (isset($_POST['p_income_ss_retirement']) && $_POST['p_income_ss_retirement']=='') ) {
		$data['p_income_ss_retirement'] = 0.00;
	}
	if ( !isset($_POST['p_income_ss_disability']) || (isset($_POST['p_income_ss_disability']) && $_POST['p_income_ss_disability']=='') ) {
		$data['p_income_ss_disability'] = 0.00;
	}
	if ( !isset($_POST['p_income_pension']) || (isset($_POST['p_income_pension']) && $_POST['p_income_pension']=='') ) {
		$data['p_income_pension'] = 0.00;
	}
	if ( !isset($_POST['p_income_annuity']) || (isset($_POST['p_income_annuity']) && $_POST['p_income_annuity']=='') ) {
		$data['p_income_annuity'] = 0.00;
	}
	if ( !isset($_POST['p_income_annual_income']) || (isset($_POST['p_income_annual_income']) && $_POST['p_income_annual_income']=='') ) {
		$data['p_income_annual_income'] = 0.00;
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
		// echo "<pre>";
		// print_r($tmp['medication_doctor']);
		// echo "Medication Arr"; 
		// echo "---------------------------------------";
		//$med_doctor = ksort($tmp['medication_doctor']); 
		unset($data['medication']); // Reset medication data 
		foreach ($tmp['medication_doctor'] as $key => $values) {
			$data['medication'][$key]['medication_doctor'] 	= trim($tmp['medication_doctor'][$key]);
			$data['medication'][$key]['medication_name']	= trim($tmp['medication_name'][$key]);
			$data['medication'][$key]['medication_strength'] = trim($tmp['medication_strength'][$key]);
			$data['medication'][$key]['medication_frequency'] = trim($tmp['medication_frequency'][$key]);

			if ($data['medication'][$key]['medication_doctor'] != '' && $data['medication'][$key]['medication_name'] != '' && $data['medication'][$key]['medication_strength'] != '' && $data['medication'][$key]['medication_frequency'] != '') {
				$medsCount++;
			}
		}
		// echo "<pre>";
		// print_r($data['medication']);
				// echo "insode foreach loop medication";
				// echo "<pre>";
				// echo $key .' - '. $values; 

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

	// echo "<pre>";
	// print_r($data);	
	// // echo "valid form";
	// var_dump($valid_form);
	// echo "<br>";	die;
	
	//doctors
	if (count($data['doctors']) == 0) {
		$valid_form = false;
		//echo "in if condition";
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
	// echo $medsCount; die('Test');
	// die('Valid form');
	//meds
	if ($medsCount == 0) {
		$valid_form = false;
	} else {
		foreach ($data['medication'] as $med) {
		// echo "<pre>";
		// print_r($med['medication_doctor']);// die;
			if (($med['medication_doctor'] != '' || $med['medication_name'] != '' || $med['medication_strength'] != '' || $med['medication_frequency'] != '')) {				
				$valid_form = true;
				//var_dump($valid_form);
				//echo "---".'<br>';
			} else if($med['medication_doctor'] == '' || $med['medication_name'] == '' || $med['medication_strength'] == '' || $med['medication_frequency'] == ''){
				//missing data
				$valid_form = false;
			} 
		}
		//die('Meds data in foreach');
	}
	// echo "<pre>";
	// print_r($valid_form); die;	
	// // echo "valid form";
	// // var_dump($valid_form);
	// echo "<br>";	die('checking form');
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
				'PatientId'			=> $_SESSION[$session_key]['data']['id'],
				'PatientCCName' 	=> $data['p_first_name'] . ' ' . $data['p_last_name'],
				'PatientCCType' 	=> $data['p_cc_type'],
				'PatientCCNumber' 	=> $data['p_cc_number'],
				'PatientCCExpMonth'	=> $data['p_cc_exp_month'],
				'PatientCCExpYear' 	=> $data['p_cc_exp_year'],
				'PatientCCCVV'		=> $data['p_cc_cvv']
			);

			if ($_SERVER['SERVER_NAME'] == 'prescriptionhope.staging-box.net') {
				//$cUrl = "http://prescriptionhope.staging-box.net/rxi/webservice/";
				$cUrl = "http://rxirebuild.staging-box.net/webservice/";
			}
			else{
				$cUrl = "http://172.31.47.116/webservice/";
			}
	
			$cu = curl_init();

			curl_setopt_array($cu, array(
				CURLOPT_URL => $cUrl . "ccvalidation2.php",
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($cc_data),
				CURLOPT_RETURNTRANSFER => true
			));

			$response = curl_exec($cu);
			/*$fp = fopen("/var/www/vhosts/staging-box.net/prescriptionhope.staging-box.net/html/enrollment/card.txt","a");
                    chmod("/var/www/vhosts/staging-box.net/prescriptionhope.staging-box.net/html/enrollment/card.txt",0777);
                    fwrite($fp, print_r($response,true));
                    fwrite($fp,"\n");
			curl_close($cu);
die();*/
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
	//echo "<pre>";
	//var_dump($valid_form); //die('asdad');
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
				//echo "<pre>";print_r($patient_data);
			} else {
				$patient_data[((isset($data_request[$key][2])) ? $data_request[$key][2] : $key)] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encoding_key, $value, MCRYPT_MODE_CFB, $encoding_iv));
			}
		}
		// echo "<pre>";print_r($patient_data);die('Patient Data');
		$api_data = array(
			'command'		=> 'submit_enrollment',
			'patient' 		=> $_SESSION[$session_key]['data']['id'],
			'access_code'	=> $_SESSION[$session_key]['access_code'],
			'data'			=> $patient_data
		);

		$response = api_command($api_data);
		// echo "<pre>";
		// print_r($response); die('Response');
		//
		//log the success apps
		//
		$file = '../apps/' . date('Y-m-d-') . preg_replace('/[^a-z0-9]+/', '-', strtolower($data['p_email']));
		$data_tmp = $data;
		foreach (array('p_payment_method', 'p_cc_type', 'p_cc_number', 'p_cc_exp_month', 'p_cc_exp_year', 'p_cc_cvv', 'p_ach_holder_name', 'p_ach_routing', 'p_ach_account', 'register_step', 'bNextStep', 'session_id') as $skip_key) {
			unset($data_tmp[$skip_key]);
		}
		$data_tmp['response'] = $response;
		//file_put_contents($file, json_encode($data_tmp));
		//

		$response_success = false;

		if (isset($response->success)) {
			switch ($response->success) {
				case 1:
					$response_success = true;
					$response_msg = '<p class="moduleSubheader success">Enrollment Form Submitted Successfully.</p>';

					if (isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') {
						//agent
						$broker_name = trim(substr(urldecode($_SESSION['register_data']['p_application_source']), 9));
				        $broker_name = ($broker_name == 'Access Health Insurance, Inc') ? 'JibeHealth' : $broker_name;
						$broker_rate = (isset($_SESSION['rate']) && $_SESSION['rate'] > 0) ? $_SESSION['rate'] : 50;
						//$response_msg .= '<p class="moduleSubheader">Thank you for submitting an Enrollment Form to Prescription Hope.  We are proud to have partnered with ' . $broker_name . ' to obtain your medications for only ' . number_format($broker_rate, 2). ' per month per medication.<br><br>If you have any questions about your account with Prescription Hope, or any questions in general about the program, please visit our website at <a href="https://www.prescriptionhope.com">www.prescriptionhope.com</a> or call us at 1-877-296-4673.<br><br>A Patient Advocate will review your form and begin your Enrollment process.  You can expect a phone call and Welcome Packet from Prescription Hope soon.<br><br><strong>Please Note:</strong> If you opted for a Checking Account payment, please submit a voided check to us through mail or through fax.</p>';
						$response_msg .= '<p class="moduleSubheader" style="padding: 0 16em;">Thank you for submitting an Enrollment Form to Prescription Hope.  We are proud to have partnered with ' . $broker_name . ' to obtain your medications for only ' . number_format($broker_rate, 2). ' per month per medication.<br><br>If you have any questions about your account with Prescription Hope, or any questions in general about the program, please visit our website at <a href="https://www.prescriptionhope.com">www.prescriptionhope.com</a> or call us at 1-877-296-4673.<br><br>A Patient Advocate will review your form and begin your Enrollment process.  You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>';
					} else {
						//direct
						$response_msg .= '<p class="moduleSubheader">Thank you for submitting an Enrollment Form to Prescription Hope. A Patient Advocate will review your form and begin your Enrollment process.  You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>';
					}

					$response_msg .= '<br><p class="moduleSubheader">To print a copy of your Enrollment Form for your records.<br><br><span class="small-button-orange pdf_dwld"><a href="save_application.php" class="skipLeave" target="_blank">Click here</a></span></p>';
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
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Prescription Hope, Inc.<br>P: 877.296.4673<br><a href='https://www.prescriptionhope.com'>www.prescriptionhope.com</a></p>";

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
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Prescription Hope, Inc.<br>P: 877.296.4673<br>F: 877.298.1012<br><a href='https://www.prescriptionhope.com'>www.prescriptionhope.com</a></p>";

							$mail->AddEmbeddedImage('images/logo_41.png', 'ph_logo');
							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'><img src='cid:ph_logo' width='150' alt='Prescription Hope Logo'></p>";

							$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>CONFIDENTIALITY NOTICE: This is an e-mail transmission and the information is privileged and/or confidential. It is intended only for the use of the individual or entity to which it is addressed. If you have received this communication in error, please notify the sender at the reply e-mail address and delete it from your system without copying or forwarding it. If you are not the intended recipient, you are hereby notified that any retention, distribution, or dissemination of this information is strictly prohibited. Thank you.</p>";
						}

						$mail->AddStringAttachment(pdf_application($data, 'S'), 'Prescription_Hope_Application.pdf');

						//	stopped when started SalesForce
						//$rs = $mail->Send();
						//echo (!$rs) ? 'email error:' . $mail->ErrorInfo : 'email sent';
					}
					unset($_SESSION['register_data']);
					// Now redirect to Thank you page
					header('Location: success.php');
					//unset($_SESSION[$session_key]);
					//header('Location: thank_you.php');

					//clear the session data
					//unset($_SESSION['register_data']);
					break;

				case 2:
					$response_msg = '<p class="moduleSubheader">Operation canceled - there is already a patient with the same SSN in our system.</p>';
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
		
		// to render duplicate SSN error on form itself
		if($response->success==2){ $form_submitted = false; }
	} else {
		;//echo '<!-- something was not valid -->';
	}
}

?>

<?php include('ph_header.php'); ?>
<style type="text/css">
/*html body h1, html body h2, html body h3,html body h4, html body h5, html body h6
,html body p, html body label, html body strong, html body a, html body input, html body select, html body textarea
, html body span, html body div {font-family:Arial !important;}*/
#footer-wrapper{display:none;}
html body #main_content {padding: 0px;}
.ui-autocomplete {height: 200px;overflow: hidden;overflow-y: scroll;}
.loading-fname,.loading-lname {    
    background-color: #ffffff;
    background-image: url("../enrollment/images/loader.gif");
    background-size: 25px 25px;
    background-position:right center;
    background-repeat: no-repeat;
}
</style>

<?php /*?>
<div class="container bg-white1 w100">
	<div class="col-sm-12 m-100">
		<h2 class=" dblue-text no-text-transformation h30">Watch The Prescription Hope Process</h2>
		<div class="video-panel"><div class="intrinsic-container intrinsic-container-16x9"><iframe id="vimeo_video" src="https://player.vimeo.com/video/235383572?api=1&player_id=vimeo_video" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div></div>
	</div>
</div>
<?php */?>
<div class="container-fluid">
	<div id="enroll-now" class="row row-1 one_column white-bg text-left" style='z-index:1000;display: none;'>
		<div class="next-tool" style="display:none;"><a class="next-opt" href="javascript:void(0);">Next &raquo;</a><a class="submit-opt" href="javascript:void(0);" style="display: none;">Submit Application &raquo;</a></div>
		<div class="content createAccountBox align-center p-0">
			<div id='applicationForm'>
				<?php if (!$form_submitted) { ?>
					<!-- ENROLLMENT FORM -->				
					<script type="text/javascript">	
						var allowedIncomeAmt = [];
						jQuery(document).ready(function() {
						// Set checkApplicationValid 0 by default
  						localStorage.setItem("checkApplicationValid", 0);							
							var dataPosted = "<?php echo (isset($response->success) && $response->success==2) ? true : false; ?>";
							jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
							jQuery.validator.addMethod("custom_date",function(t,e){return t=t.replace(/\s+/g,""),td=t.split("/"),td=td[2]+"-"+td[0]+"-"+td[1],this.optional(e)||t.length>8&&t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/)&&td<=new Date().toISOString().substring(0,10)},"Please specify a valid date (mm/dd/yyyy)"),
							jQuery.validator.addMethod("SSN",function(t,e){return t=t.replace(/\s+/g,""),this.optional(e)||t.length>8&&t.match(/^\d{3}-?\d{2}-?\d{4}$/)},"Please specify a valid SSN number"),
							jQuery.validator.addMethod('SocialSecurity',
							    function (value) { 
							        return is_socialSecurity_Number(value) || value == "";
							    }, 'Please enter a valid SSN'
							),
							jQuery.validator.addMethod("lettersonly", function(value, element) { return this.optional(element) || /^[a-z'. ]+$/i.test(value); }, "Please insert only letters.");
							jQuery.validator.addMethod("valid_exp_date", function(value, element) { return ("20" + value.substr(3, 2) + value.substr(0, 2)) > <?=date('Ym')?> && parseInt(value.substr(0, 2)) > 0 && parseInt(value.substr(0, 2)) <= 12; }, "Please specify a valid date (MM/YY).");

							function is_socialSecurity_Number(str)
							{
							 regexp = /^(?!000|666)[0-8][0-9]{2}-(?!00)[0-9]{2}-(?!0000)[0-9]{4}$/;
							  
							        if (regexp.test(str))
							          {
							            return true;
							          }
							        else
							          {
							            return false;
							          }
							}
							function validSSN(value) {
							    var regex = /^([0-6]\d{2}|7[0-6]\d|77[0-2])([ \-]?)(\d{2})\2(\d{4})$/;
							    if (!regex.test(value)) {
							        return false;
							    }
							    var temp = value;
							    if (value.indexOf("-") != -1) {
							        temp = (value.split("-")).join("");
							    }
							    if (value.indexOf(" ") != -1) {
							        temp = (value.split(" ")).join("");
							    }
							    if (temp.substring(0, 3) == "000") {
							        return false;
							    }
							    if (temp.substring(3, 5) == "00") {
							        return false;
							    }
							    if (temp.substring(5, 9) == "0000") {
							        return false;
							    }
							    return true;
							}//end validSSN function
							
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
							var form_validator = jQuery("#register_form").validate({
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
									p_ssn: 						  { required: true, SSN: true,SocialSecurity: true, minlength: 9, maxlength: 11},
									//p_ssn_masked: 				  { required: true, minlength: 9, maxlength: 11 }, //SSN: true,
									p_has_income: 				  { required: true },
									p_household: 				  { required: true, digits: true },
									p_married: 					  { required: true },
									p_employment_status: 		  { required: jQuery("#p_has_income_yes") },
									p_uscitizen: 				  { required: jQuery("#p_has_income_yes") },
									p_disabled_status: 			  { required: jQuery("#p_has_income_yes") },
									p_medicare:					  { required: jQuery("#p_has_income_yes") },
									p_medicare_part_d: 			  { required: jQuery("#p_medicare_yes") },
									//p_medicare_part_d: 			  { required: jQuery("#p_medicare_yes") },
									p_medicaid: 				  { required: jQuery("#p_has_income_yes") },
									p_medicaid_denial:			  { required: jQuery("#p_medicaid_yes") },
									p_lis: 						  { required: jQuery("#p_has_income_yes") },
									p_lis_denial: 				  { required: jQuery("#p_lis_yes") },
									p_hear_about: 				  { required: true, ascii: true },
									p_income_salary:			  { required: false, number: true },
									p_pocketmoney:			  		{ required: true, number: true },
									p_income_unemployment:		  { required: false, number: true },
									p_income_pension:			  { required: false, number: true },
									p_income_annuity:			  { required: false, number: true },
									p_income_ss_retirement:		  { required: false, number: true },
									p_income_ss_disability:		  { required: false, number: true },
									p_income_file_tax_return: { required: { depends: function(element) {
										if (jQuery('input[name="p_has_income"]:checked').val() == 1) {
					                        return true;
					                    } else {
					                        return false;
					                    }
									} } },
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
									p_cc_number:				  { required: jQuery("#p_payment_method_cc"), number: true, creditcardtypes: function(element) {return {visa: (jQuery('input[name=p_cc_type]').val() == "Visa"), mastercard: (jQuery('input[name=p_cc_type]').val() == "Mastercard"), amex: (jQuery('input[name=p_cc_type]').val() == "American Express"), discover: (jQuery('input[name=p_cc_type]').val() == "Discover")};}},
									p_cc_exp_date:				  { required: jQuery("#p_payment_method_cc"), valid_exp_date: true},
									p_cc_cvv:					  { required: jQuery("#p_payment_method_cc"), digits: true, minlength: 3, maxlength: 4 },

									p_ach_holder_name:			  { required: jQuery("#p_payment_method_ach"), ascii: true },
									p_ach_routing:				  { required: jQuery("#p_payment_method_ach"), digits: true, maxlength: 9 },
									p_ach_account:				  { required: jQuery("#p_payment_method_ach"), digits: true },
									'doctor_first_name[1]':		  {required: true},
									'doctor_last_name[1]':		  {required: true},
									'doctor_city[1]':		  {required: true},
									'doctor_address[1]':		  {required: true},
									'doctor_state[1]':		  {required: true},
									'doctor_zip[1]':		  {required: true},
									'doctor_phone[1]':		  {required: true, phoneUS: true},
									'medication_name[1]':		  {required: true},
									'medication_strength[1]':		  {required: true},
									'medication_frequency[1]':		  {required: true},
									'medication_doctor[1]':		  {required: true}

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
									},
									p_parent_phone : "Please enter valid phone number", 
									p_dob : "Please enter a valid date in the format mm/dd/yyyy"									
					 			},

								highlight: jQueryValidation_Highlight,

								unhighlight: jQueryValidation_Unhighlight,

								errorPlacement: jQueryValidation_ShowErrors,
								//errorPlacement: function() {},

								invalidHandler: refreshRadioGroupsValidationIcons,

								onkeyup: false
							});
							jQuery('#register_form input,#register_form select').each(function (index, elem){
								if (jQuery(elem).is(':visible') && form_validator.check(elem) && jQuery(elem).val() != '' && jQuery(elem).val() != 0) {
									jQueryValidation_Unhighlight(elem);
								}
					
								refreshRadioGroupsValidationIcons();
							});

							//Commented by vinod					
							jQuery("input[type='radio']").keypress(function(e){
							    if(e.keyCode === 13) {
						    	    jQuery(this).attr("checked", 'checked');
							        return false;
							    }
							});
							//Commented by vinod					
							//Added by vinod
							// jQuery('input[type="radio"]').change(function () {
						 //    	    jQuery(this).attr("checked", 'checked');
							// });
							//Added by vinod

							jQuery("input[name=p_is_minor]").change(showSelectedPatientProfile);

							jQuery("input[type='password']").change(syncPatientData);

							jQuery('input[name=p_has_income]').change(showIncomeSection);

							jQuery("input[name='p_medicare'], input[name=p_medicaid], input[name=p_lis], input[name=p_medicare_part_d], input[name=p_coveragegapyes]").change(function(e) {show2ndLevelInsuranceQuestions(e);});


							// jQuery("input[name='p_medicare_part_d_yes']").change(function(e) {show2ndLevelInsuranceQuestions(e);});

							// jQuery("input[name='p_medicare_part_d_yes']").change(function(e) {show2ndLevelInsuranceQuestions(e);});


							jQuery('input[type="radio"]').change(refreshRadioGroupsValidationIcons);

							jQuery('input,select').focus(function () {
								if (jQuery(this).hasClass('field-error-only')) {
									refreshRadioGroupsValidationIcons();
								}

								//make sure the form scrolls to the correct spot even for the iOS devices
								//document.body.scrollTop = jQuery(this).offset().top;
							});

							jQuery('input#p_income_zero').change(function() {
								if (jQuery(this).is(':checked')) {
									//jQuery('input.input_zero').val('0.00');
									jQuery('input.input_zero').val('');
								}
							});

							jQuery('input.input_zero').on('change, blur', function() {
								if(jQuery(this).val() == 'NaN'){
									jQuery(this).val('');
								}
								updateZeroIncome();								

								var cur_val = parseFloat(jQuery(this).val());
								var input_val = (cur_val != '' && cur_val != 'NaN') ? parseFloat(jQuery(this).val().replace(/[^0-9.]/g, '')).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2, useGrouping: true}) : '';
							    jQuery(this).val(input_val); //0.00

								updateTotalAnnualIncome();
								
							    //eliminate any wrong formatted number error
							    if (jQuery('label[for="' + jQuery(this).attr('name') + '"]').length > 0) {
								    if (jQuery('label[for="' + jQuery(this).attr('name') + '"]').html() == 'Please enter a valid number.') {
								    	jQuery('label[for="' + jQuery(this).attr('name') + '"]').remove();
								    }
							    }

								//check values
								if (jQuery(this).val() != '' && jQuery(this).val() != 'NaN') {
									if ( (jQuery(this).val().replace(',', '') < 100 || jQuery(this).val().replace(',', '') > 9999.99) && ( jQuery.inArray(jQuery(this).attr('id'), allowedIncomeAmt) == -1 ) ) {
										if (!jQuery(this).hasClass("error")) {
											jQuery(this).removeClass("correct");
											jQuery(this).addClass("error");
										}
										if (jQuery('label.' + jQuery(this).attr('id')).length == 0 ) { 
											errorLabel = jQuery('<label>').addClass(jQuery(this).attr('id')).addClass('error').addClass('invalid-field').addClass(jQuery(this).attr('name')).html('Are you sure this is your monthly income from this source?<span data-id="'+jQuery(this).attr('id')+'" style="float:right;"><a class="income_true inc">Yes</a><a class="income_false inc">No</a></span>');
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
							
							jQuery(document).on('click', '.income_true', function(){ 
								var inputId = jQuery(this).parent('span').data('id');
								allowedIncomeAmt.push(inputId);
								jQuery('#'+inputId).removeClass("error");
								jQuery('label.'+inputId).remove();
								if (jQuery('#'+inputId).val() != '') {
									jQuery('#'+inputId).addClass("correct");
								}
							});
							
							jQuery(document).on('click', '.income_false', function(){ 
								var inputId = jQuery(this).parent('span').data('id');
								jQuery('#'+inputId).val('').focus();
							});
							//
							jQuery('input.input_zero').focus(function() {
								var val = parseFloat((jQuery(this).val()).replace(',', '')) - parseInt(0);
								if (jQuery(this).val()=='' || val == 0 || val=='NaN') {
									jQuery(this).val('');
								} else {
								    jQuery(this).val(val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2, useGrouping: false})); //0.00
									jQuery(this).prop('selectionStart', 0).prop('selectionEnd', 0);
								}
							});
							//
							jQuery('input.input_zero').blur(function() {
								if(jQuery(this).val() == 'NaN'){
									jQuery(this).val('');
								}
								val = jQuery(this).val() - 0; 
								if (val == 0 || val=='NaN') {
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
							jQuery("input[name='p_phone']").mask("000-000-0000", {clearIfNotMatch: true});
							jQuery("input[name='p_fax']").mask("000-000-0000", {clearIfNotMatch: true});
							jQuery("input[name='p_alternate_phone']").mask("000-000-0000", {clearIfNotMatch: true});
							jQuery("input[name='p_parent_phone']").mask("000-000-0000", {clearIfNotMatch: true});
							jQuery("input[name='p_dob']").mask("00/00/0000", {clearIfNotMatch: true});
							jQuery("input[name='p_ssn']").mask("000-00-0000", {clearIfNotMatch: true});
							jQuery("input[name='p_zip']").mask("00000", {clearIfNotMatch: true});
							//jQuery("input[name='p_ssn_masked']").mask("***-**-9999");
							//jQuery("input[name='p_ssn']").inputCloak({type: 'ssn'});
							jQuery("input.dr-zip").mask("00000", {clearIfNotMatch: true});
							jQuery("input.dr-phone").mask("000-000-0000", {clearIfNotMatch: true});
							jQuery("input.dr-fax").mask("000-000-0000", {clearIfNotMatch: true});
							jQuery("input[name='p_cc_exp_date']").mask("00/00", {clearIfNotMatch: true});
							//jQuery("input[name='p_cc_number']").mask("0000000000000000", {clearIfNotMatch: true});
							

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

							$('#bAddANewDoctor').on("click",function(e){
							  AddNewDoctorForm(e);
							})

							//jQuery('#bAddANewDoctor').click(AddNewDoctorForm); //AddANewDoctor
							jQuery('#bAddANewDoctorHealthcare').click(AddNewDoctorForm); //AddANewDoctor
							//jQuery('.dr-data').change(UpdateDoctor);
							jQuery(document).on('change', '.dr-data', UpdateDoctor);
							jQuery(document).on('blur', '.dr-data', UpdateDoctor);

							jQuery('#bAddANewMedication').click(AddANewMedication);
							jQuery('#bAddANewMedicationNew').click(AddANewMedication);
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

									jQuery("body").on("click", "#enroll-progress [id^='patient-enroll-progress_'], #final-submit, #bSubmit", function(){
								var old_tab = 1; 
								var f_count = 0;
								$("#register_form [class*='patient-enroll-progress-']").each(function() {
									var cls_no = $(this).data("step-enroll");
									
									if(old_tab != cls_no) {
										if (f_count == 0) {
											$("#patient-enroll-progress_"+old_tab).removeClass("alert-danger");
											//$("#patient-enroll-progress_"+old_tab).addClass("completed");
										}
										f_count = 0;
										old_tab = cls_no; 
									}

									// if(cls_no == tab_no)
									// {
									// 	return false; // breaks
									// }
									var  e_err = 0;
									if($(this).attr("type") == "text")
									{
										if($(this).val().trim() == "")
										{					
											e_err++;
										}
									}

									if($(this).attr("type") == "password")
									{
										if($(this).val().trim() == "")
										{					
											e_err++;
										}
									}
									if($(this).is('select'))
									{
										if($(this).val().trim() == "")
										{
											e_err++;
										}
									}
									if($(this).attr("type") == "checkbox")
									{
										if($(this).prop("checked") != true)
										{					
											e_err++;
										}
									}

									 if($(this).attr("type") == "hidden")
									{
									 	let fc = $(this).data("f-count");
									 	var ec = 0;
									 	for(let i = 1; i<= fc; i++)
									 	{
									 		let fid = $(this).data("fl_"+i);
									 		if($("#"+fid).prop("checked") == true)
									 		{
									 			ec++;
									 		}	
									 	}
									 	if(ec == 0) {
									 		e_err++;
									 	} 
									}
									if(e_err > 0)
									{
										if(!$("#patient-enroll-progress_"+cls_no).hasClass("alert-danger")){
											//$("#patient-enroll-progress_"+cls_no).addClass("alert-danger");
											//$("#patient-enroll-progress_"+cls_no).removeClass("completed");
										}
										f_count++;
									}
								});
								//Check if medication is empty then stop submitting form
								$('select[name*="medication_doctor"] :selected').each(function() {
									//console.log('Length: ',this.length); 
								    //console.log('Text:',this.text,'Value',this.value);
								    if(this.value == 0 || this.value === '') {
								    	localStorage.setItem("checkApplicationValid", 5);

								    	return false;
								    } else {
										localStorage.setItem("checkApplicationValid", 0);							    	
								    }
								});

							});
		
		//jQuery('body').on('blur',"#register_form [class*='patient-enroll-progress-']",checkTabErrors());
		// next button functionality
		jQuery('body').on('click', '#bSubmit, #final-submit', function() {
			if($("#enroll-progress li").hasClass("alert-danger")) {
				jQuery(".alert-danger:eq(0) a").trigger('click');
				return false;
			}

			if( !jQuery('form#register_form').valid()){ 
				return false;
			}
			else{
				jQuery("form#register_form").trigger('submit');
			}
			//Check if medication is empty then stop submitting form
			$('select[name*="medication_doctor"] :selected').each(function() {
			    if(this.text == 'Prescribing Healthcare Provider' ) {
			    	localStorage.setItem("checkApplicationValid", 2);
			    	return false;
			    }
			});
		});

							jQuery("form#register_form").submit(function(e) { 
								var checkValid = localStorage.getItem("checkApplicationValid");
								if (checkValid == 0) {
								    return true;
								} else {
								    $('#myErrorMessage').modal('show'); 
								    $('[href="#settings-s"]').tab('show');
									return false;
								}
								//return false;
								if (jQuery(this).valid()) {
								jQuery('input#bSubmit').prop('disabled', true);
								//check doctors and meds
								hasDoctors = false;
								for (dr_id = 1; dr_id <= jQuery('input.doctor-fields').length; dr_id++) {									
									if (jQuery('input[name="doctor_first_name[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_last_name[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_address[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_city[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_state[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_zip[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_phone[' + dr_id + ']"]').val() != '') {
										//  check if patient and healthcare provider phone and address are different
										if( jQuery('input[name="doctor_phone[' + dr_id + ']"]').val()== jQuery('#p_phone').val() && jQuery('input[name="doctor_phone[' + dr_id + ']"]').siblings('label.error').length==0 ){
											errorLabel = jQuery('<label>').attr('for', 'doctor_phone[' + dr_id + ']').addClass('error invalid-field').removeClass('correct').text('Your phone number cannot match the healthcare provider\'s phone number. Please enter a valid phone number.');
											errorLabel.insertAfter(jQuery('input[name="doctor_phone[' + dr_id + ']"]'));
											jQuery('input[name="doctor_phone[' + dr_id + ']"]').removeClass('correct').addClass('error');
											console.log(' dr phone ');
										}
										else if( jQuery('input[name="doctor_address[' + dr_id + ']"]').val().toLowerCase()== jQuery('#p_address').val().toLowerCase() && jQuery('input[name="doctor_address[' + dr_id + ']"]').siblings('label.error').length==0) {
											errorLabel = jQuery('<label>').attr('for', 'doctor_address[' + dr_id + ']').addClass('error invalid-field').removeClass('correct').text('Your address cannot match the healthcare provider\'s address. Please enter a valid address.');
											errorLabel.insertAfter(jQuery('input[name="doctor_address[' + dr_id + ']"]'));
											jQuery('input[name="doctor_address[' + dr_id + ']"]').removeClass('correct').addClass('error');
											console.log(' dr address ');
										}
										else{
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

								var fieldVal = jQuery('input[name="p_ssn"]').val();
								if(fieldVal!='') {
									jQuery.post("_sync.php", {key: 'p_ssn', value: fieldVal, type: 'patient'})
									.done(function(response) {
										result = JSON.parse(response);			
										if (result.success == 0) {
											e.preventDefault();
											e.stopPropagation();
											e.stopImmediatePropagation();
											
											jQuery('label[for="'+jQuery("input[name='p_ssn']").attr('id')+'"].error').remove();
											jQueryValidation_Highlight(jQuery('input[name="p_ssn"]'));
											jQueryValidation_ShowErrors(jQuery("<label>").attr('for', jQuery("input[name='p_ssn']").attr('id')).addClass('error').text('This social security number is already in use with a different email. Please try to log into your account with the correct email or contact customer support at 1-877-296-4673 for assistance.'), jQuery("input[name='p_ssn']"));
											//jQuery('html, body').animate({
											//		scrollTop: (jQuery("input[name='p_ssn']").offset().top-parseInt(200))
											//},500);
											
											return false;
										}
										else{ console.log('in 2');
											// add correct class from the input field
											jQuery("input[name='p_ssn']").addClass('correct');
											if (jQuery("form#register_form").valid() && hasDoctors && hasMeds && !medsWithErrors) {
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
													jQuery("#medication_form").before("<label class='error nopad-error'>Please check your entries for medication(s), all fields are required.<br><br></label>");
													jQuery("input.med-data,select.med-data").each(function (index, elem) {
														if (jQuery(elem).val() == '') {
															//show errors
															jQuery(elem).removeClass('correct');
															jQuery(elem).error('correct');
															jQuery(elem).addClass('field-error-only error');
														}
														else {
															jQuery(elem).removeClass('error field-error-only').addClass('correct');
														}
													});
												}
			
												if (!hasDoctors) {
													jQuery(".dr-form").eq(0).before("<label class='error' style='width: auto !important; padding: 0; font-size: 16px;'>Please add at least one doctor.<br><br></label>");
													jQuery(".dr-form").eq(0).find("input.dr-required,select.dr-required").each(function (index, elem) {
														if (jQuery(elem).val() == '') {
															//show errors
															jQuery(elem).removeClass('correct');
															jQuery(elem).error('correct');
															jQuery(elem).addClass('field-error-only error');
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
										}
									});
								}								

								//} else {
								//	scrollToInvalidFormElements();
								//}
							   } // End jQuery valid function
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
							
							function setSelectLabel(){
								var ele_id = jQuery(this).attr('id');
								ele_id = (typeof ele_id != 'undefined') ? ele_id : jQuery(this).attr('name');
								var placeholderText = jQuery(this).attr('placeholder');
								placeholderText = jQuery.trim(placeholderText);
								var cls_wrapper = jQuery(this).attr('name').split("[");
								if(jQuery(this).val()!=''){
									if( jQuery(this).siblings('label.placeHolder').length==0 && placeholderText!=''){
										// add red colored (*) to placeholder   
										var patt = /\*/g;
										var result = patt.test(placeholderText);
										if(result){
										  placeholderText = placeholderText.replace('*','') + '<span class="red">*</span>';
										}									
										jQuery(this).addClass('not-empty');
										jQuery('<label class="placeHolder active" for="'+ele_id+'">'+placeholderText+'</label>').insertBefore(jQuery(this));
										jQuery(this).parent('.jvFloat').removeClass(''+cls_wrapper[0]+'_wrapper');
									} else {
										jQuery(this).siblings('label.placeHolder').addClass('active');
									}
								}
								else{
									var patt = /\*/g;
									var result = patt.test(placeholderText);
									if(result){
									  placeholderText = placeholderText.replace('*','') + '<span class="red">*</span>';
									}									
									jQuery(this).siblings('label.placeHolder').removeClass('active');
									jQuery(this).parent('.jvFloat').addClass('patient-select-cntntr '+cls_wrapper[0]+'_wrapper');
									if($(this).next('label').hasClass('error invalid-field')) {
										$(this).next('label:last-child').remove();
									}
									//Add validation to switch between tabs if has any error

								}
							}
							
							//  label click fix
							jQuery(document).on('change', 'select', setSelectLabel);							

							jQuery('select').each(function(){
								var cls_wrapper = jQuery(this).attr('name').split("[");
								if( jQuery(this).hasClass('no-float-label')){
									jQuery(this).wrap('<div class="jvFloat patient-select-cntntr"></div>');								
								}
								else{
									jQuery(this).wrap('<div class="jvFloat patient-select-cntntr"></div>');
								}
									
								var ele_id = jQuery(this).attr('id');
								var placeholderText = jQuery(this).attr('placeholder');
								if(jQuery(this).val()!=''){

									ele_id = (typeof ele_id != 'undefined') ? ele_id : jQuery(this).attr('name');
									placeholderText = jQuery.trim(placeholderText);
									if( jQuery(this).siblings('label.placeHolder').length==0 && placeholderText!='' ){										
										// add red colored (*) to placeholder   
										var patt = /\*/g;
										var result = patt.test(placeholderText);
										if(result){
										  placeholderText = placeholderText.replace('*','') + '<span class="red">*</span>';
										}									
										jQuery(this).addClass('not-empty');

										if( !$(this).val() ) { 
											jQuery('<label class="placeHolder" for="'+ele_id+'">'+placeholderText+'</label>').insertBefore(jQuery(this));
										} else {
										    jQuery('<label class="placeHolder active" for="'+ele_id+'">'+placeholderText+'</label>').insertBefore(jQuery(this));
										}
									}
									jQuery(this).parent('.jvFloat').removeClass(' '+cls_wrapper[0]+'_wrapper');
								} else {
									var patt = /\*/g;
									var result = patt.test(placeholderText);
									if(result){
									  placeholderText = placeholderText.replace('*','') + '<span class="red">*</span>';
									}	
									if( !$(this).val() ) { 
										jQuery('<label class="placeHolder" for="'+ele_id+'">'+placeholderText+'</label>').insertBefore(jQuery(this));
									} else {
									    jQuery('<label class="placeHolder active" for="'+ele_id+'">'+placeholderText+'</label>').insertBefore(jQuery(this));
									}
									
								}
							});
							
							if(dataPosted){
								jQuery('input[name="p_ssn"]').trigger('blur'); 
							}
							
							// dob validation							
							jQuery('#p_dob').focusout(function(){
								validate_age();
							});
							
							//GOOGLE Analytics
							//ga('send', 'event', 'steps', 'formload', '20150806, enrollment, form, step1, personal-info', {'nonInteraction': 1})
						});
						function validate_age(){
							jQuery('#p_doberr').remove();
							var age_count = validateAge(jQuery('#p_dob').val());
							dateParts = age_count.split('/');
							var years = parseInt(dateParts[2]);
							var months = parseInt(dateParts[0]);
							var days = parseInt(dateParts[1]); 
							if( (years>=120) || (years==199 && (months==12)) || (years<=0 && months<=0 && days<=0) ){
								if( jQuery('label.error.invalid-field[for="p_dob"]').is(':hidden') || jQuery('label.error.invalid-field[for="p_dob"]').length==0 ){
									jQuery('#p_dob').removeClass('correct');
									jQuery('<label id="p_doberr" class="error invalid-field">Please input a valid date of birth</label>').insertAfter(jQuery('#p_dob'));
								}
								
							}
							else{
								jQuery('#p_doberr').remove();
							}
						}
						
						
						function validateAge(dob){
							var mdate = dob.toString();							
							var dobParts = mdate.split('/');
							var yearThen = parseInt(dobParts[2]);
							var monthThen = parseInt(dobParts[0]);
							var dayThen = parseInt(dobParts[1]);							
							var today = new Date();
							var birthday = new Date(yearThen, monthThen-1, dayThen);							
							var differenceInMilisecond = today.valueOf() - birthday.valueOf();							
							var year_age = Math.floor(differenceInMilisecond / 31536000000); 
							var day_age = Math.floor((differenceInMilisecond % 31536000000) / 86400000);				
							var month_age = Math.floor(day_age/30); 							 
							day_age = day_age % 30;							
							if (isNaN(year_age) || isNaN(month_age) || isNaN(day_age)) {
								return false;
							}
							else {
								return month_age+'/'+day_age+'/'+year_age;
							}
						}

					</script>
					<script>
  jQuery( function() {
  		jQuery('body').on('focus', '.doctor-fields', function() {
  		//console.log(jQuery(this).attr('class'));
  		var classNames = jQuery(this).attr('class').split(' ');
  		var doctors_fields_list = classNames[0].split('-');
  		
  		jQuery(this).autocomplete({
      minLength: 0,
     // source: "testajax.php",
      source: function( request, response ) {
      	var patient_id = "<?php echo $_SESSION[$session_key]['data']['id'];?>";
				jQuery.ajax({
				    url: "ajax_get_doctors_list.php",
				    dataType: "json",
				    data: {
				        id: patient_id,
				        sortType: 'FirstName',
				        PrvFirstName: jQuery(".doctor_first_name-"+doctors_fields_list[1]).val()
				    },
				    beforeSend : function() {
						jQuery(".doctor_first_name-"+doctors_fields_list[1]).addClass('loading-fname');
						//jQuery("#msg").fadeOut();
					},
				    success: function (data) {
				    	//console.log(data);
				    	jQuery(".doctor_first_name-"+doctors_fields_list[1]).removeClass('loading-fname');
					        response($.map(data.doctors, function (item) {
					        	//console.log(item);
					            return {
					            	PrvProviderId: item.PrvProviderId,
					                PrvFirstName: item.PrvFirstName,
					                PrvLastName:item.PrvLastName,
					                PrvPracticeName:item.PrvPracticeName,
					                PrvAddress1:item.PrvAddress1,
					                PrvAddress2:item.PrvAddress2,
					                PrvCity:item.PrvCity,
					                PrvState:item.PrvState,
					                PrvZip:item.PrvZip,
					                PrvWorkPhone:item.PrvWorkPhone,
					                PrvFaxNumber:item.PrvFaxNumber,
					                PrvProviderId:item.PrvProviderId
					                
					            };
					        }));
					    }
				});
      },
      focus: function( event, ui ) {
        jQuery( this ).val( ui.item.PrvFirstName );
        return false;
      },
      select: function( event, ui ) {
      //	$( ".doctor_id-"+doctors_fields_list[1]).val( ui.item.PrvProviderId );
      	$( ".doctor_first_name-"+doctors_fields_list[1]).val( ui.item.PrvFirstName );
        $( ".doctor_last_name-"+doctors_fields_list[1]).val( ui.item.PrvLastName );
        $( ".doctor_facility-"+doctors_fields_list[1]).val( ui.item.PrvPracticeName );
        $( ".doctor_address-"+doctors_fields_list[1]).val( ui.item.PrvAddress1 );
        $( ".doctor_address2-"+doctors_fields_list[1]).val( ui.item.PrvAddress2 );
        $( ".doctor_city-"+doctors_fields_list[1]).val( ui.item.PrvCity );
        $(".doctor_state-"+doctors_fields_list[1]).val(ui.item.PrvState);
        $( ".doctor_zip-"+doctors_fields_list[1]).val( ui.item.PrvZip );
        $( ".doctor_phone-"+doctors_fields_list[1]).val( ui.item.PrvWorkPhone );
        $( ".doctor_fax-"+doctors_fields_list[1]).val( ui.item.PrvFaxNumber );

        if($( ".doctor_first_name-"+doctors_fields_list[1]).val()) {
        	$(".doctor_first_name-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_last_name-"+doctors_fields_list[1]).val()) {
        	$(".doctor_last_name-"+doctors_fields_list[1]).trigger("focusout");
        }	
        if($( ".doctor_facility-"+doctors_fields_list[1]).val()) {
        	$( ".doctor_facility-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_address-"+doctors_fields_list[1]).val()) {
        	$( ".doctor_address-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_address2-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_address2-"+doctors_fields_list[1]).trigger("focusout");
        }	
        if($( ".doctor_city-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_city-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_state-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_state-"+doctors_fields_list[1]).trigger("focusout");
        }	
        if($( ".doctor_zip-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_zip-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_phone-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_phone-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_fax-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_fax-"+doctors_fields_list[1]).trigger("focusout");
        }

        if($( ".doctor_first_name-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_first_name-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_last_name-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_last_name-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_facility-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_facility-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_address-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_address-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_address2-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_address2-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_city-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_city-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_state-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_state-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        $(".doctor-state-field .jvFloat").removeClass('sel_wrapper doctor_state_wrapper');
     	$('<label class="placeHolder active" for="doctor_state['+doctors_fields_list[1]+']">State <span class="red">*</span></label>').insertBefore($(".doctor_state-"+doctors_fields_list[1]));
        if($( ".doctor_zip-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_zip-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_phone-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_phone-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_fax-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_fax-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }

        if($( ".doctor_first_name-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_first_name-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_last_name-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_last_name-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_facility-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_facility-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_address-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_address-"+doctors_fields_list[1]).addClass("correct not-empty");
        }	
        if($( ".doctor_address2-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_address2-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_city-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_city-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_state-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_state-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_zip-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_zip-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_phone-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_phone-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_fax-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_fax-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($(".doctor_state-"+doctors_fields_list[1]).siblings('label').hasClass('placeHolder active')) {
        	var labelCount = $(".doctor_state-"+doctors_fields_list[1]).prevAll('label').length;
        	if(labelCount > 1) {
				$(".doctor_state-"+doctors_fields_list[1]).prevAll('label').not(':first').remove();
				console.log('Done Removed, count more than 1');        		
        	} else {
        		console.log('Only 1 label');        		
        	}
		}
        return false;
      }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
     return jQuery( "<li>" )
        .append( "<div>" + item.PrvFirstName + " " + item.PrvLastName + "<br> Address:" + item.PrvAddress1+"</div>" )
        .appendTo( ul );
    };
  	});
	
	jQuery('body').on('focus', '.doctor-lname-fields', function() {
  		//console.log(jQuery(this).attr('class'));
  		var classNames = jQuery(this).attr('class').split(' ');
  		var doctors_fields_list = classNames[0].split('-');
  		
  		jQuery(this).autocomplete({
      minLength: 0,
     // source: "testajax.php",
      source: function( request, response ) {
      	var patient_id = "<?php echo $_SESSION[$session_key]['data']['id'];?>";
				jQuery.ajax({
				    url: "ajax_get_doctors_list.php",
				    dataType: "json",
				    data: {
				        id: patient_id,
				        sortType: 'LastName',
				        PrvFirstName: jQuery(".doctor_last_name-"+doctors_fields_list[1]).val()
				    },
				    beforeSend : function() {
						jQuery(".doctor_last_name-"+doctors_fields_list[1]).addClass('loading-lname');
						//jQuery("#msg").fadeOut();
					},
				    success: function (data) {
				    	//console.log(data);
				    	jQuery(".doctor_last_name-"+doctors_fields_list[1]).removeClass('loading-lname');
					        response($.map(data.doctors, function (item) {
					        	//console.log(item);
					            return {
					            	PrvProviderId: item.PrvProviderId,
					                PrvFirstName: item.PrvFirstName,
					                PrvLastName:item.PrvLastName,
					                PrvPracticeName:item.PrvPracticeName,
					                PrvAddress1:item.PrvAddress1,
					                PrvAddress2:item.PrvAddress2,
					                PrvCity:item.PrvCity,
					                PrvState:item.PrvState,
					                PrvZip:item.PrvZip,
					                PrvWorkPhone:item.PrvWorkPhone,
					                PrvFaxNumber:item.PrvFaxNumber,
					                PrvProviderId:item.PrvProviderId
					                
					            };
					        }));
					    }
				});
      },
      focus: function( event, ui ) {
        jQuery( this ).val( ui.item.PrvLastName );
        return false;
      },
      select: function( event, ui ) {
      	console.log('Address 1 values : ',ui.item.PrvAddress1);
      	console.log('Address 2 values : ',ui.item.PrvPracticeName);
      //	$( ".doctor_id-"+doctors_fields_list[1]).val( ui.item.PrvProviderId );
      	$( ".doctor_first_name-"+doctors_fields_list[1]).val( ui.item.PrvFirstName );
        $( ".doctor_last_name-"+doctors_fields_list[1]).val( ui.item.PrvLastName );
        $( ".doctor_facility-"+doctors_fields_list[1]).val( ui.item.PrvPracticeName );
        $( ".doctor_address-"+doctors_fields_list[1]).val( ui.item.PrvAddress1 );
        $( ".doctor_address2-"+doctors_fields_list[1]).val( ui.item.PrvAddress2 );
        $( ".doctor_city-"+doctors_fields_list[1]).val( ui.item.PrvCity );
        $(".doctor_state-"+doctors_fields_list[1]).val(ui.item.PrvState);
        $( ".doctor_zip-"+doctors_fields_list[1]).val( ui.item.PrvZip );
        $( ".doctor_phone-"+doctors_fields_list[1]).val( ui.item.PrvWorkPhone );
        $( ".doctor_fax-"+doctors_fields_list[1]).val( ui.item.PrvFaxNumber );

        if($( ".doctor_first_name-"+doctors_fields_list[1]).val().length != 0) {	
        	$(".doctor_first_name-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_last_name-"+doctors_fields_list[1]).val().length != 0) {	
        	$(".doctor_last_name-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_facility-"+doctors_fields_list[1]).val().length != 0) {	
        	$( ".doctor_facility-"+doctors_fields_list[1]).trigger("focusout");
        }	
        if($( ".doctor_address-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_address-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_address2-"+doctors_fields_list[1]).val().length != 0) {	
        	$( ".doctor_address2-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_city-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_city-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_state-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_state-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_zip-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_zip-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_phone-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_phone-"+doctors_fields_list[1]).trigger("focusout");
        }
        if($( ".doctor_fax-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_fax-"+doctors_fields_list[1]).trigger("focusout");
        }

        if($( ".doctor_first_name-"+doctors_fields_list[1]).val().length != 0) {	
        	$(".doctor_first_name-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_last_name-"+doctors_fields_list[1]).val().length != 0) {	
        	$(".doctor_last_name-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_facility-"+doctors_fields_list[1]).val().length != 0) {	
        	$( ".doctor_facility-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_address-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_address-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_address2-"+doctors_fields_list[1]).val().length != 0) {	
        	$( ".doctor_address2-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_city-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_city-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_state-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_state-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
         $(".doctor-state-field .jvFloat").removeClass('sel_wrapper doctor_state_wrapper');
     	$('<label class="placeHolder active" for="doctor_state['+doctors_fields_list[1]+']">State <span class="red">*</span></label>').insertBefore($(".doctor_state-"+doctors_fields_list[1]));
        if($( ".doctor_zip-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_zip-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_phone-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_phone-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }
        if($( ".doctor_fax-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_fax-"+doctors_fields_list[1]).prev(".placeHolder").addClass("active");
        }

        if($( ".doctor_first_name-"+doctors_fields_list[1]).val().length != 0) {	
        	$(".doctor_first_name-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_last_name-"+doctors_fields_list[1]).val().length != 0) {	
        	$(".doctor_last_name-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_facility-"+doctors_fields_list[1]).val().length != 0) {	
        	$( ".doctor_facility-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_address-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_address-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_address2-"+doctors_fields_list[1]).val().length != 0 || $( ".doctor_address2-"+doctors_fields_list[1]).val().length != "") {	
        	$( ".doctor_address2-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_city-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_city-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_state-"+doctors_fields_list[1]).val()) {	
        	$(".doctor_state-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_zip-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_zip-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_phone-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_phone-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
        if($( ".doctor_fax-"+doctors_fields_list[1]).val()) {	
        	$( ".doctor_fax-"+doctors_fields_list[1]).addClass("correct not-empty");
        }
      	if($(".doctor_state-"+doctors_fields_list[1]).siblings('label').hasClass('placeHolder active')) {
        	var labelCount = $(".doctor_state-"+doctors_fields_list[1]).prevAll('label').length;
        	if(labelCount > 1) {
				$(".doctor_state-"+doctors_fields_list[1]).prevAll('label').not(':first').remove();
				console.log('Done Removed, count more than 1');        		
        	} else {
        		console.log('Only 1 label');        		
        	}
		}
        return false;
      }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
     return jQuery( "<li>" )
        .append( "<div>" + item.PrvFirstName + " " + item.PrvLastName + "<br> Address:" + item.PrvAddress1+"</div>" )
        .appendTo( ul );
    };
  	});	

  });

  </script>

					<!-- Facebook Pixel -->
					<script>
						//fbq('track', 'Lead');
					</script>
<style>html body #main_content {
    padding: 0px;
}</style>

			<div class="container">
<div class="col-sm-12 p0">
					
					<!-- <h2 class=" dblue-text no-text-transformation f30">Enrollment Form</h2> -->

					<?php /* if (isset($agent_details['use_payment']) && (bool) $agent_details['use_payment']) { ?>
						<p class="subhead2-blue left-alignment" style="color: #dd0000; text-transform: none;">
							Your benefits provider (<?=$agent_details['corporation_name']?>) has offered the Prescription Hope Pharmacy Program at no cost to you. Please complete the enrollment form below and submit the enrollment form to begin the process.
							<br><br>
							In the event your benefits provider does not cover the cost of the $<?=$_SESSION['rate']?> per month per medication service fee in the future, you will be asked to provide payment information at that time.
						</p>
					<?php } */?>

					<!-- <p class="align-center moduleSubheader">
						All of the information requested below is required by the pharmaceutical company to process<br/> your medication orders. All of your information will be kept confidential and protected.<br/><br/>
<strong>One form per person.</strong> 
					</p> -->
				
	
		<!-- <div class="text-left text-blue ">Fields with asterisks (<span class="red1">*</span>) are required</div> -->
					<?php /*<form id="register_form" method="post" action="enroll.php" autocomplete="nope">
					<h2 class="dblue-text no-text-transformation m20-new pt0">Patient Information</h2>	
					<div>
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_first_name" value="<?php echo htmlspecialchars(stripslashes($data['p_first_name']));?>" class="LoNotSensitive <?=(($data['p_first_name'] != '') ? 'correct' : '')?>" placeholder="First Name *"></div>
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['p_middle_initial']));?>" maxlength="1" class="LoNotSensitive <?=(($data['p_middle_initial'] != '') ? 'correct' : '')?>" placeholder="Middle Initial"></div>
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_last_name" value="<?php echo htmlspecialchars(stripslashes($data['p_last_name']));?>" class="LoNotSensitive <?=(($data['p_last_name'] != '') ? 'correct' : '')?>" placeholder="Last Name *"></div>
					</div>
					<div class="clear"></div>
					
					
					<div>
						<div class="half-width m-v-10"><input autocomplete="nope" type="text" data-type="patient" id="p_dob" name="p_dob" value="<?php echo $data['p_dob'];?>" class="LoNotSensitive tooltiptext" placeholder="Date of Birth (mm/dd/yyyy) *" data-text="Why Is My Date Of Birth Needed?" data-hint="Your Date of Birth is required by the pharmaceutical company to process your medication orders."></div>
						<?php if(isset($response->success) && $response->success==2){ ?>
						<div class="half-width m-v-10"><input autocomplete="nope" type="text" data-type="patient" name="p_ssn" value="<?php echo $_POST['p_ssn'];?>" maxlength="11" class="LoNotSensitive tooltiptext" placeholder="Social Security Number *" data-text="Why Is My Social Security Number Needed?" data-hint="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology."></div>
						<?php } else { ?>
						<div class="half-width m-v-10"><input autocomplete="nope" type="text" data-type="patient" name="p_ssn" value="<?php echo $data['p_ssn'];?>" maxlength="11" class="LoNotSensitive tooltiptext" placeholder="Social Security Number *" data-text="Why Is My Social Security Number Needed?" data-hint="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology."></div>
						<?php } ?>
					</div>
					<div class="clear"></div>

					<div class="radio_opt m-v-10">
						<div class="form-row1">
							<div class="half-width align-left">
								<label for="p_gender">Gender <span class="red">*</span></label>
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

						<div class="form-row1">
							<div class="half-width align-left">
								<label for="p_is_minor">Is this application on behalf of a minor? <span class="red">*</span></label>
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
					</div>

					<div class="form-row1 patient_parent_profile no-show">
						<div>
							<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_parent_first_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_first_name']));?>" class="LoNotSensitive" placeholder="Parent/Guardian First Name *"></div>
							<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_parent_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_middle_initial']));?>" maxlength="1" class="LoNotSensitive" placeholder="Parent/Guardian Middle Initial *"></div>
							<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_parent_last_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_last_name']));?>" class="LoNotSensitive" placeholder="Parent/Guardian Last Name *"></div>
						</div>
						<div class="clear"></div>

						<input autocomplete="nope" type="text" data-type="patient" name="p_parent_phone" value="<?php echo $data['p_parent_phone'];?>" class="LoNotSensitive" placeholder="Parent/Guardian Phone *">
					</div>
					<div class="clear"></div>
					<?php */?>
					<!-- <div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_alternate_contact_name" value="<?php //echo htmlspecialchars(stripslashes($data['p_alternate_contact_name']));?>" class="LoNotSensitive" placeholder="Alternate Contact Name"></div>
						<div class="half-width"><input autocomplete="nope" type="text" data-type="patient" name="p_alternate_phone" value="<?php //echo $data['p_alternate_phone'];?>" class="LoNotSensitive not_required_phone" placeholder="Alternate Contact Phone"></div>
					</div>
					<div class="clear"></div> -->
					<?php //echo "<pre>";print_r($_SESSION['register_data']);echo "</pre>";?>
					<?php if($data['p_hear_about'] == '2685-4694 Access Health Insurance, Inc') $data['p_hear_about'] = '2685-4694 JibeHealth'; ?>
					<?php if (isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') { ?>

					<?php 
						
						$broker_array = array();
						if(!empty($_SESSION['register_data']['p_application_source'])){
							 $broker_string = trim(substr($_SESSION['register_data']['p_application_source'],0,9));
							 $broker_array = explode('-', $broker_string);

						}
						if(count($broker_array) == 2){
						?>
							<?php /*  <input autocomplete="nope" type="text" data-type="patient" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes(trim(substr($data['p_application_source'],9))));?>" class="full-width" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>> */?>
						<?php	
						}else{
						?>	
							<?php /* <input autocomplete="nope" type="text" data-type="patient" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about']));?>" class="full-width" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>> */?>
						<?php }?>
						
					<?php } else { ?>
						<div class="full-width">
							<?php 
						//echo "<pre>";print_r($data);echo "</pre>"; 

						/*$broker_name = trim(substr($_GET['my_broker_source'],9));
						if(isset($_GET['my_broker_source'])){
							$data['p_application_source'] = $_GET['my_broker_source'];
						}*/
						$broker_array = array();
						if(!empty($data['p_application_source'])){
							 $broker_string = trim(substr($data['p_application_source'],0,9));
							 $broker_array = explode('-', $broker_string);

						}
						//print_r($broker_array);
					/*	if(preg_match("/[a-z]/i", $data['p_application_source'])){
						    print "it has alphabet!";
						}else{
							print "not alphabet!";
						}*/
						//Anil code
						?>
						<?php if(!empty($data['p_application_source']) && count($broker_array) == 2){?>
							<!-- <select data-type="patient" name="p_hear_about" id="p_hear_about" data-value="<?php echo htmlspecialchars(stripslashes($data['p_application_source']));?>" class="full-width LoNotSensitive form-control" placeholder="How did you hear about Prescription Hope? *" <?php if(!empty($data['p_application_source']) && count($broker_array) == 2){ echo "disabled";}?>> -->
							<?php /*<input autocomplete="nope" type="text" data-type="patient" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes(trim(substr($data['p_application_source'],9))));?>" class="full-width" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>> */?>
						<?php }else{?>
							<?php /*<select data-type="patient" name="p_hear_about" id="p_hear_about" data-value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about']));?>" class="full-width LoNotSensitive form-control" placeholder="How did you hear about Prescription Hope? *">
								<option value="">How did you hear about Prescription Hope? *</option>
								<?php /*if(!empty($data['p_application_source']) && count($broker_array) == 2){
									$broker_name =  trim(substr($data['p_application_source'],9));
								?>
									<option value="<?php echo $data['p_application_source'];?>"><?php echo $broker_name;?></option>
								<?php }*/?>
								<!-- <option value="Facebook">Facebook</option>
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
								<option value="Paper Mailing">Paper Mailing</option> -->
								<!--option value="Social Media">Social Media</option-->
								<!-- <option value="Linkedin">Linkedin</option>
								<option value="Twitter">Twitter</option>
								<option value="WPBF25 Health and Safety with Dr. Oz">WPBF25 Health and Safety with Dr. Oz</option>
								<option value="Other">Other</option>
							</select> -->
						<?php }?>		
								
						</div>
					<?php } ?>
					<?php /*<input autocomplete="nope" type="hidden" data-type="patient" name="p_application_source" value="<?=((isset($data['p_application_source'])) ? $data['p_application_source'] : '')?>"> */?>

					<!--div class="form-group-spacer"></div-->
					<?php /*
					<br>
					<div class="h-line">&nbsp;</div>

					<h2 class=" dblue-text no-text-transformation">Monthly Household Income Information</h2>

					<p class="normal align-center">	Your monthly household income verification is required by the pharmaceutical company to process your medication orders.<br/>It will not be used for any other purposes and will remain confidential.</p>

					<div class="form-group-spacer"></div>  */?>

					<!--p class="normal align-left">
						Your Date of Birth and Social Security Number are required by the pharmaceutical companies for completing the application process to begin filling your medication order(s).<br>Your personal information is always kept confidential.
					</p-->

					<?php /*<div class="">
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
					<br> */?>

					<!-- <div id="patient_income_section" class="hidden"> -->
						<!-- <p class="normal align-left">
							Please answer all of these questions to the best of your ability.
						</p> -->
						<!-- <div class="patient_income_yes_only">
							<div class="half-width align-left"><label for="p_employment_status" class="line-height-48">Are you currently employed? <span class="red">*</span></label></div>
							<div class="half-width">
								<select data-type="patient" id="p_employment_status" name="p_employment_status" preload="<?php echo $data['p_employment_status'];?>" class="full-width no-float-label LoNotSensitive">
										<option value=''>Select ...</option>
										<option value='F'>Full-Time</option>
										<option value='P'>Part-Time</option>
										<option value='R'>Retired</option>
										<option value='U'>Unemployed</option>
										<option value='S'>Self-Employed</option>
								</select>
							</div>
						</div> -->

						<!-- <div>
							<div class="half-width align-left"><label for="p_married" class="line-height-48">Are you married? <span class="red">*</span></label></div>
							<div class="half-width">
								<select data-type="patient" name="p_married" id="p_married" preload="<?php echo $data['p_married'];?>" class="full-width no-float-label LoNotSensitive">
									<option value=''>Select ...</option>
									<option value='S'>Single</option>
									<option value='M'>Married</option>
									<option value='D'>Separated</option>
									<option value='W'>Widowed</option>
								</select>
							</div>
						</div> -->

						<!-- <div>
							<div class="half-width align-left"><label for="p_household" class="line-height-48">How many people live in your household? <span class="red">*</span></label></div>
							<div class="half-width">
								<select data-type="patient" name="p_household" id="p_household" preload="<?php echo $data['p_household'];?>" class="full-width no-float-label LoNotSensitive">
									<option value=''>Select ...</option>
									<?php for ($i = 1; $i < 11; $i++) { ?>
										<option value='<?=$i?>'><?=$i?></option>
									<?php } ?>
								</select>
							</div>
						</div> -->

						<?php /*<div class="">
							<div class="three-quarters-width align-left">
								<label for="p_income_file_tax_return">Do you currently file tax returns? <span class="red">*</span></label>
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
						<div class="clear"></div> */?>

						<!-- <div class="">
							<div class="three-quarters-width align-left">
								<label for="p_uscitizen">Are you a US Citizen? <span class="red">*</span></label>
							</div>
							<div class="one-quarter-width align-right">
								<label for="p_uscitizen_yes" class='rb-container no_width'>Yes
									<input autocomplete="nope" type="radio" data-type="patient" id="p_uscitizen_yes" name="p_uscitizen" value="1" class="LoNotSensitive" <?php //echo ((in_array('us_citizen', $radios_submitted) && $data['p_uscitizen'] != '') ? 'preload="' . (int)$data['p_uscitizen'] . '"' : ''); ?>>
									<span class="rb-checkmark"></span>
								</label>
								<label for="p_uscitizen_no" class='rb-container no_width'>No
									<input autocomplete="nope" type="radio" data-type="patient" id="p_uscitizen_no" name="p_uscitizen" value="0" class="LoNotSensitive">
									<span class="rb-checkmark"></span>
								</label>
							</div>
							<br>
						</div>
						<div class="clear"></div> -->

						<!-- <div class="patient_income_yes_only"> -->
							<!-- <div class="">
								<div class="three-quarters-width align-left">
									<label for="p_medicaid">Have you applied for Medicaid? <span class="red">*</span></label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_medicaid_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicaid_yes" name="p_medicaid" value="1" class="radio-groups LoNotSensitive" <?php //echo ((in_array('medicaid', $radios_submitted) && $data['p_medicaid'] != '') ? 'preload="' . (int)$data['p_medicaid'] . '"' : ''); ?>>
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_medicaid_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicaid_no" name="p_medicaid" value="0" class="radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div> -->

							<!-- <div class="radio-group-row-2 p_medicaid_2nd">
								<div class="three-quarters-width align-left">
									<label for="p_medicaid_denial" class="">If yes, did you receive a denial letter? <span class="red">*</span></label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_medicaid_denial_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicaid_denial_yes" name="p_medicaid_denial" value="1" <?php //echo ((in_array('medicaid_denial', $radios_submitted) && $data['p_medicaid_denial'] != '') ? 'preload="' . (int)$data['p_medicaid_denial'] . '"' : ''); ?> class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_medicaid_denial_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicaid_denial_no" name="p_medicaid_denial" value="0" class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div> -->

							<!-- <div class="">
								<div class="three-quarters-width align-left">
									<label for="p_medicare">Are you on Medicare? <span class="red">*</span></label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_medicare_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicare_yes" name="p_medicare" value="1" class="radio-groups LoNotSensitive" <?php //echo ((in_array('medicare', $radios_submitted) && $data['p_medicare'] != '') ? 'preload="' . (int)$data['p_medicare'] . '"' : ''); ?>>
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_medicare_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicare_no" name="p_medicare" value="0" class="radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div> -->
							<?php
								$pMedicare  = ((int)$data['p_medicare'] == 1) ? 'display: block' : 'display: none';
							?>
							<!-- <div class="radio-group-row-2 p_medicare_2nd" style="<?php echo $pMedicare; ?>">
								<div class="three-quarters-width align-left">
									<label for="p_medicare_part_d" class="">Do you have Medicare Part D? <span class="red">*</span></label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_medicare_part_d_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicare_part_d_yes" name="p_medicare_part_d" value="1" <?php //echo ((in_array('medicare_part_d', $radios_submitted) && $data['p_medicare_part_d'] != '') ? 'preload="' . (int)$data['p_medicare_part_d'] . '"' : ''); ?> class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_medicare_part_d_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_medicare_part_d_no" name="p_medicare_part_d" value="0" class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div> -->

							<?php 
								$pCoverageGapYes  = ((int)$data['p_medicare_part_d'] == 1) ? 'display: block' : 'display: none';
							?>


							<!-- <div class="radio-group-row-2 p_medicare_part_d_2nd" style="<?php echo $pCoverageGapYes; ?>">
								<div class="three-quarters-width align-left">
									<label for="p_coverage_gap_yes" class="">Are you in the coverage gap? <span class="red">*</span></label>
								</div>

								<div class="one-quarter-width align-right">
									<label for="p_coveragegapyes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_coveragegapyes" name="p_coveragegapyes" value="1" class="radio-groups LoNotSensitive" <?php echo (( $data['p_coveragegapyes'] != '') ? 'preload="' . (int)$data['p_coveragegapyes'] . '"' : ''); ?>>
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_coveragegapno" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_coveragegapno" name="p_coveragegapyes" value="0" class="radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
								
							</div>	
							<div class="clear"></div> -->

							
							<!-- <div class="radio-group-row-2 p_coveragegapyes_2nd">
								<div class="three-quarters-width align-left">
									<label for="p_prescription_money_yes" class="">How much money have you spent out of pocket on your prescriptions for the current year? <span class="red">*</span></label>
								</div>
								<div class="one-quarter-width align-right pocketmoney-enroll">
									<input autocomplete="nope" type="text" data-type="patient" name="p_pocketmoney" id="p_pocketmoney" value="<?php echo $data['p_pocketmoney'];?>" class="dollar-amount input_zero no-float-label LoNotSensitive" placeholder="">									
								</div>
								<br>
								
							</div>	
							<div class="clear"></div>  -->
							<!-- <div class="p_medicaid_2nd">
								<div class="three-quarters-width align-left">
									<label for="p_lis">Have you applied for Low Income Subsidy (LIS)? <span class="red">*</span></label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_lis_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_lis_yes" name="p_lis" value="1" class="radio-groups LoNotSensitive" <?php //echo ((in_array('lis', $radios_submitted) && $data['p_lis'] != '') ? 'preload="' . (int)$data['p_lis'] . '"' : ''); ?>>
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_lis_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_lis_no" name="p_lis" value="0" class="radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div> -->

							<!-- <div class="radio-group-row-2 p_medicaid_2nd p_lis_2nd">
								<div class="three-quarters-width align-left">
									<label for="p_lis_denial" class="">If yes, did you receive a denial letter? <span class="red">*</span></label>
								</div>
								<div class="one-quarter-width align-right">
									<label for="p_lis_denial_yes" class='rb-container no_width'>Yes
										<input autocomplete="nope" type="radio" data-type="patient" id="p_lis_denial_yes" name="p_lis_denial" value="1" <?php //echo ((in_array('lis_denial', $radios_submitted) && $data['p_lis_denial'] != '') ? 'preload="' . (int)$data['p_lis_denial'] . '"' : ''); ?> class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
									<label for="p_lis_denial_no" class='rb-container no_width'>No
										<input autocomplete="nope" type="radio" data-type="patient" id="p_lis_denial_no" name="p_lis_denial" value="0" class="tmargin5 radio-groups LoNotSensitive">
										<span class="rb-checkmark"></span>
									</label>
								</div>
								<br>
							</div>
							<div class="clear"></div> -->

							<!-- <div class="">
								<div class="three-quarters-width align-left">
									<label for="p_disabled_status">Are you disabled as determined by Social Security? <span class="red">*</span></label>
								</div>
								
								<br>
							</div>
							<div class="clear"></div> -->

							<!-- <br>
							<p class="normal align-left">
								Check the box for the type(s) of income you receive monthly.<br><br>
								Then put the correct number in the box.
							</p> -->

							<!-- <div id="first_income_source_row" class="align-left">
								<label for="p_has_salary" class="cb-container checkbox-label">Monthly Gross Salary/Wages Income
									<input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_salary" name="p_has_salary" value="1" <?php //echo (($data['p_income_salary'] != '' && $data['p_income_salary'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
									<span class="cb-checkmark"></span>
								</label>
								<div class="clear"></div>
							</div> -->
							
					<?php /*<div old-id="new_doctor_form" id="doctors_forms_list">
						<?php $valid_drs = 0; ?>
						<?php foreach ($data['doctors'] as $dr_key => $doctor) { ?>
							<?php if ((count($data['doctors']) > 1 && $doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') || count($data['doctors']) == 1) { ?>
								<?php $valid_drs++; ?>
	
								<div old-id="doctor_form" class="dr-form" id="pb<?=($valid_drs)?>">
									<!-- <input class="doctor_id-<?=$valid_drs?>" autocomplete="nope" type="hidden" name="doctor_id[<?=$valid_drs?>]" id="doctor_id" value="<?=$doctor['doctor_id']?>">
									<input class="doctor_check-<?=$valid_drs?>" autocomplete="nope" type="hidden" value="<?=$doctor['doctor_first_name']?><?=$doctor['doctor_last_name']?><?=$doctor['doctor_facility']?><?=$doctor['doctor_address']?><?=$doctor['doctor_address2']?><?=$doctor['doctor_city']?><?=$doctor['doctor_state']?><?=$doctor['doctor_zip']?><?=$doctor['doctor_phone']?><?=$doctor['doctor_fax']?>"> -->
									<?php if($valid_drs>1) { ?>
									<div class="remove-div-1"><a href="javascript:void(0);" class="remove_block" id="remove_provider_pb<?=($valid_drs)?>">X Remove Provider</a></div>
									<?php } ?>
									<div class="doctor-no-field p20-width align-left bold text-center w100">Healthcare Provider <?=($valid_drs)?>:</div>
									<div>

										<div class="half-width doctor-fname-field"><input autocomplete="nope" type="text" name="doctor_first_name[<?=$valid_drs?>]" value="<?=$doctor['doctor_first_name']?>" class="doctor_first_name-<?=$valid_drs?> dr-required dr-data doctor-fields LoNotSensitive" placeholder="Healthcare Provider First Name *"></div>
										<div class="half-width doctor-lname-field"><input autocomplete="nope" type="text" name="doctor_last_name[<?=$valid_drs?>]" value="<?=$doctor['doctor_last_name']?>" class="doctor_last_name-<?=$valid_drs?> dr-required dr-data doctor-lname-fields LoNotSensitive" placeholder="Healthcare Provider Last Name *"></div>
									</div>

									<div>										
										<div class="half-width doctor-facility-field"><input autocomplete="nope" type="text" name="doctor_facility[<?=$valid_drs?>]" value="<?=$doctor['doctor_facility']?>" class="doctor_facility-<?=$valid_drs?> dr-data LoNotSensitive" placeholder="Facility Name"></div>
										<div class="half-width doctor-address-field"><input autocomplete="nope" type="text" name="doctor_address[<?=$valid_drs?>]" value="<?=$doctor['doctor_address']?>" class="doctor_address-<?=$valid_drs?> dr-required dr-data dr-address LoNotSensitive" placeholder="Address *" rel="Some health care providers have multiple locations they work from, please provide the address for the location you visit your health care provider at."></div>
									</div>
								
									<div>										
										<div class="half-width doctor-address2-field"><input autocomplete="nope" type="text" name="doctor_address2[<?=$valid_drs?>]" value="<?=$doctor['doctor_address2']?>" class="doctor_address2-<?=$valid_drs?> dr-data LoNotSensitive" placeholder="Suite Number"></div>
										<div class="half-width doctor-city-field"><input autocomplete="nope" type="text" name="doctor_city[<?=$valid_drs?>]" value="<?=$doctor['doctor_city']?>" class="doctor_city-<?=$valid_drs?> dr-required dr-data LoNotSensitive" placeholder="City *"></div>
									</div>									

									<div>										
										<div class="half-width doctor-state-field">
											<select name="doctor_state[<?=$valid_drs?>]" id="doctor_state[<?=$valid_drs?>]" preload="<?=$doctor['doctor_state']?>" class="doctor_state-<?=$valid_drs?> dr-required dr-data full-width LoNotSensitive" placeholder="State *">
												<option value="" selected="selected">State *</option>
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
												<option value="PR">Puerto Rico</option>
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
										<div class="half-width doctor-zip-field"><input autocomplete="nope" type="text" name="doctor_zip[<?=$valid_drs?>]" value="<?=$doctor['doctor_zip']?>" maxlength="5" class="doctor_zip-<?=$valid_drs?> dr-required dr-data dr-zip LoNotSensitive" placeholder="Zip Code *"></div>
									</div>									

									<div>										
										<div class="half-width doctor-phone-field"><input autocomplete="nope" type="text" name="doctor_phone[<?=$valid_drs?>]" value="<?=$doctor['doctor_phone']?>" class="doctor_phone-<?=$valid_drs?> dr-required dr-data dr-phone LoNotSensitive" placeholder="Phone Number *"></div>
										<div class="half-width doctor-fax-field"><input autocomplete="nope" type="text" name="doctor_fax[<?=$valid_drs?>]" value="<?=$doctor['doctor_fax']?>" class="doctor_fax-<?=$valid_drs?> dr-data dr-fax LoNotSensitive" placeholder="Fax Number"></div>
									</div>									
								</div>
							<?php } ?>
						<?php } ?>
					</div> 

					<div><input autocomplete="nope" type="button" name="bAddANewDoctor" id="bAddANewDoctor" value="Add Another Provider" class="cancel small-button-orange button-auto-width"></div>
					<div class="form-group-spacer"></div> */?>

					<!-- <div class="h-line">&nbsp;</div>

					<h2 class="dblue-text no-text-transformation">Medication Information</h2> -->

					<!--<div class="medication_list">
						<!-- <p class="normal align-center">
							Please list all the medications you are requesting through Prescription Hope.
							<a style="display:none;" href="#" class='disable-click inline-tooltip-icon medication_list_tt' data-tooltip1="Reminder: Prescription Hope is $<?=$_SESSION['rate']?> per month for each medication you are requesting"></a>
						</p> -->
						

						<!--<div class="no-margin" id="medication_form">
						<?php
						$valid_meds = 0;
						/* foreach ($data['medication'] as $med_key => $medication) { ?>
							<?php if ($medication['medication_name'] != '' && $medication['medication_strength'] != '' && $medication['medication_frequency'] != '' && (int) $medication['medication_doctor'] != '') {
								$valid_meds++;
								?>
								<div class="medication-row"  id="mb<?php echo $valid_meds?>">
									<?php if($valid_meds>1) { ?>
									<div class="remove-div-1"><a href="javascript:void(0);" class="remove_block" id="remove_provider_mb<?php echo $valid_meds?>">X Remove Medication</a></div>
									<?php } ?>
								
									<div class="medication-no-field p20-width align-center bold w100">Medication <?=($valid_meds)?></div>
									<div>
										<div class="medication-name-field half-width">
											<input autocomplete="nope" type="text" name="medication_name[<?=$valid_meds?>]" value="<?=$medication['medication_name']?>" placeholder="Medication Name *" class="med-data LoNotSensitive med_name">
										</div>

										<div class="medication-strength-field half-width">
											<input autocomplete="nope" type="text" name="medication_strength[<?=$valid_meds?>]" value="<?=$medication['medication_strength']?>" placeholder="Medication Strength *" class="med-data LoNotSensitive">
										</div>
									</div>
									<div>
										<div class="medication-frequency-field half-width">
											<input autocomplete="nope" type="text" name="medication_frequency[<?=$valid_meds?>]" value="<?=$medication['medication_frequency']?>" placeholder="Medication Frequency (ex. daily) *" class="med-data LoNotSensitive">
										</div>

										<div class="medication-doctor-field half-width">
											<select autocomplete="nope" name="medication_doctor[<?=$valid_meds?>]" preload="<?=$medication['medication_doctor']?>" class="doctors_dropdown med-data full-width LoNotSensitive" placeholder="Prescribing Healthcare Provider *">
												<option value="">Prescribing Healthcare Provider *</option>
											</select>
										</div>
									</div>									
								</div>								
							<?php } ?>
						<?php } */?>

						<?php /*if ($valid_meds == 0) { >
							<!--input type="hidden" name="med_id" id="med_id" value="1"-->

							<div class="medication-row" id="mb1">
							<div class="medication-no-field p20-width align-center bold w100">Medication 1</div>
								<div>
									<div class="medication-name-field half-width">
										<input autocomplete="nope" type="text" name="medication_name[1]" value="" placeholder="Medication Name *" class="med-data LoNotSensitive med_name">
									</div>
									<div class="medication-strength-field half-width">
										<input autocomplete="nope" type="text" name="medication_strength[1]" value="" placeholder="Medication Strength *" class="med-data LoNotSensitive">
									</div>
								</div>
								<div>
									<div class="medication-frequency-field half-width">
										<input autocomplete="nope" type="text" name="medication_frequency[1]" value="" placeholder="Medication Frequency (ex. daily) *" class="med-data LoNotSensitive">
									</div>
									<div class="medication-doctor-field half-width">
										<select autocomplete="nope" name="medication_doctor[1]" class="doctors_dropdown med-data full-width LoNotSensitive" placeholder="Prescribing Healthcare Provider *">
											<option value="">Prescribing Healthcare Provider *</option>
											<?php /*foreach ($data['doctors'] as $dr_key => $doctor) { ?>
												<?php if ($doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') { ?>
													<option value="<?php echo ($dr_key);?>"><?php echo 'Doctor ' . ($dr_key) . ' (' . $doctor['doctor_first_name'] . ' ' . $doctor['doctor_last_name'] . ')';?></option>
												<?php } ?>
											<?php } */ ?>
										<?php /*</select>
									</div>
								</div>								
							</div>
						<?php } */?>
						</div>-->
						<!-- <div class="med_info" id="med_info" style="display: none;">
							<p>You are requesting <span class="ct_med_txt">3 medications</span> through Prescription Hope.</p>
							<p>In the event you are approved for all of the medications you are requesting, your monthly total will be <span class="total_str">$150</span>.</p>
						</div>
						<input autocomplete="nope" type="button" name="bAddANewMedication" id="bAddANewMedication" value="ADD ANOTHER MEDICATION" class="cancel small-button-orange button-auto-width"> -->
					<!-- </div> -->
					
<div class="pay-info">
<!-- <div class="h-line">&nbsp;</div> -->

	<?php if (!(isset($agent_details['use_payment']) && (bool) $agent_details['use_payment'])) { /*?>
		<h2 class="dblue-text no-text-transformation">Payment Information</h2>
		<p class="normal align-center">Rest assured, we will not charge your card untill we verify we can access one or more of your medications.</p>
		<div id="med-list-box" style="display: none;">			
			<div class="Medication-List">
				<div class=" medication-no-field p20-width align-center bold w100">Your Medication List</div>
				<div class="responsive-table">
					<table id="med_list" class="table table-striped col-sm-10 align-center">
						<tbody>										  
							<tr class="total"><td>Total</td><td class="total_str">$150.00 x</td></tr>
						</tbody>
					</table>							
				</div>
				<div class="p-content">
					<p class="normal align-center payment-description1">Once your payment information is submitted, we will immediately begin working on your behalf to get you approved for your requested medications through the applicable patient assistance program. In the event we are able to provide access to <span class="ct_med">all 3 of</span> your medication<span class="ct_med_s">s</span>, <u>your total monthly service fee will be <span class="total_str">$150.00</span></u>.
					</p>
					<p class="normal align-center">Rest assured, we will not charge your card until we verify we can access one or more of your medications. If we can access one or more of your medications, your card will be charged $50.00 for each medication that you are approved for as soon as we begin working on your case.</p>
					<p class="normal align-center">*Please note we are working diligently to complete your medication order, however, it may take up to 6 weeks to receive your first supply of medication. During this time, we will be collecting the information we need from your healthcare provider and any additional information we may need from you to process your order. Please make sure you have enough medication to get you through this initial process.</p>
				</div>
			</div>
		</div>
		<div class="p-gatway ">
			<input autocomplete="nope" type="hidden" data-type="patient" id="p_payment_method_cc" name="p_payment_method" value="cc">
			<div id="payment_cc" class=" ">
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
					<div class="full-width"><input autocomplete="nope" type="text" data-type="patient" name="p_cc_number" value="<?php echo $data['p_cc_number'];?>" placeholder="Credit Card Number *" class="" maxlength="16"></div>
				</div>
				<div>
					
					<div class="half-width">
						<input autocomplete="nope" type="hidden" name="p_cc_exp_month" value="<?php echo $data['p_cc_exp_month'];?>">
						<input autocomplete="nope" type="hidden" name="p_cc_exp_year" value="<?php echo $data['p_cc_exp_year'];?>">
						<input autocomplete="nope" type="text" data-type="patient" name="p_cc_exp_date" value="<?=($data['p_cc_exp_month']!='' && $data['p_cc_exp_month']!='') ? $data['p_cc_exp_month'] . '/' . substr($data['p_cc_exp_year'], 2, 2) : ''?>" maxlength="5" class="" placeholder="MM/YY *">
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
				<div class="full-width align-left onlyMobile"><a href="#" data-tooltip="images/cvv4.jpg" class="skipLeave disable-click">What is this?</a><br><br><br></div>
			</div>
			<div class="align-left card col-sm-12">
				<div class="half-width align-left"><img src="images/cc_mastercard.png"><img src="images/cc_visa.png" class="payment_images_">  <img src="images/cc_amex.png"> <img src="images/cc_discover.png"></div>
				<div class="half-width align-left noMobile"><a href="#" data-tooltip="images/cvv4.jpg" class="skipLeave disable-click">What is this?</a></div>
			</div>						
		</div>
<!--	</div>-->
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
					
<!-- <div class="term-content">
<div class="h-line">&nbsp;</div>
			<div class="col-sm-12">

				<h2 class="dblue-text no-text-transformation">Terms Of Service</h2>
				<div class="chk_bx">
					<p class="moduleSubheader terms_scroll_box align-justify">
						<span class="policy_subtitle">Service:</span> Prescription Hope, Inc. is a fee-based medication advocacy service that assists patients in enrolling in applicable pharmaceutical companies’ patient assistance programs. You hereby authorize Prescription Hope, Inc. to act on your behalf and to sign applications for patient assistance programs by hereby granting to Prescription Hope, Inc. a limited power of attorney for the specific purposes of enrolling you in patient assistance programs and any related activities to process your enrollment. You understand this authorization can be revoked at any time by you by providing a signed letter of cancellation to Prescription Hope, Inc. as described in the Fees section. You hereby authorize your healthcare provider’s office to discuss/release medical information to Prescription Hope, Inc. relating to your applications for patient assistance programs that Prescription Hope, Inc. is processing on your behalf. You understand that Prescription Hope, Inc. does not ship, prescribe, purchase, sell, handle, or dispense prescription medication of any kind. The pharmaceutical companies offer the medication through patient assistance programs at no cost. You hereby acknowledge that you are not paying for medication(s) through the Prescription Hope, Inc. service; rather you are paying for the administrative service of ordering, managing, tracking, and refilling medications received through the Prescription Hope, Inc. medication advocacy service. You also understand and acknowledge that it is each individual pharmaceutical manufacturer who makes the final decision as to whether you qualify for their patient assistance programs.
						<br><br>You understand Prescription Hope, Inc. does not guarantee your approval for patient assistance programs; it is up to each applicable drug manufacturer to make the eligibility determination. You will be provided details in writing for each of your eligible medications. The medication is shipped directly from the pharmaceutical company and is delivered either to your home or healthcare provider’s office, depending upon the manufacturer delivery guidelines. You agree that you may be contacted via telephone, cellular phone, text message or email through all numbers and/or addresses provided by you and authorize receipt of pre-recorded and/or artificial voice messages and/or use of an automated dialing service by Prescription Hope, Inc. and/or its affiliates. By signing below, you further agree to release Prescription Hope, Inc., its agents, employees, successors and assigns from any and all liability including legal fees and costs arising from medication(s) taken by you which were procured through the Prescription Hope, Inc. medication advocacy service and/or your reliance upon the program in general. You further agree to indemnify and hold Prescription Hope, Inc., its agents, employees, successor and assigns harmless against any and all damages including legal fees and costs arising from third persons ingesting any medication procured for you through Prescription Hope, Inc. Medications covered are subject to change at any time. Prescription Hope, Inc. reserves the right to rescind, revoke, or amend its services at any time.
					</p>
				</div>
				<div class="right-padding-35 bottom-padding-15 align-left fancy-cb">
					<label for="p_service_agreement" class="cb-container big">By clicking here I state I have read and agreed with the above statements <span class="red">*</span>
						<input autocomplete="nope" type="checkbox" data-type="patient" id="p_service_agreement"  name="p_service_agreement" value="1" <?php echo (($data['p_service_agreement'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal LoNotSensitive">
						<span class="cb-checkmark"></span>
					</label>
				</div>
				<div class="chkbox-error-blk"><label for="p_service_agreement" class="error"></label></div>
				
				<div class="chk_bx">
					<p class="moduleSubheader terms_scroll_box align-justify">
						<span class="policy_subtitle">Guarantee:</span> If you do not receive medication because you were determined to be ineligible for a patient assistance program and you have a letter of denial by the applicable pharmaceutical manufacturer, Prescription Hope, Inc. will refund the monthly administrative service fee for the medication determined to be ineligible. All Prescription Hope, Inc. will need from you is a copy of the denial letter sent to you from the applicable drug manufacturer explaining why you are ineligible.
						<br><br>
						<span class="policy_subtitle">Privacy:</span> We value our patients and make extreme efforts to protect the privacy of our patients’ personal information. Patient information is processed for order fulfillment only and for no other purpose. Patient information, including all patient health information and personal information, will never be disclosed to any third party under any circumstances. All information given to Prescription Hope, Inc., its agents, employees, successors and assigns (collectively, “Prescription Hope, Inc.”) will be held in the strictest confidence.
					</p>
				</div>
				<div class="right-padding-35 bottom-padding-15 align-left fancy-cb">
					<label for="p_guaranty_agreement" class="cb-container big">By clicking here I state I have read and agreed with the above statements <span class="red">*</span>
						<input autocomplete="nope" type="checkbox" data-type="patient" id="p_guaranty_agreement"  name="p_guaranty_agreement" value="1" <?php echo (($data['p_guaranty_agreement'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal LoNotSensitive">
						<span class="cb-checkmark"></span>
					</label>
				</div>
				<div class="chkbox-error-blk"><label for="p_guaranty_agreement" class="error"></label></div>
				
				<div class="chk_bx">
					<p class="moduleSubheader terms_scroll_box align-justify">
						<span class="policy_subtitle">Fees:</span> Prescription Hope, Inc. charges a service fee of $50.00 per month for each medication. The monthly service fee covers 100% of the medication cost, as well as the services provided by Prescription Hope, Inc. There are no additional costs for the medication(s). If we find that we are unable to access at least one of your medication(s) during the initial enrollment process, there will be no charges to your account. If we can access your medication, the initial service fee will be debited immediately so we can begin processing the paperwork required to order each eligible medication. The initial processing of your medication order(s) ranges from an average of 4 to 6 weeks and is contingent upon prompt responses to information that we request from you and your healthcare provider(s).  Prescription Hope, Inc. will process your monthly service fee on the same day each month corresponding to your enrollment date. This monthly transaction will appear on your statement as “PRESCRIPTION HOPE”. You also agree to pay any associated fees should your EFT (electronic fund transfer) be returned unpaid by your financial institution. Due to the servicebased nature of Prescription Hope, Inc., there are no refunds other than what is explained in the Prescription Hope, Inc. Guarantee above.   
						<br><br>
						<span class="policy_subtitle">Eligibility:</span> You are experiencing a hardship with affording your medication and/or you currently do not have coverage that reimburses or pays for your prescription medications. You affirm that the information provided on this form is complete and accurate. If you determine the information was not correct at the time you provided it to Prescription Hope, Inc., or if the information was accurate but is no longer accurate, you will immediately notify Prescription Hope, Inc.
					</p>
				</div>
				
				<div class="right-padding-35 bottom-padding-15 align-left fancy-cb">
					<label for="p_payment_agreement" class="cb-container big">By clicking here I state I have read and agreed with the above statements <span class="red">*</span>
						<input autocomplete="nope" type="checkbox" data-type="patient" id="p_payment_agreement" name="p_payment_agreement" value="1" <?php echo (($data['p_payment_agreement'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal LoNotSensitive">
						<span class="cb-checkmark"></span>
					</label>
				</div>
				<div class="chkbox-error-blk"><label for="p_payment_agreement" class="error"></label></div>
			</div>

			<div class="submit-form">
				<div class="btn-en-group col-sm-8 col-sm-offset-2">
					<input autocomplete="nope" type="hidden" name="bSubmit" value="Submit" />	
					<input autocomplete="nope" type="button" name="bSubmit_btn" id="bSubmit" value="Submit" class="small-button-orange">			
				</div>
				<input type="hidden" id="removepb" value="" />				
				<div><div id="ttt" style="color: #fff;"></div></div>
			</div>
		</div> -->
	</div>
	
</div>
		</div>
	
		
			</form>
				<?php }?>

				<p class="">
					<?php if ($form_submitted) { 
						header('Location: success.php');
						exit();
					 } ?>
				</p>
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
	jQuery(document).ready(function() {
		var listItems = $("#myTabsMedication li a:not(:last-child)");
		listItems.each(function(idx, li) {
			var aText = $(li).text(); 
			var aTagLength = $(li).text().length; 
			if(aTagLength > 6) {
				var res = truncateMedicationName(aText, 8);
			   // console.log('my tabs medicationL :',res);
			    $(this).text(res);
			}
		});

		var listItems = $("#myTabs li a:not(:last-child)");
		listItems.each(function(idx, li) {
			//console.log('asdaldadaldlaldl adad',$(li).find('a').text().length);
			var aText = $(li).text(); 
			var aTagLength = $(li).text().length; 
			if(aTagLength > 6) {
				var res = truncateMedicationName(aText, 8);
				//console.log('my tabs :',res);
				$(this).text(res);
			}
		});

		//submit confirmation
		jQuery('#btConfirmSubmit').click(confirmEnrollmentSubmit);
		jQuery('#btCancelSubmit').click(cancelEnrollmentSubmit);
		jQuery('#btResetForm').click(resetEnrollmentForm);
		jQuery('#btHideSubmitConfrmation').click(hideEnrollmentSubmitConfirmation);

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

// 		jQuery('input,select,textarea').change(function (e) {
//			//dataEntered = true
// 		});

 		//
		jQuery('input[type="text"],input[type="password"]').jvFloat();

		//detect CC type
		jQuery('input[name="p_cc_number"]').keyup(updateCardType);
		jQuery('input[name="p_cc_number"]').trigger('keyup');

		//show tooltips icons
		//jQuery("input.tooltipicon").each(showTooltipsIcons);
		
		//show tooltips text
		jQuery("input.tooltiptext").each(showTooltipsText);
		jQuery(".ttip").each(showTooltipsText);
		
		// dob validation
		if(jQuery('#p_dob').val()!=''){
			validate_age();
		}

		//
 		jQuery('#p_hear_about').change(updateHearAboutExtras);
		if (jQuery('#p_hear_about').is("select") && (jQuery('#p_hear_about').val() != '' || jQuery('#p_hear_about').data('value') != '')) {
			jQuery('#p_hear_about').val(jQuery('#p_hear_about').data('value'));
			jQuery('#p_hear_about').trigger('change');
		}

		//jQuery('#register_form input,#register_form select').each(function (index, elem) {
		//	//elemSelector = jQuery(elem).prop('nodeName') + '[name="' + jQuery(elem).attr('name') + '"]';
		//
		//	if (jQuery(elem).is(':visible') && form_validator.check(elem) && jQuery(elem).val() != '' && jQuery(elem).val() != 0) {
		//		/*
		//		if (jQuery(elem).prop('nodeName') == 'SELECT' || (jQuery(elem).prop('nodeName') == 'INPUT' && jQuery(elem).attr('type') != 'checkbox' && jQuery(elem).attr('type') != 'radio')) {
		//			jQuery(elem).removeClass('correct');
		//			jQuery(elem).addClass('correct');
		//		}
		//
		//		if (jQuery(elem).prop('nodeName') == 'INPUT') {
		//			switch (jQuery(element).attr('type')) {
		//				case 'checkbox':
		//					break;
		//
		//				case 'radio':
		//					break;
		//			}
		//		}
		//		*/
		//		jQueryValidation_Unhighlight(elem);
		//	}
		//
		//	refreshRadioGroupsValidationIcons();
		//});
		
		jQuery(document).on('focusout','.med_name', showMedicationList);
		
		if( jQuery("input[name='medication_name[1]']").val()!=''){
			jQuery('.med_name').each(function(){
				jQuery(this).trigger('focusout');
			});
		}
		

		
		jQuery(document).on('click tap', '.remove_block', function(){
			var block_id = (jQuery(this).attr('id')).replace('remove_provider_','');
			var medsPrevParent 	= jQuery("a[href=#"+block_id+"]").parent('li').prev('li').find('a').attr('href');
			jQuery('#'+block_id).remove();
			var get_block_id = '';
			if(block_id.indexOf('pb') != -1){			    
				get_block_id = (block_id).replace('pb','');	
			} else {
				get_block_id = (block_id).replace('mb','');	
			}
			console.log('get_block_id', get_block_id);	

			//Code added by vinod to remove tab
			var currentLi = jQuery("a[href=#"+block_id+"]").parent('li'); 
			var decrement = get_block_id-1;
			
			console.log('decrement :',decrement);
			//Remove br tag if less than 4 tabs
			if (decrement <= 3) {
				jQuery("a[href=#pb"+get_block_id+"]").parent('li').next('br').remove(); //For Healthcare provider information
			}			
			//Remove br tag if less than 4 tabs	
			//currentLi.prevUntil('li').addClass('active');
			var indexInfo =  currentLi.index()
			currentLi.remove(); //Remove respective tab
			
			//Code added by vinod to remove tab
			jQuery('#dr_err').remove();
			jQuery('#med_err').remove();
			
			if(block_id.indexOf("pb") != -1){
				ReorderDocSpecifiers();
				console.log('COunt pb :',$('ul#myTabs li').length);
				if($('ul#myTabs li').length == 2) {
					jQuery("#pb1").addClass('active').removeClass('fade');
					jQuery("a[href=#pb1]").parent('li').addClass('active'); 
				} else {
					jQuery("#pb"+decrement).addClass('active').removeClass('fade');
					jQuery("a[href=#pb"+decrement+"]").parent('li').addClass('active'); 
				}
				jQuery('#removepb').val('1');
				updateMedicationDoctorsDropdown();
				jQuery('.doctors_dropdown').each(function(){ jQuery(this).trigger('change'); });
			}
			
			if(block_id.indexOf("mb") != -1){
				//ReorderMedSpecifiers();
				jQuery('#'+block_id).prev().addClass('active');
				jQuery('a[href=#'+block_id+']').prev().addClass('active');
				var num = block_id.replace('mb','');

				var decrement_tabs = num-1;
				jQuery('#myTabsMedication li').removeClass('active');
				console.log('Counts : ',$('ul#myTabsMedication li').length)
				if($('ul#myTabsMedication li').length == 2) {
					jQuery("#mb1").addClass('active').removeClass('fade');
					jQuery("a[href=#mb1]").parent('li').addClass('active'); 
				} else {
					jQuery(medsPrevParent).addClass('active').removeClass('fade');
					jQuery("a[href="+medsPrevParent+"]").parent('li').addClass('active');  
				}
				if (decrement_tabs <= 3) {
					jQuery("a[href=#mb"+decrement_tabs+"]").parent('li').next('br').remove(); //For Medication information
				}	


				jQuery('#tr'+num).remove();
				var ct_med = '';
				var ct_med_s = '';
				var ct_med_txt = ''; 
				if(jQuery('.m-list').length>1){
					ct_med = 'all <strong>'+jQuery('.m-list').length+'</strong> of';
					ct_med_s = 's';
					ct_med_txt = jQuery('.m-list').length+' medications';
				}
				else if(jQuery('.m-list').length==1){
					ct_med_txt = '1 medication';
				}
				//Get updated meds price
				var med_price = jQuery('#patient_updated_price').val();
				jQuery('.total_str').html('$'+(jQuery('.m-list').length)*med_price+'.00');
				jQuery('.ct_med').html(ct_med);
				jQuery('.ct_med_s').html(ct_med_s);
				jQuery('#med_info .ct_med_txt').html(ct_med_txt); 
			}		
		});		
		


		// if( !jQuery('#register_form').valid()){ 
		// 	jQuery("#final-submit").hide();
		// } else {
		// 	jQuery("#final-submit").show();

		// }			
		
		jQuery('a.submit-opt').click(function(){
			jQuery('html, body').animate({
				scrollTop: (jQuery('#bSubmit').first().offset().top-parseInt(200))
			},500);
		});
		
		jQuery('a.next-opt').click(function(){
			var formValid = jQuery('#register_form').valid();			
			if(formValid){
				//alert('All mandatory fields are filled up, please submit the form to continue the process.');
				jQuery('.next-opt').hide();
				jQuery('.submit-opt').css('top', jQuery('.next-opt').css('top'));
				jQuery('.submit-opt').show();				
				return false;
			}
			
			// check for SSN duplicacy
			checkSSN();
			//alert('here');
			// check for doctor phone and address
			jQuery('.dr-form input').each(function(){				
				if( ( jQuery(this).hasClass('dr-phone') && jQuery(this).val()!='' && jQuery(this).val()==jQuery('input[name="p_phone"]').val() ) ||
				   ( jQuery(this).hasClass('dr-address') && jQuery(this).val()!='' && jQuery(this).val().toLowerCase()==jQuery('input[name="p_address"]').val().toLowerCase() )
				) {
					if(jQuery(this).siblings('label.error').length==0){
						var txt = (jQuery(this).hasClass('dr-phone')) ? 'Your phone number cannot match the healthcare provider\'s phone number. Please enter a valid phone.' : 'Your address cannot match the healthcare provider\'s address. Please enter a valid address.';
						errorLabel = jQuery('<label>').attr('for', jQuery(this).attr('name')).addClass('error invalid-field').removeClass('correct').text(txt);
						errorLabel.insertAfter(jQuery(this));
						jQuery(this).removeClass('correct').addClass('error');
					}					
				}
			});			
			var found = false;
			jQuery('#register_form input, #register_form select, #register_form radio, #register_form checkbox').each(function(){ 
				if(/*jQuery(this).val()=='' &&*/ jQuery(this).hasClass('error')){ 
					found = true;
					var err_pos = jQuery('.error').first().offset().top-parseInt(200);
					if(jQuery(window).width()<=450){
						jQuery('.next-opt').css('top', parseInt(err_pos-60)+'px');
					}
					else if(jQuery(window).width()<=768){
						jQuery('.next-opt').css('top', parseInt(err_pos-460)+'px');
					}
					else{
						jQuery('.next-opt').css('top', parseInt(err_pos)+'px');
					}					
					jQuery('html, body').animate({
						scrollTop: err_pos
					},500);
					return false;
				}				
			});
			
			if(!found){ 
				jQuery('html, body').animate({
					scrollTop: (jQuery('.checkbox-with-label-error2').first().offset().top)
				},500);
				return false;
			}
				
		});
		
		// remove check mark from the empty value
		jQuery('input').keyup(function(){
			if(jQuery(this).val()==''){
				jQuery(this).removeClass('correct');
			}
		});
							
		<?php if ($invalid_cc) {
			 ?>InvalidCreditCard();
		<?php } ?>

	});
	
	// function yesnoCheck() {
	//     if (document.getElementById('p_medicare_part_d_yes').checked) {
	//         document.getElementById('ifYes').style.display = 'block';
	//     }
	//     else document.getElementById('ifYes').style.display = 'none';

	// }

	// Remove navigation prompt
	//window.onbeforeunload = function(e) {
</script>
<?php if(isset($_GET['debug']) && $_GET['debug']==1) { ?>
<script src="https://player.vimeo.com/api/player.js"></script>
<script type="text/javascript">
/***** vimeo player script (starts) *****/
var iframe = jQuery('#vimeo_video')[0];
var player = new Vimeo.Player(iframe);

var inner = jQuery(".video-panel");
var elementPosTop = inner.position().top;
var viewportHeight = jQuery(window).height();
jQuery(window).on('scroll', function () {
	var scrollPos = jQuery(window).scrollTop();
	// window scroll top > video block bottom then pause
	if( scrollPos > (elementPosTop + viewportHeight) ){
		console.log("Pause it now");
		//player.api("pause");
		player.pause().then(function() {
			console.log('The video is paused');
		}).catch(function(error) {
			switch (error.name) {
			  case 'PasswordError':
				  alert('Error Occured : PasswordError');
				  break;
			
			  case 'PrivacyError':
				  alert('Error Occured : PrivacyError');
				  break;
			
			  default:
				  alert('Error Occured : '+error.name);
				  break;
			}
		});
	}
	else{
		console.log("Let it play");
	}
});
/***** vimeo player script (ends) *****/
</script>
<?php } ?>

<!-- partial:index.partial.html -->
<?php /*<div  class="col-sm-12 row">
	<div class="col-xs-3 sidebar-tabs">
		<div class="logo">  
			<a href="/" class="svg">
        		<object data="/images/ph-logo.svg" type="image/svg+xml" width="241" height="90" class="top-menu-only-desktop only-desktop" style="margin-top: 20px;"><img src="/html/enrollment/images/prescription-hope-logo.png" class="header-logo left top-menu-only-desktop only-desktop"></object>
  		    </a>
  		</div>
         <!-- required for floating -->
          <!-- Nav tabs -->
		  <h2 class="title-section">Enrollment Progress</h2>
	          <ul class="nav nav-tabs tabs-left sideways">
	            <li class="active"><a href="#home-v" data-toggle="tab"><span class="index-span">1</span> <span class="glyphicon glyphicon-ok"></span> Patient Address</a></li>
	            <li><a href="#profile-v" data-toggle="tab"><span class="index-span">2</span> <span class="glyphicon glyphicon-ok"></span> Patient Information</a></li>
	            <li><a href="#messages-v" data-toggle="tab"><span class="index-span">3</span> <span class="glyphicon glyphicon-ok"></span> Monthly Household Income Information</a></li>
	            <li><a href="#settings-r" data-toggle="tab"><span class="index-span">4</span> <span class="glyphicon glyphicon-ok"></span> Healthcare Provider Information</a></li>

	            <li><a href="#settings-s" data-toggle="tab"><span class="index-span">5</span> <span class="glyphicon glyphicon-ok"></span> Medication Information</a></li>
	            <li><a href="#settings-t" data-toggle="tab"><span class="index-span">6</span> <span class="glyphicon glyphicon-ok"></span>  Payment Information</a></li>
	            <li><button class="btn btn-primary">Submit</button></li>
	          </ul>
        </div>

        <div class="col-xs-9 tab-content">
          <!-- Tab panes -->
		
		 
		   </div>
        <div class="tab-content">
		 
            <div class="tab-pane active" id="home-v">
			<div class="tab-1">
			  <div class="col-sm-4"> 
			  		<div class="heading-title"> 
			 		 <h2>Patient Information</h2>
		   			<p>We mail a welcome packet and other important 
					information to you throughout your enrollment with 
					Prescription Hope. The pharmaceutical manufacturers that ship your medication may deliver your 
					medication to this mailing address.</p>
					<p><strong>NOTE:</strong> Some medications will get delivered to your healthcare provider's office.</p>
		  			</div>
		  		</div>
			 <div class="col-sm-8"> <form>
			  <div class="form-group">
			  	<input autocomplete="nope" type="text" data-type="patient" name="p_address" id="p_address" value="<?php echo htmlspecialchars(stripslashes($data['p_address']));?>" class="LoNotSensitive" placeholder="Street Address *">
			  	<!-- <input autocomplete="nope" type="text" data-type="patient" name="p_address" id="p_address" value="<?php echo htmlspecialchars(stripslashes($data['p_address']));?>" class="LoNotSensitive form-control" aria-describedby="p_address" placeholder="Street Address*"> -->			    
			    <small  class="form-text text-muted">*The pharmaceutical manufacturers that supply your medication require a physical street address. 
			  No P.O. Boxes.</small>
			  </div>
			  <div class="form-group">
			  	<input autocomplete="nope" type="text" data-type="patient" name="p_address2" value="<?php echo htmlspecialchars(stripslashes($data['p_address2']));?>" class="LoNotSensitive" placeholder="Apartment, Suite, Unit, etc.">
			  	<!-- <input autocomplete="nope" type="text" data-type="patient" name="p_address2" value="<?php echo htmlspecialchars(stripslashes($data['p_address2']));?>" class="LoNotSensitive form-control" placeholder="Apartment, Suite, Unit, etc."> -->
			  </div>
		   <div class="col-sm-4 w-100"> 
		   		<div class="form-group">
		   			<input autocomplete="nope" type="text" data-type="patient" name="p_city" value="<?php echo htmlspecialchars(stripslashes($data['p_city']));?>" class="LoNotSensitive form-control" placeholder="City *">
		  		</div>
		  </div>
		   <div class="col-sm-4 w-100"> 
			   	<div class="form-group">
			   		<select data-type="patient" name="p_state" id="p_state" preload="<?php echo $data['p_state'];?>" class="form-control full-width LoNotSensitive" placeholder="State *">
								<option value="">State *</option>
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
								<option value="PR">Puerto Rico</option>
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
			    	<!-- <input type="text" class="form-control" id="exampleInputName" placeholder="State*"> -->
			  </div>
		  </div>
	      <div class="col-sm-4 w-100 right-layout"> 
      		<div class="form-group">
      			<input autocomplete="nope" type="text" data-type="patient" name="p_zip" value="<?php echo $data['p_zip'];?>" maxlength="5" class="LoNotSensitive form-control" placeholder="ZIP Code *">
  			</div>
	  </div>
  		<button type="submit" class="btn btn-primary btn-block">NEXT</button>
</form>



			
			
			
			</div></div></div>
            <div class="tab-pane" id="profile-v">
			
					<div class="tab-1">
			  <div class="col-sm-4"> <div class="heading-title"> 
		  <h2>Patient Information</h2>
		   <p>The pharmaceutical manufacturers require all the information requested to process your medication orders. All of your information will be kept confidential and protected.</p>

		  </div></div>
			 <div class="col-sm-8"> 
			 	<form>
	              <div class="form-group">
	                    <div class="maxl">
						<label for="p_is_minor" class="forlabel">Is this enrollment form on behalf of a minor?*</label>
	                        <label for="p_is_minor_yes" class="radio inline rb-container"> 
	                            <input autocomplete="nope" type="radio" data-type="patient" id="p_is_minor_yes" name="p_is_minor" value="1" class="LoNotSensitive" <?php echo ((in_array('is_minor', $radios_submitted) && $data['p_is_minor'] != '') ? 'preload="' . (int)$data['p_is_minor'] . '"' : ''); ?>>	
	                            <span> Yes </span> 
	                        </label>
	                        <label for="p_is_minor_no" class="radio inline rb-container"> 
	                        	<input autocomplete="nope" type="radio" data-type="patient" id="p_is_minor_no" name="p_is_minor" value="0" class="LoNotSensitive">
	                            <span>No </span> 
	                        </label>
	                    </div>
	                </div>
				   <div class="form-group">
	                    <div class="maxl">
							<label for="p_gender" class="forlabel">Gender*</label>
	                        <label for="p_gender_m" class="rb-container radio inline"> 
	                            <input autocomplete="nope" type="radio" data-type="patient" id="p_gender_m" name="p_gender" value="M" class="LoNotSensitive" preload="<?php echo $data['p_gender'];?>">
	                            <span> Male </span> 
	                        </label>
	                        <label for="p_gender_f" class="rb-container radio inline"> 
	                            <input autocomplete="nope" type="radio" data-type="patient" id="p_gender_f" name="p_gender" value="F" class="LoNotSensitive">
	                            <span>Female </span> 
	                        </label>
	                    </div>
                	</div>
					<div class="form-group">
					    <input autocomplete="nope" type="text" data-type="patient" id="p_dob" aria-describedby="date" name="p_dob" value="<?php echo $data['p_dob'];?>" class="LoNotSensitive tooltiptext form-control" placeholder="Date of Birth (mm/dd/yyyy) *" data-text="Why Is My Date Of Birth Needed?" data-hint="Your Date of Birth is required by the pharmaceutical company to process your medication orders.">

					    <small id="date-error">Why is my date of birth required?</small>
  					</div>
  					<div class="form-group">
					    
  						<?php if(isset($response->success) && $response->success==2){ ?>
						<input autocomplete="nope" type="text" data-type="patient" name="p_ssn" aria-describedby="date" class="form-control" value="<?php echo $_POST['p_ssn'];?>" maxlength="11" class="LoNotSensitive tooltiptext" placeholder="Social Security Number *" data-text="Why Is My Social Security Number Needed?" data-hint="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology.">
						<?php } else { ?>
						<input autocomplete="nope" type="text" data-type="patient" name="p_ssn" aria-describedby="date" class="form-control" value="<?php echo $data['p_ssn'];?>" maxlength="11" class="LoNotSensitive tooltiptext" placeholder="Social Security Number *" data-text="Why Is My Social Security Number Needed?" data-hint="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology.">
						<?php } ?>
					    <small id="date-error">Why is my social security number required?</small>
  					</div>
				   <div class="col-sm-6 w-100 p-l-0"> 
				   		<div class="form-group">
				    		<input autocomplete="nope" type="text" data-type="patient" name="p_alternate_contact_name" value="<?php echo htmlspecialchars(stripslashes($data['p_alternate_contact_name']));?>" class="LoNotSensitive form-control" placeholder="Alternate Contact Name">
				  		</div>
				  </div>
     			  <div class="col-sm-6 w-100 p-r-0"> 
	     			  	<div class="form-group">
				    		<input autocomplete="nope" type="text" data-type="patient" name="p_alternate_phone" value="<?php echo $data['p_alternate_phone'];?>" class="LoNotSensitive not_required_phone form-control" placeholder="Alternate Contact Phone">
				  		</div>
  					</div>
				  <div class="form-group">
				  	<?php if(!empty($data['p_application_source']) && count($broker_array) == 2){?>
							<!-- <select data-type="patient" name="p_hear_about" id="p_hear_about" data-value="<?php echo htmlspecialchars(stripslashes($data['p_application_source']));?>" class="full-width LoNotSensitive form-control" placeholder="How did you hear about Prescription Hope? *" <?php if(!empty($data['p_application_source']) && count($broker_array) == 2){ echo "disabled";}?>> -->
							<input class="form-control" autocomplete="nope" type="text" data-type="patient" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes(trim(substr($data['p_application_source'],9))));?>" class="full-width" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>>
						<?php }else{?>
							<select class="form-control" data-type="patient" name="p_hear_about" id="p_hear_about" data-value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about']));?>" class="full-width LoNotSensitive form-control" placeholder="How did you hear about Prescription Hope? *">
								<option value="">How did you hear about Prescription Hope? *</option>
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
								<option value="Linkedin">Linkedin</option>
								<option value="Twitter">Twitter</option>
								<option value="WPBF25 Health and Safety with Dr. Oz">WPBF25 Health and Safety with Dr. Oz</option>
								<option value="Other">Other</option>
							</select>
						<?php }?>
				  </div>
  
  				<button type="submit" class="btn btn-primary btn-block">NEXT</button>
			</form>



			
			
			
			</div></div>
			
			</div>
            <div class="tab-pane" id="messages-v">
			<div class="tab-1">
			  <div class="col-sm-4"> <div class="heading-title"> 
		  <h2>Monthly Household
Income Information</h2>
		   <p>The pharmaceYour monthly household income information is 
required by the pharmaceutical manufacturers to process your medication orders. It will not be used for any other purposes and will remain confidential.</p>

		  </div>
		</div>
			 <div class="col-sm-8"> <form>

              <div class="form-group">
                    <div class="maxl">
						<label for="p_has_income" class="forlabel">Do you currently have income?</label>
                        <label for="p_has_income_yes" class="rb-container radio inline"> 
                        	<input autocomplete="nope" type="radio" data-type="patient" id="p_has_income_yes" name="p_has_income" value="1" class="LoNotSensitive" <?php echo ((in_array('has_income', $radios_submitted) && $data['p_has_income'] != '') ? 'preload="' . (int)$data['p_has_income'] . '"' : ''); ?>>
                            <!-- <input type="radio" name="gender" value="male" checked> -->
                            <span> Yes </span> 
                        </label>
                        <label for="p_has_income_no" class="rb-container radio inline"> 
                            <input autocomplete="nope" type="radio" data-type="patient" id="p_has_income_no" name="p_has_income" value="0" class="LoNotSensitive">
                            <span>No </span> 
                        </label>
                    </div>
                </div>
									
		     <div class="form-group">
                <div class="maxl">
				<label for="p_income_file_tax_return" class="forlabel">Do you file taxes?</label>
                    <label for="p_income_file_tax_return_yes" class="radio inline"> 
                        <input autocomplete="nope" type="radio" data-type="patient" id="p_income_file_tax_return_yes" name="p_income_file_tax_return" value="1" class="LoNotSensitive" <?php echo ((in_array('file_tax_return', $radios_submitted) && $data['p_income_file_tax_return'] != '') ? 'preload="' . (int)$data['p_income_file_tax_return'] . '"' : ''); ?>>
                        <span> Yes </span> 
                    </label>
                    <label for="p_income_file_tax_return_no" class="radio inline"> 
                        <input autocomplete="nope" type="radio" data-type="patient" id="p_income_file_tax_return_no" name="p_income_file_tax_return" value="0" class="LoNotSensitive">
                        <span>No </span> 
                    </label>
                </div>
            </div>
     		<div class="form-group">
	            <div class="maxl">
				<label for="p_uscitizen" class="forlabel">Are you a US Citizen?*</label>
	                <label for="p_uscitizen_yes" class="radio inline"> 
	                    <input autocomplete="nope" type="radio" data-type="patient" id="p_uscitizen_yes" name="p_uscitizen" value="1" class="LoNotSensitive" <?php echo ((in_array('us_citizen', $radios_submitted) && $data['p_uscitizen'] != '') ? 'preload="' . (int)$data['p_uscitizen'] . '"' : ''); ?>>
	                    <span> Yes </span> 
	                </label>
	                <label for="p_uscitizen_no" class="radio inline"> 
	                    <input autocomplete="nope" type="radio" data-type="patient" id="p_uscitizen_no" name="p_uscitizen" value="0" class="LoNotSensitive">
	                    <span>No </span> 
	                </label>
	            </div>
            </div>											

			  <div class="form-group">
			   <label for="p_married">Are you married?</label>
				   <select data-type="patient" name="p_married" id="p_married" preload="<?php echo $data['p_married'];?>" class="form-control">
						<option value=''>Select ...</option>
						<option value='S'>Single</option>
						<option value='M'>Married</option>
						<option value='D'>Separated</option>
						<option value='W'>Widowed</option>
					</select>			    
			  </div>
  
			  <div class="form-group">
			   <label for="p_household">How Did You Hear About Prescription Hope?*</label>
				  <select data-type="patient" name="p_household" id="p_household" preload="<?php echo $data['p_household'];?>" class="full-width no-float-label LoNotSensitive">
						<option value=''>Select ...</option>
							<?php for ($i = 1; $i < 11; $i++) { ?>
								<option value='<?=$i?>'><?=$i?></option>
							<?php } ?>
					</select>			    
			  </div>  
  			<button type="submit" class="btn btn-primary btn-block">NEXT</button>
			</form>
		</div>
	</div>
</div>
<div class="tab-pane" id="settings-r">			
	<div class="tab-1">
	  <div class="col-sm-4"> 
	  	<div class="heading-title"> 
	  		<h2>Healthcare Provider Information</h2>
	   		<p>Only list the healthcare providers who are prescribing 
	medications you are requesting through Prescription Hope. If this information is not correct, your first 
	medication delivery will be delayed.</p>

  		</div>
	</div>
	<div class="col-sm-8">
		<div class="tab-container">
			<ul id="myTabs" class="tab-section-tab nav nav-pills nav-justified" role="tablist" data-tabs="tabs">
				<?php $valid_drs = 0; ?>
						<?php foreach ($data['doctors'] as $dr_key => $doctor) { ?>
							<?php if ((count($data['doctors']) > 1 && $doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') || count($data['doctors']) == 1) { ?>
								<?php $valid_drs++; ?>
								<?php 
									if($valid_drs == 1) {
										$addClass = "active";
									} else {
										$addClass = "";
									}

								?>
								    <li class="<?php echo $addClass; ?>"><a href="#pb<?=($valid_drs)?>" data-toggle="tab"><?php echo $doctor['doctor_first_name']; ?></a></li>
								   
								    <!-- <li><a href="#Videos" data-toggle="tab">Dr. Bryan Hildreth</a></li> -->
									<?php } ?>
								<?php } ?>    
							    <li><a href="#Events" data-toggle="tab"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
							  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
							</svg></a></li>
			  </ul>
			  <div class="tab-content" old-id="new_doctor_form" id="doctors_forms_list">
					<!-- <div> -->
								<?php $valid_drs = 0; ?>
								<?php foreach ($data['doctors'] as $dr_key => $doctor) { ?>
									<?php if ((count($data['doctors']) > 1 && $doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') || count($data['doctors']) == 1) { ?>
										<?php $valid_drs++;
											if($valid_drs == 1) {
												$addClass = "active";
											} else {
												$addClass = "";
											}
										 ?>
										<div old-id="doctor_form" role="tabpanel" class="dr-form tab-pane fade in <?php echo $addClass; ?>" id="pb<?=($valid_drs)?>">
											<!-- <div old-id="doctor_form" class="dr-form" id=""> -->
												<!-- <input class="doctor_id-<?=$valid_drs?>" autocomplete="nope" type="hidden" name="doctor_id[<?=$valid_drs?>]" id="doctor_id" value="<?=$doctor['doctor_id']?>">
												<input class="doctor_check-<?=$valid_drs?>" autocomplete="nope" type="hidden" value="<?=$doctor['doctor_first_name']?><?=$doctor['doctor_last_name']?><?=$doctor['doctor_facility']?><?=$doctor['doctor_address']?><?=$doctor['doctor_address2']?><?=$doctor['doctor_city']?><?=$doctor['doctor_state']?><?=$doctor['doctor_zip']?><?=$doctor['doctor_phone']?><?=$doctor['doctor_fax']?>"> -->
												<?php if($valid_drs>1) { ?>
												<div class="remove-div-1"><a href="javascript:void(0);" class="remove_block" id="remove_provider_pb<?=($valid_drs)?>">X Remove Provider</a></div>
												<?php } ?>
												<div class="doctor-no-field p20-width align-left bold text-center w100">Healthcare Provider <?=($valid_drs)?>:</div>
												<div>

													<div class="half-width doctor-fname-field"><input autocomplete="nope" type="text" name="doctor_first_name[<?=$valid_drs?>]" value="<?=$doctor['doctor_first_name']?>" class="doctor_first_name-<?=$valid_drs?> dr-required dr-data doctor-fields LoNotSensitive" placeholder="Healthcare Provider First Name *"></div>
													<div class="half-width doctor-lname-field"><input autocomplete="nope" type="text" name="doctor_last_name[<?=$valid_drs?>]" value="<?=$doctor['doctor_last_name']?>" class="doctor_last_name-<?=$valid_drs?> dr-required dr-data doctor-lname-fields LoNotSensitive" placeholder="Healthcare Provider Last Name *"></div>
												</div>

												<div>										
													<div class="half-width doctor-facility-field"><input autocomplete="nope" type="text" name="doctor_facility[<?=$valid_drs?>]" value="<?=$doctor['doctor_facility']?>" class="doctor_facility-<?=$valid_drs?> dr-data LoNotSensitive" placeholder="Facility Name"></div>
													<div class="half-width doctor-address-field"><input autocomplete="nope" type="text" name="doctor_address[<?=$valid_drs?>]" value="<?=$doctor['doctor_address']?>" class="doctor_address-<?=$valid_drs?> dr-required dr-data dr-address LoNotSensitive" placeholder="Address *" rel="Some health care providers have multiple locations they work from, please provide the address for the location you visit your health care provider at."></div>
												</div>
											
												<div>										
													<div class="half-width doctor-address2-field"><input autocomplete="nope" type="text" name="doctor_address2[<?=$valid_drs?>]" value="<?=$doctor['doctor_address2']?>" class="doctor_address2-<?=$valid_drs?> dr-data LoNotSensitive" placeholder="Suite Number"></div>
													<div class="half-width doctor-city-field"><input autocomplete="nope" type="text" name="doctor_city[<?=$valid_drs?>]" value="<?=$doctor['doctor_city']?>" class="doctor_city-<?=$valid_drs?> dr-required dr-data LoNotSensitive" placeholder="City *"></div>
												</div>									

												<div>										
													<div class="half-width doctor-state-field">
														<select name="doctor_state[<?=$valid_drs?>]" id="doctor_state[<?=$valid_drs?>]" preload="<?=$doctor['doctor_state']?>" class="doctor_state-<?=$valid_drs?> dr-required dr-data full-width LoNotSensitive" placeholder="State *">
															<option value="" selected="selected">State *</option>
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
															<option value="PR">Puerto Rico</option>
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
													<div class="half-width doctor-zip-field"><input autocomplete="nope" type="text" name="doctor_zip[<?=$valid_drs?>]" value="<?=$doctor['doctor_zip']?>" maxlength="5" class="doctor_zip-<?=$valid_drs?> dr-required dr-data dr-zip LoNotSensitive" placeholder="Zip Code *"></div>
												</div>									

												<div>										
													<div class="half-width doctor-phone-field"><input autocomplete="nope" type="text" name="doctor_phone[<?=$valid_drs?>]" value="<?=$doctor['doctor_phone']?>" class="doctor_phone-<?=$valid_drs?> dr-required dr-data dr-phone LoNotSensitive" placeholder="Phone Number *"></div>
													<div class="half-width doctor-fax-field"><input autocomplete="nope" type="text" name="doctor_fax[<?=$valid_drs?>]" value="<?=$doctor['doctor_fax']?>" class="doctor_fax-<?=$valid_drs?> dr-data dr-fax LoNotSensitive" placeholder="Fax Number"></div>
												</div>									
											<!-- </div> -->
										</div>	
									<?php 
										} ?>
								<?php } ?>
							<!-- </div> -->
						</div>	
					<div>
						<input autocomplete="nope" type="button" name="bAddANewDoctor" id="bAddANewDoctor" value="Add Another Provider" class="cancel small-button-orange button-auto-width">
					</div>  
				</div>
   <!--  <div role="tabpanel" class="tab-pane fade" id="Videos">Videos WP_Query goes here.</div>
    <div role="tabpanel" class="tab-pane fade" id="Events">Events WP_Query goes here.</div> -->
  </div>
</div>
</div>
			</div>
			            <div class="tab-pane" id="settings-s">
			
							<div class="tab-1">
			  <div class="col-sm-4"> <div class="heading-title"> 
		  <h2>Medication Information</h2>
		   <p>Please list all the medications you are requesting through Prescription Hope.</p>

		  </div></div>
			 <div class="col-sm-8">
<div class="tab-container">
  <ul id="myTabs" class="tab-section-tab nav nav-pills nav-justified" role="tablist" data-tabs="tabs">
    <li class="active"><a href="#Commentary" data-toggle="tab">Januvia</a></li>
    <li><a href="#Videos" data-toggle="tab">Xarelto</a></li>
    <li><a href="#Events" data-toggle="tab"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
</svg></a></li>
  </ul>
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane fade in active" id="Commentary">	
		<form>
		   <div class="col-sm-6 w-100 p-l-0"> <div class="form-group">
		    <input type="text" class="form-control" id="exampleInputName" placeholder="Healthcare Provider First Name*">
		  </div>
		  </div>
		     <div class="col-sm-6 w-100 p-r-0"> <div class="form-group">
		    <input type="text" class="form-control" id="exampleInputName" placeholder="Healthcare Provider Last Name*">
		  </div>
		  </div>
		  
		   <div class="col-sm-6 w-100 p-l-0"> <div class="form-group">
		    <input type="text" class="form-control" id="exampleInputName" placeholder="Suite Number*">
		  </div>
		  </div>
		   
		   <div class="col-sm-6 w-100 p-r-0"> <div class="form-group">
		    <input type="text" class="form-control" id="exampleInputName" placeholder="City*">
		  </div>
		  </div>
		  <div class="add-provider-section"><a href="#" class="provider_add"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
		  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
		</svg> <span>Add Medication</span></a></div>
		<div class="well">
			<p>You are requesting 1 medication through Prescription Hope.
		In the event you are pre-approved for all of the medications you are requesting, your monthly total will be $50.00.s</p>
		</div>
		  <button type="submit" class="btn btn-primary btn-block">NEXT</button>
		</form>

</div>
	
	</div>
    <div role="tabpanel" class="tab-pane fade" id="Videos">Videos WP_Query goes here.</div>
    <div role="tabpanel" class="tab-pane fade" id="Events">Events WP_Query goes here.</div>
  </div>
</div>
			


			
			
			
			</div>
			</div>
			  <div class="tab-pane" id="settings-t">
			
							<div class="tab-1">
			  <div class="col-sm-4"> <div class="heading-title"> 
		  <h2>Payment Information</h2>
		   <p>One your payment information is submitted, we will immediately begin working on your behalf to get you approved for your requested medications through the applicable patient assistance program . In the event you are pre-approved to enroll in patient assistance programs for 2 medication(s), your total monthly 
service fee will be $100.00.</p>
<p><strong>NOTE:</strong> We are working diligently to complete your 
medication order, however, it may take up to 6 weeks to receive your first supply of medication. During this time, we will be collecting the information we need from your healthcare provider and any additional information we may need from you to process your order. Please make sure you have enough medication to get you through this initial process.</p>
		  </div></div>
			 <div class="col-sm-8">
<div class="tab-container">
  <ul id="myTabs" class="tab-section-tab nav nav-pills nav-justified" role="tablist" data-tabs="tabs">
    <li class="active"><a href="#Commentary" data-toggle="tab">Januvia</a></li>
    <li><a href="#Videos" data-toggle="tab">Xarelto</a></li>
    <li><a href="#Events" data-toggle="tab"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
</svg></a></li>
  </ul>
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane fade in active" id="Commentary">
	
<form>


   <div class="col-sm-6 w-100 p-l-0"> <div class="form-group">
    <input type="text" class="form-control" id="exampleInputName" placeholder="Healthcare Provider First Name*">
  </div>
  </div>
     <div class="col-sm-6 w-100 p-r-0"> <div class="form-group">
    <input type="text" class="form-control" id="exampleInputName" placeholder="Healthcare Provider Last Name*">
  </div>
  </div>
  
   <div class="col-sm-6 w-100 p-l-0"> <div class="form-group">
    <input type="text" class="form-control" id="exampleInputName" placeholder="Suite Number*">
  </div>
  </div>
   
   <div class="col-sm-6 w-100 p-r-0"> <div class="form-group">
    <input type="text" class="form-control" id="exampleInputName" placeholder="City*">
  </div>
  </div>
  <div class="add-provider-section"><a href="#" class="provider_add"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
</svg> <span>Add Medication</span></a></div>
<div class="well"><p>You are requesting 1 medication through Prescription Hope.
In the event you are pre-approved for all of the medications you are requesting, your monthly total will be $50.00.</p></div>
  <button type="submit" class="btn btn-primary btn-block">NEXT</button>
</form>

</div>
	
	</div>
    <div role="tabpanel" class="tab-pane fade" id="Videos">Videos WP_Query goes here.</div>
    <div role="tabpanel" class="tab-pane fade" id="Events">Events WP_Query goes here.</div>
  </div>
</div>
			


			
			
			
			</div>
			</div>
			</div>
			</div>
          </div>
        </div>

        <div class="clearfix"></div>

      </div>
<!-- partial -->
*/?>
<!-- partial:index.partial.html -->


<div  class="col-sm-12 row d-flex menu-sidebar" id="wrapper">
	<div class="mobile-view" id="specs-mobile-view">
		<a href="#" id="menu-toggle"><span></span></a>
	</div>
    <div class="col-xs-3 sidebar-tabs bg-light border-right" id="sidebar-wrapper">
    	<!-- <div class> -->
			<a href="#" class="sidebar-closed">x</a>
	        <div class="logo">  <a href="https://prescriptionhope.com/" class="svg">
	            <object data="/images/ph-logo.svg" type="image/svg+xml" width="241" height="90" class="top-menu-only-desktop only-desktop" style="margin-top: 20px;"><img src="../enrollment/images/prescription-hope-logo.png" class="header-logo left top-menu-only-desktop only-desktop"></object>
	            </a>
	        </div>
	        <!-- required for floating -->
	        <!-- Nav tabs -->
	        <h2 class="title-section">Enrollment Progress</h2>
	        <ul class="nav nav-tabs tabs-left sideways" id="enroll-progress">
	            <li class="active" id="patient-enroll-progress_1" data-step-enroll="1"><a href="#home-v" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">1</span> <span class="glyphicon glyphicon-ok"></span> Patient Address</a></li>
	            <li id="patient-enroll-progress_2" data-step-enroll="2"><a href="#profile-v" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">2</span> <span class="glyphicon glyphicon-ok"></span> Patient Information</a></li>
	            <li id="patient-enroll-progress_3" data-step-enroll="3"><a href="#messages-v" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">3</span> <span class="glyphicon glyphicon-ok"></span> Monthly Household Income Information</a></li>
	            <li id="patient-enroll-progress_4" data-step-enroll="4">
	                <a href="#settings-r" data-toggle="tab" class="list-group-item list-group-item-action bg-light">
	                    <span class="index-span">4</span> <span class="glyphicon glyphicon-ok"></span> Healthcare Provider Information</a>
	            </li>
	            <li id="patient-enroll-progress_5" data-step-enroll="5"><a href="#settings-s" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">5</span> <span class="glyphicon glyphicon-ok"></span> Medication Information</a></li>
	            <li id="patient-enroll-progress_6" data-step-enroll="6"><a href="#settings-t" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">6</span> <span class="glyphicon glyphicon-ok"></span>  Payment Information</a></li>
	            <li id="patient-enroll-progress_7" data-step-enroll="7" style="display:none;"><a href="#settings-u" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">7</span> <span class="glyphicon glyphicon-ok"></span>  Payment Information Dummy</a></li>
	            <li class="final-submit-li"><button class="btn btn-primary" id="final-submit" type="button" data-final-submit="1" data-step-enroll="6" style="display:none;">Submit</button></li>
	        </ul>
       <!-- </div>  -->
    </div>
   
    <div class="col-xs-9 tab-content" id="page-content-wrapper">
        <!-- Tab panes -->
        <!-- Modal -->
	  <div class="modal fade hideform" id="myErrorMessage" role="dialog" data-keyboard="false" data-backdrop="static">
	    <div class="modal-dialog">
	    
	      <!-- Modal content-->
	      <div class="modal-content">
	        <div class="modal-header">
	          <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
	          <h4 class="modal-title">Form Review</h4>
	        </div>
	        <div class="modal-body">
	          <p>You may missed some required fields, please review and submit it again.</p>
	        </div>
	        <div class="modal-footer">
	          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        </div>
	      </div>
	      
	    </div>
	  </div>
   
	    <form id="register_form" method="post" action="enroll.php" autocomplete="nope">
	   		 <div class="tab-content">
		        <div class="register-form-tab tab-pane active" id="home-v">
		            <div class="tab-1">
		                <div class="col-sm-5">
		                    <div class="heading-title">
		                        <h2>Patient Mailing Address</h2>
		                        <p>We mail a welcome packet and other important 
		                            information to you throughout your enrollment with 
		                            Prescription Hope. The pharmaceutical manufacturers that ship your medication may deliver your 
		                            medication to this mailing address.
		                        </p>
								
		                        <p><strong>NOTE: </strong>There are regulations on shipping medications from the pharmaceutical manufacturer. Some pharmaceutical manufacturers will ship your medication to your healthcare provider's office.</p>
		                    </div>
		                </div>
		                <div class="col-sm-7 card-form">
		                   <!--  <form> -->
		                    <div>
								<div class="full-width form-group">
									<input type="hidden" name="p_phone" value="<?php echo $data['p_phone']; ?>">
									<input autocomplete="nope" type="text" data-type="patient" name="p_address" id="p_address" value="<?php echo htmlspecialchars(stripslashes($data['p_address']));?>" class="LoNotSensitive patient-enroll-progress-1" placeholder="Street Address *" title="Street Address *" data-step-enroll="1">
								<small class="disclaimer-content">*The pharmaceutical manufacturers that supply your medication require a physical street address. No P.O. Boxes.</small>
								</div>
								<div class="full-width form-group"><input autocomplete="nope" type="text" data-type="patient" name="p_address2" value="<?php echo htmlspecialchars(stripslashes($data['p_address2']));?>" class="LoNotSensitive" placeholder="Apartment, Suite, Unit, etc." title="Apartment, Suite, Unit, etc.">
								</div>
							</div>
							<div class="clear"></div>

						<div>
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_city" value="<?php echo htmlspecialchars(stripslashes($data['p_city']));?>" class="LoNotSensitive patient-enroll-progress-1" placeholder="City *" title="City *" data-step-enroll="1">
						</div>
						<div class="third-width">							
							<select data-type="patient" name="p_state" id="p_state" preload="<?php echo $data['p_state'];?>" class="form-control full-width LoNotSensitive patient-enroll-progress-1" placeholder="State *" title="State *" data-step-enroll="1">
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
								<option value="PR">Puerto Rico</option>
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
						<div class="third-width"><input autocomplete="nope" type="text" data-type="patient" name="p_zip" value="<?php echo $data['p_zip'];?>" maxlength="5" class="LoNotSensitive patient-enroll-progress-1" placeholder="ZIP Code *" title="ZIP Code *" data-step-enroll="1"></div>
					<div class="next-btn"><!-- <button class="btn btn-primary btn-block">NEXT</button> -->
						<a class="btn btn-primary next btn-block mv-10" href="javascript:;">Next</a>
					</div>
					</div>
					<div class="clear"></div>
						
		                </div>
		            </div>
		        </div>
		        <div class="register-form-tab tab-pane" id="profile-v">
		            <div class="tab-1">
		                <div class="col-sm-5">
		                    <div class="heading-title">
		                        <h2>Patient Information</h2>
		                        <p>The pharmaceutical manufacturers require all the information requested to process your medication orders. All of your information will be kept confidential and protected.</p>
		                    </div>
		                </div>
		                <div class="col-sm-7 card-form">
		                    <!-- <form> -->
		                        <div class="form-group radio-group">
		                            <div class="maxl">
		                                <div class="full-width align-left">
											<label for="p_is_minor">Is this application on behalf of a minor? <span class="red">*</span></label>
										</div>
										<div class="full-width align-left">
											<input type="hidden" data-f-count="2" data-fl_1="p_is_minor_yes" data-fl_2="p_is_minor_no" class="patient-enroll-progress-2" data-step-enroll="2">
											<label for="p_is_minor_yes" class='rb-container no_width'>Yes
												<input autocomplete="nope" type="radio" data-type="patient" id="p_is_minor_yes" name="p_is_minor" value="1" class="LoNotSensitive " <?php echo ((in_array('is_minor', $radios_submitted) && $data['p_is_minor'] != '') ? 'preload="' . (int)$data['p_is_minor'] . '"' : ''); ?> >
												<span class="rb-checkmark"></span>
											</label>
											<label for="p_is_minor_no" class='rb-container no_width'>No
												<input autocomplete="nope" type="radio" data-type="patient" id="p_is_minor_no" name="p_is_minor" value="0" class="LoNotSensitive " >
												<span class="rb-checkmark"></span>
											</label>
										</div>
		                            </div>
		                        </div>

		                         <div class="form-row1 patient_parent_profile no-show">
									<div class="form-group">
										<div class="full-width">
											<input autocomplete="nope" type="text" data-type="patient" name="p_parent_first_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_first_name']));?>" class="LoNotSensitive" placeholder="Parent/Guardian First Name *" title="Parent/Guardian First Name *">
										</div>
									</div>	
									<div class="form-group">	
										<div class="full-width">
											<input autocomplete="nope" type="text" data-type="patient" name="p_parent_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_middle_initial']));?>" maxlength="1" class="LoNotSensitive" placeholder="Parent/Guardian Middle Initial *" title="Parent/Guardian Middle Initial *">
										</div>
									</div>
									<div class="form-group">	
										<div class="full-width">
											<input autocomplete="nope" type="text" data-type="patient" name="p_parent_last_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_last_name']));?>" class="LoNotSensitive" placeholder="Parent/Guardian Last Name *" title="Parent/Guardian Last Name *">
										</div>
									</div>
									<div class="clear"></div>

									<input autocomplete="nope" type="text" data-type="patient" name="p_parent_phone" value="<?php echo $data['p_parent_phone'];?>" class="LoNotSensitive" placeholder="Parent/Guardian Phone *" title="Parent/Guardian Phone *">
								</div>
								<div class="clear"></div>

		                         <div class="form-group radio-group">
		                            <div class="maxl">
		                               <div class="form-row1">
											<div class="full-width align-left">
												<label for="p_gender">Gender <span class="red">*</span></label>
											</div>
											<div class="full-width align-left">
												<input type="hidden" data-f-count="2" data-fl_1="p_gender_m" data-fl_2="p_gender_f" class="patient-enroll-progress-2" data-step-enroll="2">
												<label for="p_gender_m" class='rb-container no_width'>Male
													<input autocomplete="nope" type="radio" data-type="patient" id="p_gender_m" name="p_gender" value="M" class="LoNotSensitive" preload="<?php echo $data['p_gender'];?>" >
													<span class="rb-checkmark"></span>
												</label>
												<label for="p_gender_f" class='rb-container no_width'>Female
													<input autocomplete="nope" type="radio" data-type="patient" id="p_gender_f" name="p_gender" value="F" class="LoNotSensitive " >
													<span class="rb-checkmark"></span>
												</label>
											</div>
										</div>
										<div class="clear"></div>
		                            </div>
		                        </div>
		                        <div class="form-group">
		                            <div class="full-width">
		                            	<input autocomplete="nope" type="text" data-type="patient" id="p_dob" name="p_dob" value="<?php echo $data['p_dob'];?>" class="LoNotSensitive patient-enroll-progress-2" placeholder="Date of Birth (mm/dd/yyyy) *" title="Date of Birth (mm/dd/yyyy) *" data-text="Why Is My Date Of Birth Needed?" data-hint="" data-step-enroll="2">
		                            	<a class="tooltip-text" href="javascript:void(0);" data-toggle="tooltip" title="Your Date of Birth is required by the pharmaceutical company to process your medication orders.">Why Is My Date Of Birth Needed?</a>
		                            </div>
		                        </div>
		                        <div class="form-group">
		                            <?php if(isset($response->success) && $response->success==2){ ?>
										<div class="full-width"><input autocomplete="nope" type="text" data-type="patient" name="p_ssn" value="<?php echo $_POST['p_ssn'];?>" maxlength="11" class="patient-enroll-progress-2 LoNotSensitive" placeholder="Social Security Number *" title="Social Security Number *" data-text="" data-hint="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology." data-step-enroll="2">
										<a class="tooltip-text" href="javascript:void(0);" data-toggle="tooltip" title="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology." data-hint="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology.">Why Is My Social Security Number Needed?</a>	
										</div>
										<?php } else { ?>
										<div class="full-width"><input autocomplete="nope" type="text" data-type="patient" name="p_ssn" value="<?php echo $data['p_ssn'];?>" maxlength="11" class="patient-enroll-progress-2 LoNotSensitive" placeholder="Social Security Number *" title="Social Security Number *" data-text="" data-hint="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology." data-step-enroll="2">
										<a class="tooltip-text" href="javascript:void(0);" data-toggle="tooltip" title="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology." data-hint="Your social security number is required by the pharmaceutical company to process your medication order. Information relating to electronic transactions entered into this website will be protected by 256-bit encryption technology.">Why Is My Social Security Number Needed?</a>	
										</div>
										<?php } ?>
		                        </div>
		                        <!-- <div class="col-sm-6 w-100 p-l-0"> -->
		                            <div class="form-group">
		                                <div class="full-width"><input autocomplete="nope" type="text" data-type="patient" name="p_alternate_contact_name" value="<?php echo htmlspecialchars(stripslashes($data['p_alternate_contact_name']));?>" class="LoNotSensitive" placeholder="Alternate Contact Name" title="Alternate Contact Name"></div>
		                            </div>
		                        <!-- </div>
		                        <div class="col-sm-6 w-100 p-r-0"> -->
		                            <div class="form-group">
		                               <div class="full-width"><input autocomplete="nope" type="text" data-type="patient" name="p_alternate_phone" value="<?php echo $data['p_alternate_phone'];?>" class="LoNotSensitive not_required_phone" placeholder="Alternate Contact Phone" title="Alternate Contact Phone"></div>
		                            </div>
		                        <!-- </div> -->
		                        <div class="form-group">
		                            <?php //echo "<pre>";print_r($_SESSION['register_data']);echo "</pre>";?>
					<?php if($data['p_hear_about'] == '2685-4694 Access Health Insurance, Inc') $data['p_hear_about'] = '2685-4694 JibeHealth'; ?>
					<?php if (isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') { ?>

					<?php 
						
						$broker_array = array();
						if(!empty($_SESSION['register_data']['p_application_source'])){
							 $broker_string = trim(substr($_SESSION['register_data']['p_application_source'],0,9));
							 $broker_array = explode('-', $broker_string);

						}
						if(count($broker_array) == 2){
						?>
							<input autocomplete="nope" type="text" data-type="patient" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes(trim(substr($data['p_application_source'],9))));?>" class="full-width" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>>
						<?php	
						}else{
						?>	
							<input autocomplete="nope" type="text" data-type="patient" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about']));?>" class="full-width" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>>
						<?php }?>
						
					<?php } else { ?>
						<div class="full-width">
							<?php 
						//echo "<pre>";print_r($data);echo "</pre>"; 

						/*$broker_name = trim(substr($_GET['my_broker_source'],9));
						if(isset($_GET['my_broker_source'])){
							$data['p_application_source'] = $_GET['my_broker_source'];
						}*/
						$broker_array = array();
						if(!empty($data['p_application_source'])){
							 $broker_string = trim(substr($data['p_application_source'],0,9));
							 $broker_array = explode('-', $broker_string);

						}
						//print_r($broker_array);
					/*	if(preg_match("/[a-z]/i", $data['p_application_source'])){
						    print "it has alphabet!";
						}else{
							print "not alphabet!";
						}*/
						//Anil code
						?>
						<?php if(!empty($data['p_application_source']) && count($broker_array) == 2){?>
							<!-- <select data-type="patient" name="p_hear_about" id="p_hear_about" data-value="<?php echo htmlspecialchars(stripslashes($data['p_application_source']));?>" class="full-width LoNotSensitive form-control" placeholder="How did you hear about Prescription Hope? *" <?php if(!empty($data['p_application_source']) && count($broker_array) == 2){ echo "disabled";}?>> -->
							<input autocomplete="nope" type="text" data-type="patient" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes(trim(substr($data['p_application_source'],9))));?>" class="full-width" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>>
						<?php }else{?>
							<select data-type="patient" name="p_hear_about" id="p_hear_about" data-value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about']));?>" class="full-width LoNotSensitive form-control patient-enroll-progress-2" placeholder="How did you hear about Prescription Hope? *" title="How did you hear about Prescription Hope? *" data-step-enroll="2">
								<option value=""></option>
								<?php /*if(!empty($data['p_application_source']) && count($broker_array) == 2){
									$broker_name =  trim(substr($data['p_application_source'],9));
								?>
									<option value="<?php echo $data['p_application_source'];?>"><?php echo $broker_name;?></option>
								<?php }*/?>
								<option value="Facebook">Facebook</option>
								<option value="Instagram">Instagram</option>
								<option value="Google">Google</option>
								<option value="Insurance">Insurance</option>
								<option value="Healthcare Provider">Healthcare Provider</option>
								<option value="Pharmacy">Pharmacy</option>
								<option value="Family Member">Family Member</option>
								<option value="Friend">Friend</option>
								<option value="Previous Patient">Previous Patient</option>
								<option value="Referral By Current Member of the Prescription Hope Program">Referral By Current Member of the Prescription Hope Program</option>
								<option value="Other">Other</option>
							</select>
						<?php }?>		
								
						</div>
					<?php } ?>
					<input autocomplete="nope" type="hidden" data-type="patient" name="p_application_source" value="<?=((isset($data['p_application_source'])) ? $data['p_application_source'] : '')?>">
		                        </div>
		                        <a class="btn btn-primary next btn-block mv-10" href="javascript:;">Next</a>
		                        <!-- <button type="submit" class="btn btn-primary btn-block">NEXT</button> -->
		                   <!--  </form> -->
		                </div>
		            </div>
		        </div>
		        <div class="register-form-tab tab-pane" id="messages-v">
		            <div class="tab-1">
		                <div class="col-sm-5">
		                    <div class="heading-title">
		                        <h2>Monthly Household<br/>
		                            Income Information
		                        </h2>
		                        <p>Your monthly household income information is 
required by the pharmaceutical manufacturers to process your medication orders. It will not be used for any other purposes and will remain confidential.    </p>
		                    </div>
		                </div>
		                <div class="col-sm-7 card-form">
							<div class="radio-group">
							    <div class="full-width align-left">
							        <label for="p_has_income">Do you currently have income? <span class="red">*</span></label>
							    </div>
							    <div class="full-width align-left">
							    	<input type="hidden" data-f-count="2" data-fl_1="p_has_income_yes" data-fl_2="p_has_income_no" class="patient-enroll-progress-3" data-step-enroll="3">
							        <label for="p_has_income_yes" class='rb-container no_width'>Yes
							        <input autocomplete="nope" type="radio" data-type="patient" id="p_has_income_yes" name="p_has_income" value="1" class="LoNotSensitive patient-enroll-progress-3" <?php echo ((in_array('has_income', $radios_submitted) && $data['p_has_income'] != '') ? 'preload="' . (int)$data['p_has_income'] . '"' : ''); ?> data-step-enroll="3">
							        <span class="rb-checkmark"></span>
							        </label>
							        <label for="p_has_income_no" class='rb-container no_width'>No
							        <input autocomplete="nope" type="radio" data-type="patient" id="p_has_income_no" name="p_has_income" value="0" class="LoNotSensitive patient-enroll-progress-3" data-step-enroll="3">
							        <span class="rb-checkmark"></span>
							        </label>
							    </div>
							</div>
							<div class="clear"></div>
							
							<div class="radio-group">
							        <div class="full-width align-left">
							            <label for="p_income_file_tax_return">Do you file taxes? <span class="red" style="display: none;">*</span></label>
							        </div>
							        <div class="full-width align-left addStarValidation">
							            <label for="p_income_file_tax_return_yes" class='rb-container no_width'>Yes
							            <input autocomplete="nope" type="radio" data-type="patient" id="p_income_file_tax_return_yes" name="p_income_file_tax_return" value="1" class="LoNotSensitive patient-enroll-progress-3" <?php echo ((in_array('file_tax_return', $radios_submitted) && $data['p_income_file_tax_return'] != '') ? 'preload="' . (int)$data['p_income_file_tax_return'] . '"' : ''); ?> data-step-enroll="3">
							            <span class="rb-checkmark"></span>
							            </label>
							            <label for="p_income_file_tax_return_no" class='rb-container no_width'>No
							            <input autocomplete="nope" type="radio" data-type="patient" id="p_income_file_tax_return_no" name="p_income_file_tax_return" value="0" class="LoNotSensitive patient-enroll-progress-3" data-step-enroll="3">
							            <span class="rb-checkmark"></span>
							            </label>
							        </div>
							       
							    </div>
							    <div class="clear"></div>
							 <div class="radio-group">
							        <div class="full-width align-left">
							            <label for="p_uscitizen">Are you a US Citizen? <span class="red">*</span></label>
							        </div>
							        <div class="full-width align-left">
							        	<input type="hidden" data-f-count="2" data-fl_1="p_uscitizen_yes" data-fl_2="p_uscitizen_no" class="patient-enroll-progress-3" data-step-enroll="3">
							            <label for="p_uscitizen_yes" class='rb-container no_width'>Yes
							            <input autocomplete="nope" type="radio" data-type="patient" id="p_uscitizen_yes" name="p_uscitizen" value="1" class="LoNotSensitive patient-enroll-progress-3" <?php echo ((in_array('us_citizen', $radios_submitted) && $data['p_uscitizen'] != '') ? 'preload="' . (int)$data['p_uscitizen'] . '"' : ''); ?> data-step-enroll="3">
							            <span class="rb-checkmark"></span>
							            </label>
							            <label for="p_uscitizen_no" class='rb-container no_width'>No
							            <input autocomplete="nope" type="radio" data-type="patient" id="p_uscitizen_no" name="p_uscitizen" value="0" class="LoNotSensitive patient-enroll-progress-3" data-step-enroll="3">
							            <span class="rb-checkmark"></span>
							            </label>
							        </div>
							      
							    </div>
							    <div class="clear"></div>   
							<div id="patient_income_section" class="hidden">
							   <!--  <p class="normal align-left">
							        Please answer all of these questions to the best of your ability.
							    </p> -->
							    <div class="patient_income_yes_only">
							        <div class="full-width align-left"><label for="p_employment_status" class="line-height-48">Are you currently employed? <span class="red">*</span></label></div>
							        <div class="full-width">
							            <select data-type="patient" id="p_employment_status" name="p_employment_status" preload="<?php echo $data['p_employment_status'];?>" class="full-width no-float-label LoNotSensitive" title="Are you currently employed?" placeholder="Are you currently employed?">
							                <option value=''></option>
							                <option value='F'>Full-Time</option>
							                <option value='P'>Part-Time</option>
							                <option value='R'>Retired</option>
							                <option value='U'>Unemployed</option>
							                <option value='S'>Self-Employed</option>
							            </select>
							        </div>
							    </div>
							    <div class="v_box-field">
							        <div class="full-width align-left"><label for="p_married" class="line-height-48">Are you married? <span class="red">*</span></label></div>
							        <div class="full-width">
							            <select data-type="patient" name="p_married" id="p_married" preload="<?php echo $data['p_married'];?>" class="full-width no-float-label LoNotSensitive patient-enroll-progress-3" data-step-enroll="3" title="Are you married?" placeholder="Are you married?">
							                <option value=''></option>
							                <option value='S'>Single</option>
							                <option value='M'>Married</option>
							                <option value='D'>Separated</option>
							                <option value='W'>Widowed</option>
							                <option value='B'>Divorced</option>
							            </select>
							        </div>
							    </div>
							   <div class="v_box-field">
							        <div class="full-width align-left"><label for="p_household" class="line-height-48">How many people live in your household? <span class="red">*</span></label></div>
							        <div class="full-width">
							            <select data-type="patient" name="p_household" id="p_household" preload="<?php echo $data['p_household'];?>" class="full-width no-float-label LoNotSensitive" title="How many people live in your household?" placeholder="How many people live in your household?">
							                <option value=''></option>
							                <?php for ($i = 1; $i < 11; $i++) { ?>
							                <option value='<?=$i?>'><?=$i?></option>
							                <?php } ?>
							            </select>
							        </div>
							    </div>
							    <br/>
							    
							    <div class="patient_income_yes_only">
							        <div class="radio-group">
							            <div class="full-width align-left">
							                <label for="p_medicaid">Have you applied for Medicaid? <span class="red">*</span></label>
							            </div>
							            <div class="full-width align-left">
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
							        <div class="radio-group p_medicaid_2nd">
							            <div class="full-width align-left">
							                <label for="p_medicaid_denial" class="">If yes, did you receive a denial letter? <span class="red">*</span></label>
							            </div>
							            <div class="full-width align-left">
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
							   <div class="radio-group v_box-field">
							            <div class="full-width align-left">
							                <label for="p_medicare">Are you on Medicare? <span class="red">*</span></label>
							            </div>
							            <div class="full-width align-left">
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
							        <?php
							            $pMedicare  = ((int)$data['p_medicare'] == 1) ? 'display: block' : 'display: none';
							            ?>
							        <div class="radio-group p_medicare_2nd" style="<?php echo $pMedicare; ?>">
							            <div class="full-width align-left">
							                <label for="p_medicare_part_d" class="">Do you have Medicare Part D? <span class="red">*</span></label>
							            </div>
							            <div class="full-width align-left">
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
							        <?php 
							            $pCoverageGapYes  = ((int)$data['p_medicare_part_d'] == 1) ? 'display: block' : 'display: none';
							            ?>
							        <div class="radio-group p_medicare_part_d_2nd" style="<?php echo $pCoverageGapYes; ?>">
							            <div class="full-width align-left">
							                <label for="p_coverage_gap_yes" class="">Are you in the coverage gap? <span class="red">*</span></label>
							            </div>
							            <div class="full-width align-left">
							                <label for="p_coveragegapyes" class='rb-container no_width'>Yes
							                <input autocomplete="nope" type="radio" data-type="patient" id="p_coveragegapyes" name="p_coveragegapyes" value="1" class="radio-groups LoNotSensitive" <?php echo (( $data['p_coveragegapyes'] != '') ? 'preload="' . (int)$data['p_coveragegapyes'] . '"' : ''); ?>>
							                <span class="rb-checkmark"></span>
							                </label>
							                <label for="p_coveragegapno" class='rb-container no_width'>No
							                <input autocomplete="nope" type="radio" data-type="patient" id="p_coveragegapno" name="p_coveragegapyes" value="0" class="radio-groups LoNotSensitive">
							                <span class="rb-checkmark"></span>
							                </label>
							            </div>
							            <br>
							        </div>
							        <div class="clear"></div>
							      
							        <div class="radio-group p_coveragegapyes_2nd" style="<?php echo $pCoverageGapYes; ?>">
							            <div class="full-width align-left">
							                <label for="p_prescription_money_yes" class="">How much money have you spent out of pocket on your prescriptions for the current year? <span class="red">*</span></label>
							            </div>
							            <div class="full-width align-left pocketmoney-enroll">
							                <input autocomplete="nope" type="text" data-type="patient" name="p_pocketmoney" id="p_pocketmoney" value="<?php echo $data['p_pocketmoney'];?>" class="dollar-amount input_zero no-float-label LoNotSensitive" placeholder="">									
							            </div>
							            <br>
							        </div>
							        <div class="clear"></div>
							        <div class="radio-group p_medicaid_2nd">
							            <div class="full-width align-left">
							                <label for="p_lis">Have you applied for Low Income Subsidy (LIS)? <span class="red">*</span></label>
							            </div>
							            <div class="full-width align-left">
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
							        <div class="radio-group p_medicaid_2nd p_lis_2nd">
							            <div class="full-width align-left">
							                <label for="p_lis_denial" class="">If yes, did you receive a denial letter? <span class="red">*</span></label>
							            </div>
							            <div class="full-width align-left">
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
							        <div class="radio-group">
							            <div class="full-width align-left">
							                <label for="p_disabled_status">Are you disabled as determined by Social Security? <span class="red">*</span></label>
							            </div>
							            <div class="full-width align-left">
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
							        
							        <p class="normal align-left">
							            Check the box for the type(s) of income you receive monthly.
							         </p> 
									<p class="normal align-left">
							            Then put the correct number in the box.
							        </p>
							        <div id="first_income_source_row" class="align-left">
							            <label for="p_has_salary" class="cb-container checkbox-label">Monthly Gross Salary/Wages Income
							            <input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_salary" name="p_has_salary" value="1" <?php echo (($data['p_income_salary'] != '' && $data['p_income_salary'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
							            <span class="cb-checkmark"></span>
							            </label>
							            <div class="clear"></div>
							        </div>
							        <div class="p_income_salary full-width relative hidden">
							            <div class="inline inline-div-for-inputs">$</div>
							            <input autocomplete="nope" type="text" data-type="patient" name="p_income_salary" id="p_income_salary" value="<?=(($data['p_income_salary'] != '' && $data['p_income_salary'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_salary']), 2, '.', ',') : '')?>" class="dollar-amount input_zero no-float-label LoNotSensitive">
							        </div>
							        <div class="align-left">
							            <label for="p_has_unemployment" class="cb-container checkbox-label">Monthly Unemployment Income
							            <input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_unemployment" name="p_has_unemployment" value="1" <?php echo (($data['p_income_unemployment'] != '' && $data['p_income_unemployment'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
							            <span class="cb-checkmark"></span>
							            </label>
							            <div class="clear"></div>
							        </div>
							        <div class="p_income_unemployment full-width relative hidden">
							            <div class="inline inline-div-for-inputs">$</div>
							            <input autocomplete="nope" type="text" data-type="patient" name="p_income_unemployment" id="p_income_unemployment" value="<?=(($data['p_income_unemployment'] != '' && $data['p_income_unemployment'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_unemployment']), 2, '.', ',') : '')?>" class="dollar-amount input_zero no-float-label LoNotSensitive">
							        </div>
							        <div class="align-left">
							            <label for="p_has_pension" class="cb-container checkbox-label">Monthly Pension Income
							            <input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_pension" name="p_has_pension" value="1" <?php echo (($data['p_income_pension'] != '' && $data['p_income_pension'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
							            <span class="cb-checkmark"></span>
							            </label>
							            <div class="clear"></div>
							        </div>
							        <div class="p_income_pension full-width relative hidden">
							            <div class="inline inline-div-for-inputs">$</div>
							            <input autocomplete="nope" type="text" data-type="patient" name="p_income_pension" id="p_income_pension" value="<?=(($data['p_income_pension'] != '' && $data['p_income_pension'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_pension']), 2, '.', ',') : '')?>" class="dollar-amount input_zero no-float-label LoNotSensitive">
							        </div>
							        <div class="align-left">
							            <label for="p_has_annuity" class="cb-container checkbox-label">Monthly Annuity/IRA Income
							            <input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_annuity" name="p_has_annuity" value="1" <?php echo (($data['p_income_annuity'] != '' && $data['p_income_annuity'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
							            <span class="cb-checkmark"></span>
							            </label>
							            <div class="clear"></div>
							        </div>
							        <div class="p_income_annuity full-width relative hidden">
							            <div class="inline inline-div-for-inputs">$</div>
							            <input autocomplete="nope" type="text" data-type="patient" name="p_income_annuity" id="p_income_annuity" value="<?=(($data['p_income_annuity'] != '' && $data['p_income_annuity'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_annuity']), 2, '.', ',') : '')?>" class="dollar-amount input_zero no-float-label LoNotSensitive">
							        </div>
							        <div class="align-left">
							            <label for="p_has_ss_retirement" class="cb-container checkbox-label">Monthly Social Security Retirement Income
							            <input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_ss_retirement" name="p_has_ss_retirement" value="1" <?php echo (($data['p_income_ss_retirement'] != '' && $data['p_income_ss_retirement'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
							            <span class="cb-checkmark"></span>
							            </label>
							            <div class="clear"></div>
							        </div>
							        <div class="p_income_ss_retirement full-width relative hidden">
							            <div class="inline inline-div-for-inputs">$</div>
							            <input autocomplete="nope" type="text" data-type="patient" name="p_income_ss_retirement" id="p_income_ss_retirement" value="<?=(($data['p_income_ss_retirement'] != '' && $data['p_income_ss_retirement'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_ss_retirement']), 2, '.', ',') : '')?>" class="dollar-amount input_zero no-float-label LoNotSensitive">
							        </div>
							        <div class="align-left">
							            <label for="p_has_ss_disability" class="cb-container checkbox-label">Monthly Social Security Disability Income
							            <input autocomplete="nope" type="checkbox" data-type="patient" id="p_has_ss_disability" name="p_has_ss_disability" value="1" <?php echo (($data['p_income_ss_disability'] != '' && $data['p_income_ss_disability'] != 0) ? 'checked="checked"' : ''); ?> class="checkbox-normal income_checkbox_field LoNotSensitive">
							            <span class="cb-checkmark"></span>
							            </label>
							            <div class="clear"></div>
							        </div>
							        <div class="p_income_ss_disability full-width relative hidden">
							            <div class="inline inline-div-for-inputs">$</div>
							            <input autocomplete="nope" type="text" data-type="patient" name="p_income_ss_disability" id="p_income_ss_disability" value="<?=(($data['p_income_ss_disability'] != '' && $data['p_income_ss_disability'] != 0) ? number_format(preg_replace("/[^0-9.]/", "", $data['p_income_ss_disability']), 2, '.', ',') : '')?>" class="dollar-amount input_zero no-float-label LoNotSensitive">
							        </div>
							        <div class="hideme">
							            <div class="half-width align-left m-v10"><label for="p_income_annual_income" class="hspaced">Total Annual Income</label></div>
							            <div class="half-width">
							                <div class="d_sign">$</div>
							                <input autocomplete="nope" type="text" data-type="patient" name="p_income_annual_income" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_annual_income']));?>" class="dollar-amount no-float-label tooltiptext" data-text="Why Is My Total Annual Income Needed?" readonly data-hint="Your total monthly income is required by the pharmaceutical company to process your medication orders." placeholder="Total Annual Income">
							            </div>
							        </div>
							    </div>
							    <div class="clear"></div>
							</div>
							<a class="btn btn-primary next btn-block mv-10" href="javascript:;">Next</a>
		                        <!-- <button type="submit" class="btn btn-primary btn-block">NEXT</butoon> -->
		                </div>
		            </div>
		        </div>
		        <div class="register-form-tab tab-pane" id="settings-r">
		            <div class="tab-1">
		                <div class="col-sm-5">
		                    <div class="heading-title">
		                        <h2>Healthcare Provider Information</h2>
		                        <p>Only list the healthcare providers who are prescribing 
		                            medications you are requesting through Prescription Hope. If this information is not correct, your first 
		                            medication delivery will be delayed.
		                        </p>
		                    </div>
		                </div>
		             
		                    <div class="tab-container col-sm-7 col-xs-12">
		                        <ul id="myTabs" class=" col-sm-7 tab-section-tab nav nav-pills nav-justified" role="tablist" data-tabs="tabs">
									<?php $valid_drs = 0; ?>
											<?php 
												foreach ($data['doctors'] as $dr_key => $doctor) {
												//var_dump(count($data['doctors']));	

											 ?>
												<?php if ((count($data['doctors']) > 1 && $doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') || count($data['doctors']) == 1) { ?>
													<?php $valid_drs++; ?>
													<?php 
														if($valid_drs == 1) {
															$addClass = "active";
														} else {
															$addClass = "";
														}
													?>
													    <li class="<?php echo $addClass; ?>"><a href="#pb<?=($valid_drs)?>" data-toggle="tab"><?php echo !empty($doctor['doctor_last_name']) ? 'Dr. '.ucwords(truncateMedication($doctor['doctor_last_name'],6, $dots = "...") ) : 'New'; ?></a>
													    </li>
													   
													    <!-- <li><a href="#Videos" data-toggle="tab">Dr. Bryan Hildreth</a></li> -->
														<?php 
														if($valid_drs == 4) {
																//echo "<br>";
															}
														} ?>
													<?php } ?>    
												    <li>
												    	<a href="#Events" name="bAddANewDoctor" id="bAddANewDoctor"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
														  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
														</svg>
														</a>
													</li>
								  </ul>
								     <div class="col-sm-7 card-form">
		                        <div class="tab-content" old-id="new_doctor_form" id="doctors_forms_list">
					<!-- <div> -->
								<?php $valid_drs = 0; ?>
								<?php foreach ($data['doctors'] as $dr_key => $doctor) { ?>
									<?php if ((count($data['doctors']) > 1 && $doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') || count($data['doctors']) == 1) { ?>
										<?php $valid_drs++;
											if($valid_drs == 1) {
												$addClass = "active";
											} else {
												$addClass = "";
											}
										 ?>
										<div old-id="doctor_form" role="tabpanel" class="dr-form tab-pane fade in <?php echo $addClass; ?>" id="pb<?=($valid_drs)?>">
											<!-- <div old-id="doctor_form" class="dr-form" id=""> -->
												<!-- <input class="doctor_id-<?=$valid_drs?>" autocomplete="nope" type="hidden" name="doctor_id[<?=$valid_drs?>]" id="doctor_id" value="<?=$doctor['doctor_id']?>">
												<input class="doctor_check-<?=$valid_drs?>" autocomplete="nope" type="hidden" value="<?=$doctor['doctor_first_name']?><?=$doctor['doctor_last_name']?><?=$doctor['doctor_facility']?><?=$doctor['doctor_address']?><?=$doctor['doctor_address2']?><?=$doctor['doctor_city']?><?=$doctor['doctor_state']?><?=$doctor['doctor_zip']?><?=$doctor['doctor_phone']?><?=$doctor['doctor_fax']?>"> -->
												<?php if($valid_drs>1) { ?>
												<div class="remove-div-1"><a href="javascript:void(0);" class="remove_block" id="remove_provider_pb<?=($valid_drs)?>">X Remove Provider</a></div>
												<?php } ?>
													<div>
														<div class="form-group">		
															<div class="full-width doctor-fname-field"><input autocomplete="nope" type="text" name="doctor_first_name[<?=$valid_drs?>]" value="<?=$doctor['doctor_first_name']?>" class="doctor_first_name-<?=$valid_drs?> dr-required dr-data doctor-fields LoNotSensitive patient-enroll-progress-4" placeholder="Healthcare Provider First Name *" title="Healthcare Provider First Name *" data-step-enroll="4"></div>
														</div>
														<div class="form-group">	
															<div class="full-width doctor-lname-field"><input autocomplete="nope" type="text" name="doctor_last_name[<?=$valid_drs?>]" value="<?=$doctor['doctor_last_name']?>" class="doctor_last_name-<?=$valid_drs?> dr-required dr-data doctor-lname-fields LoNotSensitive patient-enroll-progress-4" placeholder="Healthcare Provider Last Name *" title="Healthcare Provider Last Name *" data-step-enroll="4"></div>
														</div>	
													</div>

												<div>	
												
													<div class="full-width doctor-facility-field"><input autocomplete="nope" type="text" name="doctor_facility[<?=$valid_drs?>]" value="<?=$doctor['doctor_facility']?>" class="doctor_facility-<?=$valid_drs?> dr-data LoNotSensitive" placeholder="Facility Name" title="Facility Name"></div>
													<div class="full-width doctor-address-field"><input autocomplete="nope" type="text" name="doctor_address[<?=$valid_drs?>]" value="<?=$doctor['doctor_address']?>" class="doctor_address-<?=$valid_drs?> dr-required dr-data dr-address LoNotSensitive patient-enroll-progress-4" placeholder="Address *" title="Address *" rel="" data-step-enroll="4"></div>
												</div>
											
												<div>										
													<div class="half-width doctor-address2-field"><input autocomplete="nope" type="text" name="doctor_address2[<?=$valid_drs?>]" value="<?=$doctor['doctor_address2']?>" class="doctor_address2-<?=$valid_drs?> dr-data LoNotSensitive" placeholder="Suite Number" title="Suite Number"></div>
													<div class="half-width doctor-city-field"><input autocomplete="nope" type="text" name="doctor_city[<?=$valid_drs?>]" value="<?=$doctor['doctor_city']?>" class="doctor_city-<?=$valid_drs?> dr-required dr-data LoNotSensitive patient-enroll-progress-4" placeholder="City *" title="City *" data-step-enroll="4"></div>
												</div>									

												<div>										
													<div class="half-width doctor-state-field">
														<select name="doctor_state[<?=$valid_drs?>]" id="doctor_state[<?=$valid_drs?>]" preload="<?=$doctor['doctor_state']?>" class="doctor_state-<?=$valid_drs?> dr-required dr-data full-width LoNotSensitive" placeholder="State *" title="State *">
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
															<option value="PR">Puerto Rico</option>
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
													<div class="half-width doctor-zip-field"><input autocomplete="nope" type="text" name="doctor_zip[<?=$valid_drs?>]" value="<?=$doctor['doctor_zip']?>" maxlength="5" class="doctor_zip-<?=$valid_drs?> dr-required dr-data dr-zip LoNotSensitive patient-enroll-progress-4" placeholder="Zip Code *" title="Zip Code *" data-step-enroll="4"></div>
												</div>									

												<div>										
													<div class="half-width doctor-phone-field"><input autocomplete="nope" type="text" name="doctor_phone[<?=$valid_drs?>]" value="<?=$doctor['doctor_phone']?>" class="doctor_phone-<?=$valid_drs?> dr-required dr-data dr-phone LoNotSensitive patient-enroll-progress-4" placeholder="Mobile Phone Number *" title="Mobile Phone Number *" data-step-enroll="4"></div>
													<div class="half-width doctor-fax-field"><input autocomplete="nope" type="text" name="doctor_fax[<?=$valid_drs?>]" value="<?=$doctor['doctor_fax']?>" class="doctor_fax-<?=$valid_drs?> dr-data dr-fax LoNotSensitive" placeholder="Fax Number" title="Fax Number"></div>
												</div>									
											<!-- </div> -->
										</div>	
									<?php 
										} ?>
								<?php } ?>
							<!-- </div> -->
						</div>	
						<div class=" m-top">
							<!-- <button autocomplete="nope" type="button" name="bAddANewDoctor" id="bAddANewDoctor" value="Add Another Provider" class="btn btn-primary inline">Add Another Provider</button> -->
						 <div class="add-healthcare-section">
							    <a href="javascript:void(0);" class="healthcare_add" name="bAddANewDoctorHealyhcare" id="bAddANewDoctorHealthcare">
							        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
							            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
							        </svg>
							        <span>Add Healthcare Provider</span>
							    </a>
						</div>
						 <a class="btn btn-primary btn-add-new-doctor inline next" href="#">Next</a>
		                    </div>
							</div> 
		                </div>
		            </div>
		        </div>
		        <div class="register-form-tab tab-pane" id="settings-s">
		            <div class="tab-1">
		                <div class="col-sm-5">
		                    <div class="heading-title">
		                        <h2>Medication Information</h2>
		                        <p>Please list all the medications you are requesting through Prescription Hope. We only want to help you save money, so we ask that you only put the medications you are paying <b>more than $50 a month</b> for on your enrollment form.</p>
		                    </div>
		                </div>
		                
		                    <div class="tab-container col-sm-7 col-xs-12">
		                        <ul id="myTabsMedication" class="tab-section-tab nav nav-pills nav-justified" role="tablist" data-tabs="tabs">
		                        	<?php 
	                        		$valid_meds = 0;
										foreach ($data['medication'] as $med_key => $medication) { ?>
											<?php if (($medication['medication_name'] != '' && $medication['medication_strength'] != '' && $medication['medication_frequency'] != '' && (int) $medication['medication_doctor'] != '') || count($data['medication']) == 1) {
												$valid_meds++;
										?>
										<?php 
											if($valid_meds == 1) {
												$addClass = "active";
											} else {
												$addClass = "";
											}

										?>		
			                            <li class="<?php echo $addClass; ?>"><a href="#mb<?=($valid_meds)?>" data-toggle="tab"><?php echo !empty($medication['medication_name']) ? ucwords($medication['medication_name']) : 'New'; ?></a></li>
			                            <?php 
			                            if($valid_meds == 4) {
												//echo "<br>";
											}
			                        	} ?>
									<?php } ?>   	
			                            <li>
			                                <a href="#Events" name="bAddANewMedication" id="bAddANewMedication">
			                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
			                                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
			                                    </svg>
			                                </a>
			                            </li>
		                        </ul>
								<div class="col-sm-7 card-form">
									<!-- <div class="tab-content"> -->
									    <!-- <div role="tabpanel" class="tab-pane fade in active <?php echo $addClass; ?>" id="medication_form"> -->
									    	<div  id="medication_form" class="tab-content">
									        <!-- <form> -->
									        	<!-- <div class="no-margin" id="medication_form"> -->
			<?php
			$valid_meds = 0;
			foreach ($data['medication'] as $med_key => $medication) { ?>
				<?php if ($medication['medication_name'] != '' && $medication['medication_strength'] != '' && $medication['medication_frequency'] != '' && (int) $medication['medication_doctor'] != '') {
					$valid_meds++;
					if($valid_meds == 1) {
						$addClassMeds = "active";
					} else {
						$addClassMeds = "";
					}
					?>
					<div class="medication-row tab-pane fade in <?php echo $addClassMeds; ?>"  id="mb<?php echo $valid_meds?>" role="tabpanel">
						<?php if($valid_meds>1) { ?>
						<div class="remove-div-1"><a href="javascript:void(0);" class="remove_block" id="remove_provider_mb<?php echo $valid_meds?>">X Remove Medication</a></div>
						<?php } ?>
					
						<?php /*<div class="medication-no-field p20-width align-center bold w100">Medication <?=($valid_meds)?></div> */?>
						<div>
							<div class="form-group">		
								<div class="medication-name-field full-width">
									<input autocomplete="nope" type="text" name="medication_name[<?=$valid_meds?>]" value="<?=$medication['medication_name']?>" placeholder="Medication Name *" title="Medication Name *"class="med-data LoNotSensitive med_name">
								</div>
							</div>
							<div class="form-group">			
								<div class="medication-strength-field full-width">
									<input autocomplete="nope" type="text" name="medication_strength[<?=$valid_meds?>]" value="<?=$medication['medication_strength']?>" placeholder="Medication Strength *" title="Medication Strength *" class="med-data LoNotSensitive">
								</div>
							</div>	
						</div>
						<div>
							<div class="form-group">		
								<div class="medication-frequency-field full-width">
									<input autocomplete="nope" type="text" name="medication_frequency[<?=$valid_meds?>]" value="<?=$medication['medication_frequency']?>" placeholder="Medication Frequency (ex. daily) *" title="Medication Frequency (ex. daily) *" class="med-data LoNotSensitive">
								</div>	
							</div>
							<div class="form-group">			
								<div class="medication-doctor-field full-width select-dropdown">
									<select autocomplete="nope" name="medication_doctor[<?=$valid_meds?>]" preload="<?=$medication['medication_doctor']?>" class="doctors_dropdown med-data full-width LoNotSensitive" placeholder="Prescribing Healthcare Provider *" title="Prescribing Healthcare Provider *">
										<option value=""></option>
									</select>
								</div>
							</div>	
					</div>									
				</div>								
				<?php } ?>
			<?php } ?>

	<?php if ($valid_meds == 0) { ?>
							<!--input type="hidden" name="med_id" id="med_id" value="1"-->

	<div class="medication-row tab-pane fade in active" id="mb1" role="tabpanel">
	<!-- <div class="medication-no-field p20-width align-center bold w100">Medication 1</div> -->
		<div>
			<div class="form-group">		
				<div class="medication-name-field full-width">
					<input autocomplete="nope" type="text" name="medication_name[1]" value="" placeholder="Medication Name *" title="Medication Name *" class="med-data LoNotSensitive med_name patient-enroll-progress-5" data-step-enroll="5">
				</div>
			</div>
			<div class="form-group">			
				<div class="medication-strength-field full-width">
					<input autocomplete="nope" type="text" name="medication_strength[1]" value="" placeholder="Medication Strength *" title="Medication Strength *" class="med-data LoNotSensitive patient-enroll-progress-5" data-step-enroll="5">
				</div>
			</div>	
		</div>
		<div>
			<div class="form-group">		
				<div class="medication-frequency-field full-width">
					<input autocomplete="nope" type="text" name="medication_frequency[1]" value="" placeholder="Medication Frequency (ex. daily) *" title="Medication Frequency (ex. daily) *" class="med-data LoNotSensitive patient-enroll-progress-5" data-step-enroll="5">
				</div>
			</div>	
			<div class="form-group">		
				<div class="medication-doctor-field full-width select-dropdown">
					<select autocomplete="nope" name="medication_doctor[1]" class="doctors_dropdown med-data full-width LoNotSensitive patient-enroll-progress-5" placeholder="Prescribing Healthcare Provider *" title="Prescribing Healthcare Provider *" data-step-enroll="5">
						<option value=""></option>
						<?php /*foreach ($data['doctors'] as $dr_key => $doctor) { ?>
							<?php if ($doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') { ?>
								<option value="<?php echo ($dr_key);?>"><?php echo 'Doctor ' . ($dr_key) . ' (' . $doctor['doctor_first_name'] . ' ' . $doctor['doctor_last_name'] . ')';?></option>
							<?php } ?>
						<?php } */ ?>
					</select>
				</div>
			</div>	
		</div>								
	</div>
<?php } ?>
<!-- </div> -->

								

								<!--  <div class="add-provider-section">
								    <a href="#" class="provider_add">
								        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
								            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
								        </svg>
								        <span>Add Medication</span>
								    </a>
										</div> -->
										
									       <!--  </form> -->
									    </div>
									<!-- </div> -->
									<div class="add-provider-section">
									    <a href="javascript:void(0);" class="provider_add" name="bAddANewMedicationNew" id="bAddANewMedicationNew">
									        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
									            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
									        </svg>
									        <span>Add Medication</span>
									    </a>
									</div>
									<div class="med_info well m-top" id="med_info" style="display: none;">
										<input type="hidden" id="patient_updated_price" value="<?php echo $rxi_price_calculate->patient->patient_updated_price; ?>">
											<p style="color: black;">You are requesting <span class="ct_med_txt">3 medications</span> through Prescription Hope.</p>
											<p style="color: black;">In the event you are approved for all of the medications you are requesting, your monthly total will be <span class="total_str">$150</span>.</p>
										</div>
							            <div class="m-top">
							            <!-- <input autocomplete="nope" type="button" name="bAddANewMedication" id="bAddANewMedication" value="ADD ANOTHER MEDICATION" class="btn btn-primary inline"> -->					            

							            <a class="btn btn-primary inline next medication-next" href="#">Next</a>
										</div>

                    			</div>
                			</div>	
            			</div>
					</div>
		       
		        <div class="register-form-tab tab-pane" id="settings-t">
		            <div class="tab-1">
		                <div class="col-sm-5">
		                    <div class="heading-title">
		                        <h2>Payment Information</h2>
		                        <p>Once your payment information is submitted, we will immediately begin working on your behalf to get you approved for your requested medications through the applicable patient assistance program.
		                        </p>
								
		                        <p><strong>NOTE:</strong> We are working diligently to complete your 
		                            medication order, however, it may take up to 6 weeks to receive your first supply of medication. During this time, we will be collecting the information we need from your healthcare provider and any additional information we may need from you to process your order. Please make sure you have enough medication to get you through this initial process.
		                        </p>
		                    </div>
		                </div>
		                <div class="col-sm-7 card-form">
		                	<?php if (!(isset($agent_details['use_payment']) && (bool) $agent_details['use_payment'])) { ?>
		<!-- <h3 class="dblue-text no-text-transformation">Your Medication List</h3> -->
		<!-- <p class="normal align-center">Rest assured, we will not charge your card untill we verify we can access one or more of your medications.</p> -->
		<!-- <div class="list-view">
			<ul class="first-child-section"><li>Metaforin</li><li>50.00</li></ul>
			<ul class="last-child-section"><li>Total</li><li>$50.00</li></ul>
		</div> -->
		<div id="med-list-box" style="display: none;">			
			<div class="Medication-List">
				<!-- <div class=" medication-no-field p20-width align-center bold w100">Your Medication List</div> -->
				<h3 class="dblue-text no-text-transformation">Your Medication List</h3>
				<div class="responsive-table">
					<table id="med_list" class="table table-striped col-sm-10 align-center">
						<tbody>										  
							<tr class="total"><td>Total</td><td class="total_str">$150.00 x</td></tr>
						</tbody>
					</table>							
				</div>
				<!-- <div class="p-content">
					<p class="normal align-center payment-description1">Once your payment information is submitted, we will immediately begin working on your behalf to get you approved for your requested medications through the applicable patient assistance program. In the event we are able to provide access to <span class="ct_med">all 3 of</span> your medication<span class="ct_med_s">s</span>, <u>your total monthly service fee will be <span class="total_str">$150.00</span></u>.
					</p>
					<p class="normal align-center">Rest assured, we will not charge your card until we verify we can access one or more of your medications. If we can access one or more of your medications, your card will be charged $50.00 for each medication that you are approved for as soon as we begin working on your case.</p>
					<p class="normal align-center">*Please note we are working diligently to complete your medication order, however, it may take up to 6 weeks to receive your first supply of medication. During this time, we will be collecting the information we need from your healthcare provider and any additional information we may need from you to process your order. Please make sure you have enough medication to get you through this initial process.</p>
				</div> -->
			</div>
		</div>
		<div class="p-gatway ">
			<input autocomplete="nope" type="hidden" data-type="patient" id="p_payment_method_cc" name="p_payment_method" value="cc">
			<div id="payment_cc" class=" ">
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
					<div class="full-width form-group"><input autocomplete="nope" type="text" data-type="patient" name="p_cc_number" value="<?php echo $data['p_cc_number'];?>" placeholder="Credit Card Number *" title="Credit Card Number *" class="patient-enroll-progress-6" maxlength="16" data-step-enroll="6"></div>
				</div>
				<div>
					
					<div class="half-width">
						<input autocomplete="nope" type="hidden" name="p_cc_exp_month" value="<?php echo $data['p_cc_exp_month'];?>">
						<input autocomplete="nope" type="hidden" name="p_cc_exp_year" value="<?php echo $data['p_cc_exp_year'];?>">
						<input autocomplete="nope" type="text" data-type="patient" name="p_cc_exp_date" value="<?=($data['p_cc_exp_month']!='' && $data['p_cc_exp_month']!='') ? $data['p_cc_exp_month'] . '/' . substr($data['p_cc_exp_year'], 2, 2) : ''?>" maxlength="5" class="patient-enroll-progress-6" placeholder="MM/YY *" title="MM/YY *" data-step-enroll="6">
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
						<input autocomplete="nope" type="password" data-type="patient" name="p_cc_cvv" value="<?php echo $data['p_cc_cvv'];?>" maxlength="4" placeholder="CVC *" title="CVC *" class="patient-enroll-progress-6" data-step-enroll="6">
				<!--select data-type="patient" name="p_cc_exp_year" preload="<?php echo $data['p_cc_exp_year'];?>" class="full-width" placeholder="Credit Card Expiration Year *">
							<option value=""></option>
							<?php for ($i = 0; $i < 20; $i++) { ?>
								<option value="<?php echo ((int) date('Y') + $i); ?>"><?php echo ((int) date('Y') + $i); ?></option>
							<?php } ?>
						</select-->
					</div>
				</div>
				<div class="full-width align-left onlyMobile"><a href="#" data-tooltip="images/cvv14.jpg" class="skipLeave disable-click">What is this?</a><br><br><br></div>
			</div>
			<div class="align-left card col-sm-12">
				<div class="half-width align-left"><img src="images/cc_mastercard.png"><img src="images/cc_visa.png" class="payment_images_">  <img src="images/cc_amex.png"> <img src="images/cc_discover.png"></div>
				<div class="half-width align-left noMobile">
					<!-- <a href="#" data-tooltip="images/cvv4.jpg" class="skipLeave disable-click">What is this?</a> -->

					<a class="tooltip-text skipLeave disable-click show-cvv-img" href="javascript:void(0);" data-toggle="tooltip" title="<img src='https://prescriptionhope.com/html/enrollment/images/cvv4.jpg' />">What is this?</a>

				</div>
			</div>						
		</div>
<!--	</div>-->
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
<div class="term-content">
<div class="h-line">&nbsp;</div>
			<div class="col-sm-12">

				<h3 class="dblue-text no-text-transformation">Terms Of Service</h3>
				<div class="chk_bx">
					<p class="moduleSubheader terms_scroll_box align-justify">
						<span class="policy_subtitle">Service:</span> Prescription Hope, Inc. is a fee-based medication advocacy service that assists patients in enrolling in applicable pharmaceutical companies’ patient assistance programs. You hereby authorize Prescription Hope, Inc. to act on your behalf and to sign applications for patient assistance programs by hereby granting to Prescription Hope, Inc. a limited power of attorney for the specific purposes of enrolling you in patient assistance programs and any related activities to process your enrollment. You understand this authorization can be revoked at any time by you by providing a signed letter of cancellation to Prescription Hope, Inc. as described in the Fees section. You hereby authorize your healthcare provider’s office to discuss/release medical information to Prescription Hope, Inc. relating to your applications for patient assistance programs that Prescription Hope, Inc. is processing on your behalf. You understand that Prescription Hope, Inc. does not ship, prescribe, purchase, sell, handle, or dispense prescription medication of any kind. The pharmaceutical companies offer the medication through patient assistance programs at no cost. You hereby acknowledge that you are not paying for medication(s) through the Prescription Hope, Inc. service; rather you are paying for the administrative service of ordering, managing, tracking, and refilling medications received through the Prescription Hope, Inc. medication advocacy service. You also understand and acknowledge that it is each individual pharmaceutical manufacturer who makes the final decision as to whether you qualify for their patient assistance programs.
						<br><br>You understand Prescription Hope, Inc. does not guarantee your approval for patient assistance programs; it is up to each applicable drug manufacturer to make the eligibility determination. You will be provided details in writing for each of your eligible medications. The medication is shipped directly from the pharmaceutical company and is delivered either to your home or healthcare provider’s office, depending upon the manufacturer delivery guidelines. You agree that you may be contacted via telephone, cellular phone, text message or email through all numbers and/or addresses provided by you and authorize receipt of pre-recorded and/or artificial voice messages and/or use of an automated dialing service by Prescription Hope, Inc. and/or its affiliates. By signing below, you further agree to release Prescription Hope, Inc., its agents, employees, successors and assigns from any and all liability including legal fees and costs arising from medication(s) taken by you which were procured through the Prescription Hope, Inc. medication advocacy service and/or your reliance upon the program in general. You further agree to indemnify and hold Prescription Hope, Inc., its agents, employees, successor and assigns harmless against any and all damages including legal fees and costs arising from third persons ingesting any medication procured for you through Prescription Hope, Inc. Medications covered are subject to change at any time. Prescription Hope, Inc. reserves the right to rescind, revoke, or amend its services at any time.
					</p>
				</div>
				<div class="right-padding-35 bottom-padding-15 align-left fancy-cb">
					<label for="p_service_agreement" class="cb-container big">By clicking here I state I have read and agreed with the above statements <span class="red">*</span>
						<input autocomplete="nope" type="checkbox" data-type="patient" id="p_service_agreement"  name="p_service_agreement" value="1" <?php echo (($data['p_service_agreement'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal LoNotSensitive patient-enroll-progress-6" data-step-enroll="6">
						<span class="cb-checkmark"></span>
					</label>
				</div>
				<div class="chkbox-error-blk"><label for="p_service_agreement" class="error"></label></div>
				
				<div class="chk_bx">
					<p class="moduleSubheader terms_scroll_box align-justify">
						<span class="policy_subtitle">Guarantee:</span> If you do not receive medication because you were determined to be ineligible for a patient assistance program and you have a letter of denial by the applicable pharmaceutical manufacturer, Prescription Hope, Inc. will refund the monthly administrative service fee for the medication determined to be ineligible. All Prescription Hope, Inc. will need from you is a copy of the denial letter sent to you from the applicable drug manufacturer explaining why you are ineligible.
						<br><br>
						<span class="policy_subtitle">Privacy:</span> We value our patients and make extreme efforts to protect the privacy of our patients’ personal information. Patient information is processed for order fulfillment only and for no other purpose. Patient information, including all patient health information and personal information, will never be disclosed to any third party under any circumstances. All information given to Prescription Hope, Inc., its agents, employees, successors and assigns (collectively, “Prescription Hope, Inc.”) will be held in the strictest confidence.
					</p>
				</div>
				<div class="right-padding-35 bottom-padding-15 align-left fancy-cb">
					<label for="p_guaranty_agreement" class="cb-container big">By clicking here I state I have read and agreed with the above statements <span class="red">*</span>
						<input autocomplete="nope" type="checkbox" data-type="patient" id="p_guaranty_agreement"  name="p_guaranty_agreement" value="1" <?php echo (($data['p_guaranty_agreement'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal LoNotSensitive patient-enroll-progress-6" data-step-enroll="6">
						<span class="cb-checkmark"></span>
					</label>
				</div>
				<div class="chkbox-error-blk"><label for="p_guaranty_agreement" class="error"></label></div>
				
				<div class="chk_bx">
					<p class="moduleSubheader terms_scroll_box align-justify">
						<span class="policy_subtitle">Fees:</span> Prescription Hope, Inc. charges a service fee of $50.00 a month for each medication. The monthly service fee covers 100% of the medication cost, as well as the services provided by Prescription Hope, Inc. There are no additional costs for the medication(s). If we find that we are unable to access at least one of your medication(s) during the initial enrollment process, there will be no charges to your account. If we can access your medication, the initial service fee will be debited immediately so we can begin processing the paperwork required to order each eligible medication. The initial processing of your medication order(s) ranges from an average of 4 to 6 weeks and is contingent upon prompt responses to information that we request from you and your healthcare provider(s).  Prescription Hope, Inc. will process your monthly service fee on the same day each month corresponding to your enrollment date. This monthly transaction will appear on your statement as “PRESCRIPTION HOPE”. You also agree to pay any associated fees should your EFT (electronic fund transfer) be returned unpaid by your financial institution. Due to the servicebased nature of Prescription Hope, Inc., there are no refunds other than what is explained in the Prescription Hope, Inc. Guarantee above. Prescription Hope reserves the right to change its price at any time, with or without notice.  
						<br><br>
						<span class="policy_subtitle">Eligibility:</span> You are experiencing a hardship with affording your medication and/or you currently do not have coverage that reimburses or pays for your prescription medications. You affirm that the information provided on this form is complete and accurate. If you determine the information was not correct at the time you provided it to Prescription Hope, Inc., or if the information was accurate but is no longer accurate, you will immediately notify Prescription Hope, Inc.
					</p>
				</div>
				
				<div class="right-padding-35 bottom-padding-15 align-left fancy-cb">
					<label for="p_payment_agreement" class="cb-container big">By clicking here I state I have read and agreed with the above statements <span class="red">*</span>
						<input autocomplete="nope" type="checkbox" data-type="patient" id="p_payment_agreement" name="p_payment_agreement" value="1" <?php echo (($data['p_payment_agreement'] == 1) ? 'checked="checked"' : ''); ?> class="checkbox-normal LoNotSensitive patient-enroll-progress-6" data-step-enroll="6">
						<span class="cb-checkmark"></span>
					</label>
				</div>
				<div class="chkbox-error-blk"><label for="p_payment_agreement" class="error"></label></div>
			</div>

			<!-- <div class="submit-form m-10">
				<div class="btn-en-group ">
					<input autocomplete="nope" type="hidden" name="bSubmit" value="Submit" />	
					<button autocomplete="nope" type="button" name="bSubmit_btn" id="bSubmit" value="Submit" class="btn btn-primary btn-block">	Submit</button>		
				</div>
				<input type="hidden" id="removepb" value="" />				
				<div><div id="ttt" style="color: #fff;"></div></div>
			</div> -->
			<div class="submit-form">
				<div class="btn-en-group col-sm-12">
					<input autocomplete="nope" type="hidden" name="bSubmit" value="Submit" />	
					<input autocomplete="nope" type="button" name="bSubmit_btn" id="bSubmit" value="Submit" class="btn btn-primary next btn-block mv-10">			
				</div>
				<input type="hidden" id="removepb" value="" />				
				<div><div id="ttt" style="color: #fff;"></div></div>
			</div>
		</div>
		                    <?php /*<div class="tab-container">
		                        <ul id="myTabs" class="tab-section-tab nav nav-pills nav-justified" role="tablist" data-tabs="tabs">
		                            <li class="active"><a href="#Commentary" data-toggle="tab">Januvia</a></li>
		                            <li><a href="#Videos" data-toggle="tab">Xarelto</a></li>
		                            <li>
		                                <a href="#Events" data-toggle="tab">
		                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
		                                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
		                                    </svg>
		                                </a>
		                            </li>
		                        </ul>
		                        <div class="tab-content">
		                            <div role="tabpanel" class="tab-pane fade in active" id="Commentary">
		                               <!--  <form> -->
		                                    <div class="col-sm-6 w-100 p-l-0">
		                                        <div class="form-group">
		                                            <input type="text" class="form-control" id="exampleInputName" placeholder="Healthcare Provider First Name*">
		                                        </div>
		                                    </div>
		                                    <div class="col-sm-6 w-100 p-r-0">
		                                        <div class="form-group">
		                                            <input type="text" class="form-control" id="exampleInputName" placeholder="Healthcare Provider Last Name*">
		                                        </div>
		                                    </div>
		                                    <div class="col-sm-6 w-100 p-l-0">
		                                        <div class="form-group">
		                                            <input type="text" class="form-control" id="exampleInputName" placeholder="Suite Number*">
		                                        </div>
		                                    </div>
		                                    <div class="col-sm-6 w-100 p-r-0">
		                                        <div class="form-group">
		                                            <input type="text" class="form-control" id="exampleInputName" placeholder="City*">
		                                        </div>
		                                    </div>
		                                    <div class="add-provider-section">
		                                        <a href="#" class="provider_add">
		                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
		                                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
		                                            </svg>
		                                            <span>Add Medication</span>
		                                        </a>
		                                    </div>
		                                    <div class="well">
		                                        <p>You are requesting 1 medication through Prescription Hope.
		                                            In the event you are pre-approved for all of the medications you are requesting, your monthly total will be $50.00.
		                                        </p>
		                                    </div>
		                                    <button type="submit" class="btn btn-primary btn-block">NEXT</button>
		                                <!-- </form> -->
		                            </div>
		                        </div>
		                        <div role="tabpanel" class="tab-pane fade" id="Videos">Videos WP_Query goes here.</div>
		                        <div role="tabpanel" class="tab-pane fade" id="Events">Events WP_Query goes here.</div>
		                    </div> */?>
		                </div>
		            </div>
		        </div>
	    	
		        <div class="register-form-tab tab-pane" id="settings-u" style="display:none;">
		            <div class="tab-1">
		                <div class="col-sm-5">
		                    <div class="heading-title">
		                        <h2>Payment Information Dummy</h2>
		                        <p>Once your payment information is submitted, we will immediately begin working on your behalf to get you approved for your requested medications through the applicable patient assistance program.
		                        </p>
								
		                        <p><strong>NOTE:</strong> We are working diligently to complete your 
		                            medication order, however, it may take up to 6 weeks to receive your first supply of medication. During this time, we will be collecting the information we need from your healthcare provider and any additional information we may need from you to process your order. Please make sure you have enough medication to get you through this initial process.
		                        </p>
		                    </div>
		                </div>
		                <div class="col-sm-7 card-form">	
		                	<div class="form-group">
	                            <div class="full-width m-v-10">
	                            	<input autocomplete="nope" type="text" value="1" class="patient-enroll-progress-7" data-step-enroll="7">
	                            	
	                            </div>
	                        </div>
		                </div>
		            </div>
		        </div>        	

	    	</div>
	    	
	    </form>	
     </div>
	</div>
</div>
</div>
<div class="clearfix"></div>
</div>
 <script>
	/*$('.mobile-view').click(function (){
	 	$("#sidebar-wrapper").show();
	 	if (("#sidebar-wrapper").length == '') {		  
		 var sidebar_val = $(this).after('<div class="col-xs-3 sidebar-tabs bg-light border-right custom-wrapper" id="sidebar-wrapper"> \
	 		<a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a> \
	 		<div class="logo">  <a href="/" class="svg"> \
            <object data="/images/ph-logo.svg" type="image/svg+xml" width="241" height="90" class="top-menu-only-desktop only-desktop" style="margin-top: 20px;"><img src="../dist/prescription-hope-logo.png" class="header-logo left top-menu-only-desktop only-desktop"></object> \
            </a> \
        	</div> \
        	<h2 class="title-section">Enrollment Progress</h2> \
        	<ul class="nav nav-tabs tabs-left sideways" id="enroll-progress"> \
            <li class="active" id="patient-enroll-progress_1" data-step-enroll="1"><a href="#home-v" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">1</span> <span class="glyphicon glyphicon-ok"></span> Patient Address</a></li> \
            <li id="patient-enroll-progress_2" data-step-enroll="2"><a href="#profile-v" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">2</span> <span class="glyphicon glyphicon-ok"></span> Patient Information</a></li> \
            <li id="patient-enroll-progress_3" data-step-enroll="3"><a href="#messages-v" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">3</span> <span class="glyphicon glyphicon-ok"></span> Monthly Household Income Information</a></li> \
            <li id="patient-enroll-progress_4" data-step-enroll="4"> \
                <a href="#settings-r" data-toggle="tab" class="list-group-item list-group-item-action bg-light"> \
                    <span class="index-span">4</span> <span class="glyphicon glyphicon-ok"></span> Healthcare Provider Information</a> \
            </li> \
            <li id="patient-enroll-progress_5" data-step-enroll="5"><a href="#settings-s" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">5</span> <span class="glyphicon glyphicon-ok"></span> Medication Information</a></li> \
            <li id="patient-enroll-progress_6" data-step-enroll="6"><a href="#settings-t" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">6</span> <span class="glyphicon glyphicon-ok"></span>  Payment Information</a></li> \
            <li id="patient-enroll-progress_7" data-step-enroll="7" style="display:none;"><a href="#settings-u" data-toggle="tab" class="list-group-item list-group-item-action bg-light"><span class="index-span">7</span> <span class="glyphicon glyphicon-ok"></span>  Payment Information Dummy</a></li> \
            <li><button class="btn btn-primary" id="final-submit" type="button" data-final-submit="1" data-step-enroll="6" style="display:none;">Submit</button></li> \
        </ul> \
	 	</div>');

		 //saveSidebarData(sidebar_val);
		} else {

		}
	 	
	});
	$('.sidebar-closed').click(function() {
	    $("#sidebar-wrapper").hide();
		var cloneDta = $( "#sidebar-wrapper" ).clone().insertAfter('div.mobile-view'); 
		console.log('cloneDta:',cloneDta);

	}); */
	$("#specs-mobile-view").click(function () {
	   $("#sidebar-wrapper").show(); 
	})

	$('.sidebar-closed').click(function() {

	//$('#sidebar-wrapper').addClass('expand');
$("#sidebar-wrapper").hide();

})

  </script>
<!-- partial -->
<?php include('ph_footer.php'); ?>
<script type="text/javascript">

	$(function() {
		if (jQuery('input[name=p_medicare_part_d]:checked').val() == 1) {
			console.log('All set.');
			jQuery('.p_medicare_part_d_2nd').show();
			jQuery('.p_coveragegapyes_2nd').show();
		}
		if($('.nav-tabs li').hasClass('active')) {
		  var thisLi = $('.nav-tabs li.active').attr('id');
		  console.log('I have click 1', thisLi);
		  if(!$(this).hasClass('completed')) {
		  	$('.nav-tabs li:not(.active)').addClass('disabled').find('a').removeAttr("data-toggle");
		  }	
		  if(thisLi == "patient-enroll-progress_6") {
		  	jQuery(".nav-tabs li").removeClass("disabled");
		  }
		}

		$('.nav-tabs li').on('click', function(){
		    console.log('Current class:',$(this.id));
		    $(this).addClass('active').removeClass('completed disabled').nextAll('li').removeClass('active completed');
		    if(!$(this).hasClass('completed')) {
		    	console.log('In side completed check');
		    	$('.nav-tabs li:not(.active,.completed)').addClass('disabled').find('a').removeAttr("data-toggle");
		    }
		    var currentLiId = $(this).find('a').attr('href');
		    console.log('currentLiId', currentLiId); 
		    $('.register-form-tab').removeClass('active'); //Remove active class form all  tab
		    $(currentLiId).addClass('active'); //Show current tab
		    //return false;	    

		    var getParentLiId = $('a[href="'+currentLiId+'"]').parent().attr('id'); 
		    if(!$('#'+getParentLiId).hasClass('completed')) {
		    	$("#final-submit").hide();
			    if(!$('li.final-submit-li').hasClass('disabled')) {			    	
			    	$('li.final-submit-li').addClass('disabled');
			    }
		    } else {
		    	$("#patient-enroll-progress_6").removeClass('active').addClass('completed');
				$("#final-submit").show();
				$('li.final-submit-li').removeClass('disabled');// Show submit button	
		    }
		    //Close navigation
		    $("#sidebar-wrapper").hide();

		});
		$('.patient-enroll-progress-6').on('blur change',blurChange);	

	});
	function blurChange(e){
	    clearTimeout(blurChange.timeout);
	    blurChange.timeout = setTimeout(function(){
	        // Your event code goes here.
	        if(!$("#register_form").valid()){
			    $("#final-submit").hide();
			    if(!$('li.final-submit-li').hasClass('disabled')) {			    	
			    	$('li.final-submit-li').addClass('disabled');
			    	$("#patient-enroll-progress_6").addClass('active').removeClass('completed');
			    }
			    return false;
			} else {
				$("#patient-enroll-progress_6").removeClass('active').addClass('completed');
				$("#final-submit").show();
				$('li.final-submit-li').removeClass('disabled');// Show submit button
			}
	    }, 100);
	}

	
	$('.medication-next').on('click', function(){
		checkErrorForMedication();
	});	

	$('.next').on('click', function(){
		if(!$("#register_form").valid()){
		    console.log('invalid');
		    return false;
		} else {
			//add not-empty and correct class for all input fields
			jQuery('#register_form input:not(:button),#register_form select').each(function (index, elem) {
					if (jQuery(elem).is(':radio')) {
						var radioName = jQuery(elem).attr('name');
						if($("input:radio[name='"+radioName+"']").is(":checked") && (jQuery(elem).val() != '' && jQuery(elem).val() != 0)) {
							jQueryValidation_Unhighlight(elem);
						}
					
					} else {
						jQueryValidation_Unhighlight(elem);
					}
				refreshRadioGroupsValidationIcons();
			});


  			$('.tabs-left > .active').next('li').find('a').trigger('click');
  			$('.tabs-left > .active').prevAll().addClass('completed');
		  	$('.tabs-left > .active').removeClass('completed');
		  	$('.tabs-left > .active').nextAll().removeClass('completed');

		  	var next = jQuery('.nav-tabs > .active').next('li');
      		var prev = jQuery('.nav-tabs > .active').prev('li');
      		var nextId 		= $(next).attr('id');
      		var nextATag 	= $(next).find('a').attr('href');
      		$('#'+nextId).addClass('active').prev('li').removeClass('active').addClass('completed');
      		//$('#'+nextATag).addClass('active').prev('a').removeClass('active');
      		$(nextATag).addClass('active').prev('.register-form-tab').removeClass('active');

			//$(this).prevAll().removeClass('patient-enroll-progress-1');		  	
			var getClosedTabPaneId = $(this).closest('.register-form-tab').attr('id');
			//alert(getClosedTabPaneId);
			var getTargetId 	= $("a[href=#"+getClosedTabPaneId+"]").parent('li').removeClass('alert-danger'); // current tab
			console.log('This ki value:',nextATag);
			if(nextATag == "#settings-t") {
				if($(nextATag).find(':input').hasClass('not-empty') && !$(nextATag).find(':input').hasClass('error')) {
					  $('#'+nextId).removeClass('active').addClass('completed');
					  $('li.final-submit-li').removeClass('disabled');// Show submit button	
					  $('li.final-submit-li').addClass('final-sub-btn').removeClass('disabled');// Show submit button
				      $("#final-submit").show();
				  }	
			}
					
		}
		return true;
	});
	$('.back').click(function(){
	  $('.tabs-left > .active').prev('li').find('a').trigger('click');
	});
	//Adding js if user clicked on sidebar tab to swithc any tab and we show the error if not select requied field
	$('a[data-toggle="tooltip"]').tooltip({
	    animated: 'fade',
	    placement: 'bottom',
	    html: true
	});
	//$('a[data-toggle="tooltip"]').tooltip('show');
	$( ".show-cvv-img" ).tooltip({ content: '<img src="https://prescriptionhope.com/html/enrollment/images/cvv4.jpg" />' });
</script>
<style type="text/css">
	.container.footer ul li:last-child {
   display: block !important;
}
p.copy {
   color: #9099A0;
   font-family: 'Poppins', sans-serif !important;
   font-size: 13px;
   letter-spacing: 0;
   line-height: 23px;
}
.tooltip-inner img{
	width : 370px;
}
.hideform {
	display: none;
}
</style>