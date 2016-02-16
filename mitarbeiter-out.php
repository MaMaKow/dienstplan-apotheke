<?php
require 'default.php';
require 'db-verbindung.php';
require 'schreiben-ics.php'; //Dieses Script enthält eine Funktion zum schreiben von kleinen ICS Dateien, die mehrere VEVENTs enthalten können.


//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';

//$datenübertragung="";
$dienstplanCSV="";
$tage=7;


$heute=date('Y-m-d');
$datum=$heute; //Dieser Wert wird überschrieben, wenn "$wochenauswahl und $woche per POST übergeben werden."
$montagsDifferenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
$montagsDifferenzString="-".$montagsDifferenz." day";
$datum=strtotime($montagsDifferenzString, strtotime($datum));
$datum=date('Y-m-d', $datum);



require 'cookie-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'get-auswertung.php'; //Auswerten der per GET übergebenen Daten.
require 'post-auswertung.php'; //Auswerten der per POST übergebenen Daten.
if(isset($_POST['submitAuswahlMitarbeiter']))
{
	$auswahlMitarbeiter=$_POST['auswahlMitarbeiter'];
	$Plan=$_POST['Dienstplan'];
	$datum=$Plan[0]['Datum'][0];
	//echo $datum;
}
elseif (isset($_POST['submitWocheRückwärts']) OR isset($_POST['submitWocheVorwärts']))
{
	$auswahlMitarbeiter=$_POST['auswahlMitarbeiter'];
}
elseif(!isset($auswahlMitarbeiter))
{
	$auswahlMitarbeiter=1;
}
if (isset($auswahlMitarbeiter))
{
	create_cookie("auswahlMitarbeiter", $auswahlMitarbeiter); 
}

if (isset($_GET['datum'])) // Dies ist eine Wochenansicht. Wir beginnen daher immer mit dem Montag.
{
	$montagsDifferenz=date("w", strtotime($datum))-1; //Wir wollen den Anfang der Woche
	$montagsDifferenzString="-".$montagsDifferenz." day";
	$datum=strtotime($montagsDifferenzString, strtotime($datum));
	$datum=date('Y-m-d', $datum);
}
require 'db-lesen-woche-mitarbeiter.php'; //Lesen der in der Datenbank gespeicherten Daten.
require 'db-lesen-feiertag.php';

$VKcount=count($Mitarbeiter); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//end($Mitarbeiter); $VKmax=key($Mitarbeiter); reset($Mitarbeiter); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax=max(array_keys($Mitarbeiter));
foreach($Dienstplan as $key => $Dienstplantag)
{
	$PlanAnzahl[]=(count($Dienstplantag['VK']));
} 
$planAnzahl=max($PlanAnzahl);




//Produziere die Ausgabe
?>
<html>
	<head>
		<meta charset=UTF-8>
		<link rel="stylesheet" type="text/css" href="style.css" media="all">
		<link rel="stylesheet" type="text/css" href="print.css" media="print">
	</head>
	<body>
<?php
require 'navigation.php';
echo "<div class=no-image>\n";
echo "\t\t<a href=woche-out.php?datum=".$datum.">Kalenderwoche ".strftime('%V', strtotime($datum))."</a><br>\n";
//echo "\t\tKalenderwoche ".strftime('%V', strtotime($datum))."<br>\n";
echo "\t\t<form id=myform method=post>\n";
$RückwärtsButton="\t\t\t<input type=submit 	class=no-print	value='1 Woche Rückwärts'	name='submitWocheRückwärts'>\n";echo $RückwärtsButton;
$VorwärtsButton="\t\t\t<input type=submit 	class=no-print	value='1 Woche Vorwärts'	name='submitWocheVorwärts'>\n";echo $VorwärtsButton;
$zeile="<br>";
//$zeile.="<select name=auswahlMitarbeiter class=no-print onChange=this.form.submit()>";
$zeile.="<select name=auswahlMitarbeiter class=no-print onChange=document.getElementById('submitAuswahlMitarbeiter').click()>";
$zeile.="<option value=$auswahlMitarbeiter>".$auswahlMitarbeiter." ".$Mitarbeiter[$auswahlMitarbeiter]."</option>,";
for ($vk=1; $vk<$VKmax+1; $vk++)
{
	if(isset($Mitarbeiter[$vk]))
	{
		$zeile.="<option value=$vk>".$vk." ".$Mitarbeiter[$vk]."</option>,";
	}
}
$zeile.="</select>";
echo $zeile;
$submitButton="\t<input type=submit value=Absenden name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n"; echo $submitButton; //name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
echo "<H1>".$Mitarbeiter[$auswahlMitarbeiter]."</H1>";

//echo "\t\t\t<table border=0 rules=groups style=width:99%>\n";
echo "\t\t\t<table border=1>\n";
echo "\t\t\t\t<thead>\n";
echo "\t\t\t\t<tr>\n";
for ($tag=0; $tag<count($Dienstplan); $tag++)
{//Datum
	$zeile="";
	echo "\t\t\t\t\t<td width=14%>";
	echo "<a href=tag-out.php?datum=".$Dienstplan[$tag]["Datum"][0].">";
	$zeile.="<input type=hidden size=2 name=Dienstplan[".$tag."][Datum][0] value=".$Dienstplan[$tag]["Datum"][0].">";
	$zeile.=strftime('%d.%m.', strtotime( $Dienstplan[$tag]["Datum"][0]));
	echo $zeile;
	if(isset($feiertag)){echo " ".$feiertag." ";}
	if(isset($notdienst)){echo " NOTDIENST ";}
//	echo "</td>\n";
//}	
//echo "\t\t\t\t</tr><tr>\n";
echo "\t\t\t\t<br>\n";
//for ($tag=0; $tag<count($Dienstplan); $tag++)
//{//Wochentag
	$zeile="";
//	echo "\t\t\t\t\t<td style=width:20%>";
//	echo "\t\t\t\t\t<td>";
	$zeile.=strftime('%A', strtotime( $Dienstplan[$tag]["Datum"][0]));
	echo $zeile;
	echo "</a></td>\n";
}
for ($j=0; $j<$planAnzahl; $j++)
{
	if(isset($feiertag) && !isset($notdienst)){break 1;}
	echo "\t\t\t\t</tr></thead><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{
		$zeile="";
		echo "\t\t\t\t\t<td align=right>&nbsp";
		//Dienstbeginn
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Dienstbeginn"][$j] > 0 ) 
		{
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
		}
		//Dienstende
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Dienstende"][$j] > 0 ) 
		{
			$zeile.=" bis ";
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstende"][$j]));
		}
		$zeile.="";
		echo $zeile;
		
		//Mittagspause
		$zeile="";
		echo "<br>\n\t\t\t\t";
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.=" Pause: ";
			$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
		}
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.=" bis ";
			$zeile.=strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
		}
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Stunden"][$j] > 0 )
		{
			$zeile.="<br><a href=stunden-out.php?auswahlMitarbeiter=".$Dienstplan[$i]["VK"][$j].">".$Dienstplan[$i]["Stunden"][$j]." Stunden";
		}
		$zeile.="";
		
		echo $zeile;
		echo "</td>\n";
	}
}
echo "\t\t\t\t</tr>\n";
echo "\t\t\t\t<tfoot>\n";

echo "\t\t\t\t</tr>\n"; //debug DEBUG THis one seems to be bulshit. There is a </tr> a few lines above before the start of this <tfoot>
echo "\t\t\t\t<tr>\n";
echo "\t\t\t\t\t<td colspan=$tage>\n";
//for ($tag=0; $tag<count($Dienstplan); $tag++)
for ($tag=0; $tag<5; $tag++) // Wir wollen nicht wirklich die ganze Woche. Es zählen nur die "Arbeitswochenstunden".
{
	foreach($Dienstplan[$tag]['Stunden'] as $key => $stunden)
	{
		$Stunden[$Dienstplan[$tag]['VK'][$key]][]=$stunden;
	}
}
echo "Wochenstunden ";
ksort($Stunden);
$i=1;$j=1; //Zahler für den Stunden-Array (wir wollen nach je x Elementen einen Umbruch)
foreach($Stunden as $mitarbeiter => $stunden)
{
	echo array_sum($stunden);
	echo " / ";
	echo $StundenMitarbeiter[$mitarbeiter];
	if ( $StundenMitarbeiter[$mitarbeiter] != array_sum($stunden))
	{
		$differenz=array_sum($stunden)-$StundenMitarbeiter[$mitarbeiter];
		echo " <b>( ".$differenz." )</b>";
	}
}
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t\t</tfoot>\n";
echo "\t\t\t</table>\n";
// echo $submitButton;
echo "\t\t</form>\n";
echo "</div>\n";

//Jetzt wird ein Bild gezeichnet, dass den Stundenplan des Mitarbeiters wiedergibt.
foreach(array_keys($Dienstplan) as $tag ) 
{
	$datum=$Dienstplan[$tag]["Datum"][0];
	foreach($Dienstplan[$tag]['VK'] as $key => $vk) //Die einzelnen Zeilen im Dienstplan
	{
		if ( !empty($vk) ) //Wir ignorieren die nicht ausgefüllten Felder
		{
		//	list($vk)=explode(' ', $vk); //Wir brauchen nur die VK Nummer. Die steht vor dem Leerzeichen.
			$vk=$auswahlMitarbeiter;
			$dienstbeginn=$Dienstplan[$tag]["Dienstbeginn"][$key];
			$dienstende=$Dienstplan[$tag]["Dienstende"][$key];
			$mittagsbeginn=$Dienstplan[$tag]["Mittagsbeginn"][$key]; //if(empty($Mittagsbeginn)){$Mittagsbeginn="0:00";}
			$mittagsende=$Dienstplan[$tag]["Mittagsende"][$key]; //if(empty($Mittagsende)){$Mittagsende="0:00";}
//			$kommentar='Noch nicht eingebaut'
			if (isset($mittagsbeginn) && isset($mittagsende))
			{
				$sekunden=strtotime($dienstende)-strtotime($dienstbeginn);
				$mittagspause=strtotime($mittagsende)-strtotime($mittagsbeginn);
				$sekunden=$sekunden-$mittagspause;
				$stunden=$sekunden/3600;
			}
			else
			{
				$sekunden=strtotime($dienstende)-strtotime($dienstbeginn);
				$stunden=$sekunden/3600;
			}
			//Und jetzt schreiben wir die Daten noch in eine Datei, damit wir sie mit gnuplot darstellen können.
			if(empty($mittagsbeginn)){$mittagsbeginn="0:00";}
			if(empty($mittagsende)){$mittagsende="0:00";}
			//In der default.php wurde die Sprache für Zeitangaben auf Deutsch gestzt. Daher steht hier z.B. Montag statt Monday.
			$dienstplanCSV.=strftime('%A', strtotime($datum)).", $vk, $datum";
			$dienstplanCSV.=", ".$dienstbeginn;
			$dienstplanCSV.=", ".$dienstende;
			$dienstplanCSV.=", ".$mittagsbeginn;
			$dienstplanCSV.=", ".$mittagsende;  
			$dienstplanCSV.=", ".$stunden."\n";  
		}
	}
}
$filename = "tmp/Mitarbeiter.csv";
$myfile = fopen($filename, "w") or die("Unable to open file!");
fwrite($myfile, $dienstplanCSV);
fclose($myfile);
$dienstplanCSV="";
$command=('./Mitarbeiter_image.sh '.escapeshellcmd($Dienstplan[0]["Datum"][0]).'_'.escapeshellcmd($vk));
exec($command, $kommandoErgebnis);
echo "<img src=images/mitarbeiter_".$Dienstplan[0]['Datum'][0]."_".$vk.".png?".filemtime("images/mitarbeiter_".$Dienstplan[0]['Datum'][0]."_".$vk.".png")." style=width:70%;><br>"; //Um das Bild immer neu zu laden, wenn es verändert wurde müssen wir das Cachen verhindern.



schreiben_ics ($Dienstplan); //Schreibt die Daten aus dem Dienstplan (alle Tage, ohne Pause) in eine ics Datei. Fügt einen Download-button für die Datei ein.



//echo "<pre>";	var_export($_POST);    	echo "</pre>";

?>
	</body>
<html>

