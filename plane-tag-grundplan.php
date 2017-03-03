<?php
	//Daten aus der Datenbank aubrufen
	if(!isset($tag)){$tag=0;}
	$abfrage="SELECT * FROM `Grundplan`
		WHERE `Wochentag` = '".date('N', strtotime($datum))."'
		AND `Mandant` = '$mandant'";
	$ergebnis = mysqli_query_verbose($abfrage);
	while($row = mysqli_fetch_object($ergebnis))
	{
		//Mitarbeiter, die im Urlaub/Krank sind, werden gar nicht erst beachtet.
		if( isset($Abwesende[$row->VK]))
		{
			$Fehlermeldung[]=$Mitarbeiter[$row->VK]." ist abwesend. 	Die Lücke eventuell auffüllen($row->Dienstbeginn - $row->Dienstende).<br>\n";
			continue 1;
		}
		if( isset($Mitarbeiter) AND array_search($row->VK, array_keys($Mitarbeiter)) === false)
		{
			//$Fehlermeldung[]=$Mitarbeiter[$row->VK]." ist nicht angestellt.<br>\n";
			continue 1;
		}
		$Dienstplan[$tag]['Datum'][]=$datum;
		$Dienstplan[$tag]['VK'][]=$row->VK;
		$Dienstplan[$tag]['Dienstbeginn'][]=$row->Dienstbeginn;
		$Dienstplan[$tag]['Dienstende'][]=$row->Dienstende;
		$Dienstplan[$tag]['Mittagsbeginn'][]=$row->Mittagsbeginn;
		//echo $Mitarbeiter[$row->VK].": ".$row->Mittagsbeginn."<br>\n";
		//TODO: Make sure, that real NULL values are inserted into the database! By every php-file that inserts anything into the grundplan!
		$Dienstplan[$tag]['Mittagsende'][]=$row->Mittagsende;
		$Dienstplan[$tag]['Stunden'][]=$row->Stunden;
	}


	if(!empty($Dienstplan[$tag]['VK']))
	{
		/*Um die Reihenfolge vernünftig zu sortieren, rechnen wir zunächst in Unix-Sekunden um.*/
		$Sort_order=array_map('strtotime', $Dienstplan[$tag]['Dienstbeginn']);
		/*Dann sortieren wir ALLE Elemente des Arrays nach der soeben ermittelten Reihenfolge.*/
		array_multisort($Sort_order, $Dienstplan[$tag]['Dienstbeginn'], $Dienstplan[$tag]['Dienstende'],$Dienstplan[$tag]['Mittagsbeginn'],$Dienstplan[$tag]['Mittagsende'], $Dienstplan[$tag]['VK']);
	}

//echo "<pre>";	var_export($Dienstplan);    	echo "</pre>"; 

	//Hier entsteht die Mittagspausenvergabe.
	if( !empty($Dienstplan[$tag]['VK']) ) //Haben wir überhaupt einen Dienstplan?
	{
		$Besetzte_mittags_beginne=array_map('strtotime', $Dienstplan[$tag]['Mittagsbeginn']);//Zeiten, zu denen schon jemand mit dem Essen beginnt.
		$Besetzte_mittags_enden=array_map('strtotime', $Dienstplan[$tag]['Mittagsende']);//Zeiten, zu denen jemand mit dem Essen fertig ist.
		$pausen_start=strtotime('11:30:00');
		foreach($Dienstplan[$tag]['VK'] as $position => $vk) //Die einzelnen Zeilen im Dienstplan
		{
			//echo "Mittag für $Mitarbeiter[$vk]?<br>\n";
			if ( !empty($Mittag_mitarbeiter[$vk]) AND !($Dienstplan[$tag]['Mittagsbeginn'][$position]>0) AND !($Dienstplan[$tag]['Mittagsende'][$position]>0) )
			{
				//echo "Mittag ist noch nicht definiert<br>\n";
				//Zunächst berechnen wir die Stunden, damit wir wissen, wer überhaupt eine Mittagspause bekommt.
				$dienstbeginn=$Dienstplan[$tag]["Dienstbeginn"][$position];
				$dienstende=$Dienstplan[$tag]["Dienstende"][$position];
				$sekunden=strtotime($dienstende)-strtotime($dienstbeginn)-$Mittag_mitarbeiter[$vk]*60;
				if( $sekunden >= 6*3600 )
				{
					//echo "Mehr als 6 Stunden, also gibt es Mittag!";
					//Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
					$pausen_ende=$pausen_start+$Mittag_mitarbeiter[$vk]*60;
					if(array_search($pausen_start, $Besetzte_mittags_beginne)!==false OR array_search($pausen_ende, $Besetzte_mittags_enden)!==false)
					{
						//Zu diesem Zeitpunkt startet schon jemand sein Mittag. Wir warten 30 Minuten (1800 Sekunden)
						$pausen_start+=1800;
						$pausen_ende+=1800;
					}
					$Dienstplan[$tag]['Mittagsbeginn'][$position]=date('H:i', $pausen_start);
					$Dienstplan[$tag]['Mittagsende'][$position]=date('H:i', $pausen_ende);
					$pausen_start=$pausen_ende;
				}
			}
			elseif ( !empty($vk) AND !empty($Dienstplan[$tag]['Mittagsbeginn'][$position]) AND empty($Dienstplan[$tag]['Mittagsende'][$position]) )
			{
					$Dienstplan[$tag]['Mittagsende'][$position]=date('H:i', strtotime('- '.$Mittag_mitarbeiter[$vk].' minutes', $Dienstplan[$tag]['Mittagsbeginn'][$position]));
			}
			elseif ( !empty($vk) AND empty($Dienstplan[$tag]['Mittagsbeginn'][$position]) AND !empty($Dienstplan[$tag]['Mittagsende'][$position]) )
			{
					$Dienstplan[$tag]['Mittagsbeginn'][$position]=date('H:i', strtotime('+ '.$Mittag_mitarbeiter[$vk].' minutes', $Dienstplan[$tag]['Mittagsende'][$position]));
			}
		}

	}
?>
