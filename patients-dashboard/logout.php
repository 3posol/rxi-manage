<?php

session_start();
unset($_SESSION['PLP']);
//session_destroy();

header('Location: login.php');