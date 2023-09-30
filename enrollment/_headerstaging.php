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
  <?php if (basename($_SERVER['PHP_SELF']) == 'register.php') { ?>
      <script>
        dataLayer = [{
          'pageCategory': 'application-page',
          'visitorType': 'low-value'
        }];
      </script>
  <?php } elseif (basename($_SERVER['PHP_SELF']) == 'success.php') { ?>
      <script>
        dataLayer = [{
          'pageCategory': 'complete-application',
          'visitorType': 'high-value'
        }];
      </script>
  <?php } ?>

	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-WQN9FP7');</script>
	<!-- End Google Tag Manager -->

	<meta charset="UTF-8" />
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
  <?php

  $page_title = 'Patient Dashboard';
  switch (basename($_SERVER['PHP_SELF'], '.php')) {
      case 'register':
          $page_title = 'Enrollment Create Account Registration';
          break;

      case 'enroll':
          $page_title = 'Enrollment View Application';
          break;

      case 'success':
          $page_title = 'Enrollment Complete Application';
          break;
  }

  ?>
	<title>Prescription Hope - <?=$page_title?></title>
  <link rel="profile" href="http://gmpg.org/xfn/11" />
  <link rel="pingback" href="https://prescriptionhope.com/xmlrpc.php" />
  <link rel="icon" href="https://prescriptionhope.com/wp-content/uploads/2014/08/favicon.jpg" type="image/x-icon" />
  <link rel="shortcut icon" href="https://prescriptionhope.com/wp-content/uploads/2014/08/favicon.jpg" type="image/x-icon" />

  <meta name="robots" content="noindex,follow"/>

  <!--link rel='stylesheet' id='wowbrands-custom-css'  href='https://prescriptionhope.com/wp-content/themes/prescription_theme/custom-style.css' type='text/css' media='all' /-->
  
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  
  

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<?php if (basename($_SERVER['PHP_SELF']) == 'register.php' || basename($_SERVER['PHP_SELF']) == 'enroll.php') {?> 
  <link rel='stylesheet' id='wowbrands-fonts-css'  href='fonts/ARI.ttf' type='text/css' media='all' />
  <style type="text/css">
    @font-face {
    font-family: "Arial";
   /* src: url("fonts/Arial.eot");*/
    src: url("fonts/ARI.ttf") format("truetype");
  }
  </style>
<?php } else{ ?>
  <link rel='stylesheet' id='wowbrands-fonts-css'  href='//fonts.googleapis.com/css?family=Raleway%3A300italic%2C400italic%2C600italic%2C700italic%2C800italic%2C400%2C500%2C800%2C700%2C600%2C300&#038;ver=4.0' type='text/css' media='all' /> 
<?php } ?>
	<link rel="stylesheet" href="font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="css/datepicker.min.css">
	<link rel="stylesheet" type="text/css" href="css/owl.carousel.min.css">
	<link rel="stylesheet" type="text/css" href="css/owl.theme.default.min.css">
	<link rel="stylesheet" type="text/css" href="css/enrollstyle.css">
  <!--link href="js/jquery-ui/jquery-ui.min.css" rel="stylesheet"-->
  <link rel="stylesheet" type="text/css" href="css/new-styles.css">
  <link rel="stylesheet" type="text/css" href="css/responsive.css">
  <link rel="stylesheet" type="text/css" href="css/jvfloat.css">
  <link rel="stylesheet" type="text/css" href="css/load-style.css">
  
  <?php if (basename($_SERVER['PHP_SELF']) != 'register.php' && basename($_SERVER['PHP_SELF']) != 'enroll.php') {?>
   <link href="https://fonts.googleapis.com/css?family=Raleway:100,200,300,400,500,600,700,800,900" rel="stylesheet">
  <?php } ?>
	
	<script src="https://use.fontawesome.com/763e0c622e.js"></script>

  <?php if (basename($_SERVER['PHP_SELF']) == 'enroll.php') { ?>
    <!--<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB7SOnC9diP5QYqFoViOKXf8AJKSiU7PY8&libraries=places"></script>-->
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBOlfvGqz948FRGcCi35yHLbvWhLHHwSzQ&libraries=places"></script>
  <?php } ?>
  <script type="text/javascript" src="js/jquery.min.js"></script>
  <!--script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script-->
  <script type="text/javascript" src="js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="js/additional-methods.min.js"></script>
  <!--script type="text/javascript" src="js/jquery.maskedinput.min.js"></script-->
  <!--script type="text/javascript" src="js/jquery-inputcloak.min.js"></script-->
  <script type='text/javascript' src='https://prescriptionhope.com/wp-content/themes/prescription_theme/js/bootstrap.js?ver=4.0'></script>
  <script type='text/javascript' src='https://prescriptionhope.com/wp-content/themes/prescription_theme/js/bootstrap-hover-dropdown.js?ver=4.0'></script>
  <!--script type="text/javascript" src="js/pwstrength-bootstrap.min.js"></script-->
  
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  
  <script type="text/javascript" src="js/jvfloat.js"></script>
  <script type="text/javascript" src="js/tooltip.js"></script>
  <script type="text/javascript" src="js/lib.js"></script>
  <script type="text/javascript" src="js/functions.js"></script>
  <script type="text/javascript" src="js/datepicker.min.js"></script>
  <script type="text/javascript" src="js/new-functions.js"></script>
  <script type="text/javascript" src="js/jquery.mask.min.js"></script>
  <script type="text/javascript" src="js/events_loader.js"></script>
  
  <script type="text/javascript" src="js/owl.carousel.min.js"></script>

  <!-- Facebook Pixel Code -->
  <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '404868283244362');
    fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=404868283244362&ev=PageView&noscript=1"
  /></noscript>
  <!-- End Facebook Pixel Code -->

  <script type='text/javascript'>
  window.__lo_site_id = 93320;

  (function() {
      var wa = document.createElement('script'); wa.type = 'text/javascript'; wa.async = true;
      wa.src = 'https://d10lpsik1i8c69.cloudfront.net/w.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(wa, s);
    })();
  </script>
<?php
if ($patient_logged_in)
{
?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-44317014-1"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', 'UA-44317014-1', {'custom_map': {'dimension1': '1'}});
	</script>
<?php
}
else
{
?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-44317014-1"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', 'UA-44317014-1', {'custom_map': {'dimension1': '0'}});
	</script>
<?php
}
?>
<!-- Google Recaptcha -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WQN9FP7"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

<div id="page-container">
  <nav class="navbar navbar-default navbar-fixed-top header-nav" role="navigation">
    <div class="container">
      <!-- Mobile display -->
      <div class="navbar-header">
        <!--a href="https://prescriptionhope.com"><img src="https://prescriptionhope.com/wp-content/uploads/2014/08/favicon.jpg" height="28" class="header-logo-small left only-mobile"></a-->
        <span class="only-mobile top-menu-title">Enrollment</span>
        <a href="https://prescriptionhope.com" class="svg" style="margin-left:420px;">
          <object data="/images/ph-logo.svg" type="image/svg+xml" height="32" class="top-menu-only-mobile only-mobile header-logo-small-svg" style="margin-left: calc(20% - 58px);"><img src="https://prescriptionhope.com/wp-content/uploads/2017/07/prescription-hope-logo-2017_07_13.png" height="32" class="header-logo-small left top-menu-only-mobile only-mobile"></object>
        </a>
        <button type="button" class="hidden navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <div class="only-mobile right hiddens" style="line-height: 50px;">
          <?php if ($patient_logged_in) { ?>
              <a href="logout.php" class="my-account-link" style="margin-right: 15px; color: #fff;">Logout</a>
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
              <li id="menu-item-47" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-47 top-item"><a href="logout.php">Logout</a></li>
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
            <li style="width: 0px; visibility: hidden;" id="" class="hiddens menu-item menu-item-type-post_type menu-item-object-page page_item top-item"><a href="https://manage.prescriptionhope.com/enrollment/register.php">&nbsp;</a></li>
            <li id="menu-item-4218" class="hidden menu-item menu-item-type-post_type menu-item-object-page active page_item page-item-13 active menu-item-4218 top-item"><a href="https://manage.prescriptionhope.com/enrollment/register.php">Enrollment</a></li>
            <li id="menu-item-4221" class="hidden menu-item menu-item-type-post_type menu-item-object-page menu-item-4221 top-item"><a href="https://prescriptionhope.com/quick-answers/">Quick Answers</a></li>
            <li id="menu-item-4223" class="hidden menu-item menu-item-type-post_type menu-item-object-page menu-item-4223 top-item"><a href="https://prescriptionhope.com/about/">About Us</a></li>
            <li id="menu-item-4739" class="hidden menu-item menu-item-type-post_type menu-item-object-page menu-item-4739 top-item"><a href="https://prescriptionhope.com/contact/">Contact Us</a></li>
          </ul>
          <?php if ($patient_logged_in) { ?>
              <a href="logout.php" class="my-account-link" style="">Logout</a>
          <?php } ?>
        </div>
      </div>
    </div>
  </nav>

  <div id="main_content">