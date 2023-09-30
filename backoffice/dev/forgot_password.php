<?php

require_once('includes/functions.php');

session_start();
session_destroy();

$email_address = (isset($_POST['agent_email_address'])) ? trim($_POST['agent_email_address']) : null;
$recaptcha = (isset($_POST['g-recaptcha-response'])) ? trim($_POST['g-recaptcha-response']) : null;
$submit = (isset($_POST['agent_email_address']) && isset($_POST['g-recaptcha-response']));

$success = true;
$message = '';

if ($submit && $recaptcha != '') {
	//check re-captcha
	$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LcMlBMTAAAAAE4CYrpJI4HJuuEpE_8eWgMYuXjC&response=" . $_POST['g-recaptcha-response']));
	if ($response->success) {
		if ($submit && $email_address != '') {
			//reset password
			$data = array(
				'command'		=> 'forgot_password',
				'email_address' => $email_address
			);

			$response = api_command($data);

			if ($response->success == 1) {
				//password reset
				$message = 'Your password was reset succesfully. You\'ll receive the new password in an email.<br/><br/>';
			} else {
				//account not found
				$success = false;
				$message = 'No account found in the system for this emal address.<br/><br/>';
			}
		} elseif ($submit) {
			//invalid form
			$success = false;
			$message = 'No account found in the system for this emal address.<br/><br/>';
		}
	} else {
		//not human
		$success = false;
		$message = 'Please try again after you verify that you are human.<br/><br/>';
	}
} elseif ($submit) {
	//not human
	$success = false;
	$message = 'Please try again after you verify that you are human.<br/><br/>';
}

?>

<?php include('_header.php'); ?>

<div class="">
	<h2>Agent Password Reset</h2>
	<br/>

	<div id="fmMsg" class="<?=(($message != '' && !$success) ? 'error' : '')?>"><?=$message?></div>

	<form id="fmForgotPassword" method="POST">
		<label for="agent_email_address" class="<?=((!$success) ? 'error' : '')?>">Email Address</label>
		<input type="text" name="agent_email_address" id="agent_email_address" value="<?=((!$success) ? $email_address : '')?>" class="<?=((!$success) ? 'error' : '')?>">
		<br/><br/>

		<div class="g-recaptcha" data-sitekey="6LcMlBMTAAAAAELY8XpXb9XatUh42-i_bgjvmc49"></div>
		<br/>

		<input type="submit" name="agent_submit" id="btSubmit" value="Reset Password"> &nbsp;
		<a href="login.php">Login</a>
	</form>
</div>

<?php include('_footer.php'); ?>
