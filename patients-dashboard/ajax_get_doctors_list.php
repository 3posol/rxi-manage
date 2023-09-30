<?php 
require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();

if (!$patient_logged_in) {
	header('Location: login.php');
}

/*$data = array(
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> '1365070',
	'access_code'	=> '0a84a2bbe94a723cda045aa541504e43'
);*/
$data = array(
	'command'		=> 'get_doctors_list',
	'data'	  => array( 'id' => $_REQUEST['id'],'PrvFirstName'=> $_REQUEST['PrvFirstName'],'sortType'=> $_REQUEST['sortType'])
);
//echo "<pre>";print_r($data);
$rxi_data = api_command($data);
//echo "<pre>";print_r($rxi_data);echo "</pre>";
echo json_encode($rxi_data);die();
?>