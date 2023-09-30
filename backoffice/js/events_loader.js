jQuery().ready(function() {
	//login submit
	jQuery('#fmLogin').submit(function (e) {
		var valid_form = true;

		jQuery('input').each(function() {
			if (jQuery(this).val() == '') {
				valid_form = false;

				jQuery('label[for="' + jQuery(this).attr('id') + '"]').addClass('error');
				jQuery(this).addClass('error');
			} else {
				jQuery('label[for="' + jQuery(this).attr('id') + '"]').removeClass('error');
				jQuery(this).removeClass('error');
			}
		});

		if (!valid_form) {
			e.preventDefault();

			jQuery('#fmMsg').html('Please enter a valid email address and a password.<br/><br/>')
			jQuery('#fmMsg').addClass('error');
		} else {
			jQuery('#fmMsg').html('')
			jQuery('#fmMsg').removeClass('error');
		}
	});

	//forgot password submit
	jQuery('#fmForgotPassword').submit(function (e) {
		var valid_form = true;

		jQuery('input').each(function() {
			if (jQuery(this).val() == '') {
				valid_form = false;

				jQuery('label[for="' + jQuery(this).attr('id') + '"]').addClass('error');
				jQuery(this).addClass('error');
			} else {
				jQuery('label[for="' + jQuery(this).attr('id') + '"]').removeClass('error');
				jQuery(this).removeClass('error');
			}

		});

		if (!valid_form) {
			e.preventDefault();

			jQuery('#fmMsg').html('Please enter a valid email address.<br/><br/>')
			jQuery('#fmMsg').addClass('error');
		} else {
			jQuery('#fmMsg').html('')
			jQuery('#fmMsg').removeClass('error');
		}
	});

	//change password submit
	jQuery('#fmChangePassword').submit(function (e) {
		var valid_form = true;

		jQuery('input').each(function() {
			if (jQuery(this).val() == '') {
				valid_form = false;

				jQuery('label[for="' + jQuery(this).attr('id') + '"]').addClass('error');
				jQuery(this).addClass('error');
			} else {
				jQuery('label[for="' + jQuery(this).attr('id') + '"]').removeClass('error');
				jQuery(this).removeClass('error');
			}
		});

		if (valid_form && jQuery('input[name="new_password"]').val() != jQuery('input[name="new_password_confirm"]').val()) {
			valid_form = false;
		}

		if (!valid_form) {
			e.preventDefault();

			jQuery('#fmMsg').html('Please enter a valid password.<br/><br/>')
			jQuery('#fmMsg').addClass('error');
		} else {
			jQuery('#fmMsg').html('')
			jQuery('#fmMsg').removeClass('error');
		}
	});

	//tooltips
	jQuery("a[rel]").hover(
		function(e) {
			if (jQuery(this).attr("rel") != "") {
				pos = jQuery(this).position();

				if (jQuery(this).attr("rel").substring(0, 7) == 'images/') {
					jQuery("body").append("<p class='tooltip'><img src='"+ jQuery(this).attr("rel") +"' /></p>");
				} else {
					jQuery("body").append("<p class='tooltip'>" + jQuery(this).attr("rel") + "</p>");
				}

				jQuery(".tooltip")
					.css("top", (pos.top + 25) + "px")
					.css("left", (pos.left - 400) + "px");
			}
		},
		function() {
			if (jQuery(this).attr("rel") != "") {
				jQuery(".tooltip").remove();
			}
		}
	);
	//
	jQuery("a[rel]").click(function(e) {
		e.preventDefault();
	});

})