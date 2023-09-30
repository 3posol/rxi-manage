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
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);

$success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT, array('options' => array('default' => false)));

// Attach med to Doc (new)
foreach ($rxi_data->meds as $med) {
	if ($med->ProviderId == $_POST['provider_id']) {
		$meds[] = $med;
	}
}
foreach ($rxi_data->providers as $key => $provider) {
	if ($provider->PrvProviderId == $_POST['provider_id']){
		$provider->PrvMed = $meds;									// Attach med to Doc (new)
		echo json_encode($provider);
		die();
	}
}
?>
