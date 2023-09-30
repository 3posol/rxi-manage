<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

$old_password = (isset($_POST['old_password'])) ? trim($_POST['old_password']) : null;
$new_password = (isset($_POST['new_password'])) ? trim($_POST['new_password']) : '';
$new_password_confirm = (isset($_POST['new_password_confirm'])) ? trim($_POST['new_password_confirm']) : '';
$submit = (isset($_POST['new_password']) && isset($_POST['new_password_confirm']));

$success = true;
$message = '';

if ($submit && $new_password != '' && $new_password_confirm != '' && $new_password == $new_password_confirm) {
	//encoding
    $key = pack('H*', md5($_SESSION['PLP']['patient']->PatientID));
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

	//process patient's new password
	$data = array(
		'command'		=> 'change_password',
		'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
		'access_code'	=> $_SESSION['PLP']['access_code'],
		'new_password'	=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $new_password, MCRYPT_MODE_CFB, $iv)),
		'iv'			=> base64_encode($iv),
		'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
	);

	if (!is_null($old_password)) {
		$data['old_password'] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $old_password, MCRYPT_MODE_CFB, $iv));
	}

	$response = api_command($data);

	if (isset($response->success) && $response->success == 1) {
		//success
		$success = true;
		$message = 'Your password was successfully changed.<br/><br/>';

		//$redirect = 'yes';
		header('location: dashboard.php');
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
.navbar.header-nav,footer{display:none;}
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

/*form#fmChangePassword {width: 60%;margin: 0 auto;}
input[type="text"], input[type="password"] {width: 100%;padding: 13px 10px 13px 10px;line-height: 20px;border: 1px solid #c8c8c8;border-radius: 6px;-webkit-appearance: none;-moz-appearance: none;appearance: none;}
.col-lg-6:nth-child(2) {text-align: left;}
/*.col-lg-6:first-child {text-align: right;}*/
/*.tooltip-new {
	position: absolute;
	display: block;
	width: 100%;
	padding: 10px;
	text-align: left;
	background-color: #fff;
	border: 1px solid #c8c8c8;
	z-index: 2147483640;
	color: #54646F;	font-family: Raleway;	font-size: 14px;line-height: 24px;
	border: 2px solid #1765AD;	background-color: #FFFFFF;	box-shadow: 0 0 29px 4px rgba(0,0,0,0.3);
}
.tooltip-new:before{content: none;}

.change-pswd { max-width: 700px; margin: 40px auto;}
.change-pswd h2, .change-pswd h3 { text-align: center;}
.change-pswd .change-pswd-form { padding: 15px 0 20px;}
.change-pswd .change-pswd-form form { width: 100% !important;}
.change-pswd .change-pswd-form .fields { padding: 0 0 20px;}
.change-pswd .change-pswd-form .field { display: inline-block; width: 35%; padding-top: 12px; text-align: left;}
.change-pswd .change-pswd-form .field label { margin: 0;}
.change-pswd .change-pswd-form .control { display: inline-block; width: 64%; vertical-align:top; text-align: left;}
.change-pswd .change-pswd-form .actions { text-align: center; padding: 20px 0 0;}
.change-pswd .change-pswd-form .actions .btn-submit { max-width: 400px; width: 100%; height: 50px !important; margin: 0 !important;}

@media all and (min-width: 768px) and (max-width: 969px){
	.change-pswd { max-width: 600px;}
}

@media all and (max-width: 767px) {
	.container {
    width: auto !important;
}
	
	.change-pswd .change-pswd-form .field { display: block; width: 100%; padding-bottom: 10px;}
	.change-pswd .change-pswd-form .control { display: block; width: 100%;}

	.col-lg-6:nth-child(2) {text-align: center;}
	.col-lg-6:first-child {text-align: center;}
}*/
</style>
<!-- <div class="content">
	<div class="container text-center">
    
    	<div class="change-pswd">
        
            <h2>Change Password</h2>
            <?php if ($_SESSION['PLP']['patient']->account_activated == 2) { ?>
                <h3>Please change the auto-generated password.</h3>
            <?php } ?>
        
		<div class="change-pswd-form">			

			<div id="fmMsg" class="<?=((!$success && $message != '') ? 'error' : 'success')?>"><?=$message?></div>

			<form id="fmChangePassword" method="POST">
            
            	<div class="fields">
					<?php if ($_SESSION['PLP']['patient']->account_activated == 1) { ?>
                    <div class="field">
                        <label for="old_password" class="label-long <?=((!$success) ? 'error' : '')?>">Old Password</label>
                    </div>
                    <div class="control">
                        <input type="password" name="old_password" id="old_password" value="" class="<?=((!$success) ? 'error' : '')?>">
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
                	<input type="submit" name="agent_submit" id="btSubmit" class="btn-submit" value="Change Password">
                </div>

			</form>
		</div>

		</div>
        
	</div>
</div> -->
<div class="change-pswd-container"> 

    <div class="cpwd-logo">
        <a href="https://prescriptionhope.com" class="svg">
                <object data="https://prescriptionhope.com/images/ph-logo.svg" type="image/svg+xml" width="241" height="90" class="top-menu-only-desktop only-desktop" style="margin-top: 20px;"><img src="https://prescriptionhope.com/wp-content/uploads/2017/07/prescription-hope-logo-2017_07_13.png" class="header-logo left top-menu-only-desktop only-desktop"></object>
        </a>
    </div>
   
<div class="change-pswd">

    <h2>Change Password</h2>
    <?php if ($_SESSION['PLP']['patient']->account_activated == 2) { ?>
       <!--  <h3>Please change the auto-generated password.</h3> -->
    <?php } ?>

	<div class="change-pswd-form">			

        <div id="fmMsg" class="<?=((!$success && $message != '') ? 'error' : 'success')?>"><?=$message?></div>
    
        <form id="fmChangePassword" method="POST">
            
            <!--<input type="password" class="form-control" name="old_password" placeholder="Old Password*" id="old_password" value="" class="label-long <?=((!$success) ? 'error' : '')?>">-->
            <input type="password" class="form-control" name="new_password" placeholder="New Password*" id="new_password" value="" class="label-long <?=((!$success) ? 'error' : '')?>">
            <input type="password" class="form-control" name="new_password_confirm" placeholder="New Password Confirm*" id="new_password_confirm" value="" class="label-long <?=((!$success) ? 'error' : '')?>" data-text="Password Requirements" data-hint="Passwords must be 8-20 characters, contain a mix of uppercase and lowercase letters, and contain at least one number.">
            <div class="actions">
                <input type="submit" name="agent_submit" id="btSubmit" class="btn-submit" value="Change Password">
            </div>
            
            <!--<div class="fields">
                <?php if ($_SESSION['PLP']['patient']->account_activated == 1) { ?>
                <div class="field">
                    <label for="old_password" class="label-long <?=((!$success) ? 'error' : '')?>">Old Password</label>
                </div>
                <div class="control">
                    <input type="password" name="old_password" id="old_password" value="" class="<?=((!$success) ? 'error' : '')?>">
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
                <input type="submit" name="agent_submit" id="btSubmit" class="btn-submit" value="Change Password">
            </div>-->
    
        </form>
    </div>
</div>

    <div class="cpwd-get-started">
        <p class="content-sec">Have not applied to Prescription Hope yet?</p>
        <a href="/enrollment/register.php" class="links">Get Started</a><br/>
        <!--<a href="/enrollment/forgot_password.php" class="forgot-sec">Change Password?</a>-->						
    </div>					
	

</div>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.content.messagebox').hide();
		jQuery("input[data-hint]").each(showTooltipsText);
		
		var successVar = '<?php echo $redirect?>';
		if(successVar=='yes'){
			setTimeout( function(){ window.location = 'dashboard.php'; }, 5000);
		}
	});
	
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
jQuery(document).ready(function(){
		jQuery('.form-control').jvFloat();
	});
</script>
<script type="text/javascript" src="/enrollment/js/jvfloat.js"></script>
<?php include('_footer.php'); ?>
