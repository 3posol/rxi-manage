<?php

require_once('includes/functions.php');

session_start();

if($_REQUEST['type']!='' && $_REQUEST['type']=='closed'){
	$response = array('success' => 0, 'reapply_errmsg'=>'Unable to process your request, try after sometime.');

	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

	$data = array(
		'command'	=> 'patient_reapply',
		'data' 		=> array(
			'type' 			=> 'closed',
			'patient' 		=> $_REQUEST['pid'],
			'email' 		=> $_REQUEST['email'],
			'iv'			=> base64_encode($iv),
		)
	);
//print_r($data); 
	$response = api_command($data); //print_r($response); die('<===');
	if($response->success==1){ 
		if(isset($response->applicant) && $response->applicant>0){
			// unset the session values and set it to new 
			unset($_SESSION['PLP']);
			unset($_SESSION['PHEnroll']);
			$_SESSION['PHEnroll']['data']['id'] = $response->applicant;
			$_SESSION['PHEnroll']['access_code'] = md5($response->data->email); 
			$_SESSION['PHEnroll']['incomplete_application'] = true; //print_r($_SESSION); die;
			header('Location: ../enrollment/enroll.php');
		} else {			
			header('Location: dashboard.php');
		}
	}
	else {
		header('Location: dashboard.php');
	}
}
die('Access denied');