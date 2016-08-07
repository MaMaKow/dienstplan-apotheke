<?php 
require '../default.php';
require '../db-verbindung.php';

$abfrage  = "SELECT YEAR(MAX(`Datum`)) FROM `pep`";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
$Maximaljahr = mysqli_fetch_row($ergebnis);
$abfrage  = "SELECT YEAR(MIN(`Datum`)) FROM `pep`";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
$Minimaljahr = mysqli_fetch_row($ergebnis);


for ($jahr=$Minimaljahr[0]; $jahr<=$Maximaljahr[0]; $jahr++) #Montag=2, Dienstag=3
{
	$abfrage='SELECT DISTINCT MONTH(`Datum`) FROM `pep` WHERE YEAR(Datum) = \''.$jahr.'\';';
	$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($monat = mysqli_fetch_row($ergebnis))
	{
		$abfrageb = "SELECT sum(`Anzahl`) FROM `pep` WHERE MONTH(`Datum`) = '".$monat[0]."' AND YEAR(`Datum`) = ".$jahr." AND `Anzahl` < 20";
		$ergebnisb = mysqli_query($verbindungi, $abfrageb) OR die ("Error: $abfrageb <br>".mysqli_error($verbindungi));
		while($rowb = mysqli_fetch_row($ergebnisb))
		{
			$Packungen[$monat[0]][]=$rowb[0];
		}	
	}
#Es werden bis zu 19 Artikel im Abverkauf regelmäßig gefunden. Größere Vorgänge sind gegenseitige Bestellungen oder andere Buchungen.
#Bestimmt anhand einer Gauß-Verteilungs-Kurve
#SELECT `Anzahl`, COUNT(*) FROM `pep` GROUP BY `Anzahl` 
}


$txt="";
for ($monat=1; $monat<=12; $monat++)
{
	$txt.=$monat.", ".calculate_percentile($Packungen[$monat],5) .", ". calculate_percentile($Packungen[$monat],50) .", ".  calculate_percentile($Packungen[$monat],95) . "\n";
}
$filename = "pep_monatimjahr.csv";
$myfile = fopen($filename, "w") or die(" Unable to open file $filename!");
fwrite($myfile, $txt);
fclose($myfile);
$txt= "";
?>
