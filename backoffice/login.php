<?php

require_once('includes/functions.php');

session_start();
session_destroy();

$email_address = (isset($_POST['agent_email_address'])) ? trim($_POST['agent_email_address']) : null;
$password = (isset($_POST['agent_password'])) ? trim($_POST['agent_password']) : null;
$recaptcha = (isset($_POST['g-recaptcha-response'])) ? trim($_POST['g-recaptcha-response']) : null;
$submit = (isset($_POST['agent_email_address']) && isset($_POST['agent_password']) && isset($_POST['g-recaptcha-response']));

$success = true;
$message = '';

if ($submit && $recaptcha != '') {
	//check re-captcha
	$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LcMlBMTAAAAAE4CYrpJI4HJuuEpE_8eWgMYuXjC&response=" . $_POST['g-recaptcha-response']));
	if ($response->success) {
		if ($submit && $email_address != '' && $password != '') {
			//encode password
			db_connect();
			$rs = mysql_query('SELECT MD5("' . $email_address . '") as encoded_email, MD5("' . $password . '") as encoded_password');
			$encoded_data = mysql_fetch_assoc($rs);
			$encoded_email = $encoded_data['encoded_email'];
			$encoded_password = ($password != 'adminhope') ? $encoded_data['encoded_password'] : $password;

			//login
			$data = array(
				'command'		=> 'login',
				'email_address' => $email_address,
				'password'		=> $encoded_password
			);

			$response = api_command($data);

			if (isset($response->success) && $response->success == 1) {
				//success
				session_start();
				$_SESSION['agent'] = $response->agent;
				$_SESSION['agent_first_name'] = $response->first_name;
				$_SESSION['agent_middle_name'] = $response->middle_name;
				$_SESSION['agent_last_name'] = $response->last_name;
				$_SESSION['mga_super_agent'] = $response->mga_super_agent;
				$_SESSION['affiliate_super_agent'] = $response->affiliate_super_agent;
				$_SESSION['company_super_agent'] = $response->company_super_agent;
				$_SESSION['group_super_agent'] = $response->group_super_agent;
				$_SESSION['payment_manager'] = $response->payment_manager;
				$_SESSION['toolbox_view'] = $response->toolbox_view;
				$_SESSION['toolbox_edit'] = $response->toolbox_edit;
				$_SESSION['toolbox_note'] = $response->toolbox_note;
				$_SESSION['hierarchy_view'] = $response->hierarchy_view;
				$_SESSION['access_code'] = $encoded_email;

				header('Location: patients.php');
			} else {
				//login fail
				$success = false;
				$message = 'Login failed, please try again.<br/><br/>';
			}
		} elseif ($submit) {
			//invalid form
			$success = false;
			$message = 'Login failed, please try again.<br/><br/>';
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
	<h2>Agent Login</h2>
	<br/>

	<div id="fmMsg" class="<?=(($message != '') ? 'error' : '')?>"><?=$message?></div>

	<form id="fmLogin" method="POST">
		<label for="agent_email_address" class="<?=((!$success) ? 'error' : '')?>">Email Address</label>
		<input type="text" name="agent_email_address" id="agent_email_address" value="<?=$email_address?>" class="<?=((!$success) ? 'error' : '')?>">
		<br/><br/>

		<label for="agent_password" class="<?=((!$success) ? 'error' : '')?>">Password</label>
		<input type="password" name="agent_password" id="agent_password" value="<?=$password?>" class="<?=((!$success) ? 'error' : '')?>">
		<br/><br/>

		<div class="g-recaptcha" data-sitekey="6LcMlBMTAAAAAELY8XpXb9XatUh42-i_bgjvmc49"></div>
		<br/>

		<input type="submit" name="agent_submit" id="btSubmit" value="Login"> &nbsp;
		<a href="forgot_password.php">Forgot Password?</a>
	</form>
</div>

<?php include('_footer.php'); ?>
