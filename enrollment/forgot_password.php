<?php

require_once('includes/functions.php');

session_start();
unset($_SESSION[$session_key]);
//session_destroy();

$email_address = (isset($_POST['email_address'])) ? trim($_POST['email_address']) : '';
//$phone = (isset($_POST['phone'])) ? trim($_POST['phone']) : '';
//$ssn = (isset($_POST['ssn'])) ? trim($_POST['ssn']) : '';
//$recaptcha = (isset($_POST['g-recaptcha-response'])) ? trim($_POST['g-recaptcha-response']) : '';
//$submit = (isset($_POST['email_address']) && isset($_POST['security_question']) && isset($_POST['secret_answer']) && isset($_POST['g-recaptcha-response']));
//$submit = (isset($_POST['email_address']) && isset($_POST['phone']) && isset($_POST['ssn']));
$submit = ( isset($_POST['email_address']) );

$success = true;
$message = '';

//if ($submit && $recaptcha != '') {
if ($submit) {
	//check re-captcha
	//$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LcMlBMTAAAAAE4CYrpJI4HJuuEpE_8eWgMYuXjC&response=" . $_POST['g-recaptcha-response']));
	//if ($response->success) {
		//if ($submit && $email_address != '' && $phone != '' && $ssn != '') {
		if ($submit && $email_address != '') {
			//reset password
			$data = array(
				'command'	=> 'forgot_password_new',
				'email'		=> $email_address,
				//'phone'		=> $phone,
				//'ssn'		=> $ssn
			);

			$response = api_command($data);

			// if(isset($response) && !empty($response->debugData)) {
			// 	echo "<pre>";
			// 	echo $email_address; 
			// 	var_dump($response);
			// }
			
				// echo "<pre>";
				// echo $email_address; 
				// var_dump($response);
				// exit;
			

			if (isset($response) && $response->success == 1) {
				if (session_id() == '') {
					session_start();
				}
				//password reset
				$_SESSION['success-msg'] = "Your password was reset succesfully. You'll receive the new password in an email.";
				header('Location: ../patients-dashboard/login.php');
			} elseif (isset($response) &&  $response->success == 2) {
				//wrong phone number
				$success = false;
				$message = '<div class="alert alert-danger alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a>Wrong phone number or last 4 of SSN provided, please try again.</div>';
			} else {
				//account not found
				$success = false;
				$message = '<div class="alert alert-danger alert-dismissible 1"><a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a>No account found in the system for this email address.</div>';
			}
		} elseif ($submit) {
			//invalid form
			$success = false;
			$message = '<div class="alert alert-danger alert-dismissible 2"><a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a>No account found in the system for this email address.</div>';
		}
	//} else {
		//not human
	//	$success = false;
	//	$message = 'Please try again after you verify that you are human';
	//}
} elseif ($submit) {
	//not human
	$success = false;
	$message = '<div class="alert alert-danger alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a>Please try again after you verify that you are human.</div>';
}

?>

<?php include('_header.php'); ?>

<div class="clearfix"></div>
<div class="row no-margin">

<div class="content reset-sec">
			<div class="heading">	
				<h2>Password Reset</h2>	
				<p>We just need your registered email address to send you password reset.</p>	
			</div>
			<form id="fmForgotPassword" method="POST" style="text-align:left!important;">
				<?php if($submit) { ?><div id="fmMsg" class="<?=(($success && $message != '') ? 'success' : 'error')?>"><?=$message?></div><br/><?php } ?>
				<div>
					<input type="text" name="email_address" id="email_address" value="<?=((!$success) ? $email_address : '')?>" placeholder="Email Address *">
				</div>
				
				<!--<div>
					<input type="text" name="phone" id="phone" value=" //((!$success) ? $phone : '') " placeholder="Phone *">
				</div>
				
				<div>
					<input type="text" name="ssn" id="ssn" value=" //((!$success) ? $ssn : '')?>" placeholder="Last 4 number of Social Security Number *">
				</div>-->

				<div class="required-fields"><span class="red">*</span> Required Field</div>
			</form>
		
	

	<label class="mobile-hidden">&nbsp;</label>
	<div id='recaptcha' class="g-recaptcha" data-sitekey="6LefboYUAAAAAJKoAxSTReZ4zKOQG91mGboDq_MS" data-callback="onSubmit" data-size="invisible"></div>
	<input type="button" name="btSubmit" id="btSubmit" value="Reset Password" class="big-button blue-button loginPageButton" style="margin:0px auto 0px;">


</div>
</div>


<script type="text/javascript">
var captchaValid = false;
jQuery(document).ready(function() {
	//if (jQuery("input#email_address").val() != "") {
	//	getUserSecurityQuestion();
	//}
	//
	//jQuery("input#email_address").change(getUserSecurityQuestion);

	//
	//jQuery("input[name='phone']").mask("000-000-0000", {clearIfNotMatch: true});
	//jQuery("input[name='ssn']").mask("0000", {clearIfNotMatch: true});
	
	jQuery.validator.addMethod("SSN",function(t,e){return t=t.replace(/\s+/g,""),this.optional(e)||t.length==4&&t.match(/^\d{4}$/)},"Please specify a valid SSN number");
	var form_validator =
		jQuery("#fmForgotPassword").validate({
			rules: {
				email_address:{required: true, email: true},
				//phone:{required: true, phoneUS: true},
				//ssn:{required: true, SSN: true, minlength: 4, maxlength: 4}
			},
			highlight: jQueryValidation_Highlight,
			unhighlight: jQueryValidation_Unhighlight,
			errorPlacement: jQueryValidation_ShowErrors,
			invalidHandler: refreshRadioGroupsValidationIcons,
			onkeyup: false
		});
	
	jQuery('input').jvFloat();
	jQuery('#btSubmit').on('click', function(e) {
		console.log(captchaValid);
		if(jQuery("#fmForgotPassword").valid() && !captchaValid){
			e.preventDefault();
			console.log(captchaValid);
			grecaptcha.execute();		
		}		
	});
	
});
// Google Invisible captcha
function onSubmit(token) {
	console.log('Thanks for validating');
	captchaValid = true;
	jQuery("#fmForgotPassword").submit();
}

</script>
<style>

.navbar.header-nav,footer{display:none;}
#main_content{padding-bottom: 100px;}
input.error {text-align: left !important;background: #fff;border: 1px solid #ff0000 !important;}
a.links,a.forgot-sec {padding-top: 6px;display: inline-block;}
#page-container {
    background: transparent;
}
.jvFloat .placeHolder.active {top: 16px;}
</style>
<br><br>

<?php include('_footer.php'); ?>
