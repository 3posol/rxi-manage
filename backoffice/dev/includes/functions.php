<?php

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
	mysql_pconnect($db_host, $db_user, $db_pass) or die(mysql_error());
	mysql_select_db($db_name) or die(mysql_error());
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
//var_dump($response);
	return json_decode($response);
}

function is_agent_logged_in () {
	$logged = false;
	if (isset($_SESSION) && isset($_SESSION['agent']) && isset($_SESSION['access_code'])) {
		$data = array(
			'command'		=> 'check_login',
			'agent' 		=> $_SESSION['agent'],
			'access_code'	=> $_SESSION['access_code']
		);

		$response = api_command($data);

		if ($response->success == 1) {
			$logged = true;
		}
	}

	return $logged;
}

function export_patients_excel ($patients) {
    //start excel export
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");;
    header('Content-Disposition: attachment;filename=Enrolled_Patients.xls');
    header("Content-Transfer-Encoding: binary ");

    //BOF
    echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);

    //labels
    $labels = array('First Name', 'Middle Initial', 'Last Name', 'Date of Birth', '# of Meds',  'Status', 'Agent Name', 'Agent ID');
    if (isset($patients[0]->Group)) {
        $labels[] = 'Group';
    }
    if (isset($patients[0]->Company)) {
        $labels[] = 'Company';
    }
    if (isset($patients[0]->Affiliate)) {
        $labels[] = 'Affiliate';
    }

    foreach ($labels as $key => $label) {
        echo pack("ssssss", 0x204, 8 + strlen($label), 0, $key, 0x0, strlen($label));
        echo $label;
    }

    //rows
    foreach ($patients as $key => $patient) {
        $data = $patient->PatientFirstName;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 0, 0x0, strlen($data));
        echo $data;

        $data = $patient->PatientMiddleInitial;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 1, 0x0, strlen($data));
        echo $data;

        $data = $patient->PatientLastName;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 2, 0x0, strlen($data));
        echo $data;

        $data = date('m/d/Y', strtotime($patient->PatientDOB));
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 3, 0x0, strlen($data));
        echo $data;

        $data = $patient->PatientMedsNo;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 4, 0x0, strlen($data));
        echo $data;

        $data = $patient->EnrollmentStatus;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 5, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->Agent)) ? $patient->Agent : $_SESSION['agent_first_name'] . ' ' . $_SESSION['agent_middle_name'] . ' ' . $_SESSION['agent_last_name'];
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 6, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->AgentCode)) ? $patient->AgentCode : $_SESSION['agent'];
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 7, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->Group)) ? $patient->Group : '';
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 8, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->Company)) ? $patient->Company : '';
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 9, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->Affiliate)) ? $patient->Affiliate : '';
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 10, 0x0, strlen($data));
        echo $data;
	}

   //EOF
    echo pack("ss", 0x0A, 0x00);
}