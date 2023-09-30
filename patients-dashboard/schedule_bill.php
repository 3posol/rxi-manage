<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

$invoice_id = (isset($_GET['invoice'])) ? (int) $_GET['invoice'] : 0;
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
	'schedule_date' 			=> date('m/d/Y', strtotime('+1 day'))
);

$success = true;
$message = '';
if ((isset($_POST['schedule_date']))) {
	$data = array(
		'schedule_date' 			=> (isset($_POST['schedule_date'])) ? trim($_POST['schedule_date']) : ''
	);

	if ($data['schedule_date'] != '') {
		$api_data = array(
			'command'		=> 'schedule_payment',
			'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
			'access_code'	=> $_SESSION['PLP']['access_code'],
			'invoice'		=> $invoice_id,
			'schedule_date'	=> $data['schedule_date'],
			'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
		);

		$response = api_command($api_data);

		if (isset($response->success) && $response->success == 1) {
			//success
			$success = true;
			$message = 'You\'re payment was successfully scheduled for invoice #' . $invoice_id . ' on ' . $data['schedule_date'] . ' using ' . $billing_information->payment_info . '.<br/><br/><br/>';
		} else {
			//fail
			$success = false;
			$message = 'Action failed for unknown reasons, please try submitting the form again.<br>If this error persists, please call an advocate as soon as possible for assistance at 1-877-296-4673, option 3.<br><br>';
		}
	} else {
		//invalid form
		$success = false;
		$message = 'Some data is missing, please fill all the fields and try again.<br/><br/>';
	}

	$arrReturn = array(
		'success' 	=> $success,
		'message' 	=> $message
	);
	echo json_encode($arrReturn);
	die();
}

?>

<?php include('_header.php'); ?>

<div class="content">
	<div class="container">
		<h2>Schedule Payment</h2>
		<br/>

		<div class="right-content">
			&nbsp;
		</div>

		<div class="left-content">
			<div id="fmMsg" class="<?=(($message != '' && !$success) ? 'error' : 'bold')?>"><?=$message?></div>

			<?php if (!$success || !isset($_POST['schedule_date'])) { ?>

				<form id="fmSchedule" action="schedule_bill.php?invoice=<?=$invoice_id?>" method="post">
					<div class="label bold">Invoice Number:</div>
					<div class="value no-bold-force"><?=$invoice_id?></div>

					<div class="label bold">Invoice Amount:</div>
					<div class="value no-bold-force">
						<?php foreach ($billing_information->open_invoices as $invoice) { ?>
							<?php if ($invoice->InvoiceID == $invoice_id) { ?>
								$<?=number_format($invoice->InvoiceTotal, 2)?>
							<?php } ?>
						<?php } ?>
					</div>

					<div class="label bold">Payment Information:</div>
					<div class="value no-bold-force"><?=$billing_information->payment_info?></div>

					<div class="clear"></div><br/>

					<label for="schedule_date" class="label-long no-bold <?=((!$success && $data['schedule_date'] == '') ? 'error' : '')?>">Schedule Date:</label>
					<input type="text" name="schedule_date" id="schedule_date" value="<?=addslashes($data['schedule_date'])?>" class="<?=((!$success && $data['schedule_date'] == '') ? 'error' : '')?>" />
					<br/><br/><br/>

					<input type="submit" name="btSave" id="btSave" value="Schedule Payment">
				</form>

			<?php } ?>

		</div>

		<div class="clear"></div>

	</div>
</div>

<script type="text/javascript">

jQuery().ready(function() {
	jQuery.validator.addMethod("custom_date",function(t,e){return t=t.replace(/\s+/g,""),td=t.split("/"),td=td[2]+"-"+td[0]+"-"+td[1],this.optional(e)||t.length>8&&t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/)&&td>new Date().toISOString().substring(0,10)},"Please specify a valid date (mm/dd/yyyy)"),

	jQuery("#fmSchedule").validate({
		rules: {
			schedule_date: 		{ required: true, custom_date: true }
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

	//add masks
	jQuery("input#schedule_date").mask("99/99/9999");
});

</script>

<?php include('_footer.php'); ?>
