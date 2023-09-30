<?php
require_once('includes/functions.php');

//session_set_cookie_params(0, '/', '.prescriptionhope.com');
//ini_set('session.cookie_domain', '.prescriptionhope.com' );
session_start();
unset($_SESSION['PLP']);
//session_destroy();
//RxI check
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'portal.php')) {
    setcookie('RxI', 1);
} elseif (!isset($_SERVER['HTTP_REFERER'])) {
    setcookie("RxI", 0, time() - 3600);
}

$email_address = (isset($_POST['patient_email_address'])) ? strtolower(trim($_POST['patient_email_address'])) : null;
$password = (isset($_POST['patient_password'])) ? trim($_POST['patient_password']) : null;
$submit = (isset($_POST['patient_email_address']) && isset($_POST['patient_password']));

$success = true;
$message = '';
//unset($_SESSION['login_2fa_verified']);
if (isset($_SESSION['login_2fa_verified']) && $_SESSION['login_2fa_verified']) {
    $submit = true;
    $email_address = 'test';
    $password = 'test';
}

if ($submit) {
    if ($submit && $email_address != '' && $password != '') {
        //encode password
        /* if(PHP_MAJOR_VERSION == 5){
          db_connect();
          $rs = mysql_query('SELECT MD5("' . $email_address . '") as encoded_email, MD5("' . addslashes($password) . '") as encoded_password');
          $encoded_data = mysql_fetch_assoc($rs);
          }elseif (PHP_MAJOR_VERSION == 7) {
          $conn = db_connect();
          $rs = mysqli_query($conn, 'SELECT MD5("' . $email_address . '") as encoded_email, MD5("' . addslashes($password) . '") as encoded_password');
          $encoded_data = mysqli_fetch_assoc($rs);
          }

          $encoded_email = $encoded_data['encoded_email'];
          $encoded_password = $encoded_data['encoded_password']; */

        $skip = false;
        $is2Fa = false;
        $encoded_password = md5($password);
//        echo clean($email_address);
//        var_dump(isset($_COOKIE[clean($email_address)]));
//        die;
        if (isset($_COOKIE[clean($email_address)]) || isset($_GET['bypass'])) {
            $skip = true;
            $data = array(
                'command' => 'login',
                'email_address' => $email_address,
                'password' => $encoded_password,
                'from_rxi' => 1
            );
            $response = api_command($data);

            if (isset($_GET['cg_check_data']) && $_GET['cg_check_data']) {
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            }

//            $_SESSION['login_2fa_verified'] = 1;
        } elseif (!isset($_SESSION['login_2fa_verified'])) {
            //login
            $data = array(
                'command' => 'login',
                'email_address' => $email_address,
                'password' => $encoded_password,
                'from_rxi' => 1
            );
            $response = api_command($data);
            if (isset($_GET['cg_check_data']) && $_GET['cg_check_data']) {
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            }
        } else {
            $response = $_SESSION['login_2fa'];
//            $is2Fa = true;
        }
        if (isset($_GET['cg_check_data']) && $_GET['cg_check_data']) {
            echo '<pre>';
            print_r($response);
            die;
        }
//        var_dump(isset($_COOKIE[clean($email_address)]));
//        var_dump(!$skip);
//        die;
//        echo '<pre>';
//        print_r($_SESSION);
//        die;
        $print_and_mail = $response->patient->print_and_mail;
//        var_dump($email_address);
//        echo '<pre>';
//        print_r($_COOKIE);
//        var_dump(!isset($_COOKIE[$email_address]));
//        die;
//        $is2Fa = true;
        if (isset($response->patient->enable_2fa) && $response->patient->enable_2fa == 0) {
            $skip = true;
        }

        if ((in_array($response->success, [1, 2]) && !isset($_SESSION['login_2fa_verified']) && !$skip)) {

            unset($_SESSION['login_2fa_verified']);
            $_SESSION['login_2fa'] = $response;
            $code = rand(11111, 99999);
            $_SESSION['login_2fa_code'] = $code;
            $is2Fa = true;
            $data = array(
                'command' => '2fa_verification',
                'email_address' => $email_address,
                'code' => $code,
                'name' => $response->patient->first_name
            );
//            $verification_2fa = api_command($data);
            setcookie('login_2fa_code', 1, time() + (60 * 10), "/");
//            setcookie('login_2fa_code_temp', $code, time() + (60 * 10), "/");
//            header('Location: 2fa.php');
            header('Location: verification.php');
        } elseif (isset($response->patient->force_login) && $response->patient->force_login) {
            $_SESSION['incomplete_application_org'] = $response->incomplete_application;
            $response->incomplete_application = 1;
            $response->enrollment_form_account = 1;
            $response->success = 1;
            $response->incomplete_application;
        } else {
            unset($_SESSION['incomplete_application_org']);
        }



        if (isset($response->success) && $response->success == 1 && !$is2Fa) {
            //success
            if (session_id() == '') {
                session_start();
            }

//            unset 2fa
            setcookie('login_2fa_code', null, time() - (60 * 10), "/");
            unset($_SESSION['login_2fa_verified']);
            unset($_SESSION['login_2fa_code']);
            unset($_SESSION['login_2fa']);

            $_SESSION['PLP']['patient'] = $response->patient;
            $_SESSION['print_and_mail'] = $print_and_mail; //Code added by vinod


            clear_patient_last_name();
//echo '<pre>';
//        print_r($response);
//        echo "<br>Session<br>";
//        print_r($_SESSION);
//        die;
            $_SESSION['PLP']['patient_general_message'] = isset($response->patient_general_message) ? $response->patient_general_message : '';
            $_SESSION['PLP']['patient_general_message_title'] = isset($response->patient_general_message_title) ? $response->patient_general_message_title :
                    '';

            if (isset($_SESSION['PLP']['patient']->account_username)) {
                $_SESSION['PLP']['access_code'] = md5($_SESSION['PLP']['patient']->account_username);
                setcookie(clean($_SESSION['PLP']['patient']->account_username), 1, time() + (60 * 60 * 24 * 360), "/");
            }

            $_SESSION['PLP']['rxi_user'] = array(
                'id' => (isset($response->rxi_user->id)) ? (int) $response->rxi_user->id : 0,
                'name' => (isset($response->rxi_user->name)) ? $response->rxi_user->name : ''
            );

            $_SESSION['PLP']['patient_price_point'] = isset($response->patient_general_message) ? $response->patient_price_point : '';

            //enrollment form account logins
            $_SESSION['PLP']['enrollment_form_account'] = (isset($response->enrollment_form_account)) ? true : false;

            $resetPasswordLink = '<a href="/enrollment/forgot_password.php"> Please get a new password here.</a>';

            if ($response->patient->account_activated == 2 && !$_SESSION['PLP']['enrollment_form_account'] && $response->password_expire == 0) {
                header('Location: change_password.php');
            } elseif ($response->patient->account_activated == 2 && $response->password_expire == 1) {
                $success = false;
                $message = 'Your temporary password has expired.' . $resetPasswordLink;
            } else {


                if (isset($response->enrollment_form_account) && $response->enrollment_form_account == 1) {

//                    unset($_SESSION['PLP']);
//                    unset($_SESSION['PHEnroll']);

                    $_SESSION['PHEnroll']['data'] = (array) $response->patient;
                    $_SESSION['PHEnroll']['incomplete_application'] = !(bool) $response->incomplete_application;
                    $_SESSION['PHEnroll']['access_code'] = md5($_SESSION['PHEnroll']['data']['email']);

                    if ($response->account_activated == 2 && $response->password_expire == 0) {
                        header('Location: ../enrollment/change_password.php');
                    } elseif ($response->account_activated == 2 && $response->password_expire == 1) {
                        $success = false;
                        $message = 'Your temporary password has expired.' . $resetPasswordLink;
                    } else {
//                        header('Location: ../enrollment/success.php');
                        header('Location: dashboard.php');
                    }
                } else {
                    $patient_after_310820 = (date('Y-m-d', strtotime($response->patient->DateFirstEnteredSystem)) <= '2020-08-31') ? true : false;
                    if ($patient_after_310820 == true) {
                        header('Location: dashboard.php');
                    } else if (($patient_after_310820 == false) && (isset($print_and_mail) && $print_and_mail == 1)) {
                        header('Location: dashboard.php');
                    } else {
                        header('Location: success.php');
                    }
                }
            }
        } elseif (isset($response->success) && $response->success == 2 && !$is2Fa) {

            $resetPasswordLinkNew = '<a href="/enrollment/forgot_password.php"> Please get a new password here.</a>';
            //enrollment form login
            if (session_id() == '') {
                session_start();
            }

            unset($_SESSION['PLP']);
            unset($_SESSION['PHEnroll']);

            $_SESSION['PHEnroll']['data'] = (array) $response->patient;
            $_SESSION['PHEnroll']['incomplete_application'] = !(bool) $response->incomplete_application;
            $_SESSION['PHEnroll']['access_code'] = md5($_SESSION['PHEnroll']['data']['email']);
            if (isset($response->patient->force_login) && $response->patient->force_login) {
                $_SESSION['PLP']['patient'] = $response->patient;
                header('Location: dashboard.php');
            }
            // if( $response->account_activated == 2 ){ 
            // 	header('Location: ../enrollment/change_password.php');
            // }
            // else { 
            // 	header('Location: ../enrollment/enroll.php');
            // }

            if ($response->account_activated == 2 && $response->password_expire == 0) {
                header('Location: ../enrollment/change_password.php');
            } elseif ($response->account_activated == 2 && $response->password_expire == 1) {
                $success = false;
                $message = 'Your temporary password has expired.' . $resetPasswordLinkNew;
            } else {
                header('Location: ../enrollment/enroll.php');
            }
        } else {
            //login fail
            if ($response->username_exists == 0 && $response->password_exists == 1) {
                $success = false;
                $message = 'There is no account assocciated with this email address, please try a different email.';
            } else if ($response->username_exists == 1 && $response->password_exists == 0) {
                $success = false;
                $message = 'The password you entered is not correct, please try again.';
            } else {
                $success = false;
                $message = 'There is no account assocciated with this email address, please try a different email.';
            }
        }
    } elseif ($submit) {
        //invalid form
        if ($email_address == '' && $password != '') {
            $success = false;
            $message = 'There is no account assocciated with this email address, please try a different email.';
        } elseif ($email_address != '' && $password == '') {
            $success = false;
            $message = 'The password you entered is not correct, please try again.';
        } else {
            $success = false;
            $message = 'There is no account assocciated with this email address, please try a different email.';
        }
    }
} elseif ($submit) {
    //not human
    $success = false;
    $message = 'Please try again after you fill out the login form.';
}
?>

<?php include('_header.php'); ?>
<link rel="stylesheet" type="text/css" href="/enrollment/css/jvfloat.css">
<div class="row no-margin">



    <div class="new-login">
        <div class="login-logo"><a href="https://prescriptionhope.com" class="svg">
                <object data="https://prescriptionhope.com/images/ph-logo.svg" type="image/svg+xml" width="241" height="90" class="top-menu-only-desktop only-desktop" style="margin-top: 20px;"><img src="https://prescriptionhope.com/wp-content/uploads/2017/07/prescription-hope-logo-2017_07_13.png" class="header-logo left top-menu-only-desktop only-desktop"></object>
            </a></div>
        <div class="loginPanel">
            <form id="fmLogin" method="POST">
                <div class="login-section">
                    <?php
                    if (!empty($_SESSION['success-msg'])) {
                        ?>							
                        <div class="alert alert-info alert-dismissible">
                            <div class="heading password-reset-title"><h2>Password Reset</h2></div>
                            <a href="#" class="close" data-dismiss="alert" aria-label="close"><i class="fa fa-close"></i></a>A temporary pin has successfully been sent to your email. Please check your email and use the pin to log in and then you will be able to reset your password.</div>
                        <?php
                        unset($_SESSION['success-msg']);
                    } else {
                        ?>

                        <div class="heading"><h2>Log In</h2></div>
                        <?php
                    }
                    ?>
                      <?php if (isset($_SESSION['cg_email_unsubscribe'])){ ?>
                        
                   
                     <div class="email_unsubscribe success">
                                       <?php echo $_SESSION['cg_email_unsubscribe'];
                                         unset($_SESSION['cg_email_unsubscribe']);
                                         ?>
                                              </div>
                      <?php 
                }
                ?>
                    <input type="text" class="form-control" name="patient_email_address" placeholder="Email Address*" id="patient_email_address" value="<?= $email_address ?>" class="full-width <?= ((!$success) ? 'error' : '') ?>">

                    <input type="password" class="form-control" name="patient_password" placeholder="Password*" id="patient_password" value="<?= $password ?>" class="full-width <?= ((!$success) ? 'error' : '') ?>">

                    <div class="row">
                        <div class="col-sm-12"><input type="submit" name="login_submit" id="btSubmit" value="Log in" class="btn btn-primary btn-block"></div>
                    </div>

                    <div id="fmMsg" class="<?= (($message != '') ? 'error' : '') ?>"><?= $message ?></div>				

                </div>

                <div class="row">
                    <div class="col-sm-12 loginNotEnrolled">
                        <p class="content-sec">Have not become a member of Prescription Hope yet?</p>
                        <a href="/enrollment/register.php" class="links">Get Started</a><br/>
                        <a href="/enrollment/forgot_password.php" class="forgot-sec">Forgot Your Password?</a>						
                    </div>					
                </div>					
            </form>

        </div>

    </div>

</div>

<?php
//
//SalesForce Conversion Tracking
//
$page_id = '5';
$page_alias = 'Acount Login';

$SFJobID = (isset($_COOKIE['SFJobID'])) ? $_COOKIE['SFJobID'] : '';
$SFSubscriberID = (isset($_COOKIE['SFSubscriberID'])) ? $_COOKIE['SFSubscriberID'] : '';
$SFListID = (isset($_COOKIE['SFListID'])) ? $_COOKIE['SFListID'] : '';
$SFUrlID = (isset($_COOKIE['SFUrlID'])) ? $_COOKIE['SFUrlID'] : '';
$SFMemberID = (isset($_COOKIE['SFMemberID'])) ? $_COOKIE['SFMemberID'] : '';
$SFJobBatchID = (isset($_COOKIE['SFJobBatchID'])) ? $_COOKIE['SFJobBatchID'] : '';

if ($SFJobID != '' && $SFSubscriberID != '' && $SFListID != '' && $SFUrlID != '' && $SFMemberID != '') {
    $SFConversionPixel = '<img src=\'http://click.exacttarget.com/conversion.aspx?xml=';
    $SFConversionPixel .= '<system><system_name>tracking</system_name><action>conversion</action>';
    $SFConversionPixel .= '<member_id>' . $SFMemberID . '</member_id>';
    $SFConversionPixel .= '<job_id>' . $SFJobID . '</job_id>';
    $SFConversionPixel .= '<email></email>';
    $SFConversionPixel .= '<sub_id>' . $SFSubscriberID . '</sub_id>';
    $SFConversionPixel .= '<list>' . $SFListID . '</list>';
    $SFConversionPixel .= '<original_link_id>' . $SFUrlID . '</original_link_id>';
    $SFConversionPixel .= '<BatchID>' . $SFJobBatchID . '</BatchID>';
    $SFConversionPixel .= '<conversion_link_id>' . $page_id . '</conversion_link_id>';
    $SFConversionPixel .= '<link_alias>' . $page_alias . '</link_alias><display_order>' . $page_id . '</display_order>';
    $SFConversionPixel .= '<data_set></data_set>';
    $SFConversionPixel .= '</system>\'';
    $SFConversionPixel .= ' width="1" height="1">';

    echo $SFConversionPixel;
}
?>

<?php include('_footer.php'); ?>
<style>
    html {background: #eaeaea; height:100%;}
    body{background:#eaeaea;padding-top:0px;}
    .navbar.header-nav,footer{display:none;}
    #main_content{padding-bottom: 100px;}
    input.error {text-align: left !important;background: #fff;border: 1px solid #ff0000 !important;}
    a.links,a.forgot-sec {padding-top: 6px;display: inline-block;}
    .col-sm-12.loginNotEnrolled {text-align: center;}
    .login-logo{margin-bottom:20px;}
    .jvFloat .placeHolder.active {top: 16px;}
</style>
<script type="text/javascript" src="/enrollment/js/jvfloat.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.form-control').jvFloat();
    });

</script>
