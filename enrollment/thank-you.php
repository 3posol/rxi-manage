<?php

require_once('includes/functions.php');

// echo "api url: " . $GLOBALS['RXI_API_URL'];

session_start();

//check login
/*
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	// header('Location: login.php');
}
*/

//get data

$data = array(
	'command'		=> 'get_patient_data',
	'patient' 		=> $_SESSION[$session_key]['data']['id'],
	'access_code'	=> $_SESSION[$session_key]['access_code']
);

$rxi_data = api_command($data);

$_SESSION[$session_key]['data'] = decode_patient_data($_SESSION[$session_key]['access_code'], $rxi_data->patient->iv, (array) $rxi_data->patient);

if ($_SESSION[$session_key]['data']['submitted_as_account'] == 0) {
	header('Location: enroll.php');
}

$redirect = (isset($_GET['redirect'])) ? (int) $_GET['redirect'] : 0;

$SFConversionPixel = '';
//if (isset($_SERVER['HTTP_REFERER']) && basename($_SERVER['HTTP_REFERER']) == 'enroll.php') {
	//
	//SalesForce Conversion Tracking
	//
	/*$SFJobID = (isset($_COOKIE['SFJobID'])) ? $_COOKIE['SFJobID'] : '';
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
		$SFConversionPixel .= '<conversion_link_id>4</conversion_link_id>';
		$SFConversionPixel .= '<link_alias>Create Account - Complete Application</link_alias><display_order>4</display_order>';
		$SFConversionPixel .= '<data_set>';
		$SFConversionPixel .= '<data amt="1" unit="Application" accumulate="true" />';
		$SFConversionPixel .= '</data_set></system>\'';
		$SFConversionPixel .= ' width="1" height="1">';
	}*/
	
	$JobID_c 		= (isset($_COOKIE['SFJobID'])) ? $_COOKIE['SFJobID'] : '';
	$SubscriberID_c = (isset($_COOKIE['SFSubscriberID'])) ? $_COOKIE['SFSubscriberID'] : '';
	$ListID_c 		= (isset($_COOKIE['SFListID'])) ? $_COOKIE['SFListID'] : '';
	$UrlID_c 		= (isset($_COOKIE['SFUrlID'])) ? $_COOKIE['SFUrlID'] : '';
	$MemberID_c 	= (isset($_COOKIE['SFMemberID'])) ? $_COOKIE['SFMemberID'] : '';
	$sub_id_c 		= (isset($_COOKIE['SFSubID'])) ? $_COOKIE['SFSubID'] : '';
	$batch_id_c		= (isset($_COOKIE['SFJobBatchID'])) ? $_COOKIE['SFJobBatchID'] : '';
	
	if ($JobID_c !='' && $SubscriberID_c !='' && $ListID_c !='' && $UrlID_c !='' && $MemberID_c !='' && $sub_id_c !='' && $batch_id_c !=''){
		$strTP = '<img src=\'http://click.s10.exacttarget.com/conversion.aspx?xml=';
		$strTP .= '<system><system_name>tracking</system_name>';
		$strTP .= '<action>conversion</action>';
		$strTP .= '<member_id>'.$MemberID_c.'</member_id>';
		$strTP .= '<job_id>'.$JobID_c.'</job_id>';
		$strTP .= '<sub_id>'.$SubscriberID_c.'</sub_id>';
		$strTP .= '<list>'.$ListID_c.'</list>';
		$strTP .= '<original_link_id>'.$UrlID_c.'</original_link_id>';
		$strTP .= '<BatchID>'.$batch_id_c.'</BatchID>';
		$strTP .= '<conversion_link_id>1</conversion_link_id>';
		$strTP .= '<link_alias>Conversion Tracking</link_alias>';
		$strTP .= '<display_order>1</display_order>';
		$strTP .= '<email>'.$sub_id_c.'</email>';
		$strTP .= '<data_set></data_set></system>\'';
		$strTP .= ' width="1" height="1">';
		print $strTP;
	}
	
//}

?>
<?php // include('_headerstaging.php'); ?>
<?php include('_header.php'); ?>
<link rel="stylesheet" href="css/enrollstyle.css" type="text/css">
<div class="content topContent" style="background-color: #eaedf0;">
	<div class="container" style="max-width:900px;">
		<center>
			<h2 class="no-top-margin" style="line-height: 50px; font-size: 36px; font-weight: normal;"><span style="color: #007bc4;">Thank You, You Have Successfully Submitted Your Enrollment Form For Prescription Hope</span></h2>

			<p style="font-size: 22px; line-height: 30px; text-decoration: underline;">Next Steps</p>
			
			<p style="font-size: 22px; line-height: 30px;">Now that you have submitted your enrollment form there are two (things) that could happen.</p>
		</center>
		<br/>
		
		<ol class="blue-numbers" style="list-style-position: inside; text-align: center; font-size: 22px;">
			<li><strong>Your Enrollment Form Is Approved</strong></li>
			<li><strong>Your Enrollment Form Is Denied</strong></li>
		</ol>

		

		<div class="clear"></div></div></div>

	
	<div class="container ty-list" style="max-width:900px; margin-top:35px;">
			<h2 class="no-top-margin"><span style="color: #007bc4;">Your Enrollment Form Is Approved</span></h2>
			<p style="font-size: 20px; font-family: arial;">If your enrollment is approved:</p>
			<br/>
				<ol style="font-size: 18px; color: #007bc4; font-family: arial;">
					<li><span style="color: #54646F;">You will be charged immediately for your first service fee of $50 a month for each medication you are approved for.</li>
					  	<ol type=a style="color: #636363">
							<li><span style="color: #636363;">Note: You could be approved for some of the requested medications and not approved for other medications. We will not charge for any medications you are not approved for.</li>
						</ol>
					<li><span style="color: #54646F;">Within 48 hours you will receive a welcome call from one of our enrollment specialists that will explain the following:</li>
					
						<ol type=a style="color: #636363">
							<li><span style="color: #636363;">You will receive a letter from us requesting proof of income documentation, this is required by the pharmaceutical companies to process your medication order(s).</li>
							
							<ol type=i style="color: #636363">
								<li><span style="color: #636363;">This request happens only once a year.</li>
								<li><span style="color: #636363;">As soon as you receive this from us, please send all requested documents back to us in the postage-paid envelope we provide to you at no cost.</li>
							</ol>
							
							<li><span style="color: #636363;">Your doctor will also receive a letter from us, asking for the original prescriptions and signatures we need to process your order(s).</li>
							
							<ol type=i style="color: #636363">
								<li><span style="color: #636363;">Please call your doctor’s office as soon as you receive your packet and ask them to please return the requested prescriptions and forms as soon as possible.</li>
							</ol>
							
							<li><span style="color: #636363;">As soon as we get the information back from you and your doctor, we will process your order(s).</li>
							
							<ol type=i style="color: #636363">
								<li><span style="color: #636363;">Note: We will not be able to order your medication until we have all required information from you and your healthcare providers.</li>
							</ol>
							
							<li><span style="color: #636363;">The $50.00 monthly service fee for each medication includes the cost of the medication, so there are no other costs involved.</li>
							
							<ol type=i style="color: #636363">
								<li><span style="color: #636363;">Note: If your enrollment is approved, your online account will be built as we are processing your enrollment form. Please be patient during this time, as all information from your enrollment form will not be available to view until your account is fully setup.</li>
							</ol>
							
						</ol>
					<br/>

				</ol> 
		<div class="clear"></div></div>

		<hr style="width: 500px; color:#2b2b2b;">

	<div class="container ty-list" style="max-width:900px; margin-top:35px;">
			<h2 class="no-top-margin"><span style="color: #007bc4;">If Your Enrollment Form Is Not Approved</span></h2>
			<p style="font-size: 20px; font-family: arial;">If your enrollment form is not approved:</p>
			<br/>
				<ol style="font-size: 18px; color: #007bc4; font-family: arial;">
					<li><span style="color: #54646F;">There will be no charges to the payment information you provided to us.</li>
					<li><span style="color: #54646F;">An email will be sent to you explaining you have not been approved and a letter will be sent to you with the details on why your enrollment was not approved.</li>
					<li><span style="color: #54646F;">If your personal situation changes in the future, based on the reason you were not approved, you can reapply at that time.</li>
					
				</ol> <br>
		<br><div class="clear"></div></div>

<div class="content bottomhomeContent" style="background-color: #eaedf0;">
	<div class="container" style="max-width:900px;">
		<center>
			<p style="font-size: 22px; line-height: 30px;"><strong>If you have any questions after receiving your letter or have any medication changes or additions please log into your account through our website at <a href="https://prescriptionhope.com">www.prescriptionhope.com</a> or call us at 877-296-4673 and dial option 3.</strong></p>
			<br/><br/>
			<a class="btn btn-orange center" href="/enrollment/login.php">Login To My Account</a>
			
		</center>

		<div class="clear"></div></div></div>


		</div>
	</div>
</div>


<!-- CONFIRMATION -->
<script type="text/javascript">

	jQuery().ready(function() {
		//GOOGLE Analytics
		//ga('send', 'event', 'conversion', 'submission', '20150806, enrollment, form, step5, successful-sub', {'nonInteraction': 1})
	});

</script>

<!-- SalesForce Conversion Tracking -->
<?/*=$SFConversionPixel*/?>

<!-- Facebook Pixel -->
<script>
	<?php if ($redirect === 1) { ?>
		fbq('track', 'CompleteRegistration');
	<?php } ?>
</script>

<!-- BING UET -->
<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"5711728"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script><noscript><img src="//bat.bing.com/action/0?ti=5711728&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>

<?php include('_footer.php'); ?>
