<?php
//Argumente hinter dem .. sind optional.
/**
 * 
 * @global string $datum
 * @global object $verbindungi
 * @global array $List_of_employees
 * @param int $tage number of days (typically just 1 or 5, 6, 7)
 * @param int $mandant
 * @param string $VKmandant as regular expression
 * @return array $Dienstplan for the branch $mandant including $tage days beginning with $datum 
 */
function db_lesen_tage($tage, $mandant, $VKmandant='[0-9]*')
{
global $datum, $verbindungi, $List_of_employees;
	//Abruf der gespeicherten Daten aus der Datenbank
	//$tage ist die Anzahl der Tage. 5 Tage = Woche; 1 Tag = 1 Tag.
	//Branch #0 can be used for the boss, the cleaning lady, and other special people, who do not regularly appear in the roster.

	//We need information about the qualification of the workers:
	require 'db-lesen-mitarbeiter.php';

	for ($i=0; $i<$tage; $i++)
	{
		$tag=date('Y-m-d', strtotime("+$i days", strtotime($datum)));
		$sql_query='SELECT DISTINCT Dienstplan.* FROM `Dienstplan` LEFT JOIN employees ON Dienstplan.VK=employees.id WHERE Dienstplan.Mandant = "'.$mandant.'" AND `Datum` = "'.$tag.'" AND employees.branch REGEXP "^'.$VKmandant.'$" ORDER BY `Dienstbeginn` ASC, `Dienstende` ASC, `Mittagsbeginn` ASC;';
//		$sql_query='SELECT * FROM `Dienstplan` WHERE `Datum` = "'.$tag.'" AND `Mandant` = "'.$mandant.'" ORDER BY `Dienstbeginn` ASC, `Mittagsbeginn` ASC;';
		$result = mysqli_query_verbose($sql_query);
		$dienstplanCSV="";

		while($row = mysqli_fetch_object($result))
		{
			$Dienstplan[$i]["Datum"][]=$row->Datum;
			$Dienstplan[$i]["VK"][]=$row->VK;
			$Dienstplan[$i]["Dienstbeginn"][]=$row->Dienstbeginn;
			$Dienstplan[$i]["Dienstende"][]=$row->Dienstende;
			$Dienstplan[$i]["Mittagsbeginn"][]=$row->Mittagsbeginn;
			$Dienstplan[$i]["Mittagsende"][]=$row->Mittagsende;
			$Dienstplan[$i]["Stunden"][]=$row->Stunden;
			$Dienstplan[$i]["Kommentar"][]=$row->Kommentar;
			//Und jetzt schreiben wir die Daten noch in eine Datei, damit wir sie mit gnuplot darstellen können.
			if(empty($mittagsbeginn)){$mittagsbeginn="0:00";}
			if(empty($mittagsende)){$mittagsende="0:00";}
			//The next lines will be used for coloring the image dependent on the education of the workers:
			if($List_of_employee_professions[$row->VK] == "Apotheker"){
				$worker_style = 1;
			} elseif ($List_of_employee_professions[$row->VK] == "PI"){
				$worker_style = 1;
			} elseif ($List_of_employee_professions[$row->VK] == "PTA"){
				$worker_style = 2;
			} elseif ($List_of_employee_professions[$row->VK] == "PKA"){
				$worker_style = 3;
			} else{
				//anybody else
				$worker_style = 3;
			}
			//We write a file to feed the data to gnuplot for imaging.
			$dienstplanCSV.=$List_of_employees[$row->VK].", $row->VK, $row->Datum";
			$dienstplanCSV.=", ".$row->Dienstbeginn;
			$dienstplanCSV.=", ".$row->Dienstende;
			$dienstplanCSV.=", ".$row->Mittagsbeginn;
			$dienstplanCSV.=", ".$row->Mittagsende;
			$dienstplanCSV.=", ".$row->Stunden;
			$dienstplanCSV.=", ".$row->Mandant;
			$dienstplanCSV.=", ".$worker_style."\n";
		}
                /*
		if ($tage == 1) {
			# This image is shown only for views with one single day.
			$filename = "tmp/Dienstplan.csv";
			$myfile = fopen($filename, "w") or die("Unable to open file $filename!\n");
			fwrite($myfile, $dienstplanCSV);
			fclose($myfile);
			$dienstplanCSV="";
			$command=('./Dienstplan_image.sh 2>&1 '.escapeshellcmd("m".$mandant."_".$datum));
			exec($command, $kommando_ergebnis); // Kann dies Fehler verursachen?
			//Wir rufen die Funktion mehrmals mit verschiedenen Parametern auf. Kann dem Filial-Plan-Bild dabei etwas zustoßen?
		}

//		echo "<pre>";	var_export($kommando_ergebnis);    	echo "</pre>";
                 
                 */
		//Wir füllen komplett leere Tage mit Werten, damit trotzdem eine Anzeige entsteht.
		if ( !isset($Dienstplan[$i]) )
		{
			$Dienstplan[$i]["Datum"][]=$tag;
/*
			$Dienstplan[$i]["VK"][]="";
			$Dienstplan[$i]["Dienstbeginn"][]="";
			$Dienstplan[$i]["Dienstende"][]="";
			$Dienstplan[$i]["Mittagsbeginn"][]="";
			$Dienstplan[$i]["Mittagsende"][]="";
			$Dienstplan[$i]["Stunden"][]="";
			$Dienstplan[$i]["Kommentar"][]="";
*/
		}
		//echo "Ich sehe ".count($Dienstplan)." Tage."."<br>";
	}
	if (isset($Dienstplan))
	{
		return $Dienstplan;
	}
	else
	{
		return 0;
	}
}
