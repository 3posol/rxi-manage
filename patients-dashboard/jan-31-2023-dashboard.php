<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Custom-Header");
header("X-Frame-Options: ALLOW-FROM https://rxi.rxhope.com/");
header("X-Frame-Options: ALLOW-FROM http://rxi.rxhope.com/");
require_once('includes/functions.php');
session_start();
//check login
$patient_logged_in = is_patient_logged_in();
//var_dump($patient_logged_in);
//die;
if (!$patient_logged_in) {
    header('Location: login.php');
}

$patient_after_310820 = (date('Y-m-d', strtotime($_SESSION['PLP']['patient']->DateFirstEnteredSystem)) <= '2020-08-31') ? true : false;

if ($_SESSION['print_and_mail'] != 1 && ($patient_after_310820 == 0 || $patient_after_310820 == false)) {
    header('Location: success.php');
}
#print_r($_SESSION['PLP']);

if (!isset($_SESSION['PLP']['patient']->force_login)) {
//get patient data

    $data = array(
        'command' => 'get_patient_data',
        'patient' => $_SESSION['PLP']['patient']->PatientID,
        'access_code' => $_SESSION['PLP']['access_code']
    );
    $rxi_data = api_command($data);
    $_SESSION['PLP']['patient'] = $rxi_data->patient;
}
clear_patient_last_name();

//get billing data
//echo '<pre> Complete Data';
//print_r($rxi_data);
if (!isset($_SESSION['PLP']['patient']->force_login)) {
    $data = array(
        'command' => 'get_billing',
        'patient' => $_SESSION['PLP']['patient']->PatientID,
        'access_code' => $_SESSION['PLP']['access_code']
    );
    $billing_information = api_command($data);
} else {
    $billing_information = (object) array(
                'payment_info' => '',
                'open_invoices' => [],
                'PastDueBalance' => 0,
                'chargeback' => 'false',
                'DateDisenrolled' => '',
                'last_transaction' => [],
    );
}

//
//echo 'Billing --> ';
//print_r($billing_information);

$payment_method = explode('(', $billing_information->payment_info);
$billing_information->payment_info_cc = (count($payment_method) > 0) ? $payment_method[0] : 'None';
$billing_information->payment_info_exp = (count($payment_method) > 1) ? str_replace(array('Exp. Date: ', ')'), '', $payment_method[1]) : '';

$has_nsf = false;
$last_nsf_added = '';
$last_invoice_no = "";
$last_invoice_amount = 0;
foreach ($billing_information->open_invoices as $invoice) {
    if ($invoice->InvoiceHasNSF == 1) {
        $last_nsf_added = $invoice->InvoiceProcessingDate;
        $has_nsf = true;

        $last_invoice_no = $invoice->InvoiceID;
        $last_invoice_amount = number_format($invoice->InvoiceTotal, 2);
        break;
    }
}

//$_SESSION['PLP']['patient_price_point'] = $billing_information->price_point;
//get meds/providers data

$med_data = array(
    'command' => 'get_medication_and_providers',
    'patient' => $_SESSION['PLP']['patient']->PatientID,
    'access_code' => $_SESSION['PLP']['access_code']
);

$rxi_med_data = api_command($med_data);

if(isset($_GET['rxi_med_data'])&&$_GET['rxi_med_data']){
    echo '<pre>';
    print_r($rxi_med_data);
    die;
}


// get events
$e_data = array(
    'command' => 'get_event_log',
    'id' => $_SESSION['PLP']['patient']->PatientID
);

$event_information = api_command($e_data);

// get all meds for auto-complete
$mdata = array(
    'command' => 'get_all_meds',
    'data' => array('meds' => 'all')
);
$m_response = api_command($mdata);
$med_suggestion = ( isset($m_response->success) && count($m_response->data) > 0 ) ? json_encode($m_response->data) : '';

// get events
$get_patient_data = array(
    'command' => 'get_patient_files',
    'id' => $_SESSION['PLP']['patient']->PatientID
);
$get_patient_files = api_command($get_patient_data);
//echo 'Meds & Prov --> ';
//print_r($med_suggestion);
//echo '</pre>';

$success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT, array('options' => array('default' => false)));
?>

<?php include('_header.php'); ?>

<style>
.content {
    background: transparent;
}

body {
    padding-top: 0px;
}

nav.navbar.navbar-default.navbar-fixed-top.header-nav {
    position: static !important;
}

.ui-autocomplete {
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    width: inherit !important;
    max-height: 340px;
}
</style>

<div
    class="content topContent medication_main <?php echo (isset($_SESSION['PLP']['patient']->force_login) && $_SESSION['PLP']['patient']->force_login) ? "force_login" : "" ?>">



    <div class="container twoColumnContainerNo">

        <div class="row no-marginNo ">

            <div class="col-sm-12 leftIconBox" style="padding-top:15px;">

                <!-- .navbar -->
                <?php include('_header_nav.php'); ?>
                <?php if (isset($_SESSION['PLP']['patient']->force_login) && $_SESSION['PLP']['patient']->force_login): ?>
                <?php include('_force_login_popup.php'); ?>


                <!-- updated modal code -->
                <!-- Button trigger modal -->
                <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">
  Launch demo modal
</button> -->

                <!-- Modal -->
                <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <button onclick="showIframe()">Show</button>
                                <button class="close_x" onclick="hideIframe()" data-dismiss="modal">X</button>
                                <iframe name="iFrameName" id="myIfm" style="display:none;"
                                    data-featherlight-iframe-width="1400" data-featherlight-iframe-height="900"
                                    data-featherlight="iframe"></iframe>
                            </div>
                            <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> -->
                        </div>
                    </div>
                </div>

                <script>
                window.onload = function() {
                    changeUrl();
                };

                function changeUrl() {
                    var site = "https://manage.prescriptionhope.com/enrollment/enroll.php";
                    document.getElementsByName('iFrameName')[0].src = site;
                }

                function showIframe() {
                    $("#myIfm").show();
                }

                function hideIframe() {
                    $("#myIfm").hide();
                }
                </script>
                <!-- end updated modal code -->


                <?php endif; ?>
                <?php
                if (isset($event_information->success)) {
                    $message = $event_information->message;
                    $day = $event_information->day;
                    $date = $event_information->date;
                } else {
                    $message = 'No events found';
                    $day = date('l');
                    $date = date('M d, Y');
                }
                ?>

                <?php if ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback) { ?>
                <div class="row account_summary_section" id="stopLinkMsg">
                    <div class="col-md-12 alert alert-warning alert-dismissible">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close"><i
                                class="fa fa-close"></i></a>
                        <p>Your account balance is past due. Please <a href="billing.php">click here</a> to correct
                            this.</p>
                    </div>
                </div>
                <?php } ?>
                <div class="row account_summary_section Dashboard-section">
                    <!-- Calender card -->
                    <div class="col-sm-6 col-md-4" style="display:none;">
                        <div class="account_summary">
                            <div class="row">
                                <div class="Account-details">
                                    <div class="main_heading-title "><?php echo $day ?>
                                        <span class="date-summary"><small><?php echo $date ?></small></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="Account-details content_detail border-none"><?php echo $message ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Medications card -->
                    <div class="col-sm-6 col-md-4">
                        <div class="account_summary">
                            <div class="row">
                                <div class="Account-details">
                                    <div class="main_heading-title ">Medications
                                        <?php if ($_SESSION['PLP']['patient']->DateDisenrolled != '' || ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback)) { ?>
                                        <span class="document_button"><button class="focus_alert_msg btn btn-primary"><a
                                                    href="javascript:void(0);">VIEW DETAILS</a></button></span>
                                        <?php } else { ?>
                                        <span class="document_button"><button class="btn btn-primary"><a
                                                    href="<?= $basepath ?>/medication.php">VIEW
                                                    DETAILS</a></button></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="Account-details even">
                                    <div class="heading-title">
                                        <p>Regular</p>
                                    </div>
                                </div>
                                <div class="Account-details border-none">
                                    <div class="heading-title tax-sec">
                                        <?php
                                        if (count($rxi_med_data->meds) > 0) {
                                            foreach ($rxi_med_data->meds as $meds) {
                                                if ($meds->DrugAppliedFor != '') {
                                                    ?>
                                        <p><?php echo $meds->DrugAppliedFor ?> (<?php echo $meds->Dosage ?> -
                                            <?php echo $meds->Directions ?>)</p>
                                        <?php
                                                }
                                            }
                                        } else {
                                            echo '<p>No medications found</p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Get a free month card -->
                    <div class="col-sm-6 col-md-4">
                        <div class="account_summary">
                            <div class="row">
                                <div class="Account-details">
                                    <div class="main_heading-title ">Earn A Free Month</div>
                                </div>
                            </div>
                            <div class="row">
                                <!--<div class="Account-details even border-none">
                                        <div class="heading-title"><p>Regular</p></div>
                                </div>	-->
                                <div class="row">
                                    <div class="Account-details border-none p20 text-center">
                                        <?php if ($_SESSION['PLP']['patient']->DateDisenrolled != '' || ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback)) { ?>
                                        <span class="document_button"><button class="focus_alert_msg btn btn-primary"><a
                                                    href="javascript:void(0);">EARN A FREE MONTH</a></button></span>
                                        <?php } else { ?>
                                        <span class="document_button">
                                            <a href="" data-toggle="modal" data-target="#get_a_free_month"><button
                                                    class="btn btn-primary">EARN A FREE MONTH</button></a>
                                        </span>
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add a new medication card -->
                    <div class="col-sm-6 col-md-4">
                        <div class="account_summary new-medication">
                            <div class="row">
                                <div class="Account-details">
                                    <div class="main_heading-title ">Add a new medication</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="Account-details box-section border-none">
                                    <span id="add_new_med_msg1" class="hideme">Hit enter to add this medication</span>
                                    <?php if ($_SESSION['PLP']['patient']->DateDisenrolled != '' || ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback)) { ?>
                                    <input type="text" placeholder="What would you like to add?"
                                        class="add_new_med_input" />
                                    <span class="document_button"><a class="add_med_btn"
                                            href="javascript:void(0);"><button
                                                class="focus_alert_msg btn btn-primary">ADD
                                                MEDICATION</button></a></span>
                                    <?php } else { ?>
                                    <input type="text" placeholder="What would you like to add?"
                                        name="add_new_med_input" id="add_new_med_input" class="add_new_med_input" />
                                    <span class="document_button"><a id="add_med_btn" class="add_med_btn"><button
                                                class="btn btn-primary">ADD MEDICATION</button></a></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing card -->
                    <div class="col-sm-6 col-md-4">
                        <div class="account_summary acc-doc-summary">
                            <div class="row">
                                <div class="Account-details">
                                    <div class="main_heading-title ">Requested Documents
                                        <span class="document_button">
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="Account-details even">
                                    <div class="heading-title">
                                        <p>These documents are requests by the pharmaceutical company that provides your
                                            medication.</p>
                                    </div>
                                </div>
                                <div class="Account-details border-none">
                                    <div class="heading-title tax-sec">
                                        <!-- <p class="price-sec"> -->
                                        <!-- <div class="heading-title tax-sec"> -->
                                        <?php
                                        $i = 1;
                                        if ($get_patient_files->success == 1) {
                                            if (count($get_patient_files->files) > 0) {
                                                foreach ($get_patient_files->files as $file) {
                                                    $jsonFiles = json_decode($file->file_name, true);
                                                    if (!empty($jsonFiles)) {
                                                        foreach ($jsonFiles as $jsonFile) {
                                                            $str_arrs = explode(",", $jsonFile);
                                                            foreach ($str_arrs as $str_arr) {
                                                                $newStrArr = str_replace(' ', '%20', $str_arr);
                                                                ?>
                                        <p><a data-full-path="" href="#" class="pop"
                                                data-href="https://manage.prescriptionhope.com/patients-dashboard/rxi_patient_upload/<?php echo $newStrArr; ?>"><?php
                                                                        $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $str_arr);
                                                                        echo ucfirst(str_replace("_", " ", $withoutExt));
                                                                        ?></a></p>
                                        <?php
                                                                $i++;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                        <?php
                                                }
                                            } else {
                                                echo '<p>' . 'No documentation found' . '</p>';
                                            }
                                        }
                                        ?>
                                        <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog"
                                            aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-body">
                                                        <button type="button" class="close" data-dismiss="modal"><span
                                                                aria-hidden="true">&times;</span><span
                                                                class="sr-only">Close</span></button>
                                                        <img src="" class="imagepreview"
                                                            style="width: 100%;height: auto;">
                                                        <iframe class="imagepreviewpdf" src="" frameborder='0'
                                                            width="100%"></iframe>
                                                        <!-- <div class="imagepreviewdoc">									 -->
                                                        <iframe class="imagepreviewdoc" src='' frameborder='0'
                                                            width="100%"></iframe>
                                                        <!-- </div> -->
                                                    </div>
                                                    <div class="modal-footer" style="text-align: center;">
                                                        <p class="showDocTitle"></p>
                                                        <a href="" class="download-file" download>Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents card -->
                    <div class="col-sm-6 col-md-4">
                        <div class="account_summary">
                            <div class="row">
                                <div class="Account-details">
                                    <div class="main_heading-title">Upload Documents
                                        <span class="document_button hideme"><a href="" data-toggle="modal"
                                                data-target="#submit_documents"><button class="btn btn-primary">SUBMIT
                                                    DOCUMENTS</button></a></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="Account-details even" style="display:none;">
                                    <div class="heading-title">
                                        <p>Requested Documentation</p>
                                    </div>
                                </div>
                                <div class="Account-details border-none" style="display:none;">
                                    <div class="heading-title tax-sec">
                                        <p class="approved">Annual Income Proof</p>
                                    </div>
                                </div>
                                <div class="Account-details border-none p20 text-center">
                                    <span class="document_button">
                                        <?php if ($_SESSION['PLP']['patient']->DateDisenrolled != '' || ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback)) { ?>
                                        <a href="javascript:void(0);"><button
                                                class="focus_alert_msg btn btn-primary">SUBMIT DOCUMENTS</button></a>
                                        <?php } else { ?>
                                        <a href="" data-toggle="modal" data-target="#submit_documents"><button
                                                class="btn btn-primary">SUBMIT DOCUMENTS</button></a>
                                        <?php } ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing card -->
                    <div class="col-sm-6 col-md-4">
                        <div class="account_summary">
                            <div class="row">
                                <div class="Account-details">
                                    <div class="main_heading-title ">Billing
                                        <span class="document_button">
                                            <?php if ($_SESSION['PLP']['patient']->DateDisenrolled != '' || ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback)) { ?>
                                            <button class="focus_alert_msg btn btn-primary"><a
                                                    href="javascript:void(0);">VIEW DETAILS</a></button>
                                            <?php } else { ?>
                                            <button class="btn btn-primary"><a href="<?= $basepath ?>/billing.php">VIEW
                                                    DETAILS</a></button>
                                            <?php } ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="Account-details even">
                                    <div class="heading-title">
                                        <p>Last transaction</p>
                                    </div>
                                </div>
                                <div class="Account-details border-none">
                                    <div class="heading-title">
                                        <p class="price-sec">
                                            <?php if ($_SESSION['PLP']['patient']->PastDueBalance > 0 || count($billing_information->last_transaction) > 0) { ?>
                                            $<?php echo ($_SESSION['PLP']['patient']->PastDueBalance > 0) ? $_SESSION['PLP']['patient']->PastDueBalance : (int) $billing_information->last_transaction->TransactionAmount; ?>
                                            (<?php echo ($_SESSION['PLP']['patient']->PastDueBalance > 0) ? 'Past Due Balance' : 'Paid' ?>
                                            -
                                            <?php echo date('m/d/Y', strtotime($billing_information->last_transaction->TransactionDate)); ?>)
                                            <?php
                                            } else {
                                                echo 'No billing record found';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="clear"></div>
</div>
</div>

<!-- Submit Documents -->
<div class="modal fade" id="submit_documents" tabindex="-1" role="dialog" aria-labelledby="submit_documents"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" id="submitdocuments" name="submitdocuments" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-center">Submit Documents</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="msg"></div>
                    <div class="overlay_form-payment">
                        <div class="row zeroed">
                            <div class="col-sm-12 text-center">
                                <p id="drop-area">Drop multiple files to upload</p>
                                OR
                                <p id="select-area"><input type="file" class="autowidth" name="income_proof[]"
                                        id="income_proof" multiple="multiple" /></p>
                                <input type="hidden" id="action" name="action" value="save_document">
                                <input type="hidden" id="p_id" name="p_id"
                                    value="<?php echo $_SESSION['PLP']['patient']->PatientID ?>">

                                <div class="preview_uploaded_files_div">
                                    <div class="divTable">
                                        <div class="divTableBody">

                                            <div class="divTableRow">
                                                <div class="divTableCell">Name</div>
                                                <div class="divTableCell file-size">Size</div>
                                                <div class="divTableCell">Action</div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row"><span class="col-sm-12 info-text">Please do not submit password-protected
                                documents. We keep your information 100% safe and protected. It is secured by 256-bit
                                encryption, the same security banks use. (PDF | Max 50 MB)</span></div>
                        <span class="blue_loader hideme"><img src="<?= $basepath ?>/images/blue-loader.gif"><br></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <p>
                        <input type="submit" class="btn btn-primary upload_btn big-button whiteButton" value="Upload"
                            disabled />
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
<!--submit document-->

<!-- Get a free month modal -->
<div class="modal fade" id="get_a_free_month" tabindex="-1" role="dialog" aria-labelledby="get_a_free_month"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" id="getAFreeMonth" name="getAFreeMonth" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-center">Refer a Loved One</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="msg"></div>
                    <div class="refer_box">
                        <div class="row zeroed">
                            <div class="col-md-12">
                                <p><strong><u>Earn free monthly services</u></strong> for referring others to
                                    Prescription Hope.</p>
                            </div>
                            <div class="col-md-12">
                                <p>Our patient referral program is simple:</p>
                            </div>
                            <div class="col-md-12 cbp_tmlabel">
                                <ul>
                                    <li><strong><u>Refer Others:</u></strong> Tell friends and family about Prescription
                                        Hope's medication savings.</li>
                                    <li><strong><u>Referral Becomes Enrolled:</u></strong> When the person you referred
                                        gets approved into their patient assistance program through Prescription Hope,
                                        you receive a free month.</li>
                                    <li><strong><u>Receive Your Savings:</u></strong> Your account will be updated to
                                        reflect the free month of service for the following billing period.</li>
                                </ul>
                            </div>
                            <div class="col-md-12">
                                <p><strong>There is no limit to the number of referrals you can provide.</strong></p>
                            </div>
                            <div class="col-md-12">
                                <p><strong>NOTE:</strong> They should enter your full name in the "How did you hear
                                    about Prescription Hope" field within our enrollment form, found at
                                    prescriptionhope.com.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Get a free month modal -->
<?php if (!isset($_SESSION['PLP']['patient']->force_login)) : ?>
<!-- Portal tour modal -->
<div class="modal fade" id="portal_tour" tabindex="-1" role="dialog" aria-labelledby="portal_tour" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Welcome To Your Prescription Hope Dashboard</h4>
                <button type="button" class="close dismiss_tour" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="msg"></div>
                <div class="refer_box">
                    <div class="row zeroed">
                        <div class="col-md-12">
                            <p>We appreciate you creating your account with Prescription Hope. To understand how to use
                                the different features in your account, we have created a quick tutorial for you. This
                                tutorial walks you through step by step on how easy it is to use each part of your
                                account. For detailed instructions, please click "Show Me Around" below.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="text-center">
                    <a id="startTour" class="btn btn-success">Show Me Around</a>
                    <button type="button" class="btn btn-default dismiss_tour" data-dismiss="modal">I Do Not Need
                        Instructions</button>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- Portal tour modal -->
<?php endif; ?>
<div id="overlay">
    <div id="overlay_holder">
    </div>
</div>

<div id="overlay_edit_account" class="overlay_content">

    <div class="overlay_loaded_content">

        <div class="overlay_form">
            <center>
                <h3><b>Edit Account Details</b></h3>
            </center>

            <br>

            <div class="row zeroed">
                <div class="col-sm-4">
                    <b>First Name<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PatientFirstName" />
                </div>
                <div class="col-sm-4">
                    <b>Middle Initial</b><br>
                    <input type="text" class="autowidth" id="PatientMiddleInitial" />
                </div>
                <div class="col-sm-4">
                    <b>Last Name<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PatientLastName" />
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-6">
                    <b>Address Line 1<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PatientAddress1" autocomplete="new-password"
                        onfocus="this.setAttribute('autocomplete', 'new-password');" />
                </div>
                <div class="col-sm-6">
                    <b>Address Line 2</b><br>
                    <input type="text" class="autowidth" id="PatientAddress2" />
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-4">
                    <b>City<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PatientCity_1" />
                </div>
                <div class="col-sm-4">
                    <b>State<font class="red">*</font></b><br>

                    <select name="PatientState_1" id="PatientState_1" class="autowidth form-control login-portal">
                        <option value="AL">Alabama</option>
                        <option value="AK">Alaska</option>
                        <option value="AZ">Arizona</option>
                        <option value="AR">Arkansas</option>
                        <option value="CA">California</option>
                        <option value="CO">Colorado</option>
                        <option value="CT">Connecticut</option>
                        <option value="DE">Delaware</option>
                        <option value="DC">District of Columbia</option>
                        <option value="FL">Florida</option>
                        <option value="GA">Georgia</option>
                        <option value="HI">Hawaii</option>
                        <option value="ID">Idaho</option>
                        <option value="IL">Illinois</option>
                        <option value="IN">Indiana</option>
                        <option value="IA">Iowa</option>
                        <option value="KS">Kansas</option>
                        <option value="KY">Kentucky</option>
                        <option value="LA">Louisiana</option>
                        <option value="ME">Maine</option>
                        <option value="MD">Maryland</option>
                        <option value="MA">Massachusetts</option>
                        <option value="MI">Michigan</option>
                        <option value="MN">Minnesota</option>
                        <option value="MS">Mississippi</option>
                        <option value="MO">Missouri</option>
                        <option value="MT">Montana</option>
                        <option value="NE">Nebraska</option>
                        <option value="NV">Nevada</option>
                        <option value="NH">New Hampshire</option>
                        <option value="NJ">New Jersey</option>
                        <option value="NM">New Mexico</option>
                        <option value="NY">New York</option>
                        <option value="NC">North Carolina</option>
                        <option value="ND">North Dakota</option>
                        <option value="OH">Ohio</option>
                        <option value="OK">Oklahoma</option>
                        <option value="OR">Oregon</option>
                        <option value="PA">Pennsylvania</option>
                        <option value="PR">Puerto Rico</option>
                        <option value="RI">Rhode Island</option>
                        <option value="SC">South Carolina</option>
                        <option value="SD">South Dakota</option>
                        <option value="TN">Tennessee</option>
                        <option value="TX">Texas</option>
                        <option value="UT">Utah</option>
                        <option value="VT">Vermont</option>
                        <option value="VA">Virginia</option>
                        <option value="WA">Washington</option>
                        <option value="WV">West Virginia</option>
                        <option value="WI">Wisconsin</option>
                        <option value="WY">Wyoming</option>
                    </select>
                </div>

                <div class="col-sm-4">
                    <b>Zip<font class="red">*</font></b><br>
                    <input type="text" class="autowidth numeric" id="PatientZip_1" maxlength="5" />
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-4">
                    <b>Phone Number<font class="red">*</font></b><br>
                    <input type="text" class="autowidth phone_us" id="PatHomePhoneWACFmt_1" />
                </div>
                <div class="col-sm-4">
                    <b>Date of Birth</b><br>
                    <input type="text" class="autowidth date" id="PatientDOB" />
                </div>
                <div class="col-sm-4">
                    <b>Social Security Number<font class="red">*</font></b><br>
                    <input type="text" class="autowidth numeric" id="PatientSSN" maxlength="9" disabled="disabled" />
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-4">
                    <b>Alt Contact #1 Name</b><br>
                    <input type="text" class="autowidth" id="EmergencyContactName" />
                </div>
                <div class="col-sm-4">
                    <b>Alt Contact #2 Name</b><br>
                    <input type="text" class="autowidth" id="EmergencyContact2Name" />
                </div>
                <div class="col-sm-4">
                    <b>Alt Contact #3 Name</b><br>
                    <input type="text" class="autowidth" id="EmergencyContact3Name" />
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-4">
                    <b>Alt Contact #1 Phone</b><br>
                    <input type="text" class="autowidth phone_us" id="EmergencyContactPhone" />
                </div>
                <div class="col-sm-4">
                    <b>Alt Contact #2 Phone</b><br>
                    <input type="text" class="autowidth phone_us" id="EmergencyContact2Phone" />
                </div>
                <div class="col-sm-4">
                    <b>Alt Contact #3 Phone</b><br>
                    <input type="text" class="autowidth phone_us" id="EmergencyContact3Phone" />
                </div>
            </div>

        </div>

        <div class="text-center" style="padding-top:10px;">
            <div class="text-left required-fields"><span class="">*</span>Required Field</div>
            <br>

            <br><br>
            <span id="btnSaveAccountDetails">
                <a href="javascript:saveAccountDetails();" class="big-button whiteButton">Save Account Details</a>
            </span>
            <span id="btnSavingAccountDetails" style="display:none;">
                <a href="javascript:void(0);" class="big-button whiteButton">Saving...</a>
            </span>
            <br><br>
            <a href="javascript:closeOverlay();" class="small-button">Go Back</a>
        </div>

    </div>
</div>

<div id="overlay_edit_billing" class="overlay_content">

    <div class="overlay_loaded_content">

        <div class="overlay_form">
            <center>
                <h3><b>
                        <?php if ($billing_information->chargeback) { ?>
                        Please update your payment information
                        <?php } else { ?>
                        Edit Billing Information
                        <?php } ?>
                    </b></h3>
            </center>

            <br>

            <div class="row zeroed">
                <div class="col-sm-6">
                    <b>Credit Card Number<font class="red">*</font></b><br>
                    <input type="text" class="autowidth numeric" id="cc_number" maxlength="16" />
                </div>
                <div class="col-sm-6">
                    <b>Credit Card Type<font class="red">*</font></b><br>
                    <select name="cc_type" id="cc_type" class="form-control login-portal">
                        <option value=""></option>
                        <option value="A">American Express</option>
                        <option value="D">Discover</option>
                        <option value="M">Mastercard</option>
                        <option value="V">VISA</option>
                    </select>
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-6">
                    <b>Expiration Date<font class="red">*</font></b><br>
                    <input type="text" class="autowidth exp_date" id="cc_exp" />
                </div>
                <div class="col-sm-6">
                    <b>CVV<font class="red">*</font></b><span class="question" data-tooltipTarget="cvvCode">?</span><br>
                    <input type="text" class="autowidth numeric" id="cc_cvv" maxlength="4" />
                </div>
            </div>

            <br>

            <center>
                <h4><b>Billing Address</b></h4>
            </center>

            <div class="row zeroed">
                <div class="col-sm-6">
                    <b>First Name<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PatientFirstNameBilling" />
                </div>
                <div class="col-sm-6">
                    <b>Last Name<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PatientLastNameBilling" />
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-6">
                    <b>Address Line 1<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PatientAddress1Billing" autocomplete="new-password" />
                </div>
                <div class="col-sm-6">
                    <b>Address Line 2</b><br>
                    <input type="text" class="autowidth" id="PatientAddress2Billing" />
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-4">
                    <b>City<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PatientCity_1Billing" />
                </div>
                <div class="col-sm-4">
                    <b>State<font class="red">*</font></b><br>

                    <select name="PatientState_1Billing" id="PatientState_1Billing"
                        class="autowidth form-control login-portal">
                        <option value="AL">Alabama</option>
                        <option value="AK">Alaska</option>
                        <option value="AZ">Arizona</option>
                        <option value="AR">Arkansas</option>
                        <option value="CA">California</option>
                        <option value="CO">Colorado</option>
                        <option value="CT">Connecticut</option>
                        <option value="DE">Delaware</option>
                        <option value="DC">District of Columbia</option>
                        <option value="FL">Florida</option>
                        <option value="GA">Georgia</option>
                        <option value="HI">Hawaii</option>
                        <option value="ID">Idaho</option>
                        <option value="IL">Illinois</option>
                        <option value="IN">Indiana</option>
                        <option value="IA">Iowa</option>
                        <option value="KS">Kansas</option>
                        <option value="KY">Kentucky</option>
                        <option value="LA">Louisiana</option>
                        <option value="ME">Maine</option>
                        <option value="MD">Maryland</option>
                        <option value="MA">Massachusetts</option>
                        <option value="MI">Michigan</option>
                        <option value="MN">Minnesota</option>
                        <option value="MS">Mississippi</option>
                        <option value="MO">Missouri</option>
                        <option value="MT">Montana</option>
                        <option value="NE">Nebraska</option>
                        <option value="NV">Nevada</option>
                        <option value="NH">New Hampshire</option>
                        <option value="NJ">New Jersey</option>
                        <option value="NM">New Mexico</option>
                        <option value="NY">New York</option>
                        <option value="NC">North Carolina</option>
                        <option value="ND">North Dakota</option>
                        <option value="OH">Ohio</option>
                        <option value="OK">Oklahoma</option>
                        <option value="OR">Oregon</option>
                        <option value="PA">Pennsylvania</option>
                        <option value="PR">Puerto Rico</option>
                        <option value="RI">Rhode Island</option>
                        <option value="SC">South Carolina</option>
                        <option value="SD">South Dakota</option>
                        <option value="TN">Tennessee</option>
                        <option value="TX">Texas</option>
                        <option value="UT">Utah</option>
                        <option value="VT">Vermont</option>
                        <option value="VA">Virginia</option>
                        <option value="WA">Washington</option>
                        <option value="WV">West Virginia</option>
                        <option value="WI">Wisconsin</option>
                        <option value="WY">Wyoming</option>
                    </select>

                </div>
                <div class="col-sm-4">
                    <b>Zip<font class="red">*</font></b><br>
                    <input type="text" class="autowidth numeric" id="PatientZip_1Billing" maxlength="5" />
                </div>
            </div>

        </div>

        <div class="text-center" style="padding-top:10px;">
            <div class="text-left required-fields"><span class="">*</span>Required Field</div>
            <br>

            <br><br>
            <span id="btnSaveBillingDetails">
                <a href="javascript:saveBillingDetails();" class="big-button whiteButton">Save Billing Information</a>
            </span>
            <span id="btnSavingBillingDetails" style="display:none;">
                <a href="javascript:void(0);" class="big-button whiteButton">Saving...</a>
            </span>
            <br><br>
            <a href="javascript:closeOverlay();" class="small-button">Go Back</a>
        </div>
    </div>

</div>
<?php if ( isset($_GET['rand']) || (!isset($_SESSION['click_changes']) && !isset($_SESSION['PLP']['patient']->force_login) ) ): 
    $data = array(
        'command' => 'get_approved_medication',
        'patient' => $_SESSION['PLP']['patient']->PatientID,
        'access_code' => $_SESSION['PLP']['access_code']
    );

    $user_medi = api_command($data);
    ?>
<div class="modal" id="cg_changesModel" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" onclick="yesChanges('', null)">&times;</button>
                <h4 class="modal-title">Have you recently had any of these changes?</h4>
            </div>
            <div class="modal-body">
                <div class="form-group cg_changes_labels">
                    <label>Prescribed a New Medication?</label>
                    <div class="separator_line"></div>
                    <div class="horizontal_line"></div>
                </div>
                <button type="button" class="btn btn-primary cg_changes_btn" onclick="yesChanges('medi')">Yes</button>
                <?php 
                                                                    
                    if (isset($user_medi->meds) && !empty($user_medi->meds)): 
                        ?>
                <div class="form-group cg_changes_labels">
                    <label>Change in Dosage?</label>
                    <div class="separator_line"></div>
                    <div class="horizontal_line"></div>
                </div>
                <button type="button" class="btn btn-primary change_dosage_btn"
                    onclick="yesChanges('dosage')">Yes</button>


                <ul style="display: none" class="change_dosage_list list-group">
                    <?php foreach ($user_medi->meds as $user_med) : ?>
                    <li class="list-group-item" onclick="yesChanges('dosage',<?= $user_med->MedAssistDetailID ?>)">
                        <?= $user_med->DrugAppliedFor ?> <?= $user_med->Dosage ?> <i class="far fa-edit"></i></li>
                    <?php endforeach; ?>
                </ul>

                <?php endif; ?>
                <div class="form-group cg_changes_labels">
                    <label>Changed Healthcare Provider?</label>
                </div>
                <button type="button" class="btn btn-primary cg_changes_btn"
                    onclick="yesChanges('provider')">Yes</button>
            </div>
            <!--            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Proceed to Your Secure Portal</button>
                            </div>-->
        </div>

    </div>
</div>
<script>
jQuery(function() {
    jQuery('#cg_changesModel').modal('show');
});

function yesChanges(type, val = null) {
    var setCeche = true;
    if (type == 'provider') {
        window.location.href = "/patients-dashboard/providers.php?action=new";
    } else if (type == 'medi') {
        window.location.href = "/patients-dashboard/medication.php?action=new";
    } else if (type == 'dosage') {
        if (val) {
            window.location.href = "/patients-dashboard/medication.php?action=" + val;
        } else {
            setCeche = false;
            jQuery('.change_dosage_list').show();
            jQuery('.change_dosage_btn').hide();
        }
    }
    if (setCeche) {
        jQuery.ajax({
            url: "/patients-dashboard/custom.php",
            type: "POST",
            dataType: "json",
            data: {
                click_changes: 1
            },
            beforeSend: function() {

            },
            success: function(response) {
                console.log(response);
            },
            error: function(e) {}
        });
    }

}
</script>
<?php endif; ?>

<div id="overlay_overdue_invoices" class="overlay_content">

    <div class="overlay_loaded_content">

        <div class="overlay_form text-center text-16">
            <h3><b>Overdue Invoices</b></h3>
            <br>
            <div class=" details-sec-list">
                <?php foreach ($billing_information->open_invoices as $key => $invoice) { ?>
                <?php if ($key > 0) { ?>
                <div class="row zeroed">

                    <div class="col-sm-8">
                        <div class="bottom-light-border-1-small-margins"></div>
                    </div>

                </div>
                <?php } ?>

                <div class="row zeroed">

                    <div class="label-content-left text-left col-sm-6"><b>Invoice Number</b></div>
                    <div class="label-content-right text-right col-sm-6"><?= $invoice->InvoiceID ?></div>

                </div>

                <div class="row zeroed">

                    <div class="label-content-left text-left col-sm-6"><b>Invoice Date</b></div>
                    <div class="label-content-right text-right col-sm-6">
                        <?= date('m/d/Y', strtotime($invoice->InvoiceDate)) ?></div>

                </div>

                <div class="row zeroed">

                    <div class="label-content-left text-left col-sm-6"><b>Invoice Amount</b></div>
                    <div class="label-content-right text-right col-sm-6">
                        $<?= number_format($invoice->InvoiceTotal, 2) ?></div>

                </div>

            </div>
            <?php if (!$billing_information->chargeback) { ?>
            <div class="row zeroed">
                <div class="col-xs-12 text-center btn-grp">
                    <a href="javascript:makePayment(<?= $invoice->InvoiceID ?>, '<?= date('m/d/Y', strtotime($invoice->InvoiceDate)) ?>', '<?= number_format($invoice->InvoiceTotal, 2) ?>');"
                        class="big-button orangeButton">Make Payment</a>
                    <span class="mobile-hidden">&nbsp;</span>
                    <a href="javascript:schedulePayment(<?= $invoice->InvoiceID ?>, '<?= date('m/d/Y', strtotime($invoice->InvoiceDate)) ?>', '<?= number_format($invoice->InvoiceTotal, 2) ?>');"
                        class="big-button orangeButton">Schedule Payment</a>
                    <span class="mobile-hidden">&nbsp;</span>
                    <a href="dashboard.php" class="big-button orangeButton">Go Back</a>
                </div>
            </div>

            <?php } ?>

            <?php } ?>


        </div>
    </div>
</div>

<div id="overlay_schedule_payment" class="overlay_content">

    <div class="overlay_loaded_content">

        <div class="overlay_form">
            <center>
                <h3 id="titleScheduleBill"><b>Schedule Payment</b></h3>
            </center>

            <br>

            <div id="formScheduleBill">
                <div class="row">

                    <div class="col-xs-12 col-lg-5 alignLeft"><b>Invoice Number</b></div>
                    <div class="col-xs-12 col-lg-7 alignRight"><span
                            id="ScheduleInvoiceNumber"><?= $last_invoice_no ?></span><input type="hidden"
                            id="schedule_number" value="<?= $last_invoice_no ?>" /></div>

                </div>

                <div class="row">

                    <div class="col-xs-12 col-lg-5 alignLeft"><b>Invoice Amount</b></div>
                    <div class="col-xs-12 col-lg-7 alignRight"><span
                            id="ScheduleInvoiceAmount">$<?= $last_invoice_amount ?></span></div>

                </div>

                <div class="row">

                    <div class="col-xs-12 col-lg-5 alignLeft"><b>Payment Information</b></div>
                    <div class="col-xs-12 col-lg-7 alignRight"><span
                            id="SchedulePaymentMethod"><?= $billing_information->payment_info_cc ?> (Exp. Date
                            <?= $billing_information->payment_info_exp ?>)</span></div>

                </div>

                <div class="row">

                    <div class="col-xs-12 col-lg-5 alignLeft l30"><b>Schedule Date<font class="red">*</font></b></div>
                    <div class="col-xs-12 col-lg-7 alignRight"><input type="text" class="date alignRight"
                            id="schedule_date" data-toggle="datepicker" /></div>

                </div>
            </div>

            <div class="text-center btn-payment">


                <span id="btnSaveScheduledPayment">
                    <a href="javascript:saveScheduledPayment();" class="big-button whiteButton">Schedule Payment</a>
                </span>
                <span id="btnSavingScheduledPayment" style="display:none;">
                    <a href="javascript:void(0);" class="big-button whiteButton">Saving...</a>
                </span>

                <a href="javascript:closeOverlay();" id="scheduleCancelLink" class="small-button">Go Back</a>
            </div>
        </div>
    </div>

</div>

<div id="overlay_make_payment" class="overlay_content">

    <div class="overlay_loaded_content">

        <div class="overlay_form text-center">
            <p id="txtPayBillInfo" class="text-18">You are about to make a payment for <b id="payBillAmount"></b> with
                <b id="payWithCC"><?= $billing_information->payment_info_cc ?> (Exp. Date
                    <?= $billing_information->payment_info_exp ?>)</b>. If you want to use a different payment method,
                you must call a patient advocate to update your monthly recurring payment option. Please note,
                Prescription Hope is not responsible for any fees you may incur by your bank when providing payment. Are
                you sure you want to submit your payment?
            </p>
            <input type='hidden' id='payInvoiceNo' value=''>
            <input type='hidden' id='payInvoiceAmount' value=''>
            <br>
            <p id="btUseAnotherCC"><a href="#" class="big-button-sm whiteButtonSM">Use A Different Credit Card</a></p>


            <div id="btnSubmitPayment" class="overlay_form_buttons">
                <a href="javascript:submitPayment();" class="big-button whiteButton">Submit Payment</a>
            </div>
            <div class="overlay_form_buttons">
                <a href="javascript:closeOverlay();" class="small-button" id="payCancelLink">Go Back</a>
            </div>

        </div>
    </div>

</div>

<div id="overlay_make_payment_new_cc" class="overlay_content">

    <div class="overlay_loaded_content">

        <div class="overlay_form text-center">
            <h3 id="titlePayBillNewCC"><b>Make Payment</b></h3>
            <br>

            <p id="txtPayBillInfoNewCC" class="text-18" style="display: none;"></p>

            <div id="formPayBillNewCC">
                <div class="row zeroed" style="line-height:35px;">

                    <div class="col-xs-12 col-lg-6 alignLeft"><b>Invoice Number</b></div>
                    <div class="col-xs-12 col-lg-6 alignRight"><span id="payCCInvoiceNoText"></span><input type="hidden"
                            id="payCCInvoiceNo" value="" /></div>

                </div>

                <div class="row zeroed" style="line-height:35px;">

                    <div class="col-xs-12 col-lg-6 alignLeft "><b>Invoice Amount</b></div>
                    <div class="col-xs-12 col-lg-6 alignRight">$<span id="payCCInvoiceAmountText"></span><input
                            type="hidden" id="payCCInvoiceAmount" value="" /></div>

                </div>

                <div class="row zeroed" style="line-height:35px;">

                    <div class="col-xs-12 col-lg-6 alignLeft m10"><b>Credit Card Type<font class="red">*</font></b>
                    </div>
                    <div class="col-xs-12 col-lg-6 alignRight">
                        <select name="payCCType" id="payCCType" class="form-control login-portal">
                            <option value=""></option>
                            <option value="A">American Express</option>
                            <option value="D">Discover</option>
                            <option value="M">Mastercard</option>
                            <option value="V">VISA</option>
                        </select>
                    </div>

                </div>

                <div class="row zeroed" style="line-height:35px;">

                    <div class="col-xs-12 col-lg-6 alignLeft m10"><b>Credit Card Number<font class="red">*</font></b>
                    </div>
                    <div class="col-xs-12 col-lg-6 alignRight"><input type="text" class="autowidth numeric"
                            id="payCCNumber" maxlength="16" /></div>

                </div>

                <div class="row zeroed" style="line-height:35px;">

                    <div class="col-xs-12 col-lg-6 alignLeft m10"><b>Credit Card Expiration Month<font class="red">*
                            </font></b></div>
                    <div class="col-xs-12 col-lg-6 alignRight">
                        <select name="payCCExpMonth" id="payCCExpMonth" class="form-control login-portal">
                            <option value=""></option>
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03">March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>

                </div>

                <div class="row zeroed" style="line-height:35px;">

                    <div class="col-xs-12 col-lg-6 alignLeft m10"><b>Credit Card Expiration Year<font class="red">*
                            </font></b></div>
                    <div class="col-xs-12 col-lg-6 alignRight">
                        <select name="payCCExpYear" id="payCCExpYear" class="form-control login-portal">
                            <option value=""></option>
                            <?php for ($y = date('Y'); $y < (date('Y') + 10); $y++) { ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                            <?php } ?>
                        </select>
                    </div>

                </div>
            </div>

            <div class="text-center payment-sec">
                <div id="btnSubmitPaymentNewCC" class="overlay_form_buttons">
                    <a href="javascript:submitPaymentNewCC();" class="big-button whiteButton">Submit Payment</a>
                </div>
                <div id="btnSubmittingPaymentNewCC" class="overlay_form_buttons hidden">
                    <a href="javascript:void(0);" class="big-button whiteButton">Submitting Payment ...</a>
                </div>
                <div class="overlay_form_buttons">
                    <a href="javascript:closeOverlay();" class="small-button" id="payCancelLinkNewCC">Go Back</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="overlay_uploader" class="overlay_content">
    <div class="overlay_loaded_content text-center">
        <div class="overlay_form text-center">
            <h3><b>Submit Documents</b></h3>
            <br>

            <div id="dragandrophandler">
                <div class="right security-icons <?= ((basename($_SERVER['PHP_SELF']) == 'login.php') ? 'full-width' : '12') ?>"
                    style="text-align:right;">
                    <center>
                        <table width="150" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="150" valign="top" style="padding-top: 0px; border-width: 0;">
                                    <table width="150" border="0" cellpadding="2" cellspacing="0"
                                        title="Click to Verify - This site chose Symantec SSL for secure e-commerce and confidential communications.">
                                        <tr>
                                            <td width="135" align="center" valign="top">
                                                <script src="https://cdn.ywxi.net/js/inline.js?w=120"></script>
                                                <!--script type="text/javascript" src="https://seal.websecurity.norton.com/getseal?host_name=manage.prescriptionhope.com&amp;size=S&amp;use_flash=NO&amp;use_transparent=NO&amp;lang=en"></script--><br />
                                                <!--a href="http://www.symantec.com/ssl-certificates" target="_blank"  style="color:#000000; text-decoration:none; font:bold 7px verdana,sans-serif; letter-spacing:.5px; text-align:center; margin:0px; padding:0px;">ABOUT SSL CERTIFICATES</a-->
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </center>
                </div>
                Please do not submit password-protected documents. We keep your information 100% safe and protected. It
                is secured by 256-bit encryption, the same security banks use.
                <br><br>
                Select file to upload<br>(PDF, JPG, PNG | Max 10 MB)
                <br><br>
                <input type="file" class="" id="incomeProof" name="" />
                <br>
            </div>
        </div>
        <br>

        <span id="btnSaveProofOfIncome">
            <a href="javascript:submitProofOfIncome();" class="big-button whiteButton">Save and Continue</a>
        </span>
        <span id="btnSavingProofOfIncome" style="display:none;">
            <a href="javascript:void(0);" class="big-button whiteButton">Saving...</a>
        </span>

        <br><br>

        <a href="javascript:closeOverlay();" class="small-button">Go Back</a>
    </div>
</div>

<div class="tooltip_templates">
    <div id="tooltipContent_submitProof">
        Please refer to your Welcome Packet for the documentation needed to complete your medication order.
    </div>
    <div id="tooltipContent_NSFFee">
        We were unable to process your payment on <?= date('m/d/Y', strtotime($last_nsf_added)) ?> a fee of $25 has been
        added to your invoice.
        <br><br>
        Please update your Billing Information to avoid additional fees or disruption of service.
    </div>i
    <div id="tooltipContent_cvvCode">
        <img src="images/cvvLocation.png" />
    </div>
</div>

<script>
var med_suggestion = <?php echo $med_suggestion ?>;
var focusOnMsg =
    <?php echo ($_SESSION['PLP']['patient']->DateDisenrolled != '' || ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback)) ? 1 : 0; ?>;
jQuery(document).ready(function() {

    //force users to change their CC in case of chargebacks
    <?php /* if ($billing_information->chargeback) { ?>
    showEditCC();
    forceShowEditCC = 1;
    <?php } ?>

    //force users to pay their due invoices
    <?php if ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback) { ?>
    showOverdueInvoices();
    forceShowOverdueInvoices = 1;
    <?php } */ ?>


    jQuery("#submitdocuments").on('submit', function(e) {
        e.preventDefault();

        var postData = [];
        jQuery(".uploaded_files").map(function(key) {
            postData.push(jQuery(this).val());
        });
        if (postData.length > 0 && postData.length <= 5) {
            jQuery.ajax({
                url: "add_income_proof.php",
                type: "POST",
                data: {
                    income_proof_file: postData
                },
                beforeSend: function() {
                    jQuery("#msg").fadeOut();
                },
                success: function(response) {
                    data = JSON.parse(response);
                    var type = (data.success) ? 'success' : 'danger';
                    jQuery("#msg").html('<div class="alert alert-' + type + '"><p>' + data
                        .message + '</p></div>').fadeIn();
                    if (data.success) {
                        jQuery(
                                '#submitdocuments .overlay_form-payment, #submitdocuments .modal-footer'
                            )
                            .hide();
                        setTimeout(function() {
                            window.location.reload(true);
                        }, 2000);
                    }
                },
                error: function(e) {
                    jQuery("#msg").html(e).fadeIn();
                }
            });
        } else if (postData.length > 5) {
            var type = 'danger';
            var message = 'Please upload maximum five documents.';
            jQuery("#msg").html('<div class="alert alert-' + type + '"><p>' + message + '</p></div>')
                .fadeIn();
            setTimeout(function() {
                jQuery("#msg").fadeOut();
            }, 2000);
        } else {
            var type = 'danger';
            var message = 'Please upload atleast one file.';
            jQuery("#msg").html('<div class="alert alert-' + type + '"><p>' + message + '</p></div>')
                .fadeIn();
            setTimeout(function() {
                jQuery("#msg").fadeOut();
            }, 2000);
        }

    });

    jQuery('#submitdocuments').on('change', function() {

        if (jQuery(".uploaded_files").length >= 5) {
            var type = 'danger';
            var message = 'Only 5 PDF files are allowed to upload.';
            jQuery("#msg").html('<div class="alert alert-' + type + '"><p>' + message + '</p></div>')
                .fadeIn();
        } else {

            jQuery.ajax({
                url: "upload_income_proof.php",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {
                    //jQuery("#income_proof").attr('disabled',true);
                    jQuery('#submitdocuments').find('.blue_loader').removeClass('hideme');
                    jQuery("#msg").fadeOut();

                },
                success: function(response) {
                    data = JSON.parse(response);
                    //console.log(data.uploaded_files);
                    var type = (data.success) ? 'success' : 'danger';

                    if (data.success == true) {

                        var total_files = data.uploaded_files.name.length;
                        for (var i = 0; i < total_files; i++) {
                            console.log(data.uploaded_files.name[i]);
                            //divTableBody
                            var html = '';
                            html += '<div class="divTableRow">';
                            html += '<input type ="hidden" class="uploaded_files" value="' +
                                data.uploaded_files.uploaded_name[i] + '" />';
                            html += '<div class="divTableCell"><a target="_blank" href="' +
                                data.uploaded_files.uploaded_url[i] +
                                '" class="file_url">' + data.uploaded_files.name[i] +
                                '</a></div>';
                            html += '<div class="divTableCell file-size">' + data
                                .uploaded_files.size[i] + '</div>';
                            html +=
                                '<div class="divTableCell"><a href="javascript:void();" data-file_name="' +
                                data.uploaded_files.uploaded_name[i] +
                                '" class="remove_pdf"><i class="fa fa-trash-o" aria-hidden="true"></i></a></div>';
                            html += '</div>';
                            jQuery('.divTableBody').append(html);
                            jQuery('.preview_uploaded_files_div').show();
                            jQuery(".upload_btn").removeAttr("disabled");
                        }
                    }
                    //jQuery(".uploaded_files_div").append('<input type ="hidden" class="uploaded_files" value='+uploaded_files+' />');

                    if (data.success == false) {
                        jQuery("#msg").html('<div class="alert alert-' + type + '"><p>' +
                            data.message + '</p></div>').fadeIn();
                        //jQuery('#submitdocuments .overlay_form-payment, #submitdocuments .modal-footer').hide();
                        //setTimeout(function(){window.location.reload(true);},2000);
                        setTimeout(function() {
                            jQuery("#msg").fadeOut();
                        }, 2000);

                    }
                    jQuery('#submitdocuments').find('.blue_loader').addClass('hideme');
                },
                error: function(e) {
                    jQuery('#submitdocuments').find('.blue_loader').addClass('hideme');
                    jQuery("#msg").html(e).fadeIn();
                }
            });

        }
    });

    jQuery('body').on('click', '.remove_pdf', function() {
        var file_name = jQuery(this).data('file_name');
        if (file_name != '') {
            //jQuery(this).parents('.divTableRow').remove();
            jQuery.ajax({
                url: "remove_income_proof.php",
                type: "POST",
                data: {
                    file_name: file_name
                },
                beforeSend: function() {
                    //jQuery("#income_proof").attr('disabled',true);
                    jQuery('#submitdocuments').find('.blue_loader').removeClass('hideme');
                    jQuery("#msg").fadeOut();
                },
                success: function(response) {
                    data = JSON.parse(response);
                    var type = (data.success) ? 'success' : 'danger';
                    if (data.success == true) {
                        jQuery("input[value='" + data.fileName + "']").parent(
                            ".divTableRow").remove();
                        jQuery("#msg").html('<div class="alert alert-' + type + '"><p>' +
                            data.message + '</p></div>').fadeIn();
                        setTimeout(function() {
                            jQuery("#msg").fadeOut();
                        }, 2000);
                        //jQuery(this).parents('.divTableRow').remove();
                    }
                    if (data.success == false) {
                        jQuery("#msg").html('<div class="alert alert-' + type + '"><p>' +
                            data.message + '</p></div>').fadeIn();
                        setTimeout(function() {
                            jQuery("#msg").fadeOut();
                        }, 2000);
                    }
                    jQuery('#submitdocuments').find('.blue_loader').addClass('hideme');
                },
                error: function(e) {
                    jQuery('#submitdocuments').find('.blue_loader').addClass('hideme');
                    jQuery("#msg").html(e).fadeIn();
                }
            });
        }
    });

    jQuery("#drop-area").on('dragenter', function(e) {
        e.preventDefault();
        jQuery(this).css('background', '#BBD5B8');
    });

    jQuery("#drop-area").on('dragover', function(e) {
        e.preventDefault();
    });

    jQuery("#drop-area").on('drop', function(e) {
        jQuery(this).css('background', '#DEDEDE');
        e.preventDefault();
        var image = e.originalEvent.dataTransfer.files;
        var formImage = new FormData();
        //formImage.append('income_proof', image[0]);
        for (var i = 0; i < image.length; i++) {
            formImage.append('income_proof[]', image[i]);
        }

        if (jQuery(".uploaded_files").length >= 5) {
            var type = 'danger';
            var message = 'Only 5 PDF files are allowed to upload.';
            jQuery("#msg").html('<div class="alert alert-' + type + '"><p>' + message + '</p></div>')
                .fadeIn();
        } else {

            jQuery.ajax({
                url: "upload_income_proof.php",
                type: "POST",
                data: formImage,
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {
                    //jQuery("#income_proof").attr('disabled',true);
                    jQuery('#submitdocuments').find('.blue_loader').removeClass('hideme');
                    jQuery("#msg").fadeOut();
                },
                success: function(response) {
                    data = JSON.parse(response);
                    //console.log(data.uploaded_files);
                    var type = (data.success) ? 'success' : 'danger';

                    if (data.success == true) {

                        var total_files = data.uploaded_files.name.length;
                        for (var i = 0; i < total_files; i++) {
                            console.log(data.uploaded_files.name[i]);
                            //divTableBody
                            var html = '';
                            html += '<div class="divTableRow">';
                            html += '<input type ="hidden" class="uploaded_files" value="' +
                                data.uploaded_files.uploaded_name[i] + '" />';
                            html += '<div class="divTableCell"><a target="_blank" href="' +
                                data.uploaded_files.uploaded_url[i] +
                                '" class="file_url">' + data.uploaded_files.name[i] +
                                '</a></div>';
                            html += '<div class="divTableCell file-size">' + data
                                .uploaded_files.size[i] + '</div>';
                            html +=
                                '<div class="divTableCell"><a href="javascript:void();" data-file_name="' +
                                data.uploaded_files.uploaded_name[i] +
                                '" class="remove_pdf"><i class="fa fa-trash-o" aria-hidden="true"></i></a></div>';
                            html += '</div>';
                            jQuery('.divTableBody').append(html);
                            jQuery('.preview_uploaded_files_div').show();
                            jQuery(".upload_btn").removeAttr("disabled");
                        }
                    }
                    //jQuery(".uploaded_files_div").append('<input type ="hidden" class="uploaded_files" value='+uploaded_files+' />');

                    if (data.success == false) {
                        jQuery("#msg").html('<div class="alert alert-' + type + '"><p>' +
                            data.message + '</p></div>').fadeIn();
                        //jQuery('#submitdocuments .overlay_form-payment, #submitdocuments .modal-footer').hide();
                        //setTimeout(function(){window.location.reload(true);},2000);
                        setTimeout(function() {
                            jQuery("#msg").fadeOut();
                        }, 2000);

                    }
                    jQuery('#submitdocuments').find('.blue_loader').addClass('hideme');
                },
                error: function(e) {
                    jQuery('#submitdocuments').find('.blue_loader').addClass('hideme');
                    jQuery("#msg").html(e).fadeIn();
                }
            });

        }
        /*
         jQuery.ajax({
         url: "add_income_proof.php",
         type: "POST",
         data: formImage,
         contentType: false,
         cache: false,
         processData:false,
         beforeSend : function() {
         jQuery('#submitdocuments .modal-footer').hide();
         jQuery("#msg").fadeOut();
         },
         success: function(response) {
         data = JSON.parse(response);
         var type = (data.success) ? 'success' : 'danger';
         jQuery("#msg").html('<div class="alert alert-'+type+'"><p>'+data.message+'</p></div>').fadeIn();
         if(data.success){
         jQuery('#submitdocuments .overlay_form-payment, #submitdocuments .modal-footer').hide();
         setTimeout(function(){window.location.reload(true);},2000);
         }
         else{
         jQuery('#submitdocuments .modal-footer').show();
         }				
         jQuery(this).css('background', 'transparent');
         },
         error: function(e) {
         jQuery("#msg").html(e).fadeIn();
         }          
         });	
         */
    });

    jQuery("#add_new_med_input").autocomplete({
        source: med_suggestion
    }).keypress(function(e) {
        if (jQuery(this).val() == '') {
            jQuery('#add_new_med_msg').addClass('hideme');
        } else {
            jQuery('#add_new_med_msg').removeClass('hideme');
        }
        if (e.which == 13) {
            jQuery("#add_med_btn").trigger('click');
            //window.location='/html/patients-dashboard-new/medication.php?med_name='+jQuery(this).val()+'#add_new_med';
        }
    });
    jQuery("#add_med_btn").click(function() {
        window.location = '<?= $basepath ?>/medication.php?med_name=' + jQuery("#add_new_med_input")
            .val() + '#add_new_med';
    });

    if (focusOnMsg) {
        jQuery('#medication>a, #calender>a, #navbarDropdown1').attr('href', 'javascript:void(0);');
    }
    jQuery('.focus_alert_msg').click(function(e) {
        e.preventDefault();
        if (focusOnMsg) {
            jQuery("html, body").animate({
                scrollTop: 0
            }, "fast");
        }
    });

    jQuery('.pop').on('click', function() {
        var fileName = jQuery(this).data('href');
        var ext = fileName.split('.').pop();
        //alert(fileName);
        var clsName = '';
        if (ext == 'pdf') {
            clsName = '.imagepreviewpdf';
            jQuery('.imagepreviewdoc').removeAttr('src');
            jQuery('.imagepreview').removeAttr('src');
            jQuery('#imagemodal').modal('show');
            jQuery(clsName).attr('src', jQuery(this).data('href'));
            jQuery(clsName).show();
            jQuery('.imagepreviewdoc').hide();
            jQuery('.imagepreview').hide();
            jQuery(clsName).attr('height', '500px');
        } else if (ext == 'docx' || ext == 'doc') {
            clsName = '.imagepreviewdoc';
            jQuery('.imagepreviewpdf').removeAttr('src');
            jQuery('.imagepreview').removeAttr('src');
            jQuery('#imagemodal').modal('show');
            jQuery(clsName).prop('src', 'https://docs.google.com/viewer?url=' + jQuery(this).data(
                'href') + '&embedded=true');
            jQuery(clsName).show();
            jQuery('.imagepreviewpdf').hide();
            jQuery('.imagepreview').hide();
            jQuery(clsName).attr('height', '500px');
        } else if (ext == 'png' || ext == 'jpg' || ext == 'jpeg') {
            clsName = '.imagepreview';
            jQuery('.imagepreviewdoc').removeAttr('src');
            jQuery('.imagepreviewpdf').removeAttr('src');
            jQuery('#imagemodal').modal('show');
            jQuery(clsName).attr('src', jQuery(this).data('href'));
            jQuery(clsName).attr('height', '500px');
            jQuery(clsName).show();
            jQuery('.imagepreviewdoc').hide();
            jQuery('.imagepreviewpdf').hide();
        }
        jQuery('.showDocTitle').text(jQuery(this).text());
        jQuery('.download-file').attr('href', jQuery(this).data('href'));

    });

    jQuery('#imagemodal').on('hidden.bs.modal', function() {
        jQuery('.imagepreviewdoc').removeAttr('src');
        jQuery('.imagepreviewpdf').removeAttr('src');
        jQuery('.imagepreview').removeAttr('src');
    })
});
</script>
<?php

function rmrf($dir) {
    foreach (glob($dir) as $file) {
        if (is_dir($file)) {
            rmrf("$file/*");
            rmdir($file);
        } else {
            unlink($file);
        }
    }
}

$targetDir = "temp/" . $_SESSION['PLP']['patient']->PatientID;
rmrf($targetDir);
?>
<?php include('_footer.php'); ?>