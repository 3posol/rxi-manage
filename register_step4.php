
<script type="text/javascript">

	jQuery().ready(function() {
		jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");

		//load radio buttons and select objects with the correct value
		preloadSpecialFormValues();

		//show the payment method details, if preloaded
		showSelectedPaymentMethods();

		//activate form validation
		jQuery("#register_form_4").validate({
			rules: {
				p_payment_agreement:	{ required: true },
				p_service_agreement:	{ required: true },
				p_guaranty_agreement: 	{ required: true },

				p_payment_method:		{ required: true },

				p_cc_type:				{ required: jQuery("#p_payment_method_cc") },
				p_cc_number:			{ required: jQuery("#p_payment_method_cc"), creditcardtypes: function(element) {return {visa: (jQuery('select[name=p_cc_type]').val() == "Visa"), mastercard: (jQuery('select[name=p_cc_type]').val() == "Mastercard")};}},
				p_cc_exp_month:			{ required: jQuery("#p_payment_method_cc"), min: function(element) {return (jQuery('select[name=p_cc_exp_year]').val() != '<?php echo date('Y');?>') ? '01' : '<?php echo date('m');?>';}},
				p_cc_exp_year:			{ required: jQuery("#p_payment_method_cc"), min: function(element) {return (jQuery('select[name=p_cc_exp_month]').val() < '<?php echo date('m');?>') ? '<?php echo (int)date('Y')+1;?>' : '<?php echo date('Y');?>';}},
				p_cc_cvv:				{ required: jQuery("#p_payment_method_cc"), digits: true, minlength: 3, maxlength: 3 },

				p_ach_holder_name:		{ required: jQuery("#p_payment_method_ach"), ascii: true },
				p_ach_routing:			{ required: jQuery("#p_payment_method_ach"), digits: true, maxlength: 9 },
				p_ach_account:			{ required: jQuery("#p_payment_method_ach"), digits: true },

				p_acknowledge_agreement: 	{ required: true }
			},

			messages: {
				p_cc_exp_month: {
					min: 'Please enter an expiration date (Month / Year) that it\'s not in the past.'
				},
				p_ach_routing: {
					digits: 'Please insert a valid number.'
				},
				p_ach_account: {
					digits: 'Please insert a valid number.'
				}
			},

			errorPlacement: jQueryValidation_PlaceErrorLabels
		});

		/*
		jQuery("#bReminderOK").click(function(event) {
			event.preventDefault();
			jQuery("p.no-show").each(function (index) {
				if (!jQuery(this).attr("id")) {
					jQuery(this).removeClass("no-show");
				}
				jQuery("#bReminderOK").addClass("no-show");
			});
		});
		*/

		jQuery("#bPrevStep").click(function() {
			currStep = jQuery('input[name="register_step"]').val();
			jQuery('input[name="register_step"]').val(currStep-2);
		});

		jQuery("input[type='checkbox']").keypress(function(e){
		    if(e.keyCode === 13) {
	    	    jQuery(this).attr("checked", 'checked');
		        return false;
		    }
		});

		jQuery("input[name=p_payment_method]").change(showSelectedPaymentMethods);

		jQuery("a[rel]").hover(
			function(e) {
				if (jQuery(this).attr("rel") != "") {
					pos = jQuery(this).position();

					jQuery("#register_form_4").append("<p class='tooltips'><img src='"+ jQuery(this).attr("rel") +"' /></p>");
					jQuery(".tooltips")
						.css("top", (pos.top - 15) + "px")
						.css("left", (pos.left + 30) + "px")
						.fadeIn("fast");
				}
			},
			function() {
				if (jQuery(this).attr("rel") != "") {
					jQuery(".tooltips").remove();
				}
			}
		);

		//reminder
		jQuery("#btReminderClose").click(function(event) {
			event.preventDefault();
			jQuery('#reminderPopup').addClass("no-show");
		});

		//alert popup
		//jQuery('#reminderPopup').removeClass("no-show");
		//jQuery('#reminderPopup .leavePopupContent').center();

		//disbale "Go" button on Android to submit the form
		jQuery("input[type='text']").keypress(function(e){
			if(e.keyCode === 13 && /Android/.test(navigator.userAgent)) {
				e.preventDefault();
			}
		});

		//form submit
		jQuery('form#register_form_4').submit(scrollToInvalidFormElements);


		//GOOGLE Analytics
		ga('send', 'event', 'steps', 'formload', '20150806, enrollment, form, step4, payment', {'nonInteraction': 1})
	});
</script>

<div class="page_steps">
	<div class="page_step page_step_checked">Personal<br/>Information</div>
	<div class="page_step page_step_checked">Patient<br/>Information</div>
	<div class="page_step page_step_checked">Medical<br/>Information</div>
	<div class="page_step page_step_current">Enroll<br/>and Payment</div>
	<br clear="all"/>
</div>

<h2 class="center-alignment">PRESCRIPTION HOPE ENROLLMENT<br/>TERMS AND CONDITIONS</h2>

<p class="center-alignment moduleSubheader">Please read and check the boxes below, you will have an opportunity to print the policies to retain for your records.</p>

<p class="center-alignment moduleSubheader">Fields with asterisks (*) are required.</p>

<!--p class="subhead2-blue center-alignment" style="color: #ff5555; text-transform: none;">Reminder: If we find that we are unable to approve you, there will be no charges. If you are approved, the ONLY charge is $25/month/medication. If the payment section is not complete, your enrollment form <span style="text-decoration: underline;">will not</span> be processed. If you have any questions, please contact a Patient Advocate at 1-877-296-HOPE (4673).</p>
<p class="center-alignment">Fields with asterisks (*) are required.</p-->

<form id="register_form_4" method="post" action="https://manage.prescriptionhope.com/register.php">
<input type="hidden" name="register_step" value="4">

<br/>

<p class="moduleSubheader terms_scroll_box">
	<span class="policy_subtitle">Fees:</span> During the initial enrollment process if we find that we are unable to assist you with at least one medication, there will be no
	charges to your account. If we are able to assist you with one or more
	medication(s), the first month's administrative service fee of $25 per
	medication will be debited only for the medication(s) for which we
	can assist you upon receipt of this form. The monthly administrative
	service fee of $25 per medication will be debited on the 5th day of
	every month thereafter unless the 5th falls on a weekend or a holiday,
	in which case the debit will occur on the prior business day. You will
	be notified in writing of the medication(s) for which we are able to
	assist you. There are no other fees for the program or cost for the
	medication(s). It will take approximately 4-6 weeks to start receiving
	your first supply of medication(s). This range is an average amount of
	time and is contingent upon a prompt response to the information we
	request from you and your physician(s). The medication is shipped
	directly from the pharmaceutical companies and delivered either to
	your home or physician's office, depending upon the manufacturer
	delivery guidelines. You hereby acknowledge that you are not paying
	for medication(s) through the Prescription Hope service; rather you
	are paying for the administrative service of ordering, managing,
	tracking and refilling medications received through Prescription
	Hope's medication advocacy service from pharmaceutical company
	patient assistance programs. You hereby authorize Prescription Hope
	and/or its agents to debit the account provided on the front of this
	form for all administrative service fees described in this Fees section.
	You also agree to pay any associated fees should your EFT (electronic
	fund transfer) be returned unpaid by your financial institution. Due
	to the service-based nature of the Prescription Hope service, there
	are no refunds other than what is explained in the Prescription Hope
	Guarantee below. You hereby acknowledge, consent and agree this
	agreement is for twelve (12) months commencing on the date you
	sign below and will automatically be renewed for twelve (12)-month
	terms thereafter. You may terminate this agreement at any time by
	providing a signed letter of cancellation. Cancellations can take up
	to 30 days to process. Upon termination you agree to be financially
	responsible for any outstanding balances. This monthly transaction
	will appear on your billing statement as "PRESCRIPTION HOPE".
	You agree that you may be contacted via telephone, cellular phone,
	text message or email through all numbers/addresses provided by
	you and authorize receipt of pre-recorded/artificial voice messages
	and/or use of an automated dialing service by Prescription Hope or
	affiliates. By signing below, you further agree to release Prescription
	Hope, its agents, employees, successors and assigns from any and
	all liability including legal fees and costs arising from medications
	taken by you which were procured through the Prescription Hope
	medication advocacy service. You further agree to indemnify and
	hold Prescription Hope, its agents, employees, successor and assigns
	harmless against any and all damages including legal fees and costs
	arising from third persons ingesting any medication procured for
	you through the Prescription Hope advocacy program.
</p>

<p>
	<input type="checkbox" id="p_payment_agreement" name="p_payment_agreement" value="1" <?php echo (($data['p_payment_agreement'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
	<label for="p_payment_agreement" class="no_width big">I have read, understood and agree to be bound by the above paragraphs *</label>
</p>

<p class="moduleSubheader terms_scroll_box">
	<span class="policy_subtitle">Service:</span> You hereby authorize Prescription Hope to act on your
	behalf and to sign applications for patient assistance programs by
	hereby granting to Prescription Hope a limited power of attorney
	for the specific purposes of enrolling you in patient assistance
	programs with the applicable pharmaceutical companies and any
	related activities to process your enrollment. You understand this
	authorization can be revoked at any time by you by providing a
	signed letter of cancellation to Prescription Hope as described in
	the fees section. You hereby authorize your physician's office(s) to
	discuss/release medical information to Prescription Hope relating to
	your application(s) for patient assistance programs that Prescription
	Hope is processing on your behalf. You understand that Prescription
	Hope does not ship, prescribe, purchase, sell, handle or dispense
	prescription medication of any kind in its efforts to process your
	application(s) for patient assistance programs. Prescription Hope
	is a fee-based medication advocacy service that assists patients
	in enrolling in applicable pharmaceutical companies patient
	assistance programs. The medications themselves are offered by
	the pharmaceutical companies through their patient assistance
	programs at no cost to the eligible applicant. You also understand and
	acknowledge that it is each individual pharmaceutical company who
	makes the final decision as to whether you qualify for their assistance
	program(s). You understand Prescription Hope reserves the right to
	rescind, revoke, or amend its services at any time. Prescription Hope
	does not guarantee your approval for patient assistance programs;
	it is up to each applicable drug manufacturer to make the eligibility
	determination. Each drug manufacturer independently sets its own
	eligibility criteria and determines which products are included
	in their assistance programs. Medications covered are subject to
	change at any time. Prescription Hope assembles and submits your
	application to the pharmaceutical company but does not participate
	in the review process to determine which applicants are eligible.
<p>

<p>
	<input type="checkbox" id="p_service_agreement"  name="p_service_agreement" value="1" <?php echo (($data['p_service_agreement'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
	<label for="p_service_agreement" class="no_width big">I have read, understood and agree to be bound by the above paragraphs *</label>
</p>

<p class="moduleSubheader terms_scroll_box">
	<span class="policy_subtitle">Guarantee:</span> If you do not receive medication because you were
	determined to be ineligible for the patient assistance program by
	the applicable pharmaceutical manufacturer(s) and you have a
	letter of denial, Prescription Hope will gladly refund the monthly
	administrative service fee(s) for the medication(s) determined to
	be ineligible. All Prescription Hope needs from you is a copy of the
	denial letter sent to you from the applicable drug manufacturer
	explaining why you are ineligible.

	<br/><br/>

	<span class="policy_subtitle">Privacy:</span> We value our patients and make extreme efforts to protect the
	privacy of our patients personal information. Patient information is
	processed for order fulfillment only and for no other purpose. Patient
	information, including all patient health information and personal
	information, will never be disclosed to any third party under any
	circumstances. All information given to Prescription Hope, Inc., its
	agents, employees, successors and assigns (collectively, "Prescription
	Hope") will be held in the strictest confidence.

	<br/><br/>

	<span class="policy_subtitle">Eligibility:</span> You are experiencing hardship in affording your medication
	and/or you currently do not have coverage that reimburses or pays
	for your prescription medications. You affirm that the information
	provided on this form is complete and accurate. If you determine
	the information was not correct at the time you provided it to
	Prescription Hope, or if the information was accurate but is no longer
	accurate, you will immediately notify Prescription Hope in writing
	by providing the correct information.
</p>

<p>
	<input type="checkbox" id="p_guaranty_agreement"  name="p_guaranty_agreement" value="1" <?php echo (($data['p_guaranty_agreement'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
	<label for="p_guaranty_agreement" class="no_width big">I have read, understood and agree to be bound by the above paragraphs *</label>
</p>

<br/>
<h2 class="center-alignment">PAYMENT INFORMATION</h2>

<div class="pull-left">
	<p>
		<label for="p_payment_method" class="as_block" style="margin-top: 9px;">Payment Method *</label>
	</p>
</div>
<div class="pull-left">
	<p>
		<input type="radio" id="p_payment_method_cc" name="p_payment_method" value="cc" <?php echo (($data['p_payment_method'] != '') ? 'preload="' . $data['p_payment_method'] . '"' : ''); ?>> <label for="p_payment_method_cc" class='no_width'> &nbsp;Visa, Mastercard or Debit Card</label>
		<br clear="all"/>
		<img src="images/visa.jpg" border="0" class="payment_images_" /> &nbsp;<img src="images/mastercard.jpg" border="0" />
		<br/><br/>

		<!--label class="blank">&nbsp;</label-->
		<input type="radio" id="p_payment_method_ach" name="p_payment_method" value="ach"> <label for="p_payment_method_ach" class='no_width'> &nbsp;Checking Account</label>
		<br clear="all"/>
		<img src="images/check.jpg" border="0" class="payment_images_" />
	</p>
</div>
<br clear="all" />

<p id="payment_cc" class="no-show">
	<label for="p_cc_type">Credit Card Type *</label>
	<select name="p_cc_type" preload="<?php echo $data['p_cc_type'];?>">
		<option value="Visa">Visa
		<option value="Mastercard">Mastercard
	</select>
	<br/><br/>

	<label for="p_cc_number">Credit Card Number *</label>
	<input type="text" name="p_cc_number" value="<?php echo $data['p_cc_number'];?>"><br/>
	<br/>

	<label for="p_cc_exp_month">Credit Card Expiration Month *</label>
	<select name="p_cc_exp_month" preload="<?php echo $data['p_cc_exp_month'];?>">
		<option value="01">January
		<option value="02">February
		<option value="03">March
		<option value="04">April
		<option value="05">May
		<option value="06">June
		<option value="07">July
		<option value="08">August
		<option value="09">September
		<option value="10">October
		<option value="11">November
		<option value="12">December
	</select>
	<br/><br/>

	<label for="p_cc_exp_year">Credit Card Expiration Year *</label>
	<select name="p_cc_exp_year" preload="<?php echo $data['p_cc_exp_year'];?>">
		<?php for ($i = 0; $i < 10; $i++) { ?>
			<option value="<?php echo ((int) date('Y') + $i); ?>"><?php echo ((int) date('Y') + $i); ?>
		<?php } ?>
	</select>
	<br/><br/>

	<label for="p_cc_cvv">CVV Security Code (<a href="#" rel="images/card_visa.gif" class="skipLeave disable-click">?</a>)*</label>
	<input type="text" name="p_cc_cvv" value="<?php echo $data['p_cc_cvv'];?>" maxlength="3"><br/>
</p>

<p id="payment_ach" class="no-show">
	<label for="p_ach_holder_name">Account Holder’s Name *</label>
	<input type="text" name="p_ach_holder_name" value="<?php echo $data['p_ach_holder_name'];?>"><br/>
	<br/>

	<label for="p_ach_routing">Bank Routing Number (<a href="#" rel="images/ach2.jpg" class="skipLeave disable-click">?</a>)*</label>
	<input type="text" name="p_ach_routing" value="<?php echo $data['p_ach_routing'];?>" maxlength="9"><br/>
	<br/>

	<label for="p_ach_account">Checking Account Number (<a href="#" rel="images/ach2.jpg" class="skipLeave disable-click">?</a>)*</label>
	<input type="text" name="p_ach_account" value="<?php echo $data['p_ach_account'];?>"><br />
</p>

<br/>

<p class="moduleSubheader">
	<strong>
	By checking this box, I acknowledge that I have read and agree to the terms and conditions of Prescription Hope, including the fee policy, service policy, privacy policy, and guarantee. I authorize Prescription Hope to charge my account $25 per month, per medication that I may qualify for. Due to the service-based nature of the Prescription Hope program, I acknowledge there are no refunds other than what is explained in the Prescription Hope guarantee. If I do not receive medication because I am determined to be ineligible for the patient assistance program by the applicable pharmaceutical manufacturer(s) and I have a letter of denial, I acknowledge Prescription Hope will refund the monthly administrative service fee(s) for the medication(s) determined to be ineligible only after Prescription Hope has exhausted all avenues of appeal. In order to receive a refund, I will provide Prescription Hope a copy of the denial letter sent from the applicable drug manufacturer explaining why I am ineligible.
	<br/><br/>
	This agreement is in effect starting on this day of my application, until I rescind my authorization in writing.
	</strong>
</p>

<p>
	<input type="checkbox" id="p_acknowledge_agreement"  name="p_acknowledge_agreement" value="1" <?php echo (($data['p_acknowledge_agreement'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
	<label for="p_acknowledge_agreement" class="no_width big">I have read, understood and agree to be bound by the above paragraphs *</label>
</p>

<br/>

<p class="center-alignment">
	<input type="submit" name="bPrevStep" id="bPrevStep" value="Back" class="cancel small-button-orange">
	<input type="submit" name="bNextStep" id="bNextStep" value="Submit Application" class="small-button-orange">
</p>

<!--p class="center-alignment" id="btReminderOK">
	<input type="submit" name="bReminderOK" id="bReminderOK" value="OK" class="small-button-orange">
</p-->

</form>
