<?php
require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
    header('Location: login.php');
}
$patient_after_310820 = (date('Y-m-d', strtotime($_SESSION['PLP']['patient']->DateFirstEnteredSystem)) <= '2020-08-31') ? true : false;
if ($_SESSION['print_and_mail'] != 1 && ($patient_after_310820 == 0 || $patient_after_310820 == false)) {
    header('Location: success.php');
}
//get data
if (!isset($_SESSION['PLP']['patient']->force_login)) {
    $data = array(
        'command' => 'get_medication_and_providers',
        'patient' => $_SESSION['PLP']['patient']->PatientID,
        'access_code' => $_SESSION['PLP']['access_code']
    );

    $rxi_data = api_command($data);
} else {
    $rxi_data = (object) array(
                'meds' => [],
                'providers' => [],
    );
}

if ($_GET['debug'] == 1) {
    //echo "<pre>";print_r($rxi_data);echo "</pre>";
}


$success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT, array('options' => array('default' => false)));
$med_appstatus = array(
    '1' => '<span class="med_processing req_approved">Request Approved & Processing</span>',
    '3' => '<span class="med_processing req_denied">Request Denied</span>',
    '4' => '<span class="med_processing req_closed">Request Closed</span>'
);
?>

<?php include('_header.php'); ?>

<style>
    .tooltipBox{
        width: 600px;
    }
    .content{
        background:transparent;
    }
    body{
        padding-top:0px;
    }
    nav.navbar.navbar-default.navbar-fixed-top.header-nav {
        position: static !important;
    }
    .ui-autocomplete {
        z-index: 5000;
        height: 200px;
        overflow: hidden;
        overflow-y: scroll;
    }
    .loading-fname,.loading-lname {
        background-color: #ffffff;
        background-image: url("/patients-dashboard/images/loader.gif");
        background-size: 25px 25px;
        background-position:right center;
        background-repeat: no-repeat;
    }
</style>

<?php //echo fn_breadcrumbs( 'My Medications' ); ?>

<div class="content topContent medication_main <?php echo (isset($_SESSION['PLP']['patient']->force_login) && $_SESSION['PLP']['patient']->force_login) ? "force_login" : "" ?>">
    <div class="container twoColumnContainerNo">

        <div class="row no-marginNo">

            <div class="col-sm-12 leftIconBox" style="padding-top:15px;">
                <!-- .navbar -->
                <?php include('_header_nav.php'); ?>
                <?php if (isset($_SESSION['PLP']['patient']->force_login) && $_SESSION['PLP']['patient']->force_login): ?>
                    <?php include('_force_login_popup.php'); ?>
                <?php endif; ?>
                <div class=" medication-section-title" >
                    <div class="row">
                        <div class="col-sm-12" style="display: none">
                            <div class="medication_content">
                                <h4 class="mb-3 your-medication-50-per-month">Welcome To Your Medication Page</h4>
                                <p class="my-3 prescription-hope-utilizes-u-s">This is where you will see your current medication list. You’ll be able  to add or remove item from your shipments,<br/> check dose times, and see side effects and other information about your medications.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row account_summary_section Dashboard-section m20">
                    <div class="medication-section maintab" id="tables_main">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="information_details">
                                    <ul id="myTabss" class="nav nav-pills" role="tablist" data-tabs="tabs">
                                        <li id="prescription_tab" class="active"><a href="<?= $basepath ?>/medication.php" class="panel-heading">Prescription</a></li>
                                        <li id="hcp"><a href="<?= $basepath ?>/providers.php" class="panel-heading">Healthcare Provider</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane fade in active" id="Prescription">
                                            <div class="panel panel-default panel-info Profile">						
                                                <div class="panel-body">
                                                    <div class="list-details">
                                                        <div class="table-wrap">
                                                            <table class="table">
                                                                <thead><tr><th>Type</th><th>Medication</th><th></th><th id="med_status">Medication Status</th></tr></thead>
                                                                <?php
                                                                if (isset($_GET['cbtester']) && $_GET['cbtester']) {
                                                                    echo '<prE>';
                                                                    print_r($rxi_data->meds);
                                                                    die;
                                                                }
                                                                ?>
                                                                <tbody>
                                                                    <?php
                                                                    $firstMed = 1;
                                                                    foreach ($rxi_data->meds as $key => $med) {
                                                                        if ($med->DrugAppliedFor != '') {
                                                                            # count alerts
                                                                            $intAlerts = 0;
                                                                            $strAlertSuffix = "";

                                                                            if (isset($med->InAppealsProcessing) && $med->InAppealsProcessing)
                                                                                $intAlerts++;
                                                                            if (isset($med->NeedPatientInfo) && $med->NeedPatientInfo)
                                                                                $intAlerts++;
                                                                            if (isset($med->NeedPatientInfo) && $med->NeedProviderInfo)
                                                                                $intAlerts++;

                                                                            if ($intAlerts != 1) {
                                                                                $strAlertSuffix = "s";
                                                                            }
                                                                            ?>
                                                                            <tr class="med_item <?php echo (isset($med->MedEstArrivalDate) && $med->MedEstArrivalDate != '' ) ? 'has_date' : ''; ?>" id="med<?= $med->MedAssistDetailID ?>">
                                                                                <td><img class="non_active" src="<?= $basepath ?>/images/Medication/tablet.png">
                                                                                    <img class="white_active" src="<?= $basepath ?>/images/Medication/tablet-white.png"></td>
                                                                                <td><span><?= $med->DrugAppliedFor ?> <?= $med->Dosage ?>  </span></td>
                                                                                <td id="view<?= $med->MedAssistDetailID ?>"><a href="javascript:void(0);" class="view-medi <?php
                                                                                    if ($firstMed == 1) {
                                                                                        echo 'firstmed';
                                                                                    }
                                                                                    ?>"><i class="fa fa-eye"></i> <span>View Details</span></a></td>

                                                                                <td>
                                                                                    <?php
                                                                                    if (isset($med->AppStatus) && $med->AppStatus != '') {
                                                                                        if ($med->AppStatus == 2 && isset($med->MedEstArrivalDate)) {
                                                                                            ?>
                                                                                            <?php
                                                                                            $etadate = (isset($med->MedEstArrivalDate) && $med->MedEstArrivalDate != '') ? date('d/M', strtotime($med->MedEstArrivalDate . ' + 11 days')) : '';
                                                                                            if ($etadate != '') {
                                                                                                $etaparts = explode('/', $etadate);
                                                                                                ?>
                                                                                                <img class="date-trans" src="<?= $basepath ?>/images/Medication/date.png">
                                                                                                <img class="date-blue" src="<?= $basepath ?>/images/Medication/date-blue.png">
                                                                                                <div class="arrival-date"><?php echo '<b>' . $etaparts[0] . '</b>' . $etaparts[1] ?></div>
                                                                                                <?php
                                                                                            } else {
                                                                                                echo '<span class="med_processing req_approved">Request Approved & Processing</span>';
                                                                                            }
                                                                                        }
                                                                                        if ($med->AppStatus == 1 || $med->AppStatus == 3 || $med->AppStatus == 4) {
                                                                                            echo $med_appstatus[$med->AppStatus];
                                                                                        }
                                                                                    } elseif (isset($med->approval_status) && $med->approval_status == 2) {
                                                                                        echo '<span class="med_processing req_denied">Request Denied</span>';
                                                                                        if (isset($med->app_status_comment) && $med->app_status_comment != '') {
                                                                                            echo '<span class="app_status_comment">' . $med->app_status_comment . '</span>';
                                                                                        }
                                                                                    } else {
                                                                                        echo '<span class="med_processing req_pending">Pending Approval</span>';
                                                                                    }
                                                                                    ?>
                                                                                    <?php if ($intAlerts > 0) echo ' <font style="font-weight:200;">(' . $intAlerts . ' alert' . $strAlertSuffix . ')</font>' ?>
                                                                                </td>
                                                                            </tr>
                                                                            <?php
                                                                            $firstMed++;
                                                                        }
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>										
                                                </div> 
                                            </div> 
                                        </div>
                                        <div role="tabpanel" class="tab-pane fade" id="Healthcare"></div>
                                    </div>
                                </div>
                            </div> 
                        </div> 
                    </div>


                    <div class="table-wrap-desc">
                        <div class="medication_v1">
                            <?php foreach ($rxi_data->meds as $key => $med) { ?>
                                <?php
                                //get provider								
                                $med_provider = null;
                                if ($med->ProviderId != -1) {
                                    foreach ($rxi_data->providers as $provider) {
                                        if ($provider->PrvProviderId == $med->ProviderId) {
                                            $med_provider = $provider;
                                        }
                                    }
                                } else {
                                    $med_provider = $med;
                                }
                                ?>
                                <div class="panel panel-default panel-info Profile meddesc-box" id="meddesc<?= $med->MedAssistDetailID ?>" style="display: none;">
                                    <div class="panel-heading">
                                        <span class="pull-left" data-dismiss1="alert" id="close_d_v">
                                            <a <?php
                                            if ($med->ProviderId == -1) {
                                                echo 'style="display:none;"';
                                            }
                                            ?> href="javascript:editMedication('<?= $med->MedAssistDetailID ?>');"><img src="<?= $basepath ?>/images/Medication/edit.png"> CHANGE DOSAGE </a>
                                        </span>
                                        <span class="pull-right close_desc" data-id="meddesc<?= $med->MedAssistDetailID ?>" data-dismiss1="alert"><img src="<?= $basepath ?>/images/Medication/close.png"> Close</span>			
                                    </div>
                                    <div class="panel-body">
                                        <div class="details"><h4 class="heading-titles"><?= $med->DrugAppliedFor . ' ' . $med->Dosage ?></h4></div>
                                        <div class="dates-section">
                                            <?php
                                            $heading = 'Medication Status';
                                            if (isset($med->AppStatus) && $med->AppStatus != '') {
                                                if ($med->AppStatus == 2 && isset($med->MedEstArrivalDate)) {
                                                    if (isset($med->MedEstArrivalDate) && $med->MedEstArrivalDate != '') {
                                                        $date = date('m/d/Y', strtotime($med->MedEstArrivalDate . ' + 11 days'));
                                                        $heading = 'Estimated Arrival Date';
                                                    } else {
                                                        $date = '<span class="med_processing req_approved">Request Approved & Processing</span>';
                                                    }
                                                }
                                                if ($med->AppStatus == 1 || $med->AppStatus == 3 || $med->AppStatus == 4) {
                                                    $date = $med_appstatus[$med->AppStatus];
                                                }
                                            } elseif (isset($med->approval_status) && $med->approval_status == 2) {
                                                $date = '<span class="med_processing req_denied">Request Denied</span>';
                                                if (isset($med->app_status_comment) && $med->app_status_comment != '') {
                                                    $date .= '<span class="app_status_comment">' . $med->app_status_comment . '</span>';
                                                }
                                            } else {
                                                $date = '<span class="med_processing req_pending">Pending Approval</span>';
                                            }
                                            ?>
                                            <p>
                                                <small class="headi-date"><?php echo $heading; ?></small>
                                                <small class="date-secc"><?php echo $date; ?></small>
                                            </p>
                                            <?php if (isset($med->InAppealsProcessing) && $med->InAppealsProcessing) { ?>
                                                <div class="alert alert-info">
                                                    <b>This medication is in an appeals status.</b>  We are diligently working with the pharmaceutical company to appeal their decision to deny your enrollment.  We will contact you as soon as this process is complete to review the outcome with you.
                                                </div>
                                            <?php } ?>

                                            <?php if (isset($med->NeedPatientInfo) && $med->NeedPatientInfo) { ?>
                                                <div class="alert alert-danger">
                                                    <b>We have been trying to contact you for additional information.</b> Please call a patient advocate as soon as possible to prevent further delay in receiving your medication.
                                                </div>
                                            <?php } ?>

                                            <?php if (isset($med->NeedProviderInfo) && $med->NeedProviderInfo) { ?>
                                                <div class="alert alert-danger">
                                                    <b>We have been trying to contact your healthcare provider for additional information.</b> Please call a patient advocate as soon as possible to prevent further delay in receiving your medication.
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="dir_v-s">
                                            <p><small class="main_heading">Directions</small><small class="sub_heading"><?= $med->Directions ?></small></p>
                                        </div>
                                        <div class="dir_v-s">
                                            <p><small class="main_heading">Medication Delivered To</small><small class="sub_heading"><?= ((isset($med->MedDelivery)) ? $med->MedDelivery : '') ?></small></p>
                                        </div>
                                        <div class="dir_v-s">
                                            <p><small class="main_heading">Date Medication Last Ordered</small><small class="sub_heading"><?= ((isset($med->MedDateOrdered)) ? $med->MedDateOrdered : '') ?></small></p>
                                        </div>
                                        <div class="dir_v-s">
                                            <p><small class="main_heading">Estimated Delivery Date <span class="question" data-tooltiptarget="estimatedDeliveryDate"><img src="<?= $basepath ?>/images/Medication/question.png"></span></small><small class="sub_heading">
                                                    <?php
                                                    $etadate = ((isset($med->MedEstArrivalDate)) ? $med->MedEstArrivalDate : '');
                                                    if ($etadate != '') {
                                                        echo date('m/d/Y', strtotime($etadate . ' + 11 days'));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </small></p>
                                        </div>
                                        <div class="dir_v-s">
                                            <p>
                                                <small class="main_heading">Healthcare Provider</small>
                                                <small class="sub_heading"><strong><?= ((isset($med_provider->PrvPrefix)) ? $med_provider->PrvPrefix : 'Dr.') ?> <?= $med_provider->PrvFirstName ?> <?= ((isset($med_provider->PrvMiddleInitial) && $med_provider->PrvMiddleInitial != '') ? $med_provider->PrvMiddleInitial . '.' : '') ?> <?= $med_provider->PrvLastName ?> <?= ((isset($med_provider->PrvProfDesignation)) ? $med_provider->PrvProfDesignation : '') ?> </strong>
                                                    <br/>
                                                    <?= $med_provider->PrvAddress1 ?><?= (($med_provider->PrvAddress2 != '' && strpos($med_provider->PrvAddress1, $med_provider->PrvAddress2) === false) ? ', ' . $med_provider->PrvAddress2 : '') ?>
                                                    <br>
                                                    <?= $med_provider->PrvCity ?>, <?= $med_provider->PrvState ?> <?= $med_provider->PrvZip ?>
                                                    <br/>
                                                    <a href="tel:<?= $med_provider->PrvWorkPhone ?>"><i class="fa fa-phone"></i> <?= $med_provider->PrvWorkPhone ?> </a><br/>
                                                    <i class="fa fa-fax"></i> <?= $med_provider->PrvFaxNumber ?></small>
                                            </p>
                                        </div>
                                        <div class="dir_v-s">
                                                <!--<small class="main_heading">Healthcare Provider</small>-->
                                            <div class="cg_sub_heading">
                                                <p class="label"><strong>Group</strong></p>
                                                <p class="val"><span><?= ($med_provider->cg_group) ? $med_provider->cg_group : 'n/a' ?></span></p>
                                            </div>
                                            <div class="cg_sub_heading">
                                                <p class="label"><strong>ID</strong></p>
                                                <p class="val"><span><?= ($med_provider->cg_id) ? $med_provider->cg_id : 'n/a' ?></span></p>
                                            </div> 
                                            <div class="cg_sub_heading">
                                                <p class="label"><strong>Bin</strong></p>
                                                <p class="val"><span><?= ($med_provider->cg_bin) ? $med_provider->cg_bin : 'n/a' ?></span></p>
                                            </div>
                                            <div class="cg_sub_heading">
                                                <p class="label"><strong>PCN</strong></p>
                                                <p class="val"><span><?= ($med_provider->cg_pcn) ? $med_provider->cg_pcn : 'n/a' ?></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                            <?php } ?>													
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="javascript:addMedication();" class="addGreyBtn add_med"><span><img src="<?= $basepath ?>/images/Medication/add-circular-outlined-button.png"></span> New Medication</a>
    </div>
</div>

<?php
foreach ($rxi_data->meds as $key => $med) {
//get provider
//print_r($med);
    $med_provider = null;
    if ($med->ProviderId != -1) {
        foreach ($rxi_data->providers as $provider) {
            if ($provider->PrvProviderId == $med->ProviderId) {
                $med_provider = $provider;
            }
        }
    } else {
        $med_provider = $med;
    }

# count alerts
    $intAlerts = 0;
    $strAlertSuffix = "";

    if (isset($med->InAppealsProcessing) && $med->InAppealsProcessing)
        $intAlerts++;
    if (isset($med->NeedPatientInfo) && $med->NeedPatientInfo)
        $intAlerts++;
    if (isset($med->NeedPatientInfo) && $med->NeedProviderInfo)
        $intAlerts++;

    if ($intAlerts != 1) {
        $strAlertSuffix = "s";
    }
    ?>
    <!--<div class="medRow">
    <div class="row medRowTop">
    <div class="col-sm-8" style="padding-top:5px;">
    <h3 class="noMargin<?= (((isset($med->InAppealsProcessing) && $med->InAppealsProcessing) || (isset($med->NeedPatientInfo) && $med->NeedPatientInfo) || (isset($med->NeedPatientInfo) && $med->NeedProviderInfo)) ? ' medWithNotice' : '' ) ?>"><?= $med->DrugAppliedFor ?><?php if ($intAlerts > 0) echo ' <font style="font-weight:200;">(' . $intAlerts . ' alert' . $strAlertSuffix . ')</font>' ?></h3>
    <?= $med->Dosage ?>
    </div>
    <div class="col-sm-4 smColRight">
    <a class="contentHeaderLink medView list-link" href="javascript:void(0);" data-id="<?= $med->MedAssistDetailID ?>"><span id="medView_<?= $med->MedAssistDetailID ?>"><i class="fa fa-eye"></i> VIEW</span></a>&nbsp;&nbsp;&nbsp;&nbsp;<img src="images/link_sep.png" class="link-sep" />&nbsp;&nbsp;&nbsp;&nbsp;<a class="contentHeaderLink list-link" href="javascript:editMedication('<?= $med->MedAssistDetailID ?>');"><i class="fa fa-edit"></i> CHANGE DOSAGE</a>
    </div>
    </div>
    <div class="row medDetailRow" id="medRow_<?= $med->MedAssistDetailID ?>" style="display:none;">
    <?php if (isset($med->InAppealsProcessing) && $med->InAppealsProcessing) { ?>
                                    <div class="box small redMedNoticeBox">
                                    <b>This medication is in an appeals status.</b>  We are diligently working with the pharmaceutical company to appeal their decision to deny your enrollment.  We will contact you as soon as this process is complete to review the outcome with you.
                                    </div>
    <?php } ?>

    <?php if (isset($med->NeedPatientInfo) && $med->NeedPatientInfo) { ?>
                                    <div class="box small redMedNoticeBox">
                                    <b>We have been trying to contact you for additional information.</b> Please call a patient advocate as soon as possible to prevent further delay in receiving your medication.
                                    </div>
    <?php } ?>

    <?php if (isset($med->NeedProviderInfo) && $med->NeedProviderInfo) { ?>
                                    <div class="box small redMedNoticeBox">
                                    <b>We have been trying to contact your healthcare provider for additional information.</b> Please call a patient advocate as soon as possible to prevent further delay in receiving your medication.
                                    </div>
    <?php } ?>

    <div class="box small yellowMedNoticeBox">
    Any dosage changes or medications added to your account will be verified by your healthcare provider. If you add a medication or change a dosage, please allow 2-4 weeks from the date of the change to ensure the healthcare provider has enough time to respond to our requests and for us to place the order.
    </div>

    <div class="col-sm-6">
    <b>Directions:</b><br>
    <?= $med->Directions ?>
    <br><br>
    <b>Medication Delivered To:</b><br>
    <?= ((isset($med->MedDelivery)) ? $med->MedDelivery : '') ?>
    <br><br>
    <b>Date Medication Last Ordered:</b><br>
    <?= ((isset($med->MedDateOrdered)) ? $med->MedDateOrdered : '') ?>
    <br><br>
    <b>Estimated Delivery Date:</b><span class="question" data-tooltipTarget="estimatedDeliveryDate">?</span><br>
    <?= ((isset($med->MedEstArrivalDate)) ? $med->MedEstArrivalDate : '') ?>

    <div class="onlyMobile"><br><br></div>
    </div>
    <div class="col-sm-6">
    <div class="contentHeader">Healthcare Provider</div>

    <b><?= ((isset($med_provider->PrvPrefix)) ? $med_provider->PrvPrefix : 'Dr.') ?> <?= $med_provider->PrvFirstName ?> <?= ((isset($med_provider->PrvMiddleInitial) && $med_provider->PrvMiddleInitial != '') ? $med_provider->PrvMiddleInitial . '.' : '') ?> <?= $med_provider->PrvLastName ?> <?= ((isset($med_provider->PrvProfDesignation)) ? $med_provider->PrvProfDesignation : '') ?></b>
    <br/>

    <div class="no-bold">
    <?= $med_provider->PrvAddress1 ?><?= (($med_provider->PrvAddress2 != '' && strpos($med_provider->PrvAddress1, $med_provider->PrvAddress2) === false) ? ', ' . $med_provider->PrvAddress2 : '') ?>
    <br><?= $med_provider->PrvCity ?>, <?= $med_provider->PrvState ?> <?= $med_provider->PrvZip ?>
    <br/>
    <br>
    <span class="phonelink"><i class="fa fa-phone"></i> <?= $med_provider->PrvWorkPhone ?></span>
    <br>
    <?php if (empty($med_provider->PrvFaxNumber)) { ?>
                                    <br>
                                    <a class="contentHeaderLink" href="javascript:editProvider(<?= $med_provider->PrvProviderId ?>);"><i class="fa fa-plus-square"></i> Add Fax Number</a>
    <?php } else { ?>
                                    <div class="phonelink" style="padding-top:5px;"><i class="fa fa-fax"></i> <?= $med_provider->PrvFaxNumber ?></div>
    <?php } ?>
    </div>

    </div>
    </div>

    </div>-->

<?php } ?>


</div>

</div>

<?php if ($success !== false) { ?>
    <div id="fmMsg" class="bold">You're new information was saved successfully.<br/><br/><br></div>
<?php } ?>

<div class="left-content">
    <?php
    foreach ($rxi_data->meds as $key => $med) {
//get provider
        $med_provider = null;
        foreach ($rxi_data->providers as $provider) {
            if ($provider->PrvProviderId == $med->ProviderId) {
                $med_provider = $provider;
            }
        }
        ?>
        <!--
        <?= (((isset($med->InAppealsProcessing) && $med->InAppealsProcessing) || (isset($med->NeedPatientInfo) && $med->NeedPatientInfo) || (isset($med->NeedProviderInfo) && $med->NeedProviderInfo)) ? '<div class="dark-red">' : '' ) ?>
        
        <h3><?= $med->DrugAppliedFor ?></h3>
        <br/>
        
        <?php if (isset($med->InAppealsProcessing) && $med->InAppealsProcessing) { ?>
                                        <p class="box small">
                                        This medication is in an appeals status.  We are diligently working with the pharmaceutical company to appeal their decision to deny your enrollment.  We will contact you as soon as this process is complete to review the outcome with you.
                                        </p>
                                        <br/>
        <?php } ?>
        
        <?php if (isset($med->NeedPatientInfo) && $med->NeedPatientInfo) { ?>
                                        <p class="box small">
                                        We have been trying to contact you for additional information, please call a patient advocate as soon as possible to prevent further delay in receiving your medication.
                                        </p>
                                        <br/>
        <?php } ?>
        
        <?php if (isset($med->NeedProviderInfo) && $med->NeedProviderInfo) { ?>
                                        <p class="box small">
                                        We have been trying to contact your healthcare provider for additional information, please call a patient advocate as soon as possible to prevent further delay in receiving your medication.
                                        </p>
                                        <br/>
        <?php } ?>
        
        <div class="label">Strength:</div>
        <div class="value"><?= $med->Dosage ?></div>
        
        <div class="label">Directions:</div>
        <div class="value">
        <div class="label">Medication Delivered To:</div>
        <div class="value"><?= ((isset($med->MedDelivery) && $med->MedDelivery == 'provider') ? 'Provider' : 'Patient') ?></div>
        
        <div class="label">Prescribing Healthcare Provider:</div>
        <div class="value">
        <?= $med_provider->PrvPrefix ?> <?= $med_provider->PrvFirstName ?> <?= (($med_provider->PrvMiddleInitial != '') ? $med_provider->PrvMiddleInitial . '.' : '') ?> <?= $med_provider->PrvLastName ?> <?= $med_provider->PrvProfDesignation ?>
        <br/>
        
        <div class="no-bold">
        <?= $med_provider->PrvAddress1 ?><?= (($med_provider->PrvAddress2 != '' && strpos($med_provider->PrvAddress1, $med_provider->PrvAddress2) === false) ? ', ' . $med_provider->PrvAddress2 : '') ?>, <?= $med_provider->PrvCity ?>, <?= $med_provider->PrvState ?> <?= $med_provider->PrvZip ?>
        <br/>
        
        <?= $med_provider->PrvWorkPhone ?>
        </div>
        </div>
        
        <div class="label">Date Medication Last Ordered:</div>
        <div class="value"><?= ((isset($med->MedDateOrdered)) ? $med->MedDateOrdered : '') ?></div>
        
        <div class="label">Estimated Delivery Date:</div>
        <div class="value">
        <?php if (isset($med->MedEstArrivalDate) && $med->MedEstArrivalDate != '') { ?>
            <?= date('m/d/Y', strtotime('+10 days', strtotime($med->MedEstArrivalDate))) ?>
                                        (<a href="#" rel="<strong>If you have not received your medication by the listed Estimated Delivery Date, please do the following:</strong>
                                        <br/><br/>
                                        For medication(s) normally delivered to your home address:
                                        <br/>
                                        <ol>
                                        <li><strong>Please double-check your medication supplies</strong> to make sure that it was not just overlooked.</li>
                                        <li><strong>If your address has changed, contact us immediately to update your address.</strong> Your medication may have been shipped to your previous address.</li>
                                        </ol>
                                        <br/>
                                        
                                        For medication(s) normally delivered to your doctor's office:<br/>
                                        <ol>
                                        <li>
                                        <strong>Please call your doctor's office</strong> and ask a member of the nursing staff to check in the following areas for your medication(s):
                                        <ul>
                                        <li>The sample drawer/closet</li>
                                        <li>The doctor's personal desk, inside and out (the medication is in a box addressed to the doctor, but there is an invoice within the box that has your information on it)</li>
                                        <li>For insulins, have the refrigerators checked (even the break room refrigerator)</li>
                                        </ul>
                                        </li>
                                        <li><strong>If your doctor's office address has changed, contact us immediately to update their address.</strong> Your medication may have been shipped to the doctor's previous address.</li>
                                        <li><strong>If you have recently changed doctors, contact us immediately.</strong></li>
                                        </ol>
                                        <br/>
                                        
                                        <strong>If the medication(s) are not found; please call us at 1-877-296-HOPE (4673).</strong>">?</a>)
                                        
                                        <div class='inline font-12 black'>Hover over the (?) for more details.</div>
        <?php } ?>
        </div>
        
        <div class="clear"></div>
        
        <?= (((isset($med->InAppealsProcessing) && $med->InAppealsProcessing) || (isset($med->NeedPatientInfo) && $med->NeedPatientInfo) || (isset($med->NeedProviderInfo) && $med->NeedProviderInfo)) ? '</div>' : '' ) ?>
        
        <?php if (($key + 1) != count($rxi_data->meds)) { ?>
                                        <br/>
                                        <div class="bottom-light-border-1-small-margins"></div>
                                        <br/>
        <?php } ?>
        -->
    <?php } ?>
</div>

<div class="clear"></div>
<br/><br/>

</div>
</div>

<div id="overlay" class="popup">
    <div id="overlay_holder">
    </div>
</div>

<div id="overlay_add_medication" class="overlay_content">

    <div class="overlay_loaded_content">

        <div class="overlay_form">
            <script>
                $(function () {
                    $("#PrvFirstName").autocomplete({
                        minLength: 0,
                        // source: "testajax.php",
                        source: function (request, response) {
                            var patient_id = "<?php echo $_SESSION['PLP']['patient']->PatientID; ?>";
                            if ($("#PrvFirstName").val() == '' || jQuery("#PrvFirstName").val().length <= 2) {
                                $(".ui-autocomplete").hide();
                            }
                            if ($("#PrvFirstName").val() != '' && $("#PrvFirstName").val().length > 2) {
                                $.ajax({
                                    url: "ajax_get_doctors_list.php",
                                    dataType: "json",
                                    data: {
                                        id: patient_id,
                                        sortType: 'FirstName',
                                        PrvFirstName: $("#PrvFirstName").val()
                                    },
                                    beforeSend: function () {
                                        $("#PrvFirstName").removeClass('correct');
                                        $("#PrvFirstName").addClass('loading-fname');

                                        //jQuery("#msg").fadeOut();
                                    },
                                    success: function (data) {
                                        //console.log(data);
                                        if (data.success == 0) {
                                            $("#PrvFirstName").removeClass('loading-fname');
                                            jQuery(".ui-autocomplete").hide();

                                        } else {
                                            response($.map(data.doctors, function (item) {
                                                console.log(item);
                                                return {
                                                    id: item.PrvProviderId,
                                                    PrvFirstName: item.PrvFirstName,
                                                    PrvLastName: item.PrvLastName,
                                                    PrvPracticeName: item.PrvPracticeName,
                                                    PrvAddress1: item.PrvAddress1,
                                                    PrvAddress2: item.PrvAddress2,
                                                    PrvCity: item.PrvCity,
                                                    PrvState: item.PrvState,
                                                    PrvZip: item.PrvZip,
                                                    PrvWorkPhone: item.PrvWorkPhone,
                                                    PrvFaxNumber: item.PrvFaxNumber,
                                                    PrvProviderId: item.PrvProviderId

                                                };
                                            }));
                                        }

                                    }
                                });
                            } else {
                                $("#PrvFirstName").removeClass('loading-fname');

                            }
                        },
                        focus: function (event, ui) {
                            $("#PrvFirstName").val(ui.item.PrvFirstName);
                            return false;
                        },
                        select: function (event, ui) {
                            //	console.log(ui.item.PrvProviderId);
                            $("#PrvFirstName").val(ui.item.PrvFirstName);
                            $("#PrvLastName").val(ui.item.PrvLastName);
                            $("#PrvPracticeName").val(ui.item.PrvPracticeName);
                            $("#PrvAddress1").val(ui.item.PrvAddress1);
                            $("#PrvAddress2").val(ui.item.PrvAddress2);
                            $("#PrvCity").val(ui.item.PrvCity);
                            $('#PrvState').val(ui.item.PrvState);
                            $("#PrvZip").val(ui.item.PrvZip);
                            $("#PrvWorkPhone").val(ui.item.PrvWorkPhone);
                            $("#PrvFaxNumber").val(ui.item.PrvFaxNumber);
                            //$( "#hiddenPrvProviderId" ).val( ui.item.PrvProviderId );
                            //hiddenPrvProviderId
                            //$("#checkData").val(ui.item.PrvFirstName+ui.item.PrvLastName+ui.item.PrvAddress1+ui.item.PrvAddress2+ui.item.PrvCity+ui.item.PrvState+ui.item.PrvZip+ui.item.PrvWorkPhone+ui.item.PrvFaxNumber);
                            return false;
                        }
                    })
                            .autocomplete("instance")._renderItem = function (ul, item) {
                        $("#PrvFirstName").removeClass('loading-fname');
                        return $("<li>")
                                .append("<div>" + item.PrvFirstName + " " + item.PrvLastName + "<br> Address:" + item.PrvAddress1 + "</div>")
                                .appendTo(ul);
                    };

                    $("#PrvLastName").autocomplete({
                        minLength: 0,
                        // source: "testajax.php",
                        source: function (request, response) {
                            var patient_id = "<?php echo $_SESSION['PLP']['patient']->PatientID; ?>";
                            if ($("#PrvLastName").val() == '' || jQuery("#PrvLastName").val().length <= 2) {
                                $(".ui-autocomplete").hide();
                            }
                            if ($("#PrvLastName").val() != '' && $("#PrvLastName").val().length > 2) {
                                $.ajax({
                                    url: "ajax_get_doctors_list.php",
                                    dataType: "json",
                                    data: {
                                        id: patient_id,
                                        sortType: 'LastName',
                                        PrvFirstName: $("#PrvLastName").val()
                                    },
                                    beforeSend: function () {
                                        $("#PrvLastName").removeClass('correct');
                                        $("#PrvLastName").addClass('loading-lname');

                                        //jQuery("#msg").fadeOut();
                                    },
                                    success: function (data) {
                                        //console.log(data);
                                        if (data.success == 0) {
                                            $("#PrvLastName").removeClass('loading-lname');
                                            $(".ui-autocomplete").hide();

                                        } else {
                                            response($.map(data.doctors, function (item) {
                                                console.log(item);
                                                return {
                                                    id: item.PrvProviderId,
                                                    PrvFirstName: item.PrvFirstName,
                                                    PrvLastName: item.PrvLastName,
                                                    PrvPracticeName: item.PrvPracticeName,
                                                    PrvAddress1: item.PrvAddress1,
                                                    PrvAddress2: item.PrvAddress2,
                                                    PrvCity: item.PrvCity,
                                                    PrvState: item.PrvState,
                                                    PrvZip: item.PrvZip,
                                                    PrvWorkPhone: item.PrvWorkPhone,
                                                    PrvFaxNumber: item.PrvFaxNumber,
                                                    PrvProviderId: item.PrvProviderId

                                                };
                                            }));
                                        }
                                    }
                                });
                            } else {
                                $("#PrvLastName").removeClass('loading-lname');
                            }
                        },
                        focus: function (event, ui) {
                            $("#PrvLastName").val(ui.item.PrvLastName);
                            return false;
                        },
                        select: function (event, ui) {
                            //	console.log(ui.item.PrvProviderId);
                            $("#PrvFirstName").val(ui.item.PrvFirstName);
                            $("#PrvLastName").val(ui.item.PrvLastName);
                            $("#PrvPracticeName").val(ui.item.PrvPracticeName);
                            $("#PrvAddress1").val(ui.item.PrvAddress1);
                            $("#PrvAddress2").val(ui.item.PrvAddress2);
                            $("#PrvCity").val(ui.item.PrvCity);
                            $('#PrvState').val(ui.item.PrvState);
                            $("#PrvZip").val(ui.item.PrvZip);
                            $("#PrvWorkPhone").val(ui.item.PrvWorkPhone);
                            $("#PrvFaxNumber").val(ui.item.PrvFaxNumber);
                            //$( "#hiddenPrvProviderId" ).val( ui.item.PrvProviderId );
                            //hiddenPrvProviderId
                            //$("#checkData").val(ui.item.PrvFirstName+ui.item.PrvLastName+ui.item.PrvAddress1+ui.item.PrvAddress2+ui.item.PrvCity+ui.item.PrvState+ui.item.PrvZip+ui.item.PrvWorkPhone+ui.item.PrvFaxNumber);
                            return false;
                        }
                    })
                            .autocomplete("instance")._renderItem = function (ul, item) {
                        $("#PrvLastName").removeClass('loading-lname');
                        return $("<li>")
                                .append("<div>" + item.PrvFirstName + " " + item.PrvLastName + "<br> Address:" + item.PrvAddress1 + "</div>")
                                .appendTo(ul);
                    };
                });

            </script>
            <center>
                <h3>
                    <b><span id="medication_modal_title">Add Medication</span></b>
                    <a href="javascript:closeOverlay();"><button type="button" class="close" data-dismiss="popup" aria-label="Close"><span aria-hidden="true">&times;</span></button></a>
                </h3>
            </center>

            <br>

            <div id="stopLinkMsg"><div class="col-md-12 alert alert-warning alert-dismissible">Any dosage changes or medications added to your account will be verified by your healthcare provider. If you add a medication or change a dosage, please allow 2-4 weeks from the date of the change to ensure the healthcare provider has enough time to respond to our requests and for us to place the order.</div></div>

            <div class="row zeroed" id="DrugNameRow">
                <div class="col-sm-12">
                    <!--<b>Medication Name<font class="red">*</font></b><br>-->
                    <input placeholder="Medication Name*" type="text" class="autowidth" name="DrugAppliedFor" id="DrugAppliedFor"/>
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-12">
                    <!--<b>Strength<font class="red">*</font></b> (10mg, 20mL, etc)<br>-->
                    <input type="text" class="autowidth" name="Dosage" id="Dosage" placeholder="Strength* (10mg, 20mL, etc)"/>
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-12">
                    <!--<b>Directions</b> (Take twice daily, take with meals, etc)<br>-->
                    <input type="text" class="autowidth" name="Directions" id="Directions" placeholder="Directions* (Take twice daily, take with meals, etc)"/>
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-12">
                    <!--<b>Healthcare Provider<font class="red">*</font></b> (Select from list of added healthcare providers)<br>-->
                    <select name="ProviderId" id="ProviderId" class="form-control login-portal">
                        <option value="">Select a Healthcare Provider*</option>
                        <?php foreach ($rxi_data->providers as $provider) { ?>
                            <option value="<?= $provider->PrvProviderId ?>"><?= $provider->PrvFirstName ?> <?= ((isset($provider->PrvMiddleInitial) && $provider->PrvMiddleInitial != '') ? $provider->PrvMiddleInitial . '.' : '') ?> <?= $provider->PrvLastName ?></option>
                        <?php } ?>
                        <option value="-1">*Add A New Healthcare Provider</option>
                    </select>
                </div>
            </div>

            <div id="formNewProvider">
                <div class="row zeroed">
                    <div class="col-sm-4">
                        <!--<b>First Name<font class="red">*</font></b><br>-->
                        <input type="text" class="autowidth" id="PrvFirstName" placeholder="First Name*"/>
                    </div>
                    <div class="col-sm-4">
                        <!--<b>Last Name<font class="red">*</font></b><br>-->
                        <input type="text" class="autowidth" id="PrvLastName" placeholder="Last Name*"/>
                    </div>
                    <div class="col-sm-4">
                        <!--<b>Practice Name<font class="red">*</font></b><br>-->
                        <input type="text" class="autowidth" id="PrvPracticeName" placeholder="Practice Name*"/>
                    </div>
                </div>

                <div class="row zeroed">
                    <div class="col-sm-6">
                        <!--<b>Address Line 1<font class="red">*</font></b><br>-->
                        <input type="text" class="autowidth" id="PrvAddress1" autocomplete="nope" placeholder="Address Line 1*"/>
                    </div>
                    <div class="col-sm-6">
                        <!--<b>Address Line 2</b><br>-->
                        <input type="text" class="autowidth" id="PrvAddress2" placeholder="Address Line 2"/>
                    </div>
                </div>

                <div class="row zeroed">
                    <div class="col-sm-4">
                        <!--<b>City</b><font class="red">*</font><br>-->
                        <input type="text" class="autowidth" id="PrvCity" placeholder="City*"/>
                    </div>
                    <div class="col-sm-4">
                        <!--<b>State</b><font class="red">*</font><br>-->
                        <select name="PrvState" id="PrvState" class="form-control login-portal">
                            <option value="">State*</option>
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
                        <!--<b>Zip</b><font class="red">*</font><br>-->
                        <input type="text" class="autowidth numeric" id="PrvZip" maxlength="5" placeholder="Zip*"/>
                    </div>
                </div>

                <div class="row zeroed">
                    <div class="col-sm-6">
                        <!--<b>Phone Number<font class="red">*</font></b><br>-->
                        <input type="text" class="autowidth phone_us" id="PrvWorkPhone" placeholder="Phone Number*"/>
                    </div>
                    <div class="col-sm-6">
                        <!--<b>Fax Number</b><br>-->
                        <input type="text" class="autowidth phone_us" id="PrvFaxNumber" placeholder="Fax Number"/>
                    </div>
                </div>
            </div>
            <div class="row zeroed">
                <div class="col-sm-12">
                <!--<div class="text-left required-fields s_v"><span class="red">*</span>Required Field</div>-->
                    <br/>
                    <p>

                        <a href="javascript:addMedication();" class="big-button-sm whiteButtonSM btAddMedication"><i class="fa fa-plus-square"></i> Add Another Medication</a>
                        <a id="btnSaveMedication" href="javascript:saveNewMedication();" class="big-button whiteButton">Save Medication</a></p>
                    <a id="btnSavingMedication" href="javascript:void(0);" class="big-button whiteButton" style="display:none;" enabled="false">Saving...</a>
                </div>
            </div>
        </div>



    </div>
</div>

<div class="tooltip_templates">
    <div id="tooltipContent_estimatedDeliveryDate">
        <strong>If you have not received your medication by the listed Estimated Delivery Date, please do the following:</strong>
        <br/><br/>
        For medication(s) normally delivered to your home address:
        <br/>
        <ol>
            <li><strong>Please double-check your medication supplies</strong> to make sure that it was not just overlooked.</li>
            <li><strong>If your address has changed, contact us immediately to update your address.</strong> Your medication may have been shipped to your previous address.</li>
        </ol>
        <br/>

        <div style="margin-left:15px">
            For medication(s) normally delivered to your doctor's office:<br/>
            <ol>
                <li>
                    <strong>Please call your doctor's office</strong> and ask a member of the nursing staff to check in the following areas for your medication(s):
                    <ul>
                        <li>The sample drawer/closet</li>
                        <li>The doctor's personal desk, inside and out (the medication is in a box addressed to the doctor, but there is an invoice within the box that has your information on it)</li>
                        <li>For insulins, have the refrigerators checked (even the break room refrigerator)</li>
                    </ul>
                </li>
                <li><strong>If your doctor's office address has changed, contact us immediately to update their address.</strong> Your medication may have been shipped to the doctor's previous address.</li>
                <li><strong>If you have recently changed doctors, contact us immediately.</strong></li>
            </ol>
            <br/>
            <strong>If the medication(s) are not found; please call us at 1-877-296-HOPE (4673).</strong>
        </div>

    </div>
</div>

<div id="overlay_add_provider" class="overlay_content">

    <div class="overlay_loaded_content">

        <div class="overlay_form">
            <center>
                <h3><b><span id="medication_modal_title"></span></b></h3>
            </center>

            <div class="row zeroed">
                <div class="col-sm-4">
                    <b>First Name<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PrvFirstName"/>
                </div>
                <div class="col-sm-4">
                    <b>Last Name<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PrvLastName"/>
                </div>
                <div class="col-sm-4">
                    <b>Practice Name<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PrvPracticeName"/>
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-6">
                    <b>Address Line 1<font class="red">*</font></b><br>
                    <input type="text" class="autowidth" id="PrvAddress1" autocomplete="nope"/>
                </div>
                <div class="col-sm-6">
                    <b>Address Line 2</b><br>
                    <input type="text" class="autowidth" id="PrvAddress2"/>
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-4">
                    <b>City</b><font class="red">*</font><br>
                    <input type="text" class="autowidth" id="PrvCity"/>
                </div>
                <div class="col-sm-4">
                    <b>State</b><font class="red">*</font><br>
                    <select name="PrvState" id="PrvState" class="form-control login-portal">
                        <option value="">...</option>
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
                    <b>Zip</b><font class="red">*</font><br>
                    <input type="text" class="autowidth numeric" id="PrvZip" maxlength="5"/>
                </div>
            </div>

            <div class="row zeroed">
                <div class="col-sm-6">
                    <b>Phone Number<font class="red">*</font></b><br>
                    <input type="text" class="autowidth phone_us" id="PrvWorkPhone"/>
                </div>
                <div class="col-sm-6">
                    <b>Fax Number</b><br>
                    <input type="text" class="autowidth phone_us" id="PrvFaxNumber"/>
                </div>
            </div>

            <center>
                <a href="#" class="hidden big-button-sm whiteButtonSM"><i class="fa fa-plus-square"></i> Add Another Healthcare Provider</a>
            </center>
        </div>

        <div class="alignLeft" style="padding-top:10px;">
            <div class="text-left required-fields"><span class="red">*</span>Required Field</div>
            <br>

            <br><br>
            <a id="btnSaveProvider" href="javascript:saveNewProvider();" class="big-button whiteButton">Save Provider</a>
            <a id="btnSavingProvider" href="javascript:void(0);" class="big-button whiteButton" style="display:none;">Saving...</a>

            <br><br>
            <a href="javascript:closeOverlay();" class="small-button">Go Back</a>
        </div>

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        if (jQuery('.arrival-date').length > 0) {
            jQuery('#med_status').text('Estimated Arrival Date');
        }

        jQuery('.med_item .view-medi').click(function () {
            var thisId = jQuery(this).parents('.med_item').attr('id');
            // check if View Details is clicked or Hide Details
            if (jQuery(this).parents('.med_item').hasClass('med-active')) {
                jQuery('.table-wrap-desc').find('#' + thisId.replace('med', 'meddesc')).find('.close_desc').trigger('click');
            } else {
                jQuery('.med_item .view-medi').each(function () {
                    jQuery(this).html('<i class="fa fa-eye" aria-hidden="true"></i> <span>View Details</span>');
                });
                jQuery('.table-wrap-desc').find('.meddesc-box').hide();
                jQuery('.table-wrap-desc').show();
                jQuery(this).parents('.med_item').siblings().removeClass('med-active');
                jQuery(this).parents('.med_item').addClass('med-active');
                jQuery('#tables_main').addClass('showmeddata');
                jQuery('.table-wrap').find('#' + thisId.replace('med', 'view') + '>a').html('<i class="fa fa-eye-slash" aria-hidden="true"></i> <span>Hide Details</span>');
                jQuery('.table-wrap-desc').find('#' + thisId.replace('med', 'meddesc')).fadeIn(500);
            }
        });

        jQuery('.close_desc').click(function () {
            var thisId = jQuery(this).attr('data-id');
            jQuery('#' + thisId).show();
            jQuery('#' + thisId.replace('meddesc', 'med')).removeClass('med-active');
            jQuery('#tables_main').removeClass('showmeddata');
            jQuery('.table-wrap').find('#' + thisId.replace('meddesc', 'view') + '>a').html('<i class="fa fa-eye" aria-hidden="true"></i> <span>View Details</span>');
            jQuery('.table-wrap-desc').find('#' + thisId).fadeOut(100);
        });

        if (location.hash.substr(1) == 'add_new_med') {
            var urlParams = parse_url(window.location.href);
            if (window.location.href.indexOf('#') > -1) {
                history.pushState('', document.title, window.location.pathname);
            }
            setTimeout(function () {
                var medname = (urlParams.query).replace('med_name=', '');
                jQuery('#DrugNameRow #DrugAppliedFor').val(decodeURI(medname.trim()));
            }, 1000);
            addMedication();
        }
    });

    function parse_url(str, component) {
        var query

        var mode = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.parse_url.mode') : undefined) || 'php'

        var key = [
            'source',
            'scheme',
            'authority',
            'userInfo',
            'user',
            'pass',
            'host',
            'port',
            'relative',
            'path',
            'directory',
            'file',
            'query',
            'fragment'
        ]

        // For loose we added one optional slash to post-scheme to catch file:/// (should restrict this)
        var parser = {
            php: new RegExp([
                '(?:([^:\\/?#]+):)?',
                '(?:\\/\\/()(?:(?:()(?:([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?))?',
                '()',
                '(?:(()(?:(?:[^?#\\/]*\\/)*)()(?:[^?#]*))(?:\\?([^#]*))?(?:#(.*))?)'
            ].join('')),
            strict: new RegExp([
                '(?:([^:\\/?#]+):)?',
                '(?:\\/\\/((?:(([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?))?',
                '((((?:[^?#\\/]*\\/)*)([^?#]*))(?:\\?([^#]*))?(?:#(.*))?)'
            ].join('')),
            loose: new RegExp([
                '(?:(?![^:@]+:[^:@\\/]*@)([^:\\/?#.]+):)?',
                '(?:\\/\\/\\/?)?',
                '((?:(([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?)',
                '(((\\/(?:[^?#](?![^?#\\/]*\\.[^?#\\/.]+(?:[?#]|$)))*\\/?)?([^?#\\/]*))',
                '(?:\\?([^#]*))?(?:#(.*))?)'
            ].join(''))
        }

        var m = parser[mode].exec(str)
        var uri = {}
        var i = 14

        while (i--) {
            if (m[i]) {
                uri[key[i]] = m[i]
            }
        }

        if (component) {
            return uri[component.replace('PHP_URL_', '').toLowerCase()]
        }

        if (mode !== 'php') {
            var name = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.parse_url.queryKey') : undefined) || 'queryKey'
            parser = /(?:^|&)([^&=]*)=?([^&]*)/g
            uri[name] = {}
            query = uri[key[12]] || ''
            query.replace(parser, function ($0, $1, $2) {
                if ($1) {
                    uri[name][$1] = $2
                }
            })
        }

        delete uri.source
        return uri
    }
<?php if (isset($_GET['action'])): ?>
        jQuery(function () {
    <?php if ($_GET['action'] == 'new'): ?>
                javascript:addMedication();
    <?php else: ?>
                javascript:editMedication('<?php echo $_GET['action'] ?>');
    <?php endif; ?>
        });
<?php endif; ?>
</script>
<?php include('_footer.php'); ?>
