<?php

$url_query = (count($_GET) > 0) ? '?' . http_build_query($_GET) : '';

header('Location: register.php' . $url_query);

?>