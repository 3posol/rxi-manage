<?php

require_once('includes/functions.php');

session_start();
//unset($_SESSION[$session_key]);
//session_destroy();
//check login

$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}
//echo '<pre>'; print_r($_SESSION); echo '</pre>';
$success = true;
$message = '';

$id = (isset($_SESSION['PHEnroll']['data']['id'])) ? trim($_SESSION['PHEnroll']['data']['id']) : '';
$code = (isset($_SESSION['PHEnroll']['data']['email'])) ? md5($_SESSION['PHEnroll']['data']['email']) : '';
$old_password = (isset($_POST['old_password'])) ? trim($_POST['old_password']) : null;
$new_password = (isset($_POST['new_password'])) ? trim($_POST['new_password']) : '';
$new_password_confirm = (isset($_POST['new_password_confirm'])) ? trim($_POST['new_password_confirm']) : '';
$submit = (isset($_POST['new_password']) && isset($_POST['new_password_confirm']));

if ($submit && $new_password != '' && $new_password_confirm != '' && $new_password == $new_password_confirm) {
	//process patient's new password
	$data = array(
		'command'		=> 'change_password',
		'patient' 		=> $id,
		'access_code'	=> $code,
		'old_password'	=> $old_password,
		'new_password'	=> $new_password
		//'new_password'	=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $new_password, MCRYPT_MODE_CFB, $iv))
	);

	//if (!is_null($old_password)) {
	//	$data['old_password'] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $old_password, MCRYPT_MODE_CFB, $iv));
	//}

	$response = api_command($data);

	if (isset($response->success) && $response->success == 1) {
		//success
		$success = true;
		$message = 'Your password was successfully changed.<br/><br/>';

		if (session_id() == '') {
			session_start();
		}

		$_SESSION[$session_key]['data']['id'] = $id;
		$_SESSION[$session_key]['access_code'] = $code;

		//$redirect = 'yes'; //commented by vinod
		header('Location: enroll.php');
	} else {
		//fail
		$success = false;
		$message = 'Your password was not changed, please try again after filling all the fields correctly.<br/><br/>';
		$redirect = 'no';
	}
} elseif ($submit) {
	//invalid form
	$success = false;
	$message = 'Please enter a valid password.<br/><br/>';
	$redirect = 'no';
}

?>

<?php include('_header.php'); ?>
<link rel="stylesheet" type="text/css" href="/enrollment/css/jvfloat.css">	
<style>
html {background: #eaeaea; height:100%;}	
body{background:#eaeaea;padding-top:0px;}	
.navbar-default.header-nav, footer{display:none;}	
#main_content{padding-bottom: 100px;}	
input.error {text-align: left !important;background: #fff;border: 1px solid #ff0000 !important;}	
a.links,a.forgot-sec {padding-top: 6px;display: inline-block;}
.jvFloat .placeHolder.active {top: 16px;}
.change-pswd .change-pswd-form .actions .btn-submit {	
    background: #0a69ad;	
    padding: 19px 20px;	
	margin-top: 20px !important;	
}	
.change-pswd .change-pswd-form .actions .btn-submit:hover { background: #06538a;}	
.change-pswd .change-pswd-form .jvFloat + div { font-size: 14px;}	
.tooltip-new {font-size: 14px;line-height: 24px;}	

</style>
<?php /*
<div class="content createAccountBox">

	<div class="change-pswd">
    	<h2>Change password</h2>
		<?php if ($_SESSION[$session_key]['data']['account_activated'] == 2) { ?>
        <h3 class="">Please change the auto-generated password</h3>
        <?php } ?>
        
        <div class="change-pswd-form">
        
        	<div id="fmMsg" style="text-align: center;" class="<?=(($submit && $success && $message != '') ? 'success' : 'error')?>"><?=$message?></div>
            
            <form id="fmChangePassword2" method="POST">	            
        	<div class="fields">
            	<?php if ($_SESSION[$session_key]['patient']->account_activated == 1) { ?>
            	<div class="field">
                	<label for="old_password" class="label-long <?=((!$success) ? 'error' : '')?>">Temporary Password</label>
                </div>
                <div class="control">
                	<input type="password" name="old_password" id="old_password" value="" class="" />
                </div>
                <?php } ?>
            </div>
            
            <div class="fields">
            	<div class="field">
                	<label for="new_password" class="label-long <?=((!$success) ? 'error' : '')?>">New Password</label>
                </div>
                <div class="control">
                	<input type="password" name="new_password" id="new_password" value="" class="<?=((!$success) ? 'error' : '')?>">
                </div>
            </div>
            
            <div class="fields">
            	<div class="field">
                	<label for="new_password_confirm" class="label-long <?=((!$success) ? 'error' : '')?>">Confirm New Password</label>
                </div>
                <div class="control">
               	<input type="password" name="new_password_confirm" id="new_password_confirm" value="" class="<?=((!$success) ? 'error' : '')?>" data-text="Password Requirements" data-hint="Passwords must be 8-20 characters, contain a mix of uppercase and lowercase letters, and contain at least one number.">
                </div>
            </div>
            
            <div class="actions">
            	<label class="mobile-hidden">&nbsp;</label>
                <input type="submit" name="btSubmit" id="btSubmit" value="Change Password" class="blue-button btn-submit loginPageButton">
            
                <label class="mobile-hidden">&nbsp;</label>
            </div>
            
            </form>
        </div>
    </div>    
	
</div> */?>
<div class="change-pswd-container">
	<div class="cpwd-logo">
        <a href="https://prescriptionhope.com" class="svg">
                <object data="https://prescriptionhope.com/images/ph-logo.svg" type="image/svg+xml" width="241" height="90" class="top-menu-only-desktop only-desktop" style="margin-top: 20px;"><img src="https://prescriptionhope.com/wp-content/uploads/2017/07/prescription-hope-logo-2017_07_13.png" class="header-logo left top-menu-only-desktop only-desktop"></object>
        </a>
    </div>
	<div class="change-pswd">
    	<h2>Change Password</h2>
		<?php if ($_SESSION[$session_key]['data']['account_activated'] == 2) { ?>
        <!-- <h3 class="">Please change the auto-generated password</h3> -->
        <?php } ?>
        
        <div class="change-pswd-form">
        
        	<div id="fmMsg" style="text-align: center;" class="<?=(($submit && $success && $message != '') ? 'success' : 'error')?>"><?=$message?></div>
            
            <form id="fmChangePassword2" method="POST">	   
            
                <input type="password" class="form-control" name="new_password" placeholder="New Password *" id="new_password" value="" class="label-long <?=((!$success) ? 'error' : '')?>">
                <input type="password" class="form-control" name="new_password_confirm" placeholder="New Password Confirm *" id="new_password_confirm" value="" class="label-long <?=((!$success) ? 'error' : '')?>" data-text="Password Requirements" data-hint="Passwords must be 8-20 characters, contain a mix of uppercase and lowercase letters, and contain at least one number.">
                <div class="actions">
                    <input type="submit" name="btSubmit" id="btSubmit" value="Change Password" class="btn-submit">
                </div>                    

            
            </form>
        </div>
    </div>    
    
    <div class="cpwd-get-started">
        <p class="content-sec">Have not applied to Prescription Hope yet?</p>
        <a href="/enrollment/register.php" class="links">Get Started</a>						
    </div>	
	
</div>

<script type="text/javascript">
// To show tooltip text instead of icon
function showTooltipsText () {
	if (jQuery(this).data("hint") != "" && jQuery(this).data("text")!='') {
		jQuery("<div style='padding: 10px 0;'><a class='tooltip-text'>"+jQuery(this).data("text")+"</a>").attr('data-tooltip', jQuery(this).data('hint')).hover(showTooltipBox, hideTooltipBox).insertAfter(jQuery(this));
	}
}

function showTooltipBox (e) {
	if (jQuery(this).data("tooltip") != "") {
		tooltipHTML = "<div class='tooltip-new'>" + jQuery(this).data("tooltip") + "</div>";

		jQuery(this).parent().append(tooltipHTML);

		// for tooltips with text label
		pos = jQuery(this).position();
		width = jQuery(this).width();
		height = 15; //jQuery(this).parent().height()+parseInt(10);
		topVal = pos.top;

        leftVal = pos.left;
		topVal = parseInt(topVal)+parseInt(height)+parseInt(20);
        //width = (width<50) ? parseInt(300)+parseInt(width) : width;

		
		if (jQuery(window).width() < 1024) { console.log('wd<1024');
			//mobile
			topVal = (jQuery(this).hasClass('tooltip-icon')) ? pos.top + 50 : pos.top + height + 30;
			leftVal = (jQuery(this).hasClass('tooltip-icon') || jQuery(this).hasClass('tooltip-text')) ? pos.left : 0;
			max_width = jQuery('body').width() - (pos.left * 2) - 15;
			if(jQuery(this).hasClass('tooltip-text')){
				topVal = parseInt(topVal)+parseInt(0);
			}
		}
		else{
			if(jQuery(this).hasClass('tooltip-text')){
				topVal = parseInt(topVal)+parseInt(30);
			}
		}

		jQuery(".tooltip-new")
			.css("top", topVal + "px")
			.css("left", leftVal + "px")
			//.css("max-width", max_width + "px")
            .css("width", width + "px")
			.fadeIn("fast");		
	}
}

function hideTooltipBox () {
	if (jQuery(this).data("tooltip") != "") {
		jQuery(".tooltip-new").remove();
	}
}
jQuery(document).ready(function() {
	jQuery('.form-control').jvFloat();
	
	jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
	jQuery.validator.addMethod("lettersonly", function(value, element) { return this.optional(element) || /^[a-z'. ]+$/i.test(value); }, "Please insert only letters.");
	//jQuery.validator.addMethod("password_aA1", function(value, element) { return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,20}$/.test(value); }, "Please use upper & lower case letters and numbers.");
	jQuery.validator.addMethod("password_aA1", function(value, element) { return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[\W\w]{8,20}$/.test(value); }, "Please use upper & lower case letters and numbers.");
	jQuery.validator.addMethod("custom_date",function(t,e){return t=t.replace(/\s+/g,""),td=t.split("/"),td=td[2]+"-"+td[0]+"-"+td[1],this.optional(e)||t.length>8&&t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/)&&td<=new Date().toISOString().substring(0,10)},"Please specify a valid date (mm/dd/yyyy)"),
	//jQuery.validator.addMethod("same_as", function(value, element, param) { return this.optional(element) || value == $(param).val(); }, "");

	formValidation = jQuery("#fmChangePassword2").validate({
		rules: {
			old_password: 				{ required: true },
			new_password: 				{ required: true, password_aA1: true, minlength: 8, maxlength: 20 },
			new_password_confirm: 		{ required: true, password_aA1: true, minlength: 8, maxlength: 20, equalTo: '#new_password' }
		},

		highlight: jQueryValidation_Highlight,

		unhighlight: jQueryValidation_Unhighlight,

		errorPlacement: jQueryValidation_ShowErrors,

		invalidHandler: function() {
			//jQuery('#fmMsg').addClass('has-error').addClass('no-bold').html('Please fill out correctly all the fields marked with red and then try again to submit the form.<br><br>');
		}
	});

	jQuery('input[name="new_password"]').keyup(function (e) {
		jQuery('#password-8-20').removeClass('neutral').removeClass('checked')
		jQuery('#password-upper-lower').removeClass('neutral').removeClass('checked')
		jQuery('#password-digits').removeClass('neutral').removeClass('checked')

		pswd = jQuery(this).val();
		jQuery('#password-8-20').addClass((pswd.length >= 8 && pswd.length <= 20) ? 'checked' : 'neutral');
		jQuery('#password-upper-lower').addClass((pswd.match('[a-z]') && pswd.match('[A-Z]')) ? 'checked' : 'neutral');
		jQuery('#password-digits').addClass((pswd.match('[0-9]')) ? 'checked' : 'neutral');
	});

	//
	var successVar = '<?php echo $redirect?>';
	if(successVar=='yes'){
		setTimeout( function(){ window.location = 'enroll.php'; }, 5000);
	}
	
	jQuery('.content.messagebox').hide();
	jQuery("input[data-hint]").each(showTooltipsText);
});

</script>
<script type="text/javascript" src="/enrollment/js/jvfloat.js"></script>
<br><br>

<?php include('_footer.php'); ?>
