<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

$success = false;
$message = '';
if(isset($_POST['file_name']) && $_POST['file_name'] !=''){

	$targetDir = "temp/".$_SESSION['PLP']['patient']->PatientID;
	$fileName = $_POST['file_name'];
	$targetFilePath = $targetDir .'/'. $fileName;
	if( file_exists($targetFilePath) ) {
		unlink($targetFilePath);
		$success = true;
		$message = "File Deleted successfully.";
	}else{
		$success = false;
		$message = "Error in deleting file.";
	}

}
$arrReturn = array(
	'success' 	=> $success,
	'message' 	=> $message,
	'fileName' =>  $fileName
);
echo json_encode($arrReturn);
die();