<?php
require_once('includes/functions.php');
session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
    header('Location: patients-dashboard/login.php');
}

//get data

$data = array(
    'command' => 'get_patient_data',
    'patient' => $_SESSION[$session_key]['data']['id'],
    'access_code' => $_SESSION[$session_key]['access_code']
);

$rxi_data = api_command($data);

$_SESSION[$session_key]['data'] = decode_patient_data($_SESSION[$session_key]['access_code'], $rxi_data->patient->iv, (array) $rxi_data->patient);

//echo $session_key . '<br>';

if ($_SESSION[$session_key]['data']['submitted_as_account'] == 0) {
    header('Location: enroll.php');
}

$redirect = (isset($_GET['redirect'])) ? (int) $_GET['redirect'] : 0;

$SFConversionPixel = '';

$JobID_c = (isset($_COOKIE['SFJobID'])) ? $_COOKIE['SFJobID'] : '';
$SubscriberID_c = (isset($_COOKIE['SFSubscriberID'])) ? $_COOKIE['SFSubscriberID'] : '';
$ListID_c = (isset($_COOKIE['SFListID'])) ? $_COOKIE['SFListID'] : '';
$UrlID_c = (isset($_COOKIE['SFUrlID'])) ? $_COOKIE['SFUrlID'] : '';
$MemberID_c = (isset($_COOKIE['SFMemberID'])) ? $_COOKIE['SFMemberID'] : '';
$sub_id_c = (isset($_COOKIE['SFSubID'])) ? $_COOKIE['SFSubID'] : '';
$batch_id_c = (isset($_COOKIE['SFJobBatchID'])) ? $_COOKIE['SFJobBatchID'] : '';

if ($JobID_c != '' && $SubscriberID_c != '' && $ListID_c != '' && $UrlID_c != '' && $MemberID_c != '' && $sub_id_c != '' && $batch_id_c != '') {
    $strTP = '<img src=\'http://click.s10.exacttarget.com/conversion.aspx?xml=';
    $strTP .= '<system><system_name>tracking</system_name>';
    $strTP .= '<action>conversion</action>';
    $strTP .= '<member_id>' . $MemberID_c . '</member_id>';
    $strTP .= '<job_id>' . $JobID_c . '</job_id>';
    $strTP .= '<sub_id>' . $SubscriberID_c . '</sub_id>';
    $strTP .= '<list>' . $ListID_c . '</list>';
    $strTP .= '<original_link_id>' . $UrlID_c . '</original_link_id>';
    $strTP .= '<BatchID>' . $batch_id_c . '</BatchID>';
    $strTP .= '<conversion_link_id>1</conversion_link_id>';
    $strTP .= '<link_alias>Conversion Tracking</link_alias>';
    $strTP .= '<display_order>1</display_order>';
    $strTP .= '<email>' . $sub_id_c . '</email>';
    $strTP .= '<data_set></data_set></system>\'';
    $strTP .= ' width="1" height="1">';
    print $strTP;
}
?>

<?php include('_header.php'); ?>

<!-- <link rel="stylesheet" type="text/css" href="../patients-dashboard/css/new-styles.css"> -->
<style>
    body{padding-top: 0px !important}
    .content {background: transparent !important;}
    .cbp_tmtimeline {margin: 7% auto 11% auto;}
</style>
<?php // echo '<pre>'; print_r($_SESSION[$session_key]['data']['medication']); echo '</pre>'; ?>
<div class="content topContent medication_main medication_section">

    <div class="container twoColumnContainerNo">

        <div class="row no-marginNo">

            <div class="col-sm-12 leftIconBox" style="padding-top:15px;">
                <!-- .navbar -->

                <div class=" medication-section-title">					
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="medication_content">
                                <h4 class="mb-3 your-medication-50-per-month">Welcome to Prescription Hope!</h4>
                                <p class="my-3 prescription-hope-utilizes-u-s">During the initial processing of your medication order we are collecting additional information from you and your healthcare provider(s) to be able to place your order. We will be sending you a request for additional information that we must have to complete your order. Please respond with the requested information as soon as you can. We will also be sending out a request to your healthcare provider(s) for a signature and a prescription. Please contact your healthcare provider in a few days and ask them to return the information we requested from them. Once we have the requested information from you and your healthcare provider(s) we will be able to order your medication from the pharmaceutical company. Please note: Without the requested information from you and your healthcare provider(s) we will not be able to complete your orders.
                                    <br/>If you have any questions please call us at 1-877-296-4673.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $total_meds = count($_SESSION[$session_key]['data']['medication']);
                //$ul_hw_class = ($total_meds>=4) ? 'half_width' : ''; 
                $ul_hw_class = ($total_meds >= 4) ? '' : '';
                ?>
                <div class="medicine_details <?php echo $ul_hw_class ?>">					
                    <?php
                    if ($total_meds > 0) {
                        foreach ($_SESSION[$session_key]['data']['medication'] as $meds) {
                            ?>
                            <ul>
                                <li><p><small>Medications Requested</small><span><?php echo $meds->name; ?></span></p></li>
                                <li><p><small>Dosage/Frequency</small><span><?php echo $meds->strength . ' ' . $meds->frequency; ?></span></p></li>
                                <li><img src="/patients-dashboard/images/Dashbaord/processing.png" class="processing"><p> <small>Medication Status</small><span><?php echo ($meds->deleted > 0) ? 'Denied' : 'Processing' ?></span></p></li>
                            </ul>
                        <?php
                        }
                    }
                    ?>
                </div>

                <div class="main">
                    <ul class="cbp_tmtimeline">
                        <li>	
                            <div class="center-div">						
                                <div class="cbp_tmicon cbp_tmicon-phone"></div>
                                <time class="cbp_tmtime" dataid="step1" datetime="2013-04-10 18:30"><span>STEP 1</span> <span class="enroll-text">Enrollment Form</span></time>
                                <div class="timeline_image" dataid="step1"><img src="/enrollment/images/Account/step-1.png"></div></div>
                            <div class="cbp_tmlabel" id="step1" style="display:none;"><div class="close-section">  <button type="button" class="close">&times;</button></div>
                                <p>We are currently processing your enrollment form to see if you are pre-approved for the pharmaceutical manufacturer's patient assistance programs through Prescription Hope.</p>
                                <p>During the processing of your application, we review:</p>
                                <ul>
                                    <li>Which medication(s) you are applying for</li>
                                    <li>Your income information to see if you can get approved for the patient assistance program.</li>
                                    <li>Then we can pre-approve or deny you for each medication(s) requested based on the information you have submitted.</li>
                                </ul>
                                <h2>If Your Enrollment Form Is Not Approved</h2>
                                <ul><li> There will be no charges to the payment information you provided to us.</li>
                                    <li>An email will be sent to you explaining you have not been approved, and a letter will be sent to you with the details on why your enrollment was not approved.</li>
                                    <li>If your situation changes in the future, based on the reason you were not approved, you can reapply at that time.</li></ul>
                                <p>Note: If you have applied for more than one medication, it is possible that you can get approved for one medication and not the other(s). Each medication has a different patient assistance program. Please log in to your account to see which medications got approved to receive."</p>
                            </div>
                        </li>
                        <li>	
                            <div class="center-div">
                                <div class="cbp_tmicon cbp_tmicon-screen"></div>
                                <time class="cbp_tmtime" dataid="step2" datetime="2013-04-11T12:04"><span>STEP 2</span> <span class="process-text">Processing</span></time>
                                <div class="timeline_image" dataid="step2"><img src="/enrollment/images/Account/step-2.png"></div>
                            </div>
                            <div class="cbp_tmlabel" id="step2"><div class="close-section">  <button type="button" class="close">&times;</button></div>
                                <p>We are currently processing your enrollment form to see if you are pre-approved for the pharmaceutical manufacturer's patient assistance programs through Prescription Hope.</p>
                                <p>During the processing of your application, we review:</p>
                                <ul>
                                    <li>Which medication(s) you are applying for</li>
                                    <li>Your income information to see if you can get approved for the patient assistance program.</li>
                                    <li>Then we can pre-approve or deny you for each medication(s) requested based on the information you have submitted.</li>
                                </ul>
                                <h2>If Your Enrollment Form Is Not Approved</h2>
                                <ul><li> There will be no charges to the payment information you provided to us.</li>
                                    <li>An email will be sent to you explaining you have not been approved, and a letter will be sent to you with the details on why your enrollment was not approved.</li>
                                    <li>If your situation changes in the future, based on the reason you were not approved, you can reapply at that time.</li></ul>
                                <p>Note: If you have applied for more than one medication, it is possible that you can get approved for one medication and not the other(s). Each medication has a different patient assistance program. Please log in to your account to see which medications got approved to receive."</p>
                            </div>



                        </li>
                        <li>
                            <div class="center-div">						
                                <div class="cbp_tmicon cbp_tmicon-mail"></div>
                                <time class="cbp_tmtime" dataid="step3" datetime="2013-04-13 05:36"><span>STEP 3</span> <span class="approve-text">Pre-Approval</span></time>
                                <div class="timeline_image" dataid="step3"><img src="/enrollment/images/Account/step-3.png"></div>
                            </div>

                            <div class="cbp_tmlabel" id="step3" style="display:none;"><div class="close-section">  <button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                <p>If you are pre-approved for any medication(s) requested, you<br/> will be charged a service fee of $50 for each pre-approved medication. Within 48 hours of the submission of your application, you will receive a welcome call from one of our enrollment specialist that will explain the following:</p>
                                <ul class="list-sub-content"> 
                                    <li class="points"><span>You will receive a letter from us requesting proof of income documentation. The requested documentation is required by the pharmaceutical companies to process your medication order(s).</span></li>
                                    <p class="p7"><strong>a.</strong> This request happens only once a year.</p>
                                    <p class="p7"><strong>b.</strong> As soon as you receive this from us, please send all requested documents back to us in the postage-paid envelope we provide to you at no cost.</p>
                                </ul>
                                <ul class="list-sub-content"> 
                                    <li class="points"><span>Your healthcare provider will also receive a letter from us, asking for the original prescriptions and signatures we need to process your order(s).</span></li>
                                    <p class="p7"><strong>a.</strong> Please call your healthcare provider’s office as soon as you receive your packet and ask them to please return the requested prescriptions and forms as quickly as possible.</p>
                                </ul>
                                <ul class="list-sub-content"> 
                                    <li class="points"><span>As soon as we get the information back from you and your healthcare provider, we will process your order(s).</span></li>
                                    <p class="">Note: We will not be able to order your medication until we have all the required information from you and your healthcare providers.</p>
                                </ul>
                                <ul class="list-sub-content"> 
                                    <li class="points"> <span>The $50.00 monthly service fee for each medication includes the cost of the medication, so there are no other costs involved.</span></li>
                                    <p class=""> Note: As your enrollment is processed, your account will display more information that allows you to stay up to date the entire time.</p>
                                </ul>
                            </div>

                            <!--<div class="cbp_tmlabel" id="step3" style="display:none;"><div class="close-section">  <button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                    <h2>Sprout garlic kohlrabi</h2>
                                    <p>You will be charged immediately for your first service fee of $50 a month for each medication you are approved for.</p>
                                    <p>Within 48 hours you will receive a welcome call from one of our enrollment specialists that will explain the following</p>
                                    <h2>If Your Enrollment Form Is Not Approved</h2>
                                    <p>There will be no charges to the payment information you provided to us.</p>
                                    <p>An email will be sent to you explaining you have not been approved and a letter will be sent to you with the details on why your enrollment was not approved.</p>
                                    <p>If your personal situation changes in the future, based on the reason you were not approved, you can reapply at that time.</p>
                            </div>-->
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container footer">
    <div class="col-sm-6 col-xs-12 safe-enroll pull-left">
        <div class="text-safe pull-left">
            <ul class="links">
                <li><a href="https://prescriptionhope.com/privacy-policy/" target="_blank">Privacy Policy</a></li> |
                <li><a href="https://prescriptionhope.com/terms-of-service/" target="_blank">Terms of service</a></li> |
                <li><p class="copy">2021 ©Prescription Hope, Inc.</p></li>
            </ul>
        </div>
    </div>
    <div class="col-sm-6 col-xs-12 safe-enroll pull-right">
        <div class="text-safe pull-right">
            <img class="padding-right-30 ttip" src="https://prescriptionhope.com/wp-content/themes/prescription_theme/images/new-images/256-shield.png" data-text="hidetext" data-hint="We keep your information safe and protected. It is secured by 256-bit encryption, the same security banks use." />
            <img src="images/mcafee-trans.png">
        </div>
    </div>
</div>
<div class="content topContent" style="display: none;">
    <div class="container" style="max-width:750px;">
        <center>
            <h2 class="no-top-margin">Welcome, <?= $_SESSION[$session_key]['data']['first_name'] ?>!</h2>
            <br/>
            <?php
            if ($_SESSION[$session_key]['data']['deleted'] == '0') {
                ?>
                This is your Prescription Hope dashboard. From here you can edit your account details including payment information, manage your medications and update your healthcare providers. If you have any questions contact a representative at <b><font style="color:#6699CC;">1-877-296-HOPE</font></b> (4673).
                <?php
            }
            ?>
        </center>

        <div class="clear"></div>
        <br/>
        <?php
        if ($_SESSION[$session_key]['data']['deleted'] == '1') {
            ?>
            <div class="alert alert-info alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a>Your account with Prescription Hope was denied. If you feel this was an error or want more information on why your application was denied, please contact us at 1-877-296-HOPE (4673). If your situation has changed and you would like to submit a new enrollment form please click <a href="https://manage.prescriptionhope.com/register.php" class="bold underline">Get Started Now</a>.</div>
            <br><br>
            <?php
        }
        ?>
    </div>
</div>

<!-- CONFIRMATION -->
<script type="text/javascript">

    jQuery(document).ready(function () {
        //GOOGLE Analytics
        //ga('send', 'event', 'conversion', 'submission', '20150806, enrollment, form, step5, successful-sub', {'nonInteraction': 1})
        jQuery('.stop_link').click(function () {
            //jQuery('#stopLinkMsg').show().fadeOut(10000);
        });
        jQuery('.cbp_tmtime, .timeline_image').click(function () {
            var itmId = jQuery(this).attr('dataid');
            jQuery('.cbp_tmlabel').css('display', 'none');
            jQuery('#' + itmId).css('display', 'inline-block');
        });

        jQuery('button.close').click(function () {
            jQuery('.cbp_tmlabel').css('display', 'none');
        });

    });
    // Facebook Pixel
<?php if ($redirect === 1) { ?>
        fbq('track', 'CompleteRegistration');
<?php } ?>

</script>

<?php include('_footer.php'); ?>



<style>
    .timeline_image {  
        width: 160px;
        height: 160px;

    }


</style>