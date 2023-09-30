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
})