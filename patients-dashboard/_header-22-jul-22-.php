<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" lang="en-US" prefix="og: http://ogp.me/ns#">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" lang="en-US" prefix="og: http://ogp.me/ns#">
<![endif]-->
<!--[if IE 9]>
<html class="ie ie9" lang="en-US" prefix="og: http://ogp.me/ns#">
<![endif]-->
<!--[if !(IE 7) | !(IE 8) | !(IE 9)  ]><!-->
<html lang="en-US" prefix="og: http://ogp.me/ns#">
<!--<![endif]-->
<head>
	<meta name="facebook-domain-verification" content="jtj0xc4rjtz4wh8nq0vn45ehbp8whl" />
	<!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-WQN9FP7');</script>
  <!-- End Google Tag Manager -->
	<meta charset="UTF-8" />
	<meta name="robots" content="noindex,follow"/>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<title>Prescription Hope - Patient Dashboard</title>

	<link rel="icon" href="images/favicon.jpg" type="image/x-icon" />
	<link rel="shortcut icon" href="images/favicon.jpg" type="image/x-icon" />

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	
	<link rel="stylesheet" href="css/bootstrap-tour.min.css" />

	<link rel='stylesheet' id='wowbrands-fonts-css'  href='//fonts.googleapis.com/css?family=Raleway%3A300italic%2C400italic%2C600italic%2C700italic%2C800italic%2C400%2C500%2C800%2C700%2C600%2C300&#038;ver=4.0' type='text/css' media='all' />
	<link rel="stylesheet" type="text/css" href="css/datepicker.min.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<link rel="stylesheet" type="text/css" href="css/new-styles.css?v=<?php echo rand(); ?>">
	<link rel="stylesheet" type="text/css" href="css/responsive.css">

	<link href="https://fonts.googleapis.com/css?family=Raleway:100,200,300,400,500,600,700,800,900" rel="stylesheet">
	<script src="https://use.fontawesome.com/763e0c622e.js"></script>

	<script type="text/javascript" src="js/jquery.min.js"></script>
	<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>-->
	
	<!--<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>-->
	<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js" integrity="sha256-sPB0F50YUDK0otDnsfNHawYmA5M0pjjUf4TvRJkGFrI=" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js" integrity="sha256-vb+6VObiUIaoRuSusdLRWtXs/ewuz62LgVXg2f1ZXGo=" crossorigin="anonymous"></script>-->

<!--	<script type="text/javascript" src="js/jquery.validate.min.js"></script>
	<script type="text/javascript" src="js/additional-methods.min.js"></script>-->
	
	<script type='text/javascript' src='https://prescriptionhope.com/wp-content/themes/prescription_theme/js/jquery-ui.min.js'></script>
	<script type="text/javascript" src="js/jquery.validate.min.js"></script>
	<script type="text/javascript" src="js/additional-methods.min.js"></script>
	<!--<script type='text/javascript' src='js/bootstrap3.min.js'></script>-->

<!--	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>-->

	<script type='text/javascript' src='//prescriptionhope.com/wp-content/themes/prescription_theme/js/bootstrap.js?ver=4.0'></script>
	<script type='text/javascript' src='//prescriptionhope.com/wp-content/themes/prescription_theme/js/bootstrap-hover-dropdown.js?ver=4.0'></script>
	<script type="text/javascript" src="js/jquery.maskedinput.min.js"></script>
	<script type="text/javascript" src="js/pwstrength-bootstrap.min.js"></script>
	<script type='text/javascript' src='js/bootstrap-tour.min.js'></script>
	
	<?php if($_SERVER['SERVER_NAME'] == 'prescriptionhope.staging-box.net') { ?>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBOlfvGqz948FRGcCi35yHLbvWhLHHwSzQ&libraries=places"></script>
  <?php } else { ?>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBOlfvGqz948FRGcCi35yHLbvWhLHHwSzQ&libraries=places"></script>
  <!--<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB7SOnC9diP5QYqFoViOKXf8AJKSiU7PY8&libraries=places"></script>-->
  <?php } ?>
	
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/datepicker.min.js"></script>
	<script type="text/javascript" src="js/jquery.mask.js"></script>
	<script type="text/javascript" src="js/events_loader.js"></script>
	<script type="text/javascript" src="js/new-functions.js"></script>
	<script src='https://www.google.com/recaptcha/api.js'></script>
<?php
if ($patient_logged_in) {
/*?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-44317014-1"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', 'UA-44317014-1', {'custom_map': {'dimension1': '1'}});
	</script>
<?php } else { ?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-44317014-1"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', 'UA-44317014-1', {'custom_map': {'dimension1': '0'}});
	</script>
<?php */ } ?>
  <style type="text/css">
  .container{width:1170px;}
  @font-face {font-family: "Arial";src: url("fonts/ARI.ttf") format("truetype");}
  nav.navbar-default.header-nav {background: transparent;box-shadow: none;}
	ul#menu-header-menu {display: none;}
	.navbar-header {display: none;}
	@media screen and (max-width: 1024px) {
		nav.navbar-default.header-nav .container {width: auto !important;}
		.log-in {margin: 40px 20px;}
	}
	@media screen and (max-width: 768px) {
		nav.navbar-default.header-nav .container {width: auto !important;}
		.log-in {margin: 40px 20px;}
	}
	.collapse.navbar-collapse.main-nav.yamm.home-092017 {margin: 20px;}
  </style>
</head>
<body class="<?php echo (isset($body_class))?$body_class:'' ?>">
<!-- Google Tag Manager (noscript) --> 
 <noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WQN9FP7" height="0" width="0" style="display:none;visibility:hidden"></iframe>
  </noscript> <!-- End Google Tag Manager (noscript) -->
<div id="page-container">
  <nav class="navbar navbar-default navbar-fixed-top header-nav" role="navigation">
    <div class="container">
      <!-- Mobile display -->
      <div class="navbar-header">
        <!--a href="https://prescriptionhope.com"><img src="https://prescriptionhope.com/wp-content/uploads/2014/08/favicon.jpg" height="28" class="header-logo-small left only-mobile"></a-->
        <span class="only-mobile top-menu-title">My Account</span>
        <a href="https://prescriptionhope.com" class="svg">
          <object data="https://prescriptionhope.com/images/ph-logo.svg" type="image/svg+xml" height="32" class="top-menu-only-mobile only-mobile header-logo-small-svg" style="margin-left: calc(20% - 58px);"><img src="https://prescriptionhope.com/wp-content/uploads/2017/07/prescription-hope-logo-2017_07_13.png" height="32" class="header-logo-small left top-menu-only-mobile only-mobile"></object>
        </a>
        <button type="button" class="hidden navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <div class="only-mobile right hiddens" style="line-height: 50px;">
          <?php if ($patient_logged_in) { ?>
              <a href="logout.php" class="not_href_rmv top-menu-login my-account-link1" style="margin-right: 15px; color: #fff;">Log out</a>
          <?php } ?>
        </div>
      </div>
      <div class="only-mobile">
        <!-- Collect the nav links for toggling -->
        <div class="hidden collapse navbar-collapse navbar-ex1-collapse">
          <ul id="menu-mobile-menu" class="nav navbar-nav">
            <li id="menu-item-46" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-46 top-item"><a href="https://prescriptionhope.com/">Home</a></li>
            <li id="menu-item-44" class="menu-item menu-item-type-post_type menu-item-object-page active page_item page-item-13 active menu-item-44 top-item"><a href="https://manage.prescriptionhope.com/enrollment/register.php">Enrollment</a></li>
            <li id="menu-item-43" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-43 top-item"><a href="https://prescriptionhope.com/quick-answers/">Quick Answers</a></li>
            <li id="menu-item-42" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-42 top-item"><a href="https://prescriptionhope.com/about/">About Us</a></li>
            <li id="menu-item-41" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-41 top-item"><a href="https://prescriptionhope.com/contact/">Contact Us</a></li>
            <?php if ($patient_logged_in) { ?>
              <li id="menu-item-47" class="not_href_rmv menu-item menu-item-type-post_type menu-item-object-page menu-item-47 top-item"><a href="logout.php">Log out</a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
      <!--a href="https://prescriptionhope.com"><img src="https://prescriptionhope.com/wp-content/uploads/2017/07/prescription-hope-logo-2017_07_13.png" class="header-logo left only-desktop"></a-->
      <a href="https://prescriptionhope.com" class="svg">
        <object data="https://prescriptionhope.com/images/ph-logo.svg" type="image/svg+xml" width="241" height="90" class="top-menu-only-desktop only-desktop" style="margin-top: 20px;"><img src="https://prescriptionhope.com/wp-content/uploads/2017/07/prescription-hope-logo-2017_07_13.png" class="header-logo left top-menu-only-desktop only-desktop"></object>
      </a>
      <div class="right only-desktop nav-conatiner">
        <div class="collapse navbar-collapse main-nav yamm home-092017">
          <ul id="menu-header-menu" class="nav navbar-nav navbar-right">
            <li style="width: 0px; visibility: hidden;" id="" class="hiddens menu-item menu-item-type-post_type menu-item-object-page page_item top-item"><a href="">&nbsp;</a></li>
            <li id="menu-item-4218" class="hidden menu-item menu-item-type-post_type menu-item-object-page active page_item page-item-13 active menu-item-4218 top-item"><a href="https://manage.prescriptionhope.com/enrollment/register.php">Enrollment</a></li>
            <li id="menu-item-4221" class="hidden menu-item menu-item-type-post_type menu-item-object-page menu-item-4221 top-item"><a href="https://prescriptionhope.com/quick-answers/">Quick Answers</a></li>
            <li id="menu-item-4223" class="hidden menu-item menu-item-type-post_type menu-item-object-page menu-item-4223 top-item"><a href="https://prescriptionhope.com/about/">About Us</a></li>
            <li id="menu-item-4739" class="hidden menu-item menu-item-type-post_type menu-item-object-page menu-item-4739 top-item"><a href="https://prescriptionhope.com/contact/">Contact Us</a></li>
          </ul>
         
        </div>
		<div class="log-in">
		 <?php if ($patient_logged_in) { ?>
              <a href="logout.php" class="not_href_rmv top-menu-login my-account-link1" style="">Log out</a>
          <?php } ?>
		  </div>
      </div>
    </div>
  </nav>

  <div id="main_content">7
      
      
      <!-- Hotjar Tracking Code for https://manage.prescriptionhope.com/enrollment/register.php -->
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:3073263,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>