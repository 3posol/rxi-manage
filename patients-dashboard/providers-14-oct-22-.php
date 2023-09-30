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
$success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT, array('options' => array('default' => false)));

$data = array(
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$rxi_data = api_command($data);

//add extra providers
$options_html = '';
$medToDoc = array();

foreach ($rxi_data->meds as $med) {
	if ($med->ProviderId == -1) {
		//add new provider to the list
		$rxi_data->providers[] = (object) array(
											"PrvProviderId"			=> $med->MedAssistDetailID,
											"PrvPrefix"				=> (isset($med->PrvPrefix)) ? $med->PrvPrefix : 'Dr.',
											"PrvFirstName"			=> $med->PrvFirstName,
											"PrvMiddleInitial"		=> (isset($med->PrvMiddleInitial)) ? $med->PrvMiddleInitial : '',
											"PrvLastName"			=> $med->PrvLastName,
											"PrvPracticeName"		=> (isset($med->PrvPracticeName)) ? $med->PrvPracticeName : '',
											"PrvProfDesignation"	=> (isset($med->PrvProfDesignation)) ? $med->PrvProfDesignation : '',
											"PrvAddress1"			=> $med->PrvAddress1,
											"PrvAddress2"			=> $med->PrvAddress2,
											"PrvCity"				=> $med->PrvCity,
											"PrvState"				=> $med->PrvState,
											"PrvZip"				=> $med->PrvZip,
											"PrvWorkPhone"			=> $med->PrvWorkPhone,
											"PrvFaxNumber"			=> $med->PrvFaxNumber,
											"PrvForFuture"			=> $med->PrvForFuture,
											"request_type"			=> 'new'
		);
	}
	$medToDoc[$med->ProviderId][] = $med->MedAssistDetailID;
	
	// dont add unapproved request
	if($med->ProviderId != -1 && $med->DrugAppliedFor!='' && $med->approval_status!=2){
		$options_html .= '<option data-mname="'.$med->DrugAppliedFor.'" value="'.$med->MedAssistDetailID.'_'.$med->ProviderId.'">'.$med->DrugAppliedFor.'('.$med->Dosage.' - '.$med->Directions.')</option>';
	}
	
}

$medToDocJson = (count($medToDoc)>0) ? json_encode($medToDoc) : '';
?>

<?php include('_header.php'); ?>

<style>
.popup .zeroed{margin-bottom:20px;}
.provdesc-box{padding-bottom:20px;}
.tooltipBox{width: 600px;}
.content{background:transparent;}
body{padding-top:0px;}
nav.navbar.navbar-default.navbar-fixed-top.header-nav {position: static !important;}

ul.prov_med_list li {list-style: none;border-bottom: 1px solid #dedede;padding: 10px 0px; width: 100%;float: left;}
ul.prov_med_list li div {float: left;}
ul.prov_med_list {
    padding: 0px 0 0 20px;
    padding-bottom: 20px !important;
    float: left;
    width: 100%;
}
.ui-autocomplete {z-index: 5000;height: 200px;overflow: hidden;overflow-y: scroll;}
.loading-fname,.loading-lname { background-color: #ffffff; background-image: url("/patients-dashboard/images/loader.gif");background-size: 25px 25px;background-position:right center;background-repeat: no-repeat;}
</style>

<div class="content topContent medication_main provider-section provider-new">

	<div class="container twoColumnContainerNo">
	
		<div class="row no-marginNo">
		
			<div class="col-sm-12 leftIconBox" style="padding-top:15px;">
				<!-- .navbar -->
				<?php include('_header_nav.php'); ?>
				<div class=" medication-section-title" >
					<div class="row"></div>
				</div>
				<div class="medication-section maintab" id="tables_main">
					<div class="row">
						<div class="col-sm-12">
							<div class="information_details">
								<ul id="myTabss" class="nav nav-pills" role="tablist" data-tabs="tabs">
									<li><a href="<?= $basepath?>/medication.php"  class="panel-heading">Prescription</a></li>
									<li class="active"><a href="#" class="panel-heading">Healthcare Provider</a></li>
								</ul>
								<div class="tab-content">
									<div role="tabpanel" class="tab-pane fade in active" id="Prescription">
										<div class="panel panel-default panel-info Profile">						
											<div class="panel-body">
												<div class="list-details">
													<div class="table-wrap">
														<table class="table">
															<thead><tr><th></th><th>Provider</th><th></th><th>No. of Meds</th></tr></thead>
															<tbody>
															<?php
															$firstProv = 1;															
															foreach ($rxi_data->providers as $key => $provider) { 
																$arrProviderMeds = array();
																foreach($rxi_data->meds as $med){
																	// if med is empty; continue the loop
																	if($med->DrugAppliedFor==''){
																		continue;
																	}
																	if (($provider->PrvForFuture=='false' || $provider->PrvForFuture==0) && (($med->ProviderId == $provider->PrvProviderId) || ($med->ProviderId == -1 && $med->MedAssistDetailID == $provider->PrvProviderId))){
																		$arrProviderMeds[] = $med;																		
																	}
																	else if (($provider->PrvForFuture=='true' || $provider->PrvForFuture==1) && $med->MedAssistDetailID>0 && $med->ProviderId == $provider->PrvProviderId && $med->PrvForFuture=='false'){
																		$arrProviderMeds[] = $med;
																	}
																} ?>
																<tr class="prov_item" id="prov<?=$provider->PrvProviderId?>">
																	<td></td>
																	<td id="<?=$provider->PrvProviderId?>" data-meds="<?=count($arrProviderMeds)?>"><?=((isset($provider->PrvPrefix)) ? $provider->PrvPrefix : 'Dr.')?> <?=$provider->PrvFirstName?> <?=((isset($provider->PrvMiddleInitial) && $provider->PrvMiddleInitial != '') ? $provider->PrvMiddleInitial . '.' : '') ?> <?=$provider->PrvLastName?> <?=((isset($provider->PrvProfDesignation)) ? $provider->PrvProfDesignation : '')?> </td>
																	<td id="view<?=$provider->PrvProviderId?>"><a href="javascript:void(0);" class="view-medi <?php if($firstProv==1){ echo 'firstprov'; }?>"><i class="fa fa-eye"></i> <span>View Details</span></a></td>
																	<td>
																	<?php
																	if( ($provider->PrvForFuture=='true' || $provider->PrvForFuture==1) && count($arrProviderMeds)<1 ) {
																		echo '<div class="saved_for_future">You\'ve saved this provider for future use, there are no medications associated with this provider currently.</div>';
																	} else {
																		echo count($arrProviderMeds)?> Medication<?php if (count($arrProviderMeds)>1) echo 's';
																	}?>
																	</td>
																</tr>
																<?php $firstProv++; } ?>
															</tbody>
														</table>
													</div>
												</div>										
											</div> 
										</div> 
									</div>
								</div>
							</div>
						</div> 
					</div> 
				</div>
				
				
				<div class="table-wrap-desc">
					<div class="medication_v1">
						<?php
						foreach ($rxi_data->providers as $key => $provider) {
							$arrProviderMeds = array();
							foreach($rxi_data->meds as $med){
								/*if (($provider->PrvForFuture=='false' || $provider->PrvForFuture==0) && ($med->ProviderId == $provider->PrvProviderId || ($med->ProviderId == -1 && $med->MedAssistDetailID == $provider->PrvProviderId))){
									$arrProviderMeds[] = $med;
								}*/
								// if med is empty; continue the loop
								if($med->DrugAppliedFor==''){
									continue;
								}
								if (($provider->PrvForFuture=='false' || $provider->PrvForFuture==0) && (($med->ProviderId == $provider->PrvProviderId) || ($med->ProviderId == -1 && $med->MedAssistDetailID == $provider->PrvProviderId))){
									$arrProviderMeds[] = $med;																		
								}
								else if (($provider->PrvForFuture=='true' || $provider->PrvForFuture==1) && $med->MedAssistDetailID>0 && $med->ProviderId == $provider->PrvProviderId && $med->PrvForFuture=='false'){
									$arrProviderMeds[] = $med;
								}
							} ?>
						<div class="panel panel-default panel-info Profile provdesc-box" id="provdesc<?=$provider->PrvProviderId?>" style="display: none;">
							<div class="panel-heading">
								<span class="pull-left" data-dismiss1="alert" id="close_d_v">									
									<a <?php if($provider->request_type=='new') { echo 'style="display:none;"';}?> href="javascript:editProvider('<?=$provider->PrvProviderId?>');"><img src="<?= $basepath?>/images/Medication/edit.png"> Edit </a>
								</span>
								<span class="pull-right close_desc" data-id="provdesc<?=$provider->PrvProviderId?>" data-dismiss1="alert"><img src="<?= $basepath?>/images/Medication/close.png"> Close</span>			
							</div>
							<div class="panel-body">
								<div class="dir_v-s">
									<p>
										<strong><?=((isset($provider->PrvPrefix)) ? $provider->PrvPrefix : 'Dr.')?> <?=$provider->PrvFirstName?> <?=((isset($provider->PrvMiddleInitial) && $provider->PrvMiddleInitial != '') ? $provider->PrvMiddleInitial . '.' : '') ?> <?=$provider->PrvLastName?> <?=((isset($provider->PrvProfDesignation)) ? $provider->PrvProfDesignation : '')?></strong>
										<br/>
										<small class="sub_heading">
											<?=$provider->PrvPracticeName?><br/>
											<?=$provider->PrvAddress1?><?=(($provider->PrvAddress2 != '' && strpos($provider->PrvAddress1, $provider->PrvAddress2) === false) ? ', ' . $provider->PrvAddress2: '') ?>
											<br/><?=$provider->PrvCity?>, <?=$provider->PrvState?> <?=$provider->PrvZip?>
											<br/>
											<span class="phonelink"><i class="fa fa-phone"></i> <a href="tel:<?php echo str_replace('-', '', $provider->PrvWorkPhone)?>"><?=$provider->PrvWorkPhone?></a></span>
											<br/>
											<div class="phonelink" style="padding-top:5px;"><i class="fa fa-fax"></i> <?=$provider->PrvFaxNumber?></div>
										</small>
									</p>
								</div>
								<div class="dir_v-s">
									<p><small class="main_heading">Prescribed Medication(s)</small></p><br/>
									<?php				
									
									if( ($provider->PrvForFuture=='true' || $provider->PrvForFuture==1) && count($arrProviderMeds)<1){ //$arrProviderMeds = array();
										echo '<div class="saved_for_future">You\'ve saved this provider for future use, there are no medications associated with this provider currently.</div>'; }
									
									if(count($arrProviderMeds)>0) {
										echo '<ul class="prov_med_list">
												<li class="row">
													<div class="col-sm-4 col-md-4">Name</div>
													<div class="col-sm-4 col-md-4">Dosage</div>
													<div class="col-sm-4 col-md-4">Directions</div>
												</li>';
										foreach ($arrProviderMeds as $providerMed){
											?>
											<li id="med<?=$providerMed->MedAssistDetailID?>" class="row">
												<div class="col-sm-4 col-md-4">
													<span class="medname"><?=$providerMed->DrugAppliedFor?></span>
												</div>
												<div class="col-sm-4 col-sm-4">
													<span class="meddose"><?=$providerMed->Dosage?></span>
												</div>
												<div class="col-sm-4 col-sm-3">
													<span class="meddir"><?=$providerMed->Directions?></span>
												</div>
												<?php if($providerMed->approval_status==1 || $provider->request_type!='new') { ?>
												<a class="contentHeaderLink" href="javascript:editMedication('<?=$providerMed->MedAssistDetailID?>');"><img src="<?= $basepath?>/images/Medication/edit.png"> &nbsp;</a>
												<?php } ?>
											</li>
											<?php
										}
										echo '</ul>';
									} else {
										echo '<p>No Medication is associated with this provider</p>';
									}?>
								</div>
							</div>
						</div> 
						<?php } ?>													
					</div>
				</div>
			</div>
		</div>
		<a href="javascript:addProvider();" class="addGreyBtn add_med"><span><img src="<?= $basepath?>/images/Medication/add-circular-outlined-button.png"></span> New Provider</a>
	</div>
</div>

</div>

<div class="clear"></div>
<br/><br/>

</div>
</div>

<div id="overlay" class="popup">
<div id="overlay_holder">
</div>
</div>


<!--<div id="overlay">
	<div id="overlay_holder">
	</div>
</div>-->
<div id="overlay_add_medication" class="modal fade" role="dialog">
  <div class="modal-dialog1">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
     <a href="javascript:closeOverlay();"><button type="button" class="close" data-dismiss="popup" aria-label="Close"><span aria-hidden="true">×</span></button></a>
        <h4 class="modal-title"><span id="medication_modal_title">Add Medication</span></h4>
      </div>
		<div class="overlay_form" style="margin-top:0px;margin-bottom:0px;">
		

			  <div class="modal-body">
<script>
  $( function() {
    $( "#PrvFirstName" ).autocomplete({
      minLength: 0,
     // source: "testajax.php",
      source: function( request, response ) {
      	var patient_id = "<?php echo $_SESSION['PLP']['patient']->PatientID;?>";
      	if($("#PrvFirstName").val() =='' || jQuery("#PrvFirstName").val().length <= 2){
      		$(".ui-autocomplete").hide();
      	}
      if($("#PrvFirstName").val() !='' && $("#PrvFirstName").val().length > 2){
				$.ajax({
				    url: "ajax_get_doctors_list.php",
				    dataType: "json",
				    data: {
				        id: patient_id,
				        sortType: 'FirstName',
				        PrvFirstName: $("#PrvFirstName").val()
				    },
				    beforeSend : function() {
						$("#PrvFirstName").removeClass('correct');
						$("#PrvFirstName").addClass('loading-fname');

						//jQuery("#msg").fadeOut();
					},
				    success: function (data) {
				    	//console.log(data);
				    	if(data.success == 0){
				    			$("#PrvFirstName").removeClass('loading-fname');
				    			jQuery(".ui-autocomplete").hide();
				    			
				    		}else{
					        response($.map(data.doctors, function (item) {
					        	console.log(item);
					            return {
					            	id: item.PrvProviderId,
					                PrvFirstName: item.PrvFirstName,
					                PrvLastName:item.PrvLastName,
					                PrvPracticeName:item.PrvPracticeName,
					                PrvAddress1:item.PrvAddress1,
					                PrvAddress2:item.PrvAddress2,
					                PrvCity:item.PrvCity,
					                PrvState:item.PrvState,
					                PrvZip:item.PrvZip,
					                PrvWorkPhone:item.PrvWorkPhone,
					                PrvFaxNumber:item.PrvFaxNumber,
					                PrvProviderId:item.PrvProviderId
					                
					            };
					        }));
					    }
					    }
				});
			}else{
				$("#PrvFirstName").removeClass('loading-fname');
			}
      },
      focus: function( event, ui ) {
        $( "#PrvFirstName" ).val( ui.item.PrvFirstName );
        return false;
      },
      select: function( event, ui ) {
      //	console.log(ui.item.PrvProviderId);
      	$( "#PrvFirstName" ).val( ui.item.PrvFirstName );
        $( "#PrvLastName" ).val( ui.item.PrvLastName );
        $( "#PrvPracticeName" ).val( ui.item.PrvPracticeName );
        $( "#PrvAddress1" ).val( ui.item.PrvAddress1 );
        $( "#PrvAddress2" ).val( ui.item.PrvAddress2 );
        $( "#PrvCity" ).val( ui.item.PrvCity );
        $('#PrvState').val(ui.item.PrvState);
        $( "#PrvZip" ).val( ui.item.PrvZip );
        $( "#PrvWorkPhone" ).val( ui.item.PrvWorkPhone );
        $( "#PrvFaxNumber" ).val( ui.item.PrvFaxNumber );
        //$( "#hiddenPrvProviderId" ).val( ui.item.PrvProviderId );
        //hiddenPrvProviderId
 		//$("#checkData").val(ui.item.PrvFirstName+ui.item.PrvLastName+ui.item.PrvAddress1+ui.item.PrvAddress2+ui.item.PrvCity+ui.item.PrvState+ui.item.PrvZip+ui.item.PrvWorkPhone+ui.item.PrvFaxNumber);
        return false;
      }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
    	$( "#PrvFirstName" ).removeClass('loading-fname');
      return $( "<li>" )
        .append( "<div>" + item.PrvFirstName + " " + item.PrvLastName + "<br> Address:" + item.PrvAddress1+"</div>" )
        .appendTo( ul );
    };

    $( "#PrvLastName" ).autocomplete({
      minLength: 0,
     // source: "testajax.php",
      source: function( request, response ) {
      	var patient_id = "<?php echo $_SESSION['PLP']['patient']->PatientID;?>";
      	if($("#PrvLastName").val() =='' || jQuery("#PrvLastName").val().length <= 2){
      		$(".ui-autocomplete").hide();
      	}
      	if($("#PrvLastName").val() !='' && $("#PrvLastName").val().length > 2){
				$.ajax({
				    url: "ajax_get_doctors_list.php",
				    dataType: "json",
				    data: {
				        id: patient_id,
				        sortType: 'LastName',
				        PrvFirstName: $("#PrvLastName").val()
				    },
				    beforeSend : function() {
						$("#PrvLastName").removeClass('correct');
						$("#PrvLastName").addClass('loading-lname');

						//jQuery("#msg").fadeOut();
					},
				    success: function (data) {
				    	//console.log(data);
				    	if(data.success == 0){
				    			$("#PrvLastName").removeClass('loading-lname');
				    			$(".ui-autocomplete").hide();
				    			
				    		}else{
					        response($.map(data.doctors, function (item) {
					        	console.log(item);
					            return {
					            	id: item.PrvProviderId,
					                PrvFirstName: item.PrvFirstName,
					                PrvLastName:item.PrvLastName,
					                PrvPracticeName:item.PrvPracticeName,
					                PrvAddress1:item.PrvAddress1,
					                PrvAddress2:item.PrvAddress2,
					                PrvCity:item.PrvCity,
					                PrvState:item.PrvState,
					                PrvZip:item.PrvZip,
					                PrvWorkPhone:item.PrvWorkPhone,
					                PrvFaxNumber:item.PrvFaxNumber,
					                PrvProviderId:item.PrvProviderId
					                
					            };
					        }));
					    }
					    }
				});
			}else{
				$("#PrvLastName").removeClass('loading-lname');
			}
      },
      focus: function( event, ui ) {
        $( "#PrvLastName" ).val( ui.item.PrvLastName );
        return false;
      },
      select: function( event, ui ) {
      //	console.log(ui.item.PrvProviderId);
      	$( "#PrvFirstName" ).val( ui.item.PrvFirstName );
        $( "#PrvLastName" ).val( ui.item.PrvLastName );
        $( "#PrvPracticeName" ).val( ui.item.PrvPracticeName );
        $( "#PrvAddress1" ).val( ui.item.PrvAddress1 );
        $( "#PrvAddress2" ).val( ui.item.PrvAddress2 );
        $( "#PrvCity" ).val( ui.item.PrvCity );
        $('#PrvState').val(ui.item.PrvState);
        $( "#PrvZip" ).val( ui.item.PrvZip );
        $( "#PrvWorkPhone" ).val( ui.item.PrvWorkPhone );
        $( "#PrvFaxNumber" ).val( ui.item.PrvFaxNumber );
        //$( "#hiddenPrvProviderId" ).val( ui.item.PrvProviderId );
        //hiddenPrvProviderId
 		//$("#checkData").val(ui.item.PrvFirstName+ui.item.PrvLastName+ui.item.PrvAddress1+ui.item.PrvAddress2+ui.item.PrvCity+ui.item.PrvState+ui.item.PrvZip+ui.item.PrvWorkPhone+ui.item.PrvFaxNumber);
        return false;
      }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
    	$( "#PrvLastName" ).removeClass('loading-lname');
      return $( "<li>" )
        .append( "<div>" + item.PrvFirstName + " " + item.PrvLastName + "<br> Address:" + item.PrvAddress1+"</div>" )
        .appendTo( ul );
    };
  } );

  </script>
			<div class="box small yellowMedNoticeBox">
				<center>Any dosage changes or medications added to your account will be verified by your healthcare provider. If you add a medication or change a dosage, please allow 2-4 weeks from the date of the change to ensure the healthcare provider has enough time to respond to our requests and for us to place the order.</center>
			</div>

			<div class="row zeroed" id="DrugNameRow">
				<div class="col-sm-12">
					<b>Medication Name<font class="red">*</font></b><br>
					<input type="text" class="autowidth" name="DrugAppliedFor" id="DrugAppliedFor"/>
				</div>
			</div>

			<div class="row zeroed">
				<div class="col-sm-12">
					<b>Strength<font class="red">*</font></b> (10mg, 20mL, etc)<br>
					<input type="text" class="autowidth" name="Dosage" id="Dosage"/>
				</div>
			</div>

			<div class="row zeroed">
				<div class="col-sm-12">
					<b>Directions</b> (Take twice daily, take with meals, etc)<br>
					<input type="text" class="autowidth" name="Directions" id="Directions"/>
				</div>
			</div>

			<div class="row zeroed">
				<div class="col-sm-12">
					<b>Healthcare Provider<font class="red">*</font></b> (Select from list of added healthcare providers)<br>
					<select name="ProviderId" id="ProviderId" class="form-control login-portal">
						<option value="">Select a Healthcare Provider</option>
						<?php foreach ($rxi_data->providers as $provider) { ?>
							<option value="<?=$provider->PrvProviderId?>"><?=$provider->PrvFirstName?> <?=((isset($provider->PrvMiddleInitial) && $provider->PrvMiddleInitial != '') ? $provider->PrvMiddleInitial . '.' : '')?> <?=$provider->PrvLastName?></option>
						<?php } ?>
						<option value="-1">*Add A New Healthcare Provider</option>
					</select>
				</div>
			</div>

			<div id="formNewProvider">
				<div class="row zeroed">
					<div class="col-sm-4">
						<b>First Name<font class="red">*</font></b><br>
						<input type="text" class="autowidth" id="PrvFirstName"/>
					</div>
					<div class="col-sm-4">
						<b>Last Name<font class="red">*</font></b><br>
						<input type="text" class="autowidth" id="PrvLastName"/>
					</div>
					<div class="col-sm-4">
						<b>Practice Name<font class="red">*</font></b><br>
						<input type="text" class="autowidth" id="PrvPracticeName"/>
					</div>
				</div>

				<div class="row zeroed">
					<div class="col-sm-6">
						<b>Address Line 1<font class="red">*</font></b><br>
						<input type="text" class="autowidth" id="PrvAddress1" autocomplete="nope"/>
					</div>
					<div class="col-sm-6">
						<b>Address Line 2</b><br>
						<input type="text" class="autowidth" id="PrvAddress2"/>
					</div>
				</div>

				<div class="row zeroed">
					<div class="col-sm-4">
						<b>City</b><font class="red">*</font><br>
						<input type="text" class="autowidth" id="PrvCity"/>
					</div>
					<div class="col-sm-4">
						<b>State</b><font class="red">*</font><br>
						<select name="PrvState" id="PrvState" class="form-control login-portal">
							<option value="">...</option>
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
						<b>Zip</b><font class="red">*</font><br>
						<input type="text" class="autowidth numeric" id="PrvZip" maxlength="5"/>
					</div>
				</div>

				<div class="row zeroed">
					<div class="col-sm-6">
						<b>Phone Number<font class="red">*</font></b><br>
						<input type="text" class="autowidth phone_us" id="PrvWorkPhone"/>
					</div>
					<div class="col-sm-6">
						<b>Fax Number</b><br>
						<input type="text" class="autowidth phone_us" id="PrvFaxNumber"/>
					</div>
				</div>
			</div>

				<div class="modal-footer">
			<div class="text-center btn-group-popup">
			
		
			<a id="btnSaveMedication" href="javascript:saveNewMedication();" class="big-button whiteButton">Save Medication</a>
			<a id="btnSavingMedication" href="javascript:void(0);" class="big-button whiteButton" style="display:none;" enabled="false">Saving...</a>
		
		</div>
		</div>
		</div>

	

	</div>

</div>
</div>
</div>


<div id="overlay_add_provider" class="modal fade" role="dialog">
  <div class="modal-dialog1">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
     <a href="javascript:closeOverlay();"><button type="button" class="close" data-dismiss="popup" aria-label="Close"><span aria-hidden="true">×</span></button></a>
        <h4 class="modal-title"><span id="medication_modal_title"></h4>
      </div>
      <div class="modal-body">
      	<script>
  $( function() {
    $( "#PrvFirstName" ).autocomplete({
      minLength: 0,
     // source: "testajax.php",
      source: function( request, response ) {
      	var patient_id = "<?php echo $_SESSION['PLP']['patient']->PatientID;?>";
      	if($("#PrvFirstName").val() =='' || jQuery("#PrvFirstName").val().length <= 2){
      		$(".ui-autocomplete").hide();
      	}
      if($("#PrvFirstName").val() !='' && $("#PrvFirstName").val().length > 2){
				$.ajax({
				    url: "ajax_get_doctors_list.php",
				    dataType: "json",
				    data: {
				        id: patient_id,
				        sortType: 'FirstName',
				        PrvFirstName: $("#PrvFirstName").val()
				    },
				    beforeSend : function() {
						$("#PrvFirstName").removeClass('correct');
						$("#PrvFirstName").addClass('loading-fname');

						//jQuery("#msg").fadeOut();
					},
				    success: function (data) {
				    	//console.log(data);
				    	if(data.success == 0){
				    			$("#PrvFirstName").removeClass('loading-fname');
				    			jQuery(".ui-autocomplete").hide();
				    			
				    		}else{
					        response($.map(data.doctors, function (item) {
					        	console.log(item);
					            return {
					            	id: item.PrvProviderId,
					                PrvFirstName: item.PrvFirstName,
					                PrvLastName:item.PrvLastName,
					                PrvPracticeName:item.PrvPracticeName,
					                PrvAddress1:item.PrvAddress1,
					                PrvAddress2:item.PrvAddress2,
					                PrvCity:item.PrvCity,
					                PrvState:item.PrvState,
					                PrvZip:item.PrvZip,
					                PrvWorkPhone:item.PrvWorkPhone,
					                PrvFaxNumber:item.PrvFaxNumber,
					                PrvProviderId:item.PrvProviderId
					                
					            };
					        }));
					    }
					    }
				});
			}else{
				$("#PrvFirstName").removeClass('loading-fname');
			}
      },
      focus: function( event, ui ) {
        $( "#PrvFirstName" ).val( ui.item.PrvFirstName );
        return false;
      },
      select: function( event, ui ) {
      //	console.log(ui.item.PrvProviderId);
      	$( "#PrvFirstName" ).val( ui.item.PrvFirstName );
        $( "#PrvLastName" ).val( ui.item.PrvLastName );
        $( "#PrvPracticeName" ).val( ui.item.PrvPracticeName );
        $( "#PrvAddress1" ).val( ui.item.PrvAddress1 );
        $( "#PrvAddress2" ).val( ui.item.PrvAddress2 );
        $( "#PrvCity" ).val( ui.item.PrvCity );
        $('#PrvState').val(ui.item.PrvState);
        $( "#PrvZip" ).val( ui.item.PrvZip );
        $( "#PrvWorkPhone" ).val( ui.item.PrvWorkPhone );
        $( "#PrvFaxNumber" ).val( ui.item.PrvFaxNumber );
        //$( "#hiddenPrvProviderId" ).val( ui.item.PrvProviderId );
        //hiddenPrvProviderId
 		//$("#checkData").val(ui.item.PrvFirstName+ui.item.PrvLastName+ui.item.PrvAddress1+ui.item.PrvAddress2+ui.item.PrvCity+ui.item.PrvState+ui.item.PrvZip+ui.item.PrvWorkPhone+ui.item.PrvFaxNumber);
        return false;
      }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
    	$( "#PrvFirstName" ).removeClass('loading-fname');
      return $( "<li>" )
        .append( "<div>" + item.PrvFirstName + " " + item.PrvLastName + "<br> Address:" + item.PrvAddress1+"</div>" )
        .appendTo( ul );
    };

    $( "#PrvLastName" ).autocomplete({
      minLength: 0,
     // source: "testajax.php",
      source: function( request, response ) {
      	var patient_id = "<?php echo $_SESSION['PLP']['patient']->PatientID;?>";
      	if($("#PrvLastName").val() =='' || jQuery("#PrvLastName").val().length <= 2){
      		$(".ui-autocomplete").hide();
      	}
      	if($("#PrvLastName").val() !='' && $("#PrvLastName").val().length > 2){
				$.ajax({
				    url: "ajax_get_doctors_list.php",
				    dataType: "json",
				    data: {
				        id: patient_id,
				        sortType: 'LastName',
				        PrvFirstName: $("#PrvLastName").val()
				    },
				    beforeSend : function() {
						$("#PrvLastName").removeClass('correct');
						$("#PrvLastName").addClass('loading-lname');

						//jQuery("#msg").fadeOut();
					},
				    success: function (data) {
				    	//console.log(data);
				    	if(data.success == 0){
				    			$("#PrvLastName").removeClass('loading-lname');
				    			$(".ui-autocomplete").hide();
				    			
				    		}else{
					        response($.map(data.doctors, function (item) {
					        	console.log(item);
					            return {
					            	id: item.PrvProviderId,
					                PrvFirstName: item.PrvFirstName,
					                PrvLastName:item.PrvLastName,
					                PrvPracticeName:item.PrvPracticeName,
					                PrvAddress1:item.PrvAddress1,
					                PrvAddress2:item.PrvAddress2,
					                PrvCity:item.PrvCity,
					                PrvState:item.PrvState,
					                PrvZip:item.PrvZip,
					                PrvWorkPhone:item.PrvWorkPhone,
					                PrvFaxNumber:item.PrvFaxNumber,
					                PrvProviderId:item.PrvProviderId
					                
					            };
					        }));
					    }
					    }
				});
			}else{
				$("#PrvLastName").removeClass('loading-lname');
			}
      },
      focus: function( event, ui ) {
        $( "#PrvLastName" ).val( ui.item.PrvLastName );
        return false;
      },
      select: function( event, ui ) {
      //	console.log(ui.item.PrvProviderId);
      	$( "#PrvFirstName" ).val( ui.item.PrvFirstName );
        $( "#PrvLastName" ).val( ui.item.PrvLastName );
        $( "#PrvPracticeName" ).val( ui.item.PrvPracticeName );
        $( "#PrvAddress1" ).val( ui.item.PrvAddress1 );
        $( "#PrvAddress2" ).val( ui.item.PrvAddress2 );
        $( "#PrvCity" ).val( ui.item.PrvCity );
        $('#PrvState').val(ui.item.PrvState);
        $( "#PrvZip" ).val( ui.item.PrvZip );
        $( "#PrvWorkPhone" ).val( ui.item.PrvWorkPhone );
        $( "#PrvFaxNumber" ).val( ui.item.PrvFaxNumber );
        //$( "#hiddenPrvProviderId" ).val( ui.item.PrvProviderId );
        //hiddenPrvProviderId
 		//$("#checkData").val(ui.item.PrvFirstName+ui.item.PrvLastName+ui.item.PrvAddress1+ui.item.PrvAddress2+ui.item.PrvCity+ui.item.PrvState+ui.item.PrvZip+ui.item.PrvWorkPhone+ui.item.PrvFaxNumber);
        return false;
      }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
    	$( "#PrvLastName" ).removeClass('loading-lname');
      return $( "<li>" )
        .append( "<div>" + item.PrvFirstName + " " + item.PrvLastName + "<br> Address:" + item.PrvAddress1+"</div>" )
        .appendTo( ul );
    };
  } );

  </script>
        <div class="row zeroed" id="rowPrvFirstName">
				<div class="col-sm-4">
					
					<input type="text" class="autowidth" id="PrvFirstName" placeholder="First Name">
				</div>
				<div class="col-sm-4">
					
					<input type="text" class="autowidth" id="PrvLastName" placeholder="Last Name">
				</div>
				<div class="col-sm-4">				
					<input type="text" class="autowidth" id="PrvPracticeName"placeholder="Practice Name">
				</div>
			</div>
			
			<div class="row zeroed">
				<div class="col-sm-6">					
					<input type="text" class="autowidth" id="PrvAddress1" autocomplete="nope" placeholder="Address Line 1">
				</div>
				<div class="col-sm-6">					
					<input type="text" class="autowidth" id="PrvAddress2" placeholder="Address Line 2">
				</div>
			</div>

			<div class="row zeroed">
				<div class="col-sm-4">
					
					<input type="text" class="autowidth" id="PrvCity" placeholder="City">
				</div>
				<div class="col-sm-4">
					
					<select name="PrvState" id="PrvState" class="form-control login-portal">
						<option value="">State</option>
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
					<input type="text" class="autowidth numeric" id="PrvZip" maxlength="5" placeholder="Zip">
				</div>
			</div>

			<div class="row zeroed">
				<div class="col-sm-6">
				
					<input type="text" class="autowidth phone_us" id="PrvWorkPhone" placeholder="Phone Number">
				</div>
				<div class="col-sm-6">
					
					<input type="text" class="autowidth phone_us" id="PrvFaxNumber" placeholder="Fax Number">
				</div>				
			</div>
			
			<div class="row zeroed">
				<div class="col-sm-12">
				
					<div id="existingMeds"></div>
					<select id="medToDoc" name="medToDoc" class="form-control login-portal" placeholder="Prescribed Medication">
						<option value="">Select A Medication</option>
						<?php if($options_html!=''){ ?>
						<optgroup label="Add existing medication">
							<?php echo $options_html;?>
						</optgroup>
						<?php } ?>
						<optgroup label="Others">
							<option value="-1">Save For Future Use</option>
							<option value="0">Add new Medication</option>							
						</optgroup>
					</select>
				</div>
			</div>
			
			<div id="add_med" style="display:none;">
				<div class="box small yellowMedNoticeBox">
					<center>Any dosage changes or medications added to your account will be verified by your healthcare provider. If you add a medication or change a dosage, please allow 2-4 weeks from the date of the change to ensure the healthcare provider has enough time to respond to our requests and for us to place the order.</center>
				</div>
				<div class="row zeroed" id="add_new_med">
					<div class="row zeroed" id="DrugNameRow">
						<div class="col-sm-12">
							
							<input type="text" class="autowidth" name="DrugAppliedFor" id="DrugAppliedFor" placeholder="Medication Name">
						</div>
					</div>
			
					<div class="row zeroed">
						<div class="col-sm-6 w-s">
							
							<input type="text" class="autowidth" name="Dosage" id="Dosage"/ placeholder="Strength (10mg, 20mL, etc)">
						</div>
						<div class="col-sm-6 w-s">
						
							<input type="text" class="autowidth" name="Directions" id="Directions" placeholder="Directions (Take twice daily, take with meals, etc)"/>
						</div>
					</div>
						<div id="add_med" style="display:none;">
				<div class="box small yellowMedNoticeBox">
					<center>Any dosage changes or medications added to your account will be verified by your healthcare provider. If you add a medication or change a dosage, please allow 2-4 weeks from the date of the change to ensure the healthcare provider has enough time to respond to our requests and for us to place the order.</center>
				</div>
				<div class="row zeroed" id="add_new_med">
					<div class="row zeroed" id="DrugNameRow">
						<div class="col-sm-12">
							
							<input type="text" class="autowidth" name="DrugAppliedFor" id="DrugAppliedFor" placeholder="Medication Name"/>
						</div>
					</div>
			
					<div class="row zeroed">
						<div class="col-sm-6 w-s">
						
							<input type="text" class="autowidth" name="Dosage" id="Dosage" placeholder="Strength (10mg, 20mL, etc)"/>
						</div>
						<div class="col-sm-6 w-s">
							
							<input type="text" class="autowidth" name="Directions" id="Directions" placeholder="Directions (Take twice daily, take with meals, etc)"/>
						</div>
					</div>
				</div>				
			</div>

				</div>				
			</div>
      </div>
      <div class="modal-footer">
	<p>    		
			<a id="btnSaveProvider" href="javascript:saveNewProvider();" class="big-button whiteButton">Save Healthcare Provider</a>
			<a id="btnSavingProvider" href="javascript:void(0);" class="big-button whiteButton" style="display:none;">Saving...</a>		
			
				<div style="display:none;">
		<select id="medToDocSample">
			<option value="">Select A Medication</option>
			<optgroup label="Add existing medication">
				<?php echo $options_html;?>
			</optgroup>
			<optgroup label="Others">
				<option value="-1">Save For Future Use</option>
				<option value="0">Add new Medication</option>							
			</optgroup>													
		</select>	
	</div>
      </div>
    </div>

  </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function(){
	//jQuery(".phone_us").mask("000-000-0000");	
	jQuery(document).on('change', '#medToDoc', function(){
		if(jQuery(this).val()!=''){
			jQuery(this).find('option[value="'+jQuery(this).val()+'"]').addClass('selected').siblings('option').removeClass('selected');
		}
		if(jQuery(this).val()!='' && jQuery(this).val()=='0'){			
			jQuery('#overlay_holder').css('margin','80px auto');
			jQuery('#add_med').show();
		}
		else{
			jQuery('#overlay_holder').css('margin','auto');
			jQuery('#add_med').hide();
		}
		refreshOverlaySize(false);
	});
	
	//jQuery('.prov_item').click(function(){
	jQuery('.prov_item .view-medi').click(function(){
		var thisId = jQuery(this).parents('.prov_item').attr('id');
		// check if View Details is clicked or Hide Details
		if(jQuery(this).parents('.prov_item').hasClass('med-active')){
			jQuery('.table-wrap-desc').find('#'+ thisId.replace('prov','provdesc')).find('.close_desc').trigger('click');			
		}
		else{
			jQuery('.prov_item .view-medi').each(function(){
				jQuery(this).html('<i class="fa fa-eye" aria-hidden="true"></i> <span>View Details</span>');
			});
			jQuery('.table-wrap-desc').find('.provdesc-box').hide();
			jQuery('.table-wrap-desc').show();
			jQuery(this).parents('.prov_item').siblings().removeClass('med-active');
			jQuery(this).parents('.prov_item').addClass('med-active');
			jQuery('#tables_main').addClass('showmeddata');
			jQuery('.table-wrap').find('#'+ thisId.replace('prov','view')+'>a').html('<i class="fa fa-eye-slash" aria-hidden="true"></i> <span>Hide Details</span>');
			jQuery('.table-wrap-desc').find('#'+ thisId.replace('prov','provdesc')).fadeIn(500);
		}
	});
	jQuery('.close_desc').click(function(){
		var thisId = jQuery(this).attr('data-id');
		jQuery('#'+thisId).show();
		jQuery('#'+thisId.replace('provdesc','prov')).removeClass('med-active');
		jQuery('#tables_main').removeClass('showmeddata');
		console.log('#'+ thisId.replace('provdesc','view')+'>a.view-medi');
		jQuery('.table-wrap').find('#'+ thisId.replace('provdesc','view')+'>a.view-medi').html('<i class="fa fa-eye" aria-hidden="true"></i> <span>View Details</span>');
		jQuery('.table-wrap-desc').find('#'+ thisId).fadeOut(100);		
	});	
});
</script>

<?php include('_footer.php'); ?>
