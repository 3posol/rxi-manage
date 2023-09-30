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
		'command'		=> 'confirm_email',
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
		$message = 'You successfully confirmed your email address.<br/><br/><br/>';
	} else {
		//error
		$success = false;
		$message = 'We were unable to confirm your email address, your confirmation code is invalid.<br/><br/>';
	}
} else {
	$success = false;
	$message = 'We were unable to confirm your email address, your confirmation code is invalid.<br/><br/>';
}

?>

<?php include('_header.php'); ?>

<div class="content">
	<h2>Your Email Address Was <?=((!$success) ? 'Not' : '')?> Confirmed</h2>
	<br/>

	<div id="fmMsg" class="<?=((!$success && $message != '') ? 'error' : '')?>"><?=$message?></div>

	<br/><br/>

</div>

<?php include('_footer.php'); ?>
