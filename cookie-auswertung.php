<?php
	#In order to set a cookie we must do this before any text has been written to the browser.
	if(isset($_COOKIE["auswahl_mitarbeiter"]))
	{
		$auswahl_mitarbeiter=$_COOKIE["auswahl_mitarbeiter"];
	}
	if(isset($_COOKIE["mandant"]))
	{
		$mandant=$_COOKIE["mandant"];
	}
	if(isset($_COOKIE["datum"]))
	{
		$datum=$_COOKIE["datum"];
	}
	if(isset($_COOKIE["year"])) 
	{
		$year=$_COOKIE["year"];
	}
