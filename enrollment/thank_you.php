<?php include('_header.php'); ?>
<div class="text-xs-center bg">
<div class="container">
 <div class="row">
 <div class="col-sm-12">
 <div class="span4">  
 
<span><img src="images/right.png" class="img-fluid" alt="Responsive image"></span>
          <span class="content-heading heading-h2">Thank You, You Have Successfully Submitted
Your Enrollment Form For Prescription Hope.</span>
 </div></span>
  
 </div>
 </div>
 </div>
 <div class=" bg-light-blue">
 <div class="container bg-before">
 <div class="row">
 <div class="col-sm-12 p10">
 <h4 class="heading-content-sec"><ul><li> Now that you have submitted your enrollment form, one of two things will happen:</li></ul></h4>
 <div class="tag"><img src="images/mark.png" class="img-fluid" alt="Responsive image"></div>
 <div class="tag"><img src="images/mark1.png" class="img-fluid" alt="Responsive image"></div>
 </div>
 </div>
 </div>
  </div>
</div>

<div class="approved">
<div class="container">
<div class="row">
<div class="col-sm-12">
<h3 class="content-heading">If Your Enrollment Form <strong>Is Pre-Approved</strong></h3>
<ul class="list-content">
<li class="bg-btm"><span class="main-list">1. You will be charged immediately for your first service fee of $50 a month for each medication you are pre-approved for.</span>

<ul class="list-sub-content"> 
<li class="points"><span>Note: You could be pre-approved for some of the requested medications and not pre-approved for other medications. <strong>We will not charge for any medications you are not pre-approved for.</strong></span></li>
</ul>
</li>
<li><span class="main-list">2. Within 48 hours you will receive a welcome call from one of our enrollment specialists that will explain the following:</span>

<ul class="list-sub-content"> 
<li class="points"><span>You will receive a letter from us requesting proof of income documentation, this is required by the pharmaceutical companies to process your medication order(s).</span></li>
<p><strong>a.</strong> This request happens only once a year.</p>
<p><strong>b.</strong> As soon as you receive this from us, please send all requested documents back to us in the postage-paid envelope we provide to you at no cost.</p>
</ul>
<ul class="list-sub-content"> 
<li class="points"><span>Your doctor will also receive a letter from us, asking for the original prescriptions and signatures we need to process your order(s).</span></li>
<p><strong>a.</strong> Please call your doctor’s office as soon as you receive your packet and ask them to please return the requested prescriptions and forms as soon as possible.</p>
</ul>
<ul class="list-sub-content"> 
<li class="points"><span>As soon as we get the information back from you and your doctor, we will process your order(s).</span></li>
<p><strong>a.</strong> Note: We will not be able to order your medication until we have all required information from you and your healthcare providers.</p>
</ul>
<ul class="list-sub-content"> 
<li class="points"> <span>The $50.00 monthly service fee for each medication includes the cost of the medication, so there are no other costs involved.</span></li>
<p><strong>a.</strong> Note: If your enrollment is pre-approved, your online account will be built as we are processing your enrollment form. Please be patient during this time, as all information from your enrollment form will not be available to view until your account is fully setup.</p>
</ul>
</li></ul>

</div>
</div>
</div>
</div>

<div class="not-approved">
<div class="container">
<div class="row">
<div class="col-sm-12">
<h3 class="content-heading">If Your Enrollment Form  <strong>Is Not Approved</strong></h3>
<ul class="list-content">

<li>There will be no charges to the payment information you provided to us.</li>
<li>An email will be sent to you explaining you have not been approved and a letter will be sent to you with the details on why your enrollment was not approved.
</li>
<li>If your personal situation changes in the future, based on the reason you were not approved, you can reapply at that time.
</li>
</ul>


</div></div>
</div>
</div>

<div class="bg-image contact-info">
<div class="container">
<div class="row">
<div class="col-sm-12">
<h3>If you have any questions after receiving your letter or have any medication
changes or additions please log into your account through our website at</h3>
<p><strong>www.PrescriptionHope.com</strong> or call us at <strong>1-877-296-HOPE(4673)</strong> and dial option 3.</p>
<button class="btn btn-primary" onclick="window.location='/enrollment/login.php'">LOGIN TO MY ACCOUNT</button>
</div>
</div>
</div>
</div>
<?php include('_footer.php'); ?>

<style>

.heading-content-sec ul li{padding:0 80px;}
.heading-content-sec ul li:before {
    content: 'Next Steps:';
    position: absolute;
    left: 30px;
    font-weight: bold;
}
.bg-light-blue{position:relative;}
.bg {
    background: url(images/bg-1.png) !important;
    background-repeat: no-repeat;
    background-position: center;   
    z-index: 999;
    background-size: cover !important;   
    margin-top: -60px;
}
li{list-style:none;}
span.main-list {
    font-size: 18px;
}
.approved {
    padding: 20px 0 40px;
}

.jumbotron.text-xs-center {
   
    margin-top: -8px;
}
h4.heading-content-sec {
    width: 68%;
    line-height: 24px;
   
}
.heading-h2 {
    font-size: 28px;
    color: #39b24d;
    font-weight: 800;
    text-align: left !important;
    width: 93%;
    float: right;
    line-height: 36px;
    padding: 0 300px 0 0px;
}
.span4 {
    padding: 140px 0 0;
   
}
.bg-light-blue {
    margin: 100px 0 0;
    padding-bottom: 70px;
}
h3.content-heading {
    text-align: center;
    padding: 20px 0;
    font-size: 25px;
    color: #000;
    font-weight: 400;
}
h3.content-heading strong {
    color: #1765ad;
}
ul.list-content li {
    color: #54646f;
    font-size: 18px;
    font-weight: 600;
    border-bottom: 1px solid #ebebeb;
    padding: 15px 0;
}
.bg-btm{border-bottom: 1px solid #ebebeb;}
ul.list-sub-content .points {
    list-style: none;
    font-size: 16px;
    font-weight: 600;
    padding: 4px 46px;
	position:relative;
}
ul.list-sub-content li {
    border-bottom: none;
}
ul.list-sub-content {
    padding: 10px 0px;
}

.bg-image {
 
    background: url(images/bg-contact.png) no-repeat top center;
    background-size: cover;
    text-align: center;
    color: #fff;
    background-repeat: no-repeat;
   padding: 50px 0 60px;
}
.list-sub-content p{position:relative; margin-left: 20px;}
.list-sub-content p strong{position:absolute; left: 28px;}

.bg-image.contact-info h3 {
    font-size: 30px;
    line-height: 36px;
    font-weight: 300;
    padding: 20px;
}
.bg-image.contact-info p {
    font-size: 20px;
  
    color: #fff;
	padding: 0px 0px;
}
.not-approved {
    padding: 20px 0 80px;
    border-top: 1px solid#ddd;
}
ul.list-content li:nth-child(3){border:none;}
ul.list-content li:last-child {
    border-bottom: 0px;
}
.bg-image.contact-info button.btn.btn-primary{
    background: #f7900a;
    font-size: 16px;
    padding: 10px;
    font-weight: 600;
    margin: 20px;
    /* background-image: -webkit-linear-gradient(top,#f7900a 0,#f7900a 100%); */
    background-image: -o-linear-gradient(top,#337ab7 0,#265a88 100%);
    background-image: linear-gradient(to bottom,#f7900a 0,#dc810a 100%);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff337ab7', endColorstr='#ff265a88', GradientType=0);
    filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
    background-repeat: repeat-x;
border-color: #ce7707;}
.text-xs-center {
    background: #f6f6f6;
}
li.points:before {
    content: '';
    background-image: url(images/bullet.png);
    position: absolute;
    width: 100%;
    height: 100%;
    background-repeat: no-repeat;
    left: 20px;
}
.list-sub-content p {
    color: #54646F;
    font-family: Raleway;
    font-size: 16px;
    line-height: 24px;
    font-weight: 400;
    padding: 8px 0px 0 46px;
}
.approved ol.list-content li {
    border: none;
}
.points span {
    font-weight: 300;
}
.not-approved ul.list-content li{list-style-type: decimal;}
@media (max-width: 1024px){
	.list-sub-content p {padding: 0px 0px 0 0px;}
	.list-sub-content p strong {
    position: absolute;
    left: -20px;
}
	li.points:before {left:0;}
	ul.list-sub-content .points { padding: 4px 20px;}
h4.heading-content-sec {
    width: 100%;
}
.heading-content-sec ul li {
    padding: 0 0px 0 80px;
}
html body #main_content {
    padding: 0;
}
.heading-h2 {
    width: 100%;
    padding: 10px 10px;
    text-align: center !important;
}
.span4 { text-align: center;}

.bg-image.contact-info h3 {padding:10px;}
html body .bg {
background-image: none !important;}
.bg-light-blue {
    background: #d2e4f2;
    padding: 40px;
	margin: 20px 0;
}
img {
    vertical-align: middle;
    max-width: 100%;
}

}
</style>