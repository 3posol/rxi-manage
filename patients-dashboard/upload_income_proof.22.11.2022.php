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
	'IncomeProof' 			=> ''
);
//echo count($_FILES['income_proof']['name']);
//print_r($_FILES['income_proof']);
//$image_info = getimagesize($_FILES["income_proof"]["tmp_name"]);
//echo $image_width = $image_info[0];
//echo $image_height = $image_info[1];

$success = false;
//echo count($_FILES['income_proof']['name']);

// My Codes starts here
$valid_files = true;
$valid_size = true;

$ur = rtrim($_SERVER['HTTP_REFERER'], '/\\');
$url = explode('/',$ur);
array_pop($url);
$final_url =  implode('/', $url); 
$data = array();
$uploaded_array = array();

//$message = 'Some data is missing, please fill all required fields and try again.';
$message = '';
function rmrf($dir) {
    foreach (glob($dir) as $file) {
        if (is_dir($file)) { 
            rmrf("$file/*");
            rmdir($file);
        } else {
            unlink($file);
        }
    }
}
function size_as_kb($yoursize) {
  if($yoursize < 1024) {
    return "{$yoursize} bytes";
  } elseif($yoursize < 1048576) {
    $size_kb = round($yoursize/1024);
    return "{$size_kb} KB";
  } else {
    $size_mb = round($yoursize/1048576, 1);
    return "{$size_mb} MB";
  }
}
if (isset($_FILES['income_proof'])) {

	$files_count = count($_FILES['income_proof']['name']);
	if($files_count > 5){
		$success = false;
		$message = 'Only 5 PDF files are allowed to upload.';
	}else{
			 $targetDir = "temp/".$_SESSION['PLP']['patient']->PatientID;
			//rmrf($targetDir);
			if (!file_exists($targetDir)) {
				mkdir($targetDir, 0777, true);
			}


			for($i=0; $i < $files_count; $i++) {

				if($_FILES['income_proof']["type"][$i] == 'application/pdf'){
					
				}else{ 
					$valid_files = 0;
					break;
				}

				if($_FILES['income_proof']["size"][$i] > 10485760){
						$valid_size = 0;
						break;
				}
			}

			for($j=0; $j < $files_count; $j++) {

				if($valid_files == true && $valid_size == true){

					// $fileName = time().'_'.basename($_FILES['income_proof']["name"][$j]);

					$filename   = uniqid() . "_" . time(); // 5dab1961e93a7_1571494241
					$extension  = pathinfo( $_FILES['income_proof']["name"][$j], PATHINFO_EXTENSION ); // jpg
					$fileName   = $filename . '.' . $extension; // 5dab1961e93a7_1571494241.jpg
					 $targetFilePath = $targetDir .'/'. $fileName;
					if(move_uploaded_file($_FILES['income_proof']['tmp_name'][$j], $targetFilePath)){
						$data[] = base64_encode(file_get_contents($final_url.'/'.$targetFilePath));
						$uploaded_array['name'][] = $_FILES['income_proof']["name"][$j];
						$uploaded_array['size'][] = size_as_kb($_FILES['income_proof']["size"][$j]);
						$uploaded_array['uploaded_name'][] = $fileName;
						$uploaded_array['uploaded_url'][] = $final_url.'/'.$targetFilePath;
						$success = true;
						$message = 'Files uploaded';
					}
				}elseif($valid_files == 0){
						$success = false;
						$message = 'We only accept PDF files, please make sure it is not password protected.';
					}elseif($valid_size == 0){
						$success = false;
						$message = 'File size cannot be greater than 10MB.';
					}else{
						$success = false;
						$message = 'Action failed for unknown reasons, please try submitting the form again.';
					}

			
			}
		
	}
	

}else{
	$success = false;
	$message = 'Action failed for unknown reasons, please try submitting the form again.';
}
$arrReturn = array(
	'success' 	=> $success,
	'message' 	=> $message,
	'uploaded_files' => $uploaded_array
);
echo json_encode($arrReturn);
die();

/*
//My Codes ends here 
if (isset($_FILES['income_proof']['type'])  && in_array($_FILES['income_proof']['type'], array('application/pdf')) ) {
	
	//
	//if($_FILES['income_proof']['type']=='image/png'){
	//	require_once('../enrollment/includes/fpdf/fpdf.php');
	//
	//	if(move_uploaded_file($_FILES['income_proof']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/html/patients-dashboard-new/temp/'.time().'_'.$_FILES['income_proof']['name'])){
	//		$image = $_SERVER['DOCUMENT_ROOT'].'/html/patients-dashboard-new/temp/'.time().'_'.$_FILES['income_proof']['name'];
	//		$pdf = new FPDF();
	//		$pdf->AddPage();
	//		$pdf->Image($image,0,0,200,200);
	//		$pdf->Output($_SERVER['DOCUMENT_ROOT'].'/html/patients-dashboard-new/temp/'.time().'_new.pdf','F');
	//		echo 'Created PDF';
	//	}
	//	else{
	//		echo 'Not moved';
	//	}
	//}
	//die('Done');
	
	$patient_data['income_proof_file'] = base64_encode(file_get_contents($_FILES['income_proof']['tmp_name']));

	$api_data = array(
		'command'		=> 'update_income_proof',
		'patient' 		=> $_SESSION['PLP']['patient']->PatientID,
		'access_code'	=> $_SESSION['PLP']['access_code'],
		'data'			=> $patient_data,
		'by'			=> (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
	);

	$response = api_command($api_data);

	if (isset($response->success) && $response->success == 1) {
		//success
		$success = true;
		$message = 'Thank you for submitting your documentation, a patient advocate will review it soon.';
		//header('Location: add_income_proof.php?success=1');
	} else {
		//fail
		$success = false;
		$message = 'Action failed for unknown reasons, please try submitting the form again.';
	}
	
	// for RXI rebuild
	//$cu = curl_init();					
	//curl_setopt_array($cu, array(
	//	CURLOPT_URL => "http://64.233.245.241:43444/webservice/patients/api2.php",
	//	CURLOPT_POST => 1,
	//	CURLOPT_POSTFIELDS => http_build_query($api_data),
	//	CURLOPT_RETURNTRANSFER => true
	//));
	//$response = curl_exec($cu);
	//curl_close($cu);	

} else {
	//invalid form
	$success = false;
	$message = 'We only accept PDF files, please make sure it is not password protected.';
}

$arrReturn = array(
	'success' 	=> $success,
	'message' 	=> $message
);
echo json_encode($arrReturn);
die();
*/
?>

<?php include('_header.php'); ?>

<div class="content">
	<div class="container">
		<h2>Submit Income Proof</h2>
		<br/>

		<div class="right-content">
			&nbsp;
		</div>

		<div class="left-content">
			<div id="fmMsg" class="<?=(($message != '' && !$success) ? 'error' : 'bold')?>"><?=$message?></div>

			<?php if (!$success || $message == '') { ?>

				* required fields<br/><br/>

				<form id="fmIncomeProof" action="add_income_proof.php" method="post" enctype="multipart/form-data">
					<label for="IncomeProof" class="label-long <?=((!$success) ? 'error' : '')?>">Income Proof (PDF file)*:</label>
					<input type="file" name="IncomeProof" id="IncomeProof" value="" class="<?=((!$success) ? 'error' : '')?>" />
					<br/><br/><br/>

					<input type="submit" name="btSave" id="btSave" value="Save">
					&nbsp;<a href="account.php">Cancel</a>
				</form>
			<?php } ?>
		</div>

		<div class="clear"></div>

	</div>
</div>

<script type="text/javascript">

jQuery().ready(function() {
	jQuery.validator.addMethod("ascii", function(value, element) { return this.optional(element) || /^[\x00-\x7F]*$/.test(value); }, "Please insert only alphanumeric characters.");

	jQuery("#fmIncomeProof").validate({
		rules: {
			IncomeProof: 	{ required: true, accept: "application/pdf" }
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
});

</script>

<?php include('_footer.php'); ?>
