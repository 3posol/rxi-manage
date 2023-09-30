<?php
//echo "<pre>";print_r($_SESSION);echo "</pre>";
$data = array(
    'command' => 'get_heading_message',
    'patient' => $_SESSION['PLP']['patient']->PatientID,
    'access_code' => $_SESSION['PLP']['access_code']
);
//echo "<pre>";print_r($data);echo "</pre>";
$heading_message_data = api_command($data);
//echo "<pre>";print_r($heading_message_data);echo "</pre>";
if ($heading_message_data->success == 1) {
    ?>
    <div class="alert alert-info" role="alert" style="text-align: center;">
        <?php
        echo $heading_message_data->heading_msg;
        ?>
    </div>
    <?php
}
?>
<?php // Due to the Coronavirus (COVID-19), Customer Service may be longer than expected, as our call volumes have been increasing. Processing times with the pharmaceutical compaines are also delayed, resulting in longs( shipping times. We are working diligently to make sure your requests are taken care at promptly and we apreciate your patience during this pandemic. ?>
<?php $vcf_text = 'Save Prescription Hope\'s contact info to your phone. Know when we ore calling. <a href="javascript:void(0)" onclick="sendVcfSms()">Click here</a> to receive a SMS text message to add our contract information and not miss our coils.' ?>
<?php $vcf_text = 'Save Prescription Hope\'s contact info to your phone to know when we ore calling. <a href="javascript:void(0)" onclick="sendVcfSms()">Click here</a> to receive a SMS text message to add our contact information and not miss our calls.' ?>

<div class="row new_vcf_msg">
    <div class="col-sm-12 col-md-7 new_vcf_msg_coved">
        <div class="alert alert-info cb-alert-info" role="alert">
            <span class="img"><img src="images\megaphone.png"></span>
            <span class="test">Due to the Coronavirus (COVID-19), Customer Service may be longer than expected, as our call volumes have been increasing. Processing times with the pharmaceutical compaines are also delayed, resulting in longs( shipping times. We are working diligently to make sure your requests are taken care at promptly and we apreciate your patience during this pandemic.</span>
        </div>
    </div>
    <div class="col-sm-12 col-md-5 new_vcf_msg_download">
        <div class="alert alert-success cb-alert-info" role="alert" style="text-align: center;">
            <span class="img"><img src="images\message.png"></span>
            <span class="test"><?php echo $vcf_text;?></span>
        </div>
    </div>
</div>
<script>
    var sendvcfReq = false;
    function sendVcfSms(){
        if(sendvcfReq){
            return false;
        }
        sendvcfReq = true;
        jQuery.ajax({
            url: "/enrollment/sendvcf.php",
            dataType: "json",
            beforeSend: function () {
            },
            success: function (data) {
                sendvcfReq = false;
                alert('Card send successfully.');
            }
        });
    }
</script>

<nav class="navbar navbar-full navbar-dark bg-primary" id="stciky">
    <div id="toggle">
        <div class="one"></div>
        <div class="two"></div>
        <div class="three"></div>
    </div>
    <div class="navbar-collapse" id="mainNavbarCollapse">
        <ul class="nav navbar-nav" id="menu">
            <li class="nav-item" id="dashboard">
                <?php $class = ( strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false ) ? 'nav-active' : '' ?>
                <a class="not_href_rmv header_icon_box_off <?= $class ?>" href="dashboard.php"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="22px" height="22px" viewBox="0 0 510 510" style="enable-background:new 0 0 510 510;" xml:space="preserve">
                        <g><g id="home"><polygon points="204,471.75 204,318.75 306,318.75 306,471.75 433.5,471.75 433.5,267.75 510,267.75 255,38.25 0,267.75     76.5,267.75 76.5,471.75"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg><span>Dashboard</span></a>
            </li>
            <li class="nav-item" id="medication">
                <?php $class = ( strpos($_SERVER['REQUEST_URI'], 'medication.php') !== false || strpos($_SERVER['REQUEST_URI'], 'providers.php') !== false ) ? 'nav-active' : '' ?>
                <a class="header_icon_box <?= $class ?>" href="<?php echo ($_SESSION['PLP']['patient']->DateDisenrolled != '') ? 'javascript:void(0);' : 'medication.php' ?>"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" width="22px" height="22px" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><g><path d="M472.196,39.468c-52.654-52.625-138.301-52.625-190.955,0L169.915,150.794l126.394,126.394    c26.014-21.663,58.995-35.227,95.412-35.227c20.888,0,40.765,4.339,58.851,12.087l21.624-23.624    C524.835,177.784,524.835,92.123,472.196,39.468z"/></g></g><g><g><path d="M275.27,298.577L148.7,172.008L39.742,281.073c-52.639,52.639-52.639,138.301,0,190.955    c52.653,52.622,139.3,53.626,191.955,0.999l22.088-22.194c-7.748-18.086-12.087-37.963-12.087-58.851    C241.699,356.5,254.599,324.291,275.27,298.577z"/></g></g><g><g><path d="M271.703,391.983c0,61.144,45.893,111.045,105.015,118.504V273.478C317.596,280.937,271.703,330.836,271.703,391.983z"/></g></g><g><g><path d="M406.722,273.478v237.009c59.122-7.459,105.015-57.36,105.015-118.505S465.845,280.937,406.722,273.478z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg><span>Medications</span></a>
            </li>
            <li class="nav-item" id="billing">
                <?php $class = ( strpos($_SERVER['REQUEST_URI'], 'billing.php') !== false ) ? 'nav-active' : '' ?>
                <a class="header_icon_box <?= $class ?>" href="billing.php"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" width="22px" height="22px" viewBox="0 0 25 24.61" style="enable-background:new 0 0 612 612;"xml:space="preserve"><g><defs><rect id="SVGID_1_" y="0" width="25" height="24.61"/></defs><clipPath id="SVGID_2_"><use xlink:href="#SVGID_1_" overflow="visible"/></clipPath><path clip-path="url(#SVGID_2_)"d="M19.75,0c-2.899,0-5.25,2.351-5.25,5.25c0,2.9,2.352,5.25,5.25,5.25   c2.899,0,5.25-2.35,5.25-5.25C24.996,2.352,22.647,0.003,19.75,0"/><path clip-path="url(#SVGID_2_)" d="M13.833,5.261c0-0.169,0.02-0.328,0.033-0.492H0V24.31   c0,0.006,0.038,0.01,0.053-0.004l2.326-1.936c0.255-0.209,0.621-0.213,0.88-0.009l2.777,2.241c0.018,0.013,0.041,0.011,0.058-0.004   l2.738-2.233c0.255-0.206,0.62-0.208,0.877-0.003l2.745,2.24c0.019,0.013,0.041,0.011,0.057-0.003l2.769-2.234   c0.257-0.207,0.622-0.206,0.877,0.002l2.32,1.939c0.014,0.013,0.037,0.009,0.037,0.003V11.03   C15.785,10.455,13.833,8.049,13.833,5.261"/><path id='s' clip-path="url(#SVGID_2_)" fill="#FFFFFF" d="M3.968,8.482h5.444c0.181,0,0.327,0.146,0.327,0.328   c0,0.18-0.146,0.327-0.327,0.327H3.968c-0.181,0-0.328-0.147-0.328-0.327C3.64,8.628,3.787,8.482,3.968,8.482"/><path id='s'  clip-path="url(#SVGID_2_)" fill="#FFFFFF" d="M14.577,16.346H3.968c-0.182,0-0.328-0.146-0.328-0.327   s0.146-0.327,0.328-0.327h10.609c0.181,0,0.327,0.146,0.327,0.327S14.758,16.346,14.577,16.346"/><path id='s'  clip-path="url(#SVGID_2_)" fill="#FFFFFF" d="M14.577,12.742H3.968c-0.182,0-0.328-0.146-0.328-0.328   c0-0.181,0.146-0.327,0.328-0.327h10.609c0.181,0,0.327,0.146,0.327,0.327C14.904,12.596,14.758,12.742,14.577,12.742"/><path id='s'  clip-path="url(#SVGID_2_)" fill="#FFFFFF" d="M19.75,5.107c0.979,0,1.771,0.794,1.771,1.772c0,0.929-0.717,1.7-1.644,1.768   v0.545c0,0.182-0.147,0.328-0.328,0.328s-0.326-0.146-0.326-0.328V8.571c-0.741-0.232-1.244-0.916-1.245-1.692   c0-0.181,0.146-0.327,0.328-0.327c0.18,0,0.326,0.146,0.326,0.327c0,0.617,0.5,1.117,1.116,1.118c0.617,0,1.117-0.5,1.117-1.117   c0.001-0.617-0.499-1.117-1.116-1.117c-0.979,0-1.771-0.793-1.771-1.772c0-0.86,0.615-1.595,1.462-1.745V1.82   c0-0.181,0.146-0.328,0.329-0.328c0.18,0,0.327,0.147,0.327,0.328v0.433c0.828,0.166,1.425,0.893,1.425,1.737   c0,0.181-0.146,0.328-0.327,0.328c-0.18,0-0.328-0.147-0.328-0.328c0-0.616-0.5-1.116-1.117-1.116c-0.616,0-1.116,0.5-1.116,1.117   C18.633,4.607,19.133,5.107,19.75,5.107z"/></g></svg><span>Billing</span></a>
            </li>
            <li class="nav-item" id="calender" style="display: none;">
                <?php $class = ( strpos($_SERVER['REQUEST_URI'], 'calender.php') !== false ) ? 'nav-active' : '' ?>
                <a class="header_icon_box <?= $class ?>" href="calender.php"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="22px" height="22px" viewBox="0 0 612 612" style="enable-background:new 0 0 612 612;" xml:space="preserve"><g><g><path d="M466.286,87.458V29.114C466.286,13.027,453.2,0,437.143,0C421.056,0,408,13.027,408,29.114v58.344    c0,16.087,13.085,29.114,29.143,29.114C453.229,116.571,466.286,103.544,466.286,87.458z"/><path d="M182.143,116.571c12.065,0,21.857-9.733,21.857-21.915V21.916C204,9.821,194.237,0,182.143,0h-14.572    c-12.065,0-21.857,9.734-21.857,21.916v72.711c0,12.124,9.763,21.944,21.857,21.944H182.143z"/><path d="M18.331,58.286C8.189,58.286,0,66.766,0,77.17v515.916C0,603.549,8.189,612,18.302,612h575.367    c10.142,0,18.331-8.48,18.331-18.914V77.17c0-10.433-8.219-18.885-18.331-18.885h-98.24v54.526    c0,18.302-19.584,32.902-43.743,32.902h-29.085c-24.247,0-43.743-14.717-43.743-32.902V58.286H233.143v54.526    c0,18.302-19.584,32.902-43.744,32.902h-29.084c-24.247,0-43.744-14.717-43.744-32.902V58.286H18.331z M495.429,262.286h87.429    v87.428h-87.429V262.286z M495.429,378.857h87.429v87.429h-87.429V378.857z M495.429,495.429h87.429v76.529    c0,6.003-5.596,10.899-12.531,10.899h-74.897V495.429z M378.857,262.286h87.429v87.428h-87.429V262.286z M378.857,378.857h87.429    v87.429h-87.429V378.857z M378.857,495.429h87.429v87.429h-87.429V495.429z M262.286,262.286h87.428v87.428h-87.428V262.286z     M262.286,495.429h87.428v87.429h-87.428V495.429z M145.714,262.286h87.429v87.428h-87.429V262.286z M145.714,378.857h87.429    v87.429h-87.429V378.857z M145.714,495.429h87.429v87.429h-87.429V495.429z M116.571,582.857H41.674    c-6.936,0-12.531-4.867-12.531-10.899v-76.529h87.428V582.857z M116.571,466.286H29.143v-87.429h87.428V466.286z M116.571,349.714    H29.143v-87.428h87.428V349.714z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg> <span>Calendar</span></a>
            </li>
        </ul>
        <?php
        if (isset($_SESSION['PLP']['patient']->MetaData) && $_SESSION['PLP']['patient']->MetaData->profile_image != '') {
            $pimage = 'patient_images/' . $_SESSION['PLP']['patient']->MetaData->profile_image;
        } else {
            $pimage = $basepath . '/images/Medication/user.png';
        }
        ?>
        <ul class="pull-lg-right">
            <li class="nav-item dropdown"><a class="nav-link not_href_rmv" id="navbarDropdown1" data-target="#" href="<?php echo ($_SESSION['PLP']['patient']->DateDisenrolled != '') ? 'javascript:void(0);' : 'account.php' ?>">Account Setting</a></li>
            <li class="profile-image-content"><a class="Profile" href="#">
                    <?php if (isset($_SESSION['PLP']['patient']->force_login) && $_SESSION['PLP']['patient']->force_login): ?>
                        <?php echo $_SESSION['PLP']['patient']->first_name . " " . $_SESSION['PLP']['patient']->middle_initial ?>
                    <?php else: ?>
                        <?php echo $_SESSION['PLP']['patient']->PreferredName ?> 
                    <?php endif; ?>

                </a></li>
            <li class="nav-item profile-imagess"><a class="navbar-brand" href="#"><img class="img-rounded profile" src="<?php echo $pimage ?>"></a></li>
            <li class=" profile-bell" style="display:none;"><a class="navbar-brand" href="#"><img class="img-rounded" src="<?= $basepath ?>/images/Medication/bell.png"></a></li>
        </ul>
    </div>
</nav>

<?php 

if(isset($_GET['cgdebug']) && $_GET['cgdebug']){
    echo '<pre>';
    print_r($_SESSION);
    die;
}
if ($_SESSION['PLP']['patient']->DateDisenrolled != '') { ?>
    <div class="row account_summary_section" id="stopLinkMsg">
        <div class="col-md-12 alert alert-warning alert-dismissible">
            <a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a>
            <p>Your Prescription Hope account closed on <?= date('m/d/Y', strtotime($_SESSION['PLP']['patient']->DateDisenrolled)) ?>. Contact us at <a href="tel:18772964673">1-877-296-4673</a> if you feel this is an error.</p>
            <p>
                <span class="document_button">
                    <form method="post" action="reapply.php">
                        <input type="hidden" name="pid" id="pid" value="<?= $_SESSION['PLP']['patient']->PatientID ?>" />
                        <input type="hidden" name="email" id="email" value="<?= $_SESSION['PLP']['patient']->account_username ?>" />
                        <input type="hidden" name="type" id="type" value="closed" />
                        <button type="submit" class="btn btn-primary btn-cust">Click Here To Re-apply</button>
                    </form>
                </span></p>
        </div>
    </div>
<?php } ?>

<script>
    window.onscroll = function () {
        myFunction();
    };

    var header = document.getElementById("stciky");
    var sticky = header.offsetTop;

    function myFunction() {
        var s_h = sticky + parseInt(jQuery(".navbar").height()) + parseInt(30);
        if (window.pageYOffset > s_h) {
            header.classList.add("sticky");
        } else {
            header.classList.remove("sticky");
        }
    }
</script>

<?php if (isset($_SESSION['PLP']['patient']->force_login) && $_SESSION['PLP']['patient']->force_login): ?>
    <script>
        jQuery(function () {
            jQuery('a').not('.not_href_rmv').attr('href',"javascript:void(0)");
        });
    </script>
<?php endif; ?>