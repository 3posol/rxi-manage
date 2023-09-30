<?php

require_once('includes/fpdf/fpdf.php');
require_once('includes/fpdf/fpdi/fpdi.php');

require_once('includes/phpmailer/class.phpmailer.php');

require_once('pdf_application.php');

//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

//
// Send application PDF through email
//
function sendApplicationAsEmail($data) {
	$mail = new PHPMailer(); // defaults to using php "mail()"

	$mail->isHTML(false);

	$mail->SetFrom('DoNotReply@prescriptionhope.com', 'Prescription Hope');
	$mail->AddReplyTo("DoNotReply@prescriptionhope.com","Prescription Hope");

	$mail->AddAddress($data['p_email'], (isset($data['p_first_name']) && isset($data['p_last_name'])) ? ($data['p_first_name'] . ' ' . $data['p_last_name']) : '');

	$mail->Subject = "Online Application Confirmation";

	$mail->Body = "\n\nTHIS IS AN AUTOMATED EMAIL - PLEASE DO NOT RESPOND TO THIS MESSAGE AS IT IS NOT CHECKED";
	$mail->Body .= "\n\nThank you for submitting an Enrollment Form to Prescription Hope.  A Patient Advocate will review your form and begin your Enrollment process. You can expect a phone call and Welcome Packet from Prescription Hope soon.";
	$mail->Body .= "\n\nAttached to this email you'll find a copy of your Enrollment Form for your records.\n\n\nPrescription Hope";

	$mail->AddStringAttachment(pdf_application($data, 'S'), 'Prescription_Hope_Application.pdf');

	if (!$mail->Send()) {
		return "Mailer Error: " . $mail->ErrorInfo;
	} else {
		return "Message sent!";
	}
}

$method = (isset($_GET['method']) && $_GET['method'] == 'email') ? 'email' : '';

//load session data
session_start();
$data = $_SESSION['register_data'];

if ($method == 'email' && isset($data['p_email']) && trim($data['p_email']) != '') {
	//
	// Send application PDF through email
	//
	echo sendApplicationAsEmail($data);

} elseif (isset($data['p_first_name']) && trim($data['p_first_name']) != '' && isset($data['p_last_name']) && trim($data['p_last_name']) != '') {
	//
	// Show the application PDF in the browser
	//

	//sendApplicationAsEmail($data);
	pdf_application($data);
}

?>