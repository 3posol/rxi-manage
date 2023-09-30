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
    'command' => 'get_billing',
    'patient' => $_SESSION['PLP']['patient']->PatientID,
    'access_code' => $_SESSION['PLP']['access_code']
);

$billing_information = api_command($data);

$data = array(
    'cc_type' => '',
    'cc_number' => '',
    'cc_exp_month' => '',
    'cc_exp_year' => '',
    'cc_cvv' => '',
    'patient_name' => $_SESSION['PLP']['patient']->PatientFirstName . ' ' . $_SESSION['PLP']['patient']->PatientLastName,
    'patient_address' => $_SESSION['PLP']['patient']->PatientAddress1,
    'patient_city' => $_SESSION['PLP']['patient']->PatientCity_1,
    'patient_state' => $_SESSION['PLP']['patient']->PatientState_1,
    'patient_zip' => $_SESSION['PLP']['patient']->PatientZip_1
);

$arrCCExp = explode("/", $_POST['cc_exp']);

$success = true;
$message = '';
if ((isset($_POST['cc_number']))) {
    $data = array(
        'cc_type' => (isset($_POST['cc_type'])) ? trim($_POST['cc_type']) : '',
        'cc_number' => (isset($_POST['cc_number'])) ? trim($_POST['cc_number']) : '',
        'cc_exp_month' => (isset($arrCCExp)) ? trim($arrCCExp[0]) : '',
        'cc_exp_year' => (isset($arrCCExp)) ? trim($arrCCExp[1]) : '',
        'cc_cvv' => (isset($_POST['cc_cvv'])) ? trim($_POST['cc_cvv']) : '',
        'patient_name' => (isset($_POST['PatientFirstNameBilling'])) ? trim($_POST['PatientFirstNameBilling']) . " " . trim($_POST['PatientLastNameBilling']) : '',
        'patient_address' => (isset($_POST['PatientAddress1Billing'])) ? trim($_POST['PatientAddress1Billing']) : '',
        'patient_city' => (isset($_POST['PatientCity_1Billing'])) ? trim($_POST['PatientCity_1Billing']) : '',
        'patient_state' => (isset($_POST['PatientState_1Billing'])) ? trim($_POST['PatientState_1Billing']) : '',
        'patient_zip' => (isset($_POST['PatientZip_1Billing'])) ? trim($_POST['PatientZip_1Billing']) : '',
    );

    if ($data['cc_type'] != '' && $arrCCExp[0] != '' && $arrCCExp[1] != '' && $data['cc_exp_year'] != '' && (int) $data['cc_cvv'] > 0 && (strlen($data['cc_cvv']) == 3 || strlen($data['cc_cvv']) == 4) && $data['patient_name'] != '' && $data['patient_address'] != '' && $data['patient_city'] != '' && $data['patient_state'] != '' && $data['patient_zip'] != '') {
        //for encoding
        $encode_key = pack('H*', md5($_SESSION['PLP']['patient']->PatientID));
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        //encode data
        $payment_data = array();
        foreach ($data as $key => $value) {
            $payment_data[$key] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encode_key, $value, MCRYPT_MODE_CFB, $iv));
        }
        $payment_data['iv'] = base64_encode($iv);

        $api_data = array(
            'command' => 'update_payment_method',
            'patient' => $_SESSION['PLP']['patient']->PatientID,
            'access_code' => $_SESSION['PLP']['access_code'],
            'data' => $payment_data,
            'by' => (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
        );

        $response = api_command($api_data);
//        print_r($response);
//        die;
        if (isset($response->success) && $response->success == 1) {
            //success
            $success = true;
            $message = 'You\'re payment information was successfully updated.';
#			header('Location: billing.php?success=1');
        } else {
            //fail
            $success = false;
            $message = 'The provided credit card is not valid. Please try submitting the form again with a valid credit card.';
        }
    } else {
        //invalid form
        $success = false;
        $message = 'Some data is missing, please fill all the fields and try again.';
    }

    $arrReturn = array(
        'success' => $success,
        'message' => $message
    );
    echo json_encode($arrReturn);
    die();
}
?>

<?php include('_header.php'); ?>

<div class="content">
    <div class="container">
        <h2>Update Payment Information</h2>
        <br/>

        <div class="right-content">
            &nbsp;
        </div>

        <div class="left-content">
            <div id="fmMsg" class="<?= (($message != '' && !$success) ? 'error' : 'bold') ?>"><?= $message ?></div>

<?php if (!$success || !isset($_POST['cc_number'])) { ?>

                <form id="fmPM" action="update_payment_information.php" method="post">
                    <div class="label bold">Current Payment Information:</div>
                    <div class="value"><?= $billing_information->payment_info ?></div>

                    <div class="clear"></div><br/>

                    <label for="patient_name" class="label-long <?= ((!$success && $data['patient_name'] == '') ? 'error' : '') ?>">Full Name*:</label>
                    <input type="text" name="patient_name" id="patient_name" value="<?= addslashes($data['patient_name']) ?>" class="<?= ((!$success && $data['patient_name'] == '') ? 'error' : '') ?>" />
                    <br/><br/>

                    <label for="patient_address" class="label-long <?= ((!$success && $data['patient_address'] == '') ? 'error' : '') ?>">Address*:</label>
                    <input type="text" name="patient_address" id="patient_address" value="<?= addslashes($data['patient_address']) ?>" class="<?= ((!$success && $data['patient_address'] == '') ? 'error' : '') ?>" />
                    <br/><br/>

                    <label for="patient_city" class="label-long <?= ((!$success && $data['patient_city'] == '') ? 'error' : '') ?>">City*:</label>
                    <input type="text" name="patient_city" id="patient_city" value="<?= addslashes($data['patient_city']) ?>" class="<?= ((!$success && $data['patient_city'] == '') ? 'error' : '') ?>" />
                    <br/><br/>

                    <label for="patient_state" class="label-long <?= ((!$success && $data['patient_state'] == '') ? 'error' : '') ?>">State*:</label>
                    <select name="patient_state" id="patient_state" class="<?= ((!$success && $data['patient_state'] == '') ? 'error' : '') ?>">
                        <option value="">...</option>
    <?php foreach ($us_states as $key => $state) { ?>
                            <option value="<?= $key ?>" <?= (($data['patient_state'] == $key) ? 'selected="selected"' : '') ?>><?= $state ?></option>
                        <?php } ?>
                    </select>
                    <br/><br/>

                    <label for="patient_zip" class="label-long <?= ((!$success && $data['patient_zip'] == '') ? 'error' : '') ?>">Zip Code*:</label>
                    <input type="text" name="patient_zip" id="patient_zip" value="<?= addslashes($data['patient_zip']) ?>" class="<?= ((!$success && $data['patient_zip'] == '') ? 'error' : '') ?>" />
                    <br/><br/><br/>

                    <label for="cc_type" class="label-long <?= ((!$success && $data['cc_type'] == '') ? 'error' : '') ?>">Credit Card Type:</label>
                    <select name="cc_type" id="cc_type" class="<?= ((!$success && $data['cc_type'] == '') ? 'error' : '') ?>">
                        <option value=''></option>
                        <option value='A' <?= (($data['cc_type'] == 'A') ? 'selected="selected"' : '') ?>>American Express</option>
                        <option value='D' <?= (($data['cc_type'] == 'D') ? 'selected="selected"' : '') ?>>Discover</option>
                        <option value='M' <?= (($data['cc_type'] == 'M') ? 'selected="selected"' : '') ?>>Mastercard</option>
                        <option value='V' <?= (($data['cc_type'] == 'V') ? 'selected="selected"' : '') ?>>VISA</option>
                    </select>
                    <br/><br/>

                    <label for="cc_number" class="label-long <?= ((!$success && $data['cc_number'] == '') ? 'error' : '') ?>">Credit Card Number:</label>
                    <input type="text" name="cc_number" id="cc_number" value="<?= addslashes($data['cc_number']) ?>" class="<?= ((!$success && $data['cc_number'] == '') ? 'error' : '') ?>" />
                    <br/><br/>

                    <label for="cc_exp_month" class="label-long <?= ((!$success && $data['cc_exp_month'] == '') ? 'error' : '') ?>">Credit Card Expiration Month:</label>
                    <select name="cc_exp_month" id="cc_exp_month" class="<?= ((!$success && $data['cc_exp_month'] == '') ? 'error' : '') ?>">
                        <option value=''></option>
                        <option value='1' <?= (($data['cc_exp_month'] == '1') ? 'selected="selected"' : '') ?>>January</option>
                        <option value='2' <?= (($data['cc_exp_month'] == '2') ? 'selected="selected"' : '') ?>>February</option>
                        <option value='3' <?= (($data['cc_exp_month'] == '3') ? 'selected="selected"' : '') ?>>March</option>
                        <option value='4' <?= (($data['cc_exp_month'] == '4') ? 'selected="selected"' : '') ?>>April</option>
                        <option value='5' <?= (($data['cc_exp_month'] == '5') ? 'selected="selected"' : '') ?>>May</option>
                        <option value='6' <?= (($data['cc_exp_month'] == '6') ? 'selected="selected"' : '') ?>>June</option>
                        <option value='7' <?= (($data['cc_exp_month'] == '7') ? 'selected="selected"' : '') ?>>July</option>
                        <option value='8' <?= (($data['cc_exp_month'] == '8') ? 'selected="selected"' : '') ?>>August</option>
                        <option value='9' <?= (($data['cc_exp_month'] == '9') ? 'selected="selected"' : '') ?>>September</option>
                        <option value='10' <?= (($data['cc_exp_month'] == '10') ? 'selected="selected"' : '') ?>>October</option>
                        <option value='11' <?= (($data['cc_exp_month'] == '11') ? 'selected="selected"' : '') ?>>November</option>
                        <option value='12' <?= (($data['cc_exp_month'] == '12') ? 'selected="selected"' : '') ?>>December</option>
                    </select>
                    <br/><br/>

                    <label for="cc_exp_year" class="label-long <?= ((!$success && $data['cc_exp_year'] == '') ? 'error' : '') ?>">Credit Card Expiration Year:</label>
                    <select name="cc_exp_year" id="cc_exp_year" class="<?= ((!$success && $data['cc_exp_year'] == '') ? 'error' : '') ?>">
                        <option value=''></option>
    <?php for ($i = 0; $i < 10; $i++) {
        $year = (int) date('Y') + $i; ?>
                            <option value="<?= $year ?>" <?= (($year == $data['cc_exp_year']) ? 'selected="selected"' : '') ?>><?= $year ?></option>
                        <?php } ?>
                    </select>
                    <br/><br/>

                    <label for="cc_cvv" class="label-long <?= ((!$success && $data['cc_cvv'] == '') ? 'error' : '') ?>">CVV Security Code (<a href="#" rel="images/cvv4.jpg" class="disable-click">?</a>):</label>
                    <input type="text" name="cc_cvv" id="cc_cvv" value="<?= addslashes($data['cc_cvv']) ?>" class="<?= ((!$success && $data['cc_cvv'] == '') ? 'error' : '') ?>" />
                    <br/><br/>

                    <input type="submit" name="btSave" id="btSave" value="Update Information">
                    &nbsp;<a href="billing.php">Cancel</a>
                </form>

            <?php } ?>

        </div>

        <div class="clear"></div>

    </div>
</div>

<script type="text/javascript">

    jQuery().ready(function () {
        jQuery("#fmPM").validate({
            rules: {
                cc_type: {required: true},
                cc_number: {required: true, creditcardtypes: function (element) {
                        return {visa: (jQuery('select[name=cc_type]').val() == "V"), mastercard: (jQuery('select[name=cc_type]').val() == "M"), amex: (jQuery('select[name=cc_type]').val() == "A"), discover: (jQuery('select[name=cc_type]').val() == "D")};
                    }},
                cc_exp_month: {required: true, min: function (element) {
                        return (jQuery('select[name=cc_exp_year]').val() != '<?php echo date('Y'); ?>') ? '01' : '<?php echo date('m'); ?>';
                    }},
                cc_exp_year: {required: true, min: function (element) {
                        return (jQuery('select[name=cc_exp_month]').val() < '<?php echo date('m'); ?>') ? '<?php echo (int) date('Y') + 1; ?>' : '<?php echo date('Y'); ?>';
                    }},
                cc_cvv: {required: true, digits: true, minlength: 3, maxlength: 4},
                patient_name: {required: true},
                patient_address: {required: true},
                patient_city: {required: true},
                patient_state: {required: true},
                patient_zip: {required: true, digits: true}
            },

            highlight: function (element) {
                jQuery(element).addClass("error");
                jQuery(element.form).find("label[for=label_for_" + element.id + "]").addClass('has-error');
            },

            unhighlight: function (element) {
                jQuery(element).removeClass("error");
                jQuery(element.form).find("label[for=label_for_" + element.id + "]").removeClass('has-error');
            },

            errorPlacement: function () {},

            invalidHandler: function () {
                jQuery('#fmMsg').addClass('has-error').addClass('no-bold').html('Please fill out correctly all the fields marked with red and then try again to submit the form.<br/><br/>');
            }
        });
    });

</script>

<?php include('_footer.php'); ?>
