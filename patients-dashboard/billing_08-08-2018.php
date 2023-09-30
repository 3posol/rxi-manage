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
	'command'		=> 'get_billing',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$billing_information = api_command($data);

//get meds/providers data

$data = array(
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);

$success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT, array('options' => array('default' => false)));

//active meds
$active_meds = 0;
foreach ($rxi_data->meds as $med) {
	if ($med->MedAssistDetailID > 0) {
		$active_meds++;
	}
}

?>

<?php include('_header.php'); ?>
<style>
.content{background:transparent;}
body{padding-top:0px;}
nav.navbar.navbar-default.navbar-fixed-top.header-nav {
    position: static !important;
}</style>
<div class="content topContent medication_main">
	
	<div class="container twoColumnContainerNo">

	<div class="row no-marginNo">

		<div class="col-sm-12 leftIconBox" style="padding-top:15px;">
		
		
		<!-- .navbar -->
	<?php include('_header_nav.php'); ?>
<div class="Bill-section">
<div class="information_details">
  <ul id="myTabs" class="nav nav-pills nav-justified" role="tablist" data-tabs="tabs">
    <li class="active"><a href="#Commentary" data-toggle="tab">Bills</a></li>
    <li><a href="#Payment_info" data-toggle="tab">Payment Info</a></li>
    
  </ul>
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane fade in active" id="Commentary"><div class="bill-details">
	<div class="heading-title"><span class="date-details">July 16, 2019 Delivery<br/><small>1 Medications</small></span><span class="invoice-print"><a href="#"> <img src="/html/patients-dashboard-new/images/Medication/invoice.png"> View or Print Invoice</a></span></div>
	<div class="tablet-deatils"><span class="details_d_l">CALCIUM-VITAMIN D3 600 MG/400 IU TAB   Qty 60.0
</span><span class="details_d_r">$4.41</span></div>
<div class="tablet-deatils"><span class="details_d_l">Total
</span><span class="details_d_r">$4.41</span></div>
<div class="tablet-deatils">
<p class="heading_v">Payment Info</p>
<span class="card-image"><img src="/html/patients-dashboard-new/images/Medication/card.png"></span><span class="details_d_l"><span class="card-title"> Credit Card •••• 9441<small class="dates">July 7, 2019</small></span>



</span><span class="details_d_r">$4.41</span></div>
	
	</div></div>
    <div role="tabpanel" class="tab-pane fade" id="Payment_info">
	<div class="Regular_pay"><span class="title">Regular Payment</span>  <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#card-payment">ADD PAYMENT INFO</button></div>	
	<div class="bill-details">
	<div class="heading-title"><span class="date-details"> <span class="card-image"><img src="/html/patients-dashboard-new/images/Medication/card.png"></span> Credit Card</small></span><span class="default"> Default</span><span class="invoice-print"><a href="#"> <img src="/html/patients-dashboard-new/images/Medication/delete.png"> Remove</a></span>
	<div class="tablet-deatils pv-10 "><span class="details_d_lr">**** **** **** 9441
</span></div></div>

<div class="tablet-deatils"><span class="details_d_l">Valid Thru 11/2019
</span></div>
<div class="tablet-deatils">

<span class="details_d_l">Tina<br/>
223 E. Concord Street, Orlando,<br/>
FL 32801 - Orlando Sentinel</div>
	
	</div></div>

  </div>
</div>

</div>

<div class="payment-details-view">
<div class="bill-details">
	
<p class="heading_v">Default Payment Info</p>
<div class="tablet-deatils">
<span class="card-image"><img src="/html/patients-dashboard-new/images/Medication/card.png"></span><span class="details_d_l"><span class="card-title"> Credit Card •••• 9441</span>



</span><span class="details_d_r"><a href=""><img src="/html/patients-dashboard-new/images/Medication/edit.png"> Edit </a></span></div>
	
	</div>


</div>
<!--Model-->
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
					<input type="text" class="autowidth" placeholder="Tina" id="PatientFirstNameBilling"/>
				</div>
				
			</div>

			<div class="row zeroed">
				<div class="col-sm-12">
				
					<input type="text" class="autowidth" id="PatientAddress1Billing" placeholder="223 E. Concord Street, Orlando,"/>
				</div></div>
				<div class="row zeroed">
				<div class="col-sm-12">
			
					<input type="text" class="autowidth" id="PatientAddress2Billing" placeholder="Address Line 2"/>
				</div>
			</div>

			<div class="row zeroed">
				<div class="col-sm-8">
				
					<input type="text" class="autowidth" id="PatientCity_1Billing"placeholder="Orlando Sentinel"/>
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
				
					<input type="text" class="autowidth numeric" id="PatientZip_1Billing" maxlength="5" placeholder="34994-2341"/>
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

		<?php if ($success !== false) { ?>
			<div id="fmMsg" class="bold">You're new information was saved successfully.<br/><br/><br></div>
		<?php } ?>

		
	</div>
</div>
</div>



<script>
window.onscroll = function() {myFunction()};

var header = document.getElementById("stciky");
var sticky = header.offsetTop;

function myFunction() {
  if (window.pageYOffset > sticky) {
    header.classList.add("sticky");
  } else {
    header.classList.remove("sticky");
  }
}
</script>
<?php include('_footer.php'); ?>