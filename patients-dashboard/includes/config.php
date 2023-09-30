<?php

//
ini_set("memory_limit","300M");
date_default_timezone_set("America/New_York");

if ($_SERVER['SERVER_NAME'] == 'dev.manage.prescriptionhope.com') {
	//DB settings
//	$db_host = "localhost";
//	$db_name = "rxi";
//	$db_user = "root";
//	$db_pass = "";
	
	//ini_set('session.cookie_domain', 'staging-box.net');

	$db_host = "localhost";
	$db_name = "prescriptionhope_rxidb";
	$db_user = "phope_rxi";
	$db_pass = "5Fgfu0^0";

	//RXI Webservice URL
	$RXI_API_URL = "http://phope-rxi.manage.prescriptionhope.com/webservice/patients/api2.php";
	//$RXI_API_URL = "http://rxirebuild.staging-box.net/webservice/patients/api2.php";
	
        
	//$basepath = '/html/patients-dashboard-new';
	$basepath = '/patients-dashboard';
        
} elseif ($_SERVER['SERVER_NAME'] == 'localhost') {
	//DB settings
	$db_host = "localhost";
	$db_name = "rxi";
	$db_user = "root";
	$db_pass = "";

	//RXI Webservice URL
	$RXI_API_URL = "http://localhost/phope/rxi/webservice/patients/api2.php";
	
	$basepath = '/html/patients-dashboard-new';
	
} elseif ($_SERVER['SERVER_NAME'] == 'manage.prescriptionhope.com') {
    ini_set('session.cookie_domain', 'prescriptionhope.com');

	$db_host = "localhost";
	$db_name = "phope";
	$db_user = "root";
	$db_pass = "mywebw0w";

	//RXI Webservice URL
	//$RXI_API_URL = "http://64.233.245.241:43443/patients/api2.php"; // old RXI URL
	// $RXI_API_URL = "http://64.233.245.241:43444/webservice/patients/api2.php";
    $RXI_API_URL = "http://172.31.47.116/webservice/patients/api2.php"; // AWS API URL


	
	$basepath = '/patients-dashboard';
	
}elseif ($_SERVER['SERVER_NAME'] == '3.136.24.68') {
    ini_set('session.cookie_domain', 'prescriptionhope.com');

    $db_host = "localhost";
    $db_name = "phope";
    $db_user = "root";
    $db_pass = "mywebw0w";

    //RXI Webservice URL
    //$RXI_API_URL = "http://64.233.245.241:43443/patients/api2.php"; // old RXI URL
    // $RXI_API_URL = "http://64.233.245.241:43444/webservice/patients/api2.php";
    $RXI_API_URL = "http://172.31.47.116/webservice/patients/api2.php"; // AWS API URL


    
    $basepath = '/patients-dashboard';
    
}

//
//check login
$patient_logged_in = false;

//
//SalesForce Conversion Tracking
//
if (isset($_GET['j'])) {
    $SFJobID = $_GET['j'];
    setcookie('SFJobID', $SFJobID, time()+86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['sfmc_sub'])) {
    $SFSubscriberID = $_GET['sfmc_sub'];
    setcookie('SFSubscriberID', $SFSubscriberID, time()+86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['l'])) {
    $SFListID = $_GET['l'];
    setcookie('SFListID', $SFListID, time()+86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['u'])) {
    $SFUrlID = $_GET['u'];
    setcookie('SFUrlID', $SFUrlID, time()+86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['mid'])) {
    $SFMemberID = $_GET['mid'];
    setcookie('SFMemberID', $SFMemberID, time()+86400, "/", ".prescriptionhope.com");
}
if (isset($_GET['jb'])) {
    $SFJobBatchID = $_GET['jb'];
    setcookie('SFJobBatchID', $SFJobBatchID, time()+86400, "/", ".prescriptionhope.com");
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
    'AL'=>'Alabama',
    'AK'=>'Alaska',
    'AZ'=>'Arizona',
    'AR'=>'Arkansas',
    'CA'=>'California',
    'CO'=>'Colorado',
    'CT'=>'Connecticut',
    'DE'=>'Delaware',
    'DC'=>'District of Columbia',
    'FL'=>'Florida',
    'GA'=>'Georgia',
    'HI'=>'Hawaii',
    'ID'=>'Idaho',
    'IL'=>'Illinois',
    'IN'=>'Indiana',
    'IA'=>'Iowa',
    'KS'=>'Kansas',
    'KY'=>'Kentucky',
    'LA'=>'Louisiana',
    'ME'=>'Maine',
    'MD'=>'Maryland',
    'MA'=>'Massachusetts',
    'MI'=>'Michigan',
    'MN'=>'Minnesota',
    'MS'=>'Mississippi',
    'MO'=>'Missouri',
    'MT'=>'Montana',
    'NE'=>'Nebraska',
    'NV'=>'Nevada',
    'NH'=>'New Hampshire',
    'NJ'=>'New Jersey',
    'NM'=>'New Mexico',
    'NY'=>'New York',
    'NC'=>'North Carolina',
    'ND'=>'North Dakota',
    'OH'=>'Ohio',
    'OK'=>'Oklahoma',
    'OR'=>'Oregon',
    'PA'=>'Pennsylvania',
    'RI'=>'Rhode Island',
    'SC'=>'South Carolina',
    'SD'=>'South Dakota',
    'TN'=>'Tennessee',
    'TX'=>'Texas',
    'UT'=>'Utah',
    'VT'=>'Vermont',
    'VA'=>'Virginia',
    'WA'=>'Washington',
    'WV'=>'West Virginia',
    'WI'=>'Wisconsin',
    'WY'=>'Wyoming',
);
