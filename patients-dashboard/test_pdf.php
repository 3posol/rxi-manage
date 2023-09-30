<?php 


require_once('includes/functions.php');

session_start();

//check login
$patient_logged_in = is_patient_logged_in();
if (!$patient_logged_in) {
    header('Location: login.php');
}

?>
<!DOCTYPE html>
<html>
<body>

<form action="test_pdf.php" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload Image" name="submit">
</form>

</body>
</html>
<?php

$ur = rtrim($_SERVER['HTTP_REFERER'], '/\\');
$url = explode('/',$ur);
array_pop($url);
$final_url =  implode('/', $url); 
$data = array();
$uploaded_array = array();
//echo getcwd();
//require('/var/www/html/fpdf/fpdf.php');

if(isset($_POST) && !empty($_FILES)){
	require('/var/www/html/fpdf/fpdf.php');

$target_dir = "/var/www/html/patients-dashboard/temp/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image

    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
      //  echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 5000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else { 
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
       // echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    	 ob_start();

         $pdf_file_name = time().'.pdf';
			$image = $target_file;
			$pdf = new FPDF();
			$pdf->AddPage();
			$pdf->Image($image,20,40,170,170);
           // $pdf->Output();
			$pdf->Output( $target_dir.$pdf_file_name, 'F');
            ob_end_flush(); 
            
            $ur = rtrim($_SERVER['HTTP_REFERER'], '/\\');
            $url = explode('/',$ur);
            array_pop($url);
            $final_url =  implode('/', $url); 
            $targetDir = "temp/".$_SESSION['PLP']['patient']->PatientID;
            $folder_url = $final_url.'/'.$targetDir;
            
            $file_url = 'https://manage.prescriptionhope.com/patients-dashboard/temp/'.$pdf_file_name;
            $patient_data['income_proof_file'][] = base64_encode(file_get_contents($file_url));

         //   echo "<pre>";print_r($patient_data);die();

            $api_data = array(
            'command'       => 'update_income_proof',
            'patient'       => $_SESSION['PLP']['patient']->PatientID,
            'access_code'   => $_SESSION['PLP']['access_code'],
            'data'          => $patient_data,
            'by'            => (isset($_SESSION['PLP']['rxi_user']['id']) && $_SESSION['PLP']['rxi_user']['id'] > 0) ? $_SESSION['PLP']['rxi_user']['id'] : -1
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
			 $arrReturn = array(
    'success'   => $success,
    'message'   => $message
);
echo json_encode($arrReturn);
die();
    } else {
    	echo $_FILES['fileToUpload']['error'];
        echo "Sorry, there was an error uploading your file.";
    }
}
}