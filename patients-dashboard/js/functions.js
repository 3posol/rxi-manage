jQuery.fn.center = function () {
    this.css("position","fixed");
    this.css("top", (jQuery(window).height() / 2) - (this.outerHeight() / 2));
    this.css("left", (jQuery(window).width() / 2) - (this.outerWidth() / 2));
    return this;
}

function showTooltipBox () {
	if (jQuery(this).data("tooltip") != "") {
		if (jQuery(this).data("tooltip").substring(0, 7) == 'images/') {
			tooltipHTML = "<div class='tooltip-new'><img src='"+ jQuery(this).data("tooltip") +"' class='tooltip-img' /></div>";
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
		//height = jQuery(".tooltip-new:visible").eq(0).outerHeight(true);

		topVal = ((jQuery(this).hasClass('tooltip-icon')) ? pos.top : (pos.top - 10));
		leftVal = pos.left + width + 15;
		if (jQuery(window).width() <= 1024) {
			//mobile
			topVal = (jQuery(this).hasClass('tooltip-icon')) ? pos.top + 50 : pos.top + height + 30;
			leftVal = (jQuery(this).hasClass('tooltip-icon')) ? pos.left : 0;
			max_width = jQuery('body').width() - (pos.left * 2) - 15;
		}

		jQuery(".tooltip-new")
			.css("top", topVal + "px")
			.css("left", leftVal + "px")
			.css("max-width", max_width + "px")
			.fadeIn("fast");

		if (jQuery(this).data("tooltip").substring(0, 7) == 'images/') {
			jQuery(".tooltip-new").css("width", "auto");
		}
	}
}

function hideTooltipBox () {
	if (jQuery(this).data("tooltip") != "") {
		jQuery(".tooltip-new").remove();
	}
}
