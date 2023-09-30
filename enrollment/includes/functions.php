<?php

#header('Location: maintenance.html');
require_once('includes/config.php');

function db_connect() {
    // DB connect
    //
	// couldn't use the html/includes/config.php file
    //
	$db_host = $GLOBALS['db_host'];
    $db_name = $GLOBALS['db_name'];
    $db_user = $GLOBALS['db_user'];
    $db_pass = $GLOBALS['db_pass'];
    //
    if (PHP_MAJOR_VERSION == 5) {
        mysql_pconnect($db_host, $db_user, $db_pass) or die(mysql_error());
        mysql_select_db($db_name) or die(mysql_error());
    } elseif (PHP_MAJOR_VERSION == 7) {
        $conn = mysqli_connect("p:" . $db_host, $db_user, $db_pass) or die(mysqli_error());
        mysqli_select_db($conn, $db_name) or die(mysqli_error());
        return $conn;
    }
    //
    // end DB connect
}

function api_command($data) {
    $cu = curl_init();

    curl_setopt_array($cu, array(
        CURLOPT_URL => $GLOBALS['RXI_API_URL'],
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true
    ));

    $response = curl_exec($cu);

    curl_close($cu);
    
    if(isset($_GET['testing'])){
        echo $GLOBALS['RXI_API_URL']; 
        print_r($data);
        var_dump($response);
    }
    /* if($_SERVER['REMOTE_ADDR'] == "122.168.124.60")
      {
      var_dump($data['command'], $response, '----');
      exit;
      } */
    //if ($data['command'] != 'check_login' && $_GET['debug']==1) {
    //	//var_dump($data['command'], $response, '----');
    //	//exit;
    //}

    if ($data['command'] == 'submit_enrollment') {
        //var_dump($data['command'], $response, json_decode($response), '----'); exit;
    }

    return json_decode($response);
}

function clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

function hide_mobile_no($number) {
    return '(***)-***-' . substr($number, -4);
//    return substr($number, 0, 2) . '******' . substr($number, -2);
}

function mask_email($email, $char_shown_front = 1, $char_shown_back = 1) {
    $mail_parts = explode('@', $email);
    $username = $mail_parts[0];
    $len = strlen($username);
    if ($len <= 4) {
        $char_shown_front = 1;
        $char_shown_back = 0;
    }
    if ($len < $char_shown_front or $len < $char_shown_back) {
        return implode('@', $mail_parts);
    }

    //Logic: show asterisk in middle, but also show the last character before @
    $mail_parts[0] = substr($username, 0, $char_shown_front)
            . str_repeat('*', $len - $char_shown_front - $char_shown_back)
            . substr($username, $len - $char_shown_back, $char_shown_back);

    return implode('@', $mail_parts);
}

function is_patient_logged_in() {
    global $session_key;

    $logged = false; //print_r($_SESSION);
    //// check if user login is redirected from patient dashboard
    //if( isset($_SESSION['PLP']['enrollment_form_account']) && $_SESSION['PLP']['enrollment_form_account']==1 ){ //die('111');
    //	$_SESSION[$session_key]['data'] = (array)$_SESSION['PLP']->patient;
    //	$logged = true;
    //	return true;
    //}
    //echo '<pre>';print_r($_SESSION); die;
//if(isset($_GET['debug']) && $_GET['debug']==1){ print_r($_SESSION); die;}
    if (isset($_SESSION) && isset($_SESSION[$session_key]['data']['id']) && isset($_SESSION[$session_key]['access_code'])) {
        $data = array(
            'command' => 'check_login',
            'patient' => $_SESSION[$session_key]['data']['id'],
            'access_code' => $_SESSION[$session_key]['access_code']
        );

        $response = api_command($data);

        if ($response->success == 1) {
            $logged = true;
        }
    }

    return $logged;
}

function get_data_mapping() {
    global $data_request;

    $map = array();
    foreach ($data_request as $key => $data) {
        if ($data[2] != '') {
            $map[$key] = $data[2];
        }
    }

    return $map;
}

function decode_patient_data($access_code, $iv, $data) {
    global $session_key;

    $decode_key = pack('H*', $access_code);
    $decode_iv = base64_decode($iv);

    foreach ($data as $key => $value) {
        if ($key == 'iv' || $key == 'medication' || $key == 'doctors') {
            $data[$key] = $value;
            continue;
        }

        $data[$key] = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $decode_key, base64_decode($value), MCRYPT_MODE_CFB, $decode_iv);
    }

    return $data;
}

function activate_url_properties($url_source) {
    ini_set('session.cookie_domain', '.prescriptionhope.com');
    //$RXI_API_URL = "http://64.233.245.241:43443/website/urls.php";
    //$RXI_API_URL = "http://64.233.245.241:43444/webservice/website/urls.php";
    $RXI_API_URL = "http://172.31.47.116/webservice/website/urls.php";
    $needs_redirect = false;

    $data = array(
        'command' => 'check_url_code',
        'url_code' => $url_source
    );

    //
    // API Call
    //

	$cu = curl_init();

    curl_setopt_array($cu, array(
        CURLOPT_URL => $RXI_API_URL,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true
    ));

    $response = curl_exec($cu);
    curl_close($cu);

    $response = json_decode($response);

    //

    if (isset($response->success) && $response->success == 1 && isset($response->url)) {
        if (isset($response->url->name) && isset($response->url->category) && isset($response->url->price_point) && isset($response->url->fee_waived)) {
            if (isset($_COOKIE['url_code']) && $_COOKIE['url_code'] != $url_source) {
                $needs_redirect = true;
            }

            setcookie("url_name", $response->url->name, time() + 86400, "/", "prescriptionhope.com");
            $_COOKIE['url_name'] = $response->url->name;

            setcookie("url_code", $url_source, time() + 86400, "/", "prescriptionhope.com");
            $_COOKIE['url_code'] = $url_source;

            setcookie("url_category", $response->url->category, time() + 86400, "/", "prescriptionhope.com");
            $_COOKIE['url_category'] = $response->url->category;

            setcookie("url_fee_waived", $response->url->fee_waived, time() + 86400, "/", "prescriptionhope.com");
            $_COOKIE['url_fee_waived'] = $response->url->fee_waived;

            setcookie("rate", $response->url->price_point, time() + 86400, "/", "prescriptionhope.com");
            $_COOKIE['rate'] = $response->url->price_point;
        }
    }

    if ($needs_redirect) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
    }
}

function fn_breadcrumbs($current_page) {
    $siteUrl = 'http://prescriptionhope.';
    $siteUrl .= ($_SERVER['SERVER_NAME'] == 'prescriptionhope.staging-box.net') ? 'staging-box.net' : 'com';
    $html = '<div class=""><div class="container">';
    $html .= '<p id="breadcrumbs">';
    $html .= '<span><a href="' . $siteUrl . '">Home</a></span> &raquo; ';
    $html .= '<span><a href="/enrollment/success.php">Enrollment</a></span> &raquo; ';
    $html .= '<span>' . $current_page . '</span>';
    $html .= '</p>';
    $html .= '</div></div>';
    echo $html;
}
