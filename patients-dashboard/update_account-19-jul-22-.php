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
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);
$_SESSION['PLP']['patient'] = $rxi_data->patient;
clear_patient_last_name();

$_data = array(
	'PatientFirstName' 			=> trim($_SESSION['PLP']['patient']->PatientFirstName),
	'PatientMiddleInitial' 		=> trim($_SESSION['PLP']['patient']->PatientMiddleInitial),
	'PatientLastName' 			=> trim($_SESSION['PLP']['patient']->PatientLastName),
	'PatientAddress1' 			=> trim($_SESSION['PLP']['patient']->PatientAddress1),
	'PatientCity_1' 			=> trim($_SESSION['PLP']['patient']->PatientCity_1),
	'PatientState_1' 			=> trim($_SESSION['PLP']['patient']->PatientState_1),
	'PatientZip_1' 				=> trim($_SESSION['PLP']['patient']->PatientZip_1),
	'PatHomePhoneWACFmt_1' 		=> trim($_SESSION['PLP']['patient']->PatHomePhoneWACFmt_1),
	'EmergencyContactName' 		=> trim($_SESSION['PLP']['patient']->EmergencyContactName),
	'EmergencyContactPhone' 	=> trim($_SESSION['PLP']['patient']->EmergencyContactPhone),
	'EmergencyContact2Name' 	=> trim($_SESSION['PLP']['patient']->EmergencyContact2Name),
	'EmergencyContact2Phone'	=> trim($_SESSION['PLP']['patient']->EmergencyContact2Phone),
	'EmergencyContact3Name' 	=> trim($_SESSION['PLP']['patient']->EmergencyContact3Name),
	'EmergencyContact3Phone' 	=> trim($_SESSION['PLP']['patient']->EmergencyContact3Phone),
	'PreferredName'				=> trim($_SESSION['PLP']['patient']->PreferredName),
	'Allergies'					=> trim($_SESSION['PLP']['patient']->Allergies),
	'Conditions'					=> trim($_SESSION['PLP']['patient']->Conditions),
);

$success = true;
$message = '';

if (isset($_POST)) {
	//for encoding
	$encode_key = pack('H*', md5($_SESSION['PLP']['patient']->PatientID));
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
				
	$post_data = (isset($_POST['data'])) ? $_POST['data'] : array();
	parse_str($post_data, $input_data);
	if($input_data['type'] == 'edit_eaddr') {
		$patient_data = array('new_email' => trim($input_data['eaddress']), 'p_id' => $_SESSION['PLP']['patient']->PatientID );
		$command = 'change_email';		
	}
	else if($input_data['type'] == 'edit_pswd') {
		$patient_data = array('type' => $input_data['type'], 'old_pswd' => trim($input_data['old_pswd']), 'value' => trim($input_data['new_pswd']), 'id' => $_SESSION['PLP']['patient']->PatientID, 'access_code' => $_SESSION['PLP']['access_code']);
		$command = 'save_meta_data';		
	}
	//if($input_data['type'] == 'edit_allergy' || $input_data['type'] == 'edit_conditions') {
	//	$value = ($input_data['type'] == 'edit_conditions') ? trim($input_data['conditions']) : trim($input_data['allergies']);
	//	$patient_data = array('type' => $input_data['type'], 'value' => $value, 'id' => $_SESSION['PLP']['patient']->PatientID);
	//	$command = 'save_meta_data';		
	//}
	else {
		$data = array(
			'PatientFirstName' 			=> (isset($input_data['PatientFirstName'])) ? trim($input_data['PatientFirstName']) : $_data['PatientFirstName'],
			'PatientMiddleInitial' 		=> (isset($input_data['PatientMiddleInitial'])) ? trim($input_data['PatientMiddleInitial']) : $_data['PatientMiddleInitial'],
			'PatientLastName' 			=> (isset($input_data['PatientLastName'])) ? trim($input_data['PatientLastName']) : $_data['PatientLastName'],
			'PatientAddress1' 			=> (isset($input_data['PatientAddress1'])) ? trim($input_data['PatientAddress1']) : $_data['PatientAddress1'],
			'PatientCity_1' 			=> (isset($input_data['PatientCity_1'])) ? trim($input_data['PatientCity_1']) : $_data['PatientCity_1'],
			'PatientState_1' 			=> (isset($input_data['PatientState_1'])) ? trim($input_data['PatientState_1']) : $_data['PatientState_1'],
			'PatientZip_1' 				=> (isset($input_data['PatientZip_1'])) ? trim($input_data['PatientZip_1']) : $_data['PatientZip_1'],
			'PatHomePhoneWACFmt_1' 		=> (isset($input_data['PatHomePhoneWACFmt_1'])) ? trim($input_data['PatHomePhoneWACFmt_1']) : $_data['PatHomePhoneWACFmt_1'],
			'EmergencyContactName' 		=> (isset($input_data['EmergencyContactName'])) ? trim($input_data['EmergencyContactName']) : $_data['EmergencyContactName'],
			'EmergencyContactPhone' 	=> (isset($input_data['EmergencyContactPhone'])) ? trim($input_data['EmergencyContactPhone']) : $_data['EmergencyContactPhone'],
			'EmergencyContact2Name' 	=> (isset($input_data['EmergencyContact2Name'])) ? trim($input_data['EmergencyContact2Name']) : $_data['EmergencyContact2Name'],
			'EmergencyContact2Phone' 	=> (isset($input_data['EmergencyContact2Phone'])) ? trim($input_data['EmergencyContact2Phone']) : $_data['EmergencyContact2Phone'],
			'EmergencyContact3Name' 	=> (isset($input_data['EmergencyContact3Name'])) ? trim($input_data['EmergencyContact3Name']) : $_data['EmergencyContact3Name'],
			'EmergencyContact3Phone' 	=> (isset($input_data['EmergencyContact3Phone'])) ? trim($input_data['EmergencyContact3Phone']) : $_data['EmergencyContact3Phone'],
			'PreferredName'				=> (isset($input_data['pf_name'])) ? trim($input_data['pf_name']) : $_data['PreferredName'],
			'Allergies'					=> ($input_data['type'] == 'edit_allergy' && isset($input_data['allergies'])) ? trim($input_data['allergies']) : $_data['Allergies'],
			'Conditions'				=> ($input_data['type'] == 'edit_conditions' && isset($input_data['conditions'])) ? trim($input_data['conditions']) : $_data['Conditions'],
		);

		if ($data['PatientFirstName'] != trim($_SESSION['PLP']['patient']->PatientFirstName)
			|| $data['PatientMiddleInitial'] != trim($_SESSION['PLP']['patient']->PatientMiddleInitial)
			|| $data['PatientLastName'] != trim($_SESSION['PLP']['patient']->PatientLastName)
			|| $data['PatientAddress1'] != trim($_SESSION['PLP']['patient']->PatientAddress1)
			|| $data['PatientCity_1'] != trim($_SESSION['PLP']['patient']->PatientCity_1)
			|| $data['PatientState_1'] != trim($_SESSION['PLP']['patient']->PatientState_1)
			|| $data['PatientZip_1'] != trim($_SESSION['PLP']['patient']->PatientZip_1)
			|| $data['PatHomePhoneWACFmt_1'] != trim($_SESSION['PLP']['patient']->PatHomePhoneWACFmt_1)
			|| $data['EmergencyContactName'] != trim($_SESSION['PLP']['patient']->EmergencyContactName)
			|| $data['EmergencyContactPhone'] != trim($_SESSION['PLP']['patient']->EmergencyContactPhone)
			|| $data['EmergencyContact2Name'] != trim($_SESSION['PLP']['patient']->EmergencyContact2Name)
			|| $data['EmergencyContact2Phone'] != trim($_SESSION['PLP']['patient']->EmergencyContact2Phone)
			|| $data['EmergencyContact3Name'] != trim($_SESSION['PLP']['patient']->EmergencyContact3Name)
			|| $data['EmergencyContact3Phone'] != trim($_SESSION['PLP']['patient']->EmergencyContact3Phone)
			|| $data['PreferredName'] != trim($_SESSION['PLP']['patient']->PreferredName)
			|| $data['Allergies'] != trim($_SESSION['PLP']['patient']->Allergies)
			|| $data['Conditions'] != trim($_SESSION['PLP']['patient']->Conditions) 
		) { 
			if ($data['PatientLastName'] != '' && $data['PatientAddress1'] != '' && $data['PatientCity_1'] != '' && $data['PatientState_1'] != '' && $data['PatientZip_1'] != '' && $data['PatHomePhoneWACFmt_1'] != '') {
				//encode data
				$patient_data = array();
				foreach ($data as $key => $value) {
					$patient_data[$key] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encode_key, $value, MCRYPT_MODE_CFB, $iv));
				}
				$patient_data['iv'] = base64_encode($iv);
				$command = 'update_patient_data';
			}
		}
	} 
	$api_data = array(
		'command'		=> $command,
		'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
		'access_code'	=> $_SESSION['PLP']['access_code'],
		'data'			=> $patient_data,
		'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
	);
	
	//print_r($api_data); die('=======');

	$response = api_command($api_data);

	if (isset($response->success) && $response->success == 1) {
		//success
		$success = true;
		$message = 'You\'re new information was saved successfully.';
	}
	else if (isset($response->success) && $response->success == 2) {
		//success
		$success = false;
		$message = ($input_data['type'] == 'edit_eaddr') ? 'This email adress is already in use.' : ($input_data['type'] == 'edit_pswd') ? 'Old Password is incorrect, please enter correct one and try again.' : 'Action failed for unknown reasons, please try submitting the form again.';
	}
	else {
		//fail
		$success = false;
		$message = 'Action failed for unknown reasons, please try submitting the form again.';
	}
} else {
	//invalid form
	$success = false;
	$message = 'Some data is missing, please fill all required fields and try again.';
}


	$arrReturn = array();
	$arrReturn['success'] = $success;
	$arrReturn['message'] = $message;
	echo json_encode($arrReturn);

	die();


?>

<?php include('_header.php'); ?>

<div class="content">
	<div class="container">
		<h2>Account</h2>
		<br/>

		<div class="right-content">
			&nbsp;
		</div>

		<div class="left-content">
			<div id="fmMsg" class="<?=(($message != '' && !$success) ? 'error' : 'bold')?>"><?=$message?></div>

			* required fields<br/><br/>

			<form id="fmAccount" action="update_account.php" method="post">
				<label for="PatientLastName" class="label-long <?=((!$success && $data['PatientLastName'] == '') ? 'error' : '')?>">Last Name*:</label>
				<input type="text" name="PatientLastName" id="PatientLastName" value="<?=addslashes($data['PatientLastName'])?>" class="<?=((!$success && $data['PatientLastName'] == '') ? 'error' : '')?>" />
				<br/><br/><br/>

				<label for="PatientAddress1" class="label-long <?=((!$success && $data['PatientAddress1'] == '') ? 'error' : '')?>">Address*:</label>
				<input type="text" name="PatientAddress1" id="PatientAddress1" value="<?=addslashes($data['PatientAddress1'])?>" class="<?=((!$success && $data['PatientAddress1'] == '') ? 'error' : '')?>" />
				<br/><br/>

				<label for="PatientCity_1" class="label-long <?=((!$success && $data['PatientCity_1'] == '') ? 'error' : '')?>">City*:</label>
				<input type="text" name="PatientCity_1" id="PatientCity_1" value="<?=addslashes($data['PatientCity_1'])?>" class="<?=((!$success && $data['PatientCity_1'] == '') ? 'error' : '')?>" />
				<br/><br/>

				<label for="PatientState_1" class="label-long <?=((!$success && $data['PatientState_1'] == '') ? 'error' : '')?>">State*:</label>
				<select name="PatientState_1" id="PatientState_1" class="<?=((!$success && $data['PatientState_1'] == '') ? 'error' : '')?>">
					<option value="">...</option>
					<?php foreach ($us_states as $key => $state) { ?>
						<option value="<?=$key?>" <?=(($data['PatientState_1'] == $key) ? 'selected="selected"' : '')?>><?=$state?></option>
					<?php } ?>
				</select>
				<br/><br/>

				<label for="PatientZip_1" class="label-long <?=((!$success && $data['PatientZip_1'] == '') ? 'error' : '')?>">Zip Code*:</label>
				<input type="text" name="PatientZip_1" id="PatientZip_1" value="<?=addslashes($data['PatientZip_1'])?>" class="<?=((!$success && $data['PatientZip_1'] == '') ? 'error' : '')?>" />
				<br/><br/><br/>

				<label for="PatHomePhoneWACFmt_1" class="label-long <?=((!$success && $data['PatHomePhoneWACFmt_1'] == '') ? 'error' : '')?>">Phone Number*:</label>
				<input type="text" name="PatHomePhoneWACFmt_1" id="PatHomePhoneWACFmt_1" value="<?=addslashes($data['PatHomePhoneWACFmt_1'])?>" class="<?=((!$success && $data['PatHomePhoneWACFmt_1'] == '') ? 'error' : '')?>" />
				<br/><br/><br/>

				<label for="EmergencyContactName" class="label-long">Alternate Contact Name #1:</label>
				<input type="text" name="EmergencyContactName" id="EmergencyContactName" value="<?=addslashes($data['EmergencyContactName'])?>" class="" />
				<br/><br/>

				<label for="EmergencyContactPhone" class="label-long <?=((!$success && $data['EmergencyContactPhone'] == '') ? 'error' : '')?>">Alternate Contact Phone #1:</label>
				<input type="text" name="EmergencyContactPhone" id="EmergencyContactPhone" value="<?=addslashes($data['EmergencyContactPhone'])?>" class="" />
				<br/><br/><br/>

				<label for="EmergencyContact2Name" class="label-long">Alternate Contact Name #2:</label>
				<input type="text" name="EmergencyContact2Name" id="EmergencyContact2Name" value="<?=addslashes($data['EmergencyContact2Name'])?>" class="" />
				<br/><br/>

				<label for="EmergencyContact2Phone" class="label-long">Alternate Contact Phone #2:</label>
				<input type="text" name="EmergencyContact2Phone" id="EmergencyContact2Phone" value="<?=addslashes($data['EmergencyContact2Phone'])?>" class="" />
				<br/><br/><br/>

				<label for="EmergencyContact3Name" class="label-long">Alternate Contact Name #3:</label>
				<input type="text" name="EmergencyContact3Name" id="EmergencyContact3Name" value="<?=addslashes($data['EmergencyContact3Name'])?>" class="" />
				<br/><br/>

				<label for="EmergencyContact3Phone" class="label-long">Alternate Contact Phone #3:</label>
				<input type="text" name="EmergencyContact3Phone" id="EmergencyContact3Phone" value="<?=addslashes($data['EmergencyContact3Phone'])?>" class="" />
				<br/><br/><br/>

				<input type="submit" name="btSave" id="btSave" value="Save">
				&nbsp;<a href="account.php">Cancel</a>
			</form>
		</div>

		<div class="clear"></div>

	</div>
</div>

<script type="text/javascript">

jQuery().ready(function() {
	jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");

	jQuery("#fmAccount").validate({
		rules: {
			PatientLastName: 		{ required: true, ascii: true },
			PatientAddress1: 		{ required: true },
			PatientCity_1:			{ required: true },
			PatientState_1: 		{ required: true },
			PatientZip_1:			{ required: true, digits: true },
			PatHomePhoneWACFmt_1:	{ required: true, phoneUS: true }
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
	jQuery("input#PatHomePhoneWACFmt_1").mask("999-999-9999");
	jQuery("input#EmergencyContactPhone").mask("?999-999-9999");
	jQuery("input#EmergencyContact2Phone").mask("?999-999-9999");
	jQuery("input#EmergencyContact3Phone").mask("?999-999-9999");
});

</script>

<?php include('_footer.php'); ?>
