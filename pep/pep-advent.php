<?php 
require '../default.php';
require '../db-verbindung.php';
$abfrage  = "SELECT YEAR(MAX(`Datum`)) FROM `pep`";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
$Maximaljahr = mysqli_fetch_row($ergebnis);
$abfrage  = "SELECT YEAR(MIN(`Datum`)) FROM `pep`";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
$Minimaljahr = mysqli_fetch_row($ergebnis);


for ($jahr=$Minimaljahr[0]; $jahr<=$Maximaljahr[0]; $jahr++) 
{
$Advent[0]  = date('Y-m-d', strtotime("-1 saturday",mktime(0,0,0,11,18,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres. Also ist der 
$Advent[1]  = date('Y-m-d', strtotime("+1 saturday",mktime(0,0,0,11,18,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres. Also ist der 
$Advent[2]  = date('Y-m-d', strtotime("+2 saturday",mktime(0,0,0,11,18,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres. Also ist der 
$Advent[3]  = date('Y-m-d', strtotime("+3 saturday",mktime(0,0,0,11,18,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres. Also ist der 
$Advent[4]  = date('Y-m-d', strtotime("+4 saturday",mktime(0,0,0,11,18,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres. Also ist der 
echo "$jahr\n";
	//Wir suchen die Advents-Samstage, nicht den eigentlichen Advent. Der ist natürlich am Sonntag.
	for ($advent=0; $advent<=4; $advent++) // wir nutzen den nullten Advent als Referenzsamstag.
	{
	echo "$advent";
//		$Advent[$advent]  = date('Y-m-d', strtotime("+".$advent."saturday",mktime(0,0,0,11,18,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres. Also ist der 
//		$zweiter_advent = date('Y-m-d', strtotime("+2 saturday",mktime(0,0,0,11,25,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres.
//		$dritter_advent = date('Y-m-d', strtotime("+3 saturday",mktime(0,0,0,11,25,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres.
//		$vierter_advent = date('Y-m-d', strtotime("+4 saturday",mktime(0,0,0,11,25,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres.
//		$referenzsamstag = date('Y-m-d', strtotime("-1 saturday",mktime(0,0,0,11,25,$jahr))); //Der erste Advent ist der erste Sonntag nach dem 26. November eines jeden Jahres.

		$tagesbeginn=strtotime("08:00:00");
		$tagesende = strtotime("20:00:00");
		for ($uhrzeit=$tagesbeginn; $uhrzeit<$tagesende; $uhrzeit=strtotime("+1 minutes", $uhrzeit))
		{
			$anfangszeit=date('G:i:s', strtotime('-10 minutes', $uhrzeit));
			$endzeit=date('G:i:s', strtotime('+10 minutes', $uhrzeit));
			$abfrageb = "SELECT sum(`Anzahl`) FROM `pep` WHERE `Datum` = '".$Advent[$advent]."' AND `Zeit` BETWEEN '".$anfangszeit."' AND '".$endzeit."' AND `Anzahl` < 20"; //Summe der verkauften Packungen zu dieser Urzeit
			echo "$abfrageb\n";
			$ergebnisb = mysqli_query($verbindungi, $abfrageb) OR die ("Error: $abfrageb <br>".mysqli_error($verbindungi));
			while($rowb = mysqli_fetch_row($ergebnisb))
			{
				$Packungen[$advent][$uhrzeit][]=$rowb[0];
			}	
		}
	}
}
echo "Here we are";
function calculate_percentile($arr,$perc) {
    sort($arr);
    $count = count($arr); //total numbers in array
    $middleval = floor(($count-1)*$perc/100); // find the middle value, or the lowest middle value
    if($count % 2) { // odd number, middle is the median
        $median = $arr[$middleval];
    } else { // even number, calculate avg of 2 medians
        $low = $arr[$middleval];
        $high = $arr[$middleval+1];
        $median = (($low+$high)/2);
    }
    return $median;
}

$txt="";
for ($advent=0; $advent<=4; $advent++) 
{
//	for ($uhrzeit=8; $uhrzeit<20; $uhrzeit++)
	foreach($Packungen[$advent] as $uhrzeit => $ZeitPackungen)
	{
		$txt.=date('G:i:s', $uhrzeit).", ".calculate_percentile($Packungen[$advent][$uhrzeit],5) .", ". calculate_percentile($Packungen[$advent][$uhrzeit],50) .", ".  calculate_percentile($Packungen[$advent][$uhrzeit],95) . "\n";
	}
	$filename = "pep_advent".$advent.".csv";
	$myfile = fopen($filename, "w") or die(" Unable to open file $filename!");
	fwrite($myfile, $txt);
	fclose($myfile);
	$txt= "";
}




#echo "Montags ab 8 bis 9 werden durchschnittlich ".$packungsmedian." Packungen abgegeben. <br>";
#echo "Überwiegend sind es ".calculate_percentile($Packungen[2][8],25)." bis ".calculate_percentile($Packungen[2][8],75)." Packungen.<br>";

echo "<html><pre>"; var_dump($Advent); echo"</pre></html>";
//echo "Das Script wurde ausgeführt.";
?>
