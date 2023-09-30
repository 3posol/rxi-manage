<?php 
require_once 'autoload.php'; 
use phpseclib\Net\SFTP;
/*$sftp = new SFTP('64.233.245.241','22222');

if (!$sftp->login('rave.user', 'd3v@[[3$$')) {
    throw new Exception('Login failed');
}else{
    echo 'connected';
    $sftp->chdir('/var/www/html/temp');
    $files = $sftp->nlist();
    echo "<pre>";print_r($files);
}*/
/*
3.21.146.177
ec2-user
W6A5J%Eh4au2
*/
/*
Host: 192.168.0.30
Username: rave.user
Password: r@v3@[[3$$
*/
/*$connection = ssh2_connect('192.168.0.30', 22);
if(ssh2_auth_password($connection, 'rave.user', 'r@v3@[[3$$')){
	echo 'connected';
}else{
	echo 'not connected';
}*/

//$host = '192.168.0.30';
/*$host = '192.168.0.41';
$port = 22;
$username = 'rave.user';
$password = 'r@v3@[[3$$';
$dataFile      = '/var/www/html/sftptest/vendor/why-labs-curriculum.jpg';
//$sftpServer    = '192.168.0.30';
$sftpServer    = '192.168.0.41';
$sftpUsername  = 'rave.user';
$sftpPassword  = 'd3v@[[3$$';
$sftpPort      = 22222;
$sftpRemoteDir = '/var/www/html/temp/';

$ftp_server = 'sftp://64.233.245.241:22222';
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $ftp_server . '/var/www/html/temp');
curl_setopt($curl, CURLOPT_FTPLISTONLY, 1);
curl_setopt($curl, CURLOPT_USERPWD, $sftpUsername . ':' . $sftpPassword);
curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
//curl_setopt($curl, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
//curl_setopt($curl, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_TIMEOUT, 1000);
$result = curl_exec($curl);
$error_no = curl_errno($curl);
////
if(curl_errno($curl)){
    echo 'Request Error:' . curl_error($curl).'....';
}
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
echo 'httpcode='.$httpcode.'....';
if($httpcode==0){
    die('Unable to connect');
}
else{
    die('Connected..');
}

die('=== End ===');*/

/*Host: prescriptionhope.com
Username: rave.user
Password: r@v3@[[3$$
*/
/*require_once 'autoload.php'; 
use phpseclib\Net\SFTP;
$sftp = new SFTP('www.prescriptionhope.com');

if (!$sftp->login('rave.user', 'r@v3@[[3$$')) {
	throw new Exception('Login failed');
}else{
	echo 'connected';
	$sftp->chdir('/var/www/html/');
	$files = $sftp->nlist();
	echo "<pre>";print_r($files);
}*/
/*$sftp = new SFTP('192.168.0.30');

if (!$sftp->login('rave.user', 'r@v3@[[3$$')) {
	throw new Exception('Login failed');
}else{
	echo 'connected';
	//$sftp->chdir('/var/www/main/wp-content/');
	$files = $sftp->nlist();
	echo "<pre>";print_r($files);
}*/
?>
<!DOCTYPE html>
<html>
<body>

<form action="upload.php" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="upload" id="upload">
    <input type="submit" value="Upload Image" name="Submit">
</form>

</body>
</html>