<?php
require_once('includes/functions.php');
session_start();

if (!isset($_SESSION['login_2fa_code'])) {
    header('Location: login.php');
}
$login_2fa = $_SESSION['login_2fa'];
$email = $login_2fa->patient->account_username;

$send_code = (isset($_POST['send_code']) && isset($_POST['send_code']));
if ($send_code) {
    $code = (isset($_COOKIE['login_2fa_code']) && $_COOKIE['login_2fa_code']) ? $_SESSION['login_2fa_code'] : rand(11111, 99999);
    $send_code_arr = [
        'command' => 'send_2fa_code',
        'email' => $email,
        'code' => $_SESSION['login_2fa_code'],
        'settings' => $_POST['settings'],
    ];
    setcookie('login_2fa_code', 1, time() + (60 * 10), "/");
//    setcookie('signup_2fa_code_temp', $code, time() + (60 * 10), "/");
    $_SESSION['login_2fa_code'] = $code;
    $send_code_res = api_command($send_code_arr);
    echo json_encode(['success' => 1, 'response' => $send_code_res]);
    die;
}

$submit = (isset($_POST['verify_code']) && isset($_POST['verify_code']));
if ($submit) {
    $message = '';
    $success = 1;
    $code = (isset($_POST['code']) && $_POST['code']) ? $_POST['code'] : null;
    if (!$code) {
        $message = 'Please enter code.';
        $success = 0;
    } elseif (!isset($_COOKIE['login_2fa_code'])) {
        $message = 'Code may expire please try again with resend code.';
        $success = 0;
    } elseif (isset($_COOKIE['login_2fa_code']) && $_COOKIE['login_2fa_code'] && $_SESSION['login_2fa_code'] == $code) {
        $_SESSION['login_2fa_verified'] = 1;
        $success = 1;
//        header('Location: login.php');
    } else {
        $message = 'Invalid code.';$success = 0;
    }
    echo json_encode(['message' => $message, 'success' => $success, 'location' => '/patients-dashboard/login.php']);
    die;
}



$get_contact_2fa = array(
    'command' => 'get_contact_2fa',
    'email_address' => $email,
);

$get_contact_2fa_res = api_command($get_contact_2fa);

if ($get_contact_2fa_res->success == 0) {
    header('Location: login.php');
}
$phoneNo = $get_contact_2fa_res->data->phone;
$phone = hide_mobile_no($phoneNo);
$email_mask = mask_email($email, 2, 2);
?>


<?php
$body_class = 'verification_body';
include('_header.php');
?>
<link rel="stylesheet" type="text/css" href="/enrollment/css/jvfloat.css">
<?php
$login_verification = 1;
require_once('../verification_global.php');
?>
<?php include('_footer.php'); ?>