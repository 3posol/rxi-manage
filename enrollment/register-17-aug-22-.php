<?php
require_once('includes/functions.php');

//
// Check URL
//

$url_source = filter_input(INPUT_GET, 'source', FILTER_DEFAULT, array('options' => array('default' => '')));
if ($url_source != '') {
    activate_url_properties($url_source);
}

//
$data = array(
    'first_name' => '',
    'middle_initial' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'email_confirm' => '',
    'address' => '',
    'address2' => '',
    'city' => '',
    'state' => '',
    'zipcode' => '',
    'password' => '',
    'password_confirmation' => '',
    'security_question' => 'What was the name of the town you grew up in?',
    'secret_answer' => 'ny',
    'register_terms' => 0,
    'ph_register_terms_email' => 0,
    'application_source' => '',
    'phone' => '',
    'gender' => '',
    'alternate_phone' => '',
    'alternate_contact_name' => '',
    'is_minor' => '',
    'parent_first_name' => '',
    'parent_middle_initial' => '',
    'parent_last_name' => '',
    'parent_phone' => '',
    'hear_about' => '',
    'p_hear_about_1',
    'p_hear_about_2',
    'p_hear_about_3'
);

$submit = (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response'] != '');

$success = true;
$message = '';
$application_source = '';
if ($submit) {
    $data = array(
        'first_name' => (isset($_POST['first_name'])) ? trim($_POST['first_name']) : '',
        'middle_initial' => (isset($_POST['middle_initial'])) ? trim($_POST['middle_initial']) : '',
        'last_name' => (isset($_POST['last_name'])) ? trim($_POST['last_name']) : '',
        'email' => (isset($_POST['email'])) ? strtolower(trim($_POST['email'])) : '',
        'email_confirm' => (isset($_POST['email_confirm'])) ? strtolower(trim($_POST['email_confirm'])) : '',
        'address' => (isset($_POST['address'])) ? trim($_POST['address']) : '',
        'address2' => (isset($_POST['address2'])) ? trim($_POST['address2']) : '',
        'city' => (isset($_POST['city'])) ? trim($_POST['city']) : '',
        'state' => (isset($_POST['state'])) ? trim($_POST['state']) : '',
        'zipcode' => (isset($_POST['zipcode'])) ? trim($_POST['zipcode']) : '',
        'password' => (isset($_POST['password'])) ? trim($_POST['password']) : '',
        'password_confirmation' => (isset($_POST['password_confirmation'])) ? trim($_POST['password_confirmation']) : '',
        'security_question' => (isset($_POST['security_question'])) ? trim($_POST['security_question']) : 'What was the name of the town you grew up in?',
        'secret_answer' => (isset($_POST['secret_answer'])) ? trim($_POST['secret_answer']) : 'ny',
        'register_terms' => (isset($_POST['register_terms'])) ? 1 : 0,
        'ph_register_terms_email' => (isset($_POST['ph_register_terms_email'])) ? 1 : 0,
        'application_source' => (isset($_GET['source'])) ? $_GET['source'] : '',
        'phone' => (isset($_POST['phone'])) ? trim($_POST['phone']) : '',
        'gender' => (isset($_POST['gender'])) ? trim($_POST['gender']) : '',
        'alternate_phone' => (isset($_POST['alternate_phone'])) ? trim($_POST['alternate_phone']) : '',
        'alternate_contact_name' => (isset($_POST['alternate_contact_name'])) ? trim($_POST['alternate_contact_name']) : '',
        'is_minor' => (isset($_POST['is_minor'])) ? trim($_POST['is_minor']) : '',
        'parent_first_name' => (isset($_POST['p_parent_first_name'])) ? trim($_POST['p_parent_first_name']) : '',
        'parent_middle_initial' => (isset($_POST['p_parent_middle_initial'])) ? trim($_POST['p_parent_middle_initial']) : '',
        'parent_last_name' => (isset($_POST['p_parent_last_name'])) ? trim($_POST['p_parent_last_name']) : '',
        'parent_phone' => (isset($_POST['p_parent_phone'])) ? trim($_POST['p_parent_phone']) : '',
        'hear_about' => (isset($_POST['p_hear_about'])) ? trim($_POST['p_hear_about']) : '',
        'p_hear_about_1' => (isset($_POST['p_hear_about_1'])) ? trim($_POST['p_hear_about_1']) : '',
        'p_hear_about_2' => (isset($_POST['p_hear_about_2'])) ? trim($_POST['p_hear_about_2']) : '',
        'p_hear_about_3' => (isset($_POST['p_hear_about_3'])) ? trim($_POST['p_hear_about_3']) : '',
    );
    //for encoding
    $key = pack('H*', md5($data['email']));
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    if ($data['first_name'] != '' && $data['last_name'] != '' && $data['email'] != '' && $data['phone'] != '' && $data['email'] == $data['email_confirm'] && $data['address'] != '' && $data['city'] != '' && $data['state'] != '' && $data['zipcode'] != '' && $data['password'] != '' && $data['password_confirmation'] != '' && $data['password'] == $data['password_confirmation'] && $data['security_question'] != '' && $data['secret_answer'] != '' && $data['ph_register_terms_email'] != 0 && ($data['is_minor'] != 1 || ($data['is_minor'] == 1 && $data['parent_first_name'] && $data['parent_last_name'] && $data['parent_phone'])) && $data['gender']) {
//    if ($data['first_name'] != '' && $data['last_name'] != '' && $data['email'] != '' && $data['phone'] != '' && $data['email'] == $data['email_confirm'] && $data['password'] != '' && $data['password_confirmation'] != '' && $data['password'] == $data['password_confirmation'] && $data['security_question'] != '' && $data['secret_answer'] != '' && $data['ph_register_terms_email'] != 0) {
        //register
        $api_data = array(
            'command' => 'register',
            'first_name' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['first_name'], MCRYPT_MODE_CFB, $iv)),
            'middle_initial' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['middle_initial'], MCRYPT_MODE_CFB, $iv)),
            'last_name' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['last_name'], MCRYPT_MODE_CFB, $iv)),
            'application_source' => (isset($_COOKIE['url_code']) && $_COOKIE['url_code'] != '') ? $_COOKIE['url_code'] : '',
            //'email'				=> base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['email'], MCRYPT_MODE_CFB, $iv)),
            'email' => $data['email'],
            'password' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['password'], MCRYPT_MODE_CFB, $iv)),
            'security_question' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['security_question'], MCRYPT_MODE_CFB, $iv)),
            'secret_answer' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['secret_answer'], MCRYPT_MODE_CFB, $iv)),
            'address' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['address'], MCRYPT_MODE_CFB, $iv)),
            'address2' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['address2'], MCRYPT_MODE_CFB, $iv)),
            'city' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['city'], MCRYPT_MODE_CFB, $iv)),
            'state' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['state'], MCRYPT_MODE_CFB, $iv)),
            'zipcode' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['zipcode'], MCRYPT_MODE_CFB, $iv)),
            'register_terms' => $data['register_terms'],
            'application_source' => $_GET['source'],
            'phone' => $data['phone'],
            'application_origin' => 0,
            'iv' => base64_encode($iv),
            'gender' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['gender'], MCRYPT_MODE_CFB, $iv)),
            'alternate_phone' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['alternate_phone'], MCRYPT_MODE_CFB, $iv)),
            'alternate_contact_name' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['alternate_contact_name'], MCRYPT_MODE_CFB, $iv)),
            'is_minor' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['is_minor'], MCRYPT_MODE_CFB, $iv)),
            'parent_first_name' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['parent_first_name'], MCRYPT_MODE_CFB, $iv)),
            'parent_middle_initial' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['parent_middle_initial'], MCRYPT_MODE_CFB, $iv)),
            'parent_last_name' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['parent_last_name'], MCRYPT_MODE_CFB, $iv)),
            'parent_phone' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['parent_phone'], MCRYPT_MODE_CFB, $iv)),
            'hear_about' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['hear_about'], MCRYPT_MODE_CFB, $iv)),
            'hear_about_1' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_hear_about_1'], MCRYPT_MODE_CFB, $iv)),
            'hear_about_2' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_hear_about_2'], MCRYPT_MODE_CFB, $iv)),
            'hear_about_3' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data['p_hear_about_3'], MCRYPT_MODE_CFB, $iv)),
        );
        $response = api_command($api_data);
        if(isset($_GET['debug'])&&$_GET['debug']){
            
        echo "<pre>";print_r($response);die('Response');
        }
        if (isset($response->success) && $response->success == 1) {






            //success
            $success = true;
            $message = '<center>You\'ll receive a confirmation email shortly.<br><br>Please check your email to confirm your email address.</center>';

            if (session_id() == '') {
                session_start();
            }





            $_SESSION['signup_2fa'] = $response;
            $code = rand(11111, 99999);
            $_SESSION['signup_2fa_code'] = $code;
            $is2Fa = true;
            $data = array(
                'command' => '2fa_verification',
                'email_address' => $email_address,
                'code' => $code,
                'name' => $response->patient->first_name
            );
//            $verification_2fa = api_command($data);
            setcookie('signup_2fa_code', 1, time() + (60 * 10), "/");
//            setcookie('signup_2fa_code_temp', $code, time() + (60 * 10), "/");
            header('Location: verification.php');
//            header('Location: 2fa.php');
            die;


            $_SESSION[$session_key]['data']['id'] = $response->applicant;
            $_SESSION[$session_key]['access_code'] = md5($data['email']);
            $_SESSION['PLP']['access_code'] = md5($data['email']);
            $_SESSION['PLP']['data'] = (array) $response->data;
            $_SESSION['PLP']['patient'] = (object) array_merge((array) $response->data, array(
                        'force_login' => 1,
                        'PatientID' => $response->applicant,
                        'account_username' => $data['email']
            ));
            $_SESSION['PLP']['incomplete_application'] = '';

            $_SESSION['incomplete_application_org'] = 0;
            /* if(isset($_GET['source'])){
              header('Location: enroll.php?my_broker_source='.$_GET['source']);
              }else{
              header('Location: enroll.php');
              } */
//            header('Location: enroll.php');
//            echo '<pre>';
//            print_r($_SESSION);
//            die;
            header('Location: ../patients-dashboard/dashboard.php');
//            header('Location: enroll.php');
        } elseif (isset($response->success) && $response->success == 2) {
            $success = false;
            $message = 'Registration failed, there is already an account for this email address.';
        } else {
            //fail
            $success = false;
            $message = 'Registration failed: Please verify that you filled out correctly all the required information. If this problem continues, please contact a patient advocate at 1-877-296-4673, option 3.';
        }
    } elseif ($submit) {
        //invalid form
        $success = false;
        $message = 'Registration failed: Please make sure you filled correctly all the information and try again.<br><br>';
    }
}
?>

<?php include('_header.php'); ?> 
<script type="text/javascript" src="/enrollment/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBOlfvGqz948FRGcCi35yHLbvWhLHHwSzQ&libraries=places"></script>
<style type="text/css">
    html body h1, html body h2, html body h3,html body h4, html body h5, html body h6
    ,html body p, html body label, html body strong, html body a, html body input, html body textarea
    , html body span, html body div {font-family:Arial !important;} .text-content.text-content-mac, .text-content-mac, .already_acc {display: none;}
    footer {display: none;}
    html {background: #eaeaea; height:100%;}
    body{background:#eaeaea;padding-top:0px;}
    #page-container {background: transparent;}
</style>
<?= (($success && $submit) ? '<center><h2>Account Successfully Created</h2></center>' : '') ?>
<div class="container Form-register-section">
    <div class="">

        <!----Testimonial---->
        <div class="col-md-5 col-sm-12 col-xs-12 pull-left" id="testimonials-row">    
            <div class="">
                <div class="col-md-12 col-sm-12 col-xs-12">			
                    <h2 class="heading-title"><span><img src="/enrollment/images/Account/get-start-icon.png"></span>   Get Started</h2>

                    <div class="col-sm-12 pull-right">

                        <div class="text-content-text"><span>You are a few steps away from saving money on your<br/> medication. To begin your enrollment, let's start with<br/> some contact information to create your account.</span></div>

                    </div>	

                </div>
                <div class=" column L10 ph-desktop-view">

                    <div class="profile-image"><a class="open_video" data-name="Theresa from Oregon" data-href="O1ZjD1DvMpQ"><img src="/enrollment/images/Account/Profile-video.png"></a>

                        <div class="overlay-profile"><a href="#" class="open_video" data-name="Theresa from Oregon" data-href="O1ZjD1DvMpQ"><img src="/enrollment/images/Account/play.png"></a></div>
                    </div>

                    <div id="owl-demo" class="owl-carousel owl-theme  bg-image-profile ">
                        <div class="item">	
                            <div class="testimonials col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <h3>
                                    <span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
                                    <span class="text-testimonials">When the rep from Prescription Hope called to tell me I was qualified, I was speechless. They saved our home, they saved my health, they lifted that enormous financial burden at a time when we needed it most.</span>
                                    <p><span>- Theresa from Oregon</span></p>
                                </h3>
                            </div>
                        </div>
                        <div class="item">	
                            <div class="testimonials col-lg-12 col-md-12 col-sm-12  col-xs-12">
                                <h3>
                                    <span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
                                    <span class="text-testimonials">Prescription Hope has allowed me and my family to do several other things because it has freed up funds from really expensive medication.</span>
                                    <p><span>- Stephen from Iowa</span> </p>
                                </h3>
                            </div>		
                        </div>
                        <div class="item">
                            <div class="testimonials col-lg-12 col-md-12 col-sm-12  col-xs-12">
                                <h3>
                                    <span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
                                    <span class="text-testimonials">They were the most kind helpful people I have ever spoken with. The experience has been life-changing.</span>
                                    <p><span>- Mary From Indiana</span> </p>
                                </h3>
                            </div>	
                        </div>
                    </div>
                </div>
                <div id="video_box" style="display:none;">
                    <div id="close_video_box"><img src="./images/close.jpg"></div>
                    <div id="video_holder"></div>
                    <div id="video_info"></div>
                </div>
            </div>

            <br/><br/>
        </div><!--end of container-->
        <div class=" col-md-7 col-sm-12 co-xs-12 pull-right enroll-form-1">
            <div class="row">
                <form role="form" id="fmRegister" method="POST" autocomplete="nope">
                    <div class="col-md-12 col-sm-12 col-xs-12">			

                        <div class="col-sm-8 col-xs-12 pull-right">
                            <div class="text-content text-content-mac"><img src="images/safe.jpg"> <span>We keep your information safe and protected. It is secured by 256-bit encryption, the same security banks use.</span></div>
                            <div class="text-content-mac"><img src="images/mcafee-trans.png"></div>
                        </div>	

                    </div>				
                    <div class="colorgraph col-md-12 col-sm-12 col-xs-12">
                        <?php if ($message != '' && !$success) { ?>
                            <div class="">
                                <div id="fmMsg" role="alert" style="text-align: center;" class="alert alert-info col-xs-12 col-sm-12 col-md-12 <?= (($message != '' && !$success) ? 'error' : 'bold') ?>"><?= $message ?>						
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">	
                            <div class="col-xs-12 col-sm-4 col-md-4">
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" name="first_name" id="first_name" class="jvf form-control input-lg" placeholder="First Name *" tabindex="1" value="<?= addslashes($data['first_name']) ?>">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4">
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" name="middle_initial" id="middle_initial" class="jvf form-control input-lg" placeholder="Middle Name" tabindex="2" value="<?= addslashes($data['middle_initial']) ?>" maxlength="1">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4">
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" name="last_name" id="last_name" class="jvf form-control input-lg" placeholder="Last Name *" tabindex="3" value="<?= addslashes($data['last_name']) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">				
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="email" name="email" id="email" class="jvf form-control input-lg" placeholder="Email Address *" tabindex="4" value="<?= addslashes($data['email']) ?>" data-hintold="Enter a valid email address. Your email address is required for communication regarding your medication orders and important updates about Prescription Hope.">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="email" name="email_confirm" id="email_confirm" class="jvf form-control input-lg" placeholder="Confirm Email Address *" tabindex="5" value="<?= $data['email_confirm'] ?>">
                                </div>
                            </div>
                        </div>			
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div id="capsInfo" style="color: blue;display: block;position: absolute;top: -19px;font-size: 13px;left: 37px;"></div>
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="password" class="jvf form-control input-lg caps_check ttip" id="password" name="password" placeholder="Password *" data-text="Password Requirements" data-hint="Passwords must be 8-20 characters, contain a mix of uppercase and lowercase letters, and contain at least one number." tabindex="6" maxlength="20"><span class="showpassword" id="password_show"><span class="eye-open"></span></span>
                                </div>							
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="password" class="jvf form-control input-lg caps_check" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password *" tabindex="7"  maxlength="20">								
                                    <span class="showpassword" id="password_confirmation_show"><span class="eye-open"></span></span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" name="phone" id="phone" class="jvf form-control input-lg required customphone" placeholder="Mobile Phone Number *" tabindex="8" value="<?= addslashes($data['phone']) ?>">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">				
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" name="address" id="p_address" class="jvf form-control input-lg" placeholder="Street Address *" tabindex="9" value="<?= addslashes($data['address']) ?>" data-hintold="Enter a valid address.">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">				
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" name="address2" id="p_address2" class="jvf form-control input-lg" placeholder="Apartment, Suite, Unit, etc. (optional)" tabindex="10" value="<?= addslashes($data['address2']) ?>" data-hintold="Enter a valid address.">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4">				
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" name="city" id="p_city" class="jvf form-control input-lg" placeholder="City *" tabindex="11" value="<?= addslashes($data['city']) ?>" data-hintold="Enter a valid city.">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4">				
                                <div class="form-group">
                                    <select data-hj-allow="true" name="state" id="p_state" preload="State" class="" placeholder="State *" title="State *" tabindex="12">
                                        <option value="" selected="selected">State *</option>
                                        <option value="AL">Alabama</option>
                                        <option value="AK">Alaska</option>
                                        <option value="AZ">Arizona</option>
                                        <option value="AR">Arkansas</option>
                                        <option value="CA">California</option>
                                        <option value="CO">Colorado</option>
                                        <option value="CT">Connecticut</option>
                                        <option value="DE">Delaware</option>
                                        <option value="DC">District Of Columbia</option>
                                        <option value="FL">Florida</option>
                                        <option value="GA">Georgia</option>
                                        <option value="HI">Hawaii</option>
                                        <option value="ID">Idaho</option>
                                        <option value="IL">Illinois</option>
                                        <option value="IN">Indiana</option>
                                        <option value="IA">Iowa</option>
                                        <option value="KS">Kansas</option>
                                        <option value="KY">Kentucky</option>
                                        <option value="LA">Louisiana</option>
                                        <option value="ME">Maine</option>
                                        <option value="MD">Maryland</option>
                                        <option value="MA">Massachusetts</option>
                                        <option value="MI">Michigan</option>
                                        <option value="MN">Minnesota</option>
                                        <option value="MS">Mississippi</option>
                                        <option value="MO">Missouri</option>
                                        <option value="MT">Montana</option>
                                        <option value="NE">Nebraska</option>
                                        <option value="NV">Nevada</option>
                                        <option value="NH">New Hampshire</option>
                                        <option value="NJ">New Jersey</option>
                                        <option value="NM">New Mexico</option>
                                        <option value="NY">New York</option>
                                        <option value="NC">North Carolina</option>
                                        <option value="ND">North Dakota</option>
                                        <option value="OH">Ohio</option>
                                        <option value="OK">Oklahoma</option>
                                        <option value="OR">Oregon</option>
                                        <option value="PA">Pennsylvania</option>
                                        <option value="PR">Puerto Rico</option>
                                        <option value="RI">Rhode Island</option>
                                        <option value="SC">South Carolina</option>
                                        <option value="SD">South Dakota</option>
                                        <option value="TN">Tennessee</option>
                                        <option value="TX">Texas</option>
                                        <option value="UT">Utah</option>
                                        <option value="VT">Vermont</option>
                                        <option value="VA">Virginia</option>
                                        <option value="WA">Washington</option>
                                        <option value="WV">West Virginia</option>
                                        <option value="WI">Wisconsin</option>
                                        <option value="WY">Wyoming</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4">				
                                <div class="form-group">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" name="zipcode" id="p_zipcode" class="jvf form-control input-lg" placeholder="Zip Code *" tabindex="13" value="<?= addslashes($data['zipcode']) ?>" data-hintold="Enter a valid zipcode.">
                                </div>
                            </div>	
                        </div>


                        <div class="form-group radio-group">
                            <div class="maxl">
                                <div class="form-row1">
                                    <div class="full-width align-left">
                                        <label for="gender">Gender <span class="red">*</span></label>
                                    </div>
                                    <div class="full-width align-left">
                                        <input data-hj-allow="true" type="hidden" data-f-count="2" data-fl_1="gender_m" data-fl_2="gender_f" class="patient-enroll-progress-2" data-step-enroll="2">
                                        <label for="gender_m" class='rb-container no_width'>Male
                                            <input data-hj-allow="true" autocomplete="nope" type="radio" tabindex="14" data-type="patient" id="gender_m" name="gender" value="M" <?= ($data['gender'] && $data['gender'] == 'M') ? 'checked' : '' ?> class="LoNotSensitive" preload="" >
                                            <span class="rb-checkmark"></span>
                                        </label>
                                        <label for="gender_f" class='rb-container no_width'>Female
                                            <input data-hj-allow="true" autocomplete="nope" type="radio" tabindex="15" data-type="patient" id="gender_f" name="gender" value="F" <?= ($data['gender'] && $data['gender'] == 'F') ? 'checked' : '' ?> class="LoNotSensitive " >
                                            <span class="rb-checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>

                        <div class="form-group radio-group">
                            <div class="maxl">
                                <div class="full-width align-left">
                                    <label for="is_minor">Is this application on behalf of a minor? <span class="red">*</span></label>
                                </div>
                                <div class="full-width align-left">
                                    <input data-hj-allow="true" type="hidden" data-f-count="2" data-fl_1="is_minor_yes" data-fl_2="is_minor_no" class="patient-enroll-progress-2" data-step-enroll="2">
                                    <label for="is_minor_yes" class='rb-container no_width'>Yes
                                        <input data-hj-allow="true" onchange="changeIsMinor()" autocomplete="nope" type="radio" tabindex="16" data-type="patient" id="is_minor_yes" name="is_minor" value="1" <?= ($data['is_minor'] == '1') ? 'checked' : '' ?> class="LoNotSensitive " <?php echo ((in_array('is_minor', $radios_submitted) && $data['is_minor'] != '') ? 'preload="' . (int) $data['is_minor'] . '"' : ''); ?> >
                                        <span class="rb-checkmark"></span>
                                    </label>
                                    <label for="is_minor_no" class='rb-container no_width'>No
                                        <input data-hj-allow="true" onchange="changeIsMinor()" autocomplete="nope" type="radio" tabindex="17" data-type="patient" id="is_minor_no" name="is_minor" value="0" <?= ($data['is_minor'] == '0') ? 'checked' : '' ?> class="LoNotSensitive " >
                                        <span class="rb-checkmark"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <script>
                            jQuery('select[name="p_state"], select[id="p_state"]').val('<?= $data['state'] ?>').trigger('change').trigger('blur');




                            function changeIsMinor() {
                                if (jQuery('[name=is_minor]:checked').length) {
                                    if (jQuery('[name="is_minor"]:checked').val() == 1) {
                                        jQuery('.patient_parent_profile').show();
                                    } else {
                                        jQuery('.patient_parent_profile').hide();
                                        jQuery('#p_parent_first_name').val('');
                                        jQuery('#p_parent_middle_initial').val('');
                                        jQuery('#p_parent_last_name').val('');
                                        jQuery('#p_parent_phone').val('');
                                    }
                                } else {
                                    jQuery('.patient_parent_profile').hide();
                                    jQuery('#p_parent_first_name').val('');
                                    jQuery('#p_parent_middle_initial').val('');
                                    jQuery('#p_parent_last_name').val('');
                                    jQuery('#p_parent_phone').val('');
                                }
                            }
                            setTimeout(function(){changeIsMinor();},1000);
                        </script>
                        <div class="form-row1 patient_parent_profile no-show">
                            <div class="form-group">
                                <div class="full-width">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" tabindex="18" data-type="patient" id="p_parent_first_name" name="p_parent_first_name" value="<?php echo htmlspecialchars(stripslashes($data['parent_first_name'])); ?>" class="LoNotSensitive" placeholder="Parent/Guardian First Name *" title="Parent/Guardian First Name *">
                                </div>
                            </div>	
                            <div class="form-group">	
                                <div class="full-width">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" tabindex="19" data-type="patient" id="p_parent_middle_initial" name="p_parent_middle_initial" value="<?php echo htmlspecialchars(stripslashes($data['parent_middle_initial'])); ?>" maxlength="1" class="LoNotSensitive" placeholder="Parent/Guardian Middle Initial" title="Parent/Guardian Middle Initials">
                                </div>
                            </div>
                            <div class="form-group">	
                                <div class="full-width">
                                    <input data-hj-allow="true" autocomplete="nope" type="text" tabindex="20" data-type="patient" id="p_parent_last_name" name="p_parent_last_name" value="<?php echo htmlspecialchars(stripslashes($data['parent_last_name'])); ?>" class="LoNotSensitive" placeholder="Parent/Guardian Last Name *" title="Parent/Guardian Last Name *">
                                </div>
                            </div>
                            <div class="clear"></div>

                            <input data-hj-allow="true" autocomplete="nope" type="text" tabindex="21" data-type="patient" id="p_parent_phone" name="p_parent_phone" value="<?php echo $data['parent_phone']; ?>" class="LoNotSensitive customphone" placeholder="Parent/Guardian Phone *" title="Parent/Guardian Phone *">
                        </div>
                        <div class="form-group">
                                    <div class="full-width"><input data-hj-allow="true" tabindex="22" autocomplete="nope" type="text" data-type="patient" name="alternate_contact_name" value="<?php echo htmlspecialchars(stripslashes($data['alternate_contact_name'])); ?>" class="LoNotSensitive" placeholder="Alternate Contact Name" title="Alternate Contact Name"></div>
                                </div>
                                <div class="form-group">
                                    <div class="full-width"><input data-hj-allow="true" tabindex="23" autocomplete="nope" type="text" data-type="patient" name="alternate_phone" value="<?php echo $data['alternate_phone']; ?>" class="LoNotSensitive not_required_phone" placeholder="Alternate Contact Phone" title="Alternate Contact Phone"></div>
                                </div>
                        <div class="form-group">

                            <div class="full-width">
                                <select data-hj-allow="true" data-type="patient" name="p_hear_about" tabindex="24" id="p_hear_about" data-value="<?php echo htmlspecialchars(stripslashes($data['p_hear_about'])); ?>" class="full-width LoNotSensitive form-control patient-enroll-progress-2" placeholder="How did you hear about Prescription Hope? *" title="How did you hear about Prescription Hope? *" data-step-enroll="2">
                                    <option value="">How did you hear about Prescription Hope? *</option>
                                    <?php /* if(!empty($data['p_application_source']) && count($broker_array) == 2){
                                      $broker_name =  trim(substr($data['p_application_source'],9));
                                      ?>
                                      <option value="<?php echo $data['p_application_source'];?>"><?php echo $broker_name;?></option>
                                      <?php } */ ?>
                                    <option value="Facebook">Facebook</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="Google">Google</option>
                                    <option value="Insurance">Insurance</option>
                                    <option value="Healthcare Provider">Healthcare Provider</option>
                                    <option value="Pharmacy">Pharmacy</option>
                                    <option value="Family Member">Family Member</option>
                                    <option value="Friend">Friend</option>
                                    <option value="Previous Patient">Previous Patient</option>
                                    <option value="Referral By Current Member of the Prescription Hope Program">Referral By Current Member of the Prescription Hope Program</option>
                                    <option value="Other">Other</option>
                                </select>

                            </div>
                            <input data-hj-allow="true" autocomplete="nope" type="hidden" data-type="patient" name="p_application_source" value="<?= ((isset($data['p_application_source'])) ? $data['p_application_source'] : '') ?>">
                        </div>
                        


                        <div class="checked-class">			
                            <div class="condition">
                                <label >Would you like to receive text message updates?</label><br/>	
                                <div class="form-check">	

                                    <label class="form-check-label cb-container checkbox-label" for="register_terms">
                                        <input data-hj-allow="true" type="checkbox" tabindex="25" class="form-check-input checkbox-normal" id="register_terms" name="register_terms"><span class="cb-checkmark"></span>								

                                        <span>By selecting this checkbox, you agree to receive important enrollment <br/>information, 
                                            updates, reminders, and promotional messages from Prescription<br/> Hope directly to your phone.
                                            Message frequency varies. Text <a target="_blank" href="https://prescriptionhope.com/privacy-policy/">HELP to 964673</a> for <br/>help, <a target="_blank" href="https://prescriptionhope.com/terms-of-service/">STOP to 964673</a> to stop, Message and Data Rates May Apply. 
                                            By opting<br/> in, you authorize Prescription Hope to deliver messages using an automatic <br/>telephone dialing system, and you
                                            understand that you are not required to<br/> opt-in as a condition of purchasing any property, goods, or services. By leaving<br/> this checkbox unchecked, you will not opt-in for SMS messages at this time.</span> </label>		
                                    <label for="register_terms" class="error" style="display:none;"></label> 
                                </div>							
                            </div>

                            <div class="condition">
                                <div class="form-check">								
                                    <label class="form-check-label cb-container checkbox-label" for="ph_register_terms_email">
                                        <input data-hj-allow="true" type="checkbox" tabindex="26" class="form-check-input checkbox-normal" id="ph_register_terms_email" name="ph_register_terms_email"><span class="cb-checkmark"></span>								
                                        <span>By selecting this checkbox, you understand and agree to Prescription Hope's <a target="_blank" href="https://prescriptionhope.com/privacy-policy/">privacy policy</a> and <a target="_blank" href="https://prescriptionhope.com/terms-of-service/">terms of service</a> Prescription Hope will send you emails with important enrollment information,
                                            updates, and reminders. You can unsubscribe at any time by clicking the link at the bottom of any Prescription Hope email.*</span> </label>
                                    <label for="ph_register_terms_email" class="error" style="display:none;"></label>
                                </div>							
                            </div>
                        </div>					
                        <div class="row">				
                            <div class="col-xs-12 col-sm-12 col-xs-12">
                                <div id='recaptcha' class="g-recaptcha" data-sitekey="6LefboYUAAAAAJKoAxSTReZ4zKOQG91mGboDq_MS" data-callback="onSubmit" data-size="invisible"></div>
                                <input data-hj-allow="true" tabindex="27" type="button" name="register_submit" id="btSubmit" value="Submit" class="big-button loginPageButton btn btn-default bt btn-block btn-lg">
                                <!--<a href="#" class="btn btn-default bt btn-block btn-lg">CREATE ACCOUNT</a>-->
                            </div>
                        </div>
                    </div>		
                </form>	
            </div>
        </div>

        <div class=" column L10 ph-mobile-view">
            <div id="owl-demo-mobile" class="owl-carousel owl-theme  bg-image-profile ">
                <div class="item">	
                    <div class="testimonials col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h3>
                            <span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
                            <span class="text-testimonials">When the rep from Prescription Hope called to tell me I was qualified, I was speechless. They saved our home, they saved my health, they lifted that enormous financial burden at a time when we needed it most.</span>
                            <p><span>- Theresa from Oregon</span></p>
                        </h3>
                    </div>
                </div>
                <div class="item">	
                    <div class="testimonials col-lg-12 col-md-12 col-sm-12  col-xs-12">
                        <h3>
                            <span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
                            <span class="text-testimonials">Prescription Hope has allowed me and my family to do several other things because it has freed up funds from really expensive medication.</span>
                            <p><span>- Stephen from Iowa</span> </p>
                        </h3>
                    </div>		
                </div>
                <div class="item">
                    <div class="testimonials col-lg-12 col-md-12 col-sm-12  col-xs-12">
                        <h3>
                            <span class="t1"><img src="/enrollment/images/Account/testimonial-arrow.png"></span>
                            <span class="text-testimonials">They were the most kind helpful people I have ever spoken with. The experience has been life-changing.</span>
                            <p><span>- Mary From Indiana</span> </p>
                        </h3>
                    </div>	
                </div>
            </div>
        </div>

    </div>
</div>

<div class="container footer">
    <div class="col-sm-6 col-xs-12 safe-enroll pull-left">
        <div class="text-safe pull-left">
            <ul class="links">
                <li><a href="https://prescriptionhope.com/privacy-policy/">Privacy Policy</a></li> |
                <li><a href="https://prescriptionhope.com/terms-of-service/">Terms of Service</a></li> |
                <li><p class="copy">2021 ©Prescription Hope, Inc.</p></li>
            </ul>
        </div>
    </div>
    <div class="col-sm-6 col-xs-12 safe-enroll pull-right">
        <div class="text-safe pull-right">
            <img class="padding-right-30 ttip" src="https://prescriptionhope.com/wp-content/themes/prescription_theme/images/new-images/256-shield.png" data-text="hidetext" data-hint="We keep your information safe and protected. It is secured by 256-bit encryption, the same security banks use." />
            <img src="images/mcafee-trans.png">
        </div>
    </div>
</div>

<!--<div class="container">
        <div class="col-sm-12 col-xs-12 safe-enroll">
                <div class="text-safe pull-right">
                        <img class="padding-right-30 ttip" src="/wp-content/themes/prescription_theme/images/new-images/256-shield.png" data-text="hidetext" data-hint="We keep your information safe and protected. It is secured by 256-bit encryption, the same security banks use." />
                        <img src="images/mcafee-trans.png">
                </div>
        </div>
</div>-->

<div id="overlay" style="display:none;"><div id="overlay_holder"></div></div>
<div id="overlay_missing_email" class="overlay_content">
    <div class="overlay_loaded_content">
        <div class="overlay_form text-center">
            <p class="text-18 red">The email address entered does not match what we have on file for your account.  In order to keep your information as safe as possible, please contact a patient advocate at 1-877-296-4673 between 8:00am and 4:00pm Eastern Time.</p>
        </div>
        <div class="text-center"></div>
    </div>
</div>

<script type="text/javascript">
    var captchaValid = false;
    jQuery(document).ready(function () {
        jQuery.validator.addMethod("ascii", function (value, element) {
            return this.optional(element) || /^[\x00-\x7F]*$/.test(value);
        }, "Please insert only alphanumeric characters.");
        jQuery.validator.addMethod("lettersonly", function (value, element) {
            return this.optional(element) || /^[a-z'. ]+$/i.test(value);
        }, "Please insert only letters.");
        //jQuery.validator.addMethod("password_aA1", function(value, element) { return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,20}$/.test(value); }, "Please use upper & lower case letters and numbers.");
        jQuery.validator.addMethod("password_aA1", function (value, element) {
            return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[\W\w]{8,20}$/.test(value);
        }, "Must use between 8-20 characters, upper and lower case letters and numbers.");
        jQuery.validator.addMethod("custom_date", function (t, e) {
            return t = t.replace(/\s+/g, ""), td = t.split("/"), td = td[2] + "-" + td[0] + "-" + td[1], this.optional(e) || t.length > 8 && t.match(/^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/]\d{4}$/) && td <= new Date().toISOString().substring(0, 10);
        }, "Please specify a valid date (mm/dd/yyyy)");
        //jQuery.validator.addMethod("same_as", function(value, element, param) { return this.optional(element) || value == $(param).val(); }, "");
        // jQuery.validator.addMethod("phonenu", function (value, element) {
        //       if ( /^\d{3}-?\d{3}-?\d{4}$/g.test(value)) {
        //           return true;
        //       } else {
        //           return false;
        //       };
        //   }, "Invalid phone number");
        jQuery.validator.addMethod("customphone", function (phone_number, element) {
            phone_number = phone_number.replace(/\s+/g, "");
            return this.optional(element) || phone_number.length > 9 &&
                    phone_number.match(/^(\+?1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
        }, "Please enter a valid phone number");

        //  jQuery.validator.addMethod('customphone', function (value, element) {
        //     return this.optional(element) || /^\d{3}-\d{3}-\d{4}$/.test(value);
        // }, "Please enter a valid phone number");


        formValidation = jQuery("#fmRegister").validate({
            rules: {
                first_name: {required: true, lettersonly: true},
                middle_initial: {required: false, lettersonly: true, ascii: true, maxlength: 1},
                last_name: {required: true, lettersonly: true},
                email: {required: true, email: true},
                email_confirm: {required: true, equalTo: '#email'},
                password: {required: true, password_aA1: true, minlength: 8, maxlength: 20},
                password_confirmation: {required: true, equalTo: '#password'},
                register_terms: {required: true},
                ph_register_terms_email: {required: true},
                gender: {required: true},
                is_minor: {required: true},
                phone: {required: true, phoneUS: true},
                p_parent_first_name: {required: jQuery("#p_is_minor_yes"), ascii: true},
                p_parent_last_name: {required: jQuery("#p_is_minor_yes"), ascii: true},
                p_parent_phone: {required: jQuery("#p_is_minor_yes"), phoneUS: true},
                address: {required: true, ascii: true},
                city: {required: true, ascii: true},
                state: {required: true},
                p_hear_about: {required: true},
            },

            messages: {
                first_name: {required: 'Please enter your first name.', lettersonly: 'Only alphabets are allowed.'},
                middle_initial: {maxlength: 'Please enter no more than 1 character.'},
                last_name: {required: 'Please enter your last name.'},
                email: {required: 'Please enter your email address.'},
                email_confirm: {required: 'Please confirm your email address.', equalTo: 'Please enter the same email address again.'},
                password: {required: 'Please enter your password.'},
                password_confirmation: {equalTo: 'Please enter the same password again.'},
                p_hear_about: {required: 'This field is required.'},
            },
            highlight: function (element) {
                jQuery(element).removeClass("correct");
                jQuery(element).addClass("error");
                jQuery(element.form).find("label[for=label_for_" + element.id + "]").addClass('has-error');
            },
            unhighlight: function (element) {
                //if(jQuery(element).hasClass('error')){
                jQuery(element).removeClass("error");
                jQuery(element).removeClass("correct");
                if (jQuery(element).val() != "") {
                    jQuery(element).addClass("correct");
                }
                //jQuery(element).addClass("correct");
                //}
            },
            invalidHandler: function () {
                jQuery('#fmMsg').addClass('has-error').addClass('no-bold').html('Please fill out correctly all the fields marked with red and then try again to submit the form.');
            },
            onkeyup: false
        });

        // remove check mark from the empty value
        jQuery('input').keyup(function () {
            if (jQuery(this).val() == '') {
                jQuery(this).removeClass('correct');
            }
        });

        jQuery('input[name="email"]').on('blur', function () {
            if (formValidation.valid('#email')) {
                //add email to SalesForce
                //syncEmail();
            }
        });
        // jQuery('input[name="register_terms"]').on('click', function () {
        // 	if (jQuery('input[name="register_terms"]').is(':checked')) {
        // 		jQuery('input[name="register_terms"]').parent().parent().removeClass('checkbox-with-label-error');
        // 		jQuery('input[name="register_terms"]').parent().parent().addClass('checkbox-with-label-correct');
        // 	} else {
        // 		jQuery('input[name="register_terms"]').parent().parent().removeClass('checkbox-with-label-correct');
        // 		jQuery('input[name="register_terms"]').parent().parent().addClass('checkbox-with-label-error');
        // 	}
        // });

        jQuery('input[name="ph_register_terms_email"]').on('click', function () {
            if (jQuery('input[name="ph_register_terms_email"]').is(':checked')) {
                jQuery('input[name="ph_register_terms_email"]').parent().parent().removeClass('checkbox-with-label-error');
                jQuery('input[name="ph_register_terms_email"]').parent().parent().addClass('checkbox-with-label-correct');
            } else {
                jQuery('input[name="ph_register_terms_email"]').parent().parent().removeClass('checkbox-with-label-correct');
                jQuery('input[name="ph_register_terms_email"]').parent().parent().addClass('checkbox-with-label-error');
            }
        });

        jQuery('.showpassword').click(function () {
            var pswdFieldId = jQuery(this).attr('id').replace('_show', '');
            var pswdType = jQuery('#' + pswdFieldId).attr('type');
            if (pswdType == 'password') {
                jQuery(this).find('span').removeClass('eye-open').addClass('eye-close');
                jQuery('#' + pswdFieldId).attr('type', 'text');
            } else {
                jQuery(this).find('span').removeClass('eye-close').addClass('eye-open');
                jQuery('#' + pswdFieldId).attr('type', 'password');
            }
        });

        jQuery('#btSubmit').on('click', function (e) {
            console.log(captchaValid);
            if (jQuery("#fmRegister").valid() && !captchaValid) {
                e.preventDefault();
                console.log(captchaValid);
                grecaptcha.execute();
            }
        });

<?php if (!$success) { ?>
            jQuery('input[name="email"]').trigger('keyup');
            jQuery('input[name="register_terms"]').attr('checked', <?= !(bool) $data['register_terms'] ?>);
            jQuery('input[name="register_terms"]').trigger('click');

            jQuery('input[name="ph_register_terms_email"]').attr('checked', <?= !(bool) $data['ph_register_terms_email'] ?>);
            jQuery('input[name="ph_register_terms_email"]').trigger('click');
<?php } ?>

        jQuery('.jvf').jvFloat();
        //jQuery('input, select').tooltip();
        jQuery(".ttip").each(showTooltipsText);


        // lOGIC FOR IDENTIFYING CAPS ON/OFF (start)
        var isShiftPressed = false;
        var isCapsOn = false;
        jQuery('input').bind("keydown", function (e) {
            var keyCode = e.keyCode ? e.keyCode : e.which;
            if (keyCode == 16) {
                isShiftPressed = true;
            }
        });
        jQuery('input').bind("keyup", function (e) {
            var keyCode = e.keyCode ? e.keyCode : e.which;

            if (keyCode == 16) {
                isShiftPressed = false;
            }
            if (keyCode == 20) {
                if (isCapsOn == true) {
                    isCapsOn = false;
                } else if (isCapsOn == false) {
                    isCapsOn = true;
                }
            }
        });
        jQuery('.caps_check').bind("keypress", function (e) {
            var keyCode = e.keyCode ? e.keyCode : e.which;
            if (keyCode >= 65 && keyCode <= 90 && !isShiftPressed) {
                isCapsOn = true;
            } else {
                isCapsOn = false;
            }
            if (isCapsOn == true) {
                console.log('ON');
                jQuery('#capsInfo').html('CAPS lock key turned ON.').fadeIn(100);
            } else {
                console.log('OFF');
                if (jQuery('#capsInfo').is(':hidden') == false) {
                    jQuery('#capsInfo').html('CAPS lock key turned OFF.').fadeOut(3000);
                }
            }
        });
        // lOGIC FOR IDENTIFYING CAPS ON/OFF (end)
        //onload();
        jQuery("#owl-demo").owlCarousel({
            navigation: true, // Show next and prev buttons
            slideSpeed: 300,
            paginationSpeed: 400,
            items: 1
        });

        jQuery("#owl-demo-mobile").owlCarousel({
            navigation: true, // Show next and prev buttons
            slideSpeed: 300,
            paginationSpeed: 400,
            items: 1
        });

        jQuery('.open_video').click(function () {
            jQuery('#video_holder').html('<div class="iframe-wrapper"><iframe src="https://www.youtube.com/embed/' + jQuery(this).attr('data-href') + '?rel=0&showinfo=0&autoplay=1" frameborder="0" allowfullscreen></iframe></div>');
            jQuery('#video_info').html('<p>Prescription Hope Story: ' + jQuery(this).attr('data-name') + ' </p>');
            jQuery('#video_box').show();
        });
        jQuery('#close_video_box').click(function () {
            jQuery('#video_holder, #video_info').html('');
            jQuery('#video_box').hide();
        });
    });

// Google Invisible captcha
    function onSubmit(token) {
        console.log('Thanks for registering with us');
        captchaValid = true;
        jQuery("#fmRegister").submit();
    }

// jQuery("#phone").mask("999-9999-9999");
    jQuery("input[name='phone']").mask("000-000-0000", {clearIfNotMatch: true});
    jQuery("input[name='p_parent_phone']").mask("000-000-0000", {clearIfNotMatch: true});
    jQuery("input[name='alternate_phone']").mask("000-000-0000", {clearIfNotMatch: true});


    var hear_about_extra_1_value = "<?= htmlspecialchars(stripslashes($data['p_hear_about_1'])); ?>";
    var hear_about_extra_2_value = "<?= htmlspecialchars(stripslashes($data['p_hear_about_2'])); ?>";
    var hear_about_extra_3_value = "<?= htmlspecialchars(stripslashes($data['p_hear_about_3'])); ?>";
    setTimeout(function(){
        hear_about_extra_1_value = "<?= htmlspecialchars(stripslashes($data['p_hear_about_1'])); ?>";
        hear_about_extra_2_value = "<?= htmlspecialchars(stripslashes($data['p_hear_about_2'])); ?>";
        hear_about_extra_3_value = "<?= htmlspecialchars(stripslashes($data['p_hear_about_3'])); ?>";
        jQuery('#p_hear_about').val('<?php echo $data['hear_about']; ?>').trigger('change');
    },1000);

    jQuery('#p_hear_about').change(updateHearAboutExtras);
    if (jQuery('#p_hear_about').is("select") && (jQuery('#p_hear_about').val() != '' || jQuery('#p_hear_about').data('value') != '')) {
        jQuery('#p_hear_about').val(jQuery('#p_hear_about').data('value'));
        jQuery('#p_hear_about').trigger('change');
    }
</script>

<?php include('_footer.php'); ?>
