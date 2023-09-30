<?php
require_once('includes/functions.php');
session_start();


$click_changes = (isset($_POST['click_changes']) && isset($_POST['click_changes']));
if ($click_changes) {
    $_SESSION['click_changes'] = 1;
    echo json_encode(['success' => 1, 'response' =>  $_SESSION['click_changes']]);
    die;
}

