<?php
#sheader('Location: maintenance.html');
require_once('includes/config.php');

function db_connect () {
	// DB connect
	//
	// couldn't use the html/includes/config.php file
	//
	$db_host = $GLOBALS['db_host'];
	$db_name = $GLOBALS['db_name'];
	$db_user = $GLOBALS['db_user'];
	$db_pass = $GLOBALS['db_pass'];
	//

	if(PHP_MAJOR_VERSION == 5){
		mysql_pconnect($db_host, $db_user, $db_pass) or die(mysql_error());
		mysql_select_db($db_name) or die(mysql_error());
	}elseif (PHP_MAJOR_VERSION == 7) {
		$conn = mysqli_connect("p:".$db_host, $db_user, $db_pass) or die(mysqli_error());
		mysqli_select_db($conn, $db_name) or die(mysqli_error());
		return $conn;
	}
	//
	// end DB connect
}

function api_command ($data) {
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => $GLOBALS['RXI_API_URL'],
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => http_build_query($data),
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);
//var_dump($data['command'], $response, '----');

	if ($data['command'] == 'get_patient_data' && $_GET['debug']==1) {
		//var_dump($response); die('--functions.php==');
		//return $response;
	}
	
	if ($data['command'] == 'login') {
		//echo "<pre>";print_r($response);die();
		//var_dump($data['command'], $response, '----'); exit;
		//return $response;
	}
	
	/*if($data['command'] == 'get_invoice_data')
	{
		echo $data['command'];
		echo "<pre>";
		print_r($response);
		echo "</pre>";
	}*/
	
	return json_decode($response);
}

function is_patient_logged_in () {
	$logged = false;

	//get operation for HIPAA logs
	$page = basename($_SERVER["SCRIPT_FILENAME"]);
	switch ($page) {
		case 'change_password.php':
			if (!isset($_POST['new_password']) || !isset($_POST['new_password_confirm'])) {
				$operation = 'View -> Change Password';
			} else {
				$operation = '';
			}

			break;

		case 'pay_bill.php':
			$operation = '';
			$operation = 'Action -> Make Payment';
			break;

		default:
			$operation = 'View -> ' . ucwords(str_replace(array('.php', '_'), array('', ' '), basename($_SERVER["SCRIPT_FILENAME"])));
			break;
	}

	if (isset($_SESSION) && isset($_SESSION['PLP']['patient']->PatientID) && isset($_SESSION['PLP']['access_code'])) {
		$data = array(
			'command'		=> 'check_login',
			'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
			'access_code'	=> $_SESSION['PLP']['access_code'],
			'operation'		=> $operation,
			'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
		);

		$response = api_command($data);

		if ($response->success == 1) {
			$logged = true;
		}
	}

	return $logged;
}

function clear_patient_last_name () {
	//strip "(Closed)" from patient last name
	if (isset($_SESSION['PLP']['patient']->PatientLastName)) {
	    $_SESSION['PLP']['patient']->PatientLastName = trim(str_ireplace('(Closed)', '', $_SESSION['PLP']['patient']->PatientLastName));
	}
}

function fn_breadcrumbs( $current_page ){
	$siteUrl = 'http://prescriptionhope.';
	$siteUrl .= ($_SERVER['SERVER_NAME'] == 'prescriptionhope.staging-box.net') ? 'staging-box.net':'com';
	$html = '<div class="row"><div class="container">';
	$html .= '<p id="breadcrumbs">';
	$html .= '<span><a href="'.$siteUrl.'">Home</a></span> &raquo; ';
	if($current_page!='Dashboard'){
		$html .= '<span><a href="/html/patients-dashboard/dashboard.php">Dashboard</a></span> &raquo; ';
	}
	$html .= '<span>'.$current_page.'</span>';
	$html .= '</p>';
	$html .= '</div></div>';
	echo $html;
}
