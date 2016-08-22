<?php
/*Die Konfiguration könnte über die Datei default.php eingelesen werden. */
$config = array(
'database_name'=>"Apotheke", //Name der MySQL-Datenbank
'database_user'=>"apotheke", //Name des Datenbankbenutzers
'application_name'=>"Dienstplan Apotheke", //Für den Title des HTML HEAD
'LC_TIME'=>"de_DE.utf8",
'mb_internal_encoding'=>"UTF-8",
'error_reporting'=>32767,
'hide_disapproved'=>false
);


//Diese Datei kann später mit folgendem Befehl neu geschrieben werden:
//	file_put_contents('config/config.php', '<?php  $config =' . var_export($config, true) . ';');

?>
