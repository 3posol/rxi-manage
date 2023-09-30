<?php

require_once('includes/functions.php');

$patient_id = '1349718';

$data = array(
	"aaabbb",
	"What is your mother's maiden name?",
	"aloha's",
	'2Sec0nds@&',
	'~!@#$%',
	'lulu "lala"',
	"lulu \"lala\""
);

//for encoding
$key = pack('H*', md5($patient_id));
$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

$api_data = array(
	'command'				=> 'test',
	'patient_id'			=> $patient_id,
	'iv'					=> base64_encode($iv)
);

foreach ($data as $value) {
	$api_data['data'][] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $value, MCRYPT_MODE_CFB, $iv));
}

$response = (array) api_command($api_data);

foreach ($data as $key => $value) {
	echo md5($value) . ' = ' . $response['data'][$key] . '<br>';
}

?>