<?php

require_once('includes/functions.php');

session_start();

//check login
$agent_logged = is_agent_logged_in();
if (!$agent_logged) {
	header('Location: login.php');
}

$new_password = (isset($_POST['new_password']) && trim($_POST['new_password']) != '') ? trim($_POST['new_password']) : '';
$new_password_confirm = (isset($_POST['new_password_confirm']) && trim($_POST['new_password_confirm']) != '') ? trim($_POST['new_password_confirm']) : '';
$submit = (isset($_POST['new_password']) && isset($_POST['new_password_confirm']));

$success = true;
$message = '';

if ($submit && $new_password != '' && $new_password_confirm != '' && $new_password == $new_password_confirm) {
	//encode new password
	db_connect();
	$rs = mysql_query('SELECT MD5("' . $new_password . '") as encoded_password');
	$encoded_data = mysql_fetch_assoc($rs);
	$encoded_password = $encoded_data['encoded_password'];

	//process agent's new password
	$data = array(
		'command'		=> 'change_password',
		'agent' 		=> $_SESSION['agent'],
		'access_code'	=> $_SESSION['access_code'],
		'new_password'	=> $encoded_password
	);

	$response = api_command($data);

	if (isset($response->success) && $response->success == 1) {
		//success
		$success = true;
		$message = 'Your password was changed successfully.<br/><br/>';
	} else {
		//fail
		$success = false;
		$message = 'Password change has failed, please try again.<br/><br/>';
	}
} elseif ($submit) {
	//invalid form
	$success = false;
	$message = 'Please enter a valid password1.<br/><br/>';
}

?>

<?php include('_header.php'); ?>

<div class="">
	<h2>Change Password</h2>
	<br/>

	<div id="fmMsg" class="<?=((!$success && $message != '') ? 'error' : '')?>"><?=$message?></div>

	<form id="fmChangePassword" method="POST">
		<label for="new_password" class="label-long <?=((!$success) ? 'error' : '')?>">New Password</label>
		<input type="password" name="new_password" id="new_password" value="" class="<?=((!$success) ? 'error' : '')?>">
		<br/><br/>

		<label for="new_password_confirm" class="label-long <?=((!$success) ? 'error' : '')?>">Confirm New Password</label>
		<input type="password" name="new_password_confirm" id="new_password_confirm" value="" class="<?=((!$success) ? 'error' : '')?>">
		<br/><br/>

		<input type="submit" name="agent_submit" id="btSubmit" value="Change Password">
	</form>
</div>

<?php include('_footer.php'); ?>
