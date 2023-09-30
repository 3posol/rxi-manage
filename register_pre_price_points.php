<?php

function get_agent_name ($agent_code) {
	//get data from the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/get_broker_agent_name.php",
		//CURLOPT_URL => "http://localhost/phope/webservice/get_broker_agent_name.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'agent=' . $agent_code,
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);

	return ($response != '') ? ' - ' . $response : '';
}

ini_set('session.cookie_domain', '.prescriptionhope.com');

session_start();

if (!isset($_POST['bSubmit'])) {
	//reset session id - to make sure somebody from the same computer didn't submitted an application before
	//				   - no more overwrites because of the same session id
	session_regenerate_id();
}

if (isset($_GET['source'])) {
	$_SESSION['my_source'] = $_GET['source'];
	$_SESSION['register_data']['p_application_source'] = $_GET['source'];
	$_SESSION['register_data']['p_hear_about'] = $_GET['source'];
}

if (isset($_GET['website'])) {
	if ($_GET['website'] == 1) {
		header("Location: https://prescriptionhope.com?hide_pdf=1");
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

//if (isset($_SESSION['register_data']['p_hear_about']) ) {
//	if(!isset($_GET['source'])) {
//		$_GET['source'] = $_SESSION['register_data']['p_hear_about'];
//	}
//}

//blank data
$data_request = array(
	'p_application_source'		=> array(false, 1),
	'p_first_name'				=> array(true, 1),
	'p_middle_initial'			=> array(true, 1),
	'p_last_name'				=> array(true, 1),
	'p_is_minor'				=> array(true, 1),
	'p_parent_first_name'		=> array(false, 1),
	'p_parent_middle_initial'	=> array(false, 1),
	'p_parent_last_name'		=> array(false, 1),
	'p_parent_phone'			=> array(false, 1),
	'p_address'					=> array(true, 1),
	'p_city'					=> array(true, 1),
	'p_state'					=> array(true, 1),
	'p_zip'						=> array(true, 1),
	'p_phone'					=> array(true, 1),
	'p_fax'						=> array(false, 1),
	'p_email'					=> array(false, 1),
	'p_alternate_contact_name' 	=> array(false, 1),
	'p_alternate_phone' 		=> array(false, 1),
	'p_dob'						=> array(true, 2),
	'p_gender'					=> array(true, 2),
	'p_ssn'						=> array(true, 2),
	'p_household' 				=> array(true, 2),
	'p_married' 				=> array(true, 2),
	'p_employment_status' 		=> array(true, 2),
	'p_uscitizen' 				=> array(true, 2),
	'p_disabled_status' 		=> array(true, 2),
	'p_medicare' 				=> array(true, 2),
	'p_medicare_part_d' 		=> array(false, 2),
	'p_medicaid' 				=> array(true, 2),
	'p_medicaid_denial'			=> array(false, 2),
	'p_lis' 					=> array(true, 2),
	'p_lis_denial'				=> array(false, 2),
	'p_hear_about' 				=> array(true, 2),
	'p_income_salary' 			=> array(false, 2),
	'p_income_unemployment' 	=> array(false, 2),
	'p_income_pension' 			=> array(false, 2),
	'p_income_annuity' 			=> array(false, 2),
	'p_income_ss_retirement' 	=> array(false, 2),
	'p_income_ss_disability' 	=> array(false, 2),
	'p_income_zero'			 	=> array(false, 2),
	'p_income_file_tax_return'	=> array(false, 2),
	'p_payment_agreement' 		=> array(true, 4),
	'p_service_agreement' 		=> array(true, 4),
	'p_guaranty_agreement' 		=> array(true, 4),
	'p_payment_method'			=> array(true, 4),
	'p_cc_type' 				=> array(false, 4),
	'p_cc_number' 				=> array(false, 4),
	'p_cc_exp_month' 			=> array(false, 4),
	'p_cc_exp_year' 			=> array(false, 4),
	'p_cc_cvv' 					=> array(false, 4),
	'p_ach_holder_name' 		=> array(false, 4),
	'p_ach_routing' 			=> array(false, 4),
	'p_ach_account'				=> array(false, 4),
	'p_acknowledge_agreement' 	=> array(true, 4)
);

//initialize data
$data = array_fill_keys(array_keys($data_request), '');
$data['p_income_salary'] = 0.00;
$data['p_income_unemployment'] = 0.00;
$data['p_income_pension'] = 0.00;
$data['p_income_annuity'] = 0.00;
$data['p_income_ss_retirement'] = 0.00;
$data['p_income_ss_disability'] = 0.00;
//
$doctor_data = array('doctor_first_name', 'doctor_last_name', 'doctor_facility', 'doctor_address', 'doctor_address2', 'doctor_city', 'doctor_state', 'doctor_zip', 'doctor_phone', 'doctor_fax');
$data['doctors'] = array(array_fill_keys($doctor_data, ''));
//
$medication_data = array('medication_doctor', 'medication_name', 'medication_strength', 'medication_frequency');
//$data['medication'] = array(array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''));
$data['medication'] = array(array_fill_keys($medication_data, ''));

$invalid_cc = false;
$valid_form = true;
$form_submitted = false;

if (!isset($_POST['bSubmit'])) {
	//save blank data into session
	//$data['p_hear_about'] = (isset($_GET['source'])) ? trim(urldecode($_GET['source'])) : '';
	$data['p_hear_about'] = (isset($_SESSION['my_source'])) ? trim(urldecode($_SESSION['my_source'])) . get_agent_name(substr($_SESSION['my_source'], 0, 9)) : '';
	$data['p_application_source'] = (isset($_SESSION['my_source'])) ? trim(urldecode($_SESSION['my_source'])) : '';
} else {
	//form submitted

	//
	//get form data
	//

	$tmp_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_MAGIC_QUOTES);
	foreach ($tmp_data as $tmp_data_key => $tmp_data_value) {
		$data[$tmp_data_key] = trim($tmp_data_value);
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

	//save data into session
	$_SESSION['register_data'] = $data;

	//
	//validate data
	//

	foreach ($data_request as $data_key => $data_info) {
		if ($data_info[0] && $data[$data_key] == '') {
			//missing data
			$valid_form = false;
		}
	}

	//doctors
	if (count($data['doctors']) == 0) {
		$valid_form = false;
	} else {
		foreach ($data['doctors'] as $doctor) {
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
	if ($data['p_payment_method'] == 'cc') {
		if ($data['p_cc_type'] == '' || $data['p_cc_number'] == '' || $data['p_cc_exp_month'] == '' || $data['p_cc_exp_year'] == '' || $data['p_cc_cvv'] == '') {
			$valid_form = false;
		}
		//card expiration date should be in the future
		elseif (sprintf('%d-%02d-01', $data['p_cc_exp_year'], $data['p_cc_exp_month']) < date('Y-m-01')) {
			$valid_form = false;
		}
		//validate credit card
		else {
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
				CURLOPT_URL => "http://64.233.245.241:43443/ccvalidation.php",
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($cc_data),
				CURLOPT_RETURNTRANSFER => true
			));

			$response = curl_exec($cu);
			curl_close($cu);

			if ($response == 'ERROR') {
				$invalid_cc = true;
				$valid_form = false;
			}
		}
	} elseif ($data['p_payment_method'] == 'ach') {
		if ($data['p_ach_holder_name'] == '' || $data['p_ach_routing'] == '' || $data['p_ach_account'] == '') {
			$valid_form = false;
		}
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

		//prepare to encrypt CC / ACH data
		$key = pack('H*', md5($data['p_first_name'].$data['p_last_name']));
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		$data['p_payment_method']	= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_payment_method'], MCRYPT_MODE_CFB, $iv));
		$data['p_cc_type']			= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_cc_type'], MCRYPT_MODE_CFB, $iv));
		$data['p_cc_number']		= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_cc_number'], MCRYPT_MODE_CFB, $iv));
		$data['p_cc_exp_month']		= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_cc_exp_month'], MCRYPT_MODE_CFB, $iv));
		$data['p_cc_exp_year']		= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_cc_exp_year'], MCRYPT_MODE_CFB, $iv));
		$data['p_cc_cvv']			= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_cc_cvv'], MCRYPT_MODE_CFB, $iv));
		$data['p_ach_holder_name']	= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_ach_holder_name'], MCRYPT_MODE_CFB, $iv));
		$data['p_ach_routing']		= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_ach_routing'], MCRYPT_MODE_CFB, $iv));
		$data['p_ach_account']		= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_ach_account'], MCRYPT_MODE_CFB, $iv));

		//add the iv to the data array
		$data['p'] = base64_encode($iv);

		$data['incomplete_step'] = $step;
		$data['session_id'] = session_id();

	    //send data to the server
		$cu = curl_init();

		curl_setopt_array($cu, array(
			CURLOPT_URL => "http://64.233.245.241:43443/register_application_all_data.php",
			//CURLOPT_URL => "http://localhost/hope/vpn/webservice/register_application.php",
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => http_build_query($data),
			CURLOPT_RETURNTRANSFER => true
		));

		$response = curl_exec($cu);
		curl_close($cu);

		$response_success = false;
		switch ($response) {
			case 'SUCCESS':
				$response_success = true;
				$response_msg = '<p class="moduleSubheader">Enrollment Form Submitted Successfully.</p>';
				$response_msg .= '<p class="moduleSubheader">Thank you for submitting an Enrollment Form to Prescription Hope.  A Patient Advocate will review your form and begin your Enrollment process.  You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>';
				$response_msg .= '<br/><p class="moduleSubheader">To print a copy of your Enrollment Form for your records.<br/><br/><span class="small-button-orange"><a href="save_application.php" class="skipLeave" target="_blank">Click here</a></span></p>';
				//$response_msg .= '<br/><p class="moduleSubheader">To get a copy of your Enrollment Form for your records through email.<br/><br/><span class="small-button-orange" id="app_email_link"><a href="save_application.php?method=email" id="app_email_send" class="skipLeave">Click here</a></span></p>';
				//$response_msg .= '<br/><p class="moduleSubheader"><a href="save_application.php">.</a><a href="save_application.php?method=email">.</a></p>';

				//send PDF app through email
				if (isset($data['p_email']) && trim($data['p_email']) != '') {
					$data['p_payment_method']	= $_SESSION['register_data']['p_payment_method'];
					$data['p_cc_type']			= $_SESSION['register_data']['p_cc_type'];
					$data['p_cc_number']		= $_SESSION['register_data']['p_cc_number'];

					require_once('fpdf/fpdf.php');
					require_once('fpdf/fpdi/fpdi.php');
					require_once('phpmailer/class.phpmailer.php');
					require_once('pdf_application.php');

					$mail = new PHPMailer(); // defaults to using php "mail()"
					$mail->CharSet = 'UTF-8';
					$mail->Encoding = "base64";
					$mail->isHTML(true);

					$mail->SetFrom('DoNotReply@prescriptionhope.com', 'Prescription Hope');
					$mail->AddReplyTo("DoNotReply@prescriptionhope.com","Prescription Hope");

					$mail->AddAddress($data['p_email'], (isset($data['p_first_name']) && isset($data['p_last_name'])) ? ($data['p_first_name'] . ' ' . $data['p_last_name']) : '');

					$mail->Subject = "Online Application Confirmation";

					$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>THIS IS AN AUTOMATED EMAIL - PLEASE DO NOT RESPOND TO THIS MESSAGE AS IT IS NOT CHECKED</p>";
					$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Thank you for submitting an Enrollment Form to Prescription Hope. A Patient Advocate will review your form and begin your Enrollment process. You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>";
					$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Attached to this email you'll find a copy of your Enrollment Form for your records.</p>";
					$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'><strong>Please Note:</strong> If you opted for a Checking Account payment, please submit a voided check to us through mail or through fax.</p>";

					$mail->Body .= "<br/>";
					$mail->Body .= "<table style='font-family: Arial, sans-serif; font-size: 12px;'><tr><td valign='top'><strong>Mail:</strong><br/>Prescription Hope, Inc.<br/>PO Box 2700<br/>Westerville, OH 43086</td><td width='50'>&nbsp;</td><td valign='top'><strong>Fax:</strong><br/>1-877-298-1012</td></tr></table>";
					$mail->Body .= "<br/>";

					$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Sincerely,</p>";
					$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Prescription Hope, Inc.<br/>P: 877.296.4673<br/>F: 877.298.1012<br/><a href='http://www.prescriptionhope.com'>www.prescriptionhope.com</a></p>";

					$mail->AddEmbeddedImage('images/logo_41.png', 'ph_logo');
					$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'><img src='cid:ph_logo' width='150' alt='Prescription Hope Logo'></p>";

					$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>CONFIDENTIALITY NOTICE: This is an e-mail transmission and the information is privileged and/or confidential. It is intended only for the use of the individual or entity to which it is addressed. If you have received this communication in error, please notify the sender at the reply e-mail address and delete it from your system without copying or forwarding it. If you are not the intended recipient, you are hereby notified that any retention, distribution, or dissemination of this information is strictly prohibited. Thank you.</p>";

					$mail->AddStringAttachment(pdf_application_new($data, 'S'), 'Prescription_Hope_Application.pdf');

					$mail->Send();
				}

				//clear the session data
				//unset($_SESSION['register_data']);
				break;

			case 'DUPLICATE':
				$response_msg = '<p class="moduleSubheader">Operation canceled - there is already a patient with the same SSN in our system.</p>';
				break;

			case 'DBERROR':
				$response_msg = '<p class="moduleSubheader">Operation failed - database server error.</p>';
				break;

			default:
				$response_msg = '<p class="moduleSubheader">Operation failed.</p>';
				$response_msg .= '<p class="moduleSubheader">Server Message: ' . var_export($response, true) . '</p>';
				break;
		}

		if ($response_success === false) {
			//save data locally
			$file = 'apps/' . $data['session_id'];
			foreach (array('register_step', 'bNextStep', 'session_id') as $skip_key) {
				unset($data[$skip_key]);
			}
			file_put_contents($file, json_encode($data));
		} else {
			//log the success apps
			$file = 'apps/ok-' . date('Y-m-d-') . $data['session_id'];
			$data_tmp = $data;
			foreach (array('p_payment_method', 'p_cc_type', 'p_cc_number', 'p_cc_exp_month', 'p_cc_exp_year', 'p_cc_cvv', 'p_ach_holder_name', 'p_ach_routing', 'p_ach_account', 'register_step', 'bNextStep', 'session_id') as $skip_key) {
				unset($data_tmp[$skip_key]);
			}
			$data_tmp['response'] = $response;
			file_put_contents($file, json_encode($data_tmp));
		}

		$form_submitted = true;
	}

}

?>

<?php include('_header.php'); ?>

<div class="container-fluid">
	<div id="enroll-now" class="row row-1 one_column light-grey text-left" style='z-index:1000;'>
		<div class="container">
			<div id='applicationForm'>
				<?php if (!$form_submitted) { ?>
					<!-- ENROLLMENT FORM -->
					<script type="text/javascript">

						jQuery().ready(function() {
							jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
							jQuery.validator.addMethod("custom_date",function(t,e){return t=t.replace(/\s+/g,""),td=t.split("/"),td=td[2]+"-"+td[0]+"-"+td[1],this.optional(e)||t.length>8&&t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/)&&td<=new Date().toISOString().substring(0,10)},"Please specify a valid date (mm/dd/yyyy)"),
							jQuery.validator.addMethod("SSN",function(t,e){return t=t.replace(/\s+/g,""),this.optional(e)||t.length>8&&t.match(/^\d{3}-?\d{2}-?\d{4}$/)},"Please specify a valid SSN number"),

							//show the complete patient profile, if preloaded
							showSelectedPatientProfile();

							//show/hide the 2nd-level insurance questions
							show2ndLevelInsuranceQuestions(false);

							//show the payment method details, if preloaded
							showSelectedPaymentMethods();

							//show doctors if any exists
							UpdateDoctorsList();

							//load radio buttons and select objects with the correct value
							preloadSpecialFormValues();

							<?php if ($invalid_cc) { ?>InvalidCreditCard();<?php } ?>

							//activate form validation
							jQuery("#register_form").validate({
								rules: {
									p_first_name:				{ required: true, ascii: true },
									p_middle_initial: 			{ required: true, ascii: true, maxlength: 1 },
									p_last_name:				{ required: true, ascii: true },
									p_is_minor:					{ required: true, ascii: true },
									p_parent_first_name:		{ required: jQuery("#p_is_minor_yes"), ascii: true },
									p_parent_middle_initial: 	{ required: jQuery("#p_is_minor_yes"), ascii: true , maxlength: 1 },
									p_parent_last_name:			{ required: jQuery("#p_is_minor_yes"), ascii: true },
									p_parent_phone:				{ required: jQuery("#p_is_minor_yes"), phoneUS: true },
									p_address: 					{ required: true, ascii: true },
									p_city: 					{ required: true, ascii: true },
									p_state: 					{ required: true },
									p_zip: 						{ required: true, digits: true, minlength: 5, maxlength: 5 },
									p_phone: 					{ required: true, phoneUS: true },
									p_fax: 						{ required: false, ascii: true/*, phoneUS: true*/ },
									p_email: 					{ required: false, email: true },
									p_alternate_contact_name: 	{ required: false, ascii: true },
									p_alternate_phone: 			{ required: false, ascii: true/*, phoneUS: true*/ },

									p_dob: 						{ required: true, custom_date: true },
									p_gender: 					{ required: true },
									p_ssn: 						{ required: true, SSN: true, minlength: 9, maxlength: 11 },
									p_household: 				{ required: true, digits: true },
									p_married: 					{ required: true },
									p_employment_status: 		{ required: true },
									p_uscitizen: 				{ required: true },
									p_disabled_status: 			{ required: true },
									p_medicare:					{ required: true },
									p_medicare_part_d: 			{ required: jQuery("#p_medicare_yes") },
									p_medicaid: 				{ required: true },
									p_medicaid_denial:			{ required: jQuery("#p_medicaid_yes") },
									p_lis: 						{ required: true },
									p_lis_denial: 				{ required: jQuery("#p_lis_yes") },
									p_hear_about: 				{ required: true, ascii: true },

									p_income_salary:			{ required: false, number: true },
									p_income_unemployment:		{ required: false, number: true },
									p_income_pension:			{ required: false, number: true },
									p_income_annuity:			{ required: false, number: true },
									p_income_ss_retirement:		{ required: false, number: true },
									p_income_ss_disability:		{ required: false, number: true },
									p_income_zero:				{ required: { depends: function(element) {
																	                    return (jQuery('input[name=p_income_salary]').val() == 0 &&
																	                            jQuery('input[name=p_income_unemployment]').val() == 0 &&
																	                            jQuery('input[name=p_income_pension]').val() == 0 &&
																	                            jQuery('input[name=p_income_annuity]').val() == 0 &&
																	                            jQuery('input[name=p_income_ss_retirement]').val() == 0 &&
																	                            jQuery('input[name=p_income_ss_disability]').val() == 0);
					                							}}},

									p_payment_agreement:		{ required: true },
									p_service_agreement:		{ required: true },
									p_guaranty_agreement: 		{ required: true },

									p_payment_method:			{ required: true },

									p_cc_type:					{ required: jQuery("#p_payment_method_cc") },
									p_cc_number:				{ required: jQuery("#p_payment_method_cc"), creditcardtypes: function(element) {return {visa: (jQuery('select[name=p_cc_type]').val() == "Visa"), mastercard: (jQuery('select[name=p_cc_type]').val() == "Mastercard")};}},
									p_cc_exp_month:				{ required: jQuery("#p_payment_method_cc"), min: function(element) {return (jQuery('select[name=p_cc_exp_year]').val() != '<?php echo date('Y');?>') ? '01' : '<?php echo date('m');?>';}},
									p_cc_exp_year:				{ required: jQuery("#p_payment_method_cc"), min: function(element) {return (jQuery('select[name=p_cc_exp_month]').val() < '<?php echo date('m');?>') ? '<?php echo (int)date('Y')+1;?>' : '<?php echo date('Y');?>';}},
									p_cc_cvv:					{ required: jQuery("#p_payment_method_cc"), digits: true, minlength: 3, maxlength: 3 },

									p_ach_holder_name:			{ required: jQuery("#p_payment_method_ach"), ascii: true },
									p_ach_routing:				{ required: jQuery("#p_payment_method_ach"), digits: true, maxlength: 9 },
									p_ach_account:				{ required: jQuery("#p_payment_method_ach"), digits: true },

									p_acknowledge_agreement: 	{ required: true }
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

								errorPlacement: jQueryValidation_PlaceErrorLabels
							});

							jQuery("input[type='radio']").keypress(function(e){
							    if(e.keyCode === 13) {
						    	    jQuery(this).attr("checked", 'checked');
							        return false;
							    }
							});

							jQuery('input[name=p_dob]').change(function() {
								dob = jQuery(this).val();
								if (getAge(dob) < 18) {
									jQuery('input#p_is_minor_yes').trigger('click');
								} else {
									jQuery('input#p_is_minor_no').trigger('click');
								}
							})

							jQuery("input[name=p_is_minor]").change(showSelectedPatientProfile);

							jQuery("input[name='p_medicare'], input[name=p_medicaid], input[name=p_lis]").change(function(e) {show2ndLevelInsuranceQuestions(e);});

							jQuery('input#p_income_zero').change(function() {
								if (jQuery(this).is(':checked')) {
									jQuery('input.input_zero').val('0.00');
								}
							});

							jQuery('input.input_zero').change(function() {
								updateZeroIncome();
							});
							//
							jQuery('input.input_zero').focus(function() {
								val = jQuery(this).val() - 0;
								if (val == 0) {
									jQuery(this).val('');
								}
							});
							//
							jQuery('input.input_zero').blur(function() {
								val = jQuery(this).val() - 0;
								if (val == 0) {
									jQuery(this).val('0.00');
								}
							});

							//add masks
							jQuery("input[name='p_parent_phone']").mask("999-999-9999");
							jQuery("input[name='p_phone']").mask("999-999-9999");
							jQuery("input[name='p_fax']").mask("999-999-9999");
							jQuery("input[name='p_alternate_phone']").mask("999-999-9999");
							jQuery("input[name='p_dob']").mask("99/99/9999");
							jQuery("input[name='p_ssn']").mask("999-99-9999");
							jQuery("input[name='dr_zip']").mask("99999");
							jQuery("input[name='dr_phone']").mask("999-999-9999");
							jQuery("input[name='dr_fax']").mask("999-999-9999");

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

							jQuery('#bAddANewDoctor').click(AddANewDoctor);
							jQuery('.dr-data').change(UpdateDoctor);
							jQuery('.dr-data').blur(UpdateDoctor);

							jQuery('#bAddANewMedication').click(AddANewMedication);
							//jQuery('.med-data').change(UpdateMedication);

							//
							jQuery("input[name=p_payment_method]").change(showSelectedPaymentMethods);

							//form submit
							//jQuery('form#register_form').submit(scrollToInvalidFormElements);

							jQuery("form#register_form").submit(function(e) {
								if (jQuery(this).valid()) {
									//doctors and meds
									hasDoctors = false;
									for(dr_id = 1; dr_id <= jQuery('input.doctor-fields').length; dr_id++) {
										if (jQuery('input[name="doctor_first_name[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_last_name[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_address[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_city[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_state[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_zip[' + dr_id + ']"]').val() != '' && jQuery('input[name="doctor_phone[' + dr_id + ']"]').val() != '') {
											hasDoctors = true;
										}
									}

									//validate medication rows
									hasMeds = false;
									medsWithErrors = false;
									first_invalid_row = false;

									for(i = 1; i <= parseInt(jQuery('.med-data').length / 4); i++) {
										m_medication = jQuery("input[name='medication_name[" + i + "]']").val();
										m_strength = jQuery("input[name='medication_strength[" + i + "]']").val();
										m_frequency = jQuery("input[name='medication_frequency[" + i + "]']").val();
										m_doctor = jQuery("select[name='medication_doctor[" + i + "]']").val();

										//if there is any info on this line, then check if we have all the required information
										if (m_medication || m_strength || m_frequency || m_doctor) {
											mn_error = "<label class='error nopad-error'>" + ((!m_medication) ? "Required field" : ((!isAscii(m_medication)) ? "Invalid characters" : "&nbsp;")) + "</label>";
											ms_error = "<label class='error nopad-error'>" + ((!m_strength) ? "Required field" : ((!isAscii(m_strength)) ? "Invalid characters" : "&nbsp;")) + "</label>";
											mf_error = "<label class='error nopad-error'>" + ((!m_frequency) ? "Required field" : ((!isAscii(m_frequency)) ? "Invalid characters" : "&nbsp;")) + "</label>";
											md_error = "<label class='error nopad-error'>" + ((!m_doctor) ? "Required field" : ((!isAscii(m_doctor)) ? "Invalid characters" : "&nbsp;")) + "</label>";

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

									if (hasDoctors && hasMeds && !medsWithErrors) {
										return true;
									} else {
										if (!hasMeds || medsWithErrors) {
											//jQuery("#medication_form").before("<label class='error' style='width: auto !important; padding: 0; font-size: 16px;'>Please add at least one medication.<br/><br/></label>");
											//
											//jQuery(window).scrollTop(jQuery("#medication_form").position().top - 250);
											//jQuery(window).scrollLeft(0);

											if (!medsWithErrors) {
												jQuery("#medication_form").after("<label class='error nopad-error'>Please add at least one medication.</label>");
												jQuery("input[name='medication_name[1]']").focus();

												first_invalid_row = jQuery("input[name='medication_name[1]']");
											}

											if (first_invalid_row !== false) {
												jQuery(window).scrollTop(first_invalid_row.position().top - 250);
												jQuery(window).scrollLeft(0);
											}
										}

										if (!hasDoctors) {
											jQuery("#doctor_form").before("<label class='error' style='width: auto !important; padding: 0; font-size: 16px;'>Please add at least one doctor.<br/><br/></label>");

											jQuery(window).scrollTop(jQuery("#doctor_form").position().top - 250);
											jQuery(window).scrollLeft(0);
										}

										return false;
									}
								} else {
									scrollToInvalidFormElements();
								}
							});

							//tooltips
							jQuery("a[rel]").hover(
								function(e) {
									if (jQuery(this).attr("rel") != "") {
										pos = jQuery(this).position();

										if (jQuery(this).attr("rel").substring(0, 7) == 'images/') {
											jQuery("#register_form").append("<p class='tooltips'><img src='"+ jQuery(this).attr("rel") +"' /></p>");
										} else {
											jQuery("#register_form").append("<p class='tooltips'>" + jQuery(this).attr("rel") + "</p>");
										}

										jQuery(".tooltips")
											.css("top", (pos.top - 15) + "px")
											.css("left", (pos.left + 30) + "px")
											.fadeIn("fast");
									}
								},
								function() {
									if (jQuery(this).attr("rel") != "") {
										jQuery(".tooltips").remove();
									}
								}
							);

							//reminder
							jQuery("#btReminderClose").click(function(event) {
								event.preventDefault();
								jQuery('#reminderPopup').addClass("no-show");
							});

							//disable "Go" button on Android to submit the form
							jQuery("input[type='text']").keypress(function(e){
								if(e.keyCode === 13 && /Android/.test(navigator.userAgent)) {
									e.preventDefault();
								}
							});

							//GOOGLE Analytics
							//ga('send', 'event', 'steps', 'formload', '20150806, enrollment, form, step1, personal-info', {'nonInteraction': 1})
						});

					</script>

					<h2 class="center-alignment">PRESCRIPTION HOPE PHARMACY<br/>PROGRAM ENROLLMENT</h2>

					<p class="subhead2-blue left-alignment" style="color: #17386f; text-transform: none;">
						The Prescription Hope Pharmacy Program charges a set price of $25 per month per medication. There are no other costs, fees, or charges associated with your medication on our program. If we are unable to obtain a prescription medication, we will not charge a fee for that medication.
					</p>

					<p class="subhead2-blue left-alignment" style="color: #17386f; text-transform: none;">
						Your personal information, including all health information, is protected by 128-bit SSL technology, the same technology used by hospitals and banks. Your information will never be disclosed to any third parties for any reason and is for order fulfillment purposes only.
					</p>

					<p class="left-alignment moduleSubheader">
						Fields with asterisks (*) are required.
						<br/>
						One form per person.
					</p>

					<form id="register_form" method="post" action="https://manage.prescriptionhope.com/register.php">

					<h2 class="center-alignment">PERSONAL INFORMATION</h2>
					<br/>

					<p class="form-row">
						<label for="p_first_name">First Name *</label>
						<input type="text" name="p_first_name" value="<?php echo htmlspecialchars(stripslashes($data['p_first_name']));?>"><br/>
					</p>

					<p class="form-row">
						<label for="p_middle_initial">Middle Initial *</label>
						<input type="text" name="p_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['p_middle_initial']));?>" maxlength="1"><br/>
					</p>

					<p class="form-row">
						<label for="p_last_name">Last Name *</label>
						<input type="text" name="p_last_name" value="<?php echo htmlspecialchars(stripslashes($data['p_last_name']));?>">
					</p>

					<p class="form-row">
						<label for="p_email">Email Address</label>
						<input type="text" name="p_email" value="<?php echo $data['p_email'];?>">
					</p>

					<div class="form-group-spacer"></div>

					<p>
						<label for="p_is_minor_yes" class="w670-mixed">Is this application on behalf of a minor? *</label>
						<input type="radio" id="p_is_minor_yes" name="p_is_minor" value="1" <?php echo (($data['p_is_minor'] != '') ? 'preload="' . (int)$data['p_is_minor'] . '"' : ''); ?>> <label for="p_is_minor_yes" class='no_width'>Yes</label> &nbsp;
						<input type="radio" id="p_is_minor_no" name="p_is_minor" value="0"> <label for="p_is_minor_no" class='no_width'>No</label>
					</p>

					<div class="form-group-spacer"></div>

					<p class="form-row patient_parent_profile no-show">
						<label for="p_parent_first_name">Parent/Guardian First Name *</label>
						<input type="text" name="p_parent_first_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_first_name']));?>"><br/>
					</p>

					<p class="form-row patient_parent_profile no-show">
						<label for="p_parent_middle_initial">Parent/Guardian Middle Initial *</label>
						<input type="text" name="p_parent_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_middle_initial']));?>" maxlength="1"><br/>
					</p>

					<p class="form-row patient_parent_profile no-show">
						<label for="p_parent_last_name">Parent/Guardian Last Name *</label>
						<input type="text" name="p_parent_last_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_last_name']));?>">
					</p>

					<p class="form-row patient_parent_profile no-show">
						<label for="p_parent_phone">Parent/Guardian Phone *</label>
						<input type="text" name="p_parent_phone" value="<?php echo $data['p_parent_phone'];?>"><br/>
					</p>

					<div class="form-group-spacer"></div>

					<p class="form-row patient_profile">
						<label for="p_address">Address *</label>
						<input type="text" name="p_address" value="<?php echo htmlspecialchars(stripslashes($data['p_address']));?>"><br/>
					</p>

					<p class="form-row patient_profile">
						<label for="p_city">City *</label>
						<input type="text" name="p_city" value="<?php echo htmlspecialchars(stripslashes($data['p_city']));?>"><br/>
					</p>

					<p class="form-row patient_profile">
						<label for="p_state">State *</label>
						<select name="p_state" preload="<?php echo $data['p_state'];?>">
							<option value="" selected="selected">Select a State</option>
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
						</select><br/>
					</p>

					<p class="form-row patient_profile">
						<label for="p_zip">ZIP Code *</label>
						<input type="text" name="p_zip" value="<?php echo $data['p_zip'];?>" maxlength="5">
					</p>

					<div class="form-group-spacer patient_profile"></div>

					<p class="form-row patient_profile">
						<label for="p_phone">Phone *</label>
						<input type="text" name="p_phone" value="<?php echo $data['p_phone'];?>"><br/>
					</p>

					<p class="form-row patient_profile">
						<label for="p_fax">Fax</label>
						<input type="text" name="p_fax" value="<?php echo $data['p_fax'];?>" class="not_required_phone"><br/>
					</p>

					<div class="form-group-spacer patient_profile"></div>

					<p class="form-row patient_profile">
						<label for="p_alternate_contact_name">Alternate Contact Name</label>
						<input type="text" name="p_alternate_contact_name" value="<?php echo htmlspecialchars(stripslashes($data['p_alternate_contact_name']));?>"><br/>
					</p>

					<p class="form-row patient_profile">
						<label for="p_alternate_phone">Alternate Contact Phone</label>
						<input type="text" name="p_alternate_phone" value="<?php echo $data['p_alternate_phone'];?>" class="not_required_phone">
					</p>

					<br/>
					<h2 class="center-alignment">PATIENT INFORMATION</h2>

					<div class="form-group-spacer"></div>

					<p class="form-row">
						<label for="p_dob">Date of Birth * (mm/dd/yyyy)</label>
						<input type="text" id="p_dob" name="p_dob" value="<?php echo $data['p_dob'];?>"><br/>
					</p>

					<p class="form-row">
						<label for="p_gender" class="w670-mixed">Gender *</label>
						<input type="radio" id="p_gender_m" name="p_gender" value="M" preload="<?php echo $data['p_gender'];?>"> <label for="p_gender_m" class='no_width'>Male</label> &nbsp;
						<input type="radio" id="p_gender_f" name="p_gender" value="F"> <label for="p_gender_f" class='no_width'>Female</label><br/>
					</p>

					<p class="form-row">
						<label for="p_ssn">SSN (<a href="#" rel="Your Social Security Number is required by the pharmaceutical companies for completing the application process to begin<br/> filling your medication order(s).  Your personal information is always kept safe through our government-grade, secured software." class="skipLeave disable-click">?</a>)*</label>
						<input type="text" name="p_ssn" value="<?php echo $data['p_ssn'];?>" maxlength="11">
					</p>

					<p class="form-row">
						<label for="p_household">Number of people in household *</label>
						<input type="text" name="p_household" value="<?php echo htmlspecialchars(stripslashes($data['p_household']));?>">
					</p>

					<p class="form-row">
						<label for="p_married">Marital Status *</label>
						<select name="p_married" preload="<?php echo $data['p_married'];?>">
								<option value=''>Select ...
								<option value='S'>Single
								<option value='M'>Married
								<option value='D'>Divorced
								<option value='W'>Widowed
						</select>
					</p>

					<p class="form-row">
						<label for="p_employment_status">Employment Status *</label>
						<select name="p_employment_status" preload="<?php echo $data['p_employment_status'];?>">
								<option value=''>Select ...
								<option value='F'>Full-Time
								<option value='P'>Part-Time
								<option value='R'>Retired
								<option value='U'>Unemployed
								<option value='S'>Self-Employed
						</select>
					</p>

					<p class="form-row">
						<label for="p_uscitizen" class="w670">Are you a US Citizen? *</label>
						<input type="radio" id="p_uscitizen_yes" name="p_uscitizen" value="1" <?php echo (($data['p_uscitizen'] != '') ? 'preload="' . (int)$data['p_uscitizen'] . '"' : ''); ?>> <label for="p_uscitizen_yes" class='no_width rpad15'>Yes</label>
						<input type="radio" id="p_uscitizen_no" name="p_uscitizen" value="0"> <label for="p_uscitizen_no" class='no_width'>No</label>
					</p>

					<p class="form-row">
						<label for="p_disabled_status" class="w670">Are you disabled as determined by Social Security? *</label>
						<input type="radio" id="p_disabled_status_yes" name="p_disabled_status" value="1" <?php echo (($data['p_disabled_status'] != '') ? 'preload="' . (int)$data['p_disabled_status'] . '"' : ''); ?>> <label for="p_disabled_status_yes" class='no_width rpad15'>Yes</label>
						<input type="radio" id="p_disabled_status_no" name="p_disabled_status" value="0"> <label for="p_disabled_status_no" class='no_width'>No</label><br/>
					</p>

					<p class="form-row">
						<label for="p_medicare" class="w670">Are you on Medicare? *</label>
						<input type="radio" id="p_medicare_yes" name="p_medicare" value="1" <?php echo (($data['p_medicare'] != '') ? 'preload="' . (int)$data['p_medicare'] . '"' : ''); ?>> <label for="p_medicare_yes" class='no_width rpad15'>Yes</label>
						<input type="radio" id="p_medicare_no" name="p_medicare" value="0"> <label for="p_medicare_no" class='no_width'>No</label><br class='p_medicare_2nd' />

						<label for="p_medicare_part_d" class="w670 p_medicare_2nd">Do you have Medicare Part D? *</label>
						<input type="radio" id="p_medicare_part_d_yes" name="p_medicare_part_d" value="1" <?php echo (($data['p_medicare_part_d'] != '') ? 'preload="' . (int)$data['p_medicare_part_d'] . '"' : ''); ?> class="tmargin5 p_medicare_2nd"> <label for="p_medicare_part_d_yes" class='no_width rpad15 p_medicare_2nd'>Yes</label>
						<input type="radio" id="p_medicare_part_d_no" name="p_medicare_part_d" value="0" class="tmargin5 p_medicare_2nd"> <label for="p_medicare_part_d_no" class='no_width p_medicare_2nd'>No</label><br/>
					</p>

					<p class="form-row">
						<label for="p_medicaid" class="w670">Have you applied for Medicaid? *</label>
						<input type="radio" id="p_medicaid_yes" name="p_medicaid" value="1" <?php echo (($data['p_medicaid'] != '') ? 'preload="' . (int)$data['p_medicaid'] . '"' : ''); ?>> <label for="p_medicaid_yes" class='no_width rpad15'>Yes</label>
						<input type="radio" id="p_medicaid_no" name="p_medicaid" value="0"> <label for="p_medicaid_no" class='no_width'>No</label><br class='p_medicaid_2nd' />

						<label for="p_medicaid" class="w670 p_medicaid_2nd">If yes, did you receive a denial letter? *</label>
						<input type="radio" id="p_medicaid_denial_yes" name="p_medicaid_denial" value="1" <?php echo (($data['p_medicaid_denial'] != '') ? 'preload="' . (int)$data['p_medicaid_denial'] . '"' : ''); ?> class="tmargin5 p_medicaid_2nd"> <label for="p_medicaid_denial_yes" class='no_width rpad15 p_medicaid_2nd'>Yes</label>
						<input type="radio" id="p_medicaid_denial_no" name="p_medicaid_denial" value="0" class="tmargin5 p_medicaid_2nd"> <label for="p_medicaid_denial_no" class='no_width p_medicaid_2nd'>No</label><br/>
					</p>

					<p class="form-row">
						<label for="p_lis" class="w670">Have you applied for Low Income Subsidy (LIS)? *</label>
						<input type="radio" id="p_lis_yes" name="p_lis" value="1" <?php echo (($data['p_lis'] != '') ? 'preload="' . (int)$data['p_lis'] . '"' : ''); ?>> <label for="p_lis_yes" class='no_width rpad15'>Yes</label>
						<input type="radio" id="p_lis_no" name="p_lis" value="0"> <label for="p_lis_no" class='no_width'>No</label><br class='p_lis_2nd'/>

						<label for="p_lis" class="w670 p_lis_2nd">If yes, did you receive a denial letter? *</label>
						<input type="radio" id="p_lis_denial_yes" name="p_lis_denial" value="1" <?php echo (($data['p_lis_denial'] != '') ? 'preload="' . (int)$data['p_lis_denial'] . '"' : ''); ?> class="tmargin5 p_lis_2nd"> <label for="p_lis_denial_yes" class='no_width rpad15 p_lis_2nd'>Yes</label>
						<input type="radio" id="p_lis_denial_no" name="p_lis_denial" value="0" class="tmargin5 p_lis_2nd"> <label for="p_lis_denial_no" class='no_width p_lis_2nd'>No</label>
					</p>

					<p class="form-row">
						<label for="p_hear_about" class="w670">How did you hear about Prescription Hope? Please be specific. *</label><br/>
						<?php if($data['p_hear_about'] == '2685-4694 Access Health Insurance, Inc')
								    $data['p_hear_about'] = '2685-4694 JibeHealth';?>
						<input type="text" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about']));?>" class="w800 no-margin" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>>
						<input type="hidden" name="p_application_source" value="<?=((isset($data['p_application_source'])) ? $data['p_application_source'] : '')?>">
					</p>

					<br/>
					<h2 class="center-alignment">MONTHLY INCOME</h2>

					<p class="form-row">
						<label class="blank">&nbsp;</label>
						<input type="checkbox" id="p_income_zero" name="p_income_zero" value="1" <?php echo (($data['p_income_zero'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
						<label for="p_income_zero" class="big">I currently have no income</label>
					</p>

					<p class="form-row">
						<label class="blank">&nbsp;</label>
						<input type="checkbox" id="p_income_file_tax_return" name="p_income_file_tax_return" value="1" <?php echo (($data['p_income_file_tax_return'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
						<label for="p_income_file_tax_return" class="big">I currently do not file a tax return</label>
					</p>

					<p class="form-row">
						<label for="p_income_salary" class="w260">Gross Salary/Wages</label>
						$ <input type="text" name="p_income_salary" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_salary']));?>" class="input_zero"><br/>
					</p>

					<p class="form-row">
						<label for="p_income_unemployment" class="w260">Unemployment</label>
						$ <input type="text" name="p_income_unemployment" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_unemployment']));?>" class="input_zero"><br/>
					</p>

					<p class="form-row">
						<label for="p_income_pension" class="w260">Pension</label>
						$ <input type="text" name="p_income_pension" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_pension']));?>" class="input_zero"><br/>
					</p>

					<p class="form-row">
						<label for="p_income_annuity" class="w260">Annuity/IRA</label>
						$ <input type="text" name="p_income_annuity" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_annuity']));?>" class="input_zero"><br/>
					</p>

					<p class="form-row">
						<label for="p_income_ss_retirement" class="w260">SS Retirement</label>
						$ <input type="text" name="p_income_ss_retirement" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_ss_retirement']));?>" class="input_zero"><br/>
					</p>

					<p class="form-row">
						<label for="p_income_ss_disability" class="w260">SS Disability</label>
						$ <input type="text" name="p_income_ss_disability" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_ss_disability']));?>" class="input_zero"><br/>
					</p>

					<div class="form-group-spacer"></div>

					<h2 class="center-alignment">MEDICATION INFORMATION</h2>

					<p class="center-alignment moduleSubheader">
						Please list your medication, strength, frequency, and the doctor prescribing that medication.<br/>
						Only list the medications you are requesting through Prescription Hope.
					</p>

					<div id="new_doctor_form">
						<br/>
						<p class="subhead2-blue">DOCTORS:</p>
						<p class="moduleSubheader">Only list doctor prescribing the medication.</p>
						<br/>

						<p id="doctor_form">
							<input type="hidden" name="dr_id" id="dr_id" value="1">

							<label for="dr_first_name">Doctor First Name *</label>
							<input type="text" name="dr_first_name" value="" class="dr-required dr-data"><br/><br/>

							<label for="dr_last_name">Doctor Last Name *</label>
							<input type="text" name="dr_last_name" value="" class="dr-required dr-data"><br/><br/>

							<label for="dr_facility">Facility Name</label>
							<input type="text" name="dr_facility" value="" class="dr-data"><br/><br/>

							<label for="dr_address">Address *</label>
							<input type="text" name="dr_address" value="" class="dr-required dr-data"><br/><br/>

							<label for="dr_address2">Suite</label>
							<input type="text" name="dr_address2" value="" class="dr-data"><br/><br/>

							<label for="dr_city">City *</label>
							<input type="text" name="dr_city" value="" class="dr-required dr-data"><br/><br/>

							<label for="dr_state">State *</label>
							<select name="dr_state" class="dr-required dr-data">
								<option value="" selected="selected">Select a State</option>
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
							</select><br/><br/>

							<label for="dr_zip">ZIP Code *</label>
							<input type="text" name="dr_zip" value="" maxlength="5" class="dr-required dr-data"><br/><br/>

							<label for="dr_phone">Phone *</label>
							<input type="text" name="dr_phone" value="" class="dr-required dr-data"><br/><br/>

							<label for="dr_fax">Fax</label>
							<input type="text" name="dr_fax" value="" class="dr-data">

							<br/><br/>

							<label class="desktop-only">&nbsp;</label>
							<input type="button" name="bAddANewDoctor" id="bAddANewDoctor" value="ADD ANOTHER DOCTOR" class="cancel small-button-orange">
						</p>

						<?php foreach ($data['doctors'] as $dr_key => $doctor) { ?>
							<?php if ($doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') { ?>
								<input type="hidden" name="doctor_first_name[<?=$dr_key?>]" value="<?=$doctor['doctor_first_name']?>" class="doctor-fields">
								<input type="hidden" name="doctor_last_name[<?=$dr_key?>]" value="<?=$doctor['doctor_last_name']?>">
								<input type="hidden" name="doctor_facility[<?=$dr_key?>]" value="<?=$doctor['doctor_facility']?>">
								<input type="hidden" name="doctor_address[<?=$dr_key?>]" value="<?=$doctor['doctor_address']?>">
								<input type="hidden" name="doctor_address2[<?=$dr_key?>]" value="<?=$doctor['doctor_address2']?>">
								<input type="hidden" name="doctor_city[<?=$dr_key?>]" value="<?=$doctor['doctor_city']?>">
								<input type="hidden" name="doctor_state[<?=$dr_key?>]" value="<?=$doctor['doctor_state']?>">
								<input type="hidden" name="doctor_zip[<?=$dr_key?>]" value="<?=$doctor['doctor_zip']?>">
								<input type="hidden" name="doctor_phone[<?=$dr_key?>]" value="<?=$doctor['doctor_phone']?>">
								<input type="hidden" name="doctor_fax[<?=$dr_key?>]" value="<?=$doctor['doctor_fax']?>">
							<?php } ?>
						<?php } ?>
					</div>

					<div class="form-group-spacer"></div>

					<div class="medication_list">
					<br/>
						<p class="subhead2-blue">MEDICATION:</p>
						<br/>

						<?php
						$valid_meds = 0;
						foreach ($data['medication'] as $med_key => $medication) { ?>
							<?php if ($medication['medication_name'] != '' && $medication['medication_strength'] != '' && $medication['medication_frequency'] != '' && (int) $medication['medication_doctor'] != '') {
								$valid_meds++;
								?>
								<div class="no-margin" id="medication_form">
									<div class="float-left astable medication-name-field">
										<p>
											<label class="<?=(($valid_meds > 1) ? 'mobile-only' : '')?>">Medication Name</label>
											<input type="text" name="medication_name[<?=$med_key?>]" value="<?=$medication['medication_name']?>" class="med-data">
										</p>
									</div>

									<div class="float-left astable medication-strength-field">
										<p>
											<label class="<?=(($valid_meds > 1) ? 'mobile-only' : '')?>">Strength</label>
											<input type="text" name="medication_strength[<?=$med_key?>]" value="<?=$medication['medication_name']?>" class="med-data">
										</p>
									</div>

									<div class="float-left astable medication-frequency-field">
										<p>
											<label class="<?=(($valid_meds > 1) ? 'mobile-only' : '')?>">Frequency (ex. daily)</label>
											<input type="text" name="medication_frequency[<?=$med_key?>]" value="<?=$medication['medication_name']?>" class="med-data">
										</p>
									</div>

									<div class="float-left astable medication-doctor-field">
										<p>
											<label class="<?=(($valid_meds > 1) ? 'mobile-only' : '')?>">Prescribing Doctor</label>
											<select name="medication_doctor[<?=$med_key?>]" preload="<?=$medication['medication_doctor']?>" class="doctors_dropdown med-data">
												<option value="">choose doctor ...</option>
											</select>
										</p>
									</div>
								</div>
							<?php } ?>
						<?php } ?>

						<?php if ($valid_meds == 0) { ?>
							<div class="no-margin" id="medication_form">
								<!--input type="hidden" name="med_id" id="med_id" value="1"-->

								<div class="float-left astable medication-name-field">
									<p>
										<label>Medication Name</label>
										<input type="text" name="medication_name[1]" value="" class="med-data">
									</p>
								</div>

								<div class="float-left astable medication-strength-field">
									<p>
										<label>Strength</label>
										<input type="text" name="medication_strength[1]" value="" class="med-data">
									</p>
								</div>

								<div class="float-left astable medication-frequency-field">
									<p>
										<label>Frequency (ex. daily)</label>
										<input type="text" name="medication_frequency[1]" value="" class="med-data">
									</p>
								</div>

								<div class="float-left astable medication-doctor-field">
									<p>
										<label>Prescribing Doctor</label>
										<select name="medication_doctor[1]" class="doctors_dropdown med-data">
											<option value="">choose doctor ...</option>
											<?php /*foreach ($data['doctors'] as $dr_key => $doctor) { ?>
												<?php if ($doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') { ?>
													<option value="<?php echo ($dr_key);?>"><?php echo 'Doctor ' . ($dr_key) . ' (' . $doctor['doctor_first_name'] . ' ' . $doctor['doctor_last_name'] . ')';?></option>
												<?php } ?>
											<?php } */ ?>
										</select>
									</p>
								</div>
							</div>
						<?php } ?>

						<br clear="all" class=""/>
						<div class="form-group-spacer-small"></div>

						<p>
							<input type="button" name="bAddANewMedication" id="bAddANewMedication" value="ADD ANOTHER MEDICATION" class="cancel small-button-orange">
						</p>

						<?php foreach ($data['medication'] as $med_key => $medication) { ?>
						<?php } ?>
					</div>

					<div class="form-group-spacer"></div>

					<h2 class="center-alignment">TERMS AND CONDITIONS</h2>

					<br/>

					<p class="moduleSubheader terms_scroll_box">
						<span class="policy_subtitle">Fees:</span> During the initial enrollment process if we find that we are unable to assist you with at least one medication, there will be no
						charges to your account. If we are able to assist you with one or more
						medication(s), the first month's administrative service fee of $25 per
						medication will be debited only for the medication(s) for which we
						can assist you upon receipt of this form. The monthly administrative
						service fee of $25 per medication will be debited on the 5th day of
						every month thereafter unless the 5th falls on a weekend or a holiday,
						in which case the debit will occur on the prior business day. You will
						be notified in writing of the medication(s) for which we are able to
						assist you. There are no other fees for the program or cost for the
						medication(s). It will take approximately 4-6 weeks to start receiving
						your first supply of medication(s). This range is an average amount of
						time and is contingent upon a prompt response to the information we
						request from you and your physician(s). The medication is shipped
						directly from the pharmaceutical companies and delivered either to
						your home or physician's office, depending upon the manufacturer
						delivery guidelines. You hereby acknowledge that you are not paying
						for medication(s) through the Prescription Hope service; rather you
						are paying for the administrative service of ordering, managing,
						tracking and refilling medications received through Prescription
						Hope's medication advocacy service from pharmaceutical company
						patient assistance programs. You hereby authorize Prescription Hope
						and/or its agents to debit the account provided on the front of this
						form for all administrative service fees described in this Fees section.
						You also agree to pay any associated fees should your EFT (electronic
						fund transfer) be returned unpaid by your financial institution. Due
						to the service-based nature of the Prescription Hope service, there
						are no refunds other than what is explained in the Prescription Hope
						Guarantee below. You hereby acknowledge, consent and agree this
						agreement is for twelve (12) months commencing on the date you
						sign below and will automatically be renewed for twelve (12)-month
						terms thereafter. You may terminate this agreement at any time by
						providing a signed letter of cancellation. Cancellations can take up
						to 30 days to process. Upon termination you agree to be financially
						responsible for any outstanding balances. This monthly transaction
						will appear on your billing statement as "PRESCRIPTION HOPE".
						You agree that you may be contacted via telephone, cellular phone,
						text message or email through all numbers/addresses provided by
						you and authorize receipt of pre-recorded/artificial voice messages
						and/or use of an automated dialing service by Prescription Hope or
						affiliates. By signing below, you further agree to release Prescription
						Hope, its agents, employees, successors and assigns from any and
						all liability including legal fees and costs arising from medications
						taken by you which were procured through the Prescription Hope
						medication advocacy service. You further agree to indemnify and
						hold Prescription Hope, its agents, employees, successor and assigns
						harmless against any and all damages including legal fees and costs
						arising from third persons ingesting any medication procured for
						you through the Prescription Hope advocacy program.
					</p>

					<p>
						<input type="checkbox" id="p_payment_agreement" name="p_payment_agreement" value="1" <?php echo (($data['p_payment_agreement'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
						<label for="p_payment_agreement" class="no_width big">I have read, understood and agree to be bound by the above paragraphs *</label>
					</p>

					<p class="moduleSubheader terms_scroll_box">
						<span class="policy_subtitle">Service:</span> You hereby authorize Prescription Hope to act on your
						behalf and to sign applications for patient assistance programs by
						hereby granting to Prescription Hope a limited power of attorney
						for the specific purposes of enrolling you in patient assistance
						programs with the applicable pharmaceutical companies and any
						related activities to process your enrollment. You understand this
						authorization can be revoked at any time by you by providing a
						signed letter of cancellation to Prescription Hope as described in
						the fees section. You hereby authorize your physician's office(s) to
						discuss/release medical information to Prescription Hope relating to
						your application(s) for patient assistance programs that Prescription
						Hope is processing on your behalf. You understand that Prescription
						Hope does not ship, prescribe, purchase, sell, handle or dispense
						prescription medication of any kind in its efforts to process your
						application(s) for patient assistance programs. Prescription Hope
						is a fee-based medication advocacy service that assists patients
						in enrolling in applicable pharmaceutical companies patient
						assistance programs. The medications themselves are offered by
						the pharmaceutical companies through their patient assistance
						programs at no cost to the eligible applicant. You also understand and
						acknowledge that it is each individual pharmaceutical company who
						makes the final decision as to whether you qualify for their assistance
						program(s). You understand Prescription Hope reserves the right to
						rescind, revoke, or amend its services at any time. Prescription Hope
						does not guarantee your approval for patient assistance programs;
						it is up to each applicable drug manufacturer to make the eligibility
						determination. Each drug manufacturer independently sets its own
						eligibility criteria and determines which products are included
						in their assistance programs. Medications covered are subject to
						change at any time. Prescription Hope assembles and submits your
						application to the pharmaceutical company but does not participate
						in the review process to determine which applicants are eligible.
					<p>

					<p>
						<input type="checkbox" id="p_service_agreement"  name="p_service_agreement" value="1" <?php echo (($data['p_service_agreement'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
						<label for="p_service_agreement" class="no_width big">I have read, understood and agree to be bound by the above paragraphs *</label>
					</p>

					<p class="moduleSubheader terms_scroll_box">
						<span class="policy_subtitle">Guarantee:</span> If you do not receive medication because you were
						determined to be ineligible for the patient assistance program by
						the applicable pharmaceutical manufacturer(s) and you have a
						letter of denial, Prescription Hope will gladly refund the monthly
						administrative service fee(s) for the medication(s) determined to
						be ineligible. All Prescription Hope needs from you is a copy of the
						denial letter sent to you from the applicable drug manufacturer
						explaining why you are ineligible.

						<br/><br/>

						<span class="policy_subtitle">Privacy:</span> We value our patients and make extreme efforts to protect the
						privacy of our patients personal information. Patient information is
						processed for order fulfillment only and for no other purpose. Patient
						information, including all patient health information and personal
						information, will never be disclosed to any third party under any
						circumstances. All information given to Prescription Hope, Inc., its
						agents, employees, successors and assigns (collectively, "Prescription
						Hope") will be held in the strictest confidence.

						<br/><br/>

						<span class="policy_subtitle">Eligibility:</span> You are experiencing hardship in affording your medication
						and/or you currently do not have coverage that reimburses or pays
						for your prescription medications. You affirm that the information
						provided on this form is complete and accurate. If you determine
						the information was not correct at the time you provided it to
						Prescription Hope, or if the information was accurate but is no longer
						accurate, you will immediately notify Prescription Hope in writing
						by providing the correct information.
					</p>

					<p>
						<input type="checkbox" id="p_guaranty_agreement"  name="p_guaranty_agreement" value="1" <?php echo (($data['p_guaranty_agreement'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
						<label for="p_guaranty_agreement" class="no_width big">I have read, understood and agree to be bound by the above paragraphs *</label>
					</p>

					<br/>
					<h2 class="center-alignment">PAYMENT INFORMATION</h2>

					<div class="pull-left">
						<p>
							<label for="p_payment_method" class="as_block payment_method_field" style="margin-top: 0px;">Payment Method *</label>
						</p>
					</div>
					<div class="pull-left">
						<p>
							<input type="radio" id="p_payment_method_cc" name="p_payment_method" value="cc" <?php echo (($data['p_payment_method'] != '') ? 'preload="' . $data['p_payment_method'] . '"' : ''); ?>> <label for="p_payment_method_cc" class='no_width'> &nbsp;Visa, Mastercard or Debit Card</label>
							<br clear="all"/>
							<img src="images/visa.jpg" border="0" class="payment_images_" /> &nbsp;<img src="images/mastercard.jpg" border="0" />
							<br/><br/>

							<!--label class="blank">&nbsp;</label-->
							<input type="radio" id="p_payment_method_ach" name="p_payment_method" value="ach"> <label for="p_payment_method_ach" class='no_width'> &nbsp;Checking Account</label>
							<br clear="all"/>
							<img src="images/check.jpg" border="0" class="payment_images_" />
						</p>
					</div>
					<br clear="all" />

					<p id="payment_cc" class="no-show">
						<label for="p_cc_type" class="payment_method_field">Credit Card Type *</label>
						<select name="p_cc_type" preload="<?php echo $data['p_cc_type'];?>">
							<option value="Visa">Visa
							<option value="Mastercard">Mastercard
						</select>
						<br/><br/>

						<label for="p_cc_number" class="payment_method_field">Credit Card Number *</label>
						<input type="text" name="p_cc_number" value="<?php echo $data['p_cc_number'];?>"><br/>
						<br/>

						<label for="p_cc_exp_month" class="payment_method_field">Credit Card Expiration Month *</label>
						<select name="p_cc_exp_month" preload="<?php echo $data['p_cc_exp_month'];?>">
							<option value="01">January
							<option value="02">February
							<option value="03">March
							<option value="04">April
							<option value="05">May
							<option value="06">June
							<option value="07">July
							<option value="08">August
							<option value="09">September
							<option value="10">October
							<option value="11">November
							<option value="12">December
						</select>
						<br/><br/>

						<label for="p_cc_exp_year" class="payment_method_field">Credit Card Expiration Year *</label>
						<select name="p_cc_exp_year" preload="<?php echo $data['p_cc_exp_year'];?>">
							<?php for ($i = 0; $i < 10; $i++) { ?>
								<option value="<?php echo ((int) date('Y') + $i); ?>"><?php echo ((int) date('Y') + $i); ?>
							<?php } ?>
						</select>
						<br/><br/>

						<label for="p_cc_cvv" class="payment_method_field">CVV Security Code (<a href="#" rel="images/card_visa.gif" class="skipLeave disable-click">?</a>)*</label>
						<input type="text" name="p_cc_cvv" value="<?php echo $data['p_cc_cvv'];?>" maxlength="3"><br/>
					</p>

					<p id="payment_ach" class="no-show">
						<label for="p_ach_holder_name">Account Holder’s Name *</label>
						<input type="text" name="p_ach_holder_name" value="<?php echo $data['p_ach_holder_name'];?>"><br/>
						<br/>

						<label for="p_ach_routing">Bank Routing Number (<a href="#" rel="images/ach2.jpg" class="skipLeave disable-click">?</a>)*</label>
						<input type="text" name="p_ach_routing" value="<?php echo $data['p_ach_routing'];?>" maxlength="9"><br/>
						<br/>

						<label for="p_ach_account">Checking Account Number (<a href="#" rel="images/ach2.jpg" class="skipLeave disable-click">?</a>)*</label>
						<input type="text" name="p_ach_account" value="<?php echo $data['p_ach_account'];?>"><br />
					</p>

					<br/>

					<p class="moduleSubheader">
						<strong>
						By checking this box, I acknowledge that I have read and agree to the terms and conditions of Prescription Hope, including the fee policy, service policy, privacy policy, and guarantee. I authorize Prescription Hope to charge my account $25 per month, per medication that I may qualify for. Due to the service-based nature of the Prescription Hope program, I acknowledge there are no refunds other than what is explained in the Prescription Hope guarantee. If I do not receive medication because I am determined to be ineligible for the patient assistance program by the applicable pharmaceutical manufacturer(s) and I have a letter of denial, I acknowledge Prescription Hope will refund the monthly administrative service fee(s) for the medication(s) determined to be ineligible only after Prescription Hope has exhausted all avenues of appeal. In order to receive a refund, I will provide Prescription Hope a copy of the denial letter sent from the applicable drug manufacturer explaining why I am ineligible.
						<br/><br/>
						This agreement is in effect starting on this day of my application, until I rescind my authorization in writing.
						</strong>
					</p>

					<p>
						<input type="checkbox" id="p_acknowledge_agreement"  name="p_acknowledge_agreement" value="1" <?php echo (($data['p_acknowledge_agreement'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
						<label for="p_acknowledge_agreement" class="no_width big">I have read, understood and agree to be bound by the above paragraphs *</label>
					</p>

					<div class="form-group-spacer"></div>

					<p class="center-alignment">
						<br/>
						<input type="submit" name="bSubmit" id="bSubmit" value="Submit" class="small-button-orange">
					</p>

					</form>
				<?php } else { ?>
					<!-- CONFIRMATION -->
					<script type="text/javascript">

						jQuery().ready(function() {
							//GOOGLE Analytics
							//ga('send', 'event', 'conversion', 'submission', '20150806, enrollment, form, step5, successful-sub', {'nonInteraction': 1})
						});

					</script>

					<h2 class="center-alignment">PRESCRIPTION HOPE ENROLLMENT<br/><?php echo (($response_success) ? 'CONFIRMATION' : 'ERROR'); ?></h2>

					<br/>

					<div class="center-alignment"><?php echo $response_msg;?></div>

					<form id="register_form" method="post" action="https://manage.prescriptionhope.com/register.php">
						<?php if (!$response_success) { ?>
							<p class="center-alignment">
								<input type="submit" id="bPrevStep" name="bPrevStep" value="Back" class="cancel small-button-orange">
							</p>
						<?php } ?>
					</form>

					<!-- Google Code for Application Conversion Page -->
					<script type="text/javascript">
					/* <![CDATA[ */
					var google_conversion_id = 1044900775;
					var google_conversion_language = "en";
					var google_conversion_format = "3";
					var google_conversion_color = "ffffff";
					var google_conversion_label = "JztYCKXr8gkQp9ef8gM";
					var google_remarketing_only = false;
					/* ]]> */
					</script>
					<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
					</script>
					<noscript>
					<div style="display:inline;">
					<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1044900775/?label=JztYCKXr8gkQp9ef8gM&amp;guid=ON&amp;script=0"/>
					</div>
					</noscript>

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

				<p class="center-alignment">
					<?php if ($form_submitted) { ?>
						<br/>
						Protecting your personal information is our highest priority.<br/>
						We have the same secured software used by banks in place<br/>
						to ensure your personal information is always safe.
						<br /><br />
					<?php } ?>

					<table align='center' id="seals">
						<tr>
							<td style="padding-top: 25px;"><script type="text/javascript" src="https://seal.geotrust.com/getgeotrustsslseal?host_name=manage.prescriptionhope.com&amp;size=S&amp;lang=en"></script><a href="http://www.geotrust.com/ssl/" target="_blank"  style="color:#000000; text-decoration:none; font:bold 7px verdana,sans-serif; letter-spacing:.5px; text-align:center; margin:0px; padding:0px;"></a><br/><br/></td>
							<td>&nbsp;</td>
							<td>
								<table width="135" border="0" cellpadding="2" cellspacing="0" title="Click to Verify - This site chose Symantec SSL for secure e-commerce and confidential communications.">
									<tr>
										<td width="135" align="center" valign="top">
											<script type="text/javascript" src="https://seal.websecurity.norton.com/getseal?host_name=www.prescriptionhope.com&amp;size=M&amp;use_flash=YES&amp;use_transparent=YES&amp;lang=en"></script>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</p>
			</div>
		</div>
	</div>
</div>

<div id="row-4" class="row-4 one_column white text-center">
	<div class="container">
		<span class="editor block-6">
			<br/><br/>
			<h3>Questions?</h3>
			<p>If you have any questions about Prescription Hope’s brand-name medication service, call our patient advocates at 1-877-296-HOPE (4673).</p>
			<br/>
		</span>
	</div>
</div>

<div id="leavePopup"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">The Prescription Hope Pharmacy Program over the last decade has helped thousands of people nationwide obtain their prescription medication for only $25 per month per medication. Are you sure you want to navigate from this page?</p>

		    	<br/><br/>
				<p class="center-alignment" id="leaveButtons">
					<span class="small-button-orange">
						<a href="" id="btLeaveNo" class="skipLeave">No</a>
					</span>
					<span class="small-button-orange">
						<a href="" id="btLeaveYes" class="skipLeave">Yes</a>
					</span>
				</p>
		    </div>
		</div>
	</div>
</div>


<div id="leavePopup2"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">Please help us improve our services by taking a moment to tell us the reason that you do not want to complete the enrollment form</p>
		    	<br/>

		    	<form id="fmLeavePage" method="post" action="">
		    	<input type="hidden" name="id" value="<? echo session_id(); ?>">

				<p class="center-alignment">
					<textarea name="leave_reason" class="no-margin"></textarea>
				</p>
				<br/>

				<p class="center-alignment" id="leaveButtons2">
					<input type="submit" name="bSubmitLeavePage" id="btLeaveSubmit" value="Submit" class="small-button-orange">

					<span class="small-button-orange">
						<a href="" id="btLeaveCancel" class="skipLeave">Cancel</a>
					</span>
				</p>
				</form>
		    </div>
		</div>
	</div>
</div>

<div id="reminderPopup"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">YOU’RE ALMOST FINISHED!</p>

		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">Reminder: If we find that we are unable to approve you, there will be no charges. If you are approved, the ONLY charge is $25/month/medication. If the payment section is not complete, your enrollment form <span style="text-decoration: underline;">will not</span> be processed. If you have any questions, please contact a Patient Advocate at 1-877-296-HOPE (4673).</p>

		    	<br/>

		    	<p class="center-alignment" style="font-size: 12px;">Press OK to Continue</p>

				<p class="center-alignment" id="btReminderOK">
					<span class="small-button-orange">
						<a href="#" id="btReminderClose" class="skipLeave">OK</a>
					</span>
				</p>
		    </div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var preventLeave = <?=((!$form_submitted) ? 'true' : 'false') ?>;
	var lastClickedObject = null;
	var lastEventType = null;

	jQuery().ready(function() {
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
			jQuery('.leavePopupContent').center();
		});

 		jQuery('.disable-click').click(function(e) {
 			e.preventDefault();
 		});
	});
</script>

<?php include('_footer.php'); ?>
