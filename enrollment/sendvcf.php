<?php

require_once('includes/functions.php');
session_start();
if (isset($_SESSION['PLP']['patient']->account_username)) {
    $data = array(
        'command' => 'send_vcf',
        'email' => $_SESSION['PLP']['patient']->account_username,
    );
    $response = api_command($data);
    echo json_encode($response);
    die;
}

