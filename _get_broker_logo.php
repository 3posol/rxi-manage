<?php

$dev_folder = (isset($_GET['dev'])) ? 'dev/' : '';

$broker = (isset($_GET['broker'])) ? trim($_GET['broker']) : '';
if ($broker != '') {
	//send data to the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/" . $dev_folder . "get_broker_logo.php",
		//CURLOPT_URL => "http://localhost/phope/webservice/get_broker_logo.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'broker=' . $broker,
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);

	if ($response != '') {
		$img_type = substr($response, 0, strpos($response, '--'));
		$img_string = substr($response, strpos($response, '--') + 2);

		$img = @imagecreatefromstring($img_string);
		if ($img !== false) {
			switch (strtolower($img_type)) {
				case 'gif':
					header("Content-type: image/gif");
					imagegif($img);
					break;

				case 'jpg':
					header("Content-type: image/jpeg");
					imagejpeg($img);
					break;

				case 'png':
					//add transparency
					imagecolortransparent($img, imagecolorallocatealpha($img, 0, 0, 0, 127));
					imagealphablending($img, false);
					imagesavealpha($img, true);

					header("Content-type: image/png");
					imagepng($img);
					break;
			}

			imagedestroy($img);
		}
	}
}