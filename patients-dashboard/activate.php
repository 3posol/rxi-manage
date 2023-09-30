<?php

require_once('includes/functions.php');

session_start();
unset($_SESSION['PLP']);
//session_destroy();

$success = true;
$message = '';

$code = (isset($_GET['code'])) ? trim($_GET['code']) : '';
if ($code != '') {
	$data = array(
		'command'		=> 'activate_account',
		'code' 			=> $code
	);

	$response = api_command($data);

	if ($response->success == 1) {
		//succes
		$success = true;
		$message = 'Your account has been successfully activated. Click the button below to login to your account.<br/><br/><br/><br/><a href="login.php" class="big-button">Login</a><br/>';
	} else {
		//error
		$success = false;
		$message = 'We were unable to activate you account, your activation code is invalid.<br/><br/>';
	}
} else {
	$success = false;
	$message = 'We were unable to activate you account, your activation code is invalid.<br/><br/>';
}

?>

<?php include('_header.php'); ?>

<div class="content">
	<h2>Account Activation</h2>
	<br/>

	<div id="fmMsg" class="<?=((!$success && $message != '') ? 'error' : '')?>"><?=$message?></div>

	<br/><br/>

</div>

<?php include('_footer.php'); ?>
