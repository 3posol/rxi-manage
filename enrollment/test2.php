<?php

require_once('includes/functions.php');

function activate_url_properties2 ($url_source, $website) {
	ini_set('session.cookie_domain', '.prescriptionhope.com');
	$RXI_API_URL = "http://64.233.245.241:43443/website/urls.php";
	$needs_redirect = false;

	$data = array(
		'command'		=> 'check_url_code',
		'url_code' 		=> $url_source
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

			setcookie("url_name", $response->url->name, time()+86400, "/", "prescriptionhope.com");
			$_COOKIE['url_name'] = $response->url->name;

			setcookie("url_code", $url_source, time()+86400, "/", "prescriptionhope.com");
			$_COOKIE['url_code'] = $url_source;

			setcookie("url_category", $response->url->category, time()+86400, "/", "prescriptionhope.com");
			$_COOKIE['url_category'] = $response->url->category;

			setcookie("url_fee_waived", $response->url->fee_waived, time()+86400, "/", "prescriptionhope.com");
			$_COOKIE['url_fee_waived'] = $response->url->fee_waived;

			setcookie("rate", $response->url->price_point, time()+86400, "/", "prescriptionhope.com");
			$_COOKIE['rate'] = $response->url->price_point;
		}
	}

	if ($website == 1) {
		header('Location: https://prescriptionhope.com/');
	} elseif ($needs_redirect) {
		header('Location: ' . $_SERVER['REQUEST_URI']);
	}
}

//

$url_source = filter_input(INPUT_GET, 'source', FILTER_DEFAULT, array('options' => array('default' => '')));
$website = filter_input(INPUT_GET, 'website', FILTER_DEFAULT, array('options' => array('default' => 0)));
if ($url_source != '') {
//	activate_url_properties($url_source, $website);
}

var_dump($_COOKIE);