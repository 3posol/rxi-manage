<?php

session_start();
unset($_SESSION[$session_key]);
//session_destroy();

header('Location: ../patients-dashboard/login.php');