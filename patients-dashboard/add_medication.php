<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

//get providers data
$data = array(
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);

$data = array(
	'MedAssistDetailID'		=> 0,
	'DrugAppliedFor' 		=> '',
	'Dosage' 				=> '',
	'Directions' 			=> '',
	'ProviderId' 			=> 0,
	'PrvFirstName'			=> '',
	'PrvLastName'			=> '',
	'PrvAddress1'			=> '',
	'PrvAddress2'			=> '',
	'PrvCity'				=> '',
	'PrvState'				=> '',
	'PrvZip'				=> '',
	'PrvWorkPhone'			=> '',
	'PrvFaxNumber'			=> ''
);

$success = true;
$message = '';
if ((isset($_POST['Dosage']))) {
	$form_valid = true;

	//prepare & validate data
	foreach ($data as $key => $value) {
		$data[$key] = (isset($_POST[$key])) ? trim($_POST[$key]) : '';

		//check if valid
		if ($key != 'MedAssistDetailID' && (strpos($key, 'Prv') === false || (strpos($key, 'Prv') !== false && $data['ProviderId'] == -1 && $key != 'PrvAddress2' && $key != 'PrvFaxNumber'))) {
			$form_valid = ($form_valid) ? ($data[$key] != '') : $form_valid;
		}
	}

	if ($form_valid) {
		//for encoding
		$encode_key = pack('H*', md5($_SESSION['PLP']['patient']->PatientID));
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		//encode data
		$meds_data = array();
		//$meds_data[0]['MedAssistDetailID'] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encode_key, '0', MCRYPT_MODE_CFB, $iv));
		foreach ($data as $property => $value) {
			$meds_data[(int) $data['MedAssistDetailID']][$property] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encode_key, $value, MCRYPT_MODE_CFB, $iv));
		}

		$meds_data['iv'] = base64_encode($iv);

		//send new data to RxI
		$api_data = array(
			'command'		=> 'update_patient_medication',
			'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
			'access_code'	=> $_SESSION['PLP']['access_code'],
			'data'			=> $meds_data,
			'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
		);

		$response = api_command($api_data);

		if (isset($response->success) && $response->success == 1) {
			//success
			$success = true;
			$message = 'You\'re new medication was saved successfully.<br/><br/><br/>';
			//header('Location: medication.php?success=1');
		} else {
			//fail
			$success = false;
			$message = 'Action failed for unknown reasons, please try submitting the form again.<br/><br/>';
		}
	} else {
		//invalid form
		$success = false;
		$message = 'Some data is missing, please fill all the fields and try again.<br/><br/>';
	}
}

$arrReturn = array(
	'success' 	=> $success,
	'message' 	=> $message
);
echo json_encode($arrReturn);
die();

?>

<?php include('_header.php'); ?>

<div class="content">
	<div class="container">
		<h2>Add New Medication</h2>
		<br/><br/>

		<div class="right-content">If any of this information is incorrect, please inform us immediately by calling a representative at 1-877-296-HOPE (4673).</div>

		<div class="left-content">
			<div id="fmMsg" class="<?=(($message != '' && !$success) ? 'error' : 'bold')?>"><?=$message?></div>

			<?php if (!$success || !isset($_POST['DrugAppliedFor'])) { ?>

				<form id="fmMeds" action="add_medication.php" method="post">

					<label for="DrugAppliedFor" class="label-long <?=((!$success && $data['DrugAppliedFor'] == '') ? 'error' : '')?>">Name:</label>
					<input type="text" name="DrugAppliedFor" id="DrugAppliedFor" value="<?=addslashes($data['DrugAppliedFor'])?>" class="<?=((!$success && $data['DrugAppliedFor'] == '') ? 'error' : '')?>" />
					<br/><br/>

					<label for="Dosage" class="label-long <?=((!$success && $data['Dosage'] == '') ? 'error' : '')?>">Strength:</label>
					<input type="text" name="Dosage" id="Dosage" value="<?=addslashes($data['Dosage'])?>" class="<?=((!$success && $data['Dosage'] == '') ? 'error' : '')?>" />
					<br/><br/>

					<label for="Directions" class="label-long <?=((!$success && $data['Directions'] == '') ? 'error' : '')?>">Directions:</label>
					<input type="text" name="Directions" id="Directions" value="<?=addslashes($data['Directions'])?>" class="<?=((!$success && $data['Directions'] == '') ? 'error' : '')?>" />
					<br/><br/>

					<label for="ProviderId" class="label-long <?=((!$success && $data['ProviderId'] == '') ? 'error' : '')?>">Provider:</label>
					<select name="ProviderId" id="ProviderId" class="<?=((!$success && $data['ProviderId'] == '') ? 'error' : '')?>">
						<option value="0">Select a Provider</option>
						<?php foreach ($rxi_data->providers as $provider) { ?>
							<?php if ($provider->PrvProviderId > 0) { ?>
								<option value="<?=$provider->PrvProviderId?>" <?=(($data['ProviderId'] == $provider->PrvProviderId) ? 'selected="selected"' : '')?>><?=$provider->PrvPrefix?> <?=$provider->PrvFirstName?> <?=(($provider->PrvMiddleInitial != '') ? $provider->PrvMiddleInitial . '.' : '') ?> <?=$provider->PrvLastName?> <?=$provider->PrvProfDesignation?></option>
							<?php } ?>
						<?php } ?>
						<option value="-1">*Add A New Provider</option>
					</select>
					<br/><br/>

					<div class="new-provider-data hide">
						<br>
						<h3>New Provider Details:</h3>
						<br>

						<label for="PrvFirstName" class="label-long <?=((!$success && $data['PrvFirstName'] == '') ? 'error' : '')?>">First Name:</label>
						<input type="text" name="PrvFirstName" id="PrvFirstName" value="<?=addslashes($data['PrvFirstName'])?>" class="<?=((!$success && $data['PrvFirstName'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvLastName" class="label-long <?=((!$success && $data['PrvLastName'] == '') ? 'error' : '')?>">Last Name:</label>
						<input type="text" name="PrvLastName" id="PrvLastName" value="<?=addslashes($data['PrvLastName'])?>" class="<?=((!$success && $data['PrvLastName'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvAddress1" class="label-long <?=((!$success && $data['PrvAddress1'] == '') ? 'error' : '')?>">Address Line 1:</label>
						<input type="text" name="PrvAddress1" id="PrvAddress1" value="<?=addslashes($data['PrvAddress1'])?>" class="<?=((!$success && $data['PrvAddress1'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvAddress2" class="label-long <?=((!$success && $data['PrvAddress2'] == '') ? 'error' : '')?>">Address Line 2:</label>
						<input type="text" name="PrvAddress2" id="PrvAddress2" value="<?=addslashes($data['PrvAddress2'])?>" class="<?=((!$success && $data['PrvAddress2'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvCity" class="label-long <?=((!$success && $data['PrvCity'] == '') ? 'error' : '')?>">City:</label>
						<input type="text" name="PrvCity" id="PrvCity" value="<?=addslashes($data['PrvCity'])?>" class="<?=((!$success && $data['PrvCity'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvState" class="label-long <?=((!$success && $data['PrvState'] == '') ? 'error' : '')?>">State:</label>
						<select name="PrvState" id="PrvState" class="<?=((!$success && $data['PrvState'] == '') ? 'error' : '')?>">
							<option value="">...</option>
							<?php foreach ($us_states as $state_key => $state) { ?>
								<option value="<?=$state_key?>" <?=(($data['PrvState'] == $state_key) ? 'selected="selected"' : '')?>><?=$state?></option>
							<?php } ?>
						</select>
						<br/><br/>

						<label for="PrvZip" class="label-long <?=((!$success && $data['PrvZip'] == '') ? 'error' : '')?>">Zip Code:</label>
						<input type="text" name="PrvZip" id="PrvZip" value="<?=addslashes($data['PrvZip'])?>" class="<?=((!$success && $data['PrvZip'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvWorkPhone" class="label-long <?=((!$success && $data['PrvWorkPhone'] == '') ? 'error' : '')?>">Phone Number:</label>
						<input type="text" name="PrvWorkPhone" id="PrvWorkPhone" value="<?=addslashes($data['PrvWorkPhone'])?>" class="<?=((!$success && $data['PrvWorkPhone'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvFaxNumber" class="label-long <?=((!$success && $data['PrvFaxNumber'] == '') ? 'error' : '')?>">Fax Number:</label>
						<input type="text" name="PrvFaxNumber" id="PrvFaxNumber" value="<?=addslashes($data['PrvFaxNumber'])?>" class="<?=((!$success && $data['PrvFaxNumber'] == '') ? 'error' : '')?>" />
						<br/><br/>
					</div>

					<br/>
					<input type="submit" name="btSave" id="btSave" value="Save">
					&nbsp;<a href="medication.php">Cancel</a>

				</form>

			<?php } ?>

		</div>

		<div class="clear"></div>

	</div>
</div>

<script type="text/javascript">

jQuery().ready(function() {
	jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");
	jQuery.validator.addMethod("no_zero", function(value, element) { return this.optional(element) || (value != 0); }, "You must choose a provider from the drop-down.");

	jQuery("#fmMeds").validate({
		rules: {
			'DrugAppliedFor': 		{ required: true },
			'Dosage': 				{ required: true },
			'Directions': 			{ required: true },
			'ProviderId': 			{ required: true, no_zero: true },
			'PrvFirstName': 		{ required: {
										depends: function(element) {
											return ($("#ProviderId").val() == -1);
										}
									}},
			'PrvLastName': 			{ required: {
										depends: function(element) {
											return ($("#ProviderId").val() == -1);
										}
									}},
			'PrvAddress1': 			{ required: {
										depends: function(element) {
											return ($("#ProviderId").val() == -1);
										}
									}},
			'PrvCity': 				{ required: {
										depends: function(element) {
											return ($("#ProviderId").val() == -1);
										}
									}},
			'PrvState':				{ required: {
										depends: function(element) {
											return ($("#ProviderId").val() == -1);
										}
									}},
			'PrvZip':				{ required: {
										depends: function(element) {
											return ($("#ProviderId").val() == -1);
										}
									}, digits: true },
			'PrvWorkPhone':			{ required: {
										depends: function(element) {
											return ($("#ProviderId").val() == -1);
										}
									}, phoneUS: true },
			'PrvFaxNumber':			{ required: {
										depends: function(element) {
											return ($("#ProviderId").val() == -1);
										}
									}, phoneUS: true }
		},

		highlight: function(element) {
			//if (jQuery(element).attr('id') != 'EmergencyContactPhone' && jQuery(element).attr('id') != 'EmergencyContact2Phone' && jQuery(element).attr('id') != 'EmergencyContact3Phone') {
				jQuery(element).addClass("error");
				jQuery(element.form).find("label[for=" + element.id + "]").addClass('has-error');
			//}
		},

		unhighlight: function(element) {
			jQuery(element).removeClass("error");
			jQuery(element.form).find("label[for=" + element.id + "]").removeClass('has-error');
		},

		errorPlacement: function() {},

		invalidHandler: function() {
			jQuery('#fmMsg').addClass('has-error').addClass('no-bold').html('Please fill out correctly all the fields marked with red and then try again to submit the form.<br/><br/><br/>');
		}
	});

	jQuery("input#PrvWorkPhone").mask("?999-999-9999");
	jQuery("input#PrvFaxNumber").mask("?999-999-9999");

	//show/hide new provider form
	$('#ProviderId').change(function(e) {
		if ($(this).val() == -1) {
			$('.new-provider-data').show();
		} else {
			$('.new-provider-data').hide();
		}
	});
});

</script>

<?php include('_footer.php'); ?>
