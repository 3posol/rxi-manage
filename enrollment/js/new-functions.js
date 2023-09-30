$(function(){

	
	// $(document).on('click','#myTabsMedication a',function(){
	// // $("#myTabsMedication a").on('click', function(e) {
 //    var target = $(this).attr('href');
 //    console.log('Current val',target);
	// var selectedOption = $(target).find('select');
 //    console.log('Current val',$(selectedOption).val());
	//    if($(selectedOption).val() === "") {
	//        $("#myTabsMedication a").not('a[href="'+target+'"]').removeAttr('data-toggle');
	//    }
	// });

	$(".medView").click(function(){
		ele = $("#medRow_"+$(this).attr("data-id"));
		if (ele.is(":visible")){
			$("#medView_"+$(this).attr("data-id")).html("<i class='fa fa-eye'></i> VIEW");
			ele.hide();
		} else {
			$("#medView_"+$(this).attr("data-id")).html("<font class='red'><b><i class='fa fa-eye-slash'></i> CLOSE</b></font>");
			ele.show();
		}
	});

	$(document).on('mouseenter','span.question',function () {
		$(this).append('<div class="tooltipBox"><p>' + $("#tooltipContent_"+$(this).attr("data-tooltipTarget")).html() + '</p></div>');

		var tooltip_position = $("div.tooltipBox").offset();
		if (tooltip_position.left + $("div.tooltipBox").width() > $(window).width()) {
			$("div.tooltipBox").css('top', '25px').css('left', '-275px');
		}
	})

	$(document).on('mouseleave click','span.question',function () {
		$("div.tooltipBox").remove();
	})

	$(document).on('keydown', '.numeric', function(e){-1!==$.inArray(e.keyCode,[46,8,9,27,13,110,190])||/65|67|86|88/.test(e.keyCode)&&(!0===e.ctrlKey||!0===e.metaKey)||35<=e.keyCode&&40>=e.keyCode||(e.shiftKey||48>e.keyCode||57<e.keyCode)&&(96>e.keyCode||105<e.keyCode)&&e.preventDefault()});
	$(document).on('keydown', function(e){
		if (e.keyCode == 27) {
			if ($('#overlay').is(':visible')) {
				closeOverlay();
			}
		}
	});

	$('.date').mask('00/00/0000');
	$('.exp_date').mask('00/00');
	$('.phone_us').mask('(000) 000-0000');
});

function addProofOfIncome(){
	$("#overlay_holder").html($("#overlay_uploader").html());

	//
	showOverlay();
}

function submitProofOfIncome(){
	$('#btnSaveProofOfIncome').hide();
	$('#btnSavingProofOfIncome').show();

	if ($('#incomeProof').length > 0 && $('#incomeProof')[0].files.length > 0) {
		//prepare file
		var formData = new FormData();
		formData.append('IncomeProof', $('#incomeProof')[0].files[0]);

		$.ajax({
			url: 'add_income_proof.php',
			type: 'POST',
			data: formData,
			processData: false,  // tell jQuery not to process the data
			contentType: false,  // tell jQuery not to set contentType
			success : function(data) {
		  		console.log(data);
		  		data = $.parseJSON(data);

		  		//if (data.success){
				$('#btnSaveProofOfIncome').show();
				$('#btnSavingProofOfIncome').hide();
		  		alert(data.message);

		  		if (data.success){
					$('#incomeProof').val('');
		  		}
			}
		});
	} else {
		$('#btnSaveProofOfIncome').show();
		$('#btnSavingProofOfIncome').hide();
		alert('Some data is missing, please fill all required fields and try again.');
	}
}

function addProofOfIncomeDD(){
	$("#overlay_holder").html($("#overlay_uploader").html());

	var obj = $("#dragandrophandler");
	obj.on('dragenter', function (e)
	{
		e.stopPropagation();
		e.preventDefault();
		$(this).css('border', '3px solid #F9A15D');
	});
	obj.on('dragover', function (e)
	{
		 e.stopPropagation();
		 e.preventDefault();
	});
	obj.on('drop', function (e)
	{
		 $(this).css('border', '3px dashed #FFF');
		 e.preventDefault();
		 var files = e.originalEvent.dataTransfer.files;

		 //We need to send dropped files to Server
		 handleFileUpload(files,obj);
	});


	$(document).on('dragenter', function (e)
	{
		e.stopPropagation();
		e.preventDefault();
	});
	$(document).on('dragover', function (e)
	{
	  e.stopPropagation();
	  e.preventDefault();
	  obj.css('border', '3px dashed #FFF');
	});
	$(document).on('drop', function (e)
	{
		e.stopPropagation();
		e.preventDefault();
	});

	$("#overlay").fadeIn('fast');
}

var editing_med_id;

function editAccountDetails(){
	$("#overlay_holder").html($("#overlay_edit_account").html());

	$.post( "ajax_get_account.php", { id: 1 })
  	.done(function( data ) {
  		data = $.parseJSON(data);
		$("#PatientFirstName").val(data.PatientFirstName);
		$("#PatientMiddleInitial").val(data.PatientMiddleInitial);
		$("#PatientLastName").val(data.PatientLastName);
		$("#PatientAddress1").val(data.PatientAddress1);
		$("#PatientAddress2").val(data.PatientAddress2);
		$("#PatientCity_1").val(data.PatientCity_1);
		$("#PatientState_1").val(data.PatientState_1);
		$("#PatientZip_1").val(data.PatientZip_1);
		$("#PatHomePhoneWACFmt_1").val(data.PatHomePhoneWACFmt_1);
		$("#PatientDOB").val(data.PatientDOB);
		$("#PatientSSN").val('xxx-xx-' + data.PatientSSN);
		$("#EmergencyContactName").val(data.EmergencyContactName);
		$("#EmergencyContactPhone").val(data.EmergencyContactPhone);
		$("#EmergencyContact2Name").val(data.EmergencyContact2Name);
		$("#EmergencyContact2Phone").val(data.EmergencyContact2Phone);
		$("#EmergencyContact3Name").val(data.EmergencyContact3Name);
		$("#EmergencyContact3Phone").val(data.EmergencyContact3Phone);

		showOverlay();

		if ($(document).width() < 1025){
			//$('.overlay_form').height($(document).height());
		}
  	});
}

function saveAccountDetails(){
	$('#btnSaveAccountDetails').hide();
	$('#btnSavingAccountDetails').show();

	$.post( "update_account.php", {
		PatientFirstName: $("#PatientFirstName").val(),
		PatientMiddleInitial: $("#PatientMiddleInitial").val(),
		PatientLastName: $("#PatientLastName").val(),
		PatientAddress1: $("#PatientAddress1").val(),
		PatientAddress2: $("#PatientAddress2").val(),
		PatientCity_1: $("#PatientCity_1").val(),
		PatientState_1: $("#PatientState_1").val(),
		PatientZip_1: $("#PatientZip_1").val(),
		PatHomePhoneWACFmt_1: $("#PatHomePhoneWACFmt_1").val(),
		PatientDOB: $("#PatientDOB").val(),
		PatientSSN: $("#PatientSSN").val(),
		EmergencyContactName: $("#EmergencyContactName").val(),
		EmergencyContactPhone: $("#EmergencyContactPhone").val(),
		EmergencyContact2Name: $("#EmergencyContact2Name").val(),
		EmergencyContact2Phone: $("#EmergencyContact2Phone").val(),
		EmergencyContact3Name: $("#EmergencyContact3Name").val(),
		EmergencyContact3Phone: $("#EmergencyContact3Phone").val()
	})
  	.done(function( data ) {
  		console.log(data);
  		data = $.parseJSON(data);
  		if (data.success){
			window.location.reload();
  		} else {
			$('#btnSaveAccountDetails').show();
			$('#btnSavingAccountDetails').hide();
	  		alert(data.message);
  		}
  	});
}

function addMedication(provider_id){
	closeOverlay();
	$("#overlay_holder").html($("#overlay_add_medication").html());
	$("#overlay_holder select#ProviderId").change(function(e) {
		if ($(this).val() == "-1") {
			$('#formNewProvider').show();
		} else {
			$('#formNewProvider').hide();
		}

		refreshOverlaySize(false);
	});
	$('#overlay_holder p.error').remove();

	$("#DrugAppliedFor").attr("disabled", false);
	$("#medication_modal_title").html("Add Medication");
	$('.btAddMedication').hide();

	//preselect provider
	if (provider_id != '') {
		$("#overlay_holder select#ProviderId").val(provider_id);
	}

	if ($(document).width() < 1025){
		$('#overlay').height($(document).height());
	}

	//
	showOverlay();
}

function saveNewMedication(){
	$('#overlay_holder p.error').remove();

	if ($("#DrugAppliedFor").attr("disabled")){
		saveExistingMedication();
		return false;
	}

	$('#btnSaveMedication').hide();
	$('#btnSavingMedication').show();

	if ($("#DrugAppliedFor").val() == '' || $("#Dosage").val() == '' || $("#Directions").val() == '' || $("#ProviderId").val() == '' || ($("#ProviderId").val() == "-1" && ($("#PrvFirstName").val() == '' || $("#PrvLastName").val() == '' || $("#PrvAddress1").val() == '' || $("#PrvWorkPhone").val() == ''))){
		$('#DrugNameRow').before('<p class="text-center error">Error: You did not enter all required fields</p>');

		$('#btnSaveMedication').show();
		$('#btnSavingMedication').hide();
		return false;
	}

	if ($("#ProviderId").val() != '-1') {
		med_data = {
			DrugAppliedFor: $("#DrugAppliedFor").val(),
			Dosage: 		$("#Dosage").val(),
			Directions: 	$("#Directions").val(),
			ProviderId: 	$("#ProviderId").val()
		}
	} else {
		med_data = {
			DrugAppliedFor: $("#DrugAppliedFor").val(),
			Dosage: 		$("#Dosage").val(),
			Directions: 	$("#Directions").val(),
			ProviderId: 	$("#ProviderId").val(),
			PrvFirstName: 	$("#PrvFirstName").val(),
			PrvLastName: 	$("#PrvLastName").val(),
			PrvAddress1: 	$("#PrvAddress1").val(),
			PrvAddress2: 	$("#PrvAddress2").val(),
			PrvCity: 		$("#PrvCity").val(),
			PrvState: 		$("#PrvState").val(),
			PrvZip: 		$("#PrvZip").val(),
			PrvWorkPhone: 	$("#PrvWorkPhone").val(),
			PrvFaxNumber: 	$("#PrvFaxNumber").val()
		}
	}

	$.post( "add_medication.php", med_data)
  	//.done(function( data ) {
  	//	console.log(data);
  	//	window.location.reload();
  	//});
  	.done(function( data ) {
  		console.log(data);
  		data = $.parseJSON(data);
  		if (data.success){
			window.location.reload();
		} else {
  			$('#DrugNameRow').before('<p class="text-center error">Error: ' + data.message + '</p>');
			$('#btnSaveMedication').show();
			$('#btnSavingMedication').hide();
	  		//alert(data.message);
  		}
  	});
}

function editMedication(med_id){
	$("#overlay_holder").html($("#overlay_add_medication").html());
	$("#medication_modal_title").html("Change Dosage");
	$("#DrugAppliedFor").attr("disabled", true);
	$('.btAddMedication').show();
	$("#overlay_holder select#ProviderId").change(function(e) {
		if ($(this).val() == "-1") {
			$('#formNewProvider').show();
		} else {
			$('#formNewProvider').hide();
		}

		refreshOverlaySize(false);
	});
	$('#overlay_holder p.error').remove();

	$.post( "ajax_get_medication.php", { med_id: med_id })
  	.done(function( data ) {
  		data = $.parseJSON(data);
		$("#medication_modal_title").html("Change Dosage");
		$("#DrugAppliedFor").val(data.DrugAppliedFor);
		$("#Dosage").val(data.Dosage);
		$("#Directions").val(data.Directions);
		$("#ProviderId").val(data.ProviderId);

		//
		showOverlay();

		editing_med_id = med_id;
  	});
}

function saveExistingMedication(){
	$('#overlay_holder p.error').remove();
	$('#btnSaveMedication').hide();
	$('#btnSavingMedication').show();

	if ($("#Dosage").val() == '' || $("#Directions").val() == '' || $("#ProviderId").val() == '' || ($("#ProviderId").val() == "-1" && ($("#PrvFirstName").val() == '' || $("#PrvLastName").val() == '' || $("#PrvAddress1").val() == '' || $("#PrvWorkPhone").val() == ''))) {
		$('#DrugNameRow').before('<p class="text-center error">Error: You did not enter all required fields</p>');
		$('#btnSaveMedication').show();
		$('#btnSavingMedication').hide();
		return false;
	}

	if ($("#ProviderId").val() != '-1') {
		med_data = {
			MedAssistDetailID : editing_med_id,
			DrugAppliedFor: 	$("#DrugAppliedFor").val(),
			Dosage: 			$("#Dosage").val(),
			Directions: 		$("#Directions").val(),
			ProviderId: 		$("#ProviderId").val()
		}
	} else {
		med_data = {
			MedAssistDetailID : editing_med_id,
			DrugAppliedFor: 	$("#DrugAppliedFor").val(),
			Dosage: 			$("#Dosage").val(),
			Directions: 		$("#Directions").val(),
			ProviderId: 		$("#ProviderId").val(),
			PrvFirstName: 		$("#PrvFirstName").val(),
			PrvLastName: 		$("#PrvLastName").val(),
			PrvAddress1: 		$("#PrvAddress1").val(),
			PrvAddress2: 		$("#PrvAddress2").val(),
			PrvCity: 			$("#PrvCity").val(),
			PrvState: 			$("#PrvState").val(),
			PrvZip: 			$("#PrvZip").val(),
			PrvWorkPhone: 		$("#PrvWorkPhone").val(),
			PrvFaxNumber: 		$("#PrvFaxNumber").val()
		}
	}

	$.post( "add_medication.php", med_data)
  	.done(function( data ) {
  		console.log(data);
  		data = $.parseJSON(data);
  		if (data.success){
			window.location.reload();
		} else {
  			$('#DrugNameRow').before('<p class="text-center error">Error: ' + data.message + '</p>');
			$('#btnSaveMedication').show();
			$('#btnSavingMedication').hide();
	  		//alert(data.message);
  		}
  	});
}

function saveExistingProvider(){
	$('#overlay_holder p.error').remove();

	$('#btnSaveProvider').hide();
	$('#btnSavingProvider').show();

	if ($("#PrvAddress1").val() == '' || $("#PrvWorkPhone").val() == ''){
		$('#rowPrvFirstName').before('<p class="text-center error">Error: You did not enter all required fields</p>');
		$('#btnSaveProvider').show();
		$('#btnSavingProvider').hide();
		return false;
	}

	$.post( "update_provider.php", {
		PrvAddress1: [$("#PrvAddress1").val()],
		PrvAddress2: [$("#PrvAddress2").val()],
		PrvCity: [$("#PrvCity").val()],
		PrvState: [$("#PrvState").val()],
		PrvWorkPhone: [$("#PrvWorkPhone").val()],
		PrvFaxNumber: [$("#PrvFaxNumber").val()],
		PrvZip: [$("#PrvZip").val()],
		PrvPracticeName: [$("#PrvPracticeName").val()],
		modified_items: 0
	})
  	.done(function( data ) {
  		console.log(data);
  		window.location.reload();
  	});
}

function saveNewProvider(){
	$('#overlay_holder p.error').remove();

	if ($("#PrvFirstName").attr("disabled")){
		saveExistingProvider();
		return false;
	}

	$('#btnSaveProvider').hide();
	$('#btnSavingProvider').show();

	if ($("#PrvFirstName").val() == '' || $("#PrvLastName").val() == '' || $("#PrvAddress1").val() == '' || $("#PrvWorkPhone").val() == ''){
		$('#rowPrvFirstName').before('<p class="text-center error"><br>Error: You did not enter all required fields<br><br></p>');
		$('#btnSaveProvider').show();
		$('#btnSavingProvider').hide();
		return false;
	}

	$.post( "add_provider.php", {
		PrvFirstName: $("#PrvFirstName").val(),
		PrvLastName: $("#PrvLastName").val(),
		PrvPracticeName: $("#PrvPracticeName").val(),
		PrvAddress1: $("#PrvAddress1").val(),
		PrvAddress2: $("#PrvAddress2").val(),
		PrvCity: $("#PrvCity").val(),
		PrvState: $("#PrvState").val(),
		PrvWorkPhone: $("#PrvWorkPhone").val(),
		PrvFaxNumber: $("#PrvFaxNumber").val(),
		PrvZip: $("#PrvZip").val(),
		PrvPrefix: 'Dr.'
	})
  	.done(function( data ) {
  		console.log(data);
  		window.location.reload();
  	});
}

function addProvider(){
	$("#PrvFirstName").attr("disabled", false);
	$("#PrvLastName").attr("disabled", false);
	$("#overlay_holder").html($("#overlay_add_provider").html());
	$("#medication_modal_title").html("Add Healthcare Provider");
	$('#overlay_holder p.error').remove();

	//
	showOverlay();
}

function editProvider(provider_id){
	$("#PrvFirstName").attr("disabled", true);
	$("#PrvLastName").attr("disabled", true);
	$("#overlay_holder").html($("#overlay_add_provider").html());
	$("#medication_modal_title").html("Edit Healthcare Provider");
	$('#overlay_holder p.error').remove();

	$.post( "ajax_get_provider.php", { provider_id: provider_id })
  	.done(function( data ) {
  	console.log(data);
  		data = $.parseJSON(data);
		$("#PrvFirstName").val(data.PrvFirstName);
		$("#PrvLastName").val(data.PrvLastName);
		$("#PrvAddress1").val(data.PrvAddress1);
		$("#PrvAddress2").val(data.PrvAddress2);
		$("#PrvCity").val(data.PrvCity);
		$("#PrvState").val(data.PrvState);
		$("#PrvZip").val(data.PrvZip);
		$("#PrvWorkPhone").val(data.PrvWorkPhone);
		$("#PrvFaxNumber").val(data.PrvFaxNumber);
		$("#PrvPracticeName").val(data.PrvPracticeName);

		//
		showOverlay();
  	});
}

function editBillingInformation(){
	$("#overlay_holder").html($("#overlay_edit_billing").html());

	$.post( "ajax_get_billing.php", { id: 1 })
  	.done(function( data ) {
  		data = $.parseJSON(data);
		$("#PatientFirstNameBilling").val(data.PatientFirstName);
		$("#PatientMiddleInitialBilling").val(data.PatientMiddleInitial);
		$("#PatientLastNameBilling").val(data.PatientLastName);
		$("#PatientAddress1Billing").val(data.PatientAddress1);
		$("#PatientAddress2Billing").val(data.PatientAddress2);
		$("#PatientCity_1Billing").val(data.PatientCity_1);
		$("#PatientState_1Billing").val(data.PatientState_1);
		$("#PatientZip_1Billing").val(data.PatientZip_1);

		showOverlay();
	});
}

function saveBillingDetails(){
	$('#btnSaveBillingDetails').hide();
	$('#btnSavingBillingDetails').show();

	$.post( "update_payment_information.php", {
		PatientFirstNameBilling: $("#PatientFirstNameBilling").val(),
		PatientMiddleInitialBilling: $("#PatientMiddleInitialBilling").val(),
		PatientLastNameBilling: $("#PatientLastNameBilling").val(),
		PatientAddress1Billing: $("#PatientAddress1Billing").val(),
		PatientAddress2Billing: $("#PatientAddress2Billing").val(),
		PatientCity_1Billing: $("#PatientCity_1Billing").val(),
		PatientState_1Billing: $("#PatientState_1Billing").val(),
		PatientZip_1Billing: $("#PatientZip_1Billing").val(),
		cc_number: $("#cc_number").val(),
		cc_type: $("#cc_type").val(),
		cc_exp: $("#cc_exp").val(),
		cc_cvv: $("#cc_cvv").val()
	})
  	.done(function( data ) {
  		console.log(data);
  		data = $.parseJSON(data);
  		if (data.success){
			window.location.reload();
  		} else {
			$('#btnSavingBillingDetails').hide();
			$('#btnSaveBillingDetails').show();
	  		alert(data.message);
  		}
  	});
}

var forceShowOverdueInvoices = 0;

function showOverdueInvoices () {
	$("#overlay_holder").html($("#overlay_overdue_invoices").html());
	showOverlay();
}

function makePayment (invoice, date, amount) {
	closeOverlay();

	$("#overlay_holder").html($("#overlay_make_payment").html());
	$('#payBillAmount').html('$' + amount);
	$('#payInvoiceNo').val(invoice);
	$('#payInvoiceAmount').val(amount);
	$('p#btUseAnotherCC a').attr('href', 'javascript:makePaymentNewCC(' + invoice + ', "' + date + '", "' + amount + '");');
	showOverlay();
}

function submitPayment () {
	$('#txtPayBillInfo').html('Payment processing, do not close your browser or refresh the page until you receive confirmation of payment.');
	$('#btnSubmitPayment').hide();
	$('a#payCancelLink').hide();
	$('#btUseAnotherCC').hide();

	$.post( "pay_bill.php", {
		invoice: $("#payInvoiceNo").val(),
		amount: $("#payInvoiceAmount").val()
	})
  	.done(function( data ) {
  		console.log(data);
  		data = $.parseJSON(data);
  		if (data.success){
			$('#txtPayBillInfo').html('<h3>Payment Confirmation</h3><br/>Date and Time of transaction: <br class="onlyMobile"><strong>' + data.datetime + '</strong><br/><br class="onlyMobile">Transaction Amount: <br class="onlyMobile"><strong>$' + data.amount + '</strong><br/><br class="onlyMobile">Invoice Number: <br class="onlyMobile"><strong>' + data.invoice + '</strong><br><br>Thank you for your payment, a receipt has been emailed to you and an advocate will review your file soon to update your account.');
			$('a#payCancelLink').show();
			$('a#payCancelLink').html('Close');
			$('a#payCancelLink').attr('href', 'javascript:window.location.reload();');
			//window.location.reload();
  		} else {
			$('#txtPayBillInfo').html('<h3 class="error">Payment Failed</h3><br/>Please call a patient advocate as soon as possible for assistance at 1-877-298-1012, option 3.');
			$('a#payCancelLink').show();
			$('a#payCancelLink').html('Go Back');
			$('a#payCancelLink').attr('href', 'javascript:window.location.reload();');
	  		//alert(data.message);
  		}
  	});
}

function makePaymentNewCC (invoice, date, amount) {
	closeOverlay();

	$("#overlay_holder").html($("#overlay_make_payment_new_cc").html());
	$('#payCCInvoiceNo').val(invoice);
	$('#payCCInvoiceNoText').html(invoice);
	$('#payCCInvoiceAmount').val(amount);
	$('#payCCInvoiceAmountText').html(amount);
	$('p#btUseAnotherCC a').attr('href', 'javascript:makePaymentNewCC(' + invoice + ', "' + date + '", "' + amount + '");');
	showOverlay();
}

function submitPaymentNewCC () {
	//$('#titlePayBillNewCC').hide();
	$('#txtPayBillInfoNewCC').removeClass('dark-red').html('Payment processing, do not close your browser or refresh the page until you receive confirmation of payment...<br><br>').show();
	$('#btnSubmitPaymentNewCC').hide();
	$('a#payCancelLinkNewCC').hide();

	$.post( "pay_billcc.php?invoice=" + $("#payCCInvoiceNo").val(), {
		invoice_amount: $('#payCCInvoiceAmount').val(),
		cc_type: 		$("#payCCType").val(),
		cc_number: 		$("#payCCNumber").val(),
		cc_exp_month: 	$("#payCCExpMonth").val(),
		cc_exp_year: 	$("#payCCExpYear").val()
	})
  	.done(function( data ) {
  		console.log(data);
  		data = $.parseJSON(data);
  		if (data.success){
  			if (!data.bill_payed_already) {
				$('#txtPayBillInfoNewCC').html('Date and Time of transaction: <br class="onlyMobile"><strong>' + data.datetime + '</strong><br/><br class="onlyMobile">Transaction Amount: <br class="onlyMobile"><strong>$' + data.amount + '</strong><br/><br class="onlyMobile">Invoice Number: <strong>' + data.invoice + '</strong><br><br>Thank you for your payment, a receipt has been emailed to you and an advocate will review your file soon to update your account.');
  			} else {
				$('#txtPayBillInfoNewCC').removeClass('dark-red').html(data.message).show();
  			}

			$('#titlePayBillNewCC').html('Payment Confirmation');
  			$('#formPayBillNewCC').hide();
			$('a#payCancelLinkNewCC').show();
			$('a#payCancelLinkNewCC').html('Close');
			$('a#payCancelLinkNewCC').attr('href', 'javascript:window.location.reload();');
			//window.location.reload();
  		} else {
			$('#txtPayBillInfoNewCC').addClass('dark-red').html(data.message).show();
			$('#btnSubmitPaymentNewCC').show();
			$('a#payCancelLinkNewCC').show();
			$('a#payCancelLinkNewCC').html('Go Back');
			$('a#payCancelLinkNewCC').attr('href', 'javascript:closeOverlay();');
	  		//alert(data.message);
  		}
  	});
}

function schedulePayment (invoice, date, amount) {
	closeOverlay();

	$("#overlay_holder").html($("#overlay_schedule_payment").html());
	$('#overlay_holder p.error').remove();

	$('#ScheduleInvoiceNumber').html(invoice);
	$('#schedule_number').val(invoice);
	$('#ScheduleInvoiceAmount').html('$' + amount);

	d = new Date();
	$('input#schedule_date').val((((d.getMonth() + 1) < 10 ? '0' : '') + (d.getMonth() + 1)) + "/" + (d.getDate() < 10 ? '0' : '') + d.getDate() + "/" + d.getFullYear());
	$('[data-toggle="datepicker"]').datepicker({
		autoHide: true,
		zIndex: 1000001
	});

	showOverlay();
}

function saveScheduledPayment () {
	$('#btnSaveScheduledPayment').hide();
	$('#btnSavingScheduledPayment').show();

	$.post( "schedule_bill.php?invoice=" + $("#schedule_number").val(), {
		schedule_date: $("#schedule_date").val(),
	})
  	.done(function( data ) {
  		console.log(data);
  		data = $.parseJSON(data);
  		if (data.success){
			$('#overlay_holder p.error').remove();

  			$('#titleScheduleBill').html("Payment Successfully Scheduled");
  			$('#formScheduleBill').html('<p class="text-center">' + data.message + '</p>');
			$('#btnSaveScheduledPayment').hide();
			$('#btnSavingScheduledPayment').hide();
			$('a#scheduleCancelLink').html('Close');
			$('a#scheduleCancelLink').attr('href', 'javascript:window.location.reload();');
			//window.location.reload();
  		} else {
			$('#overlay_holder p.error').remove();

  			$('#formScheduleBill').before('<p class="text-center error">Error: ' + data.message + '</p>');
			$('#btnSaveScheduledPayment').show();
			$('#btnSavingScheduledPayment').hide();
			//alert(data.message);
  		}
  	});
}

function showMissingEmailNotice () {
	closeOverlay();

	$("#overlay_holder").html($("#overlay_missing_email").html());
	showOverlay();

	$('#overlay').css('position', 'fixed');
	$('#overlay').css('width', '100%');
	$('#overlay').css('height', '100%');

	$(document).unbind('keydown');
}

var scroll_on_overlay = 0;

function showOverlay() {
	$("#overlay").fadeIn('fast');

	refreshOverlaySize(true);

	//disable scrollbars
	//$('body').css('overflow', 'hidden');
	//scroll_on_overlay = $(window).scrollTop();
}

function closeOverlay(){
	editing_med_id = '';
	$("#overlay").hide();

	if (forceShowOverdueInvoices == 1) {
		showOverdueInvoices();
	}
}

function refreshOverlaySize (scroll) {
	//console.log($("#overlay_holder .overlay_loaded_content").html());
	//$('#overlay_holder').css('margin-top', '40px');
	//$("#overlay").height($("#overlay_holder .overlay_loaded_content").height() + 70);

	$("#overlay").height($("body").height() + 20);
	$('#overlay_holder').height($("#overlay_holder .overlay_loaded_content").height());
	//console.log($("#overlay").height() + ' - ' + $("#overlay_holder .overlay_loaded_content").height());
	if (scroll) {
		if ($(window).width() > 1024) {
			$(window).scrollTop(($("#overlay").height() - $("#overlay_holder .overlay_loaded_content").height()) / 2 - 50);
		} else {
			$(window).scrollTop(0);
		}
	}
}

