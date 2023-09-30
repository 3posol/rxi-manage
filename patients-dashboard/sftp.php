<?php
$ftp_server = 'ftps://rxi.rxhope.com:22222/'; 
$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $ftp_server . 'temp/');
			curl_setopt($curl, CURLOPT_FTPLISTONLY, 1);
			curl_setopt($curl, CURLOPT_USERPWD, "rave.user:r@v3@[[3$$");
			curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
			curl_setopt($curl, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			$result = curl_exec($curl);
			$error_no = curl_errno($curl);
			//print_r($result);
			//echo $error_no;
			if (curl_errno($curl)) {
				echo $error_msg = curl_error($curl);
			}
			
			if($error_no == 0)
			{
				$list = explode("\n", $result);
				print_r($list);
				
			}
?>