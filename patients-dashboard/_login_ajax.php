<?php

require_once('includes/functions.php');

//session_set_cookie_params(0, '/', '.prescriptionhope.com');
ini_set('session.cookie_domain', '.prescriptionhope.com' );
session_start();
unset($_SESSION['PLP']);
//session_destroy();

//RxI check
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'portal.php')) {
	setcookie('RxI', 1);
} elseif (!isset($_SERVER['HTTP_REFERER'])) {
	setcookie("RxI", 0, time() - 3600);
}

$email_address = (isset($_POST['patient_email_address'])) ? strtolower(trim($_POST['patient_email_address'])) : null;
$password = (isset($_POST['patient_password'])) ? trim($_POST['patient_password']) : null;

$success = true;
$message = '';

$result = array(
	'success' => 0
);

if ($email_address != '' && $password != '') {
	//encode password
	db_connect();
	$rs = mysql_query('SELECT MD5("' . $email_address . '") as encoded_email, MD5("' . addslashes($password) . '") as encoded_password');
	$encoded_data = mysql_fetch_assoc($rs);
	$encoded_email = $encoded_data['encoded_email'];
	$encoded_password = $encoded_data['encoded_password'];

	//login
	$data = array(
		'command'		=> 'login',
		'email_address' => $email_address,
		'password'		=> $encoded_password,
		'from_rxi'		=> 1
	);

	$response = api_command($data);

	if (isset($response->success) && $response->success == 1) {
		//success
		if (session_id() == '') {
			session_start();
		}

		$_SESSION['PLP']['patient'] = $response->patient;
		clear_patient_last_name();

		$_SESSION['PLP']['patient_general_message'] = $response->patient_general_message;
		$_SESSION['PLP']['patient_general_message_title'] = $response->patient_general_message_title;

		$_SESSION['PLP']['access_code'] = $encoded_email;

		$_SESSION['PLP']['rxi_user'] = array(
			'id' 	=> (isset($response->rxi_user->id)) ? (int) $response->rxi_user->id: 0,
			'name' 	=> (isset($response->rxi_user->name)) ? $response->rxi_user->name: ''
		);

		$_SESSION['PLP']['patient_price_point'] = $response->patient_price_point;

		//enrollment form account logins
		$_SESSION['PLP']['enrollment_form_account'] = (isset($response->enrollment_form_account)) ? true : false;

		$result['success'] = 1;
		$result['redirect'] = ($response->patient->account_activated == 2) ? 'change_password' : 'dashboard';
	} elseif (isset($response->success) && $response->success == 2) {
		//enrollment form login
		if (session_id() == '') {
			session_start();
		}

		//
		unset($_SESSION['PLP']);
		unset($_SESSION['PHEnroll']);

		$_SESSION['PHEnroll']['data'] = (array) $response->patient;
		$_SESSION['PHEnroll']['incomplete_application'] = ! (bool) $response->incomplete_application;
		$_SESSION['PHEnroll']['access_code'] = $encoded_email;

		$result['test1'] = $response->success;
		$result['test2'] = $response->incomplete_application;
		$result['success'] = 1;
		$result['redirect'] = 'enroll';
	}
}

echo json_encode($result);