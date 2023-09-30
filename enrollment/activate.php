<?php

require_once('includes/functions.php');

session_start();
unset($_SESSION[$session_key]);
//session_destroy();

$success = true;
$message = '';

$id = (isset($_GET['id'])) ? trim($_GET['id']) : '';
$code = (isset($_GET['code'])) ? trim($_GET['code']) : '';
if ($id > 0 && $code != '') {
	$data = array(
		'command'		=> 'activate_account',
		'patient' 		=> $id,
		'code'			=> $code
	);

	$response = api_command($data);

	if ($response->success == 1) {
		//succes -> auto-login
		if (session_id() == '') {
			session_start();
		}

		$_SESSION[$session_key]['data']['id'] = $id;
		$_SESSION[$session_key]['access_code'] = $code;

		header('Location: enroll.php');

		$success = true;
		$message = 'You successfully verified your email address. Next, login to continue your enrollment.<br/><br/><br/><br/><a href="login.php" class="big-button">Login</a><br/>';
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
	<h2>Your Account Is <?=((!$success) ? 'Not' : '')?> Ready</h2>
	<br/>

	<div id="fmMsg" class="<?=((!$success && $message != '') ? 'error' : '')?>"><?=$message?></div>

	<br/><br/>

</div>

<?php include('_footer.php'); ?>
