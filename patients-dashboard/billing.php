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

//get meds/providers data

$data = array(
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);


//Get Patient Data
$data = array(
	'command'		=> 'get_patient_data',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$get_patient_data = api_command($data);
$_SESSION['PLP']['patient'] = $get_patient_data->patient;

//get meds for invoices
$data = array(
	'command'		=> 'get_billing_info',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$meds_for_invoices = api_command($data);

$success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT, array('options' => array('default' => false)));

//active meds
$active_meds = 0;
foreach ($rxi_data->meds as $med) {
	if ($med->MedAssistDetailID > 0) {
		$active_meds++;
	}
}

$actual_link = "{$_SERVER['REQUEST_URI']}";
$inv_id = base64_decode($_GET['inv_id']);

if($inv_id!="" && (int)$inv_id)
{
	//get invoice data

	$data = array(
		'command'		=> 'get_invoice_data',
		'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
		'access_code'	=> $_SESSION['PLP']['access_code'],
		'invoice_id'	=> $inv_id
	);

	$get_invoice_data = api_command($data);	
?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Invoice</title>
	</head>
	<body>
    <!-- partial:index.partial.html -->
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0">
				<tr class="top">
					<td colspan="4">
						<table>
							<tr>
								<td class="title"><a href="https://prescriptionhope.com" class="svg"><object data="https://prescriptionhope.com/images/ph-logo.svg" type="image/svg+xml" width="150" class="top-menu-only-desktop only-desktop"><img src="https://prescriptionhope.com/wp-content/uploads/2017/07/prescription-hope-logo-2017_07_13.png"></object></a></td>
								<td></td>
							</tr>
							<tr>
								<td class="address">
									Prescription Hope, Inc.
									<br>2100 SE Ocean Blvd, Suite 300
									<br>Stuart, FL 34996
									<!-- <br> PO Box 2700
									<br> Westerville, Ohio 43086 -->
								</td>
								<td class="address">
									<h3><?php echo $get_patient_data->patient->PatientFirstName.' '.$get_patient_data->patient->PatientMiddleInitial.' '.$get_patient_data->patient->PatientLastName;?></h3> <?php echo $get_patient_data->patient->PatientAddress1;?>
									<br/> <?php echo $get_patient_data->patient->PatientCity_1;?>,
									<br/> <?php echo $get_patient_data->patient->PatientState_1.' '.$get_patient_data->patient->PatientZip_1;?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="heading">
					<td>MEDICATION</td>
					<td>QUANTITY</td>
					<td>YOUR COST</td>
				</tr>
				<?php
				if(count($get_invoice_data->approved_invoices) > 0)
				{
					foreach($get_invoice_data->approved_invoices->meds as $invoice_line)
					{
					?>
						<tr class="details">
							<td><?php echo $invoice_line->LineDescription;?></td>
							<td>1</td>
							<td>$ <?php echo $invoice_line->LineAmount;?></td>
						</tr>
					<?php
					}
				}
				?>
				<tr class="details">
					<td colspan="3"><small>INV #<?php echo $get_invoice_data->approved_invoices->InvoiceID;?></small></td>
				</tr>
				<tr class="heading">
					<td colspan="2">Your Service Fee Total</td>
					<td>$ <?php echo $get_invoice_data->approved_invoices->InvoiceTotal;?></td>
				</tr>
				<tr>
					<td colspan="3">
						<p><strong>Important:</strong> You understand that Prescription Hope does not ship, prescribe, sell, handle or dispense prescription medication of any kind in our efforts to process your application(s) for patient assistance programs.</p>
						<p>Prescription Hope is a service-based medication advocacy service that assists individuals in enrolling with the applicable pharmaceutical company patient assistance programs.</p>
						<p><strong><i>Please contact us immediately</i></strong> with any change of address, phone number, medication, dosages or doctor; this is all information that we need to continue successfully ordering your medication(s).</p>
						<p>Remember, we are advocating on your behalf. If you or your doctor receive any documentation or phone calls from a pharmaceutical company, please contact us right away so we can provide any information they may need.</p>
					</td>
				</tr>
				<tr id="print_box">
					<td colspan="3" align="center">
						<a href="javascript:void();" onclick="javascript:takePrint();">Print</a>&nbsp;
						<a href="<?= $basepath?>/billing.php">Back</a>
					</td>
				</tr>
			</table>
		</div>
		<script>
			function takePrint(){
				document.getElementById('print_box').style = 'display:none';
				window.print();
				document.getElementById('print_box').style = '';
				return false;
			}
		</script>
	<style>
	.invoice-box {
		max-width: 800px;
		margin: auto;
		padding: 10px 10px;
		border: 1px solid #eee;
		box-shadow: 0 0 10px rgba(0, 0, 0, .15);
		font-size: 16px;
		line-height: 24px;
		font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
		color: #555;
	}
	
	.invoice-box table {
		width: 100%;
		line-height: inherit;
		text-align: left;
	}
	
	.invoice-box table td {
		padding: 10px 5px 10px;
		vertical-align: top;
	}
	
	.invoice-box table tr td:nth-child(n+2) {
		text-align: right;
	}
	
	.invoice-box table tr.information table td {
		padding-bottom: 40px;
	}
	
	.invoice-box table tr.heading td {
		background: #eee;
		border-bottom: 1px solid #ddd;
		font-weight: 500;
		padding: 10px 8px;
		text-transform: uppercase;
	}
	
	.invoice-box table tr.details td {
		padding-bottom: 20px;
	}
	
	.invoice-box table tr.item td {
		border-bottom: 1px solid #eee;
	}
	
	.invoice-box table tr.item.last td {
		border-bottom: none;
	}
	
	.invoice-box table tr.item input {
		padding-left: 5px;
	}
	
	.invoice-box table tr.item td:first-child input {
		margin-left: -5px;
		width: 100%;
	}
	
	.invoice-box table tr.total td:nth-child(2) {
		border-top: 2px solid #eee;
		font-weight: bold;
	}
	
	.invoice-box input[type=number] {
		width: 60px;
	}
	
	@media only screen and (max-width: 600px) {
		.invoice-box table tr.top table td {
			width: 100%;
			display: block;
			text-align: center;
		}
		.invoice-box table tr.information table td {
			width: 100%;
			display: block;
			text-align: center;
		}
	}
	/** RTL **/
	
	.rtl {
		direction: rtl;
		font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
	}
	
	.rtl table {
		text-align: right;
	}
	
	.rtl table tr td:nth-child(2) {
		text-align: left;
	}
	
	.title small {
		font-size: 12px;
	}
	
	td h3 {
		margin: 0px 0 4px;
	}
	
	td.address {
		padding: 0px;
		font-size: 14px;
		line-height: 20px;
	}
	
	td.address {
		padding: 0 10px !important;
	}
	
	td.title {
		padding: 0px !important;
		vertical-align: middle;
	}
	
	td h3 {
		margin: 0px 0 4px;
		font-size: 18px;
		font-weight: 600 !important;
	}
	</style>
	</body>
	</html>
<?php
die();
}
?>

<?php include('_header.php'); ?>
<style>
.content{background:transparent;}
body{padding-top:0px;}
nav.navbar.navbar-default.navbar-fixed-top.header-nav {position: static !important;}
.modal{z-index:99999;}
</style>
<div class="content topContent medication_main">
	
	<div class="container twoColumnContainerNo">

	<div class="row no-marginNo">

		<div class="col-sm-12 leftIconBox" style="padding-top:15px;">
		
		
		<!-- .navbar -->
	<?php include('_header_nav.php'); ?> 
	
	<?php if($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback) { ?>
	<div class="row account_summary_section" id="stopLinkMsg">
		<div class="col-md-12 alert alert-warning alert-dismissible">
			<a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a>
			<p>Your account balance is past due. Please <a href="billing.php">click here</a> to correct this.</p>						
		</div>
	</div>
	<?php } ?>
				
<div class="Bill-section">
<div class="information_details">
  <ul id="myTabs" class="nav nav-pills nav-justified" role="tablist" data-tabs="tabs">
    <li id="bill_info" class="active"><a href="#Commentary" data-toggle="tab">Invoices</a></li>
	<!--<li><a href="#" data-toggle="tab">Payment Info</a></li>-->
    <li id="pymt_info"><a href="#Payment_info" data-toggle="tab">Payment Info</a></li>

  </ul>
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane fade in active" id="Commentary">
		<?php
		if(count($meds_for_invoices->approved_invoices) > 0)
		{
			foreach($meds_for_invoices->approved_invoices as $meds_details)
			{
				/*echo "<pre>";
				print_r($meds_details);
				echo "</pre>";*/
			?>
			<div class="bill-details">
				<div class="heading-title">
					<span class="date-details"><?php echo $meds_details->InvoiceDate;?><br/>
						<?php
						if($meds_details->meds[0]->LineDescription != "Service Fee")
						{
						?>
							<small id="med_count">
							<?php $totalMeds = count($meds_details->meds); echo count($meds_details->meds);?> Medication<?php echo ($totalMeds>1) ? 's' : '';?>
							</small>
						<?php
						}
						?>
					</span>
					<?php
					if($meds_details->InvoiceStatus == "A")
					{
					?>
						<span class="invoice-print"><a href="<?php echo $actual_link.'?inv_id='.base64_encode($meds_details->InvoiceID);?>"> <img src="<?= $basepath?>/images/Medication/invoice.png"> View or Print Invoice</a></span>
					<?php
					}
					else
					{
					?>
						<span class="invoice-print">Declined</span>
					<?php	
					}
					?>
				</div>
				<?php				
				foreach($meds_details->meds as $medications)
				{
				?>
				<div class="tablet-deatils">
					<span class="details_d_l"><?php echo $medications->LineDescription;?></span>
					<span class="details_d_r">$ <?php echo $medications->LineAmount;?></span>
				</div>
				<?php
				}
				?>
				<div class="tablet-deatils">
					<span class="details_d_l">Total</span>
					<span class="details_d_r">$ <?php echo $meds_details->InvoiceTotal;?></span>
				</div>
				<div class="tablet-deatils">
					<p class="heading_v">Payment Info</p>
					<span class="card-image"><img src="<?= $basepath?>/images/Medication/<?php echo ($meds_details->InvoiceCCType!='')? strtolower(str_replace(' ','',$meds_details->InvoiceCCType)):'card';?>.png"></span>
					<span class="details_d_l">
						<!--<span class="card-title"> Credit Card •••• 9441<small class="dates">July 7, 2019</small></span>-->
						<span class="card-title"><?php echo $meds_details->InvoicePaymentMethod; //$meds_details->InvoiceCCType for card type image display?></span>
					</span>
					<span class="details_d_r">$ <?php echo $meds_details->InvoiceTotal;?></span>
				</div>
			</div>
			<?php
			}
		}
		else
		{
		?>
			<div class="bill-details">
				<div class="heading-title no-bills">
					No Bill Information
				</div>
			</div>	
		<?php
		} 
		?>
	</div>
    <div role="tabpanel" class="tab-pane fade" id="Payment_info">
	<div class="Regular_pay">
		<span class="title">Regular Payment</span>
		<?php if ($_SESSION['PLP']['patient']->DateDisenrolled == '') {  ?>
		<button id="change_payment_info" type="button" class="btn btn-secondary" data-toggle="modal" data-target="#edit_payment_info" onclick="editBillingInformation()">CHANGE PAYMENT INFO</button>
		<?php } ?>
	</div>	
	<div class="bill-details">
	<div class="heading-title"><span class="date-details"> <span class="card-image"><img src="<?= $basepath?>/images/Medication/<?php echo (isset($meds_for_invoices->cc_type)) ? strtolower(str_replace(' ', '', $meds_for_invoices->cc_type)) : 'card'; ?>.png"></span> Credit Card</small></span><span class="default"> Default</span><span class="invoice-print hideme"><a href="#"> <img src="<?= $basepath?>/images/Medication/delete.png"> Remove</a></span>
	<div class="tablet-deatils pv-10 "><span class="details_d_lr">**** **** **** <?php echo $meds_for_invoices->cc_number?></span></div></div>

<div class="tablet-deatils"><span class="details_d_l">Expiration Date <?php echo rtrim($meds_for_invoices->cc_expdate,')')?>
</span></div>
<div class="tablet-deatils">

<span class="details_d_l"><?php echo $_SESSION['PLP']['patient']->PatientFirstName?><br/>
<?=$_SESSION['PLP']['patient']->PatientAddress1?>, <?=$_SESSION['PLP']['patient']->PatientCity_1?>,<br/>
<?=$_SESSION['PLP']['patient']->PatientState_1?> <?=$_SESSION['PLP']['patient']->PatientZip_1?></div>	
	</div></div>

  </div>
</div>

</div>

<div class="payment-details-view">
<div class="bill-details">
<p class="heading_v">Default Payment Info</p>
	<div class="tablet-deatils">
		<span class="card-image"><img src="<?= $basepath?>/images/Medication/<?php echo (isset($meds_for_invoices->cc_type)) ? strtolower(str_replace(' ', '', $meds_for_invoices->cc_type)) : 'card'; ?>.png"></span>
		<span class="details_d_l">
			<span class="card-title"><?php echo $meds_for_invoices->current_cc; // $meds_for_invoices->cc_type for card type image display?></span>
		</span>
		<?php if ($_SESSION['PLP']['patient']->DateDisenrolled == '') {  ?>
		<span class="details_d_r"><a href="#" data-toggle="modal" data-target="#edit_payment_info" onclick="editBillingInformation()"><img src="<?= $basepath?>/images/Medication/edit.png"> Edit </a></span>
		<?php } ?>
	</div>
</div>


</div>


<!--Edit Payment Info-->
<div class="modal fade" id="edit_payment_info" tabindex="-1" role="dialog" aria-labelledby="edit_payment_info" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="card-payment">Edit Billing Information</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="overlay_form-payment">
					<div class="row zeroed">
						<div class="col-sm-12">
							<input type="text" class="autowidth numeric" name="cc_number" id="cc_number" placeholder="Card Number *" maxlength="16"/>
						</div>
						<div class="col-sm-6 hideme">
							<select name="cc_type" id="cc_type" class="autowidth form-control login-portal">
								<option value="">Select Credit Card Type *</option>
								<option value="A">americanexpress</option>
								<option value="D">discover</option>
								<option value="M">mastercard</option>
								<option value="V">visa</option>
							</select>
						</div>
						<div class="col-sm-12"></div>
					</div>
					
					<div class="row zeroed">
						<div class="col-sm-6">
							<input type="text" class="autowidth numeric exp_date" name="cc_exp" id="cc_exp" placeholder="Expiration Date *" maxlength="5"/>
						</div>
						<div class="col-sm-6">
							<input type="text" class="autowidth numeric" name="cc_cvv" id="cc_cvv" placeholder="CVV *" maxlength="4"/>
						</div>
						<div class="col-sm-12"></div>
					</div>
					
					<div class="row zeroed">
						<div class="col-sm-12">
							<h4 class="title-sec"><b>Billing To</b></h4>
							<input type="text" class="autowidth" placeholder="First Name" id="PatientFirstNameBilling"/>
						</div>
					</div>
					
					<div class="row zeroed">
						<div class="col-sm-12">
							<input type="text" class="autowidth" placeholder="Last Name" id="PatientLastNameBilling"/>
						</div>
					</div>
					
					<div class="row zeroed">
						<div class="col-sm-12">
							<input type="text" class="autowidth" id="PatientAddress1Billing" placeholder="Address Line 1"/>
						</div>
					</div>
					<div class="row zeroed">
						<div class="col-sm-12">
							<input type="text" class="autowidth" id="PatientAddress2Billing" placeholder="Address Line 2"/>
						</div>
					</div>
					<div class="row zeroed">
						<div class="col-sm-8">
							<input type="text" class="autowidth" id="PatientCity_1Billing" placeholder="City"/>
						</div>
						<div class="col-sm-4">
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
					</div>
					<div class="row zeroed">
						<div class="col-sm-12">
							<input type="text" class="autowidth numeric" id="PatientZip_1Billing" maxlength="5" placeholder="Zipcode"/>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" onclick="saveBillingDetails();">Save Payment Info</button>
			</div>
		</div>
	</div>
</div>
<!--Edit Payment Info-->

<!--Model-->
<?php /* ?>
<div class="modal fade" id="card-payment" tabindex="-1" role="dialog" aria-labelledby="card-payment" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="card-payment">Add New Regular Payment Info</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       	<div class="overlay_form-payment">
			<center>
			<h3><b>
				<?php if ($billing_information->chargeback) { ?>
					Please update your payment information
				<?php } else { ?>
					
				<?php } ?>
			</b></h3>
			</center>

			<br>

			<div class="row zeroed">
				<div class="col-sm-12">
					
					<input type="text" class="autowidth numeric" id="cc_number" placeholder="Card Number" maxlength="16"/>
				</div>
				<!-- Material unchecked -->
				<div class="col-sm-12">
<label class="check-sce">This is an HSA or FSA card.
  <input type="checkbox" checked="checked">
  <span class="checkmark"></span>
</label></div>
<div class="col-sm-12">		
				
		
</div>
			</div>

			<div class="row zeroed">
				<div class="col-sm-12">
					<h4 class="title-sec"><b>Billing TO</b></h4>
					<input type="text" class="autowidth" placeholder="First Name" id="PatientFirstNameBilling"/>
				</div>
				
			</div>

			<div class="row zeroed">
				<div class="col-sm-12">
				
					<input type="text" class="autowidth" id="PatientAddress1Billing" placeholder="Address Line 1"/>
				</div></div>
				<div class="row zeroed">
				<div class="col-sm-12">
			
					<input type="text" class="autowidth" id="PatientAddress2Billing" placeholder="Address Line 2"/>
				</div>
			</div>

			<div class="row zeroed">
				<div class="col-sm-8">
				
					<input type="text" class="autowidth" id="PatientCity_1Billing" placeholder="City"/>
				</div>
				<div class="col-sm-4">
				

					<select name="PatientState_1Billing" id="PatientState_1Billing" class="autowidth form-control login-portal">
						<option value="AL">FL</option>
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
				</div>
				<div class="row zeroed">
				<div class="col-sm-12">
				
					<input type="text" class="autowidth numeric" id="PatientZip_1Billing" maxlength="5" placeholder="Zipcode"/>
				</div>
			</div>

		</div>
      </div>
      <div class="modal-footer">
       
        <button type="button" class="btn btn-primary">Save Payment Info</button>
      </div>
    </div>
  </div>
</div>
<?php */ ?>

		<?php if ($success !== false) { ?>
			<div id="fmMsg" class="bold">You're new information was saved successfully.<br/><br/><br></div>
		<?php } ?>

		
	</div>
</div>
</div>

<div id="overlay">
	<div id="overlay_holder"></div>
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
					<div class="col-xs-12 col-lg-7 alignRight"><input type="text" class="date alignRight" id="schedule_date" data-toggle="datepicker" readonly="readonly"/></div>
				</div>
			</div>
			<div class="text-center btn-payment">
				<span id="btnSaveScheduledPayment"><a href="javascript:saveScheduledPayment();" class="big-button whiteButton">Schedule Payment</a></span>
				<span id="btnSavingScheduledPayment" style="display:none;"><a href="javascript:void(0);" class="big-button whiteButton">Saving...</a></span>
				<a href="javascript:closeOverlay();" id="scheduleCancelLink" class="small-button">Go Back</a>
			</div>
		</div>		
	</div>
	<script type="text/javascript">
jQuery(document).ready(function(){

	var NewDate = new Date();
	dt = new Date();
	jQuery( "#schedule_date" ).datepicker({
		startDate: new Date(),
		endDate: new Date(dt.setMonth(dt.getMonth() + 2)),
		autoHide: true
	});
});
</script>
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

<script type="text/javascript">
jQuery(document).ready(function(){
	
	<?php if ($billing_information->chargeback) { ?>
		showEditCC();
		forceShowEditCC = 1;
	<?php } ?>

	//force users to pay their due invoices
	<?php if ($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback) { ?>
		showOverdueInvoices();
		forceShowOverdueInvoices = 1;
	<?php } ?>
	
	jQuery('input#cc_number').keyup(function(){
		jQuery('select#cc_type option').prop('selected', false);
		var payCardType = detectCardType(jQuery(this).val());
		jQuery('.cc_number_type').remove();
		if(payCardType!=''){
			jQuery('select#cc_type option:contains('+payCardType+')').prop('selected', true);
			jQuery('<div class="cc_number_type"><img src="<?= $basepath?>/images/Medication/'+payCardType.toLowerCase()+'.png"></div>').insertAfter(jQuery(this));
		}		
	});
});
//window.onscroll = function() {myFunction()};
//
//var header = document.getElementById("stciky");
//var sticky = header.offsetTop;
//
//function myFunction() {
//  if (window.pageYOffset > sticky) {
//    header.classList.add("sticky");
//  } else {
//    header.classList.remove("sticky");
//  }
//}
</script>
<?php include('_footer.php'); ?>