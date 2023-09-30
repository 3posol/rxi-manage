function getAge(dateString) {
    var today = new Date();
    var birthDate = new Date(dateString);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    return age;
}

function isAscii(str) {
	return /^[\x00-\x7F]*$/.test(str);
}

/*
*
* Sets the values for all the current page form elements that have attribute preload set
* - only for radio buttons and select objects
*
*/
function preloadSpecialFormValues() {
	//pre-check radio buttons
	jQuery(":input[preload]").each(function() {
		//input objects
		if (jQuery(this).is("input") && jQuery(this).attr('type') == 'radio' && jQuery(this).attr('preload') != '') {
			jQuery("input[name=" + jQuery(this).attr('name') + "][value=" + jQuery(this).attr('preload') + "]").attr('checked', 'checked');

			if (jQuery(this).attr('name') == 'p_medicare'  || jQuery(this).attr('name') == 'p_medicaid' || jQuery(this).attr('name') == 'p_lis') {
				show2ndLevelInsuranceQuestions(null);
			}

			if (jQuery(this).attr('name') == 'p_payment_method') {
				showSelectedPaymentMethods();
			}
		}

		//select objects
		if (jQuery(this).is("select")) {
			jQuery(this).val(jQuery(this).attr('preload'));
		}
	});
}

/*
*
* A helper for jQuery Validation to place the errors related to radio buttons or checkboxes after the last inpu's label
*
*/
function jQueryValidation_PlaceErrorLabels (error, element) {
	switch (element.attr('type')) {
		case 'checkbox':
			if (error.attr("for") == 'p_payment_agreement' || error.attr("for") == 'p_service_agreement' || error.attr("for") == 'p_guaranty_agreement' || error.attr("for") == 'p_acknowledge_agreement') {
				error.addClass("nopad-error");
			}

			error.insertAfter(jQuery("label[for=" + element.attr("id") + "]").last());
			break;

		case 'radio':
			if (error.attr("for") != 'p_is_minor' && error.attr("for") != 'p_gender' && error.attr("for") != 'p_payment_method') {
				error.addClass("radio-error");
			}

			if (error.attr("for") != 'p_payment_method') {
				error.insertAfter(jQuery("label[for=" + jQuery("input[name=" + element.attr("name") + "]").last().attr('id') + "]").last());
			} else {
				//error.insertAfter(jQuery("img[class=payment_images]").last());
				error.addClass("nopad-error");
				error.attr("style", "display: block; width: auto;");
				error.insertAfter(jQuery("label[for='p_payment_method']"));
			}

			break;

		default:
			if (error.attr("for") == 'p_hear_about' || error.attr("for") == 'p_hear_about_1' || error.attr("for") == 'leave_reason') {
				error.addClass("nopad-error");
			}

			error.insertAfter(element);
	}
}

/*
*
* Scrolls up the page to the first invalid element
*
*/
function scrollToInvalidFormElements () {
	first_invalid_element = jQuery('input.error, select.error, textarea.error').eq(0);
	if (typeof first_invalid_element.position() != 'undefined') {
		jQuery(window).scrollTop(first_invalid_element.position().top - 250);
		first_invalid_element.focus();
	}
}

/*
*
* Activates the selected payment method set of inputs
*
*/
function showSelectedPaymentMethods() {
	jQuery('#payment_cc').hide();
	jQuery('#payment_ach').hide();

	show_payment_method = jQuery('input[name=p_payment_method]:checked').val();
	jQuery('#payment_' + show_payment_method).show();
}

/*
*
* Activates the entire patient profile
*
*/
function showSelectedPatientProfile() {
	jQuery('.patient_parent_profile').hide();
	//jQuery('.patient_profile').hide();

	if (jQuery('input[name=p_is_minor]:checked').val() == 1) {
		jQuery('.patient_parent_profile').show();
		//jQuery('.patient_profile').show();
	}

	if (jQuery('input[name=p_is_minor]:checked').val() == 0) {
		//jQuery('.patient_profile').show();

		//empty parent fields
		jQuery('input[name="p_parent_first_name"]').val('');
		jQuery('input[name="p_parent_middle_initial"]').val('');
		jQuery('input[name="p_parent_last_name"]').val('');
		jQuery('input[name="p_parent_phone"]').val('');
	}
}

/*
*
* Activates the 2nd level insurance questions
*
*/
function show2ndLevelInsuranceQuestions(e) {
	if (e) {
		inputObjName = e.target.name;

		if (jQuery('input[name=' + inputObjName + ']:checked').val() == 1) {
			jQuery('.' + inputObjName + '_2nd').show();
		} else {
			jQuery('.' + inputObjName + '_2nd').hide();
			jQuery('.' + inputObjName + '_2nd').attr('checked', false);
		}
	} else {
		if (jQuery('input[name=p_medicare]:checked').val() == 1) {
			jQuery('.p_medicare_2nd').show();
		} else {
			jQuery('.p_medicare_2nd').hide();
			jQuery('.p_medicare_2nd').attr('checked', false);
		}

		if (jQuery('input[name=p_medicaid]:checked').val() == 1) {
			jQuery('.p_medicaid_2nd').show();
		} else {
			jQuery('.p_medicaid_2nd').hide();
			jQuery('.p_medicaid_2nd').attr('checked', false);
		}

		if (jQuery('input[name=p_lis]:checked').val() == 1) {
			jQuery('.p_lis_2nd').show();
		} else {
			jQuery('.p_lis_2nd').hide();
			jQuery('.p_lis_2nd').attr('checked', false);
		}
	}
}

/*
*
* 2nd Step - auto-uncheck zero income checkbox if some income was entered
*
*/
function updateZeroIncome () {
    if (jQuery('input[name=p_income_salary]').val() != 0 ||
		jQuery('input[name=p_income_unemployment]').val() != 0 ||
		jQuery('input[name=p_income_pension]').val() != 0 ||
		jQuery('input[name=p_income_annuity]').val() != 0 ||
		jQuery('input[name=p_income_ss_retirement]').val() != 0 ||
		jQuery('input[name=p_income_ss_disability]').val() != 0)
    {
		jQuery('input[name=p_income_zero]').attr('checked', false);
    }
}

/*
*
* sum up the income to get the annual income
*
*/
function updateTotalAnnualIncome () {
	var income = 0;
	income += (jQuery('input[name=p_income_salary]').val() != '') ? parseFloat(jQuery('input[name=p_income_salary]').val().replace(/[^0-9.]/g, '')) : 0;
	income += (jQuery('input[name=p_income_unemployment]').val() != '') ? parseFloat(jQuery('input[name=p_income_unemployment]').val().replace(/[^0-9.]/g, '')) : 0;
	income += (jQuery('input[name=p_income_pension]').val() != '') ? parseFloat(jQuery('input[name=p_income_pension]').val().replace(/[^0-9.]/g, '')) : 0;
	income += (jQuery('input[name=p_income_annuity]').val() != '') ? parseFloat(jQuery('input[name=p_income_annuity]').val().replace(/[^0-9.]/g, '')) : 0;
	income += (jQuery('input[name=p_income_ss_retirement]').val() != '') ? parseFloat(jQuery('input[name=p_income_ss_retirement]').val().replace(/[^0-9.]/g, '')) : 0;
	income += (jQuery('input[name=p_income_ss_disability]').val() != '') ? parseFloat(jQuery('input[name=p_income_ss_disability]').val().replace(/[^0-9.]/g, '')) : 0;
    jQuery('input[name=p_income_annual_income]').val((income * 12).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2, useGrouping: false}));
//    jQuery('input[name=p_income_annual_income]').val(income * 12).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
}

/*
*
* 3rd Step - Show new doctor form
*
*/
function showAddDoctorForm (line) {
	//hide the rest of the medication rows
	for (i = line+1; i < 10; i++) {
		jQuery('.medication_line_' + i).hide();
	}

	//hide submit buttons
	//jQuery('#bPrevStep, #bNextStep').hide();

	//hide any previous add new doctor form
	if (jQuery('#new_doctor_form').length) {
		jQuery('#new_doctor_form').remove();
	}

	//show add doctor form
	new_doctor_form_template = '\
		<div id="new_doctor_form">\
			<br/><br/>\
			<p class="center-alignment subhead2-blue">ADD NEW DOCTOR</p>\
			<p class="center-alignment subhead2-blue">Only list doctor prescribing the medication.</p>\
			<br/>\
			<p class="center-alignment moduleSubheader">Fields with asterisks (*) are required.</p>\
			\
			<p>\
				<label for="dr_first_name">Doctor First Name *</label>\
				<input type="text" name="dr_first_name" value="" class="dr-required"><br/><br/>\
				\
				<label for="dr_last_name">Doctor Last Name *</label>\
				<input type="text" name="dr_last_name" value="" class="dr-required"><br/><br/>\
				\
				<label for="dr_facility">Facility Name</label>\
				<input type="text" name="dr_facility" value=""><br/><br/>\
				\
				<label for="dr_address">Address *</label>\
				<input type="text" name="dr_address" value="" class="dr-required"><br/><br/>\
				\
				<label for="dr_address2">Suite</label>\
				<input type="text" name="dr_address2" value=""><br/><br/>\
				\
				<label for="dr_city">City *</label>\
				<input type="text" name="dr_city" value="" class="dr-required"><br/><br/>\
				\
				<label for="dr_state">State *</label>\
				<select name="dr_state" class="dr-required">\
					<option value="" selected="selected">Select a State</option>\
					<option value="AL">Alabama</option>\
					<option value="AK">Alaska</option>\
					<option value="AZ">Arizona</option>\
					<option value="AR">Arkansas</option>\
					<option value="CA">California</option>\
					<option value="CO">Colorado</option>\
					<option value="CT">Connecticut</option>\
					<option value="DE">Delaware</option>\
					<option value="DC">District Of Columbia</option>\
					<option value="FL">Florida</option>\
					<option value="GA">Georgia</option>\
					<option value="HI">Hawaii</option>\
					<option value="ID">Idaho</option>\
					<option value="IL">Illinois</option>\
					<option value="IN">Indiana</option>\
					<option value="IA">Iowa</option>\
					<option value="KS">Kansas</option>\
					<option value="KY">Kentucky</option>\
					<option value="LA">Louisiana</option>\
					<option value="ME">Maine</option>\
					<option value="MD">Maryland</option>\
					<option value="MA">Massachusetts</option>\
					<option value="MI">Michigan</option>\
					<option value="MN">Minnesota</option>\
					<option value="MS">Mississippi</option>\
					<option value="MO">Missouri</option>\
					<option value="MT">Montana</option>\
					<option value="NE">Nebraska</option>\
					<option value="NV">Nevada</option>\
					<option value="NH">New Hampshire</option>\
					<option value="NJ">New Jersey</option>\
					<option value="NM">New Mexico</option>\
					<option value="NY">New York</option>\
					<option value="NC">North Carolina</option>\
					<option value="ND">North Dakota</option>\
					<option value="OH">Ohio</option>\
					<option value="OK">Oklahoma</option>\
					<option value="OR">Oregon</option>\
					<option value="PA">Pennsylvania</option>\
					<option value="RI">Rhode Island</option>\
					<option value="SC">South Carolina</option>\
					<option value="SD">South Dakota</option>\
					<option value="TN">Tennessee</option>\
					<option value="TX">Texas</option>\
					<option value="UT">Utah</option>\
					<option value="VT">Vermont</option>\
					<option value="VA">Virginia</option>\
					<option value="WA">Washington</option>\
					<option value="WV">West Virginia</option>\
					<option value="WI">Wisconsin</option>\
					<option value="WY">Wyoming</option>\
				</select><br/><br/>\
				\
				<label for="dr_zip">ZIP Code *</label>\
				<input type="text" name="dr_zip" value="" maxlength="5" class="dr-required"><br/><br/>\
				\
				<label for="dr_phone">Phone *</label>\
				<input type="text" name="dr_phone" value="" class="dr-required"><br/><br/>\
				\
				<label for="dr_fax">Fax</label>\
				<input type="text" name="dr_fax" value="">\
				\
				<br/><br/>\
				\
				<label>&nbsp;</label>\
				<input type="button" name="bAddNewDoctor" id="bAddNewDoctor" value="ADD" class="cancel small-button-orange"> &nbsp;\
				<a href="#" id="bCancelAddNewDoctor">Cancel</a>\
			</p>\
		</div>';

	//create form
	jQuery('.medication_line_' + line).last().after(new_doctor_form_template);

	//add masks
	jQuery("input[name='dr_zip']").each(function(index, elem) {
		jQuery(elem).mask('99999');
	});
	//
	jQuery("input[name='dr_phone']").each(function(index, elem) {
		jQuery(elem).mask('999-999-9999');
	});
	//
	jQuery("input[name='dr_fax']").each(function(index, elem) {
		jQuery(elem).mask('999-999-9999');
	});

	//buttons actions
	jQuery('#bAddNewDoctor').click(AddNewDoctor);
	jQuery('#bCancelAddNewDoctor').click(hideAddDoctorForm);
}

/*
*
* 3rd Step - Hide the add new doctor form
*
*/
function AddNewDoctor (e) {
	e.preventDefault();

	valid_form = true;
	first_invalid_element = false;

	//remove existent errors
	jQuery('label.error').remove();

	//validate form
	jQuery('.dr-required').each(function(index, elem) {
		if (jQuery(elem).val() == '') {
			if (first_invalid_element === false) {
				first_invalid_element = jQuery(elem);
			}

			//add error label
			errorLabel = jQuery('<label>').attr('for', jQuery(elem).id).addClass('error').text('This field is required');
			errorLabel.insertAfter(jQuery(elem));

			valid_form = false;
		}
	})
	//ascii validation
	jQuery('input').each(function(index, elem) {
		elemName = jQuery(elem).attr('name');
		if (typeof elemName != 'undefined' && jQuery(elem).attr('name').substr(0, 3) == 'dr_') {
			if (!isAscii(jQuery(elem).val())) {
				if (first_invalid_element === false) {
					first_invalid_element = jQuery(elem);
				}

				//add error label
				errorLabel = jQuery('<label>').attr('for', jQuery(elem).id).addClass('error').text('Please insert only alphanumeric characters.');
				errorLabel.insertAfter(jQuery(elem));

				valid_form = false;
			}
		}
	})

	//validate zip code
	zip_code_elem = jQuery('input[name="dr_zip"]');
	if (!(/^\s*\d{5}\s*$/.test(zip_code_elem.val()))) {
		//add error label
		errorLabel = jQuery('<label>').attr('for', zip_code_elem.id).addClass('error').text('Invalid zip code');
		errorLabel.insertAfter(zip_code_elem);

		if ((first_invalid_element === false) || (first_invalid_element && first_invalid_element.attr('name') == 'dr_phone')) {
			zip_code_elem.focus();
			first_invalid_element = zip_code_elem;
		}

		valid_form = false;
	}

	if (valid_form) {
		//
		dr_id = jQuery('.doctors_dropdown:visible').last().find('option').size() - 1;

		//add new doctor to the form
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_first_name[' + (dr_id - 1) + ']').val(jQuery('input[name="dr_first_name"]').val()).insertBefore(jQuery('#bSubmit'));
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_last_name[' + (dr_id - 1) + ']').val(jQuery('input[name="dr_last_name"]').val()).insertBefore(jQuery('#bSubmit'));
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_facility[' + (dr_id - 1) + ']').val(jQuery('input[name="dr_facility"]').val()).insertBefore(jQuery('#bSubmit'));
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_address[' + (dr_id - 1) + ']').val(jQuery('input[name="dr_address"]').val()).insertBefore(jQuery('#bSubmit'));
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_address2[' + (dr_id - 1) + ']').val(jQuery('input[name="dr_address2"]').val()).insertBefore(jQuery('#bSubmit'));
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_city[' + (dr_id - 1) + ']').val(jQuery('input[name="dr_city"]').val()).insertBefore(jQuery('#bSubmit'));
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_state[' + (dr_id - 1) + ']').val(jQuery('select[name="dr_state"]').val()).insertBefore(jQuery('#bSubmit'));
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_zip[' + (dr_id - 1) + ']').val(jQuery('input[name="dr_zip"]').val()).insertBefore(jQuery('#bSubmit'));
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_phone[' + (dr_id - 1) + ']').val(jQuery('input[name="dr_phone"]').val()).insertBefore(jQuery('#bSubmit'));
		jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_fax[' + (dr_id - 1) + ']').val(jQuery('input[name="dr_fax"]').val()).insertBefore(jQuery('#bSubmit'));

		//add the new doctor to the medication doctor drop-downs
		jQuery('.doctors_dropdown').append(jQuery('<option>', {value: dr_id, text: 'Doctor ' + dr_id + ' (' + jQuery('input[name="dr_first_name"]').val() + ' ' + jQuery('input[name="dr_last_name"]').val() + ')'}));

		//auto-select the new doctor for the medication that's edited
		jQuery('.doctors_dropdown:visible').last().val(dr_id);

		//remove the "Add new doctor" option from the medication that's edited
		jQuery('.doctors_dropdown:visible').last().find('option').each(function() {
			if (jQuery(this).text() == 'Add new doctor') {
				jQuery(this).remove();
			}
		});

		//destroy form
		hideAddDoctorForm(e);
	} else {
		//scroll to first invalid field
		first_invalid_element.focus();
		jQuery(window).scrollTop(first_invalid_element.position().top - 250);
	}
}

/*
*
* 3rd Step - Hide the add new doctor form
*
*/
function hideAddDoctorForm (e) {
	e.preventDefault();

	//hide form
	jQuery('#new_doctor_form').remove();

	if (jQuery('.doctors_dropdown:visible').length < 10) {
		//reset doctor value if new doctor form was canceled
		if (e.target.id == 'bCancelAddNewDoctor') {
			jQuery('.doctors_dropdown:visible').last().val('');
		}

		//focus & scroll
		jQuery('.doctors_dropdown:visible').last().focus();
		jQuery(window).scrollTop(jQuery('.doctors_dropdown:visible').last().position().top - 250);

		//show all medication lines
		for (i = 0; i < 10; i++) {
			jQuery('.medication_line_' + i).show();
		}
	}

	//show submit buttons
	//jQuery('#bPrevStep, #bNextStep').show();
}

function AddANewDoctor (e) {
	new_dr_id = parseInt(jQuery('input#dr_id').val()) + 1;

	//reset dr form
	jQuery('.dr-data').val('');
	jQuery('input#dr_id').val(new_dr_id);
}

function UpdateDoctor (e) {
	//update doctor data
	var dr_id = jQuery('input#dr_id').val();
	var dr_field_name = jQuery(this).attr('name');

	valid_form = true;
	first_invalid_element = false;

	//remove existent errors
	jQuery('label.error').remove();

	//validate form
	jQuery('.dr-required').each(function(index, elem) {
		if (jQuery(elem).val() == '') {
			//add error label
			//if (jQuery(elem).attr('name') == dr_field_name) {
				if (first_invalid_element === false) {
					first_invalid_element = jQuery(elem);
				}

				errorLabel = jQuery('<label>').attr('for', jQuery(elem).attr('name')).addClass('error').text('This field is required (when you add a new doctor).');
				errorLabel.insertAfter(jQuery(elem));
			//}

			valid_form = false;
		}
	})
	//ascii validation
	jQuery('input').each(function(index, elem) {
		elemName = jQuery(elem).attr('name');
		if (typeof elemName != 'undefined' && jQuery(elem).attr('name').substr(0, 3) == 'dr_') {
			if (!isAscii(jQuery(elem).val())) {
				//add error label
				//if (jQuery(elem).attr('name') == dr_field_name) {
					if (first_invalid_element === false) {
						first_invalid_element = jQuery(elem);
					}

					errorLabel = jQuery('<label>').attr('for', jQuery(elem).attr('name')).addClass('error').text('Please insert only alphanumeric characters (when you add a new doctor).');
					errorLabel.insertAfter(jQuery(elem));
				//}

				valid_form = false;
			}
		}
	})

	//validate zip code
	zip_code_elem = jQuery('input[name="dr_zip"]');
	if (!(/^\s*\d{5}\s*$/.test(zip_code_elem.val()))) {
		//add error label
		//if (dr_field_name == 'dr_zip') {
			errorLabel = jQuery('<label>').attr('for', zip_code_elem.attr('name')).addClass('error').text('Invalid zip code (when you add a new doctor).');
			errorLabel.insertAfter(zip_code_elem);

			if ((first_invalid_element === false) || (first_invalid_element && first_invalid_element.attr('name') == 'dr_phone')) {
				zip_code_elem.focus();
				first_invalid_element = zip_code_elem;
			}
		//}

		valid_form = false;
	}

	if (valid_form) {
		if (!jQuery('input[name="doctor_first_name[' + (dr_id) + ']"]').length) {
			CreateNewDoctorRow(dr_id);
		}

		//field_name = jQuery(this).attr('name').replace('dr_', 'doctor_');
		//jQuery('input[name="' + field_name + '[' + (dr_id) + ']"]').val(jQuery(this).val());

		jQuery('input[name="doctor_first_name[' + (dr_id) + ']"]').val(jQuery('input[name="dr_first_name"]').val());
		jQuery('input[name="doctor_last_name[' + (dr_id) + ']"]').val(jQuery('input[name="dr_last_name"]').val());
		jQuery('input[name="doctor_facility[' + (dr_id) + ']"]').val(jQuery('input[name="dr_facility"]').val());
		jQuery('input[name="doctor_address[' + (dr_id) + ']"]').val(jQuery('input[name="dr_address"]').val());
		jQuery('input[name="doctor_address2[' + (dr_id) + ']"]').val(jQuery('input[name="dr_address2"]').val());
		jQuery('input[name="doctor_city[' + (dr_id) + ']"]').val(jQuery('input[name="dr_city"]').val());
		jQuery('input[name="doctor_state[' + (dr_id) + ']"]').val(jQuery('select[name="dr_state"]').val());
		jQuery('input[name="doctor_zip[' + (dr_id) + ']"]').val(jQuery('input[name="dr_zip"]').val());
		jQuery('input[name="doctor_phone[' + (dr_id) + ']"]').val(jQuery('input[name="dr_phone"]').val());
		jQuery('input[name="doctor_fax[' + (dr_id) + ']"]').val(jQuery('input[name="dr_fax"]').val());

		UpdateDoctorsList();
	} else {
		//scroll to first invalid field
		//if (first_invalid_element) {
		//	first_invalid_element.focus();
		//	jQuery(window).scrollTop(first_invalid_element.position().top - 250);
		//}
	}

	//hide error messages
	jQuery(this).nextAll('.dr-data').each(function() {
		//if (jQuery(this).attr('name') != dr_field_name) {
			jQuery('label[for="' + jQuery(this).attr('name') + '"].error').remove();
		//}
	});
}

function CreateNewDoctorRow (new_dr_id) {
	//add new doctor to the form
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_first_name[' + (new_dr_id) + ']').val('').addClass('doctor-fields').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_last_name[' + (new_dr_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_facility[' + (new_dr_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_address[' + (new_dr_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_address2[' + (new_dr_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_city[' + (new_dr_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_state[' + (new_dr_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_zip[' + (new_dr_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_phone[' + (new_dr_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'doctor_fax[' + (new_dr_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
}

function UpdateDoctorsList () {
	jQuery('#doctors_list').remove();

	if (jQuery('input.doctor-fields').length > 0) {
		drs_list = 	'<div id="doctors_list" class="medication_list">\
						<p class="no-margin desktop-only">\
							<label class="astable">Name</label>\
							<label class="astable">Phone</label>\
							<label class="astable_big">Address</label>\
							<label class="astable_small right-alignment">Action</label>\
						</p>\
						<p class="no-margin mobile-only">\
							<label class="astable">Doctors</label>\
						</p>\
						<div class="astable_hline desktop-only"></div>';

		for(dr_id = 1; dr_id <= jQuery('input.doctor-fields').length; dr_id++) {
			drs_list += '<div class="astable_hline mobile-only"></div>\
							<p class="no-margin">\
								<span class="astable no-overflow">' + jQuery('input[name="doctor_first_name[' + dr_id + ']"]').val() + ' ' + jQuery('input[name="doctor_last_name[' + dr_id + ']"]').val() + '</span>\
								<span class="astable no-overflow">' + jQuery('input[name="doctor_phone[' + dr_id + ']"]').val() + '</span>\
								<span class="astable_big no-overflow">' + jQuery('input[name="doctor_address[' + dr_id + ']"]').val() + ', ' + jQuery('input[name="doctor_city[' + dr_id + ']"]').val() + ', ' + jQuery('input[name="doctor_state[' + dr_id + ']"]').val() + ' ' + jQuery('input[name="doctor_zip[' + dr_id + ']"]').val() + '</span>\
								<span class="astable_small right-alignment"><a href="#" class="skipLeave" onclick="EditDoctor(' + dr_id + '); return false;">Edit</a></span>\
							</p>';

			//medication doctors dropdown
			var drID = dr_id;
			var drName = jQuery('input[name="doctor_first_name[' + dr_id + ']"]').val() + ' ' + jQuery('input[name="doctor_last_name[' + dr_id + ']"]').val();
			var drExists = false;
			jQuery('.doctors_dropdown').find('option').each(function() {
				if (jQuery(this).attr('value') == dr_id) {
					jQuery(this).text(drName);
					drExists = true;
				}
			})
			if (!drExists) {
				jQuery('.doctors_dropdown').append(jQuery('<option>', {value: drID, text: drName}));
			}
		}

		drs_list += '</div>';
		jQuery(drs_list).insertAfter('p#doctor_form');
	}
}

function EditDoctor (dr_id) {
	jQuery('input#dr_id').val(dr_id);
	jQuery('input[name="dr_first_name"]').val(jQuery('input[name="doctor_first_name[' + (dr_id) + ']"]').val());
	jQuery('input[name="dr_last_name"]').val(jQuery('input[name="doctor_last_name[' + (dr_id) + ']"]').val());
	jQuery('input[name="dr_facility"]').val(jQuery('input[name="doctor_facility[' + (dr_id) + ']"]').val());
	jQuery('input[name="dr_address"]').val(jQuery('input[name="doctor_address[' + (dr_id) + ']"]').val());
	jQuery('input[name="dr_address2"]').val(jQuery('input[name="doctor_address2[' + (dr_id) + ']"]').val());
	jQuery('input[name="dr_city"]').val(jQuery('input[name="doctor_city[' + (dr_id) + ']"]').val());
	jQuery('select[name="dr_state"]').val(jQuery('input[name="doctor_state[' + (dr_id) + ']"]').val());
	jQuery('input[name="dr_zip"]').val(jQuery('input[name="doctor_zip[' + (dr_id) + ']"]').val());
	jQuery('input[name="dr_phone"]').val(jQuery('input[name="doctor_phone[' + (dr_id) + ']"]').val());
	jQuery('input[name="dr_fax"]').val(jQuery('input[name="doctor_fax[' + (dr_id) + ']"]').val());
}

function AddANewMedication (e) {
	//new_med_id = parseInt(jQuery('input#med_id').val()) + 1;
	//
	//reset med form
	//jQuery('.med-data').val('');
	//jQuery('input#med_id').val(new_med_id);

	new_med_id = parseInt(jQuery('.med-data').length / 4) + 1;

	jQuery('.medication-name-field').eq(0).clone().appendTo('#medication_form');
	jQuery('.medication-name-field').eq(new_med_id - 1).find('label').addClass('mobile-only');
	jQuery('.medication-name-field').eq(new_med_id - 1).find('input').attr('name', 'medication_name[' + new_med_id + ']').val('');

	jQuery('.medication-strength-field').eq(0).clone().appendTo('#medication_form');
	jQuery('.medication-strength-field').eq(new_med_id - 1).find('label').addClass('mobile-only');
	jQuery('.medication-strength-field').eq(new_med_id - 1).find('input').attr('name', 'medication_strength[' + new_med_id + ']').val('');

	jQuery('.medication-frequency-field').eq(0).clone().appendTo('#medication_form');
	jQuery('.medication-frequency-field').eq(new_med_id - 1).find('label').addClass('mobile-only');
	jQuery('.medication-frequency-field').eq(new_med_id - 1).find('input').attr('name', 'medication_frequency[' + new_med_id + ']').val('');

	jQuery('.medication-doctor-field').eq(0).clone().appendTo('#medication_form');
	jQuery('.medication-doctor-field').eq(new_med_id - 1).find('label').addClass('mobile-only');
	jQuery('.medication-doctor-field').eq(new_med_id - 1).find('select').attr('name', 'medication_doctor[' + new_med_id + ']').val('');
}

function UpdateMedication (e) {
	//update medication data

	var validMed = true;
	jQuery('.med-data').each(function() {
		if (jQuery(this).val() == '') {
			validMed = false;
		}
	});

	if (validMed) {
		var med_id = jQuery('input#med_id').val();
			console.log('input[name="medication_name[' + (med_id) + ']"]');

		if (!jQuery('input[name="medication_name[' + (med_id) + ']"]').length) {
			CreateNewMedicationRow(med_id);
		}

		jQuery('input[name="medication_name[' + (med_id) + ']"]').val(jQuery('input[name="med_name"]').val());
		jQuery('input[name="medication_strength[' + (med_id) + ']"]').val(jQuery('input[name="med_strength"]').val());
		jQuery('input[name="medication_frequency[' + (med_id) + ']"]').val(jQuery('input[name="med_frequency"]').val());
		jQuery('input[name="medication_doctor[' + (med_id) + ']"]').val(jQuery('select[name="med_doctor"]').val());

		//UpdateMedicationList();
	}
}

function CreateNewMedicationRow (new_med_id) {
	//add new medication to the form
	jQuery('<input>').attr('type', 'hidden').attr('name', 'medication_name[' + (new_med_id) + ']').val('').addClass('medication-fields').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'medication_strength[' + (new_med_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'medication_frequency[' + (new_med_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
	jQuery('<input>').attr('type', 'hidden').attr('name', 'medication_doctor[' + (new_med_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
}

function UpdateMedicationList () {
	jQuery('#medication_list_desktop').remove();
	jQuery('#medication_list_mobile').remove();

	if (jQuery('input.medication-fields').length > 0) {
		desktop_medication_list = '\
					<div id="medication_list_desktop" class="medication_list desktop-only">\
						<div class="astable_hline"></div>';
		mobile_medication_list = '\
					<div id="medication_list_mobile" class="medication_list mobile-only">\
						<br/><br/>\
						<p class="no-margin mobile-only">\
							<label class="astable">Medication</label>\
						</p>';

		for(med_id = 1; med_id <= jQuery('input.medication-fields').length; med_id++) {
			doctor_id = jQuery('input[name="medication_doctor[' + med_id + ']"]').val();
			desktop_medication_list += '<p class="no-margin">\
											<span class="astable no-overflow">&nbsp;&nbsp;' + jQuery('input[name="medication_name[' + med_id + ']"]').val() + ' (<a href="#" onclick="EditMedication(' + med_id + '); return false;">Edit</a>)</span>\
											<span class="astable no-overflow">&nbsp;' + jQuery('input[name="medication_strength[' + med_id + ']"]').val() + '</span>\
											<span class="astable no-overflow">' + jQuery('input[name="medication_frequency[' + med_id + ']"]').val() + '</span>\
											<span class="astable no-overflow">' + jQuery('input[name="doctor_first_name[' + doctor_id + ']"]').val() + ' ' + jQuery('input[name="doctor_last_name[' + doctor_id + ']"]').val() + '</span>\
										</p>';
			mobile_medication_list += '<div class="astable_hline"></div>\
											<p class="no-margin">\
												<span class="astable no-overflow">' + jQuery('input[name="medication_name[' + med_id + ']"]').val() + '</span>\
												<span class="astable_big no-overflow">' + jQuery('input[name="medication_strength[' + med_id + ']"]').val() + ' - ' + jQuery('input[name="medication_frequency[' + med_id + ']"]').val() + '</span>\
												<span class="astable no-overflow">' + jQuery('input[name="doctor_first_name[' + doctor_id + ']"]').val() + ' ' + jQuery('input[name="doctor_last_name[' + doctor_id + ']"]').val() + '</span>\
												<span class="astable_small right-alignment"><a href="#" onclick="EditMedication(' + med_id + '); return false;">Edit</a></span>\
											</p>';
		}

		desktop_medication_list += '</div>';
		mobile_medication_list += '</div>';
		jQuery(desktop_medication_list).insertAfter('#med_id');
		jQuery(mobile_medication_list).insertAfter('#bAddANewMedication');
	}
}

function EditMedication (med_id) {
	jQuery('input#med_id').val(med_id);
	jQuery('input[name="med_name"]').val(jQuery('input[name="medication_name[' + (med_id) + ']"]').val());
	jQuery('input[name="med_strength"]').val(jQuery('input[name="medication_strength[' + (med_id) + ']"]').val());
	jQuery('input[name="med_frequency"]').val(jQuery('input[name="medication_frequency[' + (med_id) + ']"]').val());
	jQuery('select[name="med_doctor"]').val(jQuery('input[name="medication_doctor[' + (med_id) + ']"]').val());
}

function InvalidCreditCard () {
	jQuery('<div class="cc_error bold">The card you entered is not valid, please enter your card information again or try another payment method.</div>').insertAfter('#payment_cc');

	jQuery('.payment_method_field').addClass('red');

	first_invalid_row = jQuery("input[name='p_payment_method']");
	first_invalid_row.focus();
	if (first_invalid_row !== false) {
		jQuery(window).scrollTop(first_invalid_row.position().top - 200);
		jQuery(window).scrollLeft(0);
	}

}

//
//Leave Application Form functions
//

jQuery.fn.center = function () {
	this.css("position","fixed");
	this.css("top", (jQuery(window).height() / 2) - (this.outerHeight() / 2));
	this.css("left", (jQuery(window).width() / 2) - (this.outerWidth() / 2));
	return this;
}

function preventPageLeave (e) {
	if (!preventLeave || jQuery(this).hasClass('skipLeave') || jQuery(this).attr('target') == '_blank' || jQuery('#register_form_1').length > 0 || jQuery('#register_form_5').length > 0) {
		preventLeave = (jQuery(this).hasClass('skipLeave') || jQuery(this).attr('target') == '_blank') ? preventLeave : true;
	} else if (dataEntered) {
		if (!window.confirm("Are you sure you want to leave this page?\n\nAll your information will be lost.")) {
			e.preventDefault();
		}
	}

	/*
	if (!preventLeave || jQuery(this).hasClass('skipLeave') || jQuery(this).attr('target') == '_blank' || jQuery('#register_form_1').length > 0 || jQuery('#register_form_5').length > 0) {
		preventLeave = (jQuery(this).hasClass('skipLeave') || jQuery(this).attr('target') == '_blank') ? preventLeave : true;
		//console.log('open', jQuery(this));
	} else {
		//console.log('close', jQuery(this));
		//stop the normal action
		e.preventDefault();

		//save object and event
		lastClickedObject = jQuery(this);
		lastEventType = e.type

		//prevent actions
		jQuery('#leavePopup').removeClass("no-show");
		jQuery('#leavePopup .leavePopupContent').center();

		//make sure the second popup it's hidden
		jQuery('#leavePopup2').addClass("no-show");

		//hide leave form errors
		jQuery('form#fmLeavePage label.error').hide();
	}
	*/
}

function cancelPageLeave (e) {
	e.preventDefault();

	preventLeave = false;
	lastClickedObject = null;
	lastEventType = null;

	jQuery('#leavePopup').addClass("no-show");
	jQuery('#leavePopup2').addClass("no-show");
}

function continuePageLeave (e) {
	e.preventDefault();

	//hide first popup
	jQuery('#leavePopup').addClass("no-show");

	//show second popup
	//jQuery('#leavePopup2').removeClass("no-show");
	//jQuery('#leavePopup2 .leavePopupContent').center();

	preventLeave = false;

	if (lastEventType == "click" && lastClickedObject.attr('href') != '') {
		//load url
		//console.log('continue to ' + lastClickedObject.attr('href'));
		window.location.href = lastClickedObject.attr('href');
	} else if (lastEventType == "submit") {
		//submit the search form
		searchURL = 'https://prescriptionhope.com/?s=&submit=' + jQuery('form#searchform input#s').val();
		//console.log('continue to ' + searchURL);
		window.location.href = searchURL;
	}
}

function submitPageLeaveReason (e) {
	if (jQuery("#fmLeavePage").valid()) {
		//stop the default form action
		e.preventDefault();

		//submit the reason to the webservice
		//jQuery.post('_leave.php', jQuery("#fmLeavePage").serialize());

		jQuery.ajax({
			type: 'POST',
			url: '_leave.php',
			data: jQuery("#fmLeavePage").serialize(),
			async: false
		});

		//load the page that user intended to leave to
		if (lastClickedObject != null) {
			//continue the original click / submit when the user wanted to leave the page
			preventLeave = false;

			if (lastEventType == "click" && lastClickedObject.attr('href') != '') {
				//load url
				//console.log('continue to ' + lastClickedObject.attr('href'));
				window.location.href = lastClickedObject.attr('href');
			} else if (lastEventType == "submit") {
				//submit the search form
				searchURL = 'https://prescriptionhope.com/?s=&submit=' + jQuery('form#searchform input#s').val();
				//console.log('continue to ' + searchURL);
				window.location.href = searchURL;
			}
		}

		//hide popups
		preventLeave = true;
		jQuery('#leavePopup').addClass("no-show");
		jQuery('#leavePopup2').addClass("no-show");
	}
}

function preventEnrollmentSubmit () {
	jQuery('#submitPopup').removeClass("no-show");
	jQuery('#submitPopup .leavePopupContent').center();

	//make sure the second popup it's hidden
	jQuery('#submitPopup2').addClass("no-show");
}

function confirmEnrollmentSubmit (e) {
	e.preventDefault();

	//hide first popup
	jQuery('#submitPopup').addClass("no-show");

	//
	submitConfirmed = true;
	jQuery("input#bSubmit").trigger('click');
}

function cancelEnrollmentSubmit (e) {
	e.preventDefault();

	//hide first popup
	jQuery('#submitPopup').addClass("no-show");

	//show second popup
	jQuery('#submitPopup2').removeClass("no-show");
	jQuery('#submitPopup2 .leavePopupContent').center();
}

function resetEnrollmentForm (e) {
	e.preventDefault();

	//hide popups
	jQuery('#submitPopup').addClass("no-show");
	jQuery('#submitPopup2').addClass("no-show");

	//
	window.location.href = 'register.php';
}

function hideEnrollmentSubmitConfirmation (e) {
	e.preventDefault();

	//hide popups
	jQuery('#submitPopup').addClass("no-show");
	jQuery('#submitPopup2').addClass("no-show");
}

function updateHearAboutExtras (e) {
	//destroy previous possible extra elements
	jQuery('.p_hear_about_extras').remove();

	//get row element
	hearAboutRow = jQuery(this).parent();

	extra_elems = "";
	switch (jQuery(this).val()) {
		case "Insurance":
			//1 text box
			extra_elems = '<p class="form-row p_hear_about_extras"><label for="p_hear_about_1" class="no_width big">Insurance Company Name</label><br/><input type="text" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" class="w800 no-margin"></p>';
			break;

		case "Pharmacy":
			//1 text box
			extra_elems = '<p class="form-row p_hear_about_extras"><label for="p_hear_about_1" class="no_width big">Pharmacy Name</label><br/><input type="text" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" class="w800 no-margin"></p>';
			break;

		case "Previous Patient":
			//1 text box
			extra_elems = '<p class="form-row p_hear_about_extras"><label for="p_hear_about_1" class="no_width big">Please tell us what caused you to join the Prescription Hope program again.  Did you receive a mailing, email or some other reminder of the program?</label><br/><input type="text" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" class="w800 no-margin"></p>';
			break;

		case "Television":
			//1 text box
			extra_elems = '<p class="form-row p_hear_about_extras"><label for="p_hear_about_1" class="no_width big">What network were you watching when you noticed our commercial?</label><br/><input type="text" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" class="w800 no-margin"></p>';
			break;

		case "Internet":
			//1 text box
			extra_elems = '<p class="form-row p_hear_about_extras"><label for="p_hear_about_1" class="no_width big">Please provide additional details on how you found the Prescription Hope program online.</label><br/><input type="text" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" class="w800 no-margin"></p>';
			break;

		case "Other":
			//1 text box
			extra_elems = '<p class="form-row p_hear_about_extras"><label for="p_hear_about_1" class="no_width big">Please provide additional details about how you heard about us.</label><br/><input type="text" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" class="w800 no-margin" required></p>';
			break;

		case "Healthcare Provider":
			extra_elems = '<p class="form-row p_hear_about_extras"><label for="p_hear_about_1" class="no_width big">Healthcare Provider Name</label><br/><input type="text" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" class="w800 no-margin"></p>';
			extra_elems += '<p class="form-row p_hear_about_extras"><label for="p_hear_about_2" class="no_width big">Practice your healthcare provider treats you from</label><br/><input type="text" name="p_hear_about_2" value="' + hear_about_extra_2_value + '" class="w800 no-margin"></p>';
			break;

		case "Referral By Current Member of the Prescription Hope Program":
			//3 text boxes
			extra_elems = '<p class="form-row p_hear_about_extras"><label for="p_hear_about_1" class="no_width big">First Name of the person who referred you</label><br/><input type="text" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" class="w800 no-margin"></p>';
			extra_elems += '<p class="form-row p_hear_about_extras"><label for="p_hear_about_2" class="no_width big">Last Name of the person who referred you</label><br/><input type="text" name="p_hear_about_2" value="' + hear_about_extra_2_value + '" class="w800 no-margin"></p>';
			extra_elems += '<p class="form-row p_hear_about_extras"><label for="p_hear_about_3" class="no_width big">Phone Number of the person who referred you, we would like to thank them for referring you.</label><br/><input type="text" name="p_hear_about_3" value="' + hear_about_extra_3_value + '" class="w800 no-margin"></p>';
			break;

		case "Social Media":
			//1 drop box
			extra_elems = '<p class="form-row p_hear_about_extras"><select name="p_hear_about_1" class="w800 no-margin"><option value="">Select ...</option><option value="Facebook">Facebook</option><option value="Twitter">Twitter</option><option value="LinkedIn">LinkedIn</option><option value="Instagram">Instagram</option></select></p>';
			break;

		case "Paper Mailing":
			//1 drop down + 1 text box
			extra_elems = '<p class="form-row p_hear_about_extras"><select name="p_hear_about_1" class="w800 no-margin" onChange=\'onPaperMailingChange(jQuery(this), "' + hear_about_extra_2_value + '");\'><option value="">Select ...</option><option value="Letter">Letter</option><option value="Postcard">Postcard</option></select></p>';
			break;
	}

	//add new form elements
	hearAboutRow.after(extra_elems);

	//
	if (jQuery(this).val() == "Social Media" || jQuery(this).val() == "Paper Mailing") {
		jQuery('select[name="p_hear_about_1"]').val(hear_about_extra_1_value);

		if (jQuery(this).val() == "Paper Mailing" && hear_about_extra_1_value != "") {
			onPaperMailingChange(jQuery('select[name="p_hear_about_1"]'), hear_about_extra_2_value);
		}
	}

	hear_about_extra_1_value = "";
	hear_about_extra_2_value = "";
	hear_about_extra_3_value = "";
}

function onPaperMailingChange (elem, elem_value) {
	if(elem.val() != "" && jQuery('input[name="p_hear_about_2"]').length == 0) {
		extra_elems = '<p class="form-row p_hear_about_extras"><label for="p_hear_about_2" class="no_width big">Is there a code in the bottom-left of the postcard or letter?  If so, please enter it here.</label><br/><input type="text" name="p_hear_about_2" value="' + elem_value + '" class="w800 no-margin"></p>';
		elem.parent().after(extra_elems);
	}
}