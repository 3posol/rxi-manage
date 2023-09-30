<!--                           -->
<!-- REMOVE FOOTER FOR BROKERS -->
<!--                           -->
<?php if (!isset($_SESSION['register_data']['p_application_source']) || trim($_SESSION['register_data']['p_application_source']) == '') { ?>

    <div class="container fly_out_form_container" style="display: none;">
        <div class="fly_out_form_area">
            <div class="fly_out_form_inner">
                <div class="fly_out_form_closed" id="fly_out_form_closed">
                    <div class="txt_advocate">Talk to an Advocate:</div>
                    <div class="ta_center"><a href="#" id="click_to_open" class="button orange no_letter_spacing capitalize">Click To Open</a></div>
                </div>
                <div class="fly_out_form" id="fly_out_form" style="display: none;">
                    <div class="close_btn_2"><a href="" class="close_btn" id="fly_out_close">x</a></div>
                    <div class="fly_out_form_cont">
                        <!-- -->
                        <div class="wpcf7" id="wpcf7-f4353-o1" lang="en-US" dir="ltr">
                            <div class="screen-reader-response"></div>
                            <form name="" action="/~presc/current-patients/#wpcf7-f4353-o1" method="post" class="wpcf7-form" novalidate="novalidate">
                            <div style="display: none;">
                                <input type="hidden" name="_wpcf7" value="4353" />
                                <input type="hidden" name="_wpcf7_version" value="3.9.3" />
                                <input type="hidden" name="_wpcf7_locale" value="en_US" />
                                <input type="hidden" name="_wpcf7_unit_tag" value="wpcf7-f4353-o1" />
                                <input type="hidden" name="_wpnonce" value="186c4fc976" />
                            </div>
                            <div class="fly_out_form_header">
                                <div>I Am A:</div>
                                <div class="fly_item"><span class="wpcf7-form-control-wrap i_am_a"><select name="i_am_a" class="wpcf7-form-control wpcf7-select" aria-invalid="false"><option value="Patient">Patient</option><option value="Healthcare Provide">Healthcare Provide</option><option value="Social Worker">Social Worker</option><option value="Other">Other</option></select></span></div>
                                </p>
                            </div>
                            <div class="fly_out_form_body">
                                <div>Next, enter personal information:</div>
                                <div class="fly_item"><span class="wpcf7-form-control-wrap firstName"><input type="text" name="firstName" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="* First Name" /></span></div>
                                <div class="fly_item"><span class="wpcf7-form-control-wrap middleName"><input type="text" name="middleName" value="" size="40" class="wpcf7-form-control wpcf7-text" aria-invalid="false" placeholder="&nbsp;&nbsp;Middle Initial" /></span></div>
                                <div class="fly_item"><span class="wpcf7-form-control-wrap lastName"><input type="text" name="lastName" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="* Last Name" /></span></div>
                                <div class="fly_item"><span class="wpcf7-form-control-wrap validPhone"><input type="text" name="validPhone" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="* Phone Number" /></span></div>
                                <div class="fly_item"><span class="wpcf7-form-control-wrap emailAddress"><input type="email" name="emailAddress" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-email" aria-invalid="false" placeholder="&nbsp;&nbsp;&nbsp;Email" /></span></div>
                                <div class="fly_item"><span class="wpcf7-form-control-wrap addressTa"><textarea name="addressTa" cols="40" rows="10" class="wpcf7-form-control wpcf7-textarea" aria-invalid="false" placeholder="* Message"></textarea></span></div>
                                <div class="fly_item">
                                    <div class="wpcf7-response-output wpcf7-display-none"></div>
                                </div>
                                <div class="fly_item"><input type="submit" value="Request Information" class="wpcf7-form-control wpcf7-submit fly_out_btn" /></div>
                                <div class="info">* Indicates required field</div>
                                </p>
                            </div>
                            </form>
                        </div>                <!-- -->
                    </div>
                </div>
            </div><!-- fly_out_form_inner -->
        </div><!-- fly_out_form_area -->
    </div>

    <script>
        jQuery().ready(function() {
            jQuery("#click_to_open").click(function () {
                jQuery("#fly_out_form").show();
                jQuery("#fly_out_form_closed").hide();
        		var check = jQuery('body').hasClass('newsletter');
        		if(check){
        			jQuery('#row-1').css('min-height','760px');
        		}
        		var target = jQuery(".fly_out_form_cont");
        		jQuery('html,body').animate({
        			scrollTop: target.offset().top
        		}, 1000);
                return false;
            });

            jQuery("#fly_out_close").click(function () {
                jQuery("#fly_out_form").hide();
                jQuery("#fly_out_form_closed").show();
        		var check = jQuery('body').hasClass('newsletter');
        		if(check){
        			jQuery('#row-1').css('min-height','inherit');
        		}
                return false;
            });
        });
    </script>

    <div class="logos_footer">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mt_15 mb_5 ta_center">As featured and seen in:</div>
                <div class="col-md-9 logos_footer_img"><img src="https://prescriptionhope.com/wp-content/themes/prescription_theme/images/logos_footer3.png" /></div>
            </div>
        </div>
    </div>
    <footer>
    	<div class="container">
        	<div class="row">
                <div class="col-xs-12 col-md-7 no-padding-left no-padding-left-xs">
                	<div class="footer-nav clearfix"><ul id="menu-footer-menu" class="nav navbar-nav center-block"><div class=" col-sm-4 col-md-3  id-4228"><li id="menu-item-4228" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4228 top-item"><a href="https://prescriptionhope.com/enrollment/">Enrollment</a> </li></div>
                        <div class=" col-sm-4 col-md-4  id-4229"><li id="menu-item-4229" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4229 top-item"><a href="https://prescriptionhope.com/healthcare-providers/">Healthcare Providers</a> </li></div>
                        <div class=" col-sm-4 col-md-3  id-4230"><li id="menu-item-4230" class="menu-item menu-item-type-custom menu-item-object-custom active menu-item-4230 top-item"><a href="https://prescriptionhope.com/current-patients/#medication">Medications</a> </li></div><div class="md-clearfix"></div>
                        <div class=" col-sm-4 col-md-3  id-4280"><li id="menu-item-4280" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4280 top-item"><a href="https://prescriptionhope.com/patient-stories/">Patient Stories</a> </li></div>
                        <div class=" col-sm-4 col-md-4  id-4232"><li id="menu-item-4232" class="menu-item menu-item-type-post_type menu-item-object-page active page_item page-item-23 active menu-item-4232 top-item"><a href="https://prescriptionhope.com/current-patients/">Current Patients</a> </li></div>
                        <div class=" col-sm-4 col-md-3  id-4233"><li id="menu-item-4233" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4233 top-item"><a href="https://prescriptionhope.com/about/">About Us</a> </li></div>
                        <!--div class=" col-sm-4 col-md-3  id-4234"><li id="menu-item-4234" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4234 top-item"><a href="https://prescriptionhope.com/how-it-works/">How It Works</a> </li></div-->
                        <div class=" col-sm-4 col-md-3  id-4235"><li id="menu-item-4235" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4235 top-item"><a href="https://prescriptionhope.com/quick-answers/">View Quick Answers</a> </li></div>
                        <div class=" col-sm-4 col-md-4  id-4236"><li id="menu-item-4236" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4236 top-item"><a href="https://prescriptionhope.com/contact/">Contact Us</a> </li></div>
                    </ul></div>
                </div>
            	<div class="col-xs-12 col-md-5 buttons no-padding-left no-padding-left-xs no-padding-right-xs no-padding-left-md no-padding-right-md">
                    <div class="col-xs-12">
                    	<div class="col-xs-12  col-md-6 no-padding-left no-padding-left-xs no-padding-right-xs no-padding-left-md no-padding-right-md">
                            <a href="https://prescriptionhope.com/enrollment/" class="button capitalize no_letter_spacing orange">Enroll Today!</a>
                            <div class="mt_40 hidden-xs">Prescription Hope, Inc.<br/>
                                P.O.Box 2700<br/>
                                Westerville, Ohio 43086
                            </div>
                        </div>
                    	<div class="col-xs-12 col-md-6 no-padding-right no-padding-right-xs no-padding-left-xs no-padding-left-md no-padding-right-md">
                            <a href="https://prescriptionhope.com/current-patients/#medication" class="button capitalize no_letter_spacing orange">View Medication List</a>
                            <div class="mt_40 hidden-xs"><p><strong><a href="https://prescriptionhope.com/wp-content/uploads/2014/08/PH_PrivacyPolicy.pdf" target="_blank">Privacy Policy</a></strong> |<strong> </strong><a href="#"><strong>Terms of Use</strong><br />
                                </a><?=date("Y")?> ©Prescription Hope, Inc.<br/>
                                <a title="Medications List Posts" href="//prescriptionhope.com/medications-list/">Medications List</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!--<div class="col-xs-12"><a href="https://prescriptionhope.com/newsletter/" class="button white">Sign Up</a></div>-->
                </div>
             </div>

             <div class="row">
                 <div class="no-padding-left">
                	<div class="col-md-3">
                    	<a href="https://prescriptionhope.com/newsletter/" class="button white capitalize no_letter_spacing newsletter">Newsletter Opt-In</a>
                    </div>
                    <div class="col-md-6 first fb_area">
                        <a href="https://www.facebook.com/prescriptionhope" target="_blank" class="fb-footer-link">Facebook</a>
                        <iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fprescriptionhope&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;share=false&amp;height=21&amp;appId=1437709013134721" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:35px; width:80px;display: inline-block;margin-top: 0px;margin-bottom: -15px; vertical-align:bottom;" allowtransparency="true"></iframe>
                    </div>
                </div>

            </div>
        </div>
    </footer>

<!--                           -->
<!-- REMOVE FOOTER FOR BROKERS -->
<!--                           -->
<?php } ?>

<script type='text/javascript' src='https://prescriptionhope.com/wp-content/plugins/contact-form-7/includes/js/jquery.form.min.js?ver=3.51.0-2014.06.20'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var _wpcf7 = {"loaderUrl":"https:\/\/prescriptionhope.com\/wp-content\/plugins\/contact-form-7\/images\/ajax-loader.gif","sending":"Sending ..."};
/* ]]> */
</script>
<script type='text/javascript' src='https://prescriptionhope.com/wp-content/plugins/contact-form-7/includes/js/scripts.js?ver=3.9.3'></script>
<SCRIPT language="JavaScript" type="text/javascript">
    <!--
    window.ysm_customData = new Object();
    window.ysm_customData.conversion = "transId=,currency=,amount=";
    var ysm_accountid  = "1RR1M4ADR177A712EKOBUP3UJNG";
    document.write("<SCR" + "IPT language='JavaScript' type='text/javascript' "
    + "SRC=//" + "srv3.wa.marketingsolutions.yahoo.com" + "/script/ScriptServlet" + "?aid=" + ysm_accountid
    + "></SCR" + "IPT>");
    // -->
</SCRIPT>
</body>
</html>