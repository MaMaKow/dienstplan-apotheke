<?php
//Hier schauen wir, welche Daten an uns übersendet wurden und aus welchem Formular sie stammen.
//Im Gegensatz zu post-auswertung wird hier nach links gesucht, die über $_GET gesendet wurden.
if (!empty($_GET))
{
	if (isset($_GET['datum']))
	{
		$datum=htmlspecialchars($_GET['datum']);
	}
	if (isset($_GET['auswahl_mitarbeiter']))
	{
		$auswahl_mitarbeiter=htmlspecialchars($_GET['auswahl_mitarbeiter']);
	}
}
?>
