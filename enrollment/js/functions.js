jQuery.fn.center = function () {
    this.css("position","fixed");
    this.css("top", (jQuery(window).height() / 2) - (this.outerHeight() / 2));
    this.css("left", (jQuery(window).width() / 2) - (this.outerWidth() / 2));
    return this;
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
