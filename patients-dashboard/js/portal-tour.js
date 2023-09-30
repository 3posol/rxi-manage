jQuery(document).ready(function(){
	var current_url = window.location.href;
	
    // Instance the tour
	var tour = new Tour({
		debug: true,
		autoscroll: false,
		afterSetState: function (key, value) {
			if(key=='tour_current_step' && value==8){
				jQuery('#pymt_info>a').trigger('click');
			}
		},
		onNext: function(tour){ 
			//alert('tour.getCurrentStep() = '+ tour.getCurrentStep());
			if(tour.getCurrentStep()==5 && jQuery(window).width()<769){
				jQuery('#toggle').click();
			}
		},
		onShown: function(tour){
			jQuery('button[data-role="next"]').click(function(e){ tour.next(); });
			jQuery('button[data-role="prev"]').click(function(e){ tour.prev(); });
		},
		onEnd: function (tour){
			console.log('End the tour and redirect to dashboard');
			jQuery.ajax({
				type: 'post',
				url: basepath+'/_ajax_request.php',
				data: 'action=portal_tour&data=1&id='+id,
				success: function(data){
					console.log(data);
				},			
			});
			jQuery('#portal_tour_end').modal('show');			
			console.log('- in 4 -');			
		},
		template: "<div class='popover tour ph_tour'><div class='arrow'></div><h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='btn btn-default' data-role='next'>Next »</button><button class='btn btn-default endtour' data-role='end'>End tour</button></div>",
		steps: [
			{
				element: "#dashboard",
				title: "Dashboard Section",
				placement: "bottom",
				content: "This is your account dashboard. Here you will be able to have a quick overview of your entire account, upload any documents that we may need, and even add new medications to your Prescription Hope account.",
				path: basepath+"/dashboard.php",
				keyboard: false,
			},
			{
				element: "#medication",
				title: "Medication Section",
				placement: "bottom",
				content: "Under the medication section of your account, you will be able to see and update all the medications and healthcare providers added to your account at any time.",
				path: basepath+"/dashboard.php",
				keyboard: false,
			},
			{
				element: "#prescription_tab",
				title: "Prescription Section",
				placement: "bottom",
				content: "Here you will be able to see each of the prescriptions you have applied for through Prescription Hope. You can also see the status of that medication and update the dosage at any dosage time.",
				path: basepath+"/medication.php",
				keyboard: false,
				reflex: true
			},
			{
				element: ".firstmed",
				title: "Your Medication",
				placement: "bottom",
				content: "If you click on the medication here, you will be able to see all the information associated with that medication.",
				path: basepath+"/medication.php",
				keyboard: false,
				
			},
			{
				element: "#hcp",
				title: "Healthcare Provider Section",
				placement: "bottom",
				content: "Here you will be able to see each of the healthcare providers you have added to your Prescription Hope account. You can also see how many medications are currently under each healthcare provider.",
				path: basepath+"/medication.php",
				keyboard: false,				
			},
			{
				element: ".firstprov",
				title: "Your Healthcare Providers",
				placement: "bottom",
				content: "If you click on the healthcare provider here, you will be able to see all the information associated with that healthcare provider.",
				path: basepath+"/providers.php",
				keyboard: false,
				reflex: true
			},
			{
				element: "#billing",
				title: "Billing Section",
				placement: "bottom",
				content: "Here you will be able to see each invoice and edit your payment method at any time.",
				path: basepath+"/providers.php",
				keyboard: false				
			},
			{
				element: "#bill_info",
				title: "Invoice Section",
				placement: "bottom",
				content: "Here you will be able to see each of your monthly service fees, and you will have an invoice you can view or print at any time.",
				path: basepath+"/billing.php",
				keyboard: false,
				reflex: true
			},
			{
				element: "#pymt_info",
				title: "Payment Information Section",
				placement: "bottom",
				content: "Here you will be able to see and update your payment information at any time.",
				path: basepath+"/billing.php",
				keyboard: false				
			},
			{
				element: "#change_payment_info",
				title: "Change Payment Information",
				placement: "bottom",
				content: "Here you will be able to change your payment information at any time.",
				path: basepath+"/billing.php",
				keyboard: false				
			},
			{
				element: "#navbarDropdown1",
				title: "Account Settings",
				placement: "bottom",
				content: "Here you will be able to update any contact information associated with your account at any time.",
				path: basepath+"/billing.php",
				keyboard: false
			},
			{
				path: basepath+"/account.php",
				element: "#end_tour_link"
			},
		]
	}).init();
	//});
	
	// update tour flag in DB
	var currentStep = parseInt(tour.getCurrentStep());
	if( current_url.indexOf('account.php')>0 && !tour.ended() && currentStep>0 /*&& !portal_tour_flag*/ ){
		console.log('Current Step -'+tour.getCurrentStep());
		console.log('End the tour and show modal box');
		tour.end();
		jQuery.ajax({
			type: 'post',
			url: basepath+'/_ajax_request.php',
			data: 'action=portal_tour&data=1&id='+id,
			success: function(data){
				console.log(data);
			},			
		});
		jQuery('#portal_tour_end').modal('show');
	}
	
	if( !portal_tour_flag ){
		jQuery('#portal_tour').modal('show');	
	}
	
	// start tour on user request
	if(current_url.indexOf('dashboard.php')>0 && location.hash.substr(1)=='show_me_around'){
		if (window.location.href.indexOf('#') > -1) {
			history.pushState('', document.title, window.location.pathname);
		}
		setTimeout(function(){ jQuery('#portal_tour').modal('show'); }, 1000);
	}	
	
	// Start the tour
	jQuery(document).on('click', '#startTour', function(e){
		console.log('start the tour');
		jQuery('#portal_tour').modal('hide');
		e.preventDefault();
		if( jQuery(window).width()<769){
			jQuery('#toggle').click();
		}
		tour.restart();
	});
	
	jQuery(document).on('click', '#finishTour', function(e){	
		e.preventDefault();		
		jQuery('#portal_tour_end').modal('hide');
		window.location.href = basepath+'/dashboard.php';
	});
	
	jQuery(document).on('click', '.dismiss_tour', function(){
		jQuery.ajax({
			type: 'post',
			url: basepath+'/_ajax_request.php',
			data: 'action=portal_tour&data=1&id='+id,
			success: function(data){
				console.log(data);
			},			
		});
	});
});