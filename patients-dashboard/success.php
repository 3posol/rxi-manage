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
	'patient' 		=> $_SESSION['PLP']['patient']->id,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);
clear_patient_last_name();

$SFConversionPixel = '';
	
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
} ?>

<?php include('_header.php'); ?>

<link rel="stylesheet" type="text/css" href="../patients-dashboard/css/new-styles.css">
<style>
body{padding-top: 0px !important}
nav.navbar.navbar-default.navbar-fixed-top.header-nav {position: static !important;}
.ui-autocomplete {max-height: 200px;overflow-y: auto;overflow-x: hidden;width: inherit !important; max-height:340px;}
.content {background: transparent !important;}
.cbp_tmtimeline {margin: 7% auto 11% auto;}
</style>
<?php // echo '<pre>'; print_r($_SESSION['PLP']['patient']['medication']); echo '</pre>';?>
<div class="content topContent medication_main">

	<div class="container twoColumnContainerNo">
	
		<div class="row no-marginNo">
		
			<div class="col-sm-12 leftIconBox" style="padding-top:15px;">
				<!-- .navbar -->
				<?php include('_header_nav.php'); ?>
				<div class="row notice-banner" id="stopLinkMsg">
				    <div class="alert alert-warning alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a>Your enrollment is currently processing. Within 48 hours, you will be contacted via phone and email to let you know if your enrollment is pre-approved or denied.</div>
				</div>
				<div class=" medication-section-title">					
					<div class="row">
						<div class="col-sm-12">
							<div class="medication_content">
								<?php
									// echo "<pre>"; 
									// print_r($_SESSION['PLP']); die;
								?>
								<h4 class="mb-3 your-medication-50-per-month">Welcome, <?=$_SESSION['PLP']['patient']->PatientFirstName?></h4>
								<p class="my-3 prescription-hope-utilizes-u-s">Thank you, you have successfully submitted your enrollment form for Prescription Hope. Below you will see each of the medications you have requested and the current status of that medication. You will also see a break down that describes each part of the Prescription Hope process.</p>
							</div>
						</div>
					</div>
				</div>
				
				<?php
				if(isset($_SESSION['PLP']['patient']->meds)) {					
					$total_meds = count($_SESSION['PLP']['patient']->meds);
					$ul_hw_class = ($total_meds>=4) ? 'half_width' : ''; ?>
					<div class="medicine_details <?php echo $ul_hw_class?>">					
						<?php if($total_meds>0){				
							foreach($_SESSION['PLP']['patient']->meds as $meds){ ?>
						<ul>
							<li><p><small>Medications Requested</small><span><?php echo $meds->name;?></span></p></li>
							<li><p><small>Dosage/Frequency</small><span><?php echo $meds->strength.' '.$meds->frequency;?></span></p></li>
							<li><img src="/patients-dashboard/images/Dashbaord/processing.png" class="processing"><p> <small>Medication Status</small><span><?php echo ($meds->deleted > 0) ? 'Denied' : 'Processing'?></span></p></li>
						</ul>
						<?php }
						} ?>
					</div>
				<?php } ?>
				
				<div class="main">
					<ul class="cbp_tmtimeline">
						<li>						
							<div class="cbp_tmicon cbp_tmicon-phone"></div>
							<time class="cbp_tmtime" dataid="step1" datetime="2013-04-10 18:30"><span>SECTION</span> <span>Processing</span></time>
							<div class="timeline_image" dataid="step1"><img src="/patients-dashboard/images/Dashbaord/Processing.png"></div>
							<div class="cbp_tmlabel" id="step1"><div class="close-section">  <button type="button" class="close">&times;</button></div>
								<p>We are currently processing your enrollment to see if you can be pre-approved into patient assistance programs through Prescription Hope.</p>
								<p>During the processing of your application, we review:</p>
								<ul>
									<li>Which medication(s) you are applying for</li>
									<li>Your income information to compare the specific income guidelines of the medications requested</li>
									<li>Then we can pre-approve or not approve you for each medication requested based on the information you have submitted</li>
								</ul>
								<h2>If Your Enrollment Form Is Not Approved</h2>
								<ul><li> There will be no charges to the payment information you provided to us.</li>
								<li>An email will be sent to you explaining you have not been approved, and a letter will be sent to you with the details on why your enrollment was not approved.</li>
								<li>If your situation changes in the future, based on the reason you were not approved, you can reapply at that time.</li></ul>
							</div>
						</li>
						<li>						
							<div class="cbp_tmicon cbp_tmicon-screen"></div>
							<time class="cbp_tmtime" dataid="step2" datetime="2013-04-11T12:04"><span>SECTION</span> <span>Pre-Approval</span></time>
							<div class="timeline_image" dataid="step2"><img src="/patients-dashboard/images/Dashbaord/Pre-Approval.png"></div>
							<div class="cbp_tmlabel" id="step2" style="display:none;"><div class="close-section">  <button type="button" class="close" data-dismiss="modal">&times;</button></div>
								<p>If you are pre-approved for any medication(s) requested, you will be charged a service fee of $50 for each pre-approved medication. Within 48 hours of the submission of your application, you will receive a welcome call from one of our enrollment specialist that will explain the following:</p>
								<ul class="list-sub-content"> 
									<li class="points"><span>You will receive a letter from us requesting proof of income documentation. The requested documentation is required by the pharmaceutical companies to process your medication order(s).</span></li>
									<p class="p7"><strong>a.</strong> This request happens only once a year.</p>
									<p class="p7"><strong>b.</strong> As soon as you receive this from us, please send all requested documents back to us in the postage-paid envelope we provide to you at no cost.</p>
								</ul>
								<ul class="list-sub-content"> 
									<li class="points"><span>Your doctor will also receive a letter from us, asking for the original prescriptions and signatures we need to process your order(s).</span></li>
									<p class="p7"><strong>a.</strong> Please call your doctor’s office as soon as you receive your packet and ask them to please return the requested prescriptions and forms as quickly as possible.</p>
								</ul>
								<ul class="list-sub-content"> 
									<li class="points"><span>As soon as we get the information back from you and your doctor, we will process your order(s).</span></li>
									<p class="p7"><strong>a.</strong> Note: We will not be able to order your medication until we have all the required information from you and your healthcare providers.</p>
								</ul>
								<ul class="list-sub-content"> 
									<li class="points"> <span>The $50.00 monthly service fee for each medication includes the cost of the medication, so there are no other costs involved.</span></li>
									<p class="p7"><strong>a.</strong> Note: As your enrollment is processed, your account will display more information that allows you to stay up to date the entire time.</p>
								</ul>
							</div>
						</li>
						<!---<li>						
							<div class="cbp_tmicon cbp_tmicon-mail"></div>
							<time class="cbp_tmtime" dataid="step3" datetime="2013-04-13 05:36"><span>SECTION</span> <span>Request of Information</span></time>
							<div class="timeline_image" dataid="step3"><img src="/patients-dashboard/images/Dashbaord/Request-of-Information.png"></div>
							<div class="cbp_tmlabel" id="step3" style="display:none;"><div class="close-section">  <button type="button" class="close" data-dismiss="modal">&times;</button></div>
								<h2>Sprout garlic kohlrabi</h2>
								<p>You will be charged immediately for your first service fee of $50 a month for each medication you are approved for.</p>
								<p>Within 48 hours you will receive a welcome call from one of our enrollment specialists that will explain the following</p>
								<h2>If Your Enrollment Form Is Not Approved</h2>
								<p>There will be no charges to the payment information you provided to us.</p>
								<p>An email will be sent to you explaining you have not been approved and a letter will be sent to you with the details on why your enrollment was not approved.</p>
								<p>If your personal situation changes in the future, based on the reason you were not approved, you can reapply at that time.</p>
							</div>
						</li>
						<li>						
							<div class="cbp_tmicon cbp_tmicon-phone"></div>
							<time class="cbp_tmtime" dataid="step4" datetime="2013-04-15 13:15"><span>SECTION</span> <span>Medication Ordered</span></time>
							<div class="timeline_image" dataid="step4"><img src="/patients-dashboard/images/Dashbaord/Medication-Ordered.png"></div>
							<div class="cbp_tmlabel" id="step4" style="display:none;"><div class="close-section">  <button type="button" class="close" data-dismiss="modal">&times;</button></div>
								<h2>Watercress ricebean</h2>
								<p>You will be charged immediately for your first service fee of $50 a month for each medication you are approved for.</p>
								<p>Within 48 hours you will receive a welcome call from one of our enrollment specialists that will explain the following</p>
								<h2>If Your Enrollment Form Is Not Approved</h2>
								<p>There will be no charges to the payment information you provided to us.</p>
								<p>An email will be sent to you explaining you have not been approved and a letter will be sent to you with the details on why your enrollment was not approved.</p>
								<p>If your personal situation changes in the future, based on the reason you were not approved, you can reapply at that time.</p>
							</div>
						</li>
						<li>						
							<div class="cbp_tmicon cbp_tmicon-earth"></div>
							<time class="cbp_tmtime" dataid="step5" datetime="2013-04-16 21:30"><span>SECTION</span> <span>Medication Delivery</span></time>
							<div class="timeline_image" dataid="step5"><img src="/patients-dashboard/images/Dashbaord/Medication-Delivery.png"></div>
							<div class="cbp_tmlabel" id="step5" style="display:none;"><div class="close-section">  <button type="button" class="close" data-dismiss="modal">&times;</button></div>
								<h2>If Your Enrollment Form Is <a href="#">Approved</a></h2>
								<p>You will be charged immediately for your first service fee of $50 a month for each medication you are approved for.</p>
								<p>Within 48 hours you will receive a welcome call from one of our enrollment specialists that will explain the following</p>
								<h2>If Your Enrollment Form Is <a href="#">Not Approved</a></h2>
								<p>There will be no charges to the payment information you provided to us.</p>
								<p>An email will be sent to you explaining you have not been approved and a letter will be sent to you with the details on why your enrollment was not approved.</p>
								<p>If your personal situation changes in the future, based on the reason you were not approved, you can reapply at that time.</p>
							</div>
						</li>	--->				
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- CONFIRMATION -->
<script type="text/javascript">

	jQuery(document).ready(function() {
		//GOOGLE Analytics
		//ga('send', 'event', 'conversion', 'submission', '20150806, enrollment, form, step5, successful-sub', {'nonInteraction': 1})
		jQuery('.stop_link').click(function(){
			//jQuery('#stopLinkMsg').show().fadeOut(10000);
		});
		jQuery('.cbp_tmtime, .timeline_image').click(function(){
			var itmId = jQuery(this).attr('dataid');
			jQuery('.cbp_tmlabel').css('display','none');
			jQuery('#'+itmId).css('display','inline-block');
		});
		
		jQuery('button.close').click(function(){
			jQuery('.cbp_tmlabel').css('display','none');
		});
		
	});
	// Facebook Pixel
	<?php if ($redirect === 1) { ?>
		fbq('track', 'CompleteRegistration');
	<?php } ?>

</script>

<!-- BING UET -->
<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"5711728"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script><noscript><img src="//bat.bing.com/action/0?ti=5711728&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>

<?php include('_footer.php'); ?>
