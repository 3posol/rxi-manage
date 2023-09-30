<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

$invoice_id = (isset($_POST['invoice'])) ? (int) $_POST['invoice'] : 0;
$invoice_amount = (isset($_POST['amount'])) ? (float) $_POST['amount'] : 0;

if ((int) $invoice_id > 0 && (float) $invoice_amount > 0) {
	$data = array(
		'command'		=> 'make_payment',
		'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
		'access_code'	=> $_SESSION['PLP']['access_code'],
		'invoice'		=> $invoice_id,
		'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
	);

//	$payment_result = json_decode(json_encode(array('success' => 0)));
	$payment_result = api_command($data);
}

echo json_encode(array(
	'invoice' 	=> $invoice_id,
	'amount' 	=> (string) number_format($invoice_amount, 2),
	'datetime'	=> date('m/d/Y H:i a'),
	'success'	=> (isset($payment_result->success)) ? (int) $payment_result->success : 0
));