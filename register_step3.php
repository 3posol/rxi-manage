<?php

if (!isset($data['medication']) || count($data['medication']) == 0) {
	$medication_data = array('medication_doctor', 'medication_name', 'medication_strength', 'medication_frequency');
	$data['medication'] = array(array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''), array_fill_keys($medication_data, ''));
}

?>

<script type="text/javascript">

	jQuery().ready(function() {
		//load radio buttons and select objects with the correct value
		preloadSpecialFormValues();

		//activate form validation
		//jQuery("#register_form_4").validate();
		jQuery("#register_form_3").submit(function(){
			var submitButtonID = jQuery("input[type=submit][clicked=true]").attr("id");
			if (submitButtonID == "bNextStep") {
				//remove all the existent errors
				jQuery(".astable_error").remove();
				jQuery(".error").remove();

				//validate medication rows
				hasMeds = false;
				medsWithErrors = false;
				first_invalid_row = false;

				for(i = 0; i < 10; i++) {
					m_doctor = jQuery("select[name='medication_doctor[]']").eq(i).val();
					m_medication = jQuery("input[name='medication_name[]']").eq(i).val();
					m_strength = jQuery("input[name='medication_strength[]']").eq(i).val();
					m_frequency = jQuery("input[name='medication_frequency[]']").eq(i).val();

					//if there is any info on this line, then check if we have all the required information
					errorsObj = "";
					if (m_doctor || m_medication || m_strength || m_frequency) {
						errorsObj = errorsObj + "<label class='astable astable_error'>" + ((!m_medication) ? "Required field" : ((!isAscii(m_medication)) ? "Invalid characters" : "&nbsp;")) + "</label>";
						errorsObj = errorsObj + "<label class='astable astable_error'>" + ((!m_strength) ? "Required field" : ((!isAscii(m_strength)) ? "Invalid characters" : "&nbsp;")) + "</label>";
						errorsObj = errorsObj + "<label class='astable astable_error'>" + ((!m_frequency) ? "Required field" : ((!isAscii(m_frequency)) ? "Invalid characters" : "&nbsp;")) + "</label>";
						errorsObj = errorsObj + "<label class='astable astable_error'>" + ((!m_doctor) ? "Required field" : ((!isAscii(m_doctor)) ? "Invalid characters" : "&nbsp;")) + "</label>";

						validMed = (m_doctor && isAscii(m_doctor) && m_medication && isAscii(m_medication) && m_strength && isAscii(m_strength) && m_frequency && isAscii(m_frequency)) ? true : false;

						if (validMed) {
							hasMeds = true;
						} else {
							jQuery("select[name='medication_doctor[]']").eq(i).after(errorsObj);
							medsWithErrors = true;

							if (first_invalid_row === false) {
								first_invalid_row = jQuery("input[name='medication_name[]']").eq(i);
							}
						}
					}
				}

				if (hasMeds && !medsWithErrors) {
					//submit
					return true;
				} else {
					//add error for the first medication
					if (!medsWithErrors) {
						jQuery("select[name='medication_doctor[]']").eq(0).after("<label class='error nopad-error'>Please add at least one medication.</label>");
						jQuery("input[name='medication_name[]']").eq(0).focus();

						first_invalid_row = jQuery("input[name='medication_name[]']").eq(0);
					}

					//scroll to first invalid row
					if (first_invalid_row !== false) {
						jQuery(window).scrollTop(first_invalid_row.position().top - 250);
						jQuery(window).scrollLeft(0);
					}

					return false;
				}
			}
		});

		jQuery('.doctors_dropdown').change(function(e) {
			if (jQuery(this).find(':selected').text() == 'Add new doctor') {
				medication_line = parseInt(jQuery(this).attr('class').split(' ')[1].replace('medication_line_', ''));

				showAddDoctorForm(medication_line);
			} else if (jQuery(this).val() != '') {
				//remove the "Add new doctor" option from the medication that's edited
				//if (jQuery('#new_doctor_form').length) {
				//	jQuery(this).find('option').each(function() {
				//		if (jQuery(this).text() == 'Add new doctor') {
				//			jQuery(this).remove();
				//		}
				//	});
				//}

				//hide any existent add new doctor form
				hideAddDoctorForm(e);
			}
		});

		jQuery("#bPrevStep").click(function() {
			currStep = jQuery('input[name="register_step"]').val();
			jQuery('input[name="register_step"]').val(currStep-2);
		});

		jQuery("form#register_form_3 input[type=submit]").click(function() {
		    jQuery("input[type=submit]", jQuery(this).parents("form")).removeAttr("clicked");
		    jQuery(this).attr("clicked", "true");
		});

		//disbale "Go" button on Android to submit the form
		jQuery("input[type='text']").keypress(function(e){
			if(e.keyCode === 13 && /Android/.test(navigator.userAgent)) {
				e.preventDefault();
			}
		});

		//form submit
		jQuery('form#register_form_3').submit(scrollToInvalidFormElements);

		//GOOGLE Analytics
		ga('send', 'event', 'steps', 'formload', '20150806, enrollment, form, step3, medical-info', {'nonInteraction': 1})
	});

</script>

<div class="page_steps">
	<div class="page_step page_step_checked">Personal<br/>Information</div>
	<div class="page_step page_step_checked">Patient<br/>Information</div>
	<div class="page_step page_step_current">Medical<br/>Information</div>
	<div class="page_step">Enroll<br/>and Payment</div>
	<br clear="all"/>
</div>

<h2 class="center-alignment">PRESCRIPTION HOPE ENROLLMENT<br/>MEDICAL INFORMATION</h2>

<p class="center-alignment moduleSubheader">
	Please list your medication, strength, frequency, and the doctor prescribing that medication.<br/>
	Only list the medications you are requesting through Prescription Hope.
</p>

<br/>

<form id="register_form_3" method="post" action="https://manage.prescriptionhope.com/register.php">
<input type="hidden" name="register_step" value="3">

<div class="medication_list">
	<p class="no-margin">
		<label for="medication_name" class="astable">Medication Name</label>
		<label for="medication_strength" class="astable">Strength</label>
		<label for="medication_frequency" class="astable">Frequency (ex. daily)</label>
		<label for="medication_doctor" class="astable">Prescribing Doctor</label>
	</p>

	<?php foreach ($data['medication'] as $key => $medication) { ?>
		<input type="text" name="medication_name[]" value="<?php echo htmlspecialchars(stripslashes($medication['medication_name']));?>" class="astable medication_line_<?=$key?>">
		<input type="text" name="medication_strength[]" value="<?php echo htmlspecialchars(stripslashes($medication['medication_strength']));?>" class="astable medication_line_<?=$key?>">
		<input type="text" name="medication_frequency[]" value="<?php echo htmlspecialchars(stripslashes($medication['medication_frequency']));?>" class="astable medication_line_<?=$key?>">

		<select name="medication_doctor[]" preload="<?php echo $medication['medication_doctor'];?>" class="astable medication_line_<?=$key?> doctors_dropdown">
			<option value="">choose doctor ...</option>
			<option value="">Add new doctor</option>
			<?php foreach ($data['doctors'] as $dr_key => $doctor) { ?>
				<?php if ($doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') { ?>
					<option value="<?php echo ($dr_key+1);?>"><?php echo 'Doctor ' . ($dr_key+1) . ' (' . $doctor['doctor_first_name'] . ' ' . $doctor['doctor_last_name'] . ')';?></option>
				<?php } ?>
			<?php } ?>
		</select>
		<br clear="all" class="medication_line_<?=$key?>"/>
		<div class="form-group-spacer-small medication_line_<?=$key?>"></div>
	<?php } ?>
</div>

<?php foreach ($data['doctors'] as $dr_key => $doctor) { ?>
	<?php if ($doctor['doctor_first_name'] != '' && $doctor['doctor_last_name'] != '') { ?>
		<input type="hidden" name="doctor_first_name[<?=$dr_key?>]" value="<?=$doctor['doctor_first_name']?>">
		<input type="hidden" name="doctor_last_name[<?=$dr_key?>]" value="<?=$doctor['doctor_last_name']?>">
		<input type="hidden" name="doctor_facility[<?=$dr_key?>]" value="<?=$doctor['doctor_facility']?>">
		<input type="hidden" name="doctor_address[<?=$dr_key?>]" value="<?=$doctor['doctor_address']?>">
		<input type="hidden" name="doctor_address2[<?=$dr_key?>]" value="<?=$doctor['doctor_address2']?>">
		<input type="hidden" name="doctor_city[<?=$dr_key?>]" value="<?=$doctor['doctor_city']?>">
		<input type="hidden" name="doctor_state[<?=$dr_key?>]" value="<?=$doctor['doctor_state']?>">
		<input type="hidden" name="doctor_zip[<?=$dr_key?>]" value="<?=$doctor['doctor_zip']?>">
		<input type="hidden" name="doctor_phone[<?=$dr_key?>]" value="<?=$doctor['doctor_phone']?>">
		<input type="hidden" name="doctor_fax[<?=$dr_key?>]" value="<?=$doctor['doctor_fax']?>">
	<?php } ?>
<?php } ?>

<br/><br/>

<p class="center-alignment">
	<input type="submit" name="bPrevStep" id="bPrevStep" value="Back" class="cancel small-button-orange">
	<input type="submit" name="bNextStep" id="bNextStep" value="Next" class="small-button-orange">
</p>

</form>

<style>
body {
	min-width: 880px;
}
</style>