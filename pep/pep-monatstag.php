<?php 
require '../default.php';
require '../db-verbindung.php';

for ($tag=1; $tag<=31; $tag++)
{
	$abfrage='SELECT DISTINCT `Datum`  FROM `pep` WHERE DAYOFMONTH(Datum) = \''.$tag.'\';';
	$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($row = mysqli_fetch_object($ergebnis))
	{
			$abfrageb = "SELECT sum(`Anzahl`) FROM `pep` WHERE `Datum` = '".$row->Datum."' AND DAYOFWEEK(`Datum`) BETWEEN 2 AND 6 AND `Anzahl` < 20";
			$ergebnisb = mysqli_query($verbindungi, $abfrageb) OR die ("Error: $abfrageb <br>".mysqli_error($verbindungi));
			while($rowb = mysqli_fetch_row($ergebnisb))
			{
				if ($rowb[0]!=""){$Packungen[$tag][]=$rowb[0];}
			}
	}
}

$txt="";
for ($tag=1; $tag<=31; $tag++) 
{
	$txt.=$tag.", ". calculate_percentile($Packungen[$tag],5) .", ". calculate_percentile($Packungen[$tag],50) .", ".  calculate_percentile($Packungen[$tag],95) . "\n";
}
$filename = "pep_monatstag.csv";
$myfile = fopen($filename, "w") or die(" Unable to open file $filename!");
fwrite($myfile, $txt);
fclose($myfile);
$txt= "";




?>
