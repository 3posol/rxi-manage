<?php

require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
	header('Location: login.php');
}

$modified_items = filter_input(INPUT_POST, "modified_items", FILTER_SANITIZE_MAGIC_QUOTES, array('options' => array('default' => '')));

//get data
$data = array(
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);

$data = array();
foreach ($rxi_data->providers as $provider) {
	$data[] = (array) $provider;
}

$success = true;
$message = '';
$fields_to_update = array('PrvPracticeName', 'PrvAddress1', 'PrvAddress2', 'PrvCity', 'PrvState', 'PrvZip', 'PrvWorkPhone', 'PrvFaxNumber');
if ((isset($_POST['PrvPracticeName']))) {
	$form_valid = true;

	$modified_items_arr = array_filter(explode(',', $modified_items), create_function('$value', 'return $value !== "";'));

	//prepare & validate data
	foreach ($data as $key => $provider) {
		if (in_array($key, $modified_items_arr)) {
			foreach ($fields_to_update as $field_name) {
				$data[$key][$field_name] = (isset($_POST[$field_name][$key])) ? trim($_POST[$field_name][$key]) : '';

				//check if valid
				$form_valid = ($form_valid) ? ($data[$key][$field_name] != '') : $form_valid;
			}
		} else {
			unset($data[$key]);
		}
	}

	if ($form_valid) {
		//for encoding
		$encode_key = pack('H*', md5($_SESSION['PLP']['patient']->PatientID));
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		//encode data
		$providers_data = array();
		foreach ($data as $key => $provider) {
			foreach ($provider as $property => $value) {
				$providers_data[$key][$property] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encode_key, $value, MCRYPT_MODE_CFB, $iv));
			}
		}

		$providers_data['iv'] = base64_encode($iv);

		//send new data to RxI
		$api_data = array(
			'command'		=> 'update_patient_providers',
			'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
			'access_code'	=> $_SESSION['PLP']['access_code'],
			'data'			=> $providers_data,
			'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
		);

		$response = api_command($api_data);

		if (isset($response->success) && $response->success == 1) {
			//success
			$success = true;
			$message = 'You\'re providers updated information was saved successfully.<br/><br/><br/>';
			header('Location: providers.php?success=1');
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

?>

<?php include('_header.php'); ?>

<div class="content">
	<div class="container">
		<h2>My Healthcare Providers</h2>
		<br/><br/>

		<div class="right-content">If any of this information is incorrect, please inform us immediately by calling a representative at 1-877-296-HOPE (4673).</div>

		<div class="left-content">
			<div id="fmMsg" class="<?=(($message != '' && !$success) ? 'error' : 'bold')?>"><?=$message?></div>

			<form id="fmProviders" action="update_providers.php" method="post">
				<input type="hidden" name="modified_items" id="modified_items" value="<?=addslashes($modified_items)?>" />

				<?php foreach ($data as $key => $provider) { ?>
					<?php if ($provider['PrvProviderId'] > 0) { ?>
						<?php if ($key != 0) { ?>
							<br/>
							<div class="bottom-light-border-1-small-margins"></div>
							<br/>
						<?php } ?>

						<h3><?=$provider['PrvPrefix']?> <?=$provider['PrvFirstName']?> <?=(($provider['PrvMiddleInitial'] != '') ? $provider['PrvMiddleInitial'] . '.' : '') ?> <?=$provider['PrvLastName']?> <?=$provider['PrvProfDesignation']?></h3>
						<br/><br/>

						<label for="PrvPracticeName_<?=$key?>" class="label-long <?=((!$success && $provider['PrvPracticeName'] == '') ? 'error' : '')?>">Practice Name:</label>
						<input type="text" name="PrvPracticeName[<?=$key?>]" id="PrvPracticeName_<?=$key?>" value="<?=addslashes($provider['PrvPracticeName'])?>" class="<?=((!$success && $provider['PrvPracticeName'] == '') ? 'error' : '')?>" />
						<br/><br/><br/>

						<label for="PrvAddress1_<?=$key?>" class="label-long <?=((!$success && $provider['PrvAddress1'] == '') ? 'error' : '')?>">Address Line 1:</label>
						<input type="text" name="PrvAddress1[<?=$key?>]" id="PrvAddress1_<?=$key?>" value="<?=addslashes($provider['PrvAddress1'])?>" class="<?=((!$success && $provider['PrvAddress1'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvAddress2_<?=$key?>" class="label-long <?=((!$success && $provider['PrvAddress2'] == '') ? 'error' : '')?>">Address Line 2:</label>
						<input type="text" name="PrvAddress2[<?=$key?>]" id="PrvAddress2_<?=$key?>" value="<?=addslashes($provider['PrvAddress2'])?>" class="<?=((!$success && $provider['PrvAddress2'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvCity_<?=$key?>" class="label-long <?=((!$success && $provider['PrvCity'] == '') ? 'error' : '')?>">City:</label>
						<input type="text" name="PrvCity[<?=$key?>]" id="PrvCity_<?=$key?>" value="<?=addslashes($provider['PrvCity'])?>" class="<?=((!$success && $provider['PrvCity'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvState_<?=$key?>" class="label-long <?=((!$success && $provider['PrvState'] == '') ? 'error' : '')?>">State:</label>
						<select name="PrvState[<?=$key?>]" id="PrvState_<?=$key?>" class="<?=((!$success && $provider['PrvState'] == '') ? 'error' : '')?>">
							<option value="">...</option>
							<?php foreach ($us_states as $state_key => $state) { ?>
								<option value="<?=$state_key?>" <?=(($provider['PrvState'] == $state_key) ? 'selected="selected"' : '')?>><?=$state?></option>
							<?php } ?>
						</select>
						<br/><br/>

						<label for="PrvZip_<?=$key?>" class="label-long <?=((!$success && $provider['PrvZip'] == '') ? 'error' : '')?>">Zip Code:</label>
						<input type="text" name="PrvZip[<?=$key?>]" id="PrvZip_<?=$key?>" value="<?=addslashes($provider['PrvZip'])?>" class="<?=((!$success && $provider['PrvZip'] == '') ? 'error' : '')?>" />
						<br/><br/><br/>

						<label for="PrvWorkPhone_<?=$key?>" class="label-long <?=((!$success && $provider['PrvWorkPhone'] == '') ? 'error' : '')?>">Phone Number:</label>
						<input type="text" name="PrvWorkPhone[<?=$key?>]" id="PrvWorkPhone_<?=$key?>" value="<?=addslashes($provider['PrvWorkPhone'])?>" class="<?=((!$success && $provider['PrvWorkPhone'] == '') ? 'error' : '')?>" />
						<br/><br/>

						<label for="PrvFaxNumber_<?=$key?>" class="label-long <?=((!$success && $provider['PrvFaxNumber'] == '') ? 'error' : '')?>">Fax Number:</label>
						<input type="text" name="PrvFaxNumber[<?=$key?>]" id="PrvFaxNumber_<?=$key?>" value="<?=addslashes($provider['PrvFaxNumber'])?>" class="<?=((!$success && $provider['PrvFaxNumber'] == '') ? 'error' : '')?>" />
						<br/><br/>
				<?php } ?>
				<?php } ?>

				<br/>
				<input type="submit" name="btSave" id="btSave" value="Save">
				&nbsp;<a href="providers.php">Cancel</a>

			</form>

		</div>

		<div class="clear"></div>

	</div>
</div>

<script type="text/javascript">

jQuery().ready(function() {
	jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");

	jQuery("#fmProviders").validate({
		rules: {
			<?php for ($i = 0; $i < count($data); $i++) { ?>
				'PrvPracticeName[<?=$i?>]': 	{ required: true },
				'PrvAddress1[<?=$i?>]': 		{ required: true },
				'PrvAddress2[<?=$i?>]':			{ required: true },
				'PrvCity[<?=$i?>]': 			{ required: true },
				'PrvState[<?=$i?>]':			{ required: true },
				'PrvZip[<?=$i?>]':				{ required: true, digits: true },
				'PrvWorkPhone[<?=$i?>]':		{ required: true, phoneUS: true },
				'PrvFaxNumber[<?=$i?>]':		{ required: true, phoneUS: true },
			<?php } ?>
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

	//add masks
	<?php for ($i = 0; $i < count($data); $i++) { ?>
		jQuery("input#PrvWorkPhone_<?=$i?>").mask("?999-999-9999");
		jQuery("input#PrvFaxNumber_<?=$i?>").mask("?999-999-9999");
	<?php } ?>

	$('input, select').change(function(e) {
		input_id = $(this).attr('id');

		new_item_no = input_id.substring(input_id.indexOf('_') + 1);
		new_item_marked = false;

		modified_items_arr = $('#modified_items').val().split(',');
		for (key in modified_items_arr) {
			if (new_item_no == modified_items_arr[key]) {
				new_item_marked = true;
			}
		}

		if (!new_item_marked) {
			modified_items_arr.push(new_item_no);
		}

		$('#modified_items').val(modified_items_arr.join(','));
		//console.log($('#modified_items').val());
	});
});

</script>

<?php include('_footer.php'); ?>
