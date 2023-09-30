<?php

require_once('includes/functions.php');

//
// Check URL
//

$url_source = filter_input(INPUT_GET, 'source', FILTER_DEFAULT, array('options' => array('default' => '')));
if ($url_source != '') {
	activate_url_properties($url_source);
}

//
$data = array(
	'first_name' 			=> '',
	'middle_initial'		=> '',
	'last_name' 			=> '',
	'email' 				=> '',
	'phone' 				=> '',
	'email_confirm'			=> '',
	'password' 				=> '',
	'password_confirmation' => '',
	'security_question' 	=> 'What was the name of the town you grew up in?',
	'secret_answer' 		=> 'ny',
	'register_terms' 		=> 0,
	'ph_register_terms_email' 		=> 0,
	'application_source'    => '' ,
	'phone'					=> ''
);

$submit = (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response']!='');

$success = true;
$message = '';
$application_source = '';
if ($submit) { 
	$data = array(
		'first_name' 			=> (isset($_POST['first_name'])) ? trim($_POST['first_name']) : '',
		'middle_initial' 		=> (isset($_POST['middle_initial'])) ? trim($_POST['middle_initial']) : '',
		'last_name' 			=> (isset($_POST['last_name'])) ? trim($_POST['last_name']) : '',
		'email' 				=> (isset($_POST['email'])) ? strtolower(trim($_POST['email'])) : '',
		'email_confirm' 		=> (isset($_POST['email_confirm'])) ? strtolower(trim($_POST['email_confirm'])) : '',
		'password' 				=> (isset($_POST['password'])) ? trim($_POST['password']) : '',
		'password_confirmation' => (isset($_POST['password_confirmation'])) ? trim($_POST['password_confirmation']) : '',
		'security_question' 	=> (isset($_POST['security_question'])) ? trim($_POST['security_question']) : 'What was the name of the town you grew up in?',
		'secret_answer' 		=> (isset($_POST['secret_answer'])) ? trim($_POST['secret_answer']) : 'ny',
		'register_terms' 		=> (isset($_POST['register_terms'])) ? 1 : 0,
		'ph_register_terms_email' 		=> (isset($_POST['ph_register_terms_email'])) ? 1 : 0,
		'application_source'    => (isset($_GET['source'])) ? $_GET['source'] : '' ,
		'phone' 				=> (isset($_POST['phone'])) ? trim($_POST['phone']) : '',
	);

	//for encoding
    $key = pack('H*', md5($data['email']));
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

	if ($data['first_name'] != '' && $data['last_name'] != '' && $data['email'] != '' && $data['phone'] != '' && $data['email'] == $data['email_confirm'] && $data['password'] != '' && $data['password_confirmation'] != '' && $data['password'] == $data['password_confirmation'] && $data['security_question'] != '' && $data['secret_answer'] != ''  && $data['ph_register_terms_email'] != 0) {
		//register
		$api_data = array(
			'command'				=> 'register',
			'first_name'			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['first_name'], MCRYPT_MODE_CFB, $iv)),
			'middle_initial'		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['middle_initial'], MCRYPT_MODE_CFB, $iv)),
			'last_name'				=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['last_name'], MCRYPT_MODE_CFB, $iv)),
			'application_source'	=> (isset($_COOKIE['url_code']) && $_COOKIE['url_code'] != '') ? $_COOKIE['url_code'] : '',
			//'email'				=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['email'], MCRYPT_MODE_CFB, $iv)),
			'email'					=> $data['email'],
			'password'				=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['password'], MCRYPT_MODE_CFB, $iv)),
			'security_question'		=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['security_question'], MCRYPT_MODE_CFB, $iv)),
			'secret_answer'			=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['secret_answer'], MCRYPT_MODE_CFB, $iv)),
			'register_terms'			=> $data['register_terms'],
			'application_source'    => $_GET['source'],
			'phone'					=> $data['phone'], 
			'application_origin'	=> 0,
			'iv'					=> base64_encode($iv)
		);
		$response = api_command($api_data);
		//echo "<pre>";print_r($api_data);die('Response');

		if (isset($response->success) && $response->success == 1) {
			//success
			$success = true;
			$message = '<center>You\'ll receive a confirmation email shortly.<br><br>Please check your email to confirm your email address.</center>';

			if (session_id() == '') {
				session_start();
			}

			$_SESSION[$session_key]['data']['id'] = $response->applicant;
			$_SESSION[$session_key]['access_code'] = md5($data['email']);

			/*if(isset($_GET['source'])){
				header('Location: enroll.php?my_broker_source='.$_GET['source']);
			}else{
				header('Location: enroll.php');	
			}*/
			header('Location: enroll.php');

		} elseif (isset($response->success) && $response->success == 2) {
			$success = false;
			$message = 'Registration failed, there is already an account for this email address.';
		} else {
			//fail
			$success = false;
			$message = 'Registration failed: Please verify that you filled out correctly all the required information. If this problem continues, please contact a patient advocate at 1-877-296-4673, option 3.';
		}
	} elseif ($submit) {
		//invalid form
		$success = false;
		$message = 'Registration failed: Please make sure you filled correctly all the information and try again.<br><br>';
	}
}

?>

<?php include('_header.php'); ?> 
<style type="text/css">
html body h1, html body h2, html body h3,html body h4, html body h5, html body h6
,html body p, html body label, html body strong, html body a, html body input, html body textarea
, html body span, html body div {font-family:Arial !important;} .text-content.text-content-mac, .text-content-mac, .already_acc {display: none;}
footer {display: none;}
html {background: #eaeaea; height:100%;}
body{background:#eaeaea;padding-top:0px;}
#page-container {background: transparent;}
</style>
<?=(($success && $submit) ? '<center><h2>Account Successfully Created</h2></center>' : '')?>
<div class="container Form-register-section">
<div class="">

<!----Testimonial---->
<div class="col-md-5 col-sm-12 col-xs-12 pull-left" id="testimonials-row">    
	<div class="">
	<div class="col-md-12 col-sm-12 col-xs-12">			
				<h2 class="heading-title"><span><img src="/enrollment/images/Account/get-start-icon.png"></span>   Get Started</h2>
			
				<div class="col-sm-12 pull-right">
				
					<div class="text-content-text"><span>You are a few steps away from saving money on your<br/> medication. To begin your enrollment, let's start with<br/> some contact information to create your account.</span></div>
					
				</div>	
				
			</div>
		<div class=" column L10 ph-desktop-view">
		
		<div class="profile-image"><a class="open_video" data-name="Theresa from Oregon" data-href="O1ZjD1DvMpQ"><img src="/enrollment/images/Account/Profile-video.png"></a>
		
		<div class="overlay-profile"><a href="#" class="open_video" data-name="Theresa from Oregon" data-href="O1ZjD1DvMpQ"><img src="/enrollment/images/Account/play.png"></a></div>
		</div>

		<div id="owl-demo" class="owl-carousel owl-theme  bg-image-profile ">
				<div class="item">	
					<div class="testimonials col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<h3>
							<span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
							<span class="text-testimonials">When the rep from Prescription Hope called to tell me I was qualified, I was speechless. They saved our home, they saved my health, they lifted that enormous financial burden at a time when we needed it most.</span>
							<p><span>- Theresa from Oregon</span></p>
						</h3>
					</div>
				</div>
				<div class="item">	
					<div class="testimonials col-lg-12 col-md-12 col-sm-12  col-xs-12">
						<h3>
							<span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
							<span class="text-testimonials">Prescription Hope has allowed me and my family to do several other things because it has freed up funds from really expensive medication.</span>
							<p><span>- Stephen from Iowa</span> </p>
						</h3>
					</div>		
				</div>
				<div class="item">
					<div class="testimonials col-lg-12 col-md-12 col-sm-12  col-xs-12">
						<h3>
							<span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
							<span class="text-testimonials">They were the most kind helpful people I have ever spoken with. The experience has been life-changing.</span>
							<p><span>- Mary From Indiana</span> </p>
						</h3>
					</div>	
				</div>
			</div>
		</div>
		<div id="video_box" style="display:none;">
			<div id="close_video_box"><img src="./images/close.jpg"></div>
			<div id="video_holder"></div>
			<div id="video_info"></div>
		</div>
	</div>

<br/><br/>
</div><!--end of container-->
<div class=" col-md-7 col-sm-12 co-xs-12 pull-right enroll-form-1">
	<div class="row">
		<form role="form" id="fmRegister" method="POST" autocomplete="nope">
			<div class="col-md-12 col-sm-12 col-xs-12">			
				
				<div class="col-sm-8 col-xs-12 pull-right">
					<div class="text-content text-content-mac"><img src="images/safe.jpg"> <span>We keep your information safe and protected. It is secured by 256-bit encryption, the same security banks use.</span></div>
					<div class="text-content-mac"><img src="images/mcafee-trans.png"></div>
				</div>	
				
			</div>				
			<div class="colorgraph col-md-12 col-sm-12 col-xs-12">
				<?php if($message != '' && !$success){ ?>
				<div class="">
					<div id="fmMsg" role="alert" style="text-align: center;" class="alert alert-info col-xs-12 col-sm-12 col-md-12 <?=(($message != '' && !$success) ? 'error' : 'bold')?>"><?=$message?>						
					</div>
				</div>
				<?php } ?>
				<div class="row">	
					<div class="col-xs-12 col-sm-4 col-md-4">
						<div class="form-group">
							<input autocomplete="nope" type="text" name="first_name" id="first_name" class="jvf form-control input-lg" placeholder="First Name *" tabindex="1" value="<?=addslashes($data['first_name'])?>">
						</div>
					</div>
					<div class="col-xs-12 col-sm-4 col-md-4">
						<div class="form-group">
							<input autocomplete="nope" type="text" name="middle_initial" id="middle_initial" class="jvf form-control input-lg" placeholder="Middle Name" tabindex="2" value="<?=addslashes($data['middle_initial'])?>" maxlength="1">
						</div>
					</div>
					<div class="col-xs-12 col-sm-4 col-md-4">
						<div class="form-group">
							<input autocomplete="nope" type="text" name="last_name" id="last_name" class="jvf form-control input-lg" placeholder="Last Name *" tabindex="3" value="<?=addslashes($data['last_name'])?>">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12">				
						<div class="form-group">
							<input autocomplete="nope" type="email" name="email" id="email" class="jvf form-control input-lg" placeholder="Email Address *" tabindex="4" value="<?=addslashes($data['email'])?>" data-hintold="Enter a valid email address. Your email address is required for communication regarding your medication orders and important updates about Prescription Hope.">
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12">
						<div class="form-group">
							<input autocomplete="nope" type="email" name="email_confirm" id="email_confirm" class="jvf form-control input-lg" placeholder="Confirm Email Address *" tabindex="5" value="<?=$data['email_confirm']?>">
						</div>
					</div>
				</div>			
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12">
						<div id="capsInfo" style="color: blue;display: block;position: absolute;top: -19px;font-size: 13px;left: 37px;"></div>
						<div class="form-group">
							<input autocomplete="nope" type="password" class="jvf form-control input-lg caps_check ttip" id="password" name="password" placeholder="Password *" data-text="Password Requirements" data-hint="Passwords must be 8-20 characters, contain a mix of uppercase and lowercase letters, and contain at least one number." tabindex="6" maxlength="20"><span class="showpassword" id="password_show"><span class="eye-open"></span></span>
						</div>							
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12">
						<div class="form-group">
							<input autocomplete="nope" type="password" class="jvf form-control input-lg caps_check" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password *" tabindex="7"  maxlength="20">								
							<span class="showpassword" id="password_confirmation_show"><span class="eye-open"></span></span>
						</div>
					</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
						<div class="form-group">
							<input autocomplete="nope" type="text" name="phone" id="phone" class="jvf form-control input-lg required customphone" placeholder="Mobile Phone Number *" tabindex="8" value="<?=addslashes($data['phone'])?>">
						</div>
					</div>
				</div>					
				<div class="checked-class">			
					<div class="condition">
					 <label >Would you like to receive text message updates?</label><br/>	
						<div class="form-check">	
					
							<label class="form-check-label cb-container checkbox-label" for="register_terms">
								<input type="checkbox" class="form-check-input checkbox-normal" id="register_terms" name="register_terms" tabindex="9"><span class="cb-checkmark"></span>								
								
								 <span>By selecting this checkbox, you agree to receive important enrollment <br/>information, 
updates, reminders, and promotional messages from Prescription<br/> Hope directly to your phone.
 Message frequency varies. Text <a tabindex="9" target="_blank" href="https://prescriptionhope.com/privacy-policy/">HELP to 964673</a> for <br/>help, <a tabindex="10" target="_blank" href="https://prescriptionhope.com/terms-of-service/">STOP to 964673</a> to stop, Message and Data Rates May Apply. 
 By opting<br/> in, you authorize Prescription Hope to deliver messages using an automatic <br/>telephone dialing system, and you
 understand that you are not required to<br/> opt-in as a condition of purchasing any property, goods, or services. By leaving<br/> this checkbox unchecked, you will not opt-in for SMS messages at this time.</span> </label>		
 <!-- <label for="register_terms" class="error" style="display:none;"></label> -->
						</div>							
							</div>
					
						<div class="condition">
						<div class="form-check">								
							<label class="form-check-label cb-container checkbox-label" for="ph_register_terms_email">
								<input type="checkbox" class="form-check-input checkbox-normal" id="ph_register_terms_email" name="ph_register_terms_email" tabindex="10"><span class="cb-checkmark"></span>								
								<span>By selecting this checkbox, you understand and agree to Prescription Hope's <a tabindex="9" target="_blank" href="https://prescriptionhope.com/privacy-policy/">privacy policy</a> and <a tabindex="10" target="_blank" href="https://prescriptionhope.com/terms-of-service/">terms of service</a> Prescription Hope will send you emails with important enrollment information,
						updates, and reminders. You can unsubscribe at any time by clicking the link at the bottom of any Prescription Hope email.*</span> </label>
							<label for="ph_register_terms_email" class="error" style="display:none;"></label>
						</div>							
									</div>
				</div>					
				<div class="row">				
					<div class="col-xs-12 col-sm-12 col-xs-12">
						<div id='recaptcha' class="g-recaptcha" data-sitekey="6LefboYUAAAAAJKoAxSTReZ4zKOQG91mGboDq_MS" data-callback="onSubmit" data-size="invisible"></div>
						<input tabindex="11" type="button" name="register_submit" id="btSubmit" value="Submit" class="big-button loginPageButton btn btn-default bt btn-block btn-lg">
						<!--<a href="#" class="btn btn-default bt btn-block btn-lg">CREATE ACCOUNT</a>-->
					</div>
				</div>
			</div>		
		</form>	
	</div>
</div>

	<div class=" column L10 ph-mobile-view">
		<div id="owl-demo-mobile" class="owl-carousel owl-theme  bg-image-profile ">
				<div class="item">	
					<div class="testimonials col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<h3>
							<span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
							<span class="text-testimonials">When the rep from Prescription Hope called to tell me I was qualified, I was speechless. They saved our home, they saved my health, they lifted that enormous financial burden at a time when we needed it most.</span>
							<p><span>- Theresa from Oregon</span></p>
						</h3>
					</div>
				</div>
				<div class="item">	
					<div class="testimonials col-lg-12 col-md-12 col-sm-12  col-xs-12">
						<h3>
							<span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
							<span class="text-testimonials">Prescription Hope has allowed me and my family to do several other things because it has freed up funds from really expensive medication.</span>
							<p><span>- Stephen from Iowa</span> </p>
						</h3>
					</div>		
				</div>
				<div class="item">
					<div class="testimonials col-lg-12 col-md-12 col-sm-12  col-xs-12">
						<h3>
							<span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
							<span class="text-testimonials">They were the most kind helpful people I have ever spoken with. The experience has been life-changing.</span>
							<p><span>- Mary From Indiana</span> </p>
						</h3>
					</div>	
				</div>
			</div>
	</div>

</div>
</div>

	<div class="container footer">
	<div class="col-sm-6 col-xs-12 safe-enroll pull-left">
			<div class="text-safe pull-left">
				<ul class="links">
				<li><a href="https://prescriptionhope.com/privacy-policy/">Privacy Policy</a></li> |
				<li><a href="https://prescriptionhope.com/terms-of-service/">Terms of Service</a></li> |
 				<li><p class="copy">2021 ©Prescription Hope, Inc.</p></li>
				</ul>
			</div>
		</div>
		<div class="col-sm-6 col-xs-12 safe-enroll pull-right">
			<div class="text-safe pull-right">
				<img class="padding-right-30 ttip" src="https://prescriptionhope.com/wp-content/themes/prescription_theme/images/new-images/256-shield.png" data-text="hidetext" data-hint="We keep your information safe and protected. It is secured by 256-bit encryption, the same security banks use." />
				<img src="images/mcafee-trans.png">
			</div>
		</div>
	</div>

<!--<div class="container">
	<div class="col-sm-12 col-xs-12 safe-enroll">
		<div class="text-safe pull-right">
			<img class="padding-right-30 ttip" src="/wp-content/themes/prescription_theme/images/new-images/256-shield.png" data-text="hidetext" data-hint="We keep your information safe and protected. It is secured by 256-bit encryption, the same security banks use." />
			<img src="images/mcafee-trans.png">
		</div>
	</div>
</div>-->

<div id="overlay" style="display:none;"><div id="overlay_holder"></div></div>
<div id="overlay_missing_email" class="overlay_content">
	<div class="overlay_loaded_content">
		<div class="overlay_form text-center">
			<p class="text-18 red">The email address entered does not match what we have on file for your account.  In order to keep your information as safe as possible, please contact a patient advocate at 1-877-296-4673 between 8:00am and 4:00pm Eastern Time.</p>
		</div>
		<div class="text-center"></div>
	</div>
</div>

<script type="text/javascript">
var captchaValid = false;
jQuery(document).ready(function() {
	jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
	jQuery.validator.addMethod("lettersonly", function(value, element) { return this.optional(element) || /^[a-z'. ]+$/i.test(value); }, "Please insert only letters.");
	//jQuery.validator.addMethod("password_aA1", function(value, element) { return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,20}$/.test(value); }, "Please use upper & lower case letters and numbers.");
	jQuery.validator.addMethod("password_aA1", function(value, element) { return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[\W\w]{8,20}$/.test(value); }, "Please use upper & lower case letters and numbers.");
	jQuery.validator.addMethod("custom_date",function(t,e){return t=t.replace(/\s+/g,""),td=t.split("/"),td=td[2]+"-"+td[0]+"-"+td[1],this.optional(e)||t.length>8&&t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/)&&td<=new Date().toISOString().substring(0,10);},"Please specify a valid date (mm/dd/yyyy)");
	//jQuery.validator.addMethod("same_as", function(value, element, param) { return this.optional(element) || value == $(param).val(); }, "");
	 // jQuery.validator.addMethod("phonenu", function (value, element) {
  //       if ( /^\d{3}-?\d{3}-?\d{4}$/g.test(value)) {
  //           return true;
  //       } else {
  //           return false;
  //       };
  //   }, "Invalid phone number");
  	jQuery.validator.addMethod("customphone", function(phone_number, element) {
		    phone_number = phone_number.replace(/\s+/g, "");
		    return this.optional(element) || phone_number.length > 9 && 
		    phone_number.match(/^(\+?1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
		}, "Please enter a valid phone number");

	//  jQuery.validator.addMethod('customphone', function (value, element) {
	//     return this.optional(element) || /^\d{3}-\d{3}-\d{4}$/.test(value);
	// }, "Please enter a valid phone number");


	formValidation = jQuery("#fmRegister").validate({
		rules: {
			first_name:				{ required: true, lettersonly: true },
			middle_initial: 		{ required: false, lettersonly: true, ascii: true, maxlength: 1 },
			last_name:				{ required: true, lettersonly: true },
			email: 					{ required: true, email: true },
			email_confirm: 			{ required: true, equalTo: '#email' },
			password: 				{ required: true, password_aA1: true, minlength: 8, maxlength: 20 },
			password_confirmation: 	{ required: true, equalTo: '#password' },
			// register_terms:			{ required: true },
			ph_register_terms_email:			{ required: true } ,
			phone :{ required: true, phoneUS: true}
		},

		messages: {
			first_name: { required: 'Please enter your first name.', lettersonly: 'Only alphabets are allowed.'},
			middle_initial: { maxlength: 'Please enter no more than 1 character.' },
			last_name: {required: 'Please enter your last name.'},
			email: { required: 'Please enter your email address.'},
			email_confirm: { required: 'Please confirm your email address.', equalTo: 'Please enter the same email address again.' },
			password: { required: 'Please enter your password.'},
			password_confirmation: { equalTo: 'Please enter the same password again.'}
		},
		highlight: function(element) {
			jQuery(element).removeClass("correct");
			jQuery(element).addClass("error");
			jQuery(element.form).find("label[for=label_for_" + element.id + "]").addClass('has-error');
		},
		unhighlight: function(element) {
			//if(jQuery(element).hasClass('error')){
				jQuery(element).removeClass("error");
				jQuery(element).removeClass("correct");
				if (jQuery(element).val() != "") {
					jQuery(element).addClass("correct");
				}
				//jQuery(element).addClass("correct");
			//}
		},	
		invalidHandler: function() {
			jQuery('#fmMsg').addClass('has-error').addClass('no-bold').html('Please fill out correctly all the fields marked with red and then try again to submit the form.');
		},
		onkeyup: false	
	});
	
	// remove check mark from the empty value
	jQuery('input').keyup(function(){
		if(jQuery(this).val()==''){
			jQuery(this).removeClass('correct');
		}
	});
	
	jQuery('input[name="email"]').on('blur', function() {
		if (formValidation.valid('#email')) {
			//add email to SalesForce
			//syncEmail();
		}
	});
	// jQuery('input[name="register_terms"]').on('click', function () {
	// 	if (jQuery('input[name="register_terms"]').is(':checked')) {
	// 		jQuery('input[name="register_terms"]').parent().parent().removeClass('checkbox-with-label-error');
	// 		jQuery('input[name="register_terms"]').parent().parent().addClass('checkbox-with-label-correct');
	// 	} else {
	// 		jQuery('input[name="register_terms"]').parent().parent().removeClass('checkbox-with-label-correct');
	// 		jQuery('input[name="register_terms"]').parent().parent().addClass('checkbox-with-label-error');
	// 	}
	// });

	jQuery('input[name="ph_register_terms_email"]').on('click', function () {
		if (jQuery('input[name="ph_register_terms_email"]').is(':checked')) {
			jQuery('input[name="ph_register_terms_email"]').parent().parent().removeClass('checkbox-with-label-error');
			jQuery('input[name="ph_register_terms_email"]').parent().parent().addClass('checkbox-with-label-correct');
		} else {
			jQuery('input[name="ph_register_terms_email"]').parent().parent().removeClass('checkbox-with-label-correct');
			jQuery('input[name="ph_register_terms_email"]').parent().parent().addClass('checkbox-with-label-error');
		}
	});
	
	jQuery('.showpassword').click(function(){
		var pswdFieldId = jQuery(this).attr('id').replace('_show','');
		var pswdType = jQuery('#'+pswdFieldId).attr('type');
		if(pswdType=='password'){
			jQuery(this).find('span').removeClass('eye-open').addClass('eye-close');
			jQuery('#'+pswdFieldId).attr('type','text');
		}
		else{
			jQuery(this).find('span').removeClass('eye-close').addClass('eye-open');
			jQuery('#'+pswdFieldId).attr('type','password');
		}
	});
	
	jQuery('#btSubmit').on('click', function(e) {
		console.log(captchaValid);
		if(jQuery("#fmRegister").valid() && !captchaValid){
			e.preventDefault();
			console.log(captchaValid);
			grecaptcha.execute();		
		}		
	});

	<?php if (!$success) { ?>
		jQuery('input[name="email"]').trigger('keyup');
		jQuery('input[name="register_terms"]').attr('checked', <?= ! (bool) $data['register_terms']?>);
		jQuery('input[name="register_terms"]').trigger('click');

		jQuery('input[name="ph_register_terms_email"]').attr('checked', <?= ! (bool) $data['ph_register_terms_email']?>);
		jQuery('input[name="ph_register_terms_email"]').trigger('click');
	<?php } ?>

	jQuery('.jvf').jvFloat();
	//jQuery('input, select').tooltip();
jQuery(".ttip").each(showTooltipsText);
	

	// lOGIC FOR IDENTIFYING CAPS ON/OFF (start)
	var isShiftPressed = false;
	var isCapsOn = false;
	jQuery('input').bind("keydown", function (e) {
		var keyCode = e.keyCode ? e.keyCode : e.which;
		if (keyCode == 16) {
			isShiftPressed = true;
		}
	});
	jQuery('input').bind("keyup", function (e) {
		var keyCode = e.keyCode ? e.keyCode : e.which;
		
		if (keyCode == 16) {
			isShiftPressed = false;
		}
		if (keyCode == 20) {
			if (isCapsOn == true) {
				isCapsOn = false;
			} else if (isCapsOn == false) {
				isCapsOn = true;
			}
		}
	});	 
	jQuery('.caps_check').bind("keypress", function (e) {
		var keyCode = e.keyCode ? e.keyCode : e.which;
		if (keyCode >= 65 && keyCode <= 90 && !isShiftPressed) {
			isCapsOn = true;			
		}
		else {
			isCapsOn = false;
		}
		if(isCapsOn == true){console.log('ON');
			jQuery('#capsInfo').html('CAPS lock key turned ON.').fadeIn(100);
		}
		else { console.log('OFF');
			if(jQuery('#capsInfo').is(':hidden')==false) { jQuery('#capsInfo').html('CAPS lock key turned OFF.').fadeOut(3000); } 
		}
	});
	// lOGIC FOR IDENTIFYING CAPS ON/OFF (end)
	//onload();
	jQuery("#owl-demo").owlCarousel({
		navigation : true, // Show next and prev buttons
		slideSpeed : 300,
		paginationSpeed : 400,
		items:1
    });

    jQuery("#owl-demo-mobile").owlCarousel({
		navigation : true, // Show next and prev buttons
		slideSpeed : 300,
		paginationSpeed : 400,
		items:1
    });
	
	jQuery('.open_video').click(function(){
		jQuery('#video_holder').html('<div class="iframe-wrapper"><iframe src="https://www.youtube.com/embed/' +jQuery(this).attr('data-href')+ '?rel=0&showinfo=0&autoplay=1" frameborder="0" allowfullscreen></iframe></div>');
		jQuery('#video_info').html('<p>Prescription Hope Story: '+jQuery(this).attr('data-name')+' </p>');
		jQuery('#video_box').show();
	});
	jQuery('#close_video_box').click(function(){
		jQuery('#video_holder, #video_info').html('');
		jQuery('#video_box').hide();
	});
});

// Google Invisible captcha
function onSubmit(token) {
	console.log('Thanks for registering with us');
	captchaValid = true;
	jQuery("#fmRegister").submit();
}

// jQuery("#phone").mask("999-9999-9999");
jQuery("input[name='phone']").mask("000-000-0000", {clearIfNotMatch: true});
</script>

<?php include('_footer.php'); ?>
