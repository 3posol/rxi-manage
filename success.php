<?php

ini_set('session.cookie_domain', '.prescriptionhope.com');

if (!ini_get('date.timezone')) {
    date_default_timezone_set('America/New_York');
}

session_start();

$response_success = true;
$response_msg = ''; //'<p class="moduleSubheader">Enrollment Form Submitted Successfully.</p>';

if (isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') {
	//agent
	$broker_name = trim(substr(urldecode($_SESSION['register_data']['p_application_source']), 9));
    $broker_name = ($broker_name == 'Access Health Insurance, Inc') ? 'JibeHealth' : $broker_name;
	$broker_rate = (isset($_SESSION['rate']) && $_SESSION['rate'] > 0) ? $_SESSION['rate'] : 30;
	$response_msg .= '<p class="moduleSubheader">Thank you for submitting an Enrollment Form to Prescription Hope.  We are proud to have partnered with ' . $broker_name . ' to obtain your medications for only ' . number_format($broker_rate, 2). ' per month per medication.<br/><br/>If you have any questions about your account with Prescription Hope, or any questions in general about the program, please visit our website at <a href="https://www.prescriptionhope.com">www.prescriptionhope.com</a> or call us at 1-877-296-4673.<br/><br/>A Patient Advocate will review your form and begin your Enrollment process.  You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>';
} else {
	//direct
	$response_msg .= '<p class="moduleSubheader">Thank you for submitting your Enrollment Form to Prescription Hope. One of our Patient Advocates will review your form and begin your enrollment process. You can expect a phone call and Welcome Packet from Prescription Hope soon.</p>';
}

$response_msg .= '<br/><p class="moduleSubheader">To print a copy of your Enrollment Form for your records.<br/><br/><span class="small-button-orange"><a href="save_application.php" class="skipLeave" target="_blank">Click here</a></span></p>';

$form_submitted = true;

?>

<?php include('_header.php'); ?>

<div class="container-fluid">
	<div id="enroll-now" class="row row-1 one_column white text-left" style='z-index:1000;'>
		<div class="container">
			<div id='applicationForm'>
					<!-- CONFIRMATION -->
					<script type="text/javascript">

						jQuery().ready(function() {
							//GOOGLE Analytics
							//ga('send', 'event', 'conversion', 'submission', '20150806, enrollment, form, step5, successful-sub', {'nonInteraction': 1})
						});

					</script>

					<!-- Facebook Pixel -->
					<script>
						fbq('track', 'CompleteRegistration');
					</script>

					<br/>
					<h2 class="center-alignment dblue-text no-text-transformation">Enrollment Form Submitted Successfully.</h2>
					<br/>

					<div class="center-alignment"><?php echo $response_msg;?></div>

					<form id="register_form" method="post" action="https://manage.prescriptionhope.com/register.php">
						<?php if (!$response_success) { ?>
							<p class="center-alignment">
								<input type="submit" id="bPrevStep" name="bPrevStep" value="Back" class="cancel small-button-orange">
							</p>
						<?php } ?>
					</form>


    <!-- BING UET -->
<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"5711728"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script><noscript><img src="//bat.bing.com/action/0?ti=5711728&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>

					<script>

					jQuery(function() {
						jQuery.ajax({
							url: 'save_application.php?method=email';
						})

						jQuery('#app_email_send').click(function (event){
							jQuery('#app_email_send').text("SENDING EMAIL ...");
							event.preventDefault();
							jQuery.ajax({
								url: jQuery(this).attr('href'),
								success: function(response) {
									jQuery('#app_email_link').removeClass("small-button-orange");
									jQuery('#app_email_link').html("Email sent succesfully.");
								}
					 		})
							return false; //for good measure
						});
					});

					</script>

					<p class="center-alignment">
						<?php if ($form_submitted) { ?>
							<br/>
							Protecting your personal information is our highest priority.<br/>
							We have the same secured software used by banks in place<br/>
							to ensure your personal information is always safe.
							<br /><br />
						<?php } ?>

						<table align='center' id="seals">
							<tr>
								<td style="padding-top: 25px;"><script type="text/javascript" src="https://seal.geotrust.com/getgeotrustsslseal?host_name=manage.prescriptionhope.com&amp;size=S&amp;lang=en"></script><a href="http://www.geotrust.com/ssl/" target="_blank"  style="color:#000000; text-decoration:none; font:bold 7px verdana,sans-serif; letter-spacing:.5px; text-align:center; margin:0px; padding:0px;"></a><br/><br/></td>
								<td>&nbsp;</td>
								<td>
									<table width="135" border="0" cellpadding="2" cellspacing="0" title="Click to Verify - This site chose Symantec SSL for secure e-commerce and confidential communications.">
										<tr>
											<td width="135" align="center" valign="top">
												<script type="text/javascript" src="https://seal.websecurity.norton.com/getseal?host_name=www.prescriptionhope.com&amp;size=M&amp;use_flash=YES&amp;use_transparent=YES&amp;lang=en"></script>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</p>
			</div>
		</div>
	</div>
</div>

<div id="leavePopup"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">The Prescription Hope Pharmacy Program over the last decade has helped thousands of people nationwide obtain their prescription medication for only $<?=$_SESSION['rate']?> per month per medication. Are you sure you want to navigate from this page?</p>

		    	<br/><br/>
				<p class="center-alignment" id="leaveButtons">
					<span class="small-button-orange">
						<a href="" id="btLeaveNo" class="skipLeave">No</a>
					</span>
					<span class="small-button-orange">
						<a href="" id="btLeaveYes" class="skipLeave">Yes</a>
					</span>
				</p>
		    </div>
		</div>
	</div>
</div>

<div id="leavePopup2"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">Please help us improve our services by taking a moment to tell us the reason that you do not want to complete the enrollment form</p>
		    	<br/>

		    	<form id="fmLeavePage" method="post" action="">
		    	<input type="hidden" name="id" value="<? echo session_id(); ?>">

				<p class="center-alignment">
					<textarea name="leave_reason" class="no-margin"></textarea>
				</p>
				<br/>

				<p class="center-alignment" id="leaveButtons2">
					<input type="submit" name="bSubmitLeavePage" id="btLeaveSubmit" value="Submit" class="small-button-orange">

					<span class="small-button-orange">
						<a href="" id="btLeaveCancel" class="skipLeave">Cancel</a>
					</span>
				</p>
				</form>
		    </div>
		</div>
	</div>
</div>

<div id="reminderPopup"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">YOU’RE ALMOST FINISHED!</p>

		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">Reminder: If we find that we are unable to approve you, there will be no charges. If you are approved, the ONLY charge is $<?=$_SESSION['rate']?>/month/medication. If the payment section is not complete, your enrollment form <span style="text-decoration: underline;">will not</span> be processed. If you have any questions, please contact a Patient Advocate at 1-877-296-HOPE (4673).</p>

		    	<br/>

		    	<p class="center-alignment" style="font-size: 12px;">Press OK to Continue</p>

				<p class="center-alignment" id="btReminderOK">
					<span class="small-button-orange">
						<a href="#" id="btReminderClose" class="skipLeave">OK</a>
					</span>
				</p>
		    </div>
		</div>
	</div>
</div>

<div id="submitPopup"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">
			    	Upon submitting your enrollment form your first payment will be processed using the account you provided so we can begin processing your enrollment.<br/><br/>
			    	We will mail a paper request for proof of income documentation to you. This is required once per year by the pharmaceutical company that will be shipping your medication.<br/><br/>
			    	We will not be able to order your medication until you submit all requested documents. Please be on the lookout for our envelopes, we never send "Junk" mail so it is important to open and read all documents from Prescription Hope.
		    	</p>

		    	<br/><br/>
				<p class="center-alignment" id="submitButtons">
					<span class="small-button-orange">
						<a href="" id="btConfirmSubmit" class="skipLeave no-text-transformation">I understand and<br/>agree - Submit</a>
					</span>
					<span class="small-button-orange">
						<a href="" id="btCancelSubmit" class="skipLeave no-text-transformation">Please cancel the<br/>enrollment process</a>
					</span>
				</p>
		    </div>
		</div>
	</div>
</div>

<div id="submitPopup2"  class='centered warm-grey left-alignment popup no-show'>
	<div class="text-container">
		<div>
		    <div class="leavePopupContent">
		    	<p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">Are you sure you want to cancel your enrollment process? This will delete your enrollment form.</p>

		    	<br/><br/>
				<p class="center-alignment" id="submitButtons">
					<span class="small-button-orange">
						<a href="" id="btResetForm" class="skipLeave no-text-transformation">Delete<br/>&nbsp;</a>
					</span>
					<span class="small-button-orange">
						<a href="" id="btHideSubmitConfrmation" class="skipLeave no-text-transformation">Return to<br/>Enrollment Form</a>
					</span>
				</p>
		    </div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var preventLeave = <?=((!$form_submitted) ? 'true' : 'false') ?>;
	var lastClickedObject = null;
	var lastEventType = null;
	var submitConfirmed = false;

	jQuery().ready(function() {
		//submit confirmation
		jQuery('#btConfirmSubmit').click(confirmEnrollmentSubmit);
		jQuery('#btCancelSubmit').click(cancelEnrollmentSubmit);
		jQuery('#btResetForm').click(resetEnrollmentForm);
		jQuery('#btHideSubmitConfrmation').click(hideEnrollmentSubmitConfirmation);

		jQuery("#fmLeavePage").validate({
			rules: {
				leave_reason:	{ required: true }
			},

			errorPlacement: jQueryValidation_PlaceErrorLabels
		});

		jQuery('table#seals a').each(function() {
			jQuery(this).addClass('skipLeave');
		});

		jQuery('a').click(preventPageLeave);
		jQuery('form#searchform').submit(preventPageLeave);

		jQuery('#btLeaveNo').click(cancelPageLeave);
		jQuery('#btLeaveYes').click(continuePageLeave);
		jQuery('#btLeaveCancel').click(cancelPageLeave);
		jQuery('#btLeaveSubmit').click(submitPageLeaveReason);

		jQuery(window).resize(function(){
			jQuery('.leavePopupContent').center();
		});

 		jQuery('.disable-click').click(function(e) {
 			e.preventDefault();
 		});
	});
</script>

<?php include('_footer.php'); ?>
