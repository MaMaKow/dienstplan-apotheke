<?php
function schreiben_tabelle ($Dienstplan){
		global $Mitarbeiter, $mandant;
		global $verbindungi;
		global $Warnmeldung, $Fehlermeldung;
		echo "\t\t\t\t</tr><tr>\n";
		foreach($Dienstplan as $key => $Dienstplantag)
		{
			if(isset($Dienstplantag['VK']))
			{
				$Plan_anzahl[]=(count($Dienstplantag['VK']));
			}
			else
			{
				$Plan_anzahl[]=0;
			}
		}
		$plan_anzahl=max($Plan_anzahl); //Die Anzahl der Zeilen der Tabelle richtet sich nach dem Tag mit den meisten Einträgen.

		for ($j=0; $j<$plan_anzahl; $j++)
		{
			if(isset($feiertag) && !isset($notdienst)){break 1;}
			echo "\t\t\t\t</tr></thead><tr>\n";
			for ($i=0; $i<count($Dienstplan); $i++)
			{//Mitarbeiter
				//The following lines check for the state of approval.
				//Duty rosters have to be approved by the leader, before the staff can view them.
				$datum=$Dienstplan[$i]["Datum"][0];
				unset($approval);
				$abfrage="SELECT state FROM `approval` WHERE date='$datum' AND branch='$mandant'";
				$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
				while($row = mysqli_fetch_object($ergebnis)){
					$approval=$row->state;
				}
				if (isset($approval)) {
					if ($approval=="approved") {
						//$Warnmeldung[]="Alles ist gut.";
					} elseif ($approval=="not_yet_approved") {
						$Warnmeldung[]="Der Dienstplan wurde noch nicht von der Leitung bestätigt!";
					} elseif ($approval=="disapproved") {
						$Warnmeldung[]="Der Dienstplan wird noch überarbeitet!";
					}
				} else {
					$approval="not_yet_approved";
					$Warnmeldung[]="Fehlende Daten in der Tabelle `approval`";
					// TODO: This is an Exception. It will occur when There is no approval, disapproval or other connected information in the approval table of the database.
					//That might espacially occur during the development stage of this feature.
				}
				echo "\t\t\t\t\t<td align=left>";
				if ($approval=="approved") {
					$zeile="";
					if (isset($Dienstplan[$i]["VK"][$j]) && isset($Mitarbeiter[$Dienstplan[$i]["VK"][$j]]) )
					{
						$zeile.="<b><a href=mitarbeiter-out.php?datum=".$Dienstplan[$i]["Datum"][0]."&auswahl_mitarbeiter=".$Dienstplan[$i]["VK"][$j].">";
						$zeile.=$Mitarbeiter[$Dienstplan[$i]["VK"][$j]];
						$zeile.="</a></b> / ";
						$zeile.=$Dienstplan[$i]["Stunden"][$j];
						$zeile.=" ";
					}
					//Dienstbeginn
					$zeile.=" <br> ";
					if (isset($Dienstplan[$i]["VK"][$j]))
					{
						$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
					}
					//Dienstende
					if (isset($Dienstplan[$i]["VK"][$j]))
					{
						$zeile.=" - ";
						$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstende"][$j]));
					}
					$zeile.="";
					echo $zeile;
					//	Mittagspause
					$zeile="";
					echo "\t\t\t\t<br>\n";
					if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
					{
						$zeile.=" Pause: ";
						$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
					}
					if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
					{
						$zeile.=" - ";
						$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
					}
					$zeile.="";
					echo $zeile;
				}
				echo "</td>\n";
			}
		}
		echo "\t\t\t\t</tr>\n";
}
?>
