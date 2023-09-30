<?php
require_once('includes/functions.php');
session_start();
if (!isset($_SESSION['signup_2fa_code'])) {
    header('Location: ../patients-dashboard/login.php');
}

$signup_2fa = $_SESSION['signup_2fa'];
$email = $signup_2fa->data->email;
$send_code = (isset($_POST['send_code']) && isset($_POST['send_code']));
if ($send_code) {
    $code = (isset($_COOKIE['signup_2fa_code']) && $_COOKIE['signup_2fa_code']) ? $_SESSION['signup_2fa_code'] : rand(11111, 99999);
    $send_code_arr = [
        'command' => 'send_2fa_code',
        'email' => $email,
        'code' => $_SESSION['signup_2fa_code'],
        'settings' => $_POST['settings'],
    ];
    setcookie('signup_2fa_code', 1, time() + (60 * 60), "/");
    $_SESSION['signup_2fa_code'] = $code;
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
    } elseif (!isset($_COOKIE['signup_2fa_code'])) {
        $message = 'Code may expire please try again with resend code.';
        $success = 0;
    } elseif (isset($_COOKIE['signup_2fa_code']) && $_COOKIE['signup_2fa_code'] && $_SESSION['signup_2fa_code'] == $code) {




        $response = $_SESSION['signup_2fa'];

        setcookie(clean($response->data->email), 1, time() + (60 * 60 * 24 * 360), "/");


        $data = array(
            'command' => 'welcome_email',
            'email_address' => $response->data->email,
            'name' => $response->data->first_name
        );
        $response_welcome_email = api_command($data);
//echo '<pre>';
//print_r($response_welcome_email);
//print_r($data);
//die;
        //            unset 2fa
        setcookie('signup_2fa_code', null, time() - (60 * 60), "/");
        unset($_SESSION['signup_2fa_verified']);
        unset($_SESSION['signup_2fa_code']);
        unset($_SESSION['signup_2fa']);

        $_SESSION[$session_key]['data']['id'] = $response->applicant;
        $_SESSION[$session_key]['access_code'] = md5($response->data->email);
        $_SESSION['PLP']['access_code'] = md5($response->data->email);
        $_SESSION['PLP']['data'] = (array) $response->data;
        $_SESSION['PLP']['patient'] = (object) array_merge((array) $response->data, array(
                    'force_login' => 1,
                    'PatientID' => $response->applicant,
                    'account_username' => $response->data->email
        ));
        $_SESSION['PLP']['incomplete_application'] = '';

        $_SESSION['incomplete_application_org'] = 0;
//        header('Location: ../patients-dashboard/dashboard.php');





//        header('Location: login.php');
    } else {
        $message = 'Invalid code.';
        $success = 0;
    }
    echo json_encode(['message' => $message, 'success' => $success, 'location' => '/patients-dashboard/dashboard.php']);
    die;
}



$get_contact_2fa = array(
    'command' => 'get_contact_2fa',
    'email_address' => $email,
);
$get_contact_2fa_res = api_command($get_contact_2fa);
if ($get_contact_2fa_res->success == 0) {
    header('Location: ../patients-dashboard/login.php');
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
require_once('../verification_global.php');
?>
<?php include('_footer.php'); ?>

