<?php
require 'default.php';
require 'db-verbindung.php';
$mandant = 1;    //Wir zeigen den Dienstplan standardmäßig für die "Hauptapotheke" Mandant 1

$datum = date('Y-m-d'); //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."
$year = date('Y');

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
require 'cookie-auswertung.php'; //Auswerten der per COOKIE gespeicherten Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
if (isset($mandant)) {
    create_cookie('mandant', $mandant);
}
if (isset($datum)) {
    create_cookie('datum', $datum);
}
if (isset($year)) {
    create_cookie('year', $year);
}

//Hole eine Liste aller Mandanten (Filialen)
require 'db-lesen-mandant.php';

$abfrage = "SELECT * FROM Notdienst WHERE YEAR(Datum) = $year AND Mandant = $mandant";
$ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage <br>".mysqli_error($verbindungi));
while ($row = mysqli_fetch_object($ergebnis)) {
    $Notdienste['VK'][] = $row->VK;
    $Notdienste['Datum'][] = $row->Datum;
    $Notdienste['Mandant'][] = $row->Mandant;
}
?>

<html>
	<head>
		<meta charset=UTF-8>
		<link rel="stylesheet" type="text/css" href="style.css" media="all">
		<link rel="stylesheet" type="text/css" href="print.css" media="print">
	</head>
	<body>
			<table border=1>
				<tr><td>Datum</td><td>Name</td><td>Ersatz</td></tr>
				<?php
                    foreach ($Notdienste['Datum'] as $key => $datum) {
                        echo "\n\t\t\t\t<tr><td>".date('d.m.Y', strtotime($Notdienste['Datum'][$key])).'</td>';
												echo '<td>';
												echo (isset($Mitarbeiter[$Notdienste['VK'][$key]])) ? $Mitarbeiter[$Notdienste['VK'][$key]] : "";
												echo '</td>';
												echo "<td style=width:40%></td></tr>";
                    }
            ?>

		</table>
	</body>
</html>
<?php
//echo "<pre>"; var_export($Notdienste); echo "<pre>";
?>
