<?php
require_once '/var/www/main/sftptest/vendor/autoload.php'; 
use phpseclib\Net\SFTP;

// connect and login to FTP server
/*$ftp_server = "staging-box.net";
$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
$ftp_username="formasafe";
$ftp_userpass="ajCl7?61";
$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);


var_dump($_FILES);
if (ftp_put($ftp_conn, $_FILES['fileToUpload']['name'],$_FILES['fileToUpload']['tmp_name'], FTP_BINARY))
  {
  echo "Successfully uploaded $file.";
  }
else
  {
  echo "Error uploading $file.";
  }*/

/*Host: prescriptionhope.com
Username: rave.user
Password: r@v3@[[3$$
*/

/*$sftp = new SFTP('www.prescriptionhope.com');

if (!$sftp->login('rave.user', 'r@v3@[[3$$')) {
	throw new Exception('Login failed');
}else{
	echo 'prescriptionhope.com connected';
	$sftp->chdir('/var/www/main/');
	$files = $sftp->nlist();
	echo "<pre>";print_r($files);
	if ($sftp->put( $_FILES['fileToUpload']['name'], $_FILES['fileToUpload']['tmp_name'], SFTP::SOURCE_LOCAL_FILE)){
		echo "Successfully uploaded.";
	}else{
		echo "Error uploading.";
	}
}*/
/*$ftp_server = "192.168.0.30";
$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
$ftp_username="rave.user";
$ftp_userpass="r@v3@[[3$$";
$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);*/


$sftp = new SFTP('192.168.0.30');

if (!$sftp->login('rave.user', 'r@v3@[[3$$')) {
	throw new Exception('Login failed');
}else{
	echo 'connected';
	//$sftp->chdir('/var/www/main/wp-content/');
	$files = $sftp->nlist();
	echo "<pre>";print_r($files);
}


