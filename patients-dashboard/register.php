<?php

require_once('includes/functions.php');

$data = array(
	'email_address' 		=> '',
	'password' 				=> '',
	'password_confirmation' => '',
	'security_question' 	=> '',
	'secret_answer' 		=> '',
	//'first_name' 			=> '',
	//'middle_initial' 		=> '',
	//'last_name' 			=> '',
	'patient_id' 			=> ''
//	'dob' 					=> ''
);

//$recaptcha = (isset($_POST['g-recaptcha-response'])) ? trim($_POST['g-recaptcha-response']) : null;
//$submit = (isset($_POST['email_address']) && isset($_POST['password']) && isset($_POST['g-recaptcha-response']));
$submit = (isset($_POST['email_address']) && isset($_POST['password']));

$success = true;
$message = '';
//if ($submit && $recaptcha != '') {
if ($submit) {
	//check re-captcha
	//$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LcMlBMTAAAAAE4CYrpJI4HJuuEpE_8eWgMYuXjC&response=" . $_POST['g-recaptcha-response']));

	//if ($response->success) {
		$data = array(
			'email_address' 		=> (isset($_POST['email_address'])) ? trim($_POST['email_address']) : '',
			'password' 				=> (isset($_POST['password'])) ? trim($_POST['password']) : '',
			'password_confirmation' => (isset($_POST['password_confirmation'])) ? trim($_POST['password_confirmation']) : '',
			'security_question' 	=> (isset($_POST['security_question'])) ? trim($_POST['security_question']) : '',
			'secret_answer' 		=> (isset($_POST['secret_answer'])) ? trim($_POST['secret_answer']) : '',
			//'first_name' 			=> (isset($_POST['first_name'])) ? trim($_POST['first_name']) : '',
			//'middle_initial' 		=> (isset($_POST['middle_initial'])) ? trim($_POST['middle_initial']) : '',
			//'last_name' 			=> (isset($_POST['last_name'])) ? trim($_POST['last_name']) : '',
			'patient_id' 			=> (isset($_POST['patient_id'])) ? trim($_POST['patient_id']) : ''
			//'dob' 					=> (isset($_POST['dob'])) ? trim($_POST['dob']) : ''
		);

		//for encoding
	    $key = pack('H*', md5($data['patient_id']));
	    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
	    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		//if ($data['email_address'] != '' && $data['password'] != '' && $data['password_confirmation'] != '' && $data['password'] == $data['password_confirmation'] && $data['first_name'] != '' && $data['last_name'] != '' && $data['patient_id'] != '' && $data['dob'] != '') {
		if ($data['email_address'] != '' && $data['password'] != '' && $data['password_confirmation'] != '' && $data['password'] == $data['password_confirmation'] && $data['patient_id'] != '') { // && $data['dob'] != ''
			//register
			$api_data = array(
				'command'				=> 'register',
				'email_address'			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['email_address'], MCRYPT_MODE_CFB, $iv)),
				'password'				=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['password'], MCRYPT_MODE_CFB, $iv)),
				'security_question'		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['security_question'], MCRYPT_MODE_CFB, $iv)),
				'secret_answer'			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['secret_answer'], MCRYPT_MODE_CFB, $iv)),
				//'first_name'			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['first_name'], MCRYPT_MODE_CFB, $iv)),
				//'middle_initial'		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['middle_initial'], MCRYPT_MODE_CFB, $iv)),
				//'last_name'				=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['last_name'], MCRYPT_MODE_CFB, $iv)),
				'patient_id'			=> $data['patient_id'],
				//'dob'					=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['dob'], MCRYPT_MODE_CFB, $iv)),
				'iv'					=> base64_encode($iv)
			);

			$response = api_command($api_data);

			if (isset($response->success) && $response->success == 1) {
				//success
				$success = true;
				$message = '<center>You\'ll receive a confirmation email shortly, please activate your account.<br/><br/>Please close this browser window and check your email for further instructions.<br><br></center>';
				//header('Location: account.php');
			} elseif (isset($response->success) && $response->success == 2) {
				$success = false;
				$message = 'Registration failed, there is already an account for this patient.<br/><br/>';
			} elseif (isset($response->success) && $response->success == 3) {
				$success = false;
				//$message = 'The email address entered does not match what we have on file for your account. In order to keep your information as safe as possible, please contact a patient advocate at 1-877-296-4673 between 8:00am and 4:00pm Eastern Time.<br><br>';
				$message = 'Registration failed: Please verify your username matches the email connected to your account with Prescription Hope as well as you have entered your Patient ID correctly. If this problem continues, please contact a patient advocate at 1-877-296-4673, option 3.<br/><br/>';
			} else {
				//fail
				$success = false;
				$message = 'Registration failed: Please verify your username matches the email connected to your account with Prescription Hope as well as you have entered your Patient ID correctly. If this problem continues, please contact a patient advocate at 1-877-296-4673, option 3.<br/><br/>';
			}
		} elseif ($submit) {
			//invalid form
			$success = false;
			$message = 'Registration failed, please try again.<br/><br/>';
		}
	//} else {
		//not human
	//	$success = false;
	//	$message = 'Please try again after you verify that you are human.<br/><br/>';
	//}
//} elseif ($submit) {
	//not human
//	$success = false;
//	$message = 'Please try again after you verify that you are human.<br/><br/>';
}

?>

<?php include('_header.php'); ?>

<center>
	<h2><?=(($success && $submit) ? 'Account Successfully Created' : 'Create Your Account')?></h2>
	<br>

	<?php if (!$success || !$submit) { ?>
		You will need your Patient ID number from your Welcome Letter in order to create an account.
		<br>
		<br>
		<br>
	<?php } ?>
</center>

<div class="content createAccountBox">

	<div class="row">
		<div class="col-sm-12">

			<div id="fmMsg" class="<?=(($message != '' && !$success) ? 'error' : 'bold')?>"><?=$message?></div>


	<?php if (!$success || !$submit) { ?>

		<form id="fmRegister" method="POST" style="text-align:left!important;">
			<div class="forminputsection">
				<label for="label_for_email_address" class="<?=((!$success && $data['email_address'] == '') ? 'error' : '')?>">Username<span class="red">*</span></label>
				<br>
				<input type="text" name="email_address" id="email_address" value="<?=$data['email_address']?>" class="<?=((!$success && $data['email_address'] == '') ? 'error' : '')?>">
				<br>
				<h4 class="lightblue">HINT:</h4>Please enter the email address connected to your Prescription Hope account.
			</div>
			<br>

			<div class="forminputsection">
				<label for="label_for_password" class="">Password<span class="red">*</span></label>
				<input type="password" name="password" id="password" value="">
				<br/><br/>

				<label for="label_for_password_confirmation" class="">Retype Password<span class="red">*</span></label>
				<input type="password" name="password_confirmation" id="password_confirmation" value="" class="">
				<br>

				<h4 class="lightblue">HINT:</h4>Password must be different than your user name. A good password uses a combination of lower and upper case letters, as well as numbers. Avoid using a password that is easy for others to guess, such as your name or phone number.
			</div>
			<div class="clear"></div>

			<br/><br/>

			<div class="forminputsection">
				<label for="label_for_ecurity_question" class="">Security Question<span class="red">*</span></label>
				<br>
				<select name="security_question" id="security_question" class="form-control">
					<?php foreach ($security_questions as $question) { ?>
						<option value="<?=$question?>" <?=(($data['security_question'] == $question) ? 'selected="selected"' : '')?>><?=$question?></option>
					<?php } ?>
				</select>
				<br/><br/>

				<label for="label_for_secret_answer" class="<?=((!$success && $data['secret_answer'] == '') ? 'error' : '')?>">Secret Answer<span class="red">*</span></label>
				<input type="text" name="secret_answer" id="secret_answer" value="<?=addslashes($data['secret_answer'])?>" class="<?=((!$success && $data['secret_answer'] == '') ? 'error' : '')?>">

				<h4 class="lightblue">HINT:</h4>If you forget your password we will present you with your selected security question and ask for your secret answer. Make sure your answer is meaningful, but not easy for others to guess.
			</div>

			<div class="clear"></div>

			<br/><br/>

			<?php /*
			<label for="label_for_first_name" class="<?=((!$success && $data['first_name'] == '') ? 'error' : '')?>">First Name</label>
			<input type="text" name="first_name" id="first_name" value="<?=addslashes($data['first_name'])?>" class="<?=((!$success && $data['first_name'] == '') ? 'error' : '')?>">
			<label class="nobr left-padding-20 no-bold mobile-top-padding-5">Enter your full name as it appears on your Welcome Letter.</label>
			<br/><br class="mobile-hidden"/>

			<label for="label_for_middle_initial" class="">Middle Initial</label>
			<input type="text" name="middle_initial" id="middle_initial" value="<?=$data['middle_initial']?>" maxlength="1" class="width-20">
			<br/><br/>

			<label for="label_for_last_name" class="<?=((!$success && $data['last_name'] == '') ? 'error' : '')?>">Last Name</label>
			<input type="text" name="last_name" id="last_name" value="<?=addslashes($data['last_name'])?>" class="<?=((!$success && $data['last_name'] == '') ? 'error' : '')?>">
			<br/><br/><br/>

			*/ ?>

			<label for="label_for_patient_id" class="<?=((!$success && $data['patient_id'] == '') ? 'error' : '')?>">Patient ID Number<span class="red">*</span></label>
			<input type="text" name="patient_id" id="patient_id" value="<?=$data['patient_id']?>" class="<?=((!$success && $data['patient_id'] == '') ? 'error' : '')?>">
			<label class="nobr left-padding-20 no-bold">Please enter your Patient ID number as it appears on your Welcome Letter.</label>
			<br class="mobile-hidden"/>

			<?php /*
			<label for="label_for_dob" class="<?=((!$success && $data['dob'] == '') ? 'error' : '')?>">Date of Birth<span class="red">*</span></label>
			<input type="text" name="dob" id="dob" value="<?=$data['dob']?>" class="<?=((!$success && $data['dob'] == '') ? 'error' : '')?>">
			<label class="nobr left-padding-20 no-bold">Enter your date of birth in the MM/DD/YYYY format, using 4 digits for the year.</label>
			<br/><br/><br class="mobile-hidden"/>
			*/ ?>

			<?php /*
			<center>
				<label class="mobile-hidden">&nbsp;</label>
				<div class="g-recaptcha" data-sitekey="6LcMlBMTAAAAAELY8XpXb9XatUh42-i_bgjvmc49"></div>
				<br/><br/>
			</center>
			*/ ?>

	<?php } else { ?>
		<br/>
		<!--a href="login.php" class="big-button">LOGIN</a-->
	<?php } ?>

	</div>
	</div>

</div>

<?php if (!$success || !$submit) { ?>
	<div class="createAccountBoxFooter required-fields"><span class="red">*</span>Required Field</div>

	<center>
		<label class="mobile-hidden">&nbsp;</label>
		<br>
		<input type="submit" name="register_submit" id="btSubmit" value="Create Account" class="big-button blue-button loginPageButton" style="max-width:200px;margin:0px auto 0px;">

		<label class="mobile-hidden">&nbsp;</label>
		<br/>
		If you've already signed up, <a href="login.php">click here</a>.
	</center>

	</form>
<?php } ?>

<br><br>

<div id="overlay">
	<div id="overlay_holder">
	</div>
</div>

<div id="overlay_missing_email" class="overlay_content">

	<div class="overlay_loaded_content">

		<div class="overlay_form text-center">
			<p class="text-18 red">The email address entered does not match what we have on file for your account.  In order to keep your information as safe as possible, please contact a patient advocate at 1-877-296-4673 between 8:00am and 4:00pm Eastern Time.</p>
		</div>

		<div class="text-center">
		</div>
	</div>

</div>

<script type="text/javascript">

jQuery().ready(function() {
	jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
	jQuery.validator.addMethod("custom_date",function(t,e){return t=t.replace(/\s+/g,""),td=t.split("/"),td=td[2]+"-"+td[0]+"-"+td[1],this.optional(e)||t.length>8&&t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/)&&td<=new Date().toISOString().substring(0,10)},"Please specify a valid date (mm/dd/yyyy)"),
	//jQuery.validator.addMethod("same_as", function(value, element, param) { return this.optional(element) || value == $(param).val(); }, "");

	jQuery("#fmRegister").validate({
		rules: {
			email_address: 			{ required: true, email: true },
			password: 				{ required: true, minlength: 3 },
			password_confirmation: 	{ required: true, minlength: 3, equalTo: '#password' },
			security_question: 		{ required: true },
			secret_answer: 			{ required: true },
			//first_name:				{ required: true, ascii: true },
			//middle_initial: 		{ required: false, ascii: true, maxlength: 1 },
			//last_name:				{ required: true, ascii: true },
			patient_id:				{ required: true, digits: true }
			//dob: 					{ required: true, custom_date: true }
		},

		highlight: function(element) {
			jQuery(element).addClass("error");
			jQuery(element.form).find("label[for=label_for_" + element.id + "]").addClass('has-error');
		},

		unhighlight: function(element) {
			jQuery(element).removeClass("error");
			jQuery(element.form).find("label[for=label_for_" + element.id + "]").removeClass('has-error');
		},

		errorPlacement: function() {},

		invalidHandler: function() {
			jQuery('#fmMsg').addClass('has-error').addClass('no-bold').html('Please fill out correctly all the fields marked with red and then try again to submit the form.<br/><br/>');
		}
	});

	//<?=((isset($response->success) && $response->success == 3) ? 'showMissingEmailNotice();' : '')?>

	//add masks
	//jQuery("input#dob").mask("99/99/9999");
});

</script>

<?php include('_footer.php'); ?>
