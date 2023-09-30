<!DOCTYPE html>
<html lang="en-US" prefix="og: http://ogp.me/ns#">
<head>
	<meta charset="UTF-8" />
	<meta name="robots" content="noindex,follow"/>
	<title>Pescription Hope - Agents Back Office</title>

	<link rel="icon" href="images/favicon.jpg" type="image/x-icon" />
	<link rel="shortcut icon" href="images/favicon.jpg" type="image/x-icon" />

    <link rel="stylesheet" type="text/css" href="css/styles.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/events_loader.js"></script>

    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>

<div class="header">
	<img src="images/PH-Logo.png" /><br/>

	<?php if ($agent_logged) { ?>
		<br/>
		Welcome, <strong><?=$_SESSION['agent_first_name']?> <?=$_SESSION['agent_middle_name']?> <?=$_SESSION['agent_last_name']?></strong> |
		<a href="patients.php">Book of Business</a> |
		<?php if ($_SESSION['group_super_agent'] == 1) { ?><a href="group_patients.php">All My Group Agents' Book of Business</a> |<?php } ?>
		<?php if ($_SESSION['company_super_agent'] == 1) { ?><a href="company_patients.php">All My Company Agents' Book of Business</a> |<?php } ?>
		<?php if ($_SESSION['affiliate_super_agent'] == 1) { ?><a href="affiliate_patients.php">All My Affiliate Agents' Book of Business</a> |<?php } ?>
		<?php if ($_SESSION['mga_super_agent'] == 1) { ?><a href="mga_patients.php">All My Agents' Book of Business</a> |<?php } ?>
		<a href="change_password.php">Change Password</a> |
		<a href="logout.php">Logout</a>
	<?php } ?>
</div>
