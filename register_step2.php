<script type="text/javascript">

	jQuery().ready(function() {
		jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
		jQuery.validator.addMethod("custom_date",function(t,e){return t=t.replace(/\s+/g,""),td=t.split("/"),td=td[2]+"-"+td[0]+"-"+td[1],this.optional(e)||t.length>8&&t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/)&&td<=new Date().toISOString().substring(0,10)},"Please specify a valid date (mm/dd/yyyy)"),
		jQuery.validator.addMethod("SSN",function(t,e){return t=t.replace(/\s+/g,""),this.optional(e)||t.length>8&&t.match(/^\d{3}-?\d{2}-?\d{4}$/)},"Please specify a valid SSN number"),

		//load radio buttons and select objects with the correct value
		preloadSpecialFormValues();

		//load datepicker
		//$("#p_dob").datepicker();

		//show/hide the 2nd-level insurance questions
		show2ndLevelInsuranceQuestions(false);

		//activate form validation
		jQuery("#register_form_2").validate({
			rules: {
				p_dob: 					{ required: true, custom_date: true },
				p_gender: 				{ required: true },
				p_ssn: 					{ required: true, SSN: true, minlength: 9, maxlength: 11 },
				p_household: 			{ required: true, digits: true },
				p_married: 				{ required: true },
				p_employment_status: 	{ required: true },
				p_uscitizen: 			{ required: true },
				p_disabled_status: 		{ required: true },
				p_medicare:				{ required: true },
				p_medicare_part_d: 		{ required: jQuery("#p_medicare_yes") },
				p_medicaid: 			{ required: true },
				p_medicaid_denial:		{ required: jQuery("#p_medicaid_yes") },
				p_lis: 					{ required: true },
				p_lis_denial: 			{ required: jQuery("#p_lis_yes") },
				p_hear_about: 			{ required: true, ascii: true },

				p_income_salary:		{ required: false, number: true },
				p_income_unemployment:	{ required: false, number: true },
				p_income_pension:		{ required: false, number: true },
				p_income_annuity:		{ required: false, number: true },
				p_income_ss_retirement:	{ required: false, number: true },
				p_income_ss_disability:	{ required: false, number: true },
				p_income_zero:			{ required: { depends: function(element) {
												                    return (jQuery('input[name=p_income_salary]').val() == 0 &&
												                            jQuery('input[name=p_income_unemployment]').val() == 0 &&
												                            jQuery('input[name=p_income_pension]').val() == 0 &&
												                            jQuery('input[name=p_income_annuity]').val() == 0 &&
												                            jQuery('input[name=p_income_ss_retirement]').val() == 0 &&
												                            jQuery('input[name=p_income_ss_disability]').val() == 0);
                						}}}
			},

 			messages: {
				p_income_zero: "Please check this if you currently have no income."
 			},

			errorPlacement: jQueryValidation_PlaceErrorLabels
		});

		jQuery("input[type='radio']").keypress(function(e){
		    if(e.keyCode === 13) {
	    	    jQuery(this).attr("checked", 'checked');
		        return false;
		    }
		});

		jQuery("input[name='p_medicare'], input[name=p_medicaid], input[name=p_lis]").change(function(e) {show2ndLevelInsuranceQuestions(e);});

		jQuery("a[rel]").hover(
			function(e) {
				if (jQuery(this).attr("rel") != "") {
					pos = jQuery(this).position();

					jQuery("#register_form_2").append("<p class='tooltips'>" + jQuery(this).attr("rel") + "</p>");
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

		jQuery('input[type=text]').change(function() {
			updateZeroIncome();
		});

		jQuery('input.input_zero').focus(function() {
			val = jQuery(this).val() - 0;
			if (val == 0) {
				jQuery(this).val('');
			}
		});

		jQuery('input.input_zero').blur(function() {
			val = jQuery(this).val() - 0;
			if (val == 0) {
				jQuery(this).val('0.00');
			}
		});

		jQuery("#bPrevStep").click(function() {
			currStep = jQuery('input[name="register_step"]').val();
			jQuery('input[name="register_step"]').val(currStep-2);
		});

		//add masks
		jQuery("input[name='p_dob']").mask("99/99/9999");
		jQuery("input[name='p_ssn']").mask("999-99-9999");

		//hide SSN
		/*
		jQuery("input[name='p_ssn']").blur(function() {
			ssn_value = jQuery(this).val();
			jQuery(this).attr('realValue', ssn_value);

			ssn_split_value = ssn_value.split('-')
			for(ssn_part in ssn_split_value) {
				if (ssn_part < 2) {
					ssn_split_value[ssn_part] = ssn_split_value[ssn_part].replace(/[0-9]/g, "*");
				}
			}
			jQuery(this).val(ssn_split_value.join('-'));
		});
		*/

		//disbale "Go" button on Android to submit the form
		jQuery("input[type='text']").keypress(function(e){
			if(e.keyCode === 13 && /Android/.test(navigator.userAgent)) {
				e.preventDefault();
			}
		});

		//form submit
		jQuery('form#register_form_2').submit(function() {
			scrollToInvalidFormElements();
		});

		//GOOGLE Analytics
		ga('send', 'event', 'steps', 'formload', '20150806, enrollment, form, step2, patient-info', {'nonInteraction': 1})
	});

</script>

<div class="page_steps">
	<div class="page_step page_step_checked">Personal<br/>Information</div>
	<div class="page_step page_step_current">Patient<br/>Information</div>
	<div class="page_step">Medical<br/>Information</div>
	<div class="page_step">Enroll<br/>and Payment</div>
	<br clear="all"/>
</div>

<h2 class="center-alignment">PRESCRIPTION HOPE ENROLLMENT<br/>PATIENT INFORMATION</h2>

<p class="center-alignment moduleSubheader">Fields with asterisks (*) are required.</p>

<form id="register_form_2" method="post" action="https://manage.prescriptionhope.com/register.php">
<input type="hidden" name="register_step" value="2">

<p class="form-row">
	<label for="p_dob">Date of Birth * (mm/dd/yyyy)</label>
	<input type="text" id="p_dob" name="p_dob" value="<?php echo $data['p_dob'];?>"><br/>
</p>

<p class="form-row">
	<label for="p_gender" class="w670-mixed">Gender *</label>
	<input type="radio" id="p_gender_m" name="p_gender" value="M" preload="<?php echo $data['p_gender'];?>"> <label for="p_gender_m" class='no_width'>Male</label> &nbsp;
	<input type="radio" id="p_gender_f" name="p_gender" value="F"> <label for="p_gender_f" class='no_width'>Female</label><br/>
</p>

<p class="form-row">
	<label for="p_ssn">SSN (<a href="#" rel="Your Social Security Number is required by the pharmaceutical companies for completing the application process to begin<br/> filling your medication order(s).  Your personal information is always kept safe through our government-grade, secured software." class="skipLeave disable-click">?</a>)*</label>
	<input type="text" name="p_ssn" value="<?php echo $data['p_ssn'];?>" maxlength="11">
</p>

<p class="form-row">
	<label for="p_household">Number of people in household *</label>
	<input type="text" name="p_household" value="<?php echo htmlspecialchars(stripslashes($data['p_household']));?>">
</p>

<p class="form-row">
	<label for="p_married">Marital Status *</label>
	<select name="p_married" preload="<?php echo $data['p_married'];?>">
			<option value=''>Select ...
			<option value='S'>Single
			<option value='M'>Married
			<option value='D'>Divorced
			<option value='W'>Widowed
	</select>
</p>

<p class="form-row">
	<label for="p_employment_status">Employment Status *</label>
	<select name="p_employment_status" preload="<?php echo $data['p_employment_status'];?>">
			<option value=''>Select ...
			<option value='F'>Full-Time
			<option value='P'>Part-Time
			<option value='R'>Retired
			<option value='U'>Unemployed
			<option value='S'>Self-Employed
	</select>
</p>

<p class="form-row">
	<label for="p_uscitizen" class="w670">Are you a US Citizen? *</label>
	<input type="radio" id="p_uscitizen_yes" name="p_uscitizen" value="1" <?php echo (($data['p_uscitizen'] != '') ? 'preload="' . (int)$data['p_uscitizen'] . '"' : ''); ?>> <label for="p_uscitizen_yes" class='no_width rpad15'>Yes</label>
	<input type="radio" id="p_uscitizen_no" name="p_uscitizen" value="0"> <label for="p_uscitizen_no" class='no_width'>No</label>
</p>

<p class="form-row">
	<label for="p_disabled_status" class="w670">Are you disabled as determined by Social Security? *</label>
	<input type="radio" id="p_disabled_status_yes" name="p_disabled_status" value="1" <?php echo (($data['p_disabled_status'] != '') ? 'preload="' . (int)$data['p_disabled_status'] . '"' : ''); ?>> <label for="p_disabled_status_yes" class='no_width rpad15'>Yes</label>
	<input type="radio" id="p_disabled_status_no" name="p_disabled_status" value="0"> <label for="p_disabled_status_no" class='no_width'>No</label><br/>
</p>

<p class="form-row">
	<label for="p_medicare" class="w670">Are you on Medicare? *</label>
	<input type="radio" id="p_medicare_yes" name="p_medicare" value="1" <?php echo (($data['p_medicare'] != '') ? 'preload="' . (int)$data['p_medicare'] . '"' : ''); ?>> <label for="p_medicare_yes" class='no_width rpad15'>Yes</label>
	<input type="radio" id="p_medicare_no" name="p_medicare" value="0"> <label for="p_medicare_no" class='no_width'>No</label><br class='p_medicare_2nd' />

	<label for="p_medicare_part_d" class="w670 p_medicare_2nd">Do you have Medicare Part D? *</label>
	<input type="radio" id="p_medicare_part_d_yes" name="p_medicare_part_d" value="1" <?php echo (($data['p_medicare_part_d'] != '') ? 'preload="' . (int)$data['p_medicare_part_d'] . '"' : ''); ?> class="tmargin5 p_medicare_2nd"> <label for="p_medicare_part_d_yes" class='no_width rpad15 p_medicare_2nd'>Yes</label>
	<input type="radio" id="p_medicare_part_d_no" name="p_medicare_part_d" value="0" class="tmargin5 p_medicare_2nd"> <label for="p_medicare_part_d_no" class='no_width p_medicare_2nd'>No</label><br/>
</p>

<p class="form-row">
	<label for="p_medicaid" class="w670">Have you applied for Medicaid? *</label>
	<input type="radio" id="p_medicaid_yes" name="p_medicaid" value="1" <?php echo (($data['p_medicaid'] != '') ? 'preload="' . (int)$data['p_medicaid'] . '"' : ''); ?>> <label for="p_medicaid_yes" class='no_width rpad15'>Yes</label>
	<input type="radio" id="p_medicaid_no" name="p_medicaid" value="0"> <label for="p_medicaid_no" class='no_width'>No</label><br class='p_medicaid_2nd' />

	<label for="p_medicaid" class="w670 p_medicaid_2nd">If yes, did you receive a denial letter? *</label>
	<input type="radio" id="p_medicaid_denial_yes" name="p_medicaid_denial" value="1" <?php echo (($data['p_medicaid_denial'] != '') ? 'preload="' . (int)$data['p_medicaid_denial'] . '"' : ''); ?> class="tmargin5 p_medicaid_2nd"> <label for="p_medicaid_denial_yes" class='no_width rpad15 p_medicaid_2nd'>Yes</label>
	<input type="radio" id="p_medicaid_denial_no" name="p_medicaid_denial" value="0" class="tmargin5 p_medicaid_2nd"> <label for="p_medicaid_denial_no" class='no_width p_medicaid_2nd'>No</label><br/>
</p>

<p class="form-row">
	<label for="p_lis" class="w670">Have you applied for Low Income Subsidy (LIS)? *</label>
	<input type="radio" id="p_lis_yes" name="p_lis" value="1" <?php echo (($data['p_lis'] != '') ? 'preload="' . (int)$data['p_lis'] . '"' : ''); ?>> <label for="p_lis_yes" class='no_width rpad15'>Yes</label>
	<input type="radio" id="p_lis_no" name="p_lis" value="0"> <label for="p_lis_no" class='no_width'>No</label><br class='p_lis_2nd'/>

	<label for="p_lis" class="w670 p_lis_2nd">If yes, did you receive a denial letter? *</label>
	<input type="radio" id="p_lis_denial_yes" name="p_lis_denial" value="1" <?php echo (($data['p_lis_denial'] != '') ? 'preload="' . (int)$data['p_lis_denial'] . '"' : ''); ?> class="tmargin5 p_lis_2nd"> <label for="p_lis_denial_yes" class='no_width rpad15 p_lis_2nd'>Yes</label>
	<input type="radio" id="p_lis_denial_no" name="p_lis_denial" value="0" class="tmargin5 p_lis_2nd"> <label for="p_lis_denial_no" class='no_width p_lis_2nd'>No</label>
</p>

<p class="form-row">
	<label for="p_hear_about" class="w670">How did you hear about Prescription Hope? Please be specific. *</label><br/>
	<?php 			if($data['p_hear_about'] == '2685-4694 Access Health Insurance, Inc')
			    $data['p_hear_about'] = '2685-4694 JibeHealth';?>
	<input type="text" name="p_hear_about" value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about']));?>" class="w800 no-margin" <?=((isset($_SESSION['register_data']['p_application_source']) && trim($_SESSION['register_data']['p_application_source']) != '') ? 'readonly="readonly"' : '')?>>
</p>

<br/>
<h2 class="center-alignment">MONTHLY INCOME</h2>

<p class="form-row">
	<label class="blank">&nbsp;</label>
	<input type="checkbox" id="p_income_zero"  name="p_income_zero" value="1" <?php echo (($data['p_income_zero'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
	<label for="p_income_zero" class="big">I currently have no income</label>
</p>

<p class="form-row">
	<label class="blank">&nbsp;</label>
	<input type="checkbox" id="p_income_file_tax_return"  name="p_income_file_tax_return" value="1" <?php echo (($data['p_income_file_tax_return'] == 1) ? 'checked="checked"' : ''); ?>>&nbsp;&nbsp;
	<label for="p_income_file_tax_return" class="big">I currently do not file a tax return</label>
</p>

<p class="form-row">
	<label for="p_income_salary" class="w260">Gross Salary/Wages</label>
	$ <input type="text" name="p_income_salary" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_salary']));?>" class="input_zero"><br/>
</p>

<p class="form-row">
	<label for="p_income_unemployment" class="w260">Unemployment</label>
	$ <input type="text" name="p_income_unemployment" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_unemployment']));?>" class="input_zero"><br/>
</p>

<p class="form-row">
	<label for="p_income_pension" class="w260">Pension</label>
	$ <input type="text" name="p_income_pension" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_pension']));?>" class="input_zero"><br/>
</p>

<p class="form-row">
	<label for="p_income_annuity" class="w260">Annuity/IRA</label>
	$ <input type="text" name="p_income_annuity" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_annuity']));?>" class="input_zero"><br/>
</p>

<p class="form-row">
	<label for="p_income_ss_retirement" class="w260">SS Retirement</label>
	$ <input type="text" name="p_income_ss_retirement" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_ss_retirement']));?>" class="input_zero"><br/>
</p>

<p class="form-row">
	<label for="p_income_ss_disability" class="w260">SS Disability</label>
	$ <input type="text" name="p_income_ss_disability" value="<?php printf('%0.2f', preg_replace("/[^0-9.]/", "", $data['p_income_ss_disability']));?>" class="input_zero"><br/>
</p>

<div class="form-group-spacer"></div>

<p class="center-alignment">
	<input type="submit" name="bPrevStep" id="bPrevStep" value="Back" class="cancel small-button-orange">
	<input type="submit" name="bNextStep" id="bNextStep" value="Next" class="small-button-orange">
</p>

</form>
