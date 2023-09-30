<?php

require_once('includes/functions.php');

session_start();

error_reporting(0);

//check login
$agent_logged = is_agent_logged_in();
if (!$agent_logged) {
	header('Location: login.php');
}

//

$action = filter_input(INPUT_GET, "action", FILTER_SANITIZE_MAGIC_QUOTES);

$success = true;
$message = '';

if ($action == 'check') {
	if (isset($_FILES['excel_file']) && (stripos($_FILES['excel_file']['name'], '.xls') !== false || stripos($_FILES['excel_file']['name'], '.xlsx') !== false)) {

		//  Include PHPExcel_IOFactory
		include 'includes/PHPExcel/PHPExcel/IOFactory.php';

		$xlsPatients = array();
		//$xlsFile = 'example2.xlsx';
		$xlsFile = $_FILES['excel_file']['tmp_name'];

		//  Read your Excel workbook
		try {
			$inputFileType = PHPExcel_IOFactory::identify($xlsFile);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($xlsFile);
		} catch(Exception $e) {
			$success = false;
			$message = 'Could not load the file. Please try again, and make sure you upload an Excel file.';
		}

		if ($success) {
			//  Get worksheet dimensions
			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();

			//  Loop through each row of the worksheet in turn
			for ($row = 1; $row <= $highestRow; $row++){
				//  Read a row of data into an array
				$xlsPatients[] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
			}

			//get direct broker's patients
			$data = array(
				'command'		=> 'broker_patients',
				'agent' 		=> $_SESSION['agent'],
				'access_code'	=> $_SESSION['access_code']
			);
			$enrolled_patients = api_command($data);

			//
			//Compare patients lists
			//

			$extra_patients = array();
			foreach ($enrolled_patients->patients as $rxi_patient) {
				$patient_match = false;
				foreach ($xlsPatients as $broker_patient) {
					if ($broker_patient[0][0] == $rxi_patient->PatientFirstName && $broker_patient[0][1] == $rxi_patient->PatientLastName && date('m/d/Y', strtotime($broker_patient[0][2])) == date('m/d/Y', strtotime($rxi_patient->PatientDOB))) {
						$patient_match = true;
						break;
					}
				}

				if (!$patient_match) {
					$extra_patients[] = $rxi_patient;
				}
			}
		}

		$message = (count($extra_patients) > 0) ? count($extra_patients) . ' extra patient' . ((count($extra_patients) > 1) ? 's were ' : ' was ') . ' found:' : 'No extra patients were found.';
	} else {
		$success = false;
		$message = 'Could not load the file. Please try again, and make sure you upload an Excel file.';
	}
}

?>

<?php include('_header.php'); ?>

<div class="">
	<h2>Patients Payment Check</h2>
	<br/>

	<div class="padding-20 bg-dark-blue">
		<p>To generate an exception report showing which enrollees are not in your immediate group and may not have their service fees covered by your payment method on file, please import an Excel file with the following guidelines.</p>
		<br/>

		<p>The excel file needs to contain these three columns:</p>
		<ul>
			<li>Patient First Name</li>
			<li>Patient Last Name</li>
			<li>Patient DOB</li>
		</ul>

		<p>If other columns are present in between, the search will not work properly.</p>
		<br/>

		<p>Once the Excel file has been imported the system will generate a report, based off each enrollee’s first and last name and date of birth, and will give you a report showing any enrollees who are not on the Excel file you imported.</p>
		<br/>

		<p>The list that is generated can be used in-connection with your listing of enrollees in the back office to remove your payment method from those enrollees and alert us to contact them to collect payment directly at the link-discounted rate.</p>
		<br/>

		<form method='post' enctype="multipart/form-data" action='patients_verification.php?action=check'>
			<label for="file" class="label-long">Import Excel File</label>
			<input type="file" name="excel_file" id="excel_file" value="" class="">
			<br/><br/>

			<input type="submit" name="file_submit" id="btSubmit" value="Submit">
		</div>
		</form>
	</div>
	<br/>

	<?php if ($message != '') { ?>
		<div id="fmMsg" class="<?=(($success) ? '' : 'error')?>"><?=(($success) ? '<h3>' : '')?><?=$message?><?=(($success) ? '</h3>' : '')?></div>
	<?php } ?>

	<?php if ($success && $message != '' && count($extra_patients) > 0) { ?>
		<br/>

		<table>
			<thead class="no-separator">
				<tr>
					<td>Patient Name</td>
					<td>Date of Birth</td>
				</tr>
			</thead>
			<tbody>

			<?php foreach ($extra_patients as $patient) { ?>
				<tr class="top-separator">
					<td><?=$patient->PatientFirstName?> <?=$patient->PatientLastName?></td>
					<td class="align-center"><?=date('m/d/Y', strtotime($patient->PatientDOB))?></td>
				</tr>
			<?php } ?>

			</tbody>
		</table>
	<?php } ?>
</div>

<?php include('_footer.php'); ?>
