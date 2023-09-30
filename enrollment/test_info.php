<?php
phpinfo();
exit;
require_once('includes/functions.php');

require_once('includes/fpdf/fpdf.php');
require_once('includes/fpdf/fpdi/fpdi.php');
require_once('includes/phpmailer/class.phpmailer.php');
require_once('pdf_application.php');

session_start();

$data = array(
    'p_application_source'      => 'Test',
    'p_first_name'              => 'first',
    'p_middle_initial'          => 'i',
    'p_last_name'               => 'last',
    'p_is_minor'                => 0,
    'p_parent_first_name'       => '',
    'p_parent_middle_initial'   => '',
    'p_parent_last_name'        => '',
    'p_parent_phone'            => '',
    'p_address'                 => 'address',
    'p_address2'                => 'address2',
    'p_city'                    => 'city',
    'p_state'                   => 'OK',
    'p_zip'                     => '54321',
    'p_phone'                   => '876-876-9086',
    'p_fax'                     => '2345',
    'p_email'                   => 'aaa@email.com',
    'p_alternate_contact_name'  => '',
    'p_alternate_phone'         => '',
    'p_dob'                     => '12/12/1978',
    'p_gender'                  => 'M',
    'p_ssn'                     => '111-11-1111',
    'p_household'               => 4,
    'p_married'                 => 1,
    'p_employment_status'       => 'F',
    'p_uscitizen'               => 1,
    'p_disabled_status'         => 0,
    'p_medicare'                => 1,
    'p_medicare_part_d'         => 0,
    'p_medicaid'                => 0,
    'p_medicaid_denial'         => 0,
    'p_lis'                     => 1,
    'p_lis_denial'              => 0,
    'p_hear_about'              => 'Facebook',
    'p_hear_about_1'            => '',
    'p_hear_about_2'            => '',
    'p_hear_about_3'            => '',
    'p_has_salary'              => 1,
    'p_income_salary'           => 999,
    'p_has_unemployment'        => 0,
    'p_income_unemployment'     => 0,
    'p_has_pension'             => 0,
    'p_income_pension'          => 0,
    'p_has_annuity'             => 0,
    'p_income_annuity'          => 0,
    'p_has_ss_retirement'       => 0,
    'p_income_ss_retirement'    => 0,
    'p_has_ss_disability'       => 0,
    'p_income_ss_disability'    => 0,
    'p_income_annual_income'    => array(false, 2, ''),
    'p_has_income'              => 1,
    'p_income_zero'             => 0,
    'p_income_file_tax_return'  => 1,
    'p_payment_agreement'       => 1,
    'p_service_agreement'       => 1,
    'p_guaranty_agreement'      => 1,
    'p_payment_method'          => 'cc',
    'p_cc_type'                 => 'VISA',
    'p_cc_number'               => '876876876876876876',
    'p_cc_exp_month'            => '12',
    'p_cc_exp_year'             => '22',
    'p_cc_exp_date'             => '11/20',
    'p_cc_cvv'                  => '',
    'p_ach_holder_name'         => '',
    'p_ach_routing'             => '',
    'p_ach_account'             => ''
);

$mail = new PHPMailer(); // defaults to using php "mail()"
$mail->CharSet = 'UTF-8';
$mail->Encoding = "base64";
$mail->isHTML(true);

$mail->SetFrom('DoNotReply@prescriptionhope.com', 'Prescription Hope');
$mail->AddReplyTo("DoNotReply@prescriptionhope.com","Prescription Hope");

$mail->AddAddress("georgep@wowbrands.com");

$mail->Subject = "Online Application Confirmation";
$mail->Body .= "test test";

$mail->AddStringAttachment(pdf_application($data, 'S'), 'Prescription_Hope_Application.pdf');

pdf_application($data, 'I');

