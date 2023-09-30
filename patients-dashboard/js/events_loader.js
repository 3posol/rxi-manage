jQuery().ready(function() {
	/* Sticky footer code */
	/*
	jQuery(window).on('load resize scroll', function() {
	    var f = jQuery('#footer-wrapper');
	    f.css({position:'static'});
	    if (f.offset().top + f.height() < jQuery(window).height()) {
	        f.css({position:'fixed', bottom:'0', width:'100%'});
	    }
	});
	*/

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

			jQuery('#fmMsg').html('Please try again after you fill out the login form.')
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

		jQuery('select').each(function() {
			if (!valid_form) {
				jQuery('label[for="' + jQuery(this).attr('id') + '"]').addClass('error');
				jQuery(this).addClass('error');
			}
		});

		if (!valid_form) {
			e.preventDefault();

			jQuery('#fmMsg').html('Please enter a valid email address.<br/><br/>')
			jQuery('#fmMsg').addClass('error');
		} else {
			jQuery('#fmMsg').html('');
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
			jQuery('#fmMsg').removeClass('success');
			jQuery('#fmMsg').addClass('error');
		} else {
			jQuery('#fmMsg').html('')
			jQuery('#fmMsg').removeClass('error');
		}
	});

	//enable password strength plugin
	$('#password').pwstrength({
		common: {
	        usernameField: "#email_address"
		},

		rules: {
		    activated: {
		        wordTwoCharacterClasses: true,
		        wordRepetitions: true
		    }
		},

		ui: {
			showVerdictsInsideProgressBar: true,
			progressBarMinPercentage: 10
		}
    });

	//de-activate dashboard links
	jQuery('.stop-link').click(function (e) {
		e.preventDefault();
	})
	//jQuery('a.homeBox').click(function (e) {
	//	if(jQuery('a.homeBox:first').hasClass('homeWarningBox') && jQuery(this).attr('href') != 'account.php') {
	//		e.preventDefault();
	//	}
	//});

	//bill pay
	jQuery('a.pay-bill').click(function (e) {
		e.preventDefault();

		jQuery('#payConfirm').show();
		jQuery('#payConfirm .popup-content').center();

		//
		jQuery('#payConfirm a#bt-ok').html('Submit').attr('invoice', jQuery(this).attr('invoice')).attr('amount', jQuery(this).attr('amount')).show();
		jQuery('#payConfirm a#bt-cancel').html('Cancel').show();

		//update popup message
		if (jQuery(this).attr('payment-method') != "") {
			jQuery('#payConfirm p#popup-text').html('You are about to make a payment for <strong>$</strong><span id="amount-placeholder" class="bold"></span> with <span id="payment-method-placeholder" class="bold"></span>. If you want to use a different payment method, you must call a patient advocate to update your monthly recurring payment option. Please note, Prescription Hope is not responsible for any fees you may incur by your bank when providing payment. Are you sure you want to submit your payment?<br><br><a href="pay_billcc.php?invoice=' + jQuery(this).attr('invoice') + '" class="medium-button">Use A Different Card</a>');
			jQuery('#payConfirm #amount-placeholder').html(jQuery(this).attr('amount'));
			jQuery('#payConfirm #payment-method-placeholder').html(jQuery(this).attr('payment-method'));
		} else {
			jQuery('#payConfirm a#bt-ok').hide();
			jQuery('#payConfirm p#popup-text').html('We have no payment method on file for you. In order to make a payment for <strong>$</strong><span id="amount-placeholder" class="bold"></span> you must use the "Use A Different Card" option, or you must call a patient advocate to update your monthly recurring payment option. Please note, Prescription Hope is not responsible for any fees you may incur by your bank when providing payment.<br><br><br><a href="pay_billcc.php?invoice=' + jQuery(this).attr('invoice') + '" class="big-button">Use A Different Card</a>');
			jQuery('#payConfirm #amount-placeholder').html(jQuery(this).attr('amount'));
		}
	});

	//bill pay - submit
	jQuery('#payConfirm a#bt-ok').click(function (e) {
		if (jQuery(this).html() == 'Submit') {
			e.preventDefault();

			jQuery('#payConfirm #popup-text').html('Payment processing, do not close your browser or refresh the page until you receive confirmation of payment.');
			jQuery('#payConfirm p#popup-buttons').hide();
			jQuery('#payConfirm .popup-content').center();

			var invoice_id = jQuery(this).attr('invoice');
			var invoice_amount = jQuery(this).attr('amount');

			jQuery.ajax({
				method: 'POST',
				url: 'pay_bill.php',
				data: {invoice: invoice_id, amount: invoice_amount},

				success: function(data) {
					response = JSON.parse(data);
					if (response.success == 1) {
						jQuery('#payConfirm #popup-text').html('<h3>Payment Confirmation</h3><br/>Date and Time of transaction: <strong>' + response.datetime + '</strong><br/>Transaction Amount: <strong>$' + response.amount + '</strong><br/>Invoice Number: <strong>' + response.invoice + '</strong>');
						jQuery('#payConfirm a#bt-ok').html('Print');
						jQuery('#payConfirm a#bt-cancel').html('Close').attr('refresh', '1');
						jQuery('#payConfirm p#popup-buttons').show();
						jQuery('#payConfirm .popup-content').center();
					} else {
						jQuery('#payConfirm #popup-text').html('<h3 class="error">Payment Failed</h3><br/>Please call a patient advocate as soon as possible to see what can be done.');
						jQuery('#payConfirm a#bt-ok').hide();
						jQuery('#payConfirm a#bt-cancel').html('Close');
						jQuery('#payConfirm p#popup-buttons').show();
						jQuery('#payConfirm .popup-content').center();
					}
				},

				error: function() {
					jQuery('#payConfirm #popup-text').html('<h3 class="error">Payment Failed</h3><br/>Please call a patient advocate as soon as possible to see what can be done.');
					jQuery('#payConfirm a#bt-ok').hide();
					jQuery('#payConfirm a#bt-cancel').html('Close');
					jQuery('#payConfirm p#popup-buttons').show();
					jQuery('#payConfirm .popup-content').center();
				}
			});
		} else if (jQuery(this).html() == 'Print') {
			window.print();
		}
	});

	//bill pay - close popup
	jQuery('#payConfirm a#bt-cancel').click(function (e) {
		e.preventDefault();

		do_refresh = (jQuery(this).attr('refresh') !== undefined);
		jQuery('#payConfirm').hide();

		if (do_refresh) {
			window.location.reload();
		}
	});

	//tooltips
	jQuery("a[rel]").hover(
		function(e) {
			if (jQuery(this).attr("rel") != "") {
				pos = jQuery(this).position();

				if (jQuery(this).attr("rel").substring(0, 7) == 'images/') {
					jQuery("body").append("<div class='tooltip'><img src='"+ jQuery(this).attr("rel") +"' /></div>");
				} else {
					jQuery("body").append("<div class='tooltip'>" + jQuery(this).attr("rel") + "</div>");
				}

				jQuery(".tooltip")
					.css("top", (pos.top + 25) + "px")
					.css("left", (pos.left) + "px");
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

	//new tooltips
	jQuery("a[data-tooltip]").hover(showTooltipBox, hideTooltipBox);
	jQuery("a[data-tooltip]").click(function(e) {e.preventDefault();});

})