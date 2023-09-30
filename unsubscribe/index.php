<?php
    require_once('includes/functions.php');
       session_start();
       $_SESSION["cg_email_unsubscribe"] = "You have been unsubscribed.";

  if (isset($_GET['key']) && $_GET['key'] && isset($_GET['type']) && $_GET['type']) {
            $key = $_GET['key'];
            $type = $_GET['type'];
            $key =base64_decode($key);
        
             
            
                    
        $data = array(
        'command' => 'email_unsubscribe',
         'patient' => $_SESSION[$session_key]['data']['id'],
//         'access_code' => $_SESSION[$session_key]['access_code'],
            'key'=>$key,
            'type'=>$type
    );
        
       $email_unsubscribe = api_command($data);
      header('Location: '  . '/patients-dashboard/login.php');
            

        }


       
    
    
    
    
    
    ?>