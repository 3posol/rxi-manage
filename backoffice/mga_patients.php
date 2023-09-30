<?php

require_once('includes/functions.php');

session_start();

//check login
$agent_logged = is_agent_logged_in();
if (!$agent_logged) {
	header('Location: login.php');
}

$patient_id = filter_input(INPUT_GET, 'patient_id', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
$patient_name = filter_input(INPUT_GET, "patient_name", FILTER_SANITIZE_MAGIC_QUOTES, array('options' => array('default' => '')));
$action = filter_input(INPUT_GET, "action", FILTER_SANITIZE_MAGIC_QUOTES, array('options' => array('default' => '')));
//
$format = (isset($_GET['format']) ? trim($_GET['format']) : 'html');

if ($action == 'not_in_group') {
	//send email to Bryan
	//
	require_once('../phpmailer/class.phpmailer.php');
	$mail = new PHPMailer(); // defaults to using php "mail()"
	$mail->CharSet = 'UTF-8';
	$mail->Encoding = "base64";
	$mail->isHTML(true);

	$mail->SetFrom('DoNotReply@prescriptionhope.com', 'Prescription Hope');
	$mail->AddAddress('bryan@prescriptionhope.com');
	//$mail->AddAddress('georgep@wowbrands.com');
	$mail->Subject = 'Remove Patient From Employer Group Payment';
	$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>Patient " . $patient_name . ' (#' . $patient_id . ") needs to be removed from the group payment and pay on their own.</p>";

	$mail->Send();

	header('Location: '. basename(__FILE__));
}

//get agent's balance
$data = array(
	'command'		=> 'mga_patients',
	'agent' 		=> $_SESSION['agent'],
	'access_code'	=> $_SESSION['access_code']
);

$enrolled_patients = api_command($data);

$has_meds_details = false;
foreach($enrolled_patients->patients as $patient) {
	if (isset($patient->PatientMeds)) {
		$has_meds_details = true;
		break;
	}
}

//Export to Excel
if ($format == 'xls') {
	export_patients_excel($enrolled_patients->patients);
	die();
}

?>

<?php include('_header.php'); ?>

<div class="">
	<h2>All My Agents' Book of Business</h2>

	<?php if ($enrolled_patients->success == 1 && count($enrolled_patients->patients) > 0) { ?>
		<a href="mga_patients.php?format=xls">Download Excel</a>
		<br/><br/>

		<table>
			<thead class="no-separator">
				<tr>
					<td align="center">Enrollment Form<br>Submitted</td>
					<td>Patient Name</td>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td align="center">Date of Birth</td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td># of Meds</td><?php } ?>
					<?php if ($has_meds_details) { ?>
						<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>Drug Name</td><?php } ?>
						<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>Annual Date</td><?php } ?>
						<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>Date Ordered</td><?php } ?>
					<?php } ?>
					<td>Status</td>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td align="left">Agent Name</td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>Agent ID</td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>Group</td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>Company</td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>Affiliate</td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td class="no-separator">&nbsp;</td><?php } ?>
				</tr>
			</thead>
			<tbody>

			<?php foreach ($enrolled_patients->patients as $patient) { ?>
				<tr class="top-separator">
					<td class="align-center"><?=(($patient->PatientEnrollmentDate != '') ? date('m/d/Y', strtotime($patient->PatientEnrollmentDate)) : '')?></td>
					<td><?=$patient->PatientFirstName?> <?=$patient->PatientMiddleInitial?> <?=$patient->PatientLastName?></td>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td class="align-center"><?=date('m/d/Y', strtotime($patient->PatientDOB))?></td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td class="align-center"><?=$patient->PatientMedsNo?></td><?php } ?>
					<?php if (is_array($patient->PatientMeds) && count($patient->PatientMeds) > 0) { ?>
						<?php if (!(bool) $_SESSION['annual_commission']) { ?><td><?=$patient->PatientMeds[0]->Name?></td><?php } ?>
						<?php if (!(bool) $_SESSION['annual_commission']) { ?><td class="align-center"><?=(($patient->PatientMeds[0]->AnnualDate != '') ? date('m/d/Y', strtotime($patient->PatientMeds[0]->AnnualDate)) : '')?></td><?php } ?>
						<?php if (!(bool) $_SESSION['annual_commission']) { ?><td class="align-center"><?=(($patient->PatientMeds[0]->ReorderDate != '') ? date('m/d/Y', strtotime($patient->PatientMeds[0]->ReorderDate)) : '')?></td><?php } ?>
					<?php } elseif ($has_meds_details) { ?>
						<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>&nbsp;</td><?php } ?>
						<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>&nbsp;</td><?php } ?>
						<?php if (!(bool) $_SESSION['annual_commission']) { ?><td>&nbsp;</td><?php } ?>
					<?php } ?>
					<td><?=$patient->EnrollmentStatus ?></td>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td><?=$patient->Agent?></td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td class="nobr"><?=$patient->AgentCode?></td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td><?=$patient->Group?></td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td><?=$patient->Company?></td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?><td><?=$patient->Affiliate?></td><?php } ?>
					<?php if (!(bool) $_SESSION['annual_commission']) { ?>
						<td class="nobr no-separator">
							<?php if ($patient->HasPayment == 1) { ?>
								<a href="?patient_id=<?=$patient->PatientID?>&patient_name=<?=$patient->PatientFirstName?> <?=(($patient->PatientMiddleInitial != '') ? $patient->PatientMiddleInitial . ' ' : '')?><?=$patient->PatientLastName?>&action=not_in_group" class="button nobr">Not In My Group</a> (<a href="#" rel="Clicking on this button will remove the client from your group payment option. Your corporate payment will not be charged for this client's service fees once we receive a payment option from the individual.">?</a>)
							<?php } ?>
						</td>
					<?php } ?>
				</tr>
				<?php if (is_array($patient->PatientMeds) && count($patient->PatientMeds) > 1) { ?>
					<?php foreach ($patient->PatientMeds as $key => $med) { if ($key == 0) {continue;} ?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td><?=$med->Name?></td>
							<td class="align-center"><?=(($med->AnnualDate != '') ? date('m/d/Y', strtotime($med->AnnualDate)) : '')?></td>
							<td class="align-center"><?=(($med->ReorderDate != '') ? date('m/d/Y', strtotime($med->ReorderDate)) : '')?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td class="no-separator">&nbsp;</td>
						</tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>

			</tbody>
		</table>
	<?php } else { ?>
		<br/>
		There are no enrolled patients in the system.
	<?php } ?>
</div>

<?php include('_footer.php'); ?>
