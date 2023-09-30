<?php /* if (isset($_SESSION['PLP']['patient_general_message']) && trim($_SESSION['PLP']['patient_general_message']) != '') {?>

	<div class="content messagebox">
		<h3 class="text-center"><?=nl2br(stripslashes($_SESSION['PLP']['patient_general_message_title']))?></h3>
		<br>
		<p><?=stripslashes($_SESSION['PLP']['patient_general_message'])?></p>
	</div>
	<br><br>

<?php } else */ ?>

<?php if(isset($_SESSION['PLP']['enrollment_form_account']) && $_SESSION['PLP']['enrollment_form_account']) { ?>

	<div class="content messagebox">
		<h3 class="text-center">Welcome to Prescription Hope</h3>
		<br>
		<p>
			Welcome to your Prescription Hope dashboard where medication management is at your fingertips.
			<br><br>
			Within 48 hours a representative will contact you to explain the next steps to receiving your medication. Please be on the lookout for a phone call and a welcome packet from us. This welcome packet will have important information for you to keep for your records as well as requests for documentation such as proof of income, which is needed to complete the process.
			<br><br>
			We will also be sending documentation to your healthcare provider to request signatures from them as well as the necessary prescriptions. Please contact your doctor's office and let them know we will be reaching out to them soon, just so we're all on the same page. It'll help speed up the enrollment process.
		</p>
	</div>
	<br><br>

<?php } ?>

</div>
<script>
jQuery("#toggle").click(function() {
  jQuery(this).toggleClass("on");
  jQuery("#menu").slideToggle();
});
</script>
<?php if($_SESSION['PLP']['patient']->DateDisenrolled == '' && !($_SESSION['PLP']['patient']->PastDueBalance > 0 && !$billing_information->chargeback) ){ ?>
<script>
	var basepath = '<?= $basepath?>';
var id = '<?php echo $_SESSION['PLP']['patient']->PatientID?>';
var portal_tour_flag = <?php echo (isset($_SESSION['PLP']['patient']->MetaData) && $_SESSION['PLP']['patient']->MetaData->portal_tour==1) ? 1 : 0; ?>;
</script>
<script type='text/javascript' src='js/portal-tour.js'></script>
<?php } ?>
</div>

</body>
</html>
