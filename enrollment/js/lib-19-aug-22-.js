// Listen for resize changes
window.addEventListener("resize", function () {
    jQuery(".tooltip-new").remove();
}, false);

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
    jQuery(":input[preload]").each(function () {
        //input objects
        if (jQuery(this).is("input") && jQuery(this).attr('type') == 'radio' && jQuery(this).attr('preload') != '') {
            jQuery("input[name=" + jQuery(this).attr('name') + "][value=" + jQuery(this).attr('preload') + "]").attr('checked', 'checked');

            if (jQuery(this).attr('name') == 'p_medicare' || jQuery(this).attr('name') == 'p_medicaid' || jQuery(this).attr('name') == 'p_lis') {
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
 * A helper for jQuery Validation to place the errors related to radio buttons or checkboxes after the last input's label
 *
 */
function jQueryValidation_ShowErrors(error, element) {
    switch (element.attr('type')) {
        case 'checkbox':
            if (error.attr("for") == 'p_payment_agreement' || error.attr("for") == 'p_service_agreement' || error.attr("for") == 'p_guaranty_agreement' || error.attr("for") == 'p_acknowledge_agreement') {
                error.addClass("nopad-error");
            }

            //error.insertAfter(jQuery("label[for=" + element.attr("id") + "]").last());
            break;

        case 'radio':
            if (error.attr("for") != 'p_is_minor' && error.attr("for") != 'p_gender' && error.attr("for") != 'p_payment_method') {
                error.addClass("radio-error");
            }

            if (error.attr("for") != 'p_payment_method') {
                //error.insertAfter(jQuery("label[for=" + jQuery("input[name=" + element.attr("name") + "]").last().attr('id') + "]").last());
            } else {
                //error.insertAfter(jQuery("img[class=payment_images]").last());
                error.addClass("nopad-error");
                error.attr("style", "display: block; width: auto;");
                //error.insertAfter(jQuery("label[for='p_payment_method']"));
            }

            break;

        default:
            if (error.attr("for") == 'p_hear_about' || error.attr("for") == 'p_hear_about_1' || error.attr("for") == 'leave_reason') {
                //error.addClass("nopad-error");
            }

            error.addClass("invalid-field");
            error.insertAfter(element);
    }

    refreshRadioGroupsValidationIcons();
}

function jQueryValidation_Highlight(element) {
    //if (!jQuery(element).hasClass('dr-data')) {
    switch (jQuery(element).attr('type')) {
        case 'checkbox':
            jQuery(element).addClass("error");
            if (jQuery(element).attr('name').substr(0, 6) != 'p_has_') {
                jQuery(element).parent().parent().removeClass('checkbox-with-label-correct2');
                jQuery(element).parent().parent().removeClass('checkbox-with-label-correct3');

                if (jQuery(element).attr('name') == 'p_income_zero') {
                    jQuery(element).parent().parent().addClass('checkbox-with-label-error3');
                } else {
                    jQuery(element).parent().parent().addClass('checkbox-with-label-error2');
                }
            }
            break;

        case 'radio':
            jQuery(element).addClass("error");

            jQuery("#valid-" + jQuery(element).attr("name")).remove();

            radio_bt = jQuery("input[name=" + jQuery(element).attr("name") + "]").last();
            label_elem = jQuery('label[for="' + radio_bt.attr('id') + '"]').last();
            //last_radio_elem = jQuery("label[for=" + jQuery(last_radio).attr("id") + "]").last();

            pos = jQuery(label_elem).position();
            //error_left = last_radio.outerWidth() + 10;
            error_left = ((pos.left - 170) > 0) ? pos.left - 170 : 0;
            error_left = (error_left < 5 && jQuery(window).width() <= 1024) ? 5 : error_left;

            error_elem = jQuery("<div id='valid-" + jQuery(element).attr("name") + "' class='radio-with-error'>This field is required</div>")
                    //.css("top", (pos.top - 1) + "px")
                    //.css("top", "17%")
                    //.css("left", error_left + "px")
                    //.css("left", "-185px")
                    .fadeIn("fast");

            if (jQuery(window).width() <= 1024) {
                //error_elem.css('left', '-31px !important').css("padding-top", "25px");
            }

            //jQuery("#register_form").append(error_elem);
            //label_elem.append(error_elem);
            jQuery(error_elem).insertAfter(label_elem);

            break;

        default:
            jQuery(element).removeClass("correct");
            jQuery(element).removeClass("field-error-only");
            jQuery(element).addClass("error");
            if (jQuery(element).hasClass('input_zero')) {
                jQuery('label.' + jQuery(element).attr('id')).remove();
            }
    }
    //}

    refreshRadioGroupsValidationIcons();
}

function jQueryValidation_Unhighlight(element) {
    //if (!jQuery(element).hasClass('dr-data')) {
    switch (jQuery(element).attr('type')) {
        case 'checkbox':
            console.log('Correct checkbox.');
            jQuery(element).removeClass("error");

            if (jQuery(element).attr('name').substr(0, 6) != 'p_has_') {
                jQuery(element).parent().parent().removeClass('checkbox-with-label-error2');
                jQuery(element).parent().parent().removeClass('checkbox-with-label-error3');

                if (jQuery(element).attr('name') == 'p_income_zero') {
                    jQuery(element).parent().parent().addClass('checkbox-with-label-correct3');
                } else {
                    jQuery(element).parent().parent().addClass('checkbox-with-label-correct2');
                }
            }
            break;

        case 'radio':
            jQuery(element).removeClass("error");
            jQuery("#valid-" + jQuery(element).attr("name")).remove();

            radio_bt = jQuery("input[name=" + jQuery(element).attr("name") + "]").first();
            label_elem = jQuery('label[for="' + radio_bt.attr('id') + '"]').first();
            //last_radio_elem = jQuery("label[for=" + jQuery(last_radio).attr("id") + "]").last();
            pos = jQuery(label_elem).position();
            //error_left = last_radio.outerWidth() + 10;
            error_left = ((pos.left - 20) > 0) ? pos.left - 20 : 0;
            error_elem = jQuery("<div id='valid-" + jQuery(element).attr("name") + "' class='radio-correct'>&nbsp;</div>")
                    //.css("top", (pos.top - 1) + "px")
                    .css("top", "-2px")
                    //.css("left", error_left + "px")
                    .css("left", "-35px")
                    .fadeIn("fast");

            //jQuery("#register_form").append(error_elem);
            label_elem.append(error_elem);
            break;

        default:
            if (jQuery(element).hasClass('input_zero') && jQuery('label.' + jQuery(element).attr('id')).length > 0) {

            } else {
                jQuery(element).removeClass("error");
                jQuery(element).removeClass("field-error-only");
                jQuery(element).removeClass("correct");
                if (jQuery(element).val() != "") {
                    jQuery(element).addClass("correct");
                    jQuery('label.error[for="' + jQuery(element).attr('id') + '"]').remove();
                    jQuery('label.error[for="' + jQuery(element).attr('name') + '"]').remove();
                }
            }
    }
    //}

    refreshRadioGroupsValidationIcons();
}

/*
 *
 * Scrolls up the page to the first invalid element
 *
 */
function scrollToInvalidFormElements() {
    first_invalid_element = jQuery('input.error, select.error, textarea.error').eq(0);

    //if (typeof first_invalid_element.position() != 'undefined') {
    if (first_invalid_element) {
        //jQuery(window).scrollTop(first_invalid_element.position().top - 250);
        first_invalid_element.focus();
    }
}

/*
 *
 * Activates the selected payment method set of inputs
 *
 */
function showSelectedPaymentMethods() {
    //jQuery('#payment_cc').hide();
    //jQuery('#payment_ach').hide();

    //show_payment_method = jQuery('input[name=p_payment_method]').val();
    //jQuery('#payment_' + show_payment_method).show();
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
        $('.patient_parent_profile').find(':input').addClass('patient-enroll-progress-2').attr('data-step-enroll', 2); //Code added by vinod
        $('input[name="p_parent_first_name"], input[name="p_parent_middle_initial"], input[name="p_parent_last_name"], input[name="p_parent_phone"]').removeClass('correct');
        $('input[name="p_parent_first_name"], input[name="p_parent_middle_initial"], input[name="p_parent_last_name"], input[name="p_parent_phone"]').prev('label.placeHolder').removeClass('active');
    }

    if (jQuery('input[name=p_is_minor]:checked').val() == 0) {
        //jQuery('.patient_profile').show();

        //empty parent fields
        jQuery('input[name="p_parent_first_name"]').val('');
        jQuery('input[name="p_parent_middle_initial"]').val('');
        jQuery('input[name="p_parent_last_name"]').val('');
        jQuery('input[name="p_parent_phone"]').val('');

        $('.patient_parent_profile').find(':input').removeClass('patient-enroll-progress-2').attr('data-step-enroll', ''); //Code added by vinod		
    }
}

/*
 *
 * Activates the entire income section
 *
 */
function showIncomeSection() {
    if (jQuery('input[name="p_has_income"]:checked').val() == 1) {
        // mandatory sign show
        jQuery('label[for="p_income_file_tax_return"]').find('span.red').show();
        //add jquery validation class for tax return field

        jQuery('#patient_income_section').removeClass('hidden');
        jQuery('.patient_income_yes_only').removeClass('hidden');

        //see if any incomes where filled
        if (jQuery('input[name="p_income_salary"]').val() != '' && jQuery('input[name="p_income_salary"]').val().replace(',', '') > 0) {
            jQuery('input[name="p_has_salary"]').prop('checked', true);
            jQuery('div.p_income_salary').removeClass('hidden');
        }
        if (jQuery('input[name="p_income_unemployment"]').val() != '' && jQuery('input[name="p_income_unemployment"]').val().replace(',', '') > 0) {
            jQuery('input[name="p_has_unemployment"]').prop('checked', true).trigger('change');
            jQuery('div.p_income_unemployment').removeClass('hidden');
        }
        if (jQuery('input[name="p_income_pension"]').val() != '' && jQuery('input[name="p_income_pension"]').val().replace(',', '') > 0) {
            jQuery('input[name="p_has_pension"]').prop('checked', true).trigger('change');
            jQuery('div.p_income_pension').removeClass('hidden');
        }
        if (jQuery('input[name="p_income_annuity"]').val() != '' && jQuery('input[name="p_income_annuity"]').val().replace(',', '') > 0) {
            jQuery('input[name="p_has_annuity"]').prop('checked', true).trigger('change');
            jQuery('div.p_income_annuity').removeClass('hidden');
        }
        if (jQuery('input[name="p_income_ss_retirement"]').val() != '' && jQuery('input[name="p_income_ss_retirement"]').val().replace(',', '') > 0) {
            jQuery('input[name="p_has_ss_retirement"]').prop('checked', true).trigger('change');
            jQuery('div.p_income_ss_retirement').removeClass('hidden');
        }
        if (jQuery('input[name="p_income_ss_disability"]').val() != '' && jQuery('input[name="p_income_ss_disability"]').val().replace(',', '') > 0) {
            jQuery('input[name="p_has_ss_disability"]').prop('checked', true).trigger('change');
            jQuery('div.p_income_ss_disability').removeClass('hidden');
        }
        $('#p_employment_status').addClass('patient-enroll-progress-3').attr('data-step-enroll', 3); //Added by vinod to add class and set attr value by 3
        $('#p_employment_status').addClass('patient-enroll-progress-3').attr('data-step-enroll', 3); //Added by vinod to add class and set attr value by 3
        $('#p_employment_status').siblings('label.placeHolder').removeClass('active');
        var pMedicaidYes = $("label[for='p_medicaid_yes']");
        $('<input type="hidden" data-f-count="2" data-fl_1="p_medicaid_yes" data-fl_2="p_medicaid_no" class="patient-enroll-progress-3 income-information" data-step-enroll="3">\
').insertBefore(pMedicaidYes);
        var pMedicareYes = $("label[for='p_medicare_yes']");
        $('<input type="hidden" data-f-count="2" data-fl_1="p_medicare_yes" data-fl_2="p_medicare_no" class="patient-enroll-progress-3 income-information" data-step-enroll="3">\
').insertBefore(pMedicareYes);

        var pDisabledStatusYes = $("label[for='p_disabled_status_yes']");
        $('<input type="hidden" data-f-count="2" data-fl_1="p_disabled_status_yes" data-fl_2="p_disabled_status_no" class="patient-enroll-progress-3 income-information" data-step-enroll="3">\
').insertBefore(pDisabledStatusYes);

        // if( $('#p_employment_status').val() === '') {
        // 	$('#p_employment_status').addClass('error');
        // } 


        updateTotalAnnualIncome();
    }

    if (jQuery('input[name="p_has_income"]:checked').val() == 0) {
        jQuery('#patient_income_section').addClass('hidden');
        $("#valid-p_income_file_tax_return").hide();
        //empty all income fields
        jQuery('select[name="p_employment_status"]').val('');
        jQuery('select[name="p_employment_status"]').trigger('blur');
        //jQuery('select[name="p_married"]').val('');
        jQuery('select[name="p_married"]').trigger('blur');
        //jQuery('select[name="p_household"]').val('');
        jQuery('select[name="p_household"]').trigger('blur');
        //jQuery('input[name="p_income_file_tax_return"]').prop('checked', false);
        //jQuery('input[name="p_uscitizen"]').prop('checked', false);
        jQuery('input[name="p_medicare"]').prop('checked', false);
        jQuery('input[name="p_medicare_part_d"]').prop('checked', false);
        jQuery('input[name="p_medicaid"]').prop('checked', false);
        jQuery('input[name="p_medicaid_denial"]').prop('checked', false);
        jQuery('input[name="p_lis"]').prop('checked', false);
        jQuery('input[name="p_lis_denial"]').prop('checked', false);
        jQuery('input[name="p_disabled_status"]').prop('checked', false);
        jQuery('input[name="p_has_salary"]').prop('checked', false);
        jQuery('input[name="p_income_salary"]').val('');
        jQuery('input[name="p_has_unemployment"]').prop('checked', false);
        jQuery('input[name="p_income_unemployment"]').val('');
        jQuery('input[name="p_has_pension"]').prop('checked', false);
        jQuery('input[name="p_income_pension"]').val('');
        jQuery('input[name="p_has_annuity"]').prop('checked', false);
        jQuery('input[name="p_income_annuity"]').val('');
        jQuery('input[name="p_has_ss_retirement"]').prop('checked', false);
        jQuery('input[name="p_income_ss_retirement"]').val('');
        jQuery('input[name="p_has_ss_disability"]').prop('checked', false);
        jQuery('input[name="p_income_ss_disability"]').val('');
        jQuery('input[name="p_income_annual_income"]').val('');

        //remove any error/confirmation icons
        jQuery('.patient_income_yes_only input, .patient_income_yes_only select').removeClass('correct');
        jQuery('.patient_income_yes_only input, .patient_income_yes_only select').removeClass('error');
        jQuery('.patient_income_yes_only .checkbox-with-label-correct2').remove();
        jQuery('.patient_income_yes_only .checkbox-with-label-correct3').remove();
        jQuery('.patient_income_yes_only .checkbox-with-label-error2').remove();
        jQuery('.patient_income_yes_only .checkbox-with-label-error3').remove();
        jQuery('.patient_income_yes_only .radio-correct').remove();
        jQuery('.patient_income_yes_only .radio-with-error').remove();
        jQuery('.patient_income_yes_only input[type=radio]').each(function (index, element) {
            jQuery('#valid-' + jQuery(element).attr('name')).remove();
        });
    }

    if (jQuery('input[name="p_has_income"]:checked').val() == 0) {
        // mandatory sign hide
        jQuery('label[for="p_income_file_tax_return"]').find('span.red').hide();
        $("#valid-p_income_file_tax_return").hide();
        jQuery('#patient_income_section').removeClass('hidden');
        jQuery('.patient_income_yes_only').addClass('hidden');
        $('#p_employment_status').removeClass('patient-enroll-progress-3').attr('data-step-enroll', ''); //Added by vinod to remove class and blank attr

        $('.income-information').remove();
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
        //alert(inputObjName); 
        input2ndLevelName = '';
        input3rdLevelName = '';
        input4thLevelName = '';
        input5thLevelName = '';
        switch (inputObjName) {
            case 'p_medicare':
                input2ndLevelName = 'p_medicare_part_d';
                input3rdLevelName = 'p_medicare_part_d_3rd';
                input4thLevelName = 'p_medicare_part_d_4th';
                input5thLevelName = 'p_coveragegapyes_2nd';
                break;
            case 'p_medicaid':
                input2ndLevelName = 'p_medicaid_denial';
                break;
            case 'p_lis':
                input2ndLevelName = 'p_lis_denial';
                break;
            case 'p_medicare_part_d':
                input2ndLevelName = 'p_coveragegapyes';
                input3rdLevelName = 'p_medicare_part_d_3rd';
                input4thLevelName = 'p_medicare_part_d_4th';
                input5thLevelName = 'p_coveragegapyes_2nd';
                input6thLevelName = 'p_medicare_part_d_2nd';
                break;
            case 'p_coveragegapyes':
                //alert(inputObjName);
                console.log('Bunny 1');
                input2ndLevelName = 'p_pocketmoney';
                input4thLevelName = 'p_medicare_part_d_4th';
                break;
        }
        jQuery('#valid-' + input2ndLevelName).remove();
        jQuery('#error-' + input2ndLevelName).remove();

        if (jQuery('input[name=' + inputObjName + ']:checked').val() == 1) {
            console.log('Bunny 3');
            jQuery('.' + inputObjName + '_2nd').show();
            if (inputObjName == 'p_medicare') {
                jQuery('.' + input2ndLevelName + '_2nd').hide();
                jQuery('.' + input3rdLevelName).hide();
                jQuery('.' + input4thLevelName).hide();
            }
            if (inputObjName == 'p_medicare_part_d') {
                jQuery('.' + input6thLevelName).show();
                jQuery('.' + input5thLevelName).show();
                console.log('Shre 1');
            }

            if (jQuery('input[name=p_medicaid]:checked').val() == 1) {
                console.log('P mecicaed checked');
                jQuery('.p_medicaid_2nd').show();
            }

        } else {
            if (jQuery('input[name=p_lis]:checked').val() == 1) {
                jQuery('.p_lis_2nd').show();
            } else {
                jQuery('.p_lis_2nd').hide();
                jQuery('input[name=p_lis_denial]').attr('checked', false);

                data_type = 'patient';
                fieldName = 'p_lis_denial';
                fieldVal = 0;
                jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
                        .done(function (response) {
                            console.log('LIS Deniel response:', response);
                        });
            }
            if (inputObjName == 'p_medicare') {
                jQuery('.' + input2ndLevelName + '_2nd').hide();
                jQuery('.' + input3rdLevelName).hide();
                jQuery('.' + input4thLevelName).hide();
                jQuery('.' + input5thLevelName).hide();
                jQuery('#p_medicare_part_d_no').prop('checked', true);
                jQuery('#p_coveragegapno').prop('checked', true);
                jQuery("#p_pocketmoney").val('');
                jQuery("#p_pocketmoney").removeClass('correct not-empty');

                data_type = 'patient';
                fieldName = 'p_medicare_part_d';
                fieldVal = 0;
                jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
                        .done(function (response) {
                            //console.log(response);
                        });


                data_type = 'patient';
                fieldName = 'p_coveragegapyes';
                fieldVal = 0;
                jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
                        .done(function (response) {
                            //console.log(response);
                        });


                data_type = 'patient';
                fieldName = 'p_pocketmoney';
                fieldVal = 0.00;
                jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
                        .done(function (response) {
                            //console.log(response);
                        });

            }
            if (inputObjName == 'p_medicare_part_d') {
                //alert('in else p_medicare_part_d condiition');
                console.log('Shre 2');
                jQuery('.' + input3rdLevelName).hide();
                jQuery('.' + input4thLevelName).hide();
                jQuery('.' + input5thLevelName).hide();
                jQuery('.' + input6thLevelName).hide();
                jQuery('#p_coveragegapno').prop('checked', true);
                jQuery("#p_pocketmoney").val('');
                jQuery("#p_pocketmoney").removeClass('correct not-empty');

                data_type = 'patient';
                fieldName = 'p_coveragegapyes';
                fieldVal = 0;
                jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
                        .done(function (response) {
                            //console.log(response);
                        });


                data_type = 'patient';
                fieldName = 'p_pocketmoney';
                fieldVal = 0.00;
                jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
                        .done(function (response) {
                            //console.log(response);
                        });

            }

            if (inputObjName == 'p_coveragegapyes') {
                console.log('Bunny 2');
                jQuery('.' + input4thLevelName).hide();
                //jQuery("#p_pocketmoney").val('');
                //jQuery("#p_pocketmoney").removeClass('correct not-empty');

                // data_type = 'patient';
                // fieldName = 'p_pocketmoney';
                // fieldVal = 0.00;
                // jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
                // .done(function(response) {
                // 	//console.log(response);
                // });
            }

            if (jQuery('input[name=p_medicaid]:checked').val() == 0) {
                console.log('in else P mecicaed checked');
                jQuery('.p_medicaid_2nd').hide();
                jQuery('.p_medicaid_2nd').attr('checked', false);
            }

            if (jQuery('input[name=p_medicare]:checked').val() == 1) {
                jQuery('.p_medicare_2nd').show();
            } else {
                jQuery('.p_medicare_2nd').hide();
                jQuery('.p_medicare_part_d_2nd').hide();
                jQuery('.p_coveragegapyes_2nd').hide();
                jQuery('.p_medicare_2nd').attr('checked', false);
                jQuery('.p_covergage_gap_2').attr('checked', false);
            }

        }
    } else {
        if (jQuery('input[name=p_medicare]:checked').val() == 1) {
            jQuery('.p_medicare_2nd').show();
        } else {
            jQuery('.p_medicare_2nd').hide();
            jQuery('.p_medicare_part_d_2nd').hide();
            jQuery('.p_coveragegapyes_2nd').hide();
            jQuery('.p_medicare_2nd').attr('checked', false);
            jQuery('.p_covergage_gap_2').attr('checked', false);
        }

        if (jQuery('input[name=p_medicaid]:checked').val() == 1) {
            console.log('P mecicaed checked');
            jQuery('.p_medicaid_2nd').show();
        } else {
            console.log('in else P mecicaed checked');
            jQuery('.p_medicaid_2nd').hide();
            jQuery('.p_medicaid_2nd').attr('checked', false);
        }

        if (jQuery('input[name=p_lis]:checked').val() == 1) {
            jQuery('.p_lis_2nd').show();
        } else {
            jQuery('.p_lis_2nd').hide();
            jQuery('.p_lis_2nd').attr('checked', false);
        }

        if (jQuery('input[name=p_lis]:checked').val() == 1) {
            jQuery('.p_lis_2nd').show();
        } else {
            jQuery('.p_lis_2nd').hide();
            jQuery('.p_lis_2nd').attr('checked', false);
        }

        if (jQuery('input[name=p_coveragegapyes]:checked').val() == 1 && jQuery('input[name=p_medicare]:checked').val() == 1) {
            jQuery('.p_coveragegapyes_2nd').show();
        } else {
            jQuery('.p_coveragegapyes_2nd').hide();
        }
    }
}

function refreshRadioGroupsValidationIcons() {
    jQuery('input[type="radio"]').each(function (index, elem) {
        var elName = jQuery(elem).attr('name');
        var elRadio = jQuery("input[name=" + elName + "]").first();
        //var elPos = jQuery(elRadio).position();
        var elPos = jQuery('label[for="' + elRadio.attr('id') + '"]').first().position();

        /*
         if (elRadio.is(':visible')) {
         jQuery('#valid-' + elName).css("top", (elPos.top - 1) + "px");
         } else {
         jQuery('#valid-' + elName).remove();
         }
         */
    });
    // jQuery('.doctors_dropdown').each(function(index, elem){ 
    //       //var elName = jQuery(elem).attr('name');
    //    var parentElnumber = jQuery(this).attr('name').match(/\d+/);
    //    var medsName = $(this).find(":selected").text();
    //    var isValid = false; 
    //    console.log('parentElName :',parentElnumber[0]);
    //    if(medsName != '') {
    //           isValid = true;
    //        } else {
    //             $("#mb"+parentElnumber[0]).addClass('in active');
    //        }

    //   });

}

/*
 *
 * 2nd Step - auto-uncheck zero income checkbox if some income was entered
 *
 */
function updateZeroIncome() {
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
function updateTotalAnnualIncome() {
    var income1 = 0;
    var income2 = 0;
    var income3 = 0;
    var income4 = 0;
    var income5 = 0;
    var income6 = 0;
    income1 = (jQuery('input[name=p_income_salary]').val() != '') ? parseFloat(jQuery('input[name=p_income_salary]').val().replace(/[^0-9.]/g, '')) : 0;
    income2 = (jQuery('input[name=p_income_unemployment]').val() != '') ? parseFloat(jQuery('input[name=p_income_unemployment]').val().replace(/[^0-9.]/g, '')) : 0;
    income3 = (jQuery('input[name=p_income_pension]').val() != '') ? parseFloat(jQuery('input[name=p_income_pension]').val().replace(/[^0-9.]/g, '')) : 0;
    income4 = (jQuery('input[name=p_income_annuity]').val() != '') ? parseFloat(jQuery('input[name=p_income_annuity]').val().replace(/[^0-9.]/g, '')) : 0;
    income5 = (jQuery('input[name=p_income_ss_retirement]').val() != '') ? parseFloat(jQuery('input[name=p_income_ss_retirement]').val().replace(/[^0-9.]/g, '')) : 0;
    income6 = (jQuery('input[name=p_income_ss_disability]').val() != '') ? parseFloat(jQuery('input[name=p_income_ss_disability]').val().replace(/[^0-9.]/g, '')) : 0;
    var totalIncome = ((parseFloat(income1) + parseFloat(income2) + parseFloat(income3) + parseFloat(income4) + parseFloat(income5) + parseFloat(income6)) * 12).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2, useGrouping: true});
    if (totalIncome != 'NaN') {
        jQuery('input[name=p_income_annual_income]').val(totalIncome);
    } else {
        jQuery('input[name=p_income_annual_income]').val('0.00');
    }

//    jQuery('input[name=p_income_annual_income]').val(income * 12).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
}

/*
 *
 * 3rd Step - Show new doctor form
 *
 */
function showAddDoctorForm(line) {
    //hide the rest of the medication rows
    for (i = line + 1; i < 10; i++) {
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
				<label for="dr_state">State</label>\
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
					<option value="PR">Puerto Rico</option>\
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
    jQuery("input[name='dr_zip']").each(function (index, elem) {
        jQuery(elem).mask('99999', {clearIfNotMatch: true});
    });
    //
    jQuery("input[name='dr_phone']").each(function (index, elem) {
        jQuery(elem).mask('999-999-9999', {clearIfNotMatch: true});
    });
    //
    jQuery("input[name='dr_fax']").each(function (index, elem) {
        jQuery(elem).mask('999-999-9999', {clearIfNotMatch: true});
    });

    //buttons actions
    jQuery('#bAddNewDoctor').click(AddNewDoctor);
    jQuery('#bCancelAddNewDoctor').click(hideAddDoctorForm);

    resetAddMoreMedi();
}

/*
 *
 * 3rd Step - Hide the add new doctor form
 *
 */
function AddNewDoctor(e) {
    e.preventDefault();

    valid_form = true;
    first_invalid_element = false;

    //remove existent errors
    jQuery('label.error').remove();

    //validate form
    jQuery('.dr-required').each(function (index, elem) {
        if (jQuery(elem).val() == '') {
            if (first_invalid_element === false) {
                first_invalid_element = jQuery(elem);
            }

            //add error label
            errorLabel = jQuery('<label>').attr('for', jQuery(elem).id).addClass('error').addClass('invalid-field').text('This field is required');
            errorLabel.insertAfter(jQuery(elem));

            valid_form = false;
        }
    })
    //ascii validation
    jQuery('input').each(function (index, elem) {
        elemName = jQuery(elem).attr('name');
        if (typeof elemName != 'undefined' && jQuery(elem).attr('name').substr(0, 3) == 'dr_') {
            if (!isAscii(jQuery(elem).val())) {
                if (first_invalid_element === false) {
                    first_invalid_element = jQuery(elem);
                }

                //add error label
                errorLabel = jQuery('<label>').attr('for', jQuery(elem).id).addClass('error').addClass('invalid-field').text('Please insert only alphanumeric characters.');
                errorLabel.insertAfter(jQuery(elem));

                valid_form = false;
            }
        }
    })

    //validate zip code
    zip_code_elem = jQuery('input[name="dr_zip"]');
    if (!(/^\s*\d{5}\s*$/.test(zip_code_elem.val()))) {
        //add error label
        errorLabel = jQuery('<label>').attr('for', zip_code_elem.id).addClass('error').addClass('invalid-field').text('Invalid zip code');
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
        jQuery('.doctors_dropdown:visible').last().find('option').each(function () {
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
function hideAddDoctorForm(e) {
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

function AddANewDoctor(e) {
    //new_dr_id = parseInt(jQuery('input#dr_id').val()) + 1;
    new_dr_id = jQuery('input.doctor-fields').length + 1;

    //reset dr form
    jQuery('.dr-data').val('');
    jQuery('input#dr_id').val(new_dr_id);

    //remove errors and green checks
    jQuery('.dr-data').removeClass('error').removeClass('field-error-only').removeClass('correct');
    jQuery('.dr-data').each(function (index, elem) {
        jQuery(elem).parent().find('label.error').remove();
    });
}


function ReorderDocSpecifiers() {
    var i = 1;
    jQuery('.dr-form .doctor-no-field').each(function () {
        jQuery(this).html('Healthcare Provider ' + i + ':');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-fname-field input').each(function () {
        jQuery(this).attr('name', 'doctor_first_name[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-lname-field input').each(function () {
        jQuery(this).attr('name', 'doctor_last_name[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-facility-field input').each(function () {
        jQuery(this).attr('name', 'doctor_facility[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-address-field input').each(function () {
        jQuery(this).attr('name', 'doctor_address[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-address2-field input').each(function () {
        jQuery(this).attr('name', 'doctor_address2[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-city-field input').each(function () {
        jQuery(this).attr('name', 'doctor_city[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-state-field input').each(function () {
        jQuery(this).attr('name', 'doctor_state[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-zip-field input').each(function () {
        jQuery(this).attr('name', 'doctor_zip[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-phone-field input').each(function () {
        jQuery(this).attr('name', 'doctor_phone[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('.dr-form .doctor-fax-field input').each(function () {
        jQuery(this).attr('name', 'doctor_fax[' + i + ']');
        i++;
    });
}

function AddNewDoctorForm(e) {
    // Reorder doctors block
    ReorderDocSpecifiers();
    //debugger;
    var new_dr_id = jQuery('input.doctor-fields').length + 1;
    liCount = 1;
    ulCount = 1;
    rowNumber = 3;
    var is_empty = false;
    jQuery('#dr_err').remove();
    var prev_eq = (new_dr_id >= 2) ? new_dr_id - 2 : 0;
    jQuery('.dr-required').each(function () {
        if (jQuery(this).val() == '') {
            is_empty = true;
        }
    });

    if (is_empty) {
        jQuery('#doctors_forms_list').append('<span id="dr_err" class="error">Please fill complete information of Healthcare Provider ' + (new_dr_id - parseInt(1)) + '</span>');
        return false;
    }
    //newDrForm = jQuery('.dr-form').eq(0).clone();
    $("#doctors_forms_list .dr-form").removeClass("active");
    var cloneMed = cg_medication_form_dumy_html;
//    var cloneMed = jQuery('.cg_medication_form_dumy').html();
    var addmore_provider_html = jQuery('.cg_addmore_provider_wrap_dumy').html();

    newDrForm = jQuery('<div old-id="doctor_form" role="tabpanel" class="dr-form tab-pane fade in active" id="pb' + new_dr_id + '">\<div class="remove-div-1"><a href="javascript:void(0);" class="remove_block" id="remove_provider_pb' + new_dr_id + '">X Remove Provider</a></div>\
                                                                    <div class="col-sm-7 card-form">\
                                                                        <div>\
										<div class="form-group">\
											<div class="full-width doctor-fname-field"><input autocomplete="nope" type="text" name="doctor_first_name[999]" value="" class="doctor_first_name-' + new_dr_id + ' dr-required dr-data doctor-fields LoNotSensitive dyn" placeholder="Healthcare Provider First Name *" title="Healthcare Provider First Name *"></div>\
										</div>\
										<div class="form-group">\
											<div class="full-width doctor-lname-field"><input autocomplete="nope" type="text" name="doctor_last_name[999]" value="" class="doctor_last_name-' + new_dr_id + ' dr-required dr-data doctor-lname-fields LoNotSensitive dyn" placeholder="Healthcare Provider Last Name *" title="Healthcare Provider Last Name *"></div>\
										</div>\
									</div>\
									<div class="clear"></div>\
									<div>\
										<div class="">\
											<div class="full-width doctor-facility-field"><input autocomplete="nope" type="text" name="doctor_facility[999]" value="" class="doctor_facility-' + new_dr_id + ' dr-data LoNotSensitive" placeholder="Facility Name" title="Facility Name"></div>\
										</div>\
										<div class="">\
											<div class="full-width doctor-address-field"><input autocomplete="nope" type="text" name="doctor_address[999]" value="" class="doctor_address-' + new_dr_id + ' dr-required dr-data dr-address LoNotSensitive dyn" placeholder="Address *" title="Address *" rel="Some health care providers have multiple locations they work from, please provide the address for the location you visit your health care provider at."></div>\
										</div>\
									</div>\
									<div class="clear"></div>\
									<div>\
										<div class="form-group">\
											<div class="full-width doctor-address2-field"><input autocomplete="nope" type="text" name="doctor_address2[999]" value="" class="doctor_address2-' + new_dr_id + ' dr-data LoNotSensitive" placeholder="Suite Number" title="Suite Number"></div>\
										</div>\
										<div class="form-group">\
											<div class="full-width doctor-city-field"><input autocomplete="nope" type="text" name="doctor_city[999]" value="" class="doctor_city-' + new_dr_id + ' dr-required dr-data LoNotSensitive dyn" placeholder="City *" title="City *"></div>\
										</div>\
									</div>\
									<div class="clear"></div>\
									<div>\
										<div class="half-width doctor-state-field">\
										<div class="jvFloat patient-select-cntntr doctor_state_wrapper">\
										<label class="placeHolder" for="doctor_state[' + new_dr_id + ']">State <span class="red">*</span></label> \
											<select name="doctor_state[999]" class="doctor_state-' + new_dr_id + ' dr-required dr-data full-width LoNotSensitive dyn" placeholder="State *" title="State *">\
												<option value="" selected="selected"></option>\
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
												<option value="PR">Puerto Rico</option>\
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
											</select>\
											</div>\
										</div>\
										<div class="half-width doctor-zip-field"><input autocomplete="nope" type="text" name="doctor_zip[999]" value="" maxlength="5" class="doctor_zip-' + new_dr_id + ' dr-required dr-data dr-zip LoNotSensitive dyn" placeholder="Zip Code *" title="Zip Code *"></div>\
									</div>\
									<div class="clear"></div>\
									<div>\
										<div class="half-width doctor-phone-field"><input autocomplete="nope" type="text" name="doctor_phone[999]" value="" class="doctor_phone-' + new_dr_id + ' dr-required dr-data dr-phone LoNotSensitive dyn" placeholder="Phone Number *" title="Phone Number *"></div>\
										<div class="half-width doctor-fax-field"><input autocomplete="nope" type="text" name="doctor_fax[999]" value="" class="doctor_fax-' + new_dr_id + ' dr-data dr-fax LoNotSensitive" placeholder="Fax Number" title="Fax Number"></div>\
									</div>\
								</div>' + cloneMed + addmore_provider_html + '\
                                                                </div>');



    newDrForm.find('.doctor-no-field').text('Healthcare Provider ' + (new_dr_id) + ':');
    newDrForm.find('.dr-data').each(function (index, elem) {
        jQuery(elem).change(UpdateDoctor);
        jQuery(elem).attr('name', jQuery(elem).attr('name').replace(/\[.*?\]\s?/g, '[' + new_dr_id + ']'));
    });
    newDrForm.find('select.dr-data').keyup(UpdateDoctor);
    //Code added by vinod
    $("#myTabs li").removeClass("active");
    newDrFormTab = jQuery('<li class="active">\ <a href="#pb' + new_dr_id + '" data-toggle="tab">New</a></li>');
    $('#myTabs').find(' > li:nth-last-child(1)').before(newDrFormTab);

    //Code added by vinod
    newDrForm.appendTo('#doctors_forms_list');
//    jQuery(newDrForm).find('.dyn').removeClass('dyn');
    jQuery(newDrForm).find('.cg_medication_form').find('.remove_block').closest('.remove-div-1').remove();
    new_med_id = parseInt(jQuery('#settings-r .medication-row').length);
    jQuery(newDrForm).find('.cg_medication_form').attr('id', 'mb' + new_med_id);






    ReorderMedSpecifiers();
    //Added by vinod to display name on tab
    var set_id = 'pb' + new_dr_id;
    healthCareProviderNameHeader(new_dr_id, set_id, 'doctor_last_name');
    //Added by vinod to display name on tab
    jQuery("input.dr-phone").mask("000-000-0000", {clearIfNotMatch: true});
    jQuery("input.dr_fax").mask("000-000-0000", {clearIfNotMatch: true});

    //jQuery('.dr-form:last input, .dr-form:last select').jvFloat();    
    jQuery('.dr-form:last input').jvFloat();
    // bind jquery validation with dynamic fields

    var new_med_id = parseInt(jQuery('#settings-r .medication-row').length);
    console.log(jQuery('input[name="medication_name[' + new_med_id + ']"]'));
    console.log(new_med_id);
//    jQuery('input[name="medication_name[' + new_med_id + ']"]').change(syncMedicationData);
//    jQuery('input[name="medication_strength[' + new_med_id + ']"]').change(syncMedicationData);
//    jQuery('input[name="medication_frequency[' + new_med_id + ']"]').change(syncMedicationData);
//    jQuery('select[name="medication_doctor[' + new_med_id + ']"]').change(syncMedicationData).keyup(syncMedicationData);

    jQuery(document).on('change', 'input[name*="medication_name"]', syncMedicationData);
    jQuery(document).on('change', 'input[name*="medication_strength"]', syncMedicationData);
    jQuery(document).on('change', 'input[name*="medication_frequency"]', syncMedicationData);
    jQuery(document).on('change keyup', 'input[name*="medication_doctor"]', syncMedicationData);

    bindDynamicValidations();
    
    initializeDoctorAddressElementGeoLocation(document.getElementById(jQuery('input.dr-address:last').attr('id')), new_dr_id);
    
        
}

function UpdateDoctor(e) {
//    //debugger;
    //update doctor data
    var dr_id = jQuery('input#dr_id').val();
    var dr_field_name = jQuery(this).attr('name');

    valid_form = true;
    first_invalid_element = false;

    //remove existent errors
    //jQuery('.dr-data').removeClass('error');

    //show green checkmark
    if (jQuery(this).val() != '') {
        jQuery(this).addClass('correct');
    } else {
        jQuery(this).removeClass('correct');
    }

    //validate form
    jQuery('.dr-required').each(function (index, elem) {
        if (jQuery(elem).val() == '') {
            if (first_invalid_element === false) {
                first_invalid_element = jQuery(elem);
            }

            if (jQuery(elem).attr('name') == dr_field_name) {
                jQuery(elem).removeClass('correct');
                jQuery(elem).removeClass('field-error-only');
                jQuery(elem).addClass('error');

                if (jQuery(elem).parent().find('.invalid-field').length == 0) {
                    //add error label
                    errorLabel = jQuery('<label>').attr('for', jQuery(elem).id).addClass('error').addClass('invalid-field').text('This field is required');
                    errorLabel.insertAfter(jQuery(elem));
                }
                if (jQuery(elem).hasClass('dr-phone') && jQuery(elem).val() != '' && jQuery(elem).val() == jQuery('input[name="p_phone"]').val()) {
                    //add error label
                    errorLabel = jQuery('<label>').attr('for', jQuery(elem).id).addClass('error').addClass('invalid-field').text('This field is required');
                    errorLabel.insertAfter(jQuery(elem));
                }
            }
            valid_form = false;
        } else {
            if (jQuery(elem).attr('name') == dr_field_name) {
                jQuery(elem).removeClass('error');
                jQuery(elem).parent().find('.invalid-field').remove();
            }
        }
    })

    //ascii validation
    jQuery('input').each(function (index, elem) {
        elemName = jQuery(elem).attr('name');
        if (typeof elemName != 'undefined' && jQuery(elem).attr('name').substr(0, 7) == 'doctor_') {
            if (!isAscii(jQuery(elem).val())) {
                if (first_invalid_element === false) {
                    first_invalid_element = jQuery(elem);
                }

                if (jQuery(elem).attr('name') == dr_field_name) {
                    jQuery(elem).removeClass('correct');
                    jQuery(elem).removeClass('field-error-only');
                    jQuery(elem).addClass('error');

                    if (jQuery(elem).parent().find('.invalid-field').length == 0) {
                        //add error label
                        errorLabel = jQuery('<label>').attr('for', jQuery(elem).id).addClass('error').addClass('invalid-field').text('Please insert only alphanumeric characters.');
                        errorLabel.insertAfter(jQuery(elem));
                    }
                }

                valid_form = false;
            } else {
                if (jQuery(elem).attr('name') == dr_field_name) {
                    jQuery(elem).removeClass('error');
                    jQuery(elem).parent().find('.invalid-field').remove();
                }
            }
        }
    })

    //validate zip code
    //zip_code_elem = jQuery('input[name="dr_zip"]');
    zip_code_elem = jQuery(this);
    if (dr_field_name.substr(0, 10) == 'doctor_zip' && zip_code_elem != '') {
        if (!(/^\s*\d{5}\s*$/.test(zip_code_elem.val()))) {
            zip_code_elem.removeClass('correct');
            zip_code_elem.removeClass('field-error-only');
            zip_code_elem.addClass('error');

            if (zip_code_elem.parent().find('.invalid-field').length == 0) {
                //add error label
                errorLabel = jQuery('<label>').attr('for', zip_code_elem.id).addClass('error').addClass('invalid-field').text('Invalid zip code');
                errorLabel.insertAfter(zip_code_elem);
            }
            //alert('If');
            valid_form = false;
        } else {
            //alert('Else');
            if (dr_field_name.substr(0, 10) == 'doctor_zip') {
                zip_code_elem.removeClass('error');
                zip_code_elem.parent().find('.invalid-field').remove();
            }
        }
    }

    //add validation to mobile phone number
    dr_phone_elem = jQuery('.dr-phone');

    //add validation to mobile phone number
    doctor_pho_elem = jQuery(this);
    if (dr_field_name.substr(0, 10) == 'doctor_pho' && jQuery(this).val() != '' && jQuery(this).siblings('label.error').length == 0) {
        if (jQuery(this).val() != jQuery('input[name="p_phone"]').val()) {
            jQuery(this).removeClass('error').addClass('correct');
        } else {
            console.log();
            //add error label
            errorLabel = jQuery('<label>').attr('for', jQuery(this).id).addClass('error invalid-field').removeClass('correct').text('Your phone number cannot match the healthcare provider\'s phone number. Please enter a valid phone number.');
            errorLabel.insertAfter(jQuery(this));
            jQuery(this).removeClass('correct').addClass('error');
            valid_form = false;
        }
    } else if (dr_field_name.substr(0, 10) == 'doctor_pho' && doctor_pho_elem.val() == '') {
        console.log('doctor_pho_elem value', doctor_pho_elem);
        //add error label
        errorLabel = jQuery('<label>').attr('for', jQuery(this).id).addClass('error invalid-field').removeClass('correct').text('Your phone number cannot match the healthcare provider\'s phone number. Please enter a valid phone number.');
        errorLabel.insertAfter(jQuery(this));
        jQuery(this).removeClass('correct').addClass('error');
        valid_form = false;
    }

    if (dr_field_name.substr(0, 15) == 'doctor_address[' && jQuery(this).val() != '' && jQuery(this).siblings('label.error').length == 0) {
        if (jQuery(this).val().toLowerCase() != jQuery('input[name="p_address"]').val().toLowerCase()) {
            jQuery(this).removeClass('error').addClass('correct');
        } else {
            //add error label
            errorLabel = jQuery('<label>').attr('for', jQuery(this).id).addClass('error invalid-field').removeClass('correct').text('Your address cannot match the healthcare provider\'s address. Please enter a valid address.');
            errorLabel.insertAfter(jQuery(this));
            jQuery(this).removeClass('correct').addClass('error');
            valid_form = false;
        }
    }

    if (valid_form) {
        //if (!jQuery('input[name="doctor_first_name[' + (dr_id) + ']"]').length) {
        //	CreateNewDoctorRow(dr_id);
        //}

        //jQuery('input[name="doctor_first_name[' + (dr_id) + ']"]').val(jQuery('input[name="dr_first_name"]').val());
        //jQuery('input[name="doctor_last_name[' + (dr_id) + ']"]').val(jQuery('input[name="dr_last_name"]').val());
        //jQuery('input[name="doctor_facility[' + (dr_id) + ']"]').val(jQuery('input[name="dr_facility"]').val());
        //jQuery('input[name="doctor_address[' + (dr_id) + ']"]').val(jQuery('input[name="dr_address"]').val());
        //jQuery('input[name="doctor_address2[' + (dr_id) + ']"]').val(jQuery('input[name="dr_address2"]').val());
        //jQuery('input[name="doctor_city[' + (dr_id) + ']"]').val(jQuery('input[name="dr_city"]').val());
        //jQuery('input[name="doctor_state[' + (dr_id) + ']"]').val(jQuery('select[name="dr_state"]').val());
        //jQuery('input[name="doctor_zip[' + (dr_id) + ']"]').val(jQuery('input[name="dr_zip"]').val());
        //jQuery('input[name="doctor_phone[' + (dr_id) + ']"]').val(jQuery('input[name="dr_phone"]').val());
        //jQuery('input[name="doctor_fax[' + (dr_id) + ']"]').val(jQuery('input[name="dr_fax"]').val());

        //UpdateDoctorsList();

        //update medication doctor drop-down
        updateMedicationDoctorsDropdown(e); // comment when marge provide and medication

        //sync doctor data
        syncDoctorsData();

        if (e) {
            //debugger;
            var targetId = jQuery(e.target).closest('.dr-form').attr('id');
            var get_block_id = (targetId).replace('pb', '');
            console.log(get_block_id);
            console.log(jQuery(e.target).closest('.dr-form'));
            jQuery(e.target).closest('.dr-form').find('.cg_medication_doctor').val(get_block_id);
            jQuery(e.target).closest('.dr-form').find('.cg_medication_form').show();
        }

    } else {
        //scroll to first invalid field
        //if (first_invalid_element) {
        //	first_invalid_element.focus();
        //	jQuery(window).scrollTop(first_invalid_element.position().top - 250);
        //}
    }

    //hide error messages
    //jQuery(this).nextAll('.dr-data').each(function() {
    //	console.log(jQuery(this).attr('name'));
    //	jQuery(this).removeClass('error');
    //});
}

function CreateNewDoctorRow(new_dr_id) {
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

function updateMedicationDoctorsDropdown(event = null) {
    //debugger;
    if (event) {
        event.target
    }
    var updateDD = true;
    if (jQuery('#removepb').val() == 1) {
        jQuery('#removepb').val('');
        jQuery('.doctors_dropdown').html('<option value=""></option>');
        updateDD = false;
    }
    for (dr_id = 1; dr_id <= jQuery('input.doctor-fields').length; dr_id++) {
        var drID = dr_id;
        var drName = jQuery('input[name="doctor_first_name[' + dr_id + ']"]').val() + ' ' + jQuery('input[name="doctor_last_name[' + dr_id + ']"]').val();
        var drExists = false;
        jQuery('.doctors_dropdown').each(function () {
            jQuery(this).find('option').each(function () {
                if (jQuery(this).attr('value') == dr_id) {
                    jQuery(this).text(drName);
                    drExists = true;
                }
            });
        });
        if (!drExists && drName != ' ') {
            jQuery('.doctors_dropdown').append(jQuery('<option>', {value: drID, text: drName}));
        }
    }

    console.log(jQuery('.doctors_dropdown option').length);

    // check if only 1 valid option is available then pre-select it
    if (jQuery('select[name="medication_doctor[1]"] option').length == 2 && updateDD) {
        console.log('updateMedicationDoctorsDropdown() - IF');
        jQuery('.doctors_dropdown option:eq(1)').attr('selected', 'selected');
        var ele_id = "medication_doctor[1]";
        var placeholderText = jQuery('select[name="medication_doctor[1]"]').attr('placeholder');
        placeholderText = jQuery.trim(placeholderText);
        var cls_wrapper = jQuery('select[name="medication_doctor[1]"]').attr('name').split("[");
        if (jQuery('select[name="medication_doctor[1]"]').val() != '') {

            console.log('updateMedicationDoctorsDropdown() - IF 1');
            console.log(jQuery('select[name="medication_doctor[1]"]').val());

            if (jQuery('select[name="medication_doctor[1]"]').siblings('label.placeHolder').length == 0 && placeholderText != '') {

                console.log('updateMedicationDoctorsDropdown() - IF 2');

                // add red colored (*) to placeholder   
                var patt = /\*/g;
                var result = patt.test(placeholderText);
                if (result) {
                    placeholderText = placeholderText.replace('*', '') + '<span class="red">*</span>';
                }
                jQuery('select[name="medication_doctor[1]"]').addClass('not-empty');
                jQuery('<label class="placeHolder active" for="' + ele_id + '">' + placeholderText + '</label>').insertBefore(jQuery('select[name="medication_doctor[1]"]'));
                jQuery('select[name="medication_doctor[1]"]').parent('.jvFloat').removeClass(' ' + cls_wrapper[0] + '_wrapper');
                console.log('updateMedicationDoctorsDropdown() - IF 5');
            } else {
                console.log('updateMedicationDoctorsDropdown() - IF 3');
                jQuery('.doctors_dropdown').each(function () {
                    var drName = jQuery(this).find('option:selected').text();
                    if (drName != '') {
                        $(this).siblings('label.placeHolder').addClass('active');
                    }
                });
            }
        } else {
            console.log('updateMedicationDoctorsDropdown() - ELSE');
            jQuery('.doctors_dropdown').each(function () {
//                jQuery(this).find('option:selected').removeAttr('selected');
                var cls_wrapper = jQuery(this).attr('name').split("[");
                jQuery(this).removeClass('not-empty');
                jQuery(this).removeClass('correct');
                jQuery('label.placeHolder[for="' + jQuery(this).attr('name') + '"]');
                jQuery(this).parent('.jvFloat').addClass(' ' + cls_wrapper[0] + '_wrapper');
            });
        }
    } else {
        console.log('updateMedicationDoctorsDropdown() - ELSE 2');



        jQuery('.doctors_dropdown').each(function () {
//            jQuery(this).find('option:selected').removeAttr('selected');
            var cls_wrapper = jQuery(this).attr('name').split("[");
            jQuery(this).removeClass('not-empty');
            jQuery(this).removeClass('correct');
            jQuery('label.placeHolder[for="' + jQuery(this).attr('name') + '"]');
            jQuery(this).siblings('label.placeHolder').removeClass('active');
            console.log('Else 2 ', this);
            jQuery(this).parent('.jvFloat').addClass(' ' + cls_wrapper[0] + '_wrapper');


        });
        // var new_set_id = 'pb'+dr_id ; 
        // healthCareProviderNameHeader(dr_id, new_set_id, 'doctor_last_name');
    }
    jQuery('.doctors_dropdown').change();
}

function UpdateDoctorsList() {
    //debugger;
    jQuery('#doctors_list').remove();

    if (jQuery('input.doctor-fields').length > 0) {
        drs_list = '';
        dr_added = 0;
        for (dr_id = 1; dr_id <= jQuery('input.doctor-fields').length; dr_id++) {
            if (jQuery('input[name="doctor_first_name[' + dr_id + ']"]').length > 0 && jQuery('input[name="doctor_last_name[' + dr_id + ']"]').length > 0 && jQuery('input[name="doctor_phone[' + dr_id + ']"]').length > 0 && jQuery('input[name="doctor_address[' + dr_id + ']"]').length > 0 && jQuery('input[name="doctor_city[' + dr_id + ']"]').length > 0 && jQuery('input[name="doctor_state[' + dr_id + ']"]').length > 0 && jQuery('input[name="doctor_zip[' + dr_id + ']"]').length > 0) {
                dr_added++;

                if (dr_added == 1) {
                    drs_list += '<div id="doctors_list" class="medication_list">\
									<br>\
									<div class="no-margin desktop-only">\
										<span class="astable_small align-left">&nbsp;</span>\
										<span class="astable align-left">Name</span>\
										<span class="astable align-left">Phone</span>\
										<span class="astable align-left">Address</span>\
										<span class="astable_small align-right">Action</span>\
									</div>\
									<div class="no-margin mobile-only">\
										<span class="astable bold">Doctors</span>\
									</div>\
									<div class="astable_hline desktop-only"></div>';
                } else {
                    drs_list += '	<div class="astable_hline_light desktop-only"></div>';
                }

                drs_list += '		<div class="astable_hline mobile-only"></div>\
									<div class="no-margin">\
										<span class="astable_small align-left no-overflow bold">Provider ' + dr_added + '</span>\
										<span class="astable align-left no-overflow">' + jQuery('input[name="doctor_first_name[' + dr_id + ']"]').val() + ' ' + jQuery('input[name="doctor_last_name[' + dr_id + ']"]').val() + '</span>\
										<span class="astable align-left no-overflow">' + jQuery('input[name="doctor_phone[' + dr_id + ']"]').val() + '</span>\
										<span class="astable align-left no-overflow">' + jQuery('input[name="doctor_address[' + dr_id + ']"]').val() + ',<br>' + jQuery('input[name="doctor_city[' + dr_id + ']"]').val() + ', ' + jQuery('input[name="doctor_state[' + dr_id + ']"]').val() + ' ' + jQuery('input[name="doctor_zip[' + dr_id + ']"]').val() + '</span>\
										<span class="astable_small align-right mobile-only-margin-top"><a href="#" class="skipLeave" onclick="EditDoctor(' + dr_id + '); return false;">Edit</a></span>\
									</div>';

                //medication doctors dropdown
                var drID = dr_id;
                var drName = jQuery('input[name="doctor_first_name[' + dr_id + ']"]').val() + ' ' + jQuery('input[name="doctor_last_name[' + dr_id + ']"]').val();
                var drExists = false;
                jQuery('.doctors_dropdown').find('option').each(function () {
                    if (jQuery(this).attr('value') == dr_id) {
                        jQuery(this).text(drName);
                        drExists = true;
                    }
                })
                if (!drExists) {
                    jQuery('.doctors_dropdown').append(jQuery('<option>', {value: drID, text: drName}));
                }
            }
        }

        if (dr_added > 0) {
            drs_list += '</div>';
            jQuery(drs_list).insertAfter('div#doctor_form');
        }
    }
}

function EditDoctor(dr_id) {
    jQuery('input#dr_id').val(dr_id);
    jQuery('input[name="dr_first_name"]').val(jQuery('input[name="doctor_first_name[' + (dr_id) + ']"]').val()).trigger('blur');
    jQuery('input[name="dr_last_name"]').val(jQuery('input[name="doctor_last_name[' + (dr_id) + ']"]').val()).trigger('blur');
    jQuery('input[name="dr_facility"]').val(jQuery('input[name="doctor_facility[' + (dr_id) + ']"]').val()).trigger('blur');
    jQuery('input[name="dr_address"]').val(jQuery('input[name="doctor_address[' + (dr_id) + ']"]').val()).trigger('blur');
    jQuery('input[name="dr_address2"]').val(jQuery('input[name="doctor_address2[' + (dr_id) + ']"]').val()).trigger('blur');
    jQuery('input[name="dr_city"]').val(jQuery('input[name="doctor_city[' + (dr_id) + ']"]').val()).trigger('blur');
    jQuery('select[name="dr_state"]').val(jQuery('input[name="doctor_state[' + (dr_id) + ']"]').val()).trigger('blur');
    jQuery('input[name="dr_zip"]').val(jQuery('input[name="doctor_zip[' + (dr_id) + ']"]').val()).trigger('blur');
    jQuery('input[name="dr_phone"]').val(jQuery('input[name="doctor_phone[' + (dr_id) + ']"]').val()).trigger('blur');
    jQuery('input[name="dr_fax"]').val(jQuery('input[name="doctor_fax[' + (dr_id) + ']"]').val()).trigger('blur');


    jQuery('input[name="dr_first_name"]').focus();
}

function ReorderMedSpecifiers() {
    var i = 1;
    jQuery('#settings-r .medication-row .medication-no-field').each(function () {
        jQuery(this).html('Medication ' + i);
        i++;
    });
    var i = 1;
    jQuery('#settings-r .medication-row .medication-name-field input').each(function () {
        jQuery(this).attr('name', 'medication_name[' + i + ']');
        //console.log('Medication name : ',i);
        i++;
    });
    var i = 1;
    jQuery('#settings-r .medication-row .medication-strength-field input').each(function () {
        jQuery(this).attr('name', 'medication_strength[' + i + ']');
        i++;
    });
    var i = 1;
    jQuery('#settings-r .medication-row .medication-frequency-field input').each(function () {
        jQuery(this).attr('name', 'medication_frequency[' + i + ']')
        i++;
    });
    var i = 1;
    jQuery('#settings-r .cg_medication_doctor').each(function () {
        jQuery(this).attr('name', 'medication_doctor[' + i + ']');
        console.log('Medication Doctor : ', i);
        i++;
    });
    resetAddMoreMedi();
}

function AddANewMedication(e) {
    //debugger;
    if (!jQuery('form#register_form').valid()) {
        return false;
    }

    // $('#medication_form .medication-row .doctors_dropdown option').each(function() {
    //     if($(this).is(':selected')) {
    //       //console.log('Current val for select option', this.val());  
    //       console.log('Current val for select option',$(this).filter(':selected').val());
    //   }
    // });

    // re-order the sequence of blocks
    //ReorderMedSpecifiers();        

    //new_med_id = parseInt(jQuery('.med-data').length / 4) + 1;
    new_med_id = parseInt(jQuery('.medication-row').length) + 1;

    // check if user has entered values in previous inputs
    jQuery('#med_err').remove();
    var prev_eq = (new_med_id >= 2) ? new_med_id - 2 : 0;
    var yourArray = [];
    var addMedArr = [];
    $(".medication-row").each(function () {
        var getMdId = $(this).attr("id");
        yourArray.push(getMdId);
        var afterReplace = getMdId.replace('mb', '');
        console.log('AfterReplace', afterReplace);

        addMedArr.push(afterReplace);

    });
    let max_value = Math.max.apply(Math, addMedArr); // 3
    console.log('Max value :---: ', max_value);
    //if($("#mb" + new_med_id).length != 0) {
    new_med_id = max_value + 1;
    console.log('It doesnt exists', new_med_id);
    if (jQuery('.medication-name-field').eq(prev_eq).find('input').val() == '' || jQuery('.medication-strength-field').eq(prev_eq).find('input').val() == ''
            || jQuery('.medication-frequency-field').eq(prev_eq).find('input').val() == '' || jQuery('.medication-doctor-field').eq(prev_eq).find('input').val() == '') {
        jQuery('.medication-row').eq(prev_eq).append('<span id="med_err" class="error">Please fill complete information of Medication ' + parseInt(jQuery('.medication-row').length) + '</span>');
        return false;
    }

    $("#settings-r .medication-row").removeClass("active");
    var html = '<div class="medication-row tab-pane fade in active"  id="mb' + new_med_id + '" role="tabpanel">';
    html += '<div class="remove-div-1"><a href="javascript:void(0);" class="remove_block" id="remove_provider_mb' + new_med_id + '">X Remove Medication</a></div>';
    //html += '<div class="medication-no-field p20-width align-center bold w100">Medication '+new_med_id+'</div>';
    html += '<div>';
    html += '<div class="form-group"><div class="medication-name-field full-width"><input autocomplete="nope" type="text" name="medication_name[' + new_med_id + ']" value="" placeholder="Medication Name *" title="Medication Name *" class="dyn med-data LoNotSensitive med_name"></div></div>';
    html += '<div class="form-group"><div class="medication-strength-field full-width"><input autocomplete="nope" type="text" name="medication_strength[' + new_med_id + ']" value="" placeholder="Medication Strength *" title="Medication Strength *" class="dyn med-data LoNotSensitive"></div></div>';
    html += '</div>';
    html += '<div>';
    html += '<div class="form-group"><div class="medication-frequency-field full-width"><input autocomplete="nope" type="text" name="medication_frequency[' + new_med_id + ']" value="" placeholder="Medication Frequency (ex. daily) *" title="Medication Frequency (ex. daily) *" class="dyn med-data LoNotSensitive"></div></div>';
    html += '<div class="form-group"><div class="medication-doctor-field full-width select-dropdown"><div class="jvFloat  patient-select-cntntr medication_doctor_wrapper"><label class="placeHolder" for="medication_doctor[' + new_med_id + ']">Prescribing Healthcare Provider <span class="red">*</span></label><select autocomplete="nope" name="medication_doctor[' + new_med_id + ']" preload="" class="doctors_dropdown dyn med-data full-width LoNotSensitive" placeholder="Prescribing Healthcare Provider *" title="Prescribing Healthcare Provider *"><option value=""></option></select></div></div></div>';
    html += '</div>';
    html += '</div>';

    jQuery('#medication_form').append(html);

    //Code added by vinod

    //Code added by vinod
    if (new_med_id % 4 == 0) {
        $("#myTabsMedication li").removeClass("active");
        newDrFormTab = jQuery('<li class="active">\ <a href="#mb' + new_med_id + '" data-toggle="tab">New</a></li>');
    } else {
        $("#myTabsMedication li").removeClass("active");
        newDrFormTab = jQuery('<li class="active">\ <a href="#mb' + new_med_id + '" data-toggle="tab">New</a></li>');
    }

    // newDrFormTab = jQuery('<li class="">\ <a href="#pb'+new_med_id+'" data-toggle="tab">New Medication</a></li>');
    //newDrFormTab.appendTo('#myTabsMedication');
    $('#myTabsMedication').find(' > li:nth-last-child(1)').before(newDrFormTab);
    jQuery('.medication-row:last input').jvFloat();
    jQuery('input[name="medication_name[' + new_med_id + ']"]').change(syncMedicationData);
    jQuery('input[name="medication_strength[' + new_med_id + ']"]').change(syncMedicationData);
    jQuery('input[name="medication_frequency[' + new_med_id + ']"]').change(syncMedicationData);
    jQuery('select[name="medication_doctor[' + new_med_id + ']"]').change(syncMedicationData).keyup(syncMedicationData);

    // bind jquery validation with dynamic fields
    bindDynamicValidations();

    jQuery('select[name="medication_doctor[' + new_med_id + ']"]').html(jQuery('select[name="medication_doctor[' + parseInt(new_med_id) + ']"]').html());

    console.log(jQuery('select[name="medication_doctor[' + new_med_id + ']"]').val() + ' - id of selection :::: total options - ' + jQuery('select[name="medication_doctor[1]"] option').length);

    if (jQuery('select[name="medication_doctor[1]"] option').length == 2) {
        jQuery('select[name="medication_doctor[' + new_med_id + ']"] option:eq(1)').attr('selected', 'selected');
        var ele_id = "medication_doctor[" + new_med_id + "]";
        var placeholderText = jQuery('select[name="medication_doctor[' + new_med_id + ']"]').attr('placeholder');
        placeholderText = jQuery.trim(placeholderText);
        var cls_wrapper = jQuery('select[name="medication_doctor[' + new_med_id + ']"]').attr('name').split("[");
        if (jQuery('select[name="medication_doctor[' + new_med_id + ']"]').val() != '') {
            if (jQuery('select[name="medication_doctor[' + new_med_id + ']"]').siblings('label.placeHolder').length == 0 && placeholderText != '') {
                // add red colored (*) to placeholder   
                var patt = /\*/g;
                var result = patt.test(placeholderText);
                if (result) {
                    placeholderText = placeholderText.replace('*', '') + '<span class="red">*</span>';
                }
                jQuery('select[name="medication_doctor[' + new_med_id + ']"]').addClass('not-empty');
                jQuery('<label class="placeHolder active" for="' + ele_id + '">' + placeholderText + '</label>').insertBefore(jQuery('select[name="medication_doctor[' + new_med_id + ']"]'));
                jQuery('select[name="medication_doctor[' + new_med_id + ']"]').parent('.jvFloat').removeClass(' ' + cls_wrapper[0] + '_wrapper');
            }
        } else {
            jQuery('select[name="medication_doctor[' + new_med_id + ']"]').removeClass('not-empty');
            jQuery('label[for="' + ele_id + '"]').remove();
            jQuery('select[name="medication_doctor[' + new_med_id + ']"]').parent('.jvFloat').addClass(' ' + cls_wrapper[0] + '_wrapper');
        }
        console.log('AddANewMedication() - IF');
        jQuery('select[name="medication_doctor[' + new_med_id + ']"]').siblings('label.placeHolder').addClass('active');
        console.log('AddANewMedication() - Done');
    } else {
        jQuery('select[name="medication_doctor[' + new_med_id + ']"] option:selected').removeAttr('selected');
        console.log('AddANewMedication() - ELSE');
    }
    if ($("#mb" + new_med_id).length != 0) {
        //new_med_id++;
        console.log('getting MB id: ', new_med_id);
        var $options = $('select[name="medication_doctor[1]"] > option').clone();

        $('select[name="medication_doctor[' + new_med_id + ']"]').html($options);
        // $('select[name="medication_doctor[1]"] option').clone().append('select[name="medication_doctor['+new_med_id+']"]');
    }
}
function AddANewCgMedication(el, html) {
    //debugger;
    if (!jQuery('form#register_form').valid()) {
        return false;
    }

    // $('#medication_form .medication-row .doctors_dropdown option').each(function() {
    //     if($(this).is(':selected')) {
    //       //console.log('Current val for select option', this.val());  
    //       console.log('Current val for select option',$(this).filter(':selected').val());
    //   }
    // });

    // re-order the sequence of blocks
    //ReorderMedSpecifiers();        

    //new_med_id = parseInt(jQuery('.med-data').length / 4) + 1;
    new_med_id = parseInt(jQuery('#settings-r').find('.medication-row').length) + 1;
    console.log(new_med_id);
    // check if user has entered values in previous inputs
    jQuery('#med_err').remove();
    var prev_eq = (new_med_id >= 2) ? new_med_id - 2 : 0;
    var yourArray = [];
    var addMedArr = [];
    $("#settings-r .cg_medication_form").each(function () {
        var getMdId = $(this).attr("id");
        yourArray.push(getMdId);
        var afterReplace = getMdId.replace('mb', '');
        console.log('AfterReplace', afterReplace);

        addMedArr.push(afterReplace);

    });
    let max_value = Math.max.apply(Math, addMedArr); // 3
    console.log('Max value :---: ', max_value);
    //if($("#mb" + new_med_id).length != 0) {
    new_med_id = max_value + 1;
    console.log('It doesnt exists', new_med_id);
    if (jQuery('#settings-r  .medication-name-field').eq(prev_eq).find('input').val() == '' || jQuery('#settings-r  .medication-strength-field').eq(prev_eq).find('input').val() == ''
            || jQuery('#settings-r .medication-frequency-field').eq(prev_eq).find('input').val() == '' || jQuery('#settings-r  .medication-doctor-field').eq(prev_eq).find('input').val() == '') {
        jQuery('#settings-r .medication-row').eq(prev_eq).append('<span id="med_err" class="error">Please fill complete information of Medication ' + (parseInt(new_med_id) - 1) + '</span>');
        return false;
    }

//    $("#medication_form .medication-row").removeClass("active");
//    var html = '<div class="medication-row tab-pane fade in active"  id="mb' + new_med_id + '" role="tabpanel">';
//    html += '<div class="remove-div-1"><a href="javascript:void(0);" class="remove_block" id="remove_provider_mb' + new_med_id + '">X Remove Medication</a></div>';
//    //html += '<div class="medication-no-field p20-width align-center bold w100">Medication '+new_med_id+'</div>';
//    html += '<div>';
//    html += '<div class="form-group"><div class="medication-name-field full-width"><input autocomplete="nope" type="text" name="medication_name[' + new_med_id + ']" value="" placeholder="Medication Name *" title="Medication Name *" class="dyn med-data LoNotSensitive med_name"></div></div>';
//    html += '<div class="form-group"><div class="medication-strength-field full-width"><input autocomplete="nope" type="text" name="medication_strength[' + new_med_id + ']" value="" placeholder="Medication Strength *" title="Medication Strength *" class="dyn med-data LoNotSensitive"></div></div>';
//    html += '</div>';
//    html += '<div>';
//    html += '<div class="form-group"><div class="medication-frequency-field full-width"><input autocomplete="nope" type="text" name="medication_frequency[' + new_med_id + ']" value="" placeholder="Medication Frequency (ex. daily) *" title="Medication Frequency (ex. daily) *" class="dyn med-data LoNotSensitive"></div></div>';
//    html += '<div class="form-group"><div class="medication-doctor-field full-width select-dropdown"><div class="jvFloat  patient-select-cntntr medication_doctor_wrapper"><label class="placeHolder" for="medication_doctor[' + new_med_id + ']">Prescribing Healthcare Provider <span class="red">*</span></label><select autocomplete="nope" name="medication_doctor[' + new_med_id + ']" preload="" class="doctors_dropdown dyn med-data full-width LoNotSensitive" placeholder="Prescribing Healthcare Provider *" title="Prescribing Healthcare Provider *"><option value=""></option></select></div></div></div>';
//    html += '</div>';
//    html += '</div>';
//
//    jQuery('#medication_form').append(html);
    var newMedic = jQuery(cg_medication_form_dumy_html).insertAfter(jQuery(el).closest('.dr-form').find('.cg_medication_form').last());

    new_med_id = parseInt(jQuery('#settings-r .medication-row').length);
    jQuery(newMedic).find('.remove_block').attr('id', 'remove_provider_mb' + new_med_id);
    jQuery(newMedic).attr('id', 'mb' + new_med_id);
    //Code added by vinod

    //Code added by vinod
    if (new_med_id % 4 == 0) {
        $("#myTabsMedication li").removeClass("active");
        newDrFormTab = jQuery('<li class="active">\ <a href="#mb' + new_med_id + '" data-toggle="tab">New</a></li>');
    } else {
        $("#myTabsMedication li").removeClass("active");
        newDrFormTab = jQuery('<li class="active">\ <a href="#mb' + new_med_id + '" data-toggle="tab">New</a></li>');
    }
    // newDrFormTab = jQuery('<li class="">\ <a href="#pb'+new_med_id+'" data-toggle="tab">New Medication</a></li>');
    //newDrFormTab.appendTo('#myTabsMedication');
    $('#myTabsMedication').find(' > li:nth-last-child(1)').before(newDrFormTab);
    jQuery('#settings-r .medication-row:last input').jvFloat();
//    jQuery('input[name="medication_name[' + new_med_id + ']"]').change(syncMedicationData);
//    jQuery('input[name="medication_strength[' + new_med_id + ']"]').change(syncMedicationData);
//    jQuery('input[name="medication_frequency[' + new_med_id + ']"]').change(syncMedicationData);
//    jQuery('select[name="medication_doctor[' + new_med_id + ']"]').change(syncMedicationData).keyup(syncMedicationData);

    jQuery(document).on('change', 'input[name*="medication_name"]', syncMedicationData);
    jQuery(document).on('change', 'input[name*="medication_strength"]', syncMedicationData);
    jQuery(document).on('change', 'input[name*="medication_frequency"]', syncMedicationData);
    jQuery(document).on('change keyup', 'input[name*="medication_doctor"]', syncMedicationData);


    ReorderMedSpecifiers();
    // bind jquery validation with dynamic fields
    bindDynamicValidations();

    jQuery('select[name="medication_doctor[' + new_med_id + ']"]').html(jQuery('select[name="medication_doctor[' + parseInt(new_med_id) + ']"]').html());

    console.log(jQuery('select[name="medication_doctor[' + new_med_id + ']"]').val() + ' - id of selection :::: total options - ' + jQuery('select[name="medication_doctor[1]"] option').length);

    if (jQuery('select[name="medication_doctor[1]"] option').length == 2 && false) {
        jQuery('select[name="medication_doctor[' + new_med_id + ']"] option:eq(1)').attr('selected', 'selected');
        var ele_id = "medication_doctor[" + new_med_id + "]";
        var placeholderText = jQuery('select[name="medication_doctor[' + new_med_id + ']"]').attr('placeholder');
        placeholderText = jQuery.trim(placeholderText);
        var cls_wrapper = jQuery('select[name="medication_doctor[' + new_med_id + ']"]').attr('name').split("[");
        if (jQuery('select[name="medication_doctor[' + new_med_id + ']"]').val() != '') {
            if (jQuery('select[name="medication_doctor[' + new_med_id + ']"]').siblings('label.placeHolder').length == 0 && placeholderText != '') {
                // add red colored (*) to placeholder   
                var patt = /\*/g;
                var result = patt.test(placeholderText);
                if (result) {
                    placeholderText = placeholderText.replace('*', '') + '<span class="red">*</span>';
                }
                jQuery('select[name="medication_doctor[' + new_med_id + ']"]').addClass('not-empty');
                jQuery('<label class="placeHolder active" for="' + ele_id + '">' + placeholderText + '</label>').insertBefore(jQuery('select[name="medication_doctor[' + new_med_id + ']"]'));
                jQuery('select[name="medication_doctor[' + new_med_id + ']"]').parent('.jvFloat').removeClass(' ' + cls_wrapper[0] + '_wrapper');
            }
        } else {
            jQuery('select[name="medication_doctor[' + new_med_id + ']"]').removeClass('not-empty');
            jQuery('label[for="' + ele_id + '"]').remove();
            jQuery('select[name="medication_doctor[' + new_med_id + ']"]').parent('.jvFloat').addClass(' ' + cls_wrapper[0] + '_wrapper');
        }
        console.log('AddANewMedication() - IF');
        jQuery('select[name="medication_doctor[' + new_med_id + ']"]').siblings('label.placeHolder').addClass('active');
        console.log('AddANewMedication() - Done');
    } else {
//        jQuery('select[name="medication_doctor[' + new_med_id + ']"] option:selected').removeAttr('selected');
//        console.log('AddANewMedication() - ELSE');
    }
    if ($("#mb" + new_med_id).length != 0) {
        //new_med_id++;
        console.log('getting MB id: ', new_med_id);
        var $options = $('select[name="medication_doctor[1]"] > option').clone();

//        $('select[name="medication_doctor[' + new_med_id + ']"]').html($options);
        // $('select[name="medication_doctor[1]"] option').clone().append('select[name="medication_doctor['+new_med_id+']"]');
    }

    jQuery('#settings-r .med_name').first().trigger('focusout');
}

function bindDynamicValidations() {
    jQuery("#register_form .dyn").each(function () {
        var rules = '';
        if (jQuery(this).hasClass('dr-phone')) {
            rules = {
                required: true, phoneUS: true,
                messages: {
                    required: "This field is required",
                    phoneUS: "Please specify a valid phone number",
                }
            }
        } else {
            rules = {
                required: true,
                messages: {
                    required: "This field is required"
                }
            }
        }
        jQuery(this).rules("add", rules);
    });
}

function UpdateMedication_Old(e) {
    //update medication data

    var validMed = true;
    jQuery('.med-data').each(function () {
        if (jQuery(this).val() == '') {
            validMed = false;
        }
    });

    if (validMed) {
        var med_id = jQuery('input#med_id').val();
        //console.log('input[name="medication_name[' + (med_id) + ']"]');

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

function UpdateMedication(e) {
    //debugger;
    //update medication data

    //sync doctor data
    //syncDoctorsData();

    var validMed = true;
    jQuery('.med-data').each(function () {
        if (jQuery(this).val() == '') {
            validMed = false;
        }
    });

    if (validMed) {
        var med_id = jQuery('input#med_id').val();
        //console.log('input[name="medication_name[' + (med_id) + ']"]');

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

function CreateNewMedicationRow(new_med_id) {
    
    //debugger;
    //add new medication to the form
    jQuery('<input>').attr('type', 'hidden').attr('name', 'medication_name[' + (new_med_id) + ']').val('').addClass('medication-fields').insertBefore(jQuery('#bSubmit'));
    jQuery('<input>').attr('type', 'hidden').attr('name', 'medication_strength[' + (new_med_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
    jQuery('<input>').attr('type', 'hidden').attr('name', 'medication_frequency[' + (new_med_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
    jQuery('<input>').attr('type', 'hidden').attr('name', 'medication_doctor[' + (new_med_id) + ']').val('').insertBefore(jQuery('#bSubmit'));
}

function UpdateMedicationList() {
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

        for (med_id = 1; med_id <= jQuery('input.medication-fields').length; med_id++) {
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

function EditMedication(med_id) {
    jQuery('input#med_id').val(med_id);
    jQuery('input[name="med_name"]').val(jQuery('input[name="medication_name[' + (med_id) + ']"]').val());
    jQuery('input[name="med_strength"]').val(jQuery('input[name="medication_strength[' + (med_id) + ']"]').val());
    jQuery('input[name="med_frequency"]').val(jQuery('input[name="medication_frequency[' + (med_id) + ']"]').val());
    jQuery('select[name="med_doctor"]').val(jQuery('input[name="medication_doctor[' + (med_id) + ']"]').val());
}

function InvalidCreditCard() {
    jQuery('<div class="red bold align-left">The card you entered is not valid, please enter your card information again or try another payment method.</div><br>').insertBefore('#cc_number_box');

    jQueryValidation_Highlight("input[name='p_cc_number']");
    jQueryValidation_ShowErrors(jQuery("<label>").attr('for', jQuery("input[name='p_cc_number']").attr('id')).addClass('error').text('Credit Card not valid.'), jQuery("input[name='p_cc_number']"));
    jQueryValidation_Highlight("input[name='p_cc_exp_date']");
    jQueryValidation_ShowErrors(jQuery("<label>").attr('for', jQuery("input[name='p_cc_exp_date']").attr('id')).addClass('error').text('Credit Card not valid.'), jQuery("input[name='p_cc_exp_date']"));
    jQueryValidation_Highlight("input[name='p_cc_cvv']");
    jQueryValidation_ShowErrors(jQuery("<label>").attr('for', jQuery("input[name='p_cc_cvv']").attr('id')).addClass('error').text('Credit Card not valid.'), jQuery("input[name='p_cc_cvv']"));
    //jQuery('.payment_method_field').addClass('red');

    first_invalid_row = jQuery("input[name='p_cc_number']");
    //first_invalid_row.removeClass('correct').removeClass('error').addClass('error');
    if (first_invalid_row !== false) {
        jQuery(window).scrollTop(first_invalid_row.position().top - 200);
        jQuery(window).scrollLeft(0);
    }
    first_invalid_row.focus();

    var getActiveHrefVal = jQuery(".nav-tabs li").removeClass("active").find("a").attr('href');
    jQuery(getActiveHrefVal).removeClass("active");// Remove active class from current tab
    var getCurrentActiveHrefVal = jQuery("#patient-enroll-progress_6").addClass("active").find("a").attr('href');
    console.log("getCurrentActiveHrefVal:", getCurrentActiveHrefVal);
    jQuery(getCurrentActiveHrefVal).addClass("active");// Add active class to current tab content
    jQuery(".nav-tabs li").addClass("completed").removeClass("disabled");
    //jQuery(".nav-tabs li").removeClass("disabled");
    jQuery("#patient-enroll-progress_6").removeClass("completed");
}

//
//Leave Application Form functions
//

jQuery.fn.center = function () {
    this.css("position", "fixed");
    this.css("top", (jQuery(window).height() / 2) - (this.outerHeight() / 2));
    this.css("left", (jQuery(window).width() / 2) - (this.outerWidth() / 2));
    return this;
}

function preventPageLeave(e) {
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

function cancelPageLeave(e) {
    e.preventDefault();

    preventLeave = false;
    lastClickedObject = null;
    lastEventType = null;

    jQuery('#leavePopup').addClass("no-show");
    jQuery('#leavePopup2').addClass("no-show");
}

function continuePageLeave(e) {
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

function submitPageLeaveReason(e) {
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

function preventEnrollmentSubmit() {
    jQuery('#submitPopup').removeClass("no-show");
    jQuery('#submitPopup .leavePopupContent').center();

    //make sure the second popup it's hidden
    jQuery('#submitPopup2').addClass("no-show");
}

function confirmEnrollmentSubmit(e) {
    e.preventDefault();

    //hide first popup
    jQuery('#submitPopup').addClass("no-show");

    //
    submitConfirmed = true;
    jQuery("input#bSubmit").trigger('click');
}

function cancelEnrollmentSubmit(e) {
    e.preventDefault();

    //hide first popup
    jQuery('#submitPopup').addClass("no-show");

    //show second popup
    jQuery('#submitPopup2').removeClass("no-show");
    jQuery('#submitPopup2 .leavePopupContent').center();
}

function resetEnrollmentForm(e) {
    e.preventDefault();

    //hide popups
    jQuery('#submitPopup').addClass("no-show");
    jQuery('#submitPopup2').addClass("no-show");

    //
    window.location.href = 'register.php';
}

function hideEnrollmentSubmitConfirmation(e) {
    e.preventDefault();

    //hide popups
    jQuery('#submitPopup').addClass("no-show");
    jQuery('#submitPopup2').addClass("no-show");
}

function updateHearAboutExtras(e) {
    //destroy previous possible extra elements
    jQuery('.p_hear_about_extras').remove();

    //get row element
    hearAboutRow = jQuery(this);

    extra_elems = "";
    switch (jQuery(this).val()) {
        case "Insurance":
            //1 text box
            extra_elems = '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" placeholder="Insurance Company Name" title="Insurance Company Name" class=""></div>';
            break;

        case "Pharmacy":
            //1 text box
            extra_elems = '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" placeholder="Pharmacy Name" title="Pharmacy Name" class=""></div>';
            break;

        case "Previous Patient":
            //1 text box
            extra_elems = '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" placeholder="Please tell us what caused you to join the Prescription Hope program again." title="Please tell us what caused you to join the Prescription Hope program again." class="" onclick="javascript:showTitle();" id="s_t"></div>';
            break;

        case "Television":
            //1 text box
            extra_elems = '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" placeholder="What network did you see us on?" title="What network did you see us on?" class=""></div>';
            break;

        case "Google":
            //1 text box
            extra_elems = '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" placeholder="Please provide additional details on how you found the Prescription Hope program online." title="Please provide additional details on how you found the Prescription Hope program online." class="" onclick="javascript:showTitle();" id="s_t"></div>';
            break;

        case "Other":
            //1 text box
            extra_elems = '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" placeholder="Please provide additional details about how you heard about us. *" title="Please provide additional details about how you heard about us. *" class=""  onclick="javascript:showTitle();" id="s_t" required></div>';
            break;

        case "Healthcare Provider":
            extra_elems = '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" placeholder="Healthcare Provider Name" class=""></div>';
            extra_elems += '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_2" value="' + hear_about_extra_2_value + '" placeholder="Practice your healthcare provider treats you from" title="Practice your healthcare provider treats you from" class="" onclick="javascript:showTitle();" id="s_t"></div>';
            break;

        case "Referral By Current Member of the Prescription Hope Program":
            //3 text boxes
            extra_elems = '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_1" value="' + hear_about_extra_1_value + '" placeholder="First Name of the person who referred you" title="First Name of the person who referred you" class=""></div>';
            extra_elems += '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_2" value="' + hear_about_extra_2_value + '" placeholder="Last Name of the person who referred you" title="Last Name of the person who referred you" class=""></div>';
            extra_elems += '<div class="p_hear_about_extras"><input type="text" data-type="patient" name="p_hear_about_3" value="' + hear_about_extra_3_value + '" placeholder="' + ((jQuery(window).width() > 1024) ? 'Phone Number of the person who referred you, we would like to thank them for referring you' : 'Phone number of the person who referred you') + '" title="' + ((jQuery(window).width() > 1024) ? 'Phone Number of the person who referred you, we would like to thank them for referring you' : 'Phone number of the person who referred you') + '" class=""></div>';
            break;

        case "Social Media":
            //1 drop box
            extra_elems = '<div class="p_hear_about_extras"><select data-type="patient" name="p_hear_about_1" class="full-width" placeholder="Select ..."><option value=""></option><option value="Facebook">Facebook</option><option value="Twitter">Twitter</option><option value="LinkedIn">LinkedIn</option><option value="Instagram">Instagram</option></select></div>';
            break;

        case "Paper Mailing":
            //1 drop down + 1 text box
            extra_elems = '<div class="p_hear_about_extras"><select data-type="patient" name="p_hear_about_1" class="full-width" placeholder="Select ..." onChange=\'onPaperMailingChange(jQuery(this), "' + hear_about_extra_2_value + '");\'><option value=""></option><option value="Letter">Letter</option><option value="Postcard">Postcard</option></select></div>';
            break;

        case "Brokers":
            var broker_result = function () {
                var tmp = null;
                jQuery.ajax({
                    'async': false,
                    'type': "POST",
                    'global': false,
                    'url': "ajax_get_brokers_list.php",
                    'data': {},
                    'success': function (data) {
                        tmp = data;
                    }
                });
                return tmp;
            }();

            var resp = jQuery.parseJSON(broker_result);
            var selected = '';
            extra_elems = '<div class="jvFloat"><div class="p_hear_about_extras">'
            extra_elems += '<select data-type="patient" name="p_hear_about_1" class="full-width form-control" placeholder="Select ..." onChange=\'onBrokersChange(jQuery(this), "' + hear_about_extra_2_value + '");\'>'
            extra_elems += '<option value="" selected="selected">Select Broker</option>'
            for (var i = 0; i < resp.brokers.length; i++)
            {
                if (hear_about_extra_1_value == resp.brokers[i].id) {
                    selected = "selected=selected";
                }

                extra_elems += '<option ' + selected + ' data-corporation="' + resp.brokers[i].corporation_name + '" value="' + resp.brokers[i].id + '">' + resp.brokers[i].first_name + ' ' + resp.brokers[i].last_name + '</option>'
                selected = '';
            }
            extra_elems += '</select></div></div>';
            //extra_elems = '<div class="p_hear_about_extras"><select data-type="patient" name="p_hear_about_1" class="full-width" placeholder="Select ..." onChange=\'onBrokersChange(jQuery(this), "' + hear_about_extra_2_value + '");\'><option value="">Select Brokers</option><option value="Broker1">Broker1</option><option value="Broker2">Broker2</option></select></div>';
            break;
    }

    //add new form elements
    hearAboutRow.after(extra_elems);

    jQuery("input[name='p_hear_about_1'],input[name='p_hear_about_2'],input[name='p_hear_about_3'],select[name='p_hear_about_1'],select[name='p_hear_about_2'],select[name='p_hear_about_3'],input[name='p_cc_cvv']").blur(syncPatientData);

    // jQuery("input[type='password']").blur(syncPatientData);

    //if
    if (jQuery(this).val() == "Referral By Current Member of the Prescription Hope Program") {
        jQuery("input[name='p_hear_about_3']").mask("999-999-9999", {clearIfNotMatch: true});
    } else {
        jQuery("input[name='p_hear_about_3']").unmask();
    }

    //
    if (jQuery(this).val() == "Social Media" || jQuery(this).val() == "Paper Mailing" || jQuery(this).val() == "Brokers") {
        jQuery('select[name="p_hear_about_1"]').val(hear_about_extra_1_value);

        if (jQuery(this).val() == "Paper Mailing" && hear_about_extra_1_value != "") {
            onPaperMailingChange(jQuery('select[name="p_hear_about_1"]'), hear_about_extra_2_value);
        }
        if (jQuery(this).val() == "Brokers" && hear_about_extra_1_value != "") {
            onBrokersChange(jQuery('select[name="p_hear_about_1"]'), hear_about_extra_2_value);
        }

    }


    hear_about_extra_1_value = "";
    hear_about_extra_2_value = "";
    hear_about_extra_3_value = "";

    //jQuery('.p_hear_about_extras input,.p_hear_about_extras select').jvFloat();
    jQuery('.p_hear_about_extras input').jvFloat();
}

function onBrokersChange(elem, elem_value) {

    if (elem.val() != "" && jQuery('input[name="p_hear_about_2"]').length == 0) {
        extra_elems = '<div class="p_hear_about_extras"><input type="text" readonly name="p_hear_about_2" value="' + elem_value + '" class="w800 no-margin" placeholder="Corporation name" title="Corporation name"></div>';
        elem.parent().after(extra_elems);

        jQuery('.p_hear_about_extras input[name="p_hear_about_2"]').jvFloat();
    }
    new_value = jQuery('select[name="p_hear_about_1"] option:selected').data('corporation');
    jQuery('input[name="p_hear_about_2"]').val(new_value);
    jQuery('input[name="p_hear_about_2"]').prev('label').addClass('active');
    jQuery('input[name="p_hear_about_2"]').addClass('not-empty correct');
    jQuery('select[name="p_hear_about_1"]').addClass('not-empty correct');
    data_type = 'patient';
    fieldName = 'p_hear_about_2';
    fieldVal = new_value;
    jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
            .done(function (response) {
                //console.log(response);
            });
}

function onPaperMailingChange(elem, elem_value) {
    if (elem.val() != "" && jQuery('input[name="p_hear_about_2"]').length == 0) {
        extra_elems = '<div class="p_hear_about_extras"><input type="text" name="p_hear_about_2" value="' + elem_value + '" class="w800 no-margin" placeholder="Is there a code in the bottom-left of the postcard or letter?  If so, please enter it here." title="Is there a code in the bottom-left of the postcard or letter?  If so, please enter it here."></div>';
        elem.parent().after(extra_elems);

        jQuery('.p_hear_about_extras input[name="p_hear_about_2"]').jvFloat();
    }
}

function syncEmail() {
    jQuery.post("_sync.php", {first_name: jQuery('input[name="first_name"]').val(), middle_initial: jQuery('input[name="middle_initial"]').val(), last_name: jQuery('input[name="last_name"]').val(), email: jQuery('input[name="email"]').val(), type: 'email'})
            .done(function (response) {
                //console.log(response);
            });
}
/*function syncBrokerData () {
 data_type = jQuery(this).data('type');
 var fieldName = jQuery(this).attr('name');
 var fieldVal = jQuery(this).val();
 jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
 .done(function(response) {
 });
 }*/
function syncPatientData(e) {
//    //debugger;
    data_type = jQuery(this).data('type');
    if (typeof data_type !== typeof undefined && data_type !== false) {
        // remove correct class from the input field
        jQuery(this).removeClass('correct');

        fieldRules = jQuery(this).rules();
        if ((fieldRules.required === true && jQuery(this).val() != '') || fieldRules.required !== true) {
            //fieldName = (jQuery(this).attr('name') != 'p_ssn_masked') ? jQuery(this).attr("name") : "p_ssn";
            //fieldVal = (jQuery(this).attr('name') != 'p_ssn_masked') ? jQuery(this).val() : jQuery('input[name="p_ssn"]').val();

            var fieldName = jQuery(this).attr('name');
            var fieldVal = jQuery(this).val();
            switch (fieldName) {
                case 'p_ssn_masked':
                    fieldName = 'p_ssn';
                    fieldVal = jQuery('input[name="' + fieldName + '"]').val();
                    break;

                case 'p_cc_exp_date':
                    fieldName = 'p_cc_exp_month';
                    fieldVal = jQuery('input[name="' + fieldName + '"]').val();
                    break;
            }
            if (fieldName == "p_ssn") {
                if ($(this).hasClass('error')) {

                    return false;
                }

            }
            jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
                    .done(function (response) {
                        console.log('Ajax response::', response);
                        result = JSON.parse(response);
                        console.log(fieldName, result);
                        if (fieldName == 'p_ssn' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_ssn']").addClass('correct');
                            } else {
                                var err_msg = 'This social security number is already in use with a different email. Please try to log into your account with the correct email or contact customer support at 1-877-296-4673 for assistance.';
                                if (result.p_email != '') {
                                    err_msg = 'This social security number is already in use with the following email ' + result.p_email + '. Please try to log into your account with the correct email or contact customer support at 1-877-296-4673 for assistance.';
                                }
                                jQuery('label[for="' + jQuery("input[name='p_ssn']").attr('id') + '"].error').remove();
                                jQueryValidation_Highlight(jQuery('input[name="' + fieldName + '"]'));
                                jQueryValidation_ShowErrors(jQuery("<label>").attr('for', jQuery("input[name='p_ssn']").attr('id')).addClass('error').text(err_msg), jQuery("input[name='p_ssn']"));
                                if (!jQuery("input[name='p_ssn']").prevAll('input[type="text"]').hasClass('error')) {
                                    jQuery('html, body').animate({
                                        scrollTop: (jQuery("input[name='p_ssn']").offset().top - parseInt(200))
                                    }, 500);
                                }
                            }
                        }

                        if (fieldName == 'p_address2' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_address2']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_parent_first_name' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_parent_first_name']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_parent_middle_initial' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_parent_middle_initial']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_parent_last_name' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_parent_last_name']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_parent_phone' && fieldVal != '' && !$("input[name='p_parent_phone']").hasClass('error')) {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_parent_phone']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_pocketmoney' && fieldVal != '' && !$("input[name='p_pocketmoney']").hasClass('error')) {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_pocketmoney']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_dob' && fieldVal != '' && !$("#" + fieldName).hasClass('error')) {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_dob']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_alternate_contact_name' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_alternate_contact_name']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_alternate_phone' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_alternate_phone']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_hear_about' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("#p_hear_about").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_hear_about_1' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_hear_about_1']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_hear_about_2' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_hear_about_2']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_hear_about_3' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_hear_about_3']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_zip' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_zip']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_city' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_city']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_address' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("input[name='p_address']").addClass('correct');
                            }
                        }
                        if (fieldName == 'p_state' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("#p_state").addClass('correct');
                            }
                        }

                        if (fieldName == 'p_employment_status' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("#p_employment_status").addClass('correct');
                            }
                        }

                        if (fieldName == 'p_married' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("#p_married").addClass('correct');
                            }
                        }

                        if (fieldName == 'p_household' && fieldVal != '') {
                            if (result.success == 1) {
                                // add correct class from the input field
                                jQuery("#p_household").addClass('correct');
                            }
                        }
                    });

            //if synced field is the CC exp month, then we have to sync the exp year too
            if (fieldName == 'p_cc_exp_month') {
                fieldName = 'p_cc_exp_year';
                fieldVal = jQuery('input[name="' + fieldName + '"]').val();

                jQuery.post("_sync.php", {key: fieldName, value: fieldVal, type: data_type})
                        .done(function (response) {
                            //console.log(response);
                        });
            }
        }
    }
}

var lastDoctorsDataSync = 0;
function syncDoctorsData() {
    //debugger;
    //make sure last sync was over a second ago

    var d = new Date();
    var t = d.getTime();
    if (t - lastDoctorsDataSync > 1000) {
        lastDoctorsDataSync = t;

        var doctors_data = [];
        for (dr_id = 1; dr_id <= jQuery('input.doctor-fields').length; dr_id++) {
            doctors_data[dr_id] = {
                'doctor_id': jQuery('input[name="doctor_id[' + dr_id + ']"]').val(),
                'first_name': jQuery('input[name="doctor_first_name[' + dr_id + ']"]').val(),
                'last_name': jQuery('input[name="doctor_last_name[' + dr_id + ']"]').val(),
                'facility': jQuery('input[name="doctor_facility[' + dr_id + ']"]').val(),
                'address': jQuery('input[name="doctor_address[' + dr_id + ']"]').val(),
                'address2': jQuery('input[name="doctor_address2[' + dr_id + ']"]').val(),
                'city': jQuery('input[name="doctor_city[' + dr_id + ']"]').val(),
                'state': jQuery('select[name="doctor_state[' + dr_id + ']"]').val(),
                'zip': jQuery('input[name="doctor_zip[' + dr_id + ']"]').val(),
                'phone': jQuery('input[name="doctor_phone[' + dr_id + ']"]').val(),
                'fax': jQuery('input[name="doctor_fax[' + dr_id + ']"]').val()
            };
            jQuery('#cg_med_' + dr_id).val(dr_id);
        }

        jQuery.post("_sync.php", {
            type: 'provider',
            data: doctors_data
        }).done(function (response) {
            //console.log(response);
        });
    }
}



var cg_medication_form_dumy_html = '<div class="col-sm-7 card-form cg_medication_form">'+
                            '<div class="medication-row tab-pane fade in" id="mb1" role="tabpanel">'+
                                '<div>'+
                                    '<div class="form-group">'+	
                                        '<div class="medication-name-field full-width">'+
                                            '<input autocomplete="nope" type="text" name="medication_name[1]" value="" placeholder="Medication Name *" title="Medication Name *" class="dyn med-data LoNotSensitive med_name patient-enroll-progress-5 clear_vals" data-step-enroll="5">'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="form-group">'+			
                                        '<div class="medication-strength-field full-width">'+
                                            '<input autocomplete="nope" type="text" name="medication_strength[1]" value="" placeholder="Medication Strength *" title="Medication Strength *" class="dyn med-data LoNotSensitive patient-enroll-progress-5 clear_vals" data-step-enroll="5">'+
                                        '</div>'+
                                    '</div>'+	
                                '</div>'+
                                '<div>'+
                                    '<div class="form-group">'+		
                                        '<div class="medication-frequency-field full-width">'+
                                            '<input autocomplete="nope" type="text" name="medication_frequency[1]" value="" placeholder="Medication Frequency (ex. daily) *" title="Medication Frequency (ex. daily) *" class="dyn med-data LoNotSensitive patient-enroll-progress-5 clear_vals" data-step-enroll="5">'+
                                        '</div>'+
                                    '</div>'+
                                    '<input type="hidden" autocomplete="nope" name="medication_doctor[1]" class="cg_medication_doctor med-data full-width LoNotSensitive patient-enroll-progress-5">'+
                                '</div>'+
                                '<div class="remove-div-1"><a href="javascript:void(0);" class="remove_block" id="remove_provider_mb">X Remove Medication</a></div>'+
                            '</div>'+
                        '</div>';

function addCgMedication(el) {

//    var clone = jQuery('.cg_medication_form_dumy').html();
    var clone = cg_medication_form_dumy_html;

    AddANewCgMedication(el, clone);



//    console.log(jQuery(el).closest('.cg_medication_form'));
//    var newEl = jQuery(clone).insertAfter(jQuery(el).closest('.cg_medication_form'));
//    console.log(newEl);
//    ReorderMedSpecifiers();
    var targetId = jQuery(el).closest('.dr-form').attr('id');
    var get_block_id = (targetId).replace('pb', '');
    jQuery(el).closest('.dr-form').find('.cg_medication_doctor').val(get_block_id);
    resetAddMoreMedi();
}
function resetAddMoreMedi() {
    var count = 0;
    jQuery('.dr-form').each(function () {
        count++;
        jQuery(this).find('.cg_medication_doctor').val(count);
        jQuery(this).find('.provider_label').remove();
        jQuery(this).children('.card-form').first().prepend('<span class="cg_form_label provider_label">Healthcare Provider ' + count + '</span>');
        var countModi = 0;
        jQuery(this).find('.cg_medication_form').each(function () {
            countModi++;
            jQuery(this).find('.medication_label').remove();
            jQuery('<span class="cg_form_label medication_label">Medication ' + countModi + '</span>').insertBefore(jQuery(this).find('.medication-row'));
        });
    });
    var count = 0;
    jQuery('#myTabs li').not(':last').each(function () {
        count++;

        jQuery(this).find('.provider_label').remove();

        jQuery(this).prepend('<span class="cg_form_label provider_label">Healthcare Provider ' + count + '</span>');
    });
    
                jQuery('#settings-r .med_name').first().trigger('focusout');
}

var lastMedicationDataSync = 0;
function syncMedicationData() {
//    //debugger;
    //make sure last sync was over a second ago
    var d = new Date();
    var t = d.getTime();
    if (t - lastMedicationDataSync > 1000) {
        lastMedicationDataSync = t;

        var has_meds = false;
        var medication_data = [];
        for (i = 1; i <= parseInt(jQuery('#settings-r .med-data').length / 4); i++) {
            m_medication = jQuery("#settings-r input[name='medication_name[" + i + "]']").val();
            m_strength = jQuery("#settings-r input[name='medication_strength[" + i + "]']").val();
            m_frequency = jQuery("#settings-r input[name='medication_frequency[" + i + "]']").val();
            m_doctor = jQuery("#settings-r input[name='medication_doctor[" + i + "]']").val();
            //if there is any info on this line, then check if we have all the required information
//            //debugger;
            if (m_medication || m_strength || m_frequency || m_doctor) {
                validMed = (m_doctor && m_doctor > 0 && m_doctor != '' && m_medication && isAscii(m_medication) && m_strength && isAscii(m_strength) && m_frequency && isAscii(m_frequency)) ? true : false;
                if (validMed) {
                    medication_data[i] = {
                        'name': m_medication,
                        'strength': m_strength,
                        'frequency': m_frequency,
                        'provider': m_doctor
                    };

                    has_meds = true;
                }
            }
        }

        if (has_meds) {
            jQuery.post("_sync.php", {
                type: 'medication',
                data: medication_data
            }).done(function (response) {
                //console.log(response);
            });
        }
    }
}

function getUserSecurityQuestion(e) {
    email_value = jQuery("input#email_address").val();
    if (email_value != "") {
        jQuery.post("_ajax_request.php", {action: "get_security_question", data: {email: email_value}})
                .done(function (response) {
                    response = JSON.parse(response);
                    if (response.success == 1) {
                        jQuery("select#security_question").val(response.question);
                        jQuery('label[for="' + jQuery("select#security_question").attr('id') + '"]').toggleClass('active', true);
                        jQuery("select#security_question").toggleClass('not-empty', true);
                    } else if (jQuery("select#security_question").val() != "") {
                        jQuery("select#security_question").val("");
                    }
                });
    }
}

function showTooltipsIcons() {
    if (jQuery(this).data("hint") != "") {
        jQuery("<a class='tooltip-icon'></a>").attr('data-tooltip', jQuery(this).data('hint')).hover(showTooltipBox, hideTooltipBox).insertAfter(jQuery(this));
    }
}

// To show tooltip text instead of icon
function showTooltipsText() {
    if (jQuery(this).data("hint") != "" && jQuery(this).data("text") != '') {
        var sty = (jQuery(this).data("text") == 'hidetext') ? "style='padding:0px;margin-top:-10px;'" : "style='padding: 10px 0;'";
        var style = (jQuery(this).data("text") == 'hidetext') ? "style='margin-top:-40px;left:auto;padding:12px 34px;opacity:0;'" : "style=''";
        //jQuery("<div "+sty+"><a class='tooltip-text' "+style+">"
        jQuery("<a class='tooltip-text' " + style + ">" + jQuery(this).data("text") + "</a>").attr('data-tooltip', jQuery(this).data('hint')).hover(showTooltipBox, hideTooltipBox).insertAfter(jQuery(this));
    }
}

function showTooltipBox(e) {
    if (jQuery(this).data("tooltip") != "") {
        if (jQuery(this).data("tooltip").substring(0, 7) == 'images/') {
            tooltipHTML = "<div class='tooltip-new'><img src='" + jQuery(this).data("tooltip") + "' class='tooltip-img' /></div>";
        } else {
            tooltipHTML = "<div class='tooltip-new'>" + jQuery(this).data("tooltip") + "</div>";
        }

        if (jQuery("#register_form").length > 0) {
            jQuery("#register_form").append(tooltipHTML);
        } else {
            jQuery(this).parent().append(tooltipHTML);
        }

        pos = (jQuery(this).hasClass('tooltip-icon')) ? jQuery(this).parent().position() : jQuery(this).position();
        width = (jQuery(this).hasClass('tooltip-icon')) ? jQuery(this).parent().width() : jQuery(this).width();
        height = (jQuery(this).hasClass('tooltip-icon')) ? jQuery(this).parent().height() : jQuery(this).height();
        max_width = jQuery('body').width() - pos.left - width - 15;
        topVal = ((jQuery(this).hasClass('tooltip-icon')) ? pos.top : (pos.top - 10));

        // for tooltips with text label
        pos = (jQuery(this).hasClass('tooltip-text')) ? jQuery(this).parent().position() : pos;
        width = (jQuery(this).hasClass('tooltip-text')) ? jQuery(this).parent().width() : width;
        height = (jQuery(this).hasClass('tooltip-text')) ? jQuery(this).parent().height() + parseInt(10) : height;
        topVal = (jQuery(this).hasClass('tooltip-text')) ? pos.top : topVal;

        leftVal = pos.left;
        topVal = parseInt(topVal) + parseInt(parseInt(height) - parseInt(15));
        width = (width < 50) ? parseInt(300) + parseInt(width) : width;

        if (jQuery(this).data("tooltip").substring(0, 7) == 'images/') {
            topVal += 50;
        }

        if (jQuery(window).width() < 1024) {
            //mobile
            topVal = (jQuery(this).hasClass('tooltip-icon')) ? pos.top + 50 : pos.top + height + 30;
            leftVal = (jQuery(this).hasClass('tooltip-icon') || jQuery(this).hasClass('tooltip-text')) ? pos.left : 0;
            max_width = jQuery('body').width() - (pos.left * 2) - 15;

            if (jQuery(this).hasClass('tooltip-text')) {
                topVal = parseInt(topVal) + parseInt(0);
            }
        } else {
            if (jQuery(this).hasClass('tooltip-text')) {
                topVal = parseInt(topVal) + parseInt(30);
            }
        }


        if (jQuery(this).hasClass('medication_list_tt')) {
            if (jQuery(window).width() < 768) {
                leftVal = jQuery('.medication_list').offset().left;
            }
            if (jQuery(window).width() >= 768 && jQuery(window).width() < 1024) {
                leftVal = jQuery('.medication_list').offset().left + parseInt(300);
            }
            if (jQuery(window).width() <= 1024) {
                topVal = topVal + parseInt(50);
            }
        }

        // if it is one My-account page
        if (jQuery(this).hasClass('homeBox')) {
            topVal = pos.top + height + 80;
        }

        if (jQuery(this).text() == 'hidetext') {
            console.log(jQuery(this).parents('.safe-enroll').position());
            var v = (jQuery('#fmRegister').length > 0) ? jQuery(this).parent().position().top + parseInt(50) : jQuery(this).parents('.safe-enroll').position().top - parseInt(300);
            topVal = v;
        }

        jQuery(".tooltip-new")
                .css("top", topVal + "px")
                .css("left", leftVal + "px")
                //.css("max-width", max_width + "px")
                .css("width", width + "px")
                .fadeIn("fast");

        if (jQuery(this).data("tooltip").substring(0, 7) == 'images/') {
            if (jQuery(window).width() <= 600) {
                jQuery(".tooltip-new").css("width", "88%").css("left", "6%");
            } else {
                jQuery(".tooltip-new").css("width", "auto").css("left", jQuery(this).position().left - 200 + 'px');
            }
        }
    }
}

function hideTooltipBox() {
    if (jQuery(this).data("tooltip") != "") {
        //jQuery(".tooltip-new").remove();
    }
}

var geolocate_patient;
var geolocate_payment;
var geolocate_doctor = new Array;
var geolocate_options = {componentRestrictions: {country: ["us", "pr"]}};
function initializeGoogleGeoLocation() {
    geolocate_patient = new google.maps.places.Autocomplete(document.getElementById('p_address'), geolocate_options);
    geolocate_patient.addListener('place_changed', function () {
        fillInAddress(geolocate_patient, 'patient', 0);
    });

    jQuery('.dr-address').each(function (index, elem) {
        //dr_key = geolocate_doctor.length;
        rs = /\[([^)]+)\]/.exec(jQuery(elem).attr('name'));
        dr_key = rs[1];

        initializeDoctorAddressElementGeoLocation(elem, dr_key);
        //geolocate_doctor[dr_key] = new google.maps.places.Autocomplete(elem, geolocate_options);
        //geolocate_doctor[dr_key].addListener('place_changed', function () {fillInAddress(geolocate_doctor[dr_key], 'doctor', dr_key);});
    })
}
function initializeGoogleGeoLocationPayment() {
    geolocate_payment = new google.maps.places.Autocomplete(document.getElementById('p_cc_address'), geolocate_options);
    geolocate_payment.addListener('place_changed', function () {
        fillInAddress(geolocate_payment, 'payment', 0);
    });

    jQuery('.dr-address').each(function (index, elem) {
        //dr_key = geolocate_doctor.length;
        rs = /\[([^)]+)\]/.exec(jQuery(elem).attr('name'));
        dr_key = rs[1];

        initializeDoctorAddressElementGeoLocation(elem, dr_key);
        //geolocate_doctor[dr_key] = new google.maps.places.Autocomplete(elem, geolocate_options);
        //geolocate_doctor[dr_key].addListener('place_changed', function () {fillInAddress(geolocate_doctor[dr_key], 'doctor', dr_key);});
    })
}

function initializeDoctorAddressElementGeoLocation(elem, dr_key) {
    geolocate_doctor[dr_key] = new google.maps.places.Autocomplete(elem, geolocate_options);
    geolocate_doctor[dr_key].addListener('place_changed', function () {
        fillInAddress(geolocate_doctor[dr_key], 'doctor', dr_key);
    });
}

function fillInAddress(geolocate, type, el_key) {
    // Get the place details from the geolocate object.
    var place = geolocate.getPlace();

    address_value = '';
    address2_value = '';
    city_value = '';
    state_value = '';
    zip_value = '';
    country_value = 'US';

    if (typeof place.address_components !== undefined) {
        // Get each component of the address from the place details and fill the corresponding field on the form.
        for (var i = 0; i < place.address_components.length; i++) {
            address_val = place.address_components[i]['short_name'];

            switch (place.address_components[i].types[0]) {
                case 'street_number':
                    address_value = address_val;
                    address2_value = address_val;
                    break;

                case 'route':
                    address_value = address_value + ' ' + address_val;
                    break;

                case 'locality':
                    city_value = address_val;
                    break;

                case 'administrative_area_level_1':
                    state_value = address_val;
                    break;

                case 'country':
                    country_value = address_val;
                    break;

                case 'postal_code':
                    zip_value = address_val;
                    break;
            }
            if (country_value != 'US') {
                city_value = (city_value == '') ? state_value : city_value;
                state_value = country_value;
            }

            if (type == 'payment') {
                patient_new_address_selected = true;
                jQuery('input[name="p_cc_address"], input[id="p_cc_address"]').val(address_value).trigger('blur');
                jQuery('input[name="p_cc_city"], input[id="p_cc_city"]').val(city_value).trigger('blur');
                jQuery('select[name="p_cc_state"], select[id="p_cc_state"]').val(state_value).trigger('change').trigger('blur');
                jQuery('input[name="p_cc_zip"], input[id="p_cc_zip"]').val(zip_value).trigger('blur');
                jQuery('input[name="p_cc_address2"], input[id="p_cc_address2"]').focus();
            }

            if (type == 'patient') {
                patient_new_address_selected = true;
                jQuery('input[name="p_address"], input[id="p_address"]').val(address_value).trigger('blur');
                jQuery('input[name="p_city"], input[id="p_city"]').val(city_value).trigger('blur');
                jQuery('select[name="p_state"], select[id="p_state"]').val(state_value).trigger('change').trigger('blur');
                jQuery('input[name="p_zip"], input[id="p_zipcode"]').val(zip_value).trigger('blur');
                jQuery('input[name="p_address2"], input[id="p_address2"]').focus();
            }

            if (type == 'doctor') {
                doctor_new_address_selected = true;
                jQuery('input[name="doctor_address[' + el_key + ']"]').val(address_value).trigger('blur');
                jQuery('input[name="doctor_city[' + el_key + ']"]').val(city_value).trigger('blur');
                jQuery('select[name="doctor_state[' + el_key + ']"]').val(state_value).trigger('change').trigger('blur');
                jQuery('input[name="doctor_zip[' + el_key + ']"]').val(zip_value).trigger('blur');
                jQuery('input[name="doctor_address2[' + el_key + ']"]').focus();
            }
        }
    }
}

function detectCardType(cardNum) {
    var payCardType = "";
    var regexMap = [
        {regEx: /^4[0-9]{5}/ig, cardType: "Visa"},
        {regEx: /^5[1-5][0-9]{4}/ig, cardType: "Mastercard"},
        {regEx: /^3[47][0-9]{3}/ig, cardType: "American Express"},
        {regEx: /^6(?:011|5[0-9]{2})[0-9]{3,}/ig, cardType: "Discover"}
    ];

    for (var j = 0; j < regexMap.length; j++) {
        if (cardNum.match(regexMap[j].regEx)) {
            payCardType = regexMap[j].cardType;
            break;
        }
    }

    return payCardType;
}

function updateCardType() {
    ccType = detectCardType(jQuery(this).val());
    jQuery('input[name="p_cc_type"]').val(ccType);

    //apply logo on the cc number field
    ccClass = '';
    switch (ccType) {
        case "Visa":
            ccClass = 'cc_visa_no';
            break;
        case "Mastercard":
            ccClass = 'cc_mastercard_no';
            break;
        case "American Express":
            ccClass = 'cc_amex_no';
            break;
        case "Discover":
            ccClass = 'cc_discover_no';
            break;
    }

    jQuery('.cc-type-icon').remove();
    if (ccClass != '') {
        jQuery("<div class='cc-type-icon " + ccClass + "'></div>").insertAfter(jQuery(this));
    }
}

function adjustScroll() {
    //if (first_invalid_element.prop('nodeName') == 'INPUT' && (first_invalid_element.attr('type') == 'radio' || first_invalid_element.attr('type') == 'checkbox')) {
    var y = jQuery(window).scrollTop();  //your current y position on the page
    jQuery(window).scrollTop(y - 50);
    //}

    //adjust scroll for IE/Edge browsers
    if (document.documentMode || /Edge/.test(navigator.userAgent)) {
        jQuery(window).scrollTop(y - 200);
    }
}

function showTitle() {
    if (jQuery(window).width() < 767) {
        jQuery('#showtitle').remove();
        jQuery('<div id="showtitle">' + jQuery('#s_t').attr('title') + '</div>').insertAfter(jQuery('#s_t'));
        jQuery('#showtitle').fadeOut(10000);
    }
}
function showMedicationList() {
    //debugger;
    jQuery('.m-list.first-child-section').remove();

    jQuery('#settings-r .med_name').each(function () {
        var cur_med_name = jQuery(this).val().trim();
        if (!jQuery(this).closest('.cg_medication_form').length) {
            return;
        }
        if (!jQuery(this).closest('.cg_medication_form').attr('id')) {
            return;
        }
        jQuery(jQuery(this).closest('.cg_medication_form'));
        var cur_med_id = (jQuery(this).closest('.cg_medication_form').attr('id')).replace('mb', '');
        cur_med_id = jQuery.trim(cur_med_id);

        var ct_med = '';
        var ct_med_s = '';
        var ct_med_txt = '';

        if (cur_med_id > 0 && jQuery('#tr' + cur_med_id).length > 0) {
            //Get updated meds price
            var med_price = jQuery('#patient_updated_price').val();
            //Get updated meds price 
            if (cur_med_name != '') {
                jQuery('#tr' + cur_med_id).html('<td>' + cur_med_name + '</td><td>$' + med_price + '.00</td>');
            } else {
                if (jQuery('#settings-r .cg_medication_form').length > 1) {
                    ct_med = 'all <strong>' + jQuery('#settings-r .cg_medication_form').length + '</strong> of';
                    ct_med_s = 's';
                    ct_med_txt = jQuery('#settings-r .cg_medication_form').length + ' medications';
                } else if (jQuery('#settings-r .cg_medication_form').length == 1) {
                    ct_med_txt = '1 medication';
                }
                jQuery('.ct_med_s').html(ct_med_s);
                jQuery('#med-list-box .total_str, #med_info .total_str').html('$' + (jQuery("#settings-r .cg_medication_form").length) * med_price + '.00');
                //var ct_med = (jQuery('.m-list').length>1) ? ' '+jQuery('.m-list').length : '';
                jQuery('#med-list-box .ct_med, #med_info .ct_med').html(ct_med);
                //var ct_med_txt= (jQuery('.m-list').length>1) ? jQuery('.m-list').length+' medications' : (jQuery('.m-list').length==1) ? '1 medication' : '';
                jQuery('#med_info .ct_med_txt').html(ct_med_txt);
                jQuery('#tr' + cur_med_id).remove();
            }
        } else {
            if (cur_med_name != '' && cur_med_id > 0) {
                //Get updated meds price
                var med_price = jQuery('#patient_updated_price').val();
                //Get updated meds price 
                jQuery('<tr class="m-list first-child-section" id="tr' + cur_med_id + '"><td>' + cur_med_name + '</td><td>$' + med_price + '.00</td></tr>').insertBefore(jQuery('#med_list').find('.total'));
                if (jQuery('#settings-r .cg_medication_form').length > 1) {
                    ct_med = 'all <strong>' + jQuery('#settings-r .cg_medication_form').length + '</strong> of';
                    ct_med_s = 's';
                    ct_med_txt = jQuery('#settings-r .cg_medication_form').length + ' medications';
                } else if (jQuery('#settings-r .cg_medication_form').length == 1) {
                    ct_med_txt = '1 medication';
                }
                jQuery('.ct_med_s').html(ct_med_s);
                jQuery('#med-list-box .total_str, #med_info .total_str').html('$' + (jQuery('#settings-r .cg_medication_form').length) * med_price + '.00');
                //var ct_med = (jQuery('.m-list').length>1) ? ' '+jQuery('.m-list').length : '';
                jQuery('#med-list-box .ct_med, #med_info .ct_med').html(ct_med);
                //var ct_med_txt= (jQuery('.m-list').length>1) ? jQuery('.m-list').length+' medications' : (jQuery('.m-list').length==1) ? '1 medication' : '';
                jQuery('#med_info .ct_med_txt').html(ct_med_txt);
            }
        }

        // show only if any row is filled with values
        if (jQuery('.m-list.first-child-section').length > 0) {
//        if (jQuery('#settings-r .cg_medication_form').length > 0) {
            jQuery('#med-list-box').show();
            jQuery('#med_info').show();
        }
    });
}

function checkSSN() {
    var fieldVal = jQuery('input[name="p_ssn"]').val();
    if (fieldVal != '') {
        jQuery.post("_sync.php", {key: 'p_ssn', value: fieldVal, type: 'patient'})
                .done(function (response) {
                    result = JSON.parse(response);
                    if (result.success == 0) {
                        var err_msg = 'This social security number is already in use with a different email. Please try to log into your account with the correct email or contact customer support at 1-877-296-4673 for assistance.';
                        if (result.p_email != '') {
                            err_msg = 'This social security number is already in use with the following email ' + result.p_email + '. Please try to log into your account with the correct email or contact customer support at 1-877-296-4673 for assistance.';
                        }
                        jQuery('label[for="' + jQuery("input[name='p_ssn']").attr('id') + '"].error').remove();
                        jQueryValidation_Highlight(jQuery('input[name="p_ssn"]'));
                        jQueryValidation_ShowErrors(jQuery("<label>").attr('for', jQuery("input[name='p_ssn']").attr('id')).addClass('error').text(err_msg), jQuery("input[name='p_ssn']"));
                        if (!jQuery("input[name='p_ssn']").prevAll('input[type="text"]').hasClass('error')) {
                            jQuery('html, body').animate({
                                scrollTop: (jQuery("input[name='p_ssn']").offset().top - parseInt(200))
                            }, 500);
                        }
                        return false;
                    } else {
                        // add correct class from the input field
                        jQuery("input[name='p_ssn']").addClass('correct');
                        return true;
                    }
                });
    }
}

function patientReapply(id, eaddr) {
    $.post("_ajax_request.php", {action: 'reapply', type: 'denied', pid: id, email: eaddr})
            .done(function (data) {
                data = $.parseJSON(data);
                if (data.success) {
                    window.location.href = '/html/enrollment/enroll.php';
                } else {
                    alert(data.msg);
                }
            });
}
$('#bSubmit').prop('disabled', true);
$(document).ajaxStop(function () {
    $('#bSubmit').prop('disabled', false);
});
// Get and set Healthcare Provider Information on Header 
$(function () {

    jQuery('body').on('blur', ".doctor-lname-fields", function () {
        var last_name_val = $(this).val();
        var get_name_val = $(this).attr('name');
        var get_class = $(this).attr('class');
        var get_id = get_name_val.replace(/[^0-9]/g, '');
        var set_id = 'pb' + get_id;
        console.log('Last namr', last_name_val, 'Name value', get_name_val);
        if ($(".doctor_facility-" + get_id).val().length != 0) {
            $(".doctor_facility-" + get_id).removeClass("correct not-empty");
        }
        healthCareProviderNameHeader(get_id, set_id, 'doctor_last_name');
    });

    jQuery('body').on('blur', ".med_name", function () {
        var last_name_med_val = $(this).val();
        var get_name_med_val = $(this).attr('name');

        var get_med_id = get_name_med_val.replace(/[^0-9]/g, '');
        var set_med_id = 'mb' + get_med_id;
        healthCareProviderNameHeader(get_med_id, set_med_id, 'medication_name');
    });

    //For the first tab health provider tab
    jQuery('input[name="doctor_last_name[1]"]').blur(function () {
        healthCareProviderNameHeader(1, 'pb1', 'doctor_last_name');
    });
    jQuery('input[name="medication_name[1]"]').blur(function () {
        healthCareProviderNameHeader(1, 'mb1', 'medication_name');
    });
});

// To display healthcare provide name
function healthCareProviderNameHeader(dr_id, set_id, set_name) {
    console.log('Docot id  ::', dr_id);
    console.log('Set id  ::', set_id);
    if (set_name == 'doctor_last_name') {
        new_set_name = 'Dr. ';
    } else {
        new_set_name = '';
    }
    var tabVal = $('input[name="' + set_name + '[' + dr_id + ']"]').val();
    new_str = tabVal.toLowerCase().replace(/\b[a-z]/g, function (txtVal) {
        return txtVal.toUpperCase();
    });
    var res = truncateMedicationName(new_str, 6);
    console.log('Response: meds', res);
    if (res != '') {
        jQuery('a[href=#' + set_id + ']').text(new_set_name + res);
    } else {
        jQuery('a[href=#' + set_id + ']').text('New');
    }
}

function checkTabErrors() {
    console.log('Checking the function.');
    //var tab_no = $(this).data("step-enroll");
    //console.log('tab_no',tab_no);
    var old_tab = 1;
    var f_count = 0;
    $("#register_form [class*='patient-enroll-progress-']").each(function () {
        var cls_no = $(this).data("step-enroll");

        if (old_tab != cls_no) {
            if (f_count == 0) {
                //console.log('f_count first', f_count);
                $("#patient-enroll-progress_" + old_tab).removeClass("alert-danger");
            }
            f_count = 0;
            old_tab = cls_no;
        }

        // if(cls_no == tab_no)
        // {
        // 	return false; // breaks
        // }
        var e_err = 0;
        if ($(this).attr("type") == "text")
        {
            if ($(this).val().trim() == "")
            {
                e_err++;
            }
        }

        // if($(this).attr("type") == "hidden" && $(this).data("h-name") == "gender_h")
        // {
        // 	if($("#p_gender_m").prop("checked") == false && $("#p_gender_f").prop("checked") == false)
        // 	{					
        // 		e_err++;
        // 	}
        // }
        if ($(this).is('select'))
        {
            console.log('For select option');
            if ($(this).val().trim() == "")
            {
                e_err++;
            }
        }
        //console.log('This ki value : ',this);
        if ($(this).attr("type") == "checkbox")
        {
            if ($(this).prop("checked") != true)
            {
                e_err++;
            }
        }

        if ($(this).attr("type") == "hidden")
        {
            let fc = $(this).data("f-count");
            var ec = 0;
            for (let i = 1; i <= fc; i++)
            {
                let fid = $(this).data("fl_" + i);
                if ($("#" + fid).prop("checked") == true)
                {
                    ec++;
                }
            }
            if (ec == 0) {
                e_err++;
            }
        }

        //  if($(this).attr("type") == "hidden" && $(this).data("htype") == "hidden")
        // {

        // }

        if (e_err > 0)
        {
            if (!$("#patient-enroll-progress_" + cls_no).hasClass("alert-danger")) {
                $("#patient-enroll-progress_" + cls_no).addClass("alert-danger");
            }
            f_count++;
            console.log('f_count', f_count);
        }
    });
    if (!$("#enroll-progress li").hasClass("alert-danger")) {
        $("#final-submit").show();// Show submit button
    } else {
        $("#final-submit").hide();
    }
}
//To truncate value for medication provider tab	
function truncateMedicationName(source, size) {
    return source.length > size ? source.slice(0, size - 1) + "…" : source;
}


function checkErrorForMedication()
{
    jQuery('.doctors_dropdown').each(function (index, elem) {
        var parentElnumber = jQuery(this).attr('name').match(/\d+/);
        var medsName = $(this).find(":selected").text();
        var isValid = false;
        console.log('parentElName :', parentElnumber[0]);
        if (medsName != '') {
            isValid = true;
        } else {
            $("##settings-r .medication-row").removeClass("in active"); //Remove active class from all the div and add to cy=urrent one 
            $("#mb" + parentElnumber[0]).addClass('in active');

            $("#myTabsMedication li").removeClass("active");
            jQuery('a[href=#mb' + parentElnumber[0] + ']').parent('li').addClass('active');
            console.log('Stop it');

            //return false;
        }
    });
}