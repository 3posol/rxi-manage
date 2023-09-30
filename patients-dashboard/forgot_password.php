<?php

require_once('includes/functions.php');

session_start();
unset($_SESSION['PLP']);
//session_destroy();

$email_address = (isset($_POST['email_address'])) ? trim($_POST['email_address']) : '';
$security_question = (isset($_POST['security_question'])) ? trim($_POST['security_question']) : '';
$secret_answer = (isset($_POST['secret_answer'])) ? trim($_POST['secret_answer']) : '';
$recaptcha = (isset($_POST['g-recaptcha-response'])) ? trim($_POST['g-recaptcha-response']) : '';
$submit = (isset($_POST['email_address']) && isset($_POST['security_question']) && isset($_POST['secret_answer']) && isset($_POST['g-recaptcha-response']));

$success = true;
$message = '';

if ($submit && $recaptcha != '') {
	//check re-captcha
	$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LcMlBMTAAAAAE4CYrpJI4HJuuEpE_8eWgMYuXjC&response=" . $_POST['g-recaptcha-response']));
	if ($response->success) {
		if ($submit && $email_address != '' && $security_question != '' && $secret_answer != '') {
			//reset password
			$data = array(
				'command'			=> 'forgot_password',
				'email_address' 	=> $email_address,
				'security_question' => $security_question,
				'secret_answer' 	=> $secret_answer
			);

			$response = api_command($data);

			if ($response->success == 1) {
				//password reset
				$message = 'Your password was reset succesfully. You\'ll receive the new password in an email.<br/><br/>';
			} elseif ($response->success == 2) {
				//wrong question/answer pair
				$success = false;
				$message = 'Wrong security question and secret answer, please try again.<br/><br/>';
			} else {
				//account not found
				$success = false;
				$message = 'No account found in the system for this email address.<br/><br/>';
			}
		} elseif ($submit) {
			//invalid form
			$success = false;
			$message = 'No account found in the system for this email address.<br/><br/>';
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

<center>
	<h2>Password Reset</h2>
	<br/>
</center>

<div class="content createAccountBox">
	<div class="row">
		<div class="col-sm-12">

			<div id="fmMsg" class="<?=(($submit && $success && $message != '') ? 'success' : 'error')?>"><?=$message?></div>
			<br/>

			<form id="fmForgotPassword" method="POST" style="text-align:left!important;">
				<label for="email_address" class="<?=((!$success) ? 'error' : '')?>">Email Address<span class="red">*</span></label>
				<input type="text" name="email_address" id="email_address" value="<?=((!$success) ? $email_address : '')?>" class="<?=((!$success) ? 'error' : '')?>">
				<br/><br/>

				<label for="security_question" class="<?=((!$success) ? 'error' : '')?>">Security Question<span class="red">*</span></label>
				<select name="security_question" id="security_question" class="form-control <?=((!$success) ? 'error' : '')?>">
					<?php foreach ($security_questions as $question) { ?>
						<option value="<?=$question?>" <?=(($security_question == $question) ? 'selected="selected"' : '')?>><?=$question?></option>
					<?php } ?>
				</select>
				<br/><br/>

				<label for="secret_answer" class="<?=((!$success) ? 'error' : '')?>">Secret Answer<span class="red">*</span></label>
				<input type="text" name="secret_answer" id="secret_answer" value="<?=addslashes($secret_answer)?>" class="<?=((!$success) ? 'error' : '')?>">
				<br/><br/>

				<center>
					<label class="mobile-hidden">&nbsp;</label>
					<div class="g-recaptcha" data-sitekey="6LcMlBMTAAAAAELY8XpXb9XatUh42-i_bgjvmc49"></div>
					<br/><br/>
				</center>

		</div>
	</div>
</div>

<div class="createAccountBoxFooter required-fields"><span class="red">*</span>Required Field</div>

<center>
	<label class="mobile-hidden">&nbsp;</label>
	<br>
	<input type="submit" name="btSubmit" id="btSubmit" value="Reset Password" class="big-button blue-button loginPageButton" style="max-width:200px;margin:0px auto 0px;">

	<label class="mobile-hidden">&nbsp;</label>
	<br/>
	<a href="login.php">Login</a>
</center>

</form>

<br><br>

<?php include('_footer.php'); ?>
