<?php

//
ini_set("memory_limit", "300M");
date_default_timezone_set("America/New_York");
set_time_limit(600);

//ini_set('session.cookie_domain', '.prescriptionhope.com');


if ($_SERVER['SERVER_NAME'] == 'localhost') {
    //DB settings
    $db_host = "localhost";
    $db_name = "rxi";
    $db_user = "root";
    $db_pass = "";

    //RXI Webservice URL
    $RXI_API_URL = "http://localhost/phope/rxi/webservice/enrollment/api.php";
} elseif ($_SERVER['SERVER_NAME'] == 'dev.manage.prescriptionhope.com') {
    //ini_set('session.cookie_domain', 'staging-box.net');

    $db_host = "localhost";
    $db_name = "prescriptionhope_rxidb";
    $db_user = "phope_rxi";
    $db_pass = "5Fgfu0^0";

    //RXI Webservice URL
    //$RXI_API_URL = "http://prescriptionhope.staging-box.net/rxi/webservice/patients/api2.php";
    $RXI_API_URL = "http://phope-rxi.manage.prescriptionhope.com/webservice/enrollment/api.php";

    //$basepath = '/html/patients-dashboard';
} elseif ($_SERVER['SERVER_NAME'] == 'manage.prescriptionhope.com') {
    $db_host = "localhost";
    $db_name = "phope";
    $db_user = "root";
    $db_pass = "mywebw0w";

    //RXI Webservice URL
    //$RXI_API_URL = "http://64.233.245.241:43443/enrollment/api.php"; // old RXI URL
    //$RXI_API_URL = "http://64.233.245.241:43444/webservice/enrollment/api.php";
    $RXI_API_URL = "http://172.31.47.116/webservice/enrollment/api.php"; // AWS API URL
    //smtp
    $smtp_server = 'west.EXCH032.serverdata.net';
    $smtp_user = 'donotreply@prescriptionhope.com';
    $smtp_pass = '3Rl^U8jW'; //'j174dRS%'
} elseif ($_SERVER['SERVER_NAME'] == '3.136.24.68') {
    $db_host = "localhost";
    $db_name = "phope";
    $db_user = "root";
    $db_pass = "mywebw0w";

    //RXI Webservice URL
    //$RXI_API_URL = "http://64.233.245.241:43443/enrollment/api.php"; // old RXI URL
    //$RXI_API_URL = "http://64.233.245.241:43444/webservice/enrollment/api.php";
    $RXI_API_URL = "http://172.31.47.116/webservice/enrollment/api.php"; // AWS API URL
    //smtp
    $smtp_server = 'west.EXCH032.serverdata.net';
    $smtp_user = 'donotreply@prescriptionhope.com';
    $smtp_pass = '3Rl^U8jW'; //'j174dRS%'
}

//
//check login
$patient_logged_in = false;
$session_key = 'PHEnroll';

//
//SalesForce Conversion Tracking
//
if (isset($_GET['j'])) {
    $SFJobID = $_GET['j'];
    setcookie('SFJobID', $SFJobID, time() + 86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['sfmc_sub'])) {
    $SFSubscriberID = $_GET['sfmc_sub'];
    setcookie('SFSubscriberID', $SFSubscriberID, time() + 86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['l'])) {
    $SFListID = $_GET['l'];
    setcookie('SFListID', $SFListID, time() + 86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['u'])) {
    $SFUrlID = $_GET['u'];
    setcookie('SFUrlID', $SFUrlID, time() + 86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['mid'])) {
    $SFMemberID = $_GET['mid'];
    setcookie('SFMemberID', $SFMemberID, time() + 86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['jb'])) {
    $SFJobBatchID = $_GET['jb'];
    setcookie('SFJobBatchID', $SFJobBatchID, time() + 86400, "/", ".prescriptionhope.com");
}
//
// END - SalesForce Conversion Tracking
//

$security_questions = array(
    'What was the name of the town you grew up in?',
    'What is the mascot of the high school you graduated from?',
    'What is the name of the street your best friend in high school grew up on?',
    "What is your mother's maiden name?",
    'What city was your father born in?',
    'What is the make and model of your first car?',
    'Where did you meet your significant other?',
    'What was the color of the house you grew up in?'
);

$us_states = array(
    'AL' => 'Alabama',
    'AK' => 'Alaska',
    'AZ' => 'Arizona',
    'AR' => 'Arkansas',
    'CA' => 'California',
    'CO' => 'Colorado',
    'CT' => 'Connecticut',
    'DE' => 'Delaware',
    'DC' => 'District of Columbia',
    'FL' => 'Florida',
    'GA' => 'Georgia',
    'HI' => 'Hawaii',
    'ID' => 'Idaho',
    'IL' => 'Illinois',
    'IN' => 'Indiana',
    'IA' => 'Iowa',
    'KS' => 'Kansas',
    'KY' => 'Kentucky',
    'LA' => 'Louisiana',
    'ME' => 'Maine',
    'MD' => 'Maryland',
    'MA' => 'Massachusetts',
    'MI' => 'Michigan',
    'MN' => 'Minnesota',
    'MS' => 'Mississippi',
    'MO' => 'Missouri',
    'MT' => 'Montana',
    'NE' => 'Nebraska',
    'NV' => 'Nevada',
    'NH' => 'New Hampshire',
    'NJ' => 'New Jersey',
    'NM' => 'New Mexico',
    'NY' => 'New York',
    'NC' => 'North Carolina',
    'ND' => 'North Dakota',
    'OH' => 'Ohio',
    'OK' => 'Oklahoma',
    'OR' => 'Oregon',
    'PA' => 'Pennsylvania',
    'RI' => 'Rhode Island',
    'SC' => 'South Carolina',
    'SD' => 'South Dakota',
    'TN' => 'Tennessee',
    'TX' => 'Texas',
    'UT' => 'Utah',
    'VT' => 'Vermont',
    'VA' => 'Virginia',
    'WA' => 'Washington',
    'WV' => 'West Virginia',
    'WI' => 'Wisconsin',
    'WY' => 'Wyoming',
);

//blank data
$data_request = array(
    'p_application_source' => array(false, 1, 'application_source'),
    'p_first_name' => array(true, 1, 'first_name'),
    'p_middle_initial' => array(false, 1, 'middle_initial'),
    'p_last_name' => array(true, 1, 'last_name'),
    'p_is_minor' => array(true, 1, 'is_minor'),
    'p_parent_first_name' => array(false, 1, 'parent_first_name'),
    'p_parent_middle_initial' => array(false, 1, 'parent_middle_initial'),
    'p_parent_last_name' => array(false, 1, 'parent_last_name'),
    'p_parent_phone' => array(false, 1, 'parent_phone'),
    'p_address' => array(true, 1, 'address'),
    'p_address2' => array(false, 1, 'address2'),
    'p_city' => array(true, 1, 'city'),
    'p_state' => array(true, 1, 'state'),
    'p_zip' => array(true, 1, 'zipcode'),
    'p_phone' => array(true, 1, 'phone'),
    'p_coveragegapyes' => array(true, 1, 'coveragegapyes'),
    'p_pocketmoney' => array(true, 1, 'pocketmoney'),
    'p_fax' => array(false, 1, 'fax'),
    'p_email' => array(true, 1, 'email'),
    'p_alternate_contact_name' => array(false, 1, 'alternate_contact_name'),
    'p_alternate_phone' => array(false, 1, 'alternate_contact_phone'),
    'p_dob' => array(true, 2, 'dob'),
    'p_gender' => array(true, 2, 'gender'),
    //'p_ssn_masked'              => array(true, 2, ''),
    'p_ssn' => array(true, 2, 'ssn'),
    'p_household' => array(false, 2, 'household'),
    'p_married' => array(false, 2, 'marital_status'),
    'p_employment_status' => array(false, 2, 'employment_status'),
    'p_uscitizen' => array(false, 2, 'us_citizen'),
    'p_disabled_status' => array(false, 2, 'disabled'),
    'p_medicare' => array(false, 2, 'medicare'),
    'p_medicare_part_d' => array(false, 2, 'medicare_part_d'),
    'p_medicaid' => array(false, 2, 'medicaid'),
    'p_medicaid_denial' => array(false, 2, 'medicaid_denial'),
    'p_lis' => array(false, 2, 'lis'),
    'p_lis_denial' => array(false, 2, 'lis_denial'),
    'p_hear_about' => array(true, 2, 'hear_about'),
    'p_hear_about_1' => array(false, 2, 'hear_about_extra_1'),
    'p_hear_about_2' => array(false, 2, 'hear_about_extra_2'),
    'p_hear_about_3' => array(false, 2, 'hear_about_extra_3'),
    'p_has_salary' => array(false, 2, ''),
    'p_income_salary' => array(false, 2, 'salary'),
    'p_has_unemployment' => array(false, 2, ''),
    'p_income_unemployment' => array(false, 2, 'unemployment'),
    'p_has_pension' => array(false, 2, ''),
    'p_income_pension' => array(false, 2, 'pension'),
    'p_has_annuity' => array(false, 2, ''),
    'p_income_annuity' => array(false, 2, 'annuity'),
    'p_has_ss_retirement' => array(false, 2, ''),
    'p_income_ss_retirement' => array(false, 2, 'ss_retirement'),
    'p_has_ss_disability' => array(false, 2, ''),
    'p_income_ss_disability' => array(false, 2, 'ss_disability'),
    'p_income_annual_income' => array(false, 2, ''),
    'p_has_income' => array(false, 2, 'has_income'),
    'p_income_zero' => array(false, 2, 'zero_income'),
    'p_income_file_tax_return' => array(false, 2, 'file_tax_return'),
    'p_payment_agreement' => array(true, 4, 'payment_agreement'),
    'p_service_agreement' => array(true, 4, 'service_agreement'),
    'p_guaranty_agreement' => array(true, 4, 'guaranty_agreement'),
    'p_payment_method' => array(true, 4, 'payment_method'),
    'p_cc_type' => array(false, 4, 'cc_type'),
    'p_cc_number' => array(false, 4, 'cc_number'),
    'p_cc_exp_month' => array(false, 4, 'cc_exp_month'),
    'p_cc_exp_year' => array(false, 4, 'cc_exp_year'),
    'p_cc_exp_date' => array(false, 4, ''),
    'p_cc_cvv' => array(false, 4, 'cc_cvv'),
    'p_cc_name' => array(true, 4, 'cc_name'),
    'p_cc_address' => array(true, 4, 'cc_address'),
    'p_cc_address2' => array(false, 4, 'cc_address2'),
    'p_cc_city' => array(true, 4, 'cc_city'),
    'p_cc_state' => array(true, 4, 'cc_state'),
    'p_cc_zip' => array(true, 4, 'cc_zipcode'),
    'p_ach_holder_name' => array(false, 4, 'ach_holder_name'),
    'p_ach_routing' => array(false, 4, 'ach_routing'),
    'p_ach_account' => array(false, 4, 'ach_account')
        //'p_acknowledge_agreement'     => array(true, 4, 'acknowledge_agreement')
);
