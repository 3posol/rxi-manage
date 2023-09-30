<?php

require_once('includes/functions.php');

session_start();

//check login
$agent_logged = is_agent_logged_in();
if (!$agent_logged) {
	header('Location: login.php');
}

if ($_SESSION['hierarchy_view'] != 1) {
	header('patients.php');
}

//get hierarchy
$data = array(
	'command'		=> 'hierarchy',
	'agent' 		=> $_SESSION['agent'],
	'access_code'	=> $_SESSION['access_code']
);

$response = api_command($data);

?>

<?php include('_header.php'); ?>

<div>
	<div class="hierarchy">
		<h2>Hierarchy</h2>
		<br/>

		<?php if (!is_null($response->hierarchy)) { ?>
			<h3><?=$response->hierarchy->name?></h3>

			<?php if (!is_null($response->hierarchy->children)) { ?>
				<?php foreach ($response->hierarchy->children as $l1_broker) { ?>
					<h4>- <?=$l1_broker->name?></h4>

					<?php if (!is_null($l1_broker->children)) { ?>
						<?php foreach ($l1_broker->children as $l2_broker) { ?>
							<h5>- <?=$l2_broker->name?></h5>

							<?php if (!is_null($l2_broker->children)) { ?>
								<?php foreach ($l2_broker->children as $l3_broker) { ?>
									<h6>- <?=$l3_broker->name?></h6>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		<?php } ?>

	</div><br/>
</div>

<?php include('_footer.php'); ?>
