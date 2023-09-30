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
//get patient data
$data = array(
	'command'		=> 'get_patient_data',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);
$rxi_data = api_command($data);

if($_GET['debug']==1){
	//print_r($rxi_data);
}
$_SESSION['PLP']['patient'] = $rxi_data->patient;
clear_patient_last_name();

//get billing data

$data = array(
	'command'		=> 'get_billing',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$billing_information = api_command($data);
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
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_med_data = api_command($med_data);

$success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT, array('options' => array('default' => false)));
?>

<?php include('_header.php'); ?>

<style>
.content{background:transparent;}
body{padding-top:0px;}
nav.navbar.navbar-default.navbar-fixed-top.header-nav {position: static !important;}
input.error {border: 1px solid #ff0000 !important;background-color: transparent;color: #414141;}
label.error {font-size: 13px;font-weight: normal;}
.modal{z-index:99999;}
.blue_loader{text-align:center;}
.blue_loader img {width: auto !important;height: auto !important;}
</style>

<div class="content topContent medication_main">

	<div class="container twoColumnContainerNo">
	
		<div class="row no-marginNo">
		
			<div class="col-sm-12 leftIconBox" style="padding-top:15px;">

				<!-- .navbar -->
				<?php include('_header_nav.php'); //print_r($_SESSION['PLP']['patient']);?>
				<form method="post" class="account_edit">
					<a id="show_me_around" href="<?= $basepath?>/dashboard.php#show_me_around">Show Me Around</a>
					<div class="row account_summary_section">
						<div class="col-md-12" id="msg_box"><!-- Flash Message --></div>
						<div class="col-md-4 col-sm-6">
							<div class="account_personal">
								<div class="row">	
									<div class="Account-details"><div class="main_heading-title ">Personal</div></div>
								</div>
								<div class="row">
									<div class="Account-details">
										<div class="heading-title">
											<div class="editacc-main">
												<span class="right_section"><a class="contentHeaderLink editacc" href="javascript:void(0);" data-id="edit_pfname"><i class="fa fa-pencil"></i> EDIT</a></span>
												<p><small>Preferred Name</small><br/><span><?php echo $_SESSION['PLP']['patient']->PreferredName?></span></p>
											</div>
											<div id="edit_pfname" class="editblk hideme">
												<p><small>Preferred Name</small><br/><span>
												<input type="text" name="pf_name" id="pf_name" placeholder="Preferred Name" value="<?php echo $_SESSION['PLP']['patient']->PreferredName?>" maxlength="40" />
												<!--<input type="text" name="PatientMiddleInitial" id="PatientMiddleInitial" placeholder="Middle Initial" value="<?php //echo $_SESSION['PLP']['patient']->PatientMiddleInitial?>" maxlength="1" />
												<input type="text" name="PatientLastName" id="PatientLastName" placeholder="Last Name" value="<?php //echo $_SESSION['PLP']['patient']->PatientLastName?>" maxlength="40" />-->
												<span class="blue_loader hideme"><img src="<?= $basepath?>/images/blue-loader.gif" /><br/></span>
												<input type="button" data-edittype="edit_pfname" class="saveacc" value="Save" />
												<input type="button" class="close-edit" value="Cancel" />
												</span></p>
											</div>
										</div>
									</div>						
									<div class="Account-details">
										<div class="heading-title">
											<div class="editacc-main">
												<span class="right_section"><a class="contentHeaderLink editacc" href="javascript:void(0);" data-id="edit_pswd"><i class="fa fa-lock"></i> Change Password</a></span>
												<p><small>Password</small><br/><span>*************</span></p>
											</div>
											<div id="edit_pswd" class="editblk hideme">
												<p><small>Password</small><br/><span>
												<input type="password" name="old_pswd" id="old_pswd" placeholder="Current Password" value="" minlength="8" maxlength="20" />
												<input type="password" name="new_pswd" id="new_pswd" placeholder="New Password" value="" minlength="8" maxlength="20" />
												<input type="password" name="cnf_pswd" id="cnf_pswd" placeholder="Confirm Password" value="" minlength="8" maxlength="20" />
												<span class="blue_loader hideme"><img src="<?= $basepath?>/images/blue-loader.gif" /><br/></span>
												<input type="button" data-edittype="edit_pswd" class="saveacc" value="Save" />
												<input type="button" class="close-edit" value="Cancel" />
												</span></p>
											</div>
										</div>
									</div>
									<div class="Account-details ">
										<div class="heading-title">
											<p><small>Account Image</small><br/>
											<?php if(isset($_SESSION['PLP']['patient']->MetaData) && $_SESSION['PLP']['patient']->MetaData->profile_image!='') {
												$pimage = 'patient_images/'.$_SESSION['PLP']['patient']->MetaData->profile_image;
											} else {
												$pimage = $basepath.'/images/Account/user-image-with-black-background.png';
											}?>
											<span><img src="<?php echo $pimage?>"></span>
											<span class="image_chane"><a href="" data-toggle="modal" data-target="#change_image"><button class="btn btn-default change_profile_img">CHANGE IMAGE</button></a></span>	
											</p>
										</div>
									</div>
								</div>
							</div>
							<div class="account_shipping">
								<div class="row">	
									<div class="Account-details"><div class="main_heading-title ">Address</div></div>
								</div>
								<div class="row">
									<div class="Account-details border-none">
										<div class="heading-title">
											<div class="editacc-main">
												<p><span><?=$_SESSION['PLP']['patient']->PatientFirstName?><br/>
												<?=$_SESSION['PLP']['patient']->PatientAddress1?>, <?=$_SESSION['PLP']['patient']->PatientCity_1?>,<br/>
												<?=$_SESSION['PLP']['patient']->PatientState_1?> <?=$_SESSION['PLP']['patient']->PatientZip_1?></span></p>	
												<span class=""><a class="contentHeaderLink editacc" href="javascript:void(0);" data-id="edit_addr"><i class="fa fa-pencil"></i> EDIT </a></span>
												<br/><br/>
												<p class="image_chane hideme"><button class="btn btn-default">ADD ADDRESS</button></p>
											</div>
											<div id="edit_addr" class="editblk hideme">
												<p><small>Shipping Address</small><br/><span>
												<input type="text" name="PatientAddress1" id="PatientAddress1" placeholder="Address" value="<?=$_SESSION['PLP']['patient']->PatientAddress1?>" />
												<input type="text" name="PatientCity_1" id="PatientCity_1" placeholder="City" value="<?=$_SESSION['PLP']['patient']->PatientCity_1?>" />
												<!--<input type="text" name="PatientState_1" id="PatientState_1" placeholder="State" value="<?php //$_SESSION['PLP']['patient']->PatientState_1?>" />-->
												<select name="PatientState_1" id="PatientState_1" data-value="<?php echo $_SESSION['PLP']['patient']->PatientState_1;?>">
													<option value="">State *</option>
													<option value="AL">Alabama</option>
													<option value="AK">Alaska</option>
													<option value="AZ">Arizona</option>
													<option value="AR">Arkansas</option>
													<option value="CA">California</option>
													<option value="CO">Colorado</option>
													<option value="CT">Connecticut</option>
													<option value="DE">Delaware</option>
													<option value="DC">District Of Columbia</option>
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
												<input type="text" name="PatientZip_1" id="PatientZip_1" placeholder="Zipcode" value="<?=$_SESSION['PLP']['patient']->PatientZip_1?>" />
												<span class="blue_loader hideme"><img src="<?= $basepath?>/images/blue-loader.gif" /><br/></span>
												<input type="button" data-edittype="edit_addr" class="saveacc" value="Save" />
												<input type="button" class="close-edit" value="Cancel" />
												</span></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
				<div class="col-md-4 col-sm-6">
				<div class="account_communication">
				<div class="row">	
				<div class="Account-details">
				<div class="main_heading-title ">Communication</div>
				</div>
				</div>
				<div class="row">
				<div class="Account-details">
				<div class="heading-title">
				<div class="editacc-main">
				<span class="right_section"><a class="contentHeaderLink editacc" href="javascript:void(0);" data-id="edit_eaddr"><i class="fa fa-pencil"></i> EDIT </a></span>
				<p><small>Email (Username)</small><br/><span><?php echo $_SESSION['PLP']['patient']->account_username?></span></p>
				</div>
				<div id="edit_eaddr" class="editblk hideme">
				<p><small>Email (Username)</small><br/><span>
				<input type="text" name="eaddress" id="eaddress" placeholder="Email Address" value="<?php echo $_SESSION['PLP']['patient']->account_username?>" />
				<span class="blue_loader hideme"><img src="<?= $basepath?>/images/blue-loader.gif" /><br/></span>
				<input type="button" data-edittype="edit_eaddr" class="saveacc" value="Save" />
				<input type="button" class="close-edit" value="Cancel" />
				</span></p>
				</div>
				</div>
				</div>
				
				<div class="Account-details">
				<div class="heading-title">
				<div class="editacc-main">
				<span class="right_section"><a class="contentHeaderLink editacc" href="javascript:void(0);" data-id="edit_phn"><i class="fa fa-pencil"></i> EDIT </a></span>
				<p><small>Phone</small><br/><span><a href="tel:<?php echo $_SESSION['PLP']['patient']->PatHomePhoneWACFmt_1?>" class="contact_del"><?php echo $_SESSION['PLP']['patient']->PatHomePhoneWACFmt_1?></a></span></p>
				</div>
				<div id="edit_phn" class="editblk hideme">
				<p><small>Phone</small><br/><span>
				<input type="text" name="PatHomePhoneWACFmt_1" id="PatHomePhoneWACFmt_1" placeholder="Phone Number" value="<?php echo $_SESSION['PLP']['patient']->PatHomePhoneWACFmt_1?>" />
				<span class="blue_loader hideme"><img src="<?= $basepath?>/images/blue-loader.gif" /><br/></span>
				<input type="button" data-edittype="edit_phn" class="saveacc" value="Save" />
				<input type="button" class="close-edit" value="Cancel" />
				</span></p>
				</div>
				</div>
				</div>				
				
				<?php
				$contactsEmpty = array();
				$i = 1;
				for( $loop=1; $loop<=3; $loop++ ) {
					if($loop==1){
						$nameKey = 'EmergencyContactName';
						$phoneKey = 'EmergencyContactPhone';
					}
					else{
						$nameKey = 'EmergencyContact'.$loop.'Name';
						$phoneKey = 'EmergencyContact'.$loop.'Phone';
					}
					
					if($_SESSION['PLP']['patient']->$nameKey!='' || $_SESSION['PLP']['patient']->$phoneKey!='') {
					?>
				<div class="Account-details">
					<div class="heading-title">
						<div class="editacc-main">
							<span class="right_section"><a class="contentHeaderLink editacc" href="javascript:void(0);" data-id="edit_altcont<?= $i?>"><i class="fa fa-pencil"></i> EDIT </a></span>
							<p>
								<small>Alternate Contact <?= $i?></small><br/>
								<?php if($_SESSION['PLP']['patient']->$nameKey!='' || $_SESSION['PLP']['patient']->$phoneKey!='') { ?>
								<span><?php echo $_SESSION['PLP']['patient']->$nameKey?> - <a href="tel:<?php echo esc_phone($_SESSION['PLP']['patient']->$phoneKey); ?>" class="contact_del"><?php echo $_SESSION['PLP']['patient']->$phoneKey?></a></span><br/>
								<?php } ?>							
							</p>
						</div>
						<div id="edit_altcont<?= $i?>" class="editblk hideme">
							<p>
								<small>Alternate Contact <?= $i?></small><br/>
								<span>				
									<input type="text" name="<?= $nameKey?>" id="<?= $nameKey?>" placeholder="Emergency Contact Name <?= $i?>" title="Emergency Contact Name <?= $i?>" value="<?php echo $_SESSION['PLP']['patient']->$nameKey?>" />
									<input type="text" name="<?= $phoneKey?>" id="<?= $phoneKey?>" placeholder="Emergency Contact Number <?= $i?>" title="Emergency Contact Number <?= $i?>" value="<?php echo esc_phone($_SESSION['PLP']['patient']->$phoneKey)?>" />
									<span class="blue_loader hideme"><img src="<?= $basepath?>/images/blue-loader.gif" /><br/></span>
									<input type="button" data-edittype="edit_altcont<?= $i?>" class="saveacc" value="Save" />
									<input type="button" class="close-edit" value="Cancel" />
								</span>
							</p>
						</div>
					</div>
				</div>
				<?php
					$i++;
					}
					else{
						$contactsEmpty[] = $loop;
					}					
				}
				// if contacts are empty
				if( count($contactsEmpty)>0 ){
					//$i = 1;
					foreach($contactsEmpty as $cont){
						if($cont==1){
							$nameKey = 'EmergencyContactName';
							$phoneKey = 'EmergencyContactPhone';
						}
						else{
							$nameKey = 'EmergencyContact'.$cont.'Name';
							$phoneKey = 'EmergencyContact'.$cont.'Phone';
						} ?>
					<div class="Account-details">
						<div class="heading-title">
							<div class="editacc-main">
								<span class="right_section"><a class="contentHeaderLink editacc" href="javascript:void(0);" data-id="edit_altcont<?= $i?>"><i class="fa fa-pencil"></i> EDIT </a></span>
								<p><small>Alternate Contact <?= $i?></small></p>
							</div>
							<div id="edit_altcont<?= $i?>" class="editblk hideme">
								<p>
									<small>Alternate Contact <?= $i?></small><br/>
									<span>				
										<input type="text" name="<?= $nameKey?>" id="<?= $nameKey?>" placeholder="Emergency Contact Name <?= $i?>" title="Emergency Contact Name <?= $i?>" value="<?php echo $_SESSION['PLP']['patient']->$nameKey?>" />
										<input type="text" name="<?= $phoneKey?>" id="<?= $phoneKey?>" placeholder="Emergency Contact Number <?= $i?>" title="Emergency Contact Number <?= $i?>" value="<?php echo esc_phone($_SESSION['PLP']['patient']->$phoneKey)?>" />
										<span class="blue_loader hideme"><img src="<?= $basepath?>/images/blue-loader.gif" /><br/></span>
										<input type="button" data-edittype="edit_altcont<?= $i?>" class="saveacc" value="Save" />
										<input type="button" class="close-edit" value="Cancel" />
									</span>
								</p>
							</div>
						</div>
					</div>
					<?php
					$i++;
					}
				}
				?>
				</div>
				</div>
				
				<div class="account_Caregivers" style="display:none;">
				<div class="row">	
				<div class="Account-details">
				<div class="main_heading-title ">Caregivers</div>
				</div>
				</div>
				<div class="row">
				<div class="Account-details border-none">
				<div class="heading-title">
				<p><small>Your shipments will be sent to</small><br/><span><?=$_SESSION['PLP']['patient']->PatientFirstName?><br/>						
				<?=$_SESSION['PLP']['patient']->PatientAddress1?>, <?=$_SESSION['PLP']['patient']->PatientCity_1?>,<br/>
				<?=$_SESSION['PLP']['patient']->PatientState_1?> <?=$_SESSION['PLP']['patient']->PatientZip_1?></span></p>									
				<span class=""><a class="contentHeaderLink"><i class="fa fa-pencil"></i> EDIT </a></span>
				<br/><br/>
				<p class="image_chane"><button class="btn btn-default">ADD ADDRESS</button></p>	
				</div>
				</div>							
				</div>
				</div>
				</div>
				<div class="col-md-4 col-sm-12">
				<div class="account_accesbility" style="display:none;">
				<div class="row">	
				<div class="Account-details">
				<div class="main_heading-title ">Accessibility</div>
				</div>
				</div>
				<div class="row">
				<div class="Account-details">
				<div class="heading-title">
				<?php $onOffTxt = 'Off'; if(isset($patient_data['metadata'][0]) && $patient_data['metadata'][0]->accessibility!=''){
				if($patient_data['metadata'][0]->accessibility==1){
				$onOffTxt = 'On'; $onOffChecked = 'checked';
				} else {
				$onOffTxt = 'Off'; $onOffChecked = '';
				}
				}?>
				<div class="on-section"><small>Larger Text</small><br/><span id="onofftext"><?php echo $onOffTxt?></span></div>
				<div class="onoffswitch">
				<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" <?php echo $onOffChecked?> />
				<label class="onoffswitch-label" for="myonoffswitch">
				<div class="onoffswitch-inner"></div>
				<div class="onoffswitch-switch"></div>
				</label>
				</div>
				</div>
				</div>
				</div>
				</div>
				
				<div class="account_health-history">
				<div class="row">	
				<div class="Account-details">
				<div class="main_heading-title ">Health History</div>
				</div>
				</div>
				<div class="row">
				<div class="Account-details">
				<div class="heading-title">
				
				<p><small>Date of Birth</small><br/><span><?php echo date('F j, Y', strtotime($_SESSION['PLP']['patient']->PatientDOB))?></span></p>								
				</div>
				</div>
				<?php
					$allergies_text = '<div class="alert alert-info"><p>No allergies added yet</p></div>';
					if(isset($_SESSION['PLP']['patient']->Allergies) && $_SESSION['PLP']['patient']->Allergies!=''){
						$allergies = explode(',', $_SESSION['PLP']['patient']->Allergies);
						if(count($allergies)>0){
							$allergies_text = '';
							foreach($allergies as $allergy){
								$allergies_text .= '<a class="btn btn-default change_profile_img remove_hhistory"><label>'.$allergy.'</label><span data-type="allergy">&times;</span></a>';
							}
							$allergies_text .= '<br/><span class="blue_loader hideme"><img src="'.$basepath.'/images/blue-loader.gif" /><br/></span>';
						}
						else {
							$allergies_text = '<div class="alert alert-info"><p>No allergies added yet</p></div>';
						}
					}?>
				<div class="Account-details">
				<div class="heading-title">
				<div class="editacc-main">
				<p>
				<small>Allergies</small><br/>
				<span>
				<?php echo $allergies_text;?>
				<a class="btn btn-primary editacc change_profile_img" data-id="edit_allergy">+ ADD ALLERGY </a>
				</span>
				</p>
				</div>
				<div id="edit_allergy" class="editblk hideme">
				<p><small>Add your allergies (comma separated)</small><br/><span>
				<textarea name="allergies" id="allergies" placeholder="Add your allergies (comma separated)" rows="3" cols="40"><?php echo $_SESSION['PLP']['patient']->Allergies?></textarea>
				<span class="blue_loader hideme"><img src="<?= $basepath?>/images/blue-loader.gif" /><br/></span>
				<input type="button" data-edittype="edit_allergy" class="saveacc" value="Save" />
				<input type="button" class="close-edit" value="Cancel" />
				</span></p>
				</div>
				</div>
				</div>
				<?php
				$conditions_text = '<div class="alert alert-info"><p>NO KNOWN MEDICAL CONDITIONS</p></div>';
				if(isset($_SESSION['PLP']['patient']->Conditions) && $_SESSION['PLP']['patient']->Conditions!=''){
				$conditions = explode(',', $_SESSION['PLP']['patient']->Conditions);
				if(count($conditions)>0){
				$conditions_text = '';
				foreach($conditions as $condition){
				$conditions_text .= '<a class="btn btn-default change_profile_img remove_hhistory"><label>'.$condition.'</label><span data-type="conditions">&times;</span></a>';
				}
				$conditions_text .= '<br/><span class="blue_loader hideme"><img src="'.$basepath.'/images/blue-loader.gif" /><br/></span>';
				}
				else {
				$conditions_text = '<div class="alert alert-info"><p>NO KNOWN MEDICAL CONDITIONS</p></div>';
				}
				} ?>
				<div class="Account-details">
				<div class="heading-title">
				<div class="editacc-main">
				<p>
				<small>Conditions</small><br/>
				<span>
				<?php echo $conditions_text;?>
				<a class="btn btn-primary change_profile_img editacc" data-id="edit_conditions">+ ADD CONDITION </a>
				</span>
				</p>
				</div>
				<div id="edit_conditions" class="editblk hideme">
				<p><small>Add your medical conditions (comma separated)</small><br/><span>
				<textarea name="conditions" id="conditions" placeholder="Add your medical conditions (comma separated)" rows="3" cols="40"><?php echo $_SESSION['PLP']['patient']->Conditions?></textarea>
				<span class="blue_loader hideme"><img src="<?= $basepath?>/images/blue-loader.gif" /><br/></span>
				<input type="button" data-edittype="edit_conditions" class="saveacc" value="Save" />
				<input type="button" class="close-edit" value="Cancel" />
				</span></p>
				</div>
				</div>
				</div>
				</div>
				</div>
				</div>
				</div>
				</form>
			</div>
		
		</div>
	
	</div>

</div>

<div id="overlay">
	<div id="overlay_holder"></div>
</div>

<!-- Change Profile Image --> 
<div class="modal fade" id="change_image" tabindex="-1" role="dialog" aria-labelledby="change_image" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form method="post" id="changeimage" name="changeimage" enctype="multipart/form-data">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="card-payment">Change Profile Image</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="msg"></div>
					<div class="overlay_form-payment">
						<div class="row zeroed">
							<div class="col-sm-12 text-center">
								<p id="drop-area">Drop file here</p>
								OR
								<p id="select-area"><input type="file" class="autowidth" name="profile_image" id="profile_image" placeholder="Select a profile image"></p>								
								<input type="hidden" id="action" name="action" value="save_profile_image" />
								<input type="hidden" id="p_id" name="p_id" value="<?php echo $_SESSION['PLP']['patient']->PatientID?>" />
							</div>
						</div>
						<div class="row"><span class="col-sm-12 info-text"> Allowed formats JPEG, JPG, PNG and size 2 MB(max)</span></div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="submit" class="btn btn-primary" value="Save" />
				</div>
			</div>
		</form>
	</div>
</div>	
<!--Change Profile Image -->

<div id="overlay_edit_billing" class="overlay_content">

	<div class="overlay_loaded_content">
	
		<div class="overlay_form">
			<center><h3><b><?php if ($billing_information->chargeback) { ?>Please update your payment information<?php } else { ?>Edit Billing Information<?php } ?></b></h3></center>
			<br>
			<div class="row zeroed">
				<div class="col-sm-6">
					<b>Credit Card Number<font class="red">*</font></b><br>
					<input type="text" class="autowidth numeric" id="cc_number" maxlength="16"/>
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
<input type="text" class="autowidth exp_date" id="cc_exp"/>
</div>
<div class="col-sm-6">
<b>CVV<font class="red">*</font></b><span class="question" data-tooltipTarget="cvvCode">?</span><br>
<input type="text" class="autowidth numeric" id="cc_cvv" maxlength="4"/>
</div>
</div>

<br>

<center>
<h4><b>Billing Address</b></h4>
</center>

<div class="row zeroed">
<div class="col-sm-6">
<b>First Name<font class="red">*</font></b><br>
<input type="text" class="autowidth" id="PatientFirstNameBilling"/>
</div>
<div class="col-sm-6">
<b>Last Name<font class="red">*</font></b><br>
<input type="text" class="autowidth" id="PatientLastNameBilling"/>
</div>
</div>

<div class="row zeroed">
<div class="col-sm-6">
<b>Address Line 1<font class="red">*</font></b><br>
<input type="text" class="autowidth" id="PatientAddress1Billing" autocomplete="new-password"/>
</div>
<div class="col-sm-6">
<b>Address Line 2</b><br>
<input type="text" class="autowidth" id="PatientAddress2Billing"/>
</div>
</div>

<div class="row zeroed">
<div class="col-sm-4">
<b>City<font class="red">*</font></b><br>
<input type="text" class="autowidth" id="PatientCity_1Billing"/>
</div>
<div class="col-sm-4">
<b>State<font class="red">*</font></b><br>

<select name="PatientState_1Billing" id="PatientState_1Billing" class="autowidth form-control login-portal">
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
<input type="text" class="autowidth numeric" id="PatientZip_1Billing" maxlength="5"/>
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

<div id="overlay_overdue_invoices" class="overlay_content">

	<div class="overlay_loaded_content">
	
		<div class="overlay_form text-center text-16">
			<h3><b>Overdue Invoices</b></h3>
			<br>
			
			<div class="details-sec-list">
			<?php foreach ($billing_information->open_invoices as $key => $invoice) { ?>
				<?php if ($key > 0) { ?>
				<div class="row zeroed">
					<div class="col-sm-8"><div class="bottom-light-border-1-small-margins"></div></div>
				</div>
				<?php } ?>

				<div class="row zeroed">
					<div class="label-content-left text-left col-sm-6"><b>Invoice Number</b></div>
					<div class="label-content-right text-right col-sm-6"><?=$invoice->InvoiceID?></div>
				</div>

				<div class="row zeroed">
					<div class="label-content-left text-left col-sm-6"><b>Invoice Date</b></div>
					<div class="label-content-right text-right col-sm-6"><?=date('m/d/Y', strtotime($invoice->InvoiceDate))?></div>
				</div>

				<div class="row zeroed">
					<div class="label-content-left text-left col-sm-6"><b>Invoice Amount</b></div>
					<div class="label-content-right text-right col-sm-6">$<?=number_format($invoice->InvoiceTotal, 2)?></div>
				</div>			
			<?php } ?>
			</div>
			<?php if (!$billing_information->chargeback) { ?>
			<div class="row zeroed">
				<div class="col-xs-12 text-center btn-grp">
					<a href="javascript:makePayment(<?=$invoice->InvoiceID?>, '<?=date('m/d/Y', strtotime($invoice->InvoiceDate))?>', '<?=number_format($invoice->InvoiceTotal, 2)?>');" class="big-button orangeButton">Make Payment</a>
					<span class="mobile-hidden">&nbsp;</span>
					<a href="javascript:schedulePayment(<?=$invoice->InvoiceID?>, '<?=date('m/d/Y', strtotime($invoice->InvoiceDate))?>', '<?=number_format($invoice->InvoiceTotal, 2)?>');" class="big-button orangeButton">Schedule Payment</a>
					<span class="mobile-hidden">&nbsp;</span>
					<a href="dashboard.php" class="big-button orangeButton">Go Back</a>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</div>

<div id="overlay_schedule_payment" class="overlay_content">
	<div class="overlay_loaded_content">	
		<div class="overlay_form">
			<center><h3 id="titleScheduleBill"><b>Schedule Payment</b></h3></center>		
			<br>		
			<div id="formScheduleBill">
				<div class="row" style="line-height:35px;">
					<div class="col-xs-12 col-lg-5 alignLeft"><b>Invoice Number</b></div>
					<div class="col-xs-12 col-lg-7 alignRight"><span id="ScheduleInvoiceNumber"><?=$last_invoice_no?></span><input type="hidden" id="schedule_number" value="<?=$last_invoice_no?>"/></div>
				</div>
				<div class="row" style="line-height:35px;">
					<div class="col-xs-12 col-lg-5 alignLeft"><b>Invoice Amount</b></div>
					<div class="col-xs-12 col-lg-7 alignRight"><span id="ScheduleInvoiceAmount">$<?=$last_invoice_amount ?></span></div>
				</div>
				<div class="row" style="line-height:35px;">
					<div class="col-xs-12 col-lg-5 alignLeft"><b>Payment Information</b></div>
					<div class="col-xs-12 col-lg-7 alignRight"><span id="SchedulePaymentMethod"><?=$billing_information->payment_info_cc ?> (Exp. Date <?=$billing_information->payment_info_exp ?>)</span></div>
				</div>
				<div class="row" style="line-height:35px;">
					<div class="col-xs-12 col-lg-5 alignLeft"><b>Schedule Date<font class="red">*</font></b></div>
					<div class="col-xs-12 col-lg-7 alignRight"><input type="text" class="date alignRight" id="schedule_date" data-toggle="datepicker"/></div>
				</div>
			</div>
			<div class="text-center btn-payment">
				<span id="btnSaveScheduledPayment"><a href="javascript:saveScheduledPayment();" class="big-button whiteButton">Schedule Payment</a></span>
				<span id="btnSavingScheduledPayment" style="display:none;"><a href="javascript:void(0);" class="big-button whiteButton">Saving...</a></span>
				<a href="javascript:closeOverlay();" id="scheduleCancelLink" class="small-button">Go Back</a>
			</div>
		</div>		
	</div>
</div>

<div id="overlay_make_payment" class="overlay_content">
	<div class="overlay_loaded_content">
		<div class="overlay_form text-center">
			<p id="txtPayBillInfo" class="text-18">You are about to make a payment for <b id="payBillAmount"></b> with <b id="payWithCC"><?=$billing_information->payment_info_cc ?> (Exp. Date <?=$billing_information->payment_info_exp ?>)</b>. If you want to use a different payment method, you must call a patient advocate to update your monthly recurring payment option. Please note, Prescription Hope is not responsible for any fees you may incur by your bank when providing payment. Are you sure you want to submit your payment?</p>
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
			<p id="txtPayBillInfoNewCC" class="text-center" style="display: none;"></p>
			<div id="formPayBillNewCC">
				<div class="row zeroed" style="line-height:35px;">
					<div class="col-xs-12 col-lg-6 alignLeft"><b>Invoice Number</b></div>
					<div class="col-xs-12 col-lg-6 alignRight"><span id="payCCInvoiceNoText"></span><input type="hidden" id="payCCInvoiceNo" value=""/></div class="col-xs-12 col-lg-6 alignRight">
				</div>
				<div class="row zeroed" style="line-height:35px;">
					<div class="col-xs-12 col-lg-6 alignLeft"><b>Invoice Amount</b></div>
					<div class="col-xs-12 col-lg-6 alignRight">$<span id="payCCInvoiceAmountText"></span><input type="hidden" id="payCCInvoiceAmount" value=""/></div>
				</div>
				<div class="row zeroed" style="line-height:35px;">
					<div class="col-xs-12 col-lg-6 alignLeft m10"><b>Credit Card Type<font class="red">*</font></b></div>
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
					<div class="col-xs-12 col-lg-6 alignLeft m10"><b>Credit Card Number<font class="red">*</font></b></div>
					<div class="col-xs-12 col-lg-6 alignRight"><input type="text" class="autowidth numeric" id="payCCNumber" maxlength="16"/></div>
				</div>
				<div class="row zeroed" style="line-height:35px;">
					<div class="col-xs-12 col-lg-6 alignLeft m10"><b>Credit Card Expiration Month<font class="red">*</font></b></div>
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
					<div class="col-xs-12 col-lg-6 alignLeft m10"><b>Credit Card Expiration Year<font class="red">*</font></b></div>
					<div class="col-xs-12 col-lg-6 alignRight">
						<select name="payCCExpYear" id="payCCExpYear" class="form-control login-portal">
							<option value=""></option>
							<?php for ($y = date('Y'); $y < (date('Y') + 10); $y++) { ?>
							<option value="<?=$y?>"><?=$y?></option>
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
<div class="right security-icons <?=((basename($_SERVER['PHP_SELF']) == 'login.php') ? 'full-width' : '12')?>" style="text-align:right;">
<center>
<table width="150" border="0" cellpadding="0" cellspacing="0">
<tr>
<td width="150" valign="top" style="padding-top: 0px; border-width: 0;">
<table width="150" border="0" cellpadding="2" cellspacing="0" title="Click to Verify - This site chose Symantec SSL for secure e-commerce and confidential communications.">
<tr>
<td width="135" align="center" valign="top"><script src="https://cdn.ywxi.net/js/inline.js?w=120"></script><!--script type="text/javascript" src="https://seal.websecurity.norton.com/getseal?host_name=manage.prescriptionhope.com&amp;size=S&amp;use_flash=NO&amp;use_transparent=NO&amp;lang=en"></script--><br />
<!--a href="http://www.symantec.com/ssl-certificates" target="_blank"  style="color:#000000; text-decoration:none; font:bold 7px verdana,sans-serif; letter-spacing:.5px; text-align:center; margin:0px; padding:0px;">ABOUT SSL CERTIFICATES</a--></td>
</tr>
</table>
</td>
</tr>
</table>
</center>
</div>
Please do not submit password-protected documents. We keep your information 100% safe and protected. It is secured by 256-bit encryption, the same security banks use.
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
We were unable to process your payment on <?=date('m/d/Y', strtotime($last_nsf_added))?> a fee of $25 has been added to your invoice.
<br><br>
Please update your Billing Information to avoid additional fees or disruption of service.
</div>
<div id="tooltipContent_cvvCode">
<img src="images/cvvLocation.png"/>
</div>
</div>


<!-- End portal tour modal -->
<div class="modal fade" id="portal_tour_end" tabindex="-1" role="dialog" aria-labelledby="portal_tour_end" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title text-center">You Have Completed Your Tour</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div id="msg"></div>
				<div class="refer_box">
					<div class="row zeroed">
						<div class="col-md-12"><p>Thank you for going through the new Prescription Hope account tour. We look forward to being able to provide unmatched Rx savings for you.</p></div>						
						<div class="col-md-12">
							
						</div>						
					</div>						
				</div>
			</div>
			<div class="modal-footer">
				<div class="text-center">
					<a id="finishTour" class="btn btn-primary btn-success">Finish Tour</a>
				</div>
			</div>
		</div>

	</div>
</div>	
<!-- End portal tour modal -->
<script>

jQuery(document).ready(function() {
	<?php /*if ($billing_information->chargeback) { ?>
		showEditCC();
		forceShowEditCC = 1;
	<?php } ?>

	//force users to pay their due invoices
	<?php if ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback) { ?>
		showOverdueInvoices();
		forceShowOverdueInvoices = 1;
	<?php }*/ ?>
	
	jQuery('#PatientState_1 option[value='+jQuery('#PatientState_1').attr('data-value')+']').attr('selected', 'selected');
	jQuery("#PatHomePhoneWACFmt_1, #EmergencyContactPhone, #EmergencyContact2Phone, #EmergencyContact3Phone").mask('(999)999-9999', {clearIfNotMatch: true});
	jQuery("#PatientZip_1").mask('99999', {clearIfNotMatch: true});
	
	var iOS = !!navigator.platform &&  
                /iPad|iPhone|iPod/.test(navigator.platform);
	if( !iOS ){
		jQuery.validator.addMethod("password_aA1", function(value, element) { return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[\W\w]{8,20}$/.test(value); }, "Please use upper & lower case letters and numbers.");
		jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");	
		jQuery("form.account_edit").validate({
			rules:{
				PatientFirstName:{required:true, lettersonly:true, maxlength: 25},
				PatientMiddleInitial:{lettersonly:true, ascii: true, maxlength: 1},
				PatientLastName:{required:true, lettersonly:true, maxlength: 25},
				old_pswd:{required:true},
				new_pswd:{required:true, password_aA1: true, minlength: 8, maxlength: 20},
				cnf_pswd:{required:true, equalTo: '#new_pswd'},
				eaddress:{required:true, email:true},
				PatientAddress1:{required:true},
				PatientCity_1:{required:true},
				PatientState_1:{required:true},
				PatientZip_1:{required:true},
				PatHomePhoneWACFmt_1:{required:true},			
			}
		});
	}
	
	jQuery('.editacc').click(function(){
		// close other edit blocks
		jQuery('.editacc-main').removeClass('hideme');
		jQuery('.editblk').addClass('hideme');
		var blockId = jQuery(this).attr('data-id');
		jQuery(this).parents('.editacc-main').addClass('hideme');
		jQuery('#'+blockId).removeClass('hideme');
	});
	jQuery('.close-edit').click(function(){
		var blockId = jQuery(this).parents('.editblk').attr('id');
		jQuery('#'+blockId).siblings('.editacc-main').removeClass('hideme');
		jQuery('#'+blockId).addClass('hideme');
	});
	
	jQuery('.saveacc').click(function(){
		var saveType = jQuery(this).attr('data-edittype');
		var dataString = 'type='+saveType+'&id=<?php echo $_SESSION['PLP']['patient']->PatientID?>';
		var emptyVal = [];
		jQuery('#'+saveType+' input, #'+saveType+' textarea, #'+saveType+' select').each(function(i){
			var key = jQuery(this).attr('name');
			var val = jQuery(this).val();
			if(key!='' && key!='undefined' && typeof key!='undefined'){
				if(val == ''){
					if(key == 'PatientMiddleInitial' || key == 'address2' || key.indexOf('EmergencyContact')>=0 ){
						dataString += '&'+key+'='+val;
					}
					else{
						emptyVal[i] = key;
					}
				}
				else{
					if( jQuery('#'+saveType+' textarea').length>0 ){
						var all_values = val.split(',');
						var values = [];
						var j = 0;
						jQuery.each(all_values, function(i){
							if(all_values[i]!=''){
								values[j] = all_values[i];
								j++;
							}
						});
						val = values.join(',');
					}
					dataString += '&'+key+'='+val; 
				}
			}			
		});
		
		// for alt contacts validation
		if(saveType.indexOf('edit_altcont')>=0){
			var hasVal = [];
			jQuery('#'+saveType+' input[type="text"]').each(function(j){
				hasVal[j] = 0;
				if(jQuery.trim(jQuery(this).val())!=''){
					hasVal[j] = 1;					
				}
			});
			var index = emptyVal.length;
			if(hasVal[0]==1 && hasVal[1]==0){
				emptyVal[index] = jQuery('#'+saveType).find('input:nth-child(2)').attr('name');
			}
			if(hasVal[0]==0 && hasVal[1]==1){
				emptyVal[index] = jQuery('#'+saveType).find('input:nth-child(1)').attr('name');
			}
		}
		//console.log(emptyVal); console.log('stop..'); return;
		if(emptyVal.length>0){
			jQuery.each(emptyVal, function(k,v){				
				jQuery('input[name="'+v+'"]').addClass('error');				
			});
		}
		else {
			jQuery('#'+saveType+' .blue_loader').removeClass('hideme');
			
			jQuery.post("update_account.php", {data: dataString})
			.done(function(response) {
				jQuery('#'+saveType+' .blue_loader').addClass('hideme');
				response = JSON.parse(response);
				if (response.success) {
					jQuery('#msg_box').html('<p class="alert alert-success alert-dismissible">Information saved successfully.</p>');
					jQuery("html, body").animate({ scrollTop: 0 }, "2000");
					window.location.reload(true);
				} else {
					jQuery('#msg_box').html('<p class="alert alert-danger alert-dismissible">'+response.message+'</p>');
					jQuery("html, body").animate({ scrollTop: 0 }, "2000");
				}
			});
		}
	});
	
	jQuery("#changeimage").on('submit',(function(e) {
		e.preventDefault();
		jQuery.ajax({
			url: "_ajax_request.php",
			type: "POST",
			data:  new FormData(this),
			contentType: false,
			cache: false,
			processData:false,
			beforeSend : function() {
				jQuery("#msg").fadeOut();
			},
			success: function(response) {
				data = JSON.parse(response);
				jQuery("#msg").html('<div class="alert alert-'+data.type+'"><p>'+data.msg+'</p></div>').fadeIn();
				if(data.type=='success'){
					jQuery('#change_image .overlay_form-payment, #change_image .modal-footer').hide();
					setTimeout(function(){window.location.reload(true);},5000);
				}				
			},
			error: function(e) {
				jQuery("#msg").html(e).fadeIn();
			}          
		});
	}));
	
	jQuery('#myonoffswitch').on('change', function(){
		var as_value = (jQuery(this).is(':checked')) ? 'On' : 'Off';
		jQuery('<img id="loader" src="images/ajax-loader.gif">').insertAfter('label.onoffswitch-label');
		jQuery.post("_ajax_request.php", {action: "save_meta_data", data: 'type=accessibility&as_value='+as_value+'&id=<?php echo $_SESSION['PLP']['patient']->PatientID?>'})
			.done(function(response) {
				response = JSON.parse(response);
				if (response.success == 1) {
					console.log('Done');
					jQuery('#onofftext').text(as_value);
					if(as_value=='On'){
						jQuery('body').addClass('big_text').removeClass('normal_text');
					}
					else{
						jQuery('body').removeClass('big_text').addClass('normal_text');
					}					
				} else {
					alert('Error');
				}
				jQuery('#loader').remove();
			});
	});
	
	jQuery('.remove_hhistory>span').on('click', function(e){
		e.preventDefault();
		jQuery(this).parent('.remove_hhistory').siblings('span.blue_loader').removeClass('hideme');
		var type = jQuery(this).attr('data-type');
		var val = jQuery(this).parent('.remove_hhistory').find('label').text();
		
		var all_values = jQuery('#edit_'+type).find('textarea').val();
		var type_name = jQuery('#edit_'+type).find('textarea').attr('name');
		all_values = all_values.replace(val,'');
		all_values = all_values.split(',');
		var values = [];
		var j = 0;
		jQuery.each(all_values, function(i){
			if(all_values[i]!=''){
				values[j] = all_values[i];
				j++;
			}
		});
		
		//jQuery.post("_ajax_request.php", {action: "save_meta_data", data: 'type=remove_'+type+'&id=<?php echo $_SESSION['PLP']['patient']->PatientID?>&value='+val})
		jQuery.post("update_account.php", {data: 'type=edit_'+type+'&id=<?php echo $_SESSION['PLP']['patient']->PatientID?>&'+type_name+'='+values})
			.done(function(response) {
				jQuery(this).parent('.remove_hhistory').siblings('span.blue_loader').addClass('hideme');
				response = JSON.parse(response);
				if (response.success) {
					jQuery('#msg_box').html('<p class="alert alert-success alert-dismissible">Selected '+type+' removed successfully.</p>');
					jQuery("html, body").animate({ scrollTop: 0 }, "2000");
					window.location.reload(true);
				} else {
					jQuery('#msg_box').html('<p class="alert alert-danger alert-dismissible">'+response.message+'</p>');
					jQuery("html, body").animate({ scrollTop: 0 }, "2000");
				}				
			});
	});
	
	jQuery("#drop-area").on('dragenter', function (e){
		e.preventDefault();
		jQuery(this).css('background', '#BBD5B8');
	});
	
	jQuery("#drop-area").on('dragover', function (e){
		e.preventDefault();
	});
	
	jQuery("#drop-area").on('drop', function (e){
		jQuery(this).css('background', '#DEDEDE');
		e.preventDefault();
		var image = e.originalEvent.dataTransfer.files;
		var formImage = new FormData();
		formImage.append('profile_image', image[0]);
		formImage.append('action', 'save_profile_image');
		formImage.append('p_id', '<?php echo $_SESSION['PLP']['patient']->PatientID?>');
		jQuery.ajax({
			url: "_ajax_request.php",
			type: "POST",
			data: formImage,
			contentType: false,
			cache: false,
			processData:false,
			beforeSend : function() {
				jQuery('#changeimage .modal-footer').hide();
				jQuery("#msg").fadeOut();
			},
			success: function(response) {
				data = JSON.parse(response);
				jQuery("#msg").html('<div class="alert alert-'+data.type+'"><p>'+data.msg+'</p></div>').fadeIn();
				if(data.type=='success'){
					jQuery('#change_image .overlay_form-payment, #change_image .modal-footer').hide();
					setTimeout(function(){window.location.reload(true);},2000);
				}
				else{
					jQuery('#changeimage .modal-footer').show();
				}				
				jQuery(this).css('background', 'transparent');
			},
			error: function(e) {
				jQuery("#msg").html(e).fadeIn();
			}          
		});	
	});
});
</script>

<?php
function esc_phone($phone){
	return str_replace(array('(',')','-',' ') ,'', $phone);
}
include('_footer.php'); ?>