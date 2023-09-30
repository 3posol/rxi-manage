<?php
require_once('includes/functions.php');
session_start();

//check login
$patient_logged_in = is_patient_logged_in();

if(!$patient_logged_in)
{
	header('Location: login.php');
}

//get data

$data = array(
	'command'		=> 'get_medication_and_providers',
	'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
	'access_code'	=> $_SESSION['PLP']['access_code']
);

$meds = api_command($data);

/*echo "<pre>";
print_r($meds);
echo "</pre>";
*/

include('_header.php');

?>
<script>
$(function() {
	// page is now ready, initialize the calendar...
	$('#calendar').fullCalendar({
		defaultView: 'month'
	})
});
</script>
<link href="/html/patients-dashboard-new/css/calender/assets/extra-libs/calendar/fullcalendar.min.css" rel="stylesheet" />
<link href="/html/patients-dashboard-new/css/calender/assets/extra-libs/calendar/calendar.css" rel="stylesheet" />

<style>
.content{background:transparent;}
body{padding-top:0px;}
nav.navbar.navbar-default.navbar-fixed-top.header-nav {
    position: static !important;
}
</style>

<div class="content topContent medication_main">
	<div class="container twoColumnContainerNo">
		<div class="row no-marginNo">
			<div class="col-sm-12 leftIconBox" style="padding-top:15px;">
				<!-- .navbar -->
				<?php include('_header_nav.php'); ?>
				<div class="card">
				
						<div class="row">
							<div class="col-lg-12">
								<div class="card-body b-l calender-sidebar">
									<div id="calendar"></div>
								</div>
							</div>
						</div>
					
				</div>
			</div>
		</div>
	</div>
</div>
<?php include('_footer.php'); ?>
<script src="/html/patients-dashboard-new/css/calender/assets/extra-libs/calendar/moment.min.js"></script>
<script src="/html/patients-dashboard-new/css/calender/assets/extra-libs/calendar/fullcalendar.min.js"></script>
<script src="/html/patients-dashboard-new/css/calender/assets/extra-libs/calendar/cal-init.js"></script>