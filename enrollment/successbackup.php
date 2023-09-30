<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

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
<?php include('_header.php'); ?>

<div class="content topContent">
	<div class="container" style="max-width:750px;">
		<center>
			<h2 class="no-top-margin">Welcome, <?=$_SESSION[$session_key]['data']['first_name']?>!</h2>
			<br/>
			This is your Prescription Hope dashboard. From here you can edit your account details including payment information, manage your medications and update your healthcare providers. If you have any questions contact a representative at <b><font style="color:#6699CC;">1-877-296-HOPE</font></b> (4673).
		</center>

		<div class="clear"></div>
		<br/>

		<div class="row">
			<div class="col-sm-4">
				<a class="homeBox homeBlueBox accountIconBg stop-link" href="" data-tooltip='Your enrollment is currently processing. Within 48 hours you will be contacted via phone and email with the status of your enrollment. From there, you can come back to this page and click the icon to view and manage your "Medication" or "Healthcare Providers"'>
					My Account
				</a>
			</div>

			<div class="col-sm-4">
				<a class="homeBox homeLightBlueBox medIconBg stop-link" href="" data-tooltip='Your enrollment is currently processing. Within 48 hours you will be contacted via phone and email with the status of your enrollment. From there, you can come back to this page and click the icon to view and manage your "Medication" or "Healthcare Providers"'>
					My Medication
				</a>
			</div>

			<div class="col-sm-4">
				<a class="homeBox homeLightBlueBox providerIconBg stop-link" href="" data-tooltip='Your enrollment is currently processing. Within 48 hours you will be contacted via phone and email with the status of your enrollment. From there, you can come back to this page and click the icon to view and manage your "Medication" or "Healthcare Providers"'>
					My Healthcare Providers
				</a>
			</div>
		</div>
	</div>
</div>

<div class="content messagebox success">
	<h3 class="align-center">Welcome to Prescription Hope</h3>
	<br>
	Welcome to your Prescription Hope dashboard where medication management is at your fingertips.
	<br><br>
	Within 48 hours a representative will contact you to explain the next steps to receiving your medication. Please be on the lookout for a phone call and a welcome packet from us. This welcome packet will have important information for you to keep for your records as well as requests for documentation such as proof of income, which is needed to complete the process.
	<br><br>
	We will also be sending documentation to your healthcare provider to request signatures from them as well as the necessary prescriptions. Please contact your doctor's office and let them know we will be reaching out to them soon, just so we're all on the same page. It'll help speed up the enrollment process.
</div>
<br><br>

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
