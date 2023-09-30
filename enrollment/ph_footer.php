<?php
	//
	//SalesForce Conversion Tracking
	//
	if (basename($_SERVER['PHP_SELF']) == 'register.php' || basename($_SERVER['PHP_SELF']) == 'enroll.php') {
		$page_id = '';
		$page_alias = '';
		if (basename($_SERVER['PHP_SELF']) == 'register.php') {
			$page_id = '2';
			$page_alias = 'Create Account Page';
		} elseif(basename($_SERVER['PHP_SELF']) == 'enroll.php') {
			$page_id = '3';
			$page_alias = 'Create Account - Incomplete Application';
		}

		$SFJobID = (isset($_COOKIE['SFJobID'])) ? $_COOKIE['SFJobID'] : '';
		$SFSubscriberID = (isset($_COOKIE['SFSubscriberID'])) ? $_COOKIE['SFSubscriberID'] : '';
		$SFListID = (isset($_COOKIE['SFListID'])) ? $_COOKIE['SFListID'] : '';
		$SFUrlID = (isset($_COOKIE['SFUrlID'])) ? $_COOKIE['SFUrlID'] : '';
		$SFMemberID = (isset($_COOKIE['SFMemberID'])) ? $_COOKIE['SFMemberID'] : '';
		$SFJobBatchID = (isset($_COOKIE['SFJobBatchID'])) ? $_COOKIE['SFJobBatchID'] : '';

		if ($SFJobID != '' && $SFSubscriberID != '' && $SFListID != '' && $SFUrlID != '' && $SFMemberID != '') {
			$SFConversionPixel = '<img src=\'http://click.exacttarget.com/conversion.aspx?xml=';
			$SFConversionPixel .= '<system><system_name>tracking</system_name><action>conversion</action>';
			$SFConversionPixel .= '<member_id>'.$SFMemberID.'</member_id>';
			$SFConversionPixel .= '<job_id>'.$SFJobID.'</job_id>';
			$SFConversionPixel .= '<email></email>';
			$SFConversionPixel .= '<sub_id>'.$SFSubscriberID.'</sub_id>';
			$SFConversionPixel .= '<list>'.$SFListID.'</list>';
			$SFConversionPixel .= '<original_link_id>'.$SFUrlID.'</original_link_id>';
			$SFConversionPixel .= '<BatchID>'.$SFJobBatchID.'</BatchID>';
			$SFConversionPixel .= '<conversion_link_id>'.$page_id.'</conversion_link_id>';
			$SFConversionPixel .= '<link_alias>'.$page_alias.'</link_alias><display_order>'.$page_id.'</display_order>';
			$SFConversionPixel .= '<data_set></data_set>';
			$SFConversionPixel .= '</system>\'';
			$SFConversionPixel .= ' width="1" height="1">';

			echo $SFConversionPixel;
		}
	} ?>
<!-- <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js'></script> -->
</body>

</html>
<script>
$("#toggle").click(function() {
    $(this).toggleClass("on");
    $("#menu").slideToggle();
});
</script>

<div class="container footer m-v-10">
    <div class="col-sm-6 col-xs-12 safe-enroll pull-left">
        <div class="text-safe pull-left">
            <ul class="links">
                <li><a href="https://prescriptionhope.com/privacy-policy/" target="_blank">Privacy Policy</a></li>
                <li><a href="https://prescriptionhope.com/terms-of-service/" target="_blank">Terms of service</a></li>
                <li>
                    <p class="copy"><?php echo date('Y'); ?> ©Prescription Hope, Inc.</p>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-sm-6 col-xs-12 safe-enroll pull-right">
        <div class="text-safe pull-right">
            <img class="padding-right-30 ttip"
                src="https://prescriptionhope.com/wp-content/themes/prescription_theme/images/new-images/256-shield.png"
                data-text="hidetext"
                data-hint="We keep your information safe and protected. It is secured by 256-bit encryption, the same security banks use." />
            <img src="images/mcafee-trans.png">
        </div>
    </div>
</div>