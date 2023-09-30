<?php

require_once('includes/functions.php');
if (isset($_GET['key']) && $_GET['key']) {
    $data = array(
        'command' => 'download_vcf',
        'id' => $_GET['key'],
    );

    $response = api_command($data);
}
if (isset($_GET['key_2']) && $_GET['key_2']) {
    $data = array(
        'command' => 'download_vcf',
        'p_id' => $_GET['key_2'],
    );

    $response = api_command($data);
}

$file_url = 'https://manage.prescriptionhope.com/phope.vcf';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: utf-8");
header("Content-disposition: attachment; filename=phope.vcf");
readfile($file_url);

