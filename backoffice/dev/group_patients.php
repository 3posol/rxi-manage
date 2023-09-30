<?php

require_once('includes/functions.php');

session_start();

//check login
$agent_logged = is_agent_logged_in();
if (!$agent_logged) {
	header('Location: login.php');
}

$format = (isset($_GET['format']) ? trim($_GET['format']) : 'html');

//get agent's balance
$data = array(
	'command'		=> 'group_patients',
	'agent' 		=> $_SESSION['agent'],
	'access_code'	=> $_SESSION['access_code']
);

$enrolled_patients = api_command($data);

//Export to Excel
if ($format == 'xls') {
	export_patients_excel($enrolled_patients->patients);
	die();
}

?>

<?php include('_header.php'); ?>

<div class="">
	<h2>All My Group Agents' Book of Business</h2>

	<?php if ($enrolled_patients->success == 1 && count($enrolled_patients->patients) > 0) { ?>
		<a href="group_patients.php?format=xls">Download Excel</a>
		<br/><br/>

		<table>
			<thead>
				<tr>
					<td>Patient Name</td>
					<td>Date of Birth</td>
					<td># of Meds</td>
					<td>Status</td>
					<td>Agent Name</td>
					<td>Agent ID</td>
				</tr>
			</thead>
			<tbody>

			<?php foreach ($enrolled_patients->patients as $patient) { ?>
				<tr>
					<td><?=$patient->PatientFirstName?> <?=$patient->PatientMiddleInitial?> <?=$patient->PatientLastName?></td>
					<td class="align-center"><?=date('m/d/Y', strtotime($patient->PatientDOB))?></td>
					<td class="align-center"><?=$patient->PatientMedsNo?></td>
					<td><?=$patient->EnrollmentStatus ?></td>
					<td><?=$patient->Agent?></td>
					<td><?=$patient->AgentCode?></td>
				</tr>
			<?php } ?>

			</tbody>
		</table>
	<?php } else { ?>
		<br/>
		There are no enrolled patients in the system.
	<?php } ?>
</div>

<?php include('_footer.php'); ?>
