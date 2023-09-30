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
	'command'		=> 'get_billing',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$billing_information = api_command($data);

$data = array(
	'cc_type' 			=> '',
	'cc_number' 		=> '',
	'cc_exp_month' 		=> '',
	'cc_exp_year' 		=> '',
	'cc_cvv' 			=> '',
	'PatientFirstName'		=> $_SESSION['PLP']['patient']->PatientFirstName,
	'PatientLastName'		=> $_SESSION['PLP']['patient']->PatientLastName,
	'PatientAddress1'	=> $_SESSION['PLP']['patient']->PatientAddress1,
	'PatientCity_1'		=> $_SESSION['PLP']['patient']->PatientCity_1,
	'PatientState_1'		=> $_SESSION['PLP']['patient']->PatientState_1,
	'PatientZip_1'		=> $_SESSION['PLP']['patient']->PatientZip_1
);

echo json_encode($data);