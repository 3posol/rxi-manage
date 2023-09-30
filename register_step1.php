<script type="text/javascript">

	jQuery().ready(function() {
		jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
		//jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[a-z0-9\-\s\.\,\!\?]+$/i.test(value); }, "Please insert only alphanumeric characters.");

		//load radio buttons and select objects with the correct value
		preloadSpecialFormValues();

		//show the complete patient profile, if preloaded
		showSelectedPatientProfile();

		//activate form validation
		jQuery("#register_form_1").validate({
			rules: {
				p_first_name:				{ required: true, ascii: true },
				p_middle_initial: 			{ required: true, ascii: true, maxlength: 1 },
				p_last_name:				{ required: true, ascii: true },
				p_is_minor:					{ required: true, ascii: true },
				p_parent_first_name:		{ required: jQuery("#p_is_minor_yes"), ascii: true },
				p_parent_middle_initial: 	{ required: jQuery("#p_is_minor_yes"), ascii: true , maxlength: 1 },
				p_parent_last_name:			{ required: jQuery("#p_is_minor_yes"), ascii: true },
				p_parent_phone:				{ required: jQuery("#p_is_minor_yes"), phoneUS: true },
				p_address: 					{ required: true, ascii: true },
				p_city: 					{ required: true, ascii: true },
				p_state: 					{ required: true },
				p_zip: 						{ required: true, digits: true, minlength: 5, maxlength: 5 },
				p_phone: 					{ required: true, phoneUS: true },
				p_fax: 						{ required: false, ascii: true/*, phoneUS: true*/ },
				p_email: 					{ required: false, email: true },
				p_alternate_contact_name: 	{ required: false, ascii: true },
				p_alternate_phone: 			{ required: false, ascii: true/*, phoneUS: true*/ },
			},

			errorPlacement: jQueryValidation_PlaceErrorLabels
		});

		jQuery("input[name=p_is_minor]").change(showSelectedPatientProfile);

		jQuery("input[type='radio']").keypress(function(e){
		    if(e.keyCode === 13) {
	    	    jQuery(this).attr("checked", 'checked');
		        return false;
		    }
		});

		//add masks
		jQuery("input[name='p_parent_phone']").mask("999-999-9999");
		jQuery("input[name='p_phone']").mask("999-999-9999");
		jQuery("input[name='p_fax']").mask("999-999-9999");
		jQuery("input[name='p_alternate_phone']").mask("999-999-9999");

		// - fix for validate & mask conflicts
		//$("input.not_required_phone", "body")
		//	.mask("999-999-9999")
		//	.bind("blur", function () {
		//    // force revalidate on blur.
		//	var frm = $(this).parents("form");
		//	// if form has a validator
		//	if ($.data( frm[0], 'validator' )) {
		//		var validator = $(this).parents("form").validate();
		//		validator.settings.onfocusout.apply(validator, [this]);
		//	}
		//});

		//disbale "Go" button on Android to submit the form
		jQuery("input[type='text']").keypress(function(e){
			if(e.keyCode === 13 && /Android/.test(navigator.userAgent)) {
				e.preventDefault();
			}
		});

		//form submit
		jQuery('form#register_form_1').submit(scrollToInvalidFormElements);

		//GOOGLE Analytics
		ga('send', 'event', 'steps', 'formload', '20150806, enrollment, form, step1, personal-info', {'nonInteraction': 1})
	});

</script>

<div class="page_steps">
	<div class="page_step page_step_current">Personal<br/>Information</div>
	<div class="page_step">Patient<br/>Information</div>
	<div class="page_step">Medical<br/>Information</div>
	<div class="page_step">Enroll<br/>and Payment</div>
	<br clear="all"/>
</div>

<h2 class="center-alignment">PRESCRIPTION HOPE ENROLLMENT<br/>PERSONAL INFORMATION</h2>

<p class="subhead2-blue left-alignment" style="color: #17386f; text-transform: none;">
	In order to expedite your application, please have the following information ready:
</p>

<ul class="subhead2-blue" style="margin: 0 0 0 30px; padding-bottom: 5px; color: #17386f; font-family: 'Raleway'; text-transform: none;">
	<li>Your personal information.</li>
	<li>Your doctor's information; including each doctor's full name, address, phone number, and fax number.</li>
	<li>Your medication information; including the name, strength, and frequency of each medication.</li>
	<li>Your monthly income.</li>
</ul><br/>

<p class="subhead2-blue left-alignment" style="color: #17386f; text-transform: none;">
	Prescription Hope charges a set price of $25 per month per medication. If we are unable to obtain your prescription medication, we will not charge a fee for that medication. Prescription Hope accepts Visa, MasterCard, or a checking account for your monthly fee.
</p>

<p class="subhead2-blue left-alignment" style="color: #17386f; text-transform: none;">
	Remember: your total will not cost more than $25 per month per medication.
</p>

<p class="subhead2-blue left-alignment" style="color: #17386f; text-transform: none;">
	Your personal information, including all health information, is protected by 128-bit SSL technology. Your information will never be disclosed to any third parties for any reason and is for order fulfillment purposes only.
</p>

<p class="subhead2-blue left-alignment" style="color: #17386f; text-transform: none;">
	Need help completing your application? Contact our patient advocates today at 1-877-296-HOPE (4673).
</p>

<p class="left-alignment moduleSubheader">
	Fields with asterisks (*) are required.
	<br/>
	One form per person.
</p>

<form id="register_form_1" method="post" action="https://manage.prescriptionhope.com/register.php">
<input type="hidden" name="register_step" value="1">

<p class="form-row">
	<label for="p_first_name">First Name *</label>
	<input type="text" name="p_first_name" value="<?php echo htmlspecialchars(stripslashes($data['p_first_name']));?>"><br/>
</p>

<p class="form-row">
	<label for="p_middle_initial">Middle Initial *</label>
	<input type="text" name="p_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['p_middle_initial']));?>" maxlength="1"><br/>
</p>

<p class="form-row">
	<label for="p_last_name">Last Name *</label>
	<input type="text" name="p_last_name" value="<?php echo htmlspecialchars(stripslashes($data['p_last_name']));?>">
</p>

<p class="form-row">
	<label for="p_email">Email Address</label>
	<input type="text" name="p_email" value="<?php echo $data['p_email'];?>">
</p>

<div class="form-group-spacer"></div>

<p>
	<label for="p_is_minor_yes" class="w670-mixed">Is this application on behalf of a minor? *</label>
	<input type="radio" id="p_is_minor_yes" name="p_is_minor" value="1" <?php echo (($data['p_is_minor'] != '') ? 'preload="' . (int)$data['p_is_minor'] . '"' : ''); ?>> <label for="p_is_minor_yes" class='no_width'>Yes</label> &nbsp;
	<input type="radio" id="p_is_minor_no" name="p_is_minor" value="0"> <label for="p_is_minor_no" class='no_width'>No</label>
</p>

<div class="form-group-spacer"></div>

<p class="form-row patient_parent_profile no-show">
	<label for="p_parent_first_name">Parent/Guardian First Name *</label>
	<input type="text" name="p_parent_first_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_first_name']));?>"><br/>
</p>

<p class="form-row patient_parent_profile no-show">
	<label for="p_parent_middle_initial">Parent/Guardian Middle Initial *</label>
	<input type="text" name="p_parent_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_middle_initial']));?>" maxlength="1"><br/>
</p>

<p class="form-row patient_parent_profile no-show">
	<label for="p_parent_last_name">Parent/Guardian Last Name *</label>
	<input type="text" name="p_parent_last_name" value="<?php echo htmlspecialchars(stripslashes($data['p_parent_last_name']));?>">
</p>

<p class="form-row patient_parent_profile no-show">
	<label for="p_parent_phone">Parent/Guardian Phone *</label>
	<input type="text" name="p_parent_phone" value="<?php echo $data['p_parent_phone'];?>"><br/>
</p>

<div class="form-group-spacer"></div>

<p class="form-row patient_profile no-show">
	<label for="p_address">Address *</label>
	<input type="text" name="p_address" value="<?php echo htmlspecialchars(stripslashes($data['p_address']));?>"><br/>
</p>

<p class="form-row patient_profile no-show">
	<label for="p_city">City *</label>
	<input type="text" name="p_city" value="<?php echo htmlspecialchars(stripslashes($data['p_city']));?>"><br/>
</p>

<p class="form-row patient_profile no-show">
	<label for="p_state">State *</label>
	<select name="p_state" preload="<?php echo $data['p_state'];?>">
		<option value="" selected="selected">Select a State</option>
		<option value="AL">Alabama</option>
		<option value="AK">Alaska</option>
		<option value="AZ">Arizona</option>
		<option value="AR">Arkansas</option>
		<option value="CA">California</option>
		<option value="CO">Colorado</option>
		<option value="CT">Connecticut</option>
		<option value="DE">Delaware</option>
		<option value="DC">District Of Columbia</option>
		<option value="FL">Florida</option>
		<option value="GA">Georgia</option>
		<option value="HI">Hawaii</option>
		<option value="ID">Idaho</option>
		<option value="IL">Illinois</option>
		<option value="IN">Indiana</option>
		<option value="IA">Iowa</option>
		<option value="KS">Kansas</option>
		<option value="KY">Kentucky</option>
		<option value="LA">Louisiana</option>
		<option value="ME">Maine</option>
		<option value="MD">Maryland</option>
		<option value="MA">Massachusetts</option>
		<option value="MI">Michigan</option>
		<option value="MN">Minnesota</option>
		<option value="MS">Mississippi</option>
		<option value="MO">Missouri</option>
		<option value="MT">Montana</option>
		<option value="NE">Nebraska</option>
		<option value="NV">Nevada</option>
		<option value="NH">New Hampshire</option>
		<option value="NJ">New Jersey</option>
		<option value="NM">New Mexico</option>
		<option value="NY">New York</option>
		<option value="NC">North Carolina</option>
		<option value="ND">North Dakota</option>
		<option value="OH">Ohio</option>
		<option value="OK">Oklahoma</option>
		<option value="OR">Oregon</option>
		<option value="PA">Pennsylvania</option>
		<option value="RI">Rhode Island</option>
		<option value="SC">South Carolina</option>
		<option value="SD">South Dakota</option>
		<option value="TN">Tennessee</option>
		<option value="TX">Texas</option>
		<option value="UT">Utah</option>
		<option value="VT">Vermont</option>
		<option value="VA">Virginia</option>
		<option value="WA">Washington</option>
		<option value="WV">West Virginia</option>
		<option value="WI">Wisconsin</option>
		<option value="WY">Wyoming</option>
	</select><br/>
</p>

<p class="form-row patient_profile no-show">
	<label for="p_zip">ZIP Code *</label>
	<input type="text" name="p_zip" value="<?php echo $data['p_zip'];?>" maxlength="5">
</p>

<div class="form-group-spacer patient_profile no-show"></div>

<p class="form-row patient_profile no-show">
	<label for="p_phone">Phone *</label>
	<input type="text" name="p_phone" value="<?php echo $data['p_phone'];?>"><br/>
</p>

<p class="form-row patient_profile no-show">
	<label for="p_fax">Fax</label>
	<input type="text" name="p_fax" value="<?php echo $data['p_fax'];?>" class="not_required_phone"><br/>
</p>

<div class="form-group-spacer patient_profile no-show"></div>

<p class="form-row patient_profile no-show">
	<label for="p_alternate_contact_name">Alternate Contact Name</label>
	<input type="text" name="p_alternate_contact_name" value="<?php echo htmlspecialchars(stripslashes($data['p_alternate_contact_name']));?>"><br/>
</p>

<p class="form-row patient_profile no-show">
	<label for="p_alternate_phone">Alternate Contact Phone</label>
	<input type="text" name="p_alternate_phone" value="<?php echo $data['p_alternate_phone'];?>" class="not_required_phone">
</p>

<div class="form-group-spacer"></div>

<p class="center-alignment">
	<br/>
	<input type="submit" name="bNextStep" value="Next" class="small-button-orange">
</p>

</form>
