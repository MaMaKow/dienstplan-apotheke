<?php
//Hier schauen wir, welche Daten an uns übersendet wurden und aus welchem Formular sie stammen.
if ( isset($_POST['submitCopyPaste']) && count($_POST['Dienstplan']) > 0 )
{
	$datenempfang="Die Daten wurden empfangen.<br>\n";
	foreach ( $_POST['Dienstplan'] as $plan => $inhalt )
	{
		$Dienstplan[$plan]=$inhalt;
	}
//	echo "<pre>";	var_export($Dienstplan);    	echo "</pre>";
	foreach(array_keys($Dienstplan) as $tag ) //Hier sollte eigentlich nur ein einziger Tag ankommen. Oder wir bauen es auch in die Woche ein.
	{
		$datum=$Dienstplan[$tag]['Datum'][0];
		$datum=strtotime('+7 day', strtotime($datum));
		$datum=date('Y-m-d', $datum);
		$abfrage="SELECT COUNT(*) FROM `Dienstplan` WHERE `Datum` = '$datum' AND `Mandant` = '$mandant'";
		echo "$tag $abfrage<br>\n";
		$ergebnis = mysqli_query_verbose($abfrage);
		$row = mysqli_fetch_row($ergebnis);
		if ($row[0] == 0) //Wenn in dem Tag noch gar nichts eingetragen ist.
		{

			foreach($Dienstplan[$tag]['VK'] as $key => $VK) //Die einzelnen Zeilen im Dienstplan
			{
				if ( !empty($VK) ) //Wir ignorieren die nicht ausgefüllten Felder
				{
					list($VK)=explode(' ', $VK); //Wir brauchen nur die VK Nummer. Die steht vor dem Leerzeichen.
					$dienstbeginn=$Dienstplan[$tag]["Dienstbeginn"][$key];
					$dienstende=$Dienstplan[$tag]["Dienstende"][$key];
					$mittagsbeginn=$Dienstplan[$tag]["Mittagsbeginn"][$key]; if(empty($Mittagsbeginn)){$Mittagsbeginn="0:00";}
					$mittagsende=$Dienstplan[$tag]["Mittagsende"][$key]; if(empty($Mittagsende)){$Mittagsende="0:00";}
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
					$abfrage="INSERT INTO `Dienstplan` (VK, Datum, Dienstbeginn, Dienstende, Mittagsbeginn, Mittagsende, Stunden, Mandant)
						VALUES ('$VK', '$datum', '$dienstbeginn', '$dienstende', '$mittagsbeginn', '$mittagsende', '$stunden', '$mandant')";
//					echo "<pre>";	var_export($Dienstplan);    	echo "</pre>"; 
//					echo "$abfrage<br>\n";
					$ergebnis = mysqli_query_verbose($abfrage);
					$Datenübertragung="Die Daten wurden in die Datenbank eingetragen.<br>\n";
				}
			}
		}
		else
		{
			echo "Dieser Tag ist bereits gefüllt. Es wurde nicht kopiert.<br>\n";
		}
	}
}
