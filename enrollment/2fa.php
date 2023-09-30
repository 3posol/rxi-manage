<?php
require_once('includes/functions.php');
session_start();
if (!isset($_SESSION['signup_2fa_code'])) {
    header('Location: register.php');
}

$resendsubmit = (isset($_POST['code_resend']) && isset($_POST['code_resend']));
//echo '<pre>';
//print_r($_SESSION['signup_2fa']);
//die;
if ($resendsubmit) {
    $code = (isset($_COOKIE['signup_2fa_code']) && $_COOKIE['signup_2fa_code']) ? $_SESSION['signup_2fa_code'] : rand(11111, 99999);

    $response = $_SESSION['signup_2fa'];
//    $_SESSION['login_2fa_code'] = $code;
    $is2Fa = true;
    $data = array(
        'command' => '2fa_verification',
        'email_address' => $response->data->email,
        'code' => $code,
        'name' => $response->data->first_name
    );

    $response = $verification_2fa = api_command($data);
    setcookie('signup_2fa_code', 1, time() + (60 * 10), "/");
    $_SESSION['signup_2fa_code'] = $code;
    echo json_encode(['success' => true, 'response' => json_decode($response)]);
    die;
}





$submit = (isset($_POST['code_submit']) && isset($_POST['code_submit']));
if ($submit) {
    $code = (isset($_POST['code']) && $_POST['code']) ? $_POST['code'] : null;
    if (!$code) {
        $message = 'Please enter code.';
    } elseif (!isset($_COOKIE['signup_2fa_code'])) {
        $message = 'Code may expire please try again with resend code.';
    } elseif (isset($_COOKIE['signup_2fa_code']) && $_COOKIE['signup_2fa_code'] && $_SESSION['signup_2fa_code'] == $code) {




        


        $response = $_SESSION['signup_2fa'];
        $data = array(
            'command' => 'welcome_email',
            'email_address' => $response->data->email,
            'name' => $response->data->first_name
        );
$response_welcome_email  = api_command($data);
//echo '<pre>';
//print_r($response_welcome_email);
//print_r($data);
//die;

        //            unset 2fa
        setcookie('signup_2fa_code', null, time() - (60 * 10), "/");
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
        header('Location: ../patients-dashboard/dashboard.php');
    } else {
        $message = 'Invalid code.';
    }
}
?>


<?php include('_header.php'); ?>
<link rel="stylesheet" type="text/css" href="/enrollment/css/jvfloat.css">

<?php require_once('../2fa_global.php'); ?>
<?php include('_footer.php'); ?>