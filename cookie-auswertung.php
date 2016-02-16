<?php
	#In order to set a cookie we must do this before any text has been written to the browser.
	if(isset($_COOKIE["auswahlMitarbeiter"])) 
	{
		$auswahlMitarbeiter=$_COOKIE["auswahlMitarbeiter"];
	}
	if(isset($_COOKIE["mandant"])) 
	{
		$mandant=$_COOKIE["mandant"];
	}
	if(isset($_COOKIE["datum"])) 
	{
		$datum=$_COOKIE["datum"];
	}
?>
