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
	'command'		=> 'agent_patients',
	'agent' 		=> $_SESSION['agent'],
	'access_code'	=> $_SESSION['access_code']
);

$enrolled_patients = api_command($data);

//Export to Excel
if ($format == 'xls') {
	export_patients_excel($enrolled_patients->patients);
	die();
}

$_SESSION['mga_super_agent'] = 1;
$_SESSION['affiliate_super_agent'] = 1;
$_SESSION['company_super_agent'] = 1;
$_SESSION['group_super_agent'] = 1;

?>

<?php include('_header.php'); ?>

<div class="">
	<h2>My Book of Business</h2>

	<?php if ($enrolled_patients->success == 1 && count($enrolled_patients->patients) > 0) { ?>
		<a href="patients.php?format=xls">Download Excel</a>
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
					<td><?=$_SESSION['agent_first_name']?> <?=$_SESSION['agent_middle_name']?> <?=$_SESSION['agent_last_name']?></td>
					<td><?=$_SESSION['agent']?></td>
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
