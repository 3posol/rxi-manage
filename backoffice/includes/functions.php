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
    error_reporting(E_ALL);

    /** PHPExcel */
    require_once('includes/PHPExcel/PHPExcel.php');

    /** PHPExcel_Writer_Excel2007 */
    require_once('includes/PHPExcel/PHPExcel/Writer/Excel2007.php');

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    $objPHPExcel->setActiveSheetIndex(0);

    $letter_code = 65;
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Enrollment Form Submitted');
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'First Name');
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Middle Initial');
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Last Name');
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Date of Birth');
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', '# of Meds');
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Status');
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Agent Name');
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Agent ID');
    if (isset($patients[0]->Group)) {
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Group');
    }
    if (isset($patients[0]->Company)) {
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Company');
    }
    if (isset($patients[0]->Affiliate)) {
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . '1', 'Affiliate');
    }

    //add content to the excel file
    foreach($patients as $key => $patient) {
        $letter_code = 65;

        $data = ($patient->PatientEnrollmentDate != '') ? date('m/d/Y', strtotime($patient->PatientEnrollmentDate)) : '';
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = $patient->PatientFirstName;
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = $patient->PatientMiddleInitial;
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = $patient->PatientLastName;
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = date('m/d/Y', strtotime($patient->PatientDOB));
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = $patient->PatientMedsNo;
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = $patient->EnrollmentStatus;
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = (isset($patient->Agent)) ? $patient->Agent : $_SESSION['agent_first_name'] . ' ' . $_SESSION['agent_middle_name'] . ' ' . $_SESSION['agent_last_name'];
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = (isset($patient->AgentCode)) ? $patient->AgentCode : $_SESSION['agent'];
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = (isset($patient->Group)) ? $patient->Group : '';
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = (isset($patient->Company)) ? $patient->Company : '';
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);

        $data = (isset($patient->Affiliate)) ? $patient->Affiliate : '';
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($letter_code++) . ($key + 2), $data);
    }

    //save excel
    header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename=Enrolled_Patients.xlsx');

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $objWriter->save('php://output');
}

function export_patients_excel_old ($patients) {
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
    $labels = array('Enrollment Form Submitted', 'First Name', 'Middle Initial', 'Last Name', 'Date of Birth', '# of Meds',  'Status', 'Agent Name', 'Agent ID');
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
        $data = ($patient->PatientEnrollmentDate != '') ? date('m/d/Y', strtotime($patient->PatientEnrollmentDate)) : '';
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 0, 0x0, strlen($data));
        echo $data;

        $data = $patient->PatientFirstName;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 1, 0x0, strlen($data));
        echo $data;

        $data = $patient->PatientMiddleInitial;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 2, 0x0, strlen($data));
        echo $data;

        $data = $patient->PatientLastName;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 3, 0x0, strlen($data));
        echo $data;

        $data = date('m/d/Y', strtotime($patient->PatientDOB));
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 4, 0x0, strlen($data));
        echo $data;

        $data = $patient->PatientMedsNo;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 5, 0x0, strlen($data));
        echo $data;

        $data = $patient->EnrollmentStatus;
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 6, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->Agent)) ? $patient->Agent : $_SESSION['agent_first_name'] . ' ' . $_SESSION['agent_middle_name'] . ' ' . $_SESSION['agent_last_name'];
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 7, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->AgentCode)) ? $patient->AgentCode : $_SESSION['agent'];
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 8, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->Group)) ? $patient->Group : '';
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 9, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->Company)) ? $patient->Company : '';
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 10, 0x0, strlen($data));
        echo $data;

        $data = (isset($patient->Affiliate)) ? $patient->Affiliate : '';
        echo pack("ssssss", 0x204, 8 + strlen($data), $key + 1, 11, 0x0, strlen($data));
        echo $data;
    }

   //EOF
    echo pack("ss", 0x0A, 0x00);
}