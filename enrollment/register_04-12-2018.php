<?php

require_once('includes/functions.php');

$data = array(
	'first_name' 			=> '',
	'middle_initial'		=> '',
	'last_name' 			=> '',
	'email' 				=> '',
	'email_confirm'			=> '',
	'password' 				=> '',
	'password_confirmation' => '',
	'security_question' 	=> '',
	'secret_answer' 		=> '',
	'register_terms' 		=> 0
);

$submit = (isset($_POST['email']) && isset($_POST['password']));

$success = true;
$message = '';
if ($submit) {
	$data = array(
		'first_name' 			=> (isset($_POST['first_name'])) ? trim($_POST['first_name']) : '',
		'middle_initial' 		=> (isset($_POST['middle_initial'])) ? trim($_POST['middle_initial']) : '',
		'last_name' 			=> (isset($_POST['last_name'])) ? trim($_POST['last_name']) : '',
		'email' 				=> (isset($_POST['email'])) ? strtolower(trim($_POST['email'])) : '',
		'email_confirm' 		=> (isset($_POST['email_confirm'])) ? strtolower(trim($_POST['email_confirm'])) : '',
		'password' 				=> (isset($_POST['password'])) ? trim($_POST['password']) : '',
		'password_confirmation' => (isset($_POST['password_confirmation'])) ? trim($_POST['password_confirmation']) : '',
		'security_question' 	=> (isset($_POST['security_question'])) ? trim($_POST['security_question']) : '',
		'secret_answer' 		=> (isset($_POST['secret_answer'])) ? trim($_POST['secret_answer']) : '',
		'register_terms' 		=> (isset($_POST['register_terms'])) ? 1 : 0
	);

	//for encoding
    $key = pack('H*', md5($data['email']));
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

	if ($data['first_name'] != '' && $data['last_name'] != '' && $data['email'] != '' && $data['email'] == $data['email_confirm'] && $data['password'] != '' && $data['password_confirmation'] != '' && $data['password'] == $data['password_confirmation'] && $data['security_question'] != '' && $data['secret_answer'] != '' && $data['register_terms'] != 0) {
		//register
		$api_data = array(
			'command'				=> 'register',
			'first_name'			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['first_name'], MCRYPT_MODE_CFB, $iv)),
			'middle_initial'		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['middle_initial'], MCRYPT_MODE_CFB, $iv)),
			'last_name'				=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['last_name'], MCRYPT_MODE_CFB, $iv)),
			//'email'				=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['email'], MCRYPT_MODE_CFB, $iv)),
			'email'					=> $data['email'],
			'password'				=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['password'], MCRYPT_MODE_CFB, $iv)),
			'security_question'		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['security_question'], MCRYPT_MODE_CFB, $iv)),
			'secret_answer'			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['secret_answer'], MCRYPT_MODE_CFB, $iv)),
			'iv'					=> base64_encode($iv)
		);

		$response = api_command($api_data);

		if (isset($response->success) && $response->success == 1) {
			//success
			$success = true;
			$message = '<center>You\'ll receive a confirmation email shortly.<br><br>Please check your email to confirm your email address.</center>';

			if (session_id() == '') {
				session_start();
			}

			$_SESSION[$session_key]['data']['id'] = $response->applicant;
			$_SESSION[$session_key]['access_code'] = md5($data['email']);

			header('Location: enroll.php');
		} elseif (isset($response->success) && $response->success == 2) {
			$success = false;
			$message = 'Registration failed, there is already an account for this email address.<br><br><br>';
		} else {
			//fail
			$success = false;
			$message = 'Registration failed: Please verify that you filled out correctly all the required information. If this problem continues, please contact a patient advocate at 1-877-296-4673, option 3.<br><br><br>';
		}
	} elseif ($submit) {
		//invalid form
		$success = false;
		$message = 'Registration failed: Please make sure you filled correctly all the information and try again.<br><br>';
	}
}

?>

<?php include('_header.php'); ?>

<center>
	<h2><?=(($success && $submit) ? 'Account Successfully Created' : 'Create Your Account To Begin Your Enrollment')?></h2>
	<br>
</center>

<div class="content createAccountBox">

	<div class="row">
		<div class="col-sm-12">

			<div id="fmMsg" class="<?=(($message != '' && !$success) ? 'error' : 'bold')?>"><?=$message?></div>

	<?php if (!$success || !$submit) { ?>

		<p class="normal align-left">
			Fields with asterisks (<span class="red">*</span>) are required.
		</p>

		<form id="fmRegister" method="POST" autocomplete="off">

			<div class="">
				<div class="third-width"><input autocomplete="off" type="text" name="first_name" id="first_name" value="<?=addslashes($data['first_name'])?>" class=LoNotSensitive "<?=((!$success && $data['first_name'] == '') ? 'error' : ((!$success && $data['first_name'] != '') ? 'correct' : ''))?>" placeholder="First Name *"></div>
				<div class="third-width"><input autocomplete="off" type="text" name="middle_initial" id="middle_initial" value="<?=addslashes($data['middle_initial'])?>" class="LoNotSensitive" placeholder="Middle Initial" maxlength="1"></div>
				<div class="third-width"><input autocomplete="off" type="text" name="last_name" id="last_name" value="<?=addslashes($data['last_name'])?>" class="LoNotSensitive <?=((!$success && $data['last_name'] == '') ? 'error' : ((!$success && $data['last_name'] != '') ? 'correct' : ''))?>" placeholder="Last Name *"></div>
			</div>
			<div class="clear"></div>

			<p class="normal no-bottom-margin">Your email address will also be your username when you log in.</p>
			<div><input autocomplete="off" type="text" name="email" id="email" value="<?=$data['email']?>" class="LoNotSensitive <?=((!$success && $data['email'] == '') ? 'error' : ((!$success && $data['email'] != '') ? 'correct' : ''))?>" placeholder="Email Address *"></div>
			<p class="normal"><strong>Enter a valid email address.</strong><br>Your email address is required for communication regarding your medication orders and important updates about Prescription Hope.</p>
			<div class="hide register-email-confirm"><input autocomplete="off" type="text" name="email_confirm" id="email_confirm" value="<?=$data['email_confirm']?>" class="LoNotSensitive <?=((!$success && $data['email_confirm'] == '') ? 'error' : ((!$success && $data['email_confirm'] != '') ? 'correct' : ''))?>" placeholder="Confirm Your Email Address *"></div>

			<div class="hide register-part-2">
				<p class="normal no-bottom-margin">Use: <span id="password-8-20" class="label-check-list neutral">8-20 Characters</span> <span id="password-upper-lower" class="label-check-list neutral">Upper & Lowercase Letters</span> <span id="password-digits" class="label-check-list neutral">Number(s)</span></p>
				<div><input autocomplete="off" type="password" name="password" id="password" class="" value="" placeholder="Password *"></div>
				<p class="normal bold">Passwords must be 8-20 characters, contain a mix of uppercase and lowercase letters, and contain at least one number.</p>
				<div><input autocomplete="off" type="password" name="password_confirmation" id="password_confirmation" value="" class="" placeholder="Retype Password *"></div>

				<p class="normal extra-top-margin">Pick a question that only you will be able to answer. If you forget your password, we'll ask you this question to verify your identity.</p>

				<div>
					<select autocomplete="off" name="security_question" id="security_question" class="full-width <?=((!$success && $data['secret_answer'] == '') ? 'error' : ((!$success && $data['security_question'] != '') ? 'correct' : ''))?> LoNotSensitive" placeholder="Pick a Question *">
						<option value=""></option>
						<?php foreach ($security_questions as $question) { ?>
							<option value="<?=$question?>" <?=(($data['security_question'] == $question) ? 'selected="selected"' : '')?>><?=$question?></option>
						<?php } ?>
					</select>
				</div>

				<div><input autocomplete="off" type="text" name="secret_answer" id="secret_answer" value="<?=addslashes($data['secret_answer'])?>" class="<?=((!$success && $data['secret_answer'] == '') ? 'error' : ((!$success && $data['secret_answer'] != '') ? 'correct' : ''))?> LoNotSensitive" placeholder="Type an Answer"></div>
				<div class="clear"></div>
				<br>

				<div class="full-width checkbox-with-label right-padding-35 align-left">
					<label for="register_terms" class="cb-container">I understand and agree with the Prescription Hope <a href="//prescriptionhope.com/wp-content/uploads/2014/08/PH_PrivacyPolicy.pdf" class="alternative">privacy policy</a> and <a href="//prescriptionhope.com/wp-content/uploads/2014/08/PH_PrivacyPolicy.pdf" class="alternative">terms and conditions</a>.<br><br>Prescription Hope will send you emails with important enrollment information, updates and reminders. You can unsubscribe at any time by clicking the link at the bottom of any Prescription Hope email.
						<input autocomplete="off" type="checkbox" id="register_terms" name="register_terms" value="1" class="LoNotSensitive">
						<span class="cb-checkmark"></span>
					</label>
				</div>

				<center>
					<label class="mobile-hidden">&nbsp;</label><br>
					<input type="submit" name="register_submit" id="btSubmit" value="Create Account" class="big-button blue-button loginPageButton">

					<label class="mobile-hidden">&nbsp;</label><br>
					<a href="login.php" class="alternative">Already have an account? Log in.</a>
				</center>
			</div>

			<div class="clear"></div>

	<?php } else { ?>

		<br>

	<?php } ?>

	</div>
	</div>

</div>

<?php if (!$success || !$submit) { ?>
	<div class="createAccountBoxFooter">
		<div class="hide register-part-2">
		</div>

		<div class="align-center register-part-1-footer">
			If you already have an account <a href="login.php" class="dblue-text underline">Log In Here</a>.<br>
			Having trouble logging in? Forgot your <a href="forgot_password.php" class="dblue-text underline">password</a> or <a href="forgot_password.php" class="dblue-text underline">username</a>.
		</div>
	</div>

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

<script type="text/javascript" src="js/hideShowPassword.min.js"></script>
<script type="text/javascript">

jQuery().ready(function() {
	jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
	jQuery.validator.addMethod("lettersonly", function(value, element) { return this.optional(element) || /^[a-z'. ]+$/i.test(value); }, "Please insert only letters.");
	//jQuery.validator.addMethod("password_aA1", function(value, element) { return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,20}$/.test(value); }, "Please use upper & lower case letters and numbers.");
	jQuery.validator.addMethod("password_aA1", function(value, element) { return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[\W\w]{8,20}$/.test(value); }, "Please use upper & lower case letters and numbers.");
	jQuery.validator.addMethod("custom_date",function(t,e){return t=t.replace(/\s+/g,""),td=t.split("/"),td=td[2]+"-"+td[0]+"-"+td[1],this.optional(e)||t.length>8&&t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/)&&td<=new Date().toISOString().substring(0,10)},"Please specify a valid date (mm/dd/yyyy)"),
	//jQuery.validator.addMethod("same_as", function(value, element, param) { return this.optional(element) || value == $(param).val(); }, "");

	formValidation = jQuery("#fmRegister").validate({
		rules: {
			first_name:				{ required: true, lettersonly: true },
			middle_initial: 		{ required: false, ascii: true, maxlength: 1 },
			last_name:				{ required: true, lettersonly: true },
			email: 					{ required: true, email: true },
			email_confirm: 			{ required: true, email: true, equalTo: '#email' },
			password: 				{ required: true, password_aA1: true, minlength: 8, maxlength: 20 },
			password_confirmation: 	{ required: true, password_aA1: true, minlength: 8, maxlength: 20, equalTo: '#password' },
			security_question: 		{ required: true },
			secret_answer: 			{ required: true },
			register_terms:			{ required: true }
		},

		messages: {
			middle_initial: {
				maxlength: 'Please enter no more than 1 character.'
			},
			email_confirm: {
				equalTo: 'Please enter the same email address again.'
			},
			password_confirmation: {
				equalTo: 'Please enter the same password again.'
			}
		},

		highlight: jQueryValidation_Highlight,

		unhighlight: jQueryValidation_Unhighlight,

		errorPlacement: jQueryValidation_ShowErrors,

		//highlight: function(element) {
		//	jQuery(element).removeClass("correct");
		//	jQuery(element).addClass("error");
			//jQuery(element.form).find("label[for=label_for_" + element.id + "]").addClass('has-error');
		//},

		//unhighlight: function(element) {
		//	jQuery(element).removeClass("error");
		//	jQuery(element).addClass("correct");
		//	//jQuery(element.form).find("label[for=label_for_" + element.id + "]").removeClass('has-error');
		//},

		//errorPlacement: function() {},

		invalidHandler: function() {
			//jQuery('#fmMsg').addClass('has-error').addClass('no-bold').html('Please fill out correctly all the fields marked with red and then try again to submit the form.<br><br>');
		},

		onkeyup: false
	});

	jQuery('input[name="email"]').on('blur', function(e) {
		if (formValidation.check('#email')) {
			//add email to SalesForce
			syncEmail();
		}
	});

	jQuery('input[name="first_name"],input[name="last_name"],input[name="email"]').on('input keyup', function(e) {
		//if (jQuery('input[name="first_name"]').valid() && jQuery('input[name="last_name"]').valid() && jQuery('input[name="email"]').valid()) {
		//	jQuery('.register-part-2').removeClass('hide');
		//	jQuery('.register-part-1-footer').addClass('hide');
		//}

		if (formValidation.check('#first_name') && formValidation.check('#last_name') && formValidation.check('#email')) {
			jQuery('.register-email-confirm').removeClass('hide');
		}
	});

	jQuery('input[name="first_name"],input[name="last_name"],input[name="email"],input[name="email_confirm"]').on('input keyup', function(e) {
		//if (jQuery('input[name="first_name"]').valid() && jQuery('input[name="last_name"]').valid() && jQuery('input[name="email"]').valid()) {
		//	jQuery('.register-part-2').removeClass('hide');
		//	jQuery('.register-part-1-footer').addClass('hide');
		//}

		if (formValidation.check('#first_name') && formValidation.check('#last_name') && formValidation.check('#email') && formValidation.check('#email_confirm')) {
			jQuery('.register-part-2').removeClass('hide');
			jQuery('.register-part-1-footer').addClass('hide');

			jQuery('#password,#password_confirmation').hideShowPassword({show: false, innerToggle: true, toggle: {verticalAlign: 'top', offset: 2, attr: {tabindex: -1}}, wrapper: {enforceWidth: false}});
			jQuery('#password,#password_confirmation').keyup(function (e) {
				if (jQuery(this).val() == '') {
					jQuery(this).css('padding','13px 10px 13px 10px');
				} else {
					jQuery(this).css('padding', '20px 10px 6px 10px');
				}
			});
		}
	});

	jQuery('input[name="password"]').keyup(function (e) {
		jQuery('#password-8-20').removeClass('neutral').removeClass('checked')
		jQuery('#password-upper-lower').removeClass('neutral').removeClass('checked')
		jQuery('#password-digits').removeClass('neutral').removeClass('checked')

		pswd = jQuery(this).val();
		jQuery('#password-8-20').addClass((pswd.length >= 8 && pswd.length <= 20) ? 'checked' : 'neutral');
		jQuery('#password-upper-lower').addClass((pswd.match('[a-z]') && pswd.match('[A-Z]')) ? 'checked' : 'neutral');
		jQuery('#password-digits').addClass((pswd.match('[0-9]')) ? 'checked' : 'neutral');
	});

	jQuery('input[name="register_terms"]').on('click', function (e) {
		if (jQuery('input[name="register_terms"]').is(':checked')) {
			jQuery('input[name="register_terms"]').parent().parent().removeClass('checkbox-with-label-error');
			jQuery('input[name="register_terms"]').parent().parent().addClass('checkbox-with-label-correct');
		} else {
			jQuery('input[name="register_terms"]').parent().parent().removeClass('checkbox-with-label-correct');
			jQuery('input[name="register_terms"]').parent().parent().addClass('checkbox-with-label-error');
		}
	});

	jQuery('form#fmRegister').on('submit', function(e) {
		if (!jQuery('input[name="register_terms"]').is(':checked')) {
			e.preventDefault();
			jQuery('input[name="register_terms"]').parent().parent().removeClass('checkbox-with-label-correct');
			jQuery('input[name="register_terms"]').parent().parent().addClass('checkbox-with-label-error');
		}
	});

	<?php if (!$success) { ?>
		jQuery('input[name="email"]').trigger('keyup');
		jQuery('input[name="register_terms"]').attr('checked', <?= ! (bool) $data['register_terms']?>);
		jQuery('input[name="register_terms"]').trigger('click');
	<?php } ?>

	jQuery('input,select').jvFloat();
});

</script>

<?php include('_footer.php'); ?>
