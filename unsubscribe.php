<?php

$type = trim(filter_input(INPUT_GET, 'type', FILTER_SANITIZE_MAGIC_QUOTES, array('options' => array('default' => ''))));
$name = trim(filter_input(INPUT_GET, 'name', FILTER_SANITIZE_MAGIC_QUOTES, array('options' => array('default' => ''))));
$email = trim(strtolower(filter_input(INPUT_GET, 'email', FILTER_SANITIZE_MAGIC_QUOTES, array('options' => array('default' => '')))));

if ($email != '') {
	//send data to the server
	$cu = curl_init();

	curl_setopt_array($cu, array(
		CURLOPT_URL => "http://64.233.245.241:43443/unsubscribe.php",
		//CURLOPT_URL => "http://localhost/phope/rxi/webservice/unsubscribe.php",
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => 'email=' . $email . '&name=' . $name . '&type=' . $type,
		CURLOPT_RETURNTRANSFER => true
	));

	$response = curl_exec($cu);
	curl_close($cu);
}

?>

<?php include('_header.php'); ?>

<div class="container-fluid">
	<div id="enroll-now" class="row row-1 one_column light-grey text-left" style='z-index:1000;'>
		<div class="container">
			<div id='applicationForm'>
				<br/><br/>

				<h2 class="center-alignment">Unsubscribe</h2>
				<br/><br/>

				<p class="center-alignment">
					Your email address was removed successfully from our systems.
					<br /><br /><br/><br/>
				</p>
			</div>
		</div>
	</div>
</div>

<div id="row-4" class="row-4 one_column white text-center">
	<div class="container">
		<span class="editor block-6">
			<br/><br/>
			<h3>Questions?</h3>
			<p>If you have any questions about Prescription Hope’s brand-name medication service, call our patient advocates at 1-877-296-HOPE (4673).</p>
			<br/>
		</span>
	</div>
</div>

<?php include('_footer.php'); ?>
