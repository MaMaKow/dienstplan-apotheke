<?php
	require "config/config.php";
//	file_put_contents('config/config.php', '<?php  $config =' . var_export($config, true) . ';');
	setlocale(LC_TIME, "de_DE.utf8");
	//setlocale(LC_ALL, 'de_DE'); // Leider versteht die Datenbank dann nicht mehr, was die Kommata sollen.
	error_reporting(E_ALL); //debugging
	ini_set("display_errors", 1); //debugging
?>
