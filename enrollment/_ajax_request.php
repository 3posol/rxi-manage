<?php

require_once('includes/functions.php');

$action = (isset($_POST['action'])) ? addslashes($_POST['action']) : '';
$data = (isset($_POST['data'])) ? $_POST['data'] : array();

switch ($action) {
	case 'get_security_question':
		if (isset($data['email'])) {
			$data = array(
				'command'		=> 'forgot_password_get_question',
				'email' 		=> addslashes($data['email'])
			);

			$response = api_command($data);
			echo json_encode($response);
		}

		break;
}