<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

date_default_timezone_set('America/New_York');

$failed_apps_dir = '/var/www/html/apps';

$files = scandir($failed_apps_dir);
foreach ($files as $file) {
	$delete_file = true;

	if ($file != '.' && $file != '..' && $file != 'index.html' && strpos($file, 'ok-') === false) {
		$data = array();
		$data = (array) json_decode(file_get_contents($failed_apps_dir . '/'. $file));
		//http_build_query($data);

		//validate data first
		//if (isset($data['session_id']) && $data['session_id'] != '') {
		if (isset($data['p_first_name']) && $data['p_first_name'] != '' && isset($data['p_last_name']) && $data['p_last_name'] != '' && isset($data['p_phone']) && $data['p_phone'] != '' && isset($data['p_dob']) && $data['p_dob'] != '' && isset($data['p_ssn']) && $data['p_ssn'] != '') {
			//file creation time
			$data['original_date'] = date('Y-m-d H:i:s', filemtime($failed_apps_dir . '/'. $file));

		    //send data to the server
			$cu = curl_init();

			curl_setopt_array($cu, array(
				CURLOPT_URL => "http://64.233.245.241:43443/register_failed_application.php",
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($data),
				CURLOPT_RETURNTRANSFER => true
			));

			$response = curl_exec($cu);
			curl_close($cu);

			if ($response != 'SUCCESS') {
				$delete_file = false;
			}
		}
		//}

		//delete the file
		if ($delete_file) {
		    rename($failed_apps_dir . '/'. $file,$failed_apps_dir . '/ok-' . date('Y-m-d-') . $file);

			//unlink($failed_apps_dir . '/'. $file);
		}
	}
}