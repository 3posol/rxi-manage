<?php
//echo phpinfo();
if (isset($_POST['Submit'])) {

 if (!empty($_FILES['upload']['name'])) {
	$ftp_server = 'sftp://64.233.245.241:22222';
	$sftpUsername  = 'rave.user';
	$sftpPassword  = 'd3v@[[3$$';
	$sftpPort      = 22222;

 $localfile = $_FILES['upload']['tmp_name'];

 $target_dir = "/var/www/html/sftptest/vendor/";
$target_file = $target_dir . basename($_FILES["upload"]["name"]);
echo "<pre>";print_r($_FILES);
echo getcwd();
echo $target_file;
if (move_uploaded_file($_FILES["upload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["upload"]["name"]). " has been uploaded.";
    } else {
       
        echo "Sorry, there was an error uploading your file.";
    }
    die();
 $fp = fopen('/var/www/html/sftptest/vendor/'.$_FILES["upload"]["name"], 'r');

 //print_r($_FILES);die();
 //curl_setopt($ch, CURLOPT_URL, 'ftp://ftp_login:password@ftp.domain.com/'.$_FILES['upload']['name']);
  $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $ftp_server . '/var/www/html/temp/'.$_FILES['upload']['name']);
 curl_setopt($ch, CURLOPT_FTPLISTONLY, 1);
curl_setopt($ch, CURLOPT_USERPWD, $sftpUsername . ':' . $sftpPassword);
curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
 curl_setopt($ch, CURLOPT_UPLOAD, 1);
 curl_setopt($ch, CURLOPT_INFILE, $fp);
 curl_setopt($ch, CURLOPT_INFILESIZE, filesize($_FILES['upload']['size']));

  $result = curl_exec ($ch);
 $error_no = curl_errno($ch);
 $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close ($ch);
echo 'error no='.$error_no;
        if ($error_no == 0) {
         $error = 'File uploaded succesfully.';
        } else {
         $error = 'File upload error.';
        }
 } else {
     $error = 'Please select a file.';
 }
 echo $error;
}