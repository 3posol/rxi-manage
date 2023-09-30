<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

$invoice_id = (isset($_GET['invoice'])) ? (int) $_GET['invoice'] : 0;
$invoice_amount = (isset($_POST['invoice_amount'])) ? (float) $_POST['invoice_amount'] : 0;
if ((int) $invoice_id <= 0) {
	header('Location: billing.php');
}

//get data

$data = array(
	'command'		=> 'get_billing',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$billing_information = api_command($data);

$data = array(
	'cc_type' 			=> '',
	'cc_number' 		=> '',
	'cc_exp_month' 		=> '',
	'cc_exp_year' 		=> ''
//	'cc_cvv' 			=> ''
);

$success = true;
$bill_payed_already = false;
$message = '';
if ((isset($_POST['cc_number']))) {
	$data = array(
		'cc_type' 			=> (isset($_POST['cc_type'])) ? trim($_POST['cc_type']) : '',
		'cc_number' 		=> (isset($_POST['cc_number'])) ? trim($_POST['cc_number']) : '',
		'cc_exp_month' 		=> (isset($_POST['cc_exp_month'])) ? trim($_POST['cc_exp_month']) : '',
		'cc_exp_year' 		=> (isset($_POST['cc_exp_year'])) ? trim($_POST['cc_exp_year']) : ''
		//'cc_cvv' 			=> (isset($_POST['cc_cvv'])) ? trim($_POST['cc_cvv']) : ''
	);

	if ($data['cc_type'] != '' && $data['cc_number'] != '' && $data['cc_exp_month'] != '' && $data['cc_exp_year'] != '') {
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
			'command'		=> 'make_payment_new_card',
			'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
			'access_code'	=> $_SESSION['PLP']['access_code'],
			'invoice'		=> $invoice_id,
			'card'			=> $payment_data,
			'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
		);

		$response = api_command($api_data);

		if (isset($response->success) && $response->success == 1) {
			//success
			$success = true;
			$message = 'You\'re payment was successfully received. Thank you.<br/><br/>';
		} elseif (isset($response->success) && $response->success == 2) {
			$success = true;
			$bill_payed_already = true;
			$message = 'Your bill is already marked as "Payed", no payment was taken. Thank you.<br/><br/>';
		} else {
			//fail
			$success = false;
			$message = 'Payment failed, the provided credit card is not valid.<br>Please try again using a valid credit card.<br/><br/>If this error persists, please call an advocate as soon as possible for assistance at 1-877-296-4673, option 3.<br><br>';
		}
	} else {
		//invalid form
		$success = false;
		$message = 'Some data is missing, please fill all the fields and try again.<br/><br/>';
	}
}

echo json_encode(array(
	'invoice' 				=> $invoice_id,
	'amount' 				=> (string) number_format($invoice_amount, 2),
	'datetime'				=> date('m/d/Y H:i a'),
	'success'				=> $success,
	'bill_payed_already'	=> $bill_payed_already,
	'message'				=> $message
));

die();

?>

<?php include('_header.php'); ?>

<div class="content">
	<div class="container">
		<h2>Make Payment</h2>
		<br/>

		<div class="right-content">
			&nbsp;
		</div>

		<div class="left-content">
			<div id="fmMsg" class="<?=(($message != '' && !$success) ? 'error' : 'bold')?>"><?=$message?></div>

			<?php if (!$success || !isset($_POST['cc_number'])) { ?>

				<form id="fmPayCC" action="pay_billcc.php?invoice=<?=$invoice_id?>" method="post">
					<div class="label">Invoice Number:</div>
					<div class="value"><?=$invoice_id?></div>

					<div class="label">Invoice Amount:</div>
					<div class="value">
						<?php foreach ($billing_information->open_invoices as $invoice) { ?>
							<?php if ($invoice->InvoiceID == $invoice_id) { ?>
								$<?=number_format($invoice->InvoiceTotal, 2)?>
							<?php } ?>
						<?php } ?>
					</div>

					<div class="clear"></div><br/>

					<label for="cc_type" class="label-long <?=((!$success && $data['cc_type'] == '') ? 'error' : '')?>">Credit Card Type:</label>
					<select name="cc_type" id="cc_type" class="<?=((!$success && $data['cc_type'] == '') ? 'error' : '')?>">
						<option value=''></option>
						<option value='A' <?=(($data['cc_type'] == 'A') ? 'selected="selected"' : '') ?>>American Express</option>
						<option value='D' <?=(($data['cc_type'] == 'D') ? 'selected="selected"' : '') ?>>Discover</option>
						<option value='M' <?=(($data['cc_type'] == 'M') ? 'selected="selected"' : '') ?>>Mastercard</option>
						<option value='V' <?=(($data['cc_type'] == 'V') ? 'selected="selected"' : '') ?>>VISA</option>
					</select>
					<br/><br/>

					<label for="cc_number" class="label-long <?=((!$success && $data['cc_number'] == '') ? 'error' : '')?>">Credit Card Number:</label>
					<input type="text" name="cc_number" id="cc_number" value="<?=addslashes($data['cc_number'])?>" class="<?=((!$success && $data['cc_number'] == '') ? 'error' : '')?>" />
					<br/><br/>

					<label for="cc_exp_month" class="label-long <?=((!$success && $data['cc_exp_month'] == '') ? 'error' : '')?>">Credit Card Expiration Month:</label>
					<select name="cc_exp_month" id="cc_exp_month" class="<?=((!$success && $data['cc_exp_month'] == '') ? 'error' : '')?>">
						<option value=''></option>
						<option value='1' <?=(($data['cc_exp_month'] == '1') ? 'selected="selected"' : '') ?>>January</option>
						<option value='2' <?=(($data['cc_exp_month'] == '2') ? 'selected="selected"' : '') ?>>February</option>
						<option value='3' <?=(($data['cc_exp_month'] == '3') ? 'selected="selected"' : '') ?>>March</option>
						<option value='4' <?=(($data['cc_exp_month'] == '4') ? 'selected="selected"' : '') ?>>April</option>
						<option value='5' <?=(($data['cc_exp_month'] == '5') ? 'selected="selected"' : '') ?>>May</option>
						<option value='6' <?=(($data['cc_exp_month'] == '6') ? 'selected="selected"' : '') ?>>June</option>
						<option value='7' <?=(($data['cc_exp_month'] == '7') ? 'selected="selected"' : '') ?>>July</option>
						<option value='8' <?=(($data['cc_exp_month'] == '8') ? 'selected="selected"' : '') ?>>August</option>
						<option value='9' <?=(($data['cc_exp_month'] == '9') ? 'selected="selected"' : '') ?>>September</option>
						<option value='10' <?=(($data['cc_exp_month'] == '10') ? 'selected="selected"' : '') ?>>October</option>
						<option value='11' <?=(($data['cc_exp_month'] == '11') ? 'selected="selected"' : '') ?>>November</option>
						<option value='12' <?=(($data['cc_exp_month'] == '12') ? 'selected="selected"' : '') ?>>December</option>
					</select>
					<br/><br/>

					<label for="cc_exp_year" class="label-long <?=((!$success && $data['cc_exp_year'] == '') ? 'error' : '')?>">Credit Card Expiration Year:</label>
					<select name="cc_exp_year" id="cc_exp_year" class="<?=((!$success && $data['cc_exp_year'] == '') ? 'error' : '')?>">
						<option value=''></option>
						<?php for ($i = 0; $i < 10; $i++) { $year = (int) date('Y') + $i; ?>
							<option value="<?=$year?>" <?=(($year == $data['cc_exp_year']) ? 'selected="selected"' : '') ?>><?=$year?></option>
						<?php } ?>
					</select>
					<br/><br/>

					<!--label for="cc_cvv" class="label-long <?=((!$success && $data['cc_cvv'] == '') ? 'error' : '')?>">CVV Security Code:</label>
					<input type="text" name="cc_cvv" id="cc_cvv" value="<?=addslashes($data['cc_cvv'])?>" class="<?=((!$success && $data['cc_cvv'] == '') ? 'error' : '')?>" />
					<br/><br/-->

					<input type="submit" name="btSave" id="btSave" value="Make Payment">
				</form>

			<?php } elseif ($success && isset($_POST['cc_number'])) { ?>

				<?php if (!$bill_payed_already) { ?>
					<div class="label">Date and Time of transaction:</div>
					<div class="value"><?=date('m/dY g:i a')?></div>

					<div class="label">Transaction Amount:</div>
					<div class="value">
						<?php foreach ($billing_information->open_invoices as $invoice) { ?>
							<?php if ($invoice->InvoiceID == $invoice_id) { ?>
								$<?=number_format($invoice->InvoiceTotal, 2)?>
							<?php } ?>
						<?php } ?>
					</div>
				<?php } ?>

				<div class="label">Invoice Number:</div>
				<div class="value"><?=$invoice_id?></div>

			<?php } ?>

		</div>

		<div class="clear"></div>

	</div>
</div>

<script type="text/javascript">

jQuery().ready(function() {
	jQuery("#fmPayCC").validate({
		rules: {
			cc_type:			{ required: true },
			cc_number:			{ required: true, creditcardtypes: function(element) {return {visa: (jQuery('select[name=cc_type]').val() == "V"), mastercard: (jQuery('select[name=cc_type]').val() == "M"), amex: (jQuery('select[name=cc_type]').val() == "A"), discover: (jQuery('select[name=cc_type]').val() == "D")};}},
			cc_exp_month:		{ required: true, min: function(element) {return (jQuery('select[name=cc_exp_year]').val() != '<?php echo date('Y');?>') ? '01' : '<?php echo date('m');?>';}},
			cc_exp_year:		{ required: true, min: function(element) {return (jQuery('select[name=cc_exp_month]').val() < '<?php echo date('m');?>') ? '<?php echo (int)date('Y')+1;?>' : '<?php echo date('Y');?>';}}
			//cc_cvv:			{ required: jQuery("#p_payment_method_cc"), digits: true, minlength: 3, maxlength: 4 },
		},

		highlight: function(element) {
			jQuery(element).addClass("error");
			jQuery(element.form).find("label[for=label_for_" + element.id + "]").addClass('has-error');
		},

		unhighlight: function(element) {
			jQuery(element).removeClass("error");
			jQuery(element.form).find("label[for=label_for_" + element.id + "]").removeClass('has-error');
		},

		errorPlacement: function() {},

		invalidHandler: function() {
			jQuery('#fmMsg').addClass('has-error').addClass('no-bold').html('Please fill out correctly all the fields marked with red and then try again to submit the form.<br/><br/>');
		}
	});
});

</script>

<?php include('_footer.php'); ?>
