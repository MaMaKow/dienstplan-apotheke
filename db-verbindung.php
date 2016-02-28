<?php
$verbindungi = new mysqli("localhost", $config['database_user'], $config['database_password'] , $config['database_name'] );
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
// change character set to utf8
if (!$verbindungi->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $verbindungi->error);
} else {
//    printf("Current character set: %s\n", $verbindungi->character_set_name());
}
?>
