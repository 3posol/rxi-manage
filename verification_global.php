<div class="row no-margin">



    <div class="new-verification">
        <div class="verification-logo" style="display: none"><a href="https://prescriptionhope.com" class="svg">
                <object data="https://prescriptionhope.com/images/ph-logo.svg" type="image/svg+xml" width="241" height="90" class="top-menu-only-desktop only-desktop" style="margin-top: 20px;"><img src="https://prescriptionhope.com/wp-content/uploads/2017/07/prescription-hope-logo-2017_07_13.png" class="header-logo left top-menu-only-desktop only-desktop"></object>
            </a></div>
        <div class="verificationPanel">
            <form id="request_code_form" method="POST" onsubmit="request_code_form(this, event)">
                <?php if (isset($login_verification)): ?>
                    <div class="verification-section heading">
                        <div class="heading"><h2>Welcome Back</h2></div>
                    </div>
                <?php endif; ?>
                <div class="row verification_content_body">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="desctiption_section">
                            <h2 class="heading">Help Us Protect Your Account</h2>
                            <p class="description">To keep your account secure, we verify your identity for each device and web browser combination you use.</p>
                            <p class="description">We weren't able to make a match for the setup you're currently on. This may be because:</p>
                            <ul class="desctiption_list">
                                <li>You're logging in on a new device</li>
                                <li>You haven't registered this device</li>
                                <li>You've changed web browsers</li>
                                <li>You installed a recent browser patch or update</li>
                                <li>You modified your computer, operating system or software settings</li>
                                <li>You cleared your cookies</li>
                            </ul>
                            <p class="description">Any of these conditions prevents us from matching your device to you. To continue, please request a code.</p>
                        </div>
                        <div class="form-group form-check strong_heading">
                            <label class="form-check-label" ><strong>Where should we send a verification code?</strong></label>
                        </div>
                        <div class="form-group form-check phone_checkbox">
                            <label class="form-check-label"><input data-type="phone"  type="checkbox" name="settings[phone]" value="text" class="code_send_option form-check-input" >Send text message at <?php echo $phone; ?></label>
                        </div>	
                        <div class="form-group form-check email_checkbox">
                            <label class="form-check-label" ><input data-type="email" type="checkbox"  name="settings[mail]" value="1" class="code_send_option form-check-input ">Send an email at <?php echo $email_mask; ?></label>
                        </div>
                        <input type="hidden" name="send_code" value="1">
                        <div class="row verification_actions_btn">				
                            <div class="col-xs-12 col-sm-12 col-xs-12">
                                <input tabindex="25" type="submit" name="request_code" id="btSubmit" value="Request a Code" class="big-button loginPageButton btn btn-default bt btn-block btn-lg">
                                <a href="/patients-dashboard/login.php">Cancel</a>
                                <div class="nevigate vaerification_nevigation"></div>
                            </div>
                        </div>
                    </div>
                </div>			
            </form>

        </div>

    </div>

</div>


<!-- Modal -->
<div class="modal" id="cg_2faModel" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Verify Your Account</h4>
            </div>
            <div class="modal-body">
                <div class="loginPanel">
                    <form id="cg_code_from" method="POST" onsubmit="verifyCode(this, event)">
                        <p class="cg_text_ver cg_text_phone">Check your text message for your Verification Code</p>
                        <p class="cg_text_ver cg_text_email">Check your email inbox for your Verification Code</p>
                        <div class="login-section">
                            <input type="text" class="form-control" name="code" placeholder="Code*" id="code" value="" class="full-width <?= ((!$success) ? 'error' : '') ?>">

                            <div class="row">
                                <div class="col-sm-12"><input type="submit" name="code_submit" id="btSubmit" value="Verify" class="btn btn-primary btn-block"></div>
                            </div>
                            <input type="hidden" name="verify_code" value="1">
                            <div id="" class="<?= (($message != '') ? 'error' : '') ?>"><?= $message ?></div>				

                        </div>
                        <div class="row">
                            <div class="col-sm-12 loginNotEnrolled">
                                <p class="content-sec">Didn't receive the code?</p>
                                <a href="javascript:void(0)" class="links" onclick="resendCode()">Resend<span class="code_notify"></span></a>						
                            </div>					
                        </div>					
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>


<script>
    function verifyCode(form, event) {
        event.preventDefault();
        if (!jQuery('#code').val()) {
            jQuery('.code_notify').html('<div class="alert alert-danger">Please enter code.</div>');
            setTimeout(function () {
                jQuery('.code_notify').html('');
            }, 2000);
            return;
        }
        jQuery.ajax({
            dataType: "json",
            method: 'post',
            data: jQuery(form).serialize(),
            beforeSend: function (xhr) {
                jQuery('.code_notify').html('<div class="alert alert-info">Verifing code...</div>');
            }
        }).done(function (data) {
            if (data.success) {
                jQuery(location).prop('href', data.location);
            } else {
                jQuery('.code_notify').html('<div class="alert alert-danger">' + data.message + '</div>');
                setTimeout(function () {
                    jQuery('.code_notify').html('');
                }, 2000);
            }
        });
    }
    function request_code_form(form, event) {
        event.preventDefault();
        if (!jQuery('.code_send_option:checked').length) {
            jQuery(form).find('.nevigate').html('<span class="alert alert-danger">Please select an option first.</div>');
            setTimeout(function () {
                jQuery(form).find('.nevigate').html('');
            }, 3000);
            return;
        }
        jQuery(form).find('.nevigate').html('<span class="alert alert-info">Sending code...</div>');
        jQuery.ajax({
            dataType: "json",
            method: 'post',
            data: jQuery(form).serialize(),
            beforeSend: function (xhr) {
            }
        }).done(function (data) {
            if (data.success) {
                jQuery(form).find('.nevigate').html('');
                
                jQuery('.cg_text_ver').hide();
                jQuery('.code_send_option:checked').each(function(){
                    var typeText = jQuery(this).data('type');
                    jQuery('.cg_text_'+typeText).show();
                });
                
                jQuery("#cg_2faModel").modal('show');
            } else {
                jQuery(form).find('.nevigate').html('<span class="alert alert-danger">Error in sending code.</div>');
            }
            setTimeout(function () {
                jQuery(form).find('.nevigate').html('');
            }, 3000);
        });
    }
    function resendCode() {
        jQuery.ajax({
            dataType: "json",
            method: 'post',
            data: jQuery('#request_code_form').serialize(),
            beforeSend: function (xhr) {
                jQuery('.code_notify').html('<div class="alert alert-info">Sending code...</div>');
            }
        }).done(function (data) {
            jQuery('.code_notify').html('<div class="alert alert-success">Sent!</div>');
            setTimeout(function () {
                jQuery('.code_notify').html('');
            }, 2000);
        });
    }

</script>