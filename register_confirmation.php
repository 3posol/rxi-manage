<script type="text/javascript">

	jQuery().ready(function() {
		jQuery("#bPrevStep").click(function() {
			currStep = jQuery('input[name="register_step"]').val();
			jQuery('input[name="register_step"]').val(currStep-2);
		});

		//GOOGLE Analytics
		ga('send', 'event', 'conversion', 'submission', '20150806, enrollment, form, step5, successful-sub', {'nonInteraction': 1})
	});

</script>

<div class="page_steps">
	<div class="page_step page_step_checked">Personal<br/>Information</div>
	<div class="page_step page_step_checked">Patient<br/>Information</div>
	<div class="page_step page_step_checked">Medical<br/>Information</div>
	<div class="page_step page_step_checked">Enroll<br/>and Payment</div>
	<br clear="all"/>
</div>

<h2 class="center-alignment">PRESCRIPTION HOPE ENROLLMENT<br/><?php echo (($response_success) ? 'CONFIRMATION' : 'ERROR'); ?></h2>

<br/>

<div class="center-alignment"><?php echo $response_msg;?></div>

<form id="register_form_5" method="post" action="https://manage.prescriptionhope.com/register.php">
	<input type="hidden" name="register_step" value="5">
	<?php if (!$response_success) { ?>
		<p class="center-alignment">
			<input type="submit" id="bPrevStep" name="bPrevStep" value="Back" class="cancel small-button-orange">
		</p>
	<?php } else { ?>
		<!--Go to the  <a href="register.php">registration page</a>.-->
	<?php } ?>
</form>

<!-- Google Code for Application Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1044900775;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "JztYCKXr8gkQp9ef8gM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1044900775/?label=JztYCKXr8gkQp9ef8gM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

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