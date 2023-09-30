<script src="//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.js" type="text/javascript"
    charset="utf-8"></script>
<link href="//cdn.jsdelivr.net/npm/featherlight@1.7.14/release/featherlight.min.css" type="text/css" rel="stylesheet" />
<?php if (isset($_SESSION['incomplete_application_org']) && $_SESSION['incomplete_application_org'] == 0): ?>

<div class="force_login_wrap row account_summary_section">
    <div class="force_login_content col-sm-12 col-md-12">
        <div class="force_login_message">
            <div class="alert alert-danger" role="alert">
                <!-- <a class="not_href_rmv" href="/enrollment/enroll.php?iframe=1" data-featherlight-iframe-width="1400"
                    data-featherlight-iframe-height="900" data-featherlight="iframe">
                    <p>Please complete Enrollment form in order to get approved. <u>Click here</u> to finish enrollment
                        form.</p>
                </a> -->

                <a class="not_href_rmv" href="javascript:void(0)" data-toggle="modal" data-target="#exampleModalCenter">
                    <p>Please complete Enrollment form in order to get approved. <u>Click here</u> to finish enrollment
                        form.</p>
                </a>

            </div>


        </div>
    </div>
</div>








<?php elseif (isset($_SESSION['incomplete_application_org']) && $_SESSION['incomplete_application_org'] == 1): ?>

<div class="force_login_wrap row account_summary_section">
    <div class="force_login_content col-sm-12 col-md-12">
    </div>
</div>

<script>
jQuery.ajax({
    url: "/enrollment/success.php",
    type: "POST",
    beforeSend: function() {},
    success: function(response) {
        jQuery('.force_login_content').html(jQuery(response).find('.leftIconBox').html());
    },
    error: function(e) {

    }
});
</script>
<!-- Modal -->
<div class="modal" id="cg_proceedModel" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body">
                <div class="cbp_tmlabel" id="step2">
                    <div class="close-section">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <h2 class="popup_heading first">Please login into your online portal in two business days to see
                        what medications you were pre-approved for. We will be sending you a request for additional
                        information that we must have to complete your order. Please respond with the requested
                        information as soon as you can. We will also be sending out a request to your doctor(s) for a
                        signature and a prescription. Please contact your doctor in a few days and ask them to return
                        the information we requested from them. Once we have the requested information from you and your
                        doctor(s) we will be able to order your medication from the pharmaceutical company. Please note:
                        Without the requested information from you and your doctor(s) we will not be able to complete
                        your orders.</h2>
                    <p>During the processing of your application, we review:</p>
                    <ul>
                        <li>Which medication(s) you are applying for you.</li>
                        <li>Your income information to see if you can get approved for the patient assistance program.
                        </li>
                        <li>Then we can pre-approve or deny you for each medication(s) requested based on the
                            information you have submitted.</li>
                    </ul>
                    <h2 class="popup_heading second">If Your Enrollment Form Is Not Approved</h2>
                    <ul>
                        <li> There will be no charges to the payment information you provided to us.</li>
                        <li>An email will be sent to you explaining you have not been approved, and a letter will be
                            sent to you with the details on why your enrollment was not approved.</li>
                        <li>If your situation changes in the future, based on the reason you were not approved, you can
                            reapply at that time.</li>
                    </ul>
                    <p>Note: If you have applied for more than one medication, it is possible that you can get approved
                        for one medication and not the other(s). Each medication has a different patient assistance
                        program. Please log in to your account to see which medications got approved for you to receive.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Proceed to Your Secure
                    Portal</button>
            </div>
        </div>

    </div>
</div>





<script>
jQuery("#cg_proceedModel").modal('show');
</script>
<?php endif; ?>