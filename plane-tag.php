<pre>
<?php

	function finde_konstanten($spalte) //Spalte ist Dienstbeginn, Dienstende, oder eine andere Spalte der Tabelle mit den Wunschzeiten
	{
		global $row, $datum, $tag, $position;
		global $Dienstplan;//Die Variable wird heir global gesetzt, damit sie außerhab später zur Verfügung steht.
		$oderOptionen=explode('|', $row->$spalte); //Nur das erste Argument wird bisher genutzt. Das ist natürlich halbherzig debug DEBUG!
//		$undOptionen=explode('&', $row->Dienstbeginn); //Wird im weiteren bisher nicht beachtet, braucht vermutlich eine komplette Umgebung.
		preg_match('/[<>=!]+/', $oderOptionen[0], $vergleichsOperator);
		preg_match('/[^<>=!]+/', $oderOptionen[0], $wunschUhrzeit);
		if(isset($vergleichsOperator[0]))
		{
			if($vergleichsOperator[0]=="=")
			{
				//Wir legen im folgenden den VK als ersten Key fest. Dies muss später weider zurück übersetzt werden. Es ist aber notwendig um die verschiedenen Spalten zueinander zu führen.
				$KonstanterGrundplan="$wunschUhrzeit[0]";
//			echo "$row->VK, $spalte, $KonstanterGrundplan<br>\n";
				$Dienstplan[$tag]['VK'][$position]=$row->VK;
				$Dienstplan[$tag]['Datum'][$position]=$datum;
				//Die folgenden zwei Zeilen sind problematisch. Aber die Funktion zeiche-histogramm braucht vorhandene Werte im Array. Vielleicht bauen wir dort eine Prüfung ein. Dann können wir das hier entfernen. debug DEBUG
				if(!isset($Dienstplan[$tag]['Mittagsbeginn'][$position]))
				{
					$Dienstplan[$tag]['Mittagsbeginn'][$position]=null;
				}
				if(!isset($Dienstplan[$tag]['Mittagsende'][$position]))
				{
					$Dienstplan[$tag]['Mittagsende'][$position]=null;
				}
				$Dienstplan[$tag][$spalte][$position]=$KonstanterGrundplan;
			}
		}
	}





	//Daten aus der Datenbank aubrufen
	if(!isset($tag)){$tag=0;}
	$abfrage="SELECT * FROM `Grundplan`
		WHERE `Wochentag` = '".date('N', strtotime($datum))."'
		AND `Mandant` = '$mandant'";
	$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
	while($row = mysqli_fetch_object($ergebnis))
	{
		//Mitarbeiter, die im Urlaub/Krank sind, werden gar nicht erst beachtet.
		if( isset($Abwesende) AND array_search($row->VK, $Abwesende) !== false)
		{
			$Fehlermeldung[]=$Mitarbeiter[$row->VK]." ist abwesend. 	Die Lücke eventuell auffüllen($row->Dienstbeginn - $row->Dienstende).<br>\n"; continue 1;
		}
		else
		{
			$Grundplan[$tag]['Datum'][]=$datum;
			$Grundplan[$tag]['VK'][]=$row->VK;
			$Grundplan[$tag]['Dienstbeginn'][]=$row->Dienstbeginn;
			$Grundplan[$tag]['Dienstende'][]=$row->Dienstende;
			$Grundplan[$tag]['Mittagsbeginn'][]=$row->Mittagsbeginn;
			$Grundplan[$tag]['Mittagsende'][]=$row->Mittagsende;
			$Grundplan[$tag]['Stunden'][]=$row->Stunden;
	
			//Wir setzten eine feste Referenz für den Ausgabe-Array zum Wumschplan-Array.
			$position=max(array_keys($Grundplan[$tag]['VK']));
	
			//Alle festen Zeiten werden jetzt bereits definiert. Alles weitere wird später aufgefüllt.
			finde_konstanten('Dienstbeginn');
			finde_konstanten('Dienstende');
			finde_konstanten('Mittagsbeginn');
			finde_konstanten('Mittagsende');
	
			if(empty($Grundplan[$tag]['Stunden'][$position]))
			{
				$sollMinuten=round($StundenMitarbeiter[$row->VK] /5)*60; //Wie viele Arbeitsstunden in Minuten gerechnet soll pro Tag gearbeitet werden?
				$sollMinuten+=$MittagMitarbeiter[$row->VK]; //Die Mittagspause muss natürlich mit herausgearbeitet werden.
			}
			else
			{
				$sollMinuten=$row->Stunden*60; //Wie viele Arbeitsstunden in Minuten gerechnet soll pro Tag gearbeitet werden?
				$sollMinuten+=$MittagMitarbeiter[$row->VK]; //Die Mittagspause muss natürlich mit herausgearbeitet werden.
			}
	
			if(empty($Dienstplan[$tag]['Dienstbeginn'][$position]) && !empty($Dienstplan[$tag]['Dienstende'][$position])) //Wenn nur Dienstende feststeht, legen wir jetzt den Dienstbeginn fest.
			{
				$Dienstplan[$tag]['Dienstbeginn'][$position]=date('H:i', strtotime('- '.$sollMinuten.' minutes', strtotime($Dienstplan[$tag]['Dienstende'][$position])));
			}
			if(empty($Dienstplan[$tag]['Dienstende'][$position]) && !empty($Dienstplan[$tag]['Dienstbeginn'][$position])) //... und wenn nur der Dienstbeginn fest steht, berechnen wir hier das Dienstende.
			{
				$Dienstplan[$tag]['Dienstende'][$position]=date('H:i', strtotime('+ '.$sollMinuten.' minutes', strtotime($Dienstplan[$tag]['Dienstbeginn'][$position])));
			}
		}
	}


	function mache_vorschlag($uhrzeit)
	{
//debug DEBUG Vermutlich ist es cleverer, die $MitarbeiterOptionen gleich auf die Mitarbeiter zu begrenzen, die auch wirklich können. Dann sparen wir und zahlreiche Versuche.
		global $datum, $tag, $Dienstplan, $Grundplan, $Abwesende;
		global $Mitarbeiter, $MandantenMitarbeiter, $AusbildungMitarbeiter, $StundenMitarbeiter, $MittagMitarbeiter;
		//Eine Liste der zur Verfügung stehenden Mitarbeiter holen:
		foreach($MandantenMitarbeiter as $vk => $nachname)
		{	
			//Wer krank oder im Urlaub ist, der erscheint hier nicht.
			if( isset($Abwesende) AND array_search($vk, $Abwesende) !== false)
			{
				//nächster bitte
				continue;
			}
			else
			{
				//Hier müssen noch die Mitarbeiter raus, die längst im Plan stehen.
				if( array_search($vk, $Dienstplan[$tag]['VK']) !== false ) //Array-Search kann '0' zurück geben als Index. Das wird von if als false ausgewertet. Das wollen wir nicht.
				{
					//nächster bitte
					continue;
				}
				else
				{
					$posPos=array_search($vk, $Grundplan[$tag]['VK']);
					if($posPos===false) 
					{
						//Es liegen keinerlei Wünsche vor. Wir sollten in der Datenbank welche eintragen, auch wenn es ein egal ist.
						continue;
						//Es wird nur automatisch eingeplant, wer auch einen Eintrag im Grundplan hat.
						//$MitarbeiterOptionen[]=$vk;
					}
					elseif($Grundplan[$tag]['Dienstbeginn'][$posPos] === null)
					{
						//Keine Wünsche zum Dienstbeginn. Aber lässt sich das mit dem Dienstende vereinbaren? Gibt es andere Wunschhindernisse?
						$MitarbeiterOptionen[]=$vk;
						//echo "<pre>  "; var_export($MitarbeiterOptionen); echo "</pre>";
					}
					else
					{
						//Wir prüfen jetzt, ob ein Dienstbeginn denn auch gewünscht wäre.
						$oderOptionen=explode('|', $Grundplan[$tag]['Dienstbeginn'][$posPos]); //Nur das erste Argument wird bisher genutzt. Das ist natürlich halbherzig debug DEBUG!
//							$undOptionen=explode('&', $row->Dienstbeginn); //Wird im weiteren bisher nicht beachtet, braucht vermutlich eine komplette Umgebung.
						preg_match('/[<>=!]+/', $oderOptionen[0], $vergleichsOperator);
						preg_match('/[^<>=!]+/', $oderOptionen[0], $wunschUhrzeit); $wunschUhrzeit=strtotime($wunschUhrzeit[0]);
						if(isset($vergleichsOperator[0]))
						{
							if($vergleichsOperator[0]=="<=")
							{
								if( $uhrzeit <= $wunschUhrzeit )
								{
									$MitarbeiterOptionen[]=$vk;
								}
							}
							elseif($vergleichsOperator[0]=="<")
							{
								if( $uhrzeit < $wunschUhrzeit )
								{
									$MitarbeiterOptionen[]=$vk;
								}
							}
							elseif($vergleichsOperator[0]==">=")
							{
								if( $uhrzeit >= $wunschUhrzeit )
								{
									$MitarbeiterOptionen[]=$vk;
								}
							}
							elseif($vergleichsOperator[0]==">")
							{
								if( $uhrzeit > $wunschUhrzeit )
								{
									$MitarbeiterOptionen[]=$vk;
								}
							}
							elseif($vergleichsOperator[0]=="<>" OR $vergleichsOperator[0]=="!=")
							{
								if( $uhrzeit != $wunschUhrzeit )
								{
									$MitarbeiterOptionen[]=$vk;
								}
							}
							else
							{
								echo "Der Vergleichsoperator $vergleichsOperator[0] wird nicht unterstützt.<br>\n";
							}
						}
					}
				}
			}
		}
		if(empty($MitarbeiterOptionen)){/*echo "Keine weiteren Mitarbeiter<br>\n";*/ return false;}
		$vorschlag = $MitarbeiterOptionen[mt_rand(0, count($MitarbeiterOptionen) - 1)];
		akzeptiere_vorschlag($vorschlag);
	}
	
	function akzeptiere_vorschlag($vorschlag)
	{
		global $uhrzeit, $versuche;
		global $datum, $tag, $Dienstplan, $Grundplan, $Abwesende;
		global $Mitarbeiter, $MandantenMitarbeiter, $AusbildungMitarbeiter, $StundenMitarbeiter, $MittagMitarbeiter;
		$Dienstplan[$tag]['VK'][]=$vorschlag;
		$position=max(array_keys($Dienstplan[$tag]['VK']));
		$Dienstplan[$tag]['Datum'][$position]=$datum;
		$Dienstplan[$tag]['Dienstbeginn'][$position]=date('H:i', $uhrzeit);
		if(!isset($Dienstplan[$tag]['Mittagsbeginn'][$position]))
		{
			$Dienstplan[$tag]['Mittagsbeginn'][$position]=null;
		}
		if(!isset($Dienstplan[$tag]['Mittagsende'][$position]))
		{
			$Dienstplan[$tag]['Mittagsende'][$position]=null;
		}
		if(array_search($vorschlag, $Grundplan[$tag]['VK']) === false  OR  empty($Grundplan[$tag]['Stunden'][array_search($vorschlag, $Grundplan[$tag]['VK'])]))
		{
			$sollMinuten=round($StundenMitarbeiter[$vorschlag] /5)*60; //Wie viele Arbeitsstunden in Minuten gerechnet soll pro Tag gearbeitet werden?
			$sollMinuten+=$MittagMitarbeiter[$vorschlag]; //Die Mittagspause muss natürlich mit herausgearbeitet werden.
		}
		else
		{
			preg_match('/[0-9.]+/', $Grundplan[$tag]['Stunden'][array_search($vorschlag, $Grundplan[$tag]['VK'])], $wunschStunden);

//		echo "<pre> "; var_export($Grundplan[$tag]['VK']); echo "</pre>";
//		echo "<pre> "; var_export($Grundplan[$tag]['Stunden']); echo "</pre>";
//		echo "<pre> "; var_export($Grundplan[$tag]['Stunden'][array_search($vorschlag, $Grundplan[$tag]['VK'])]); echo "</pre>";
//		echo "<pre> "; var_export($wunschStunden); echo "</pre>";
			$sollMinuten=$wunschStunden[0]*60; //Wie viele Arbeitsstunden in Minuten gerechnet soll pro Tag gearbeitet werden?

			$sollMinuten+=$MittagMitarbeiter[$vorschlag]; //Die Mittagspause muss natürlich mit herausgearbeitet werden.
//			echo "Wir sind bei ".$Mitarbeiter[$vorschlag]." und es werden $sollMinuten zur weiteren Verwendung berechnet.<br>\n";
		}

		if(empty($Dienstplan[$tag]['Dienstbeginn'][$position]) && !empty($Dienstplan[$tag]['Dienstende'][$position])) //Wenn nur Dienstende feststeht, legen wir jetzt den Dienstbeginn fest.
		{
			$Dienstplan[$tag]['Dienstbeginn'][$position]=date('H:i', strtotime('- '.$sollMinuten.' minutes', strtotime($Dienstplan[$tag]['Dienstende'][$position])));
		}
		if(empty($Dienstplan[$tag]['Dienstende'][$position]) && !empty($Dienstplan[$tag]['Dienstbeginn'][$position])) //... und wenn nur der Dienstbeginn fest steht, berechnen wir hier das Dienstende.
		{
			$Dienstplan[$tag]['Dienstende'][$position]=date('H:i', strtotime('+ '.$sollMinuten.' minutes', strtotime($Dienstplan[$tag]['Dienstbeginn'][$position])));
		}
	}

	$versuche=0;
	/*Es sollten immer mindestens zwei Personen anwesend sein, damit man auch mal aufs Klo gehen kann, einen Anruf annehmen kann usw,...*/
	$min_anwesende=2;
	for($uhrzeit=strtotime('8:00:00'); $uhrzeit<strtotime('20:00:00'); $versuche++)
	{
		/*Damit wir keine Endlosschleife bauen, versuchen wir nur einige Male einen geeigneten Mitarbeiter zu finden, bevor wir zur nächsten Urzeit weiter schreiten.*/
		if($versuche > 3){$uhrzeit=strtotime('+ 30 minutes', $uhrzeit); $versuche-=2; continue;} 
		/*zeichne-histogramm.php enthält bereits den notwendigen Code, um anwesende Mitarbeiter durchzuzählen und Bedarfe zu ermitteln.*/
		$histogrammNoPrint=true; require 'zeichne-histogramm.php';
		if(!isset($SollAnwesende[$uhrzeit])){echo "Fehler bei der Bestimmung der Anwesenheit.<br>\n"; break;}//Irgendetwas stimmt mit der Berechnung der Anwesenheit nicht. Das passiert zum Beispiel an Sonntagen, weil dort niemand Vorlieben hat. :-)
		$sollAnwesende=max($min_anwesende, $SollAnwesende[$uhrzeit]);
		if($sollAnwesende > $Anwesende[$uhrzeit])
		{
			mache_vorschlag($uhrzeit);
		}
		else
		{
//			echo "Ausreichend Leute anwesend.<br>\n";
			$uhrzeit=strtotime('+ 30 minutes', $uhrzeit); //Wir gehen noch mal einen Schritt weiter.
			$versuche=0;
		}
	}
//		echo "<pre>"; var_export($Grundplan); echo "</pre>";
	/*Jetzt sortieren wir unser Ergebnis fein säuberlich, damit wir es auch lesen können.*/
	if(!empty($Dienstplan[$tag]['VK']))
	{
		//array_multisort($Dienstplan[$tag]['Dienstbeginn'], $Dienstplan[$tag]['Dienstende'],$Dienstplan[$tag]['Mittagsbeginn'],$Dienstplan[$tag]['Mittagsende'], $Dienstplan[$tag]['VK']);
		//Es scheint, dass die Funktion in der Zeile hierüber nicht funktioniert, wie erwartet.
		/*Um die Reihenfolge vernünftig zu sortieren, rechnen wir zunächst in Unix-Sekunden um.*/
		$Sort_order=array_map('strtotime', $Dienstplan[$tag]['Dienstbeginn']);
		/*Dann sortieren wir ALLE Elemente des Arrays nach der soeben ermittelten Reihenfolge.
		Wenn dabei zwei Dienstbeginne  gleich sind, so besteht de Gefahr, dass etwas vertauscht wird.
		Dafür habe ich noch keine Lösung. debug DEBUG*/
		array_multisort($Sort_order, $Dienstplan[$tag]['Dienstbeginn'], $Dienstplan[$tag]['Dienstende'],$Dienstplan[$tag]['Mittagsbeginn'],$Dienstplan[$tag]['Mittagsende'], $Dienstplan[$tag]['VK']);
/*		foreach(array_keys($Dienstplan[$tag]) as $spalte )
		{
			//Die Reihenfolge muss erhalten werden, damit sie bei den anderen Durchläufen noch so zur Verfügung steht.
			// Deshalb nutzen wir eine temporäre Variable.
			$Sort_order_here=$Sort_order;
			array_multisort($Sort_order_here, $Dienstplan[$tag][$spalte]);
		}
*/
		//Das hier drüber scheint zu funktionieren.
	}



	$BesetzteMittagsBeginne=array_map('strtotime', $Dienstplan[$tag]['Mittagsbeginn']);//Zeiten, zu denen schon jemand mit dem Essen beginnt.
	$BesetzteMittagsEnden=array_map('strtotime', $Dienstplan[$tag]['Mittagsende']);//Zeiten, zu denen jemand mit dem Essen fertig ist.
	//Hier entsteht die Mittagspausenvergabe.
	$pausenStart=strtotime('11:30:00');
	if( !empty($Dienstplan[$tag]['VK']) ) //Haben wir überhaupt einen Dienstplan?
	{
		foreach($Dienstplan[$tag]['VK'] as $position => $vk) //Die einzelnen Zeilen im Dienstplan
		{
			if ( !empty($vk) AND empty($Dienstplan[$tag]['Mittagsbeginn'][$position]) AND empty($Dienstplan[$tag]['Mittagsende'][$position]) )
			{
				//Zunächst berechnen wir die Stunden, damit wir wissen, wer überhaupt eine Mittagspause bekommt.
				$dienstbeginn=$Dienstplan[$tag]["Dienstbeginn"][$position];
				$dienstende=$Dienstplan[$tag]["Dienstende"][$position];
				$sekunden=strtotime($dienstende)-strtotime($dienstbeginn)-$MittagMitarbeiter[$vk]*60;
				if( $sekunden >= 6*3600 )
				{
					//Wer länger als 6 Stunden Arbeitszeit hat, bekommt eine Mittagspause.
					$pausenEnde=$pausenStart+$MittagMitarbeiter[$vk]*60;
					if(array_search($pausenStart, $BesetzteMittagsBeginne)!==false OR array_search($pausenEnde, $BesetzteMittagsEnden)!==false)
					{
						//Zu diesem Zeitpunkt startet schon jemand sein Mittag. Wir warten 30 Minuten (1800 Sekunden)
						$pausenStart+=1800;
						$pausenEnde+=1800;
					}
					$Dienstplan[$tag]['Mittagsbeginn'][$position]=date('H:i', $pausenStart);
					$Dienstplan[$tag]['Mittagsende'][$position]=date('H:i', $pausenEnde);
					$pausenStart=$pausenEnde;
				}
			}
			elseif ( !empty($vk) AND !empty($Dienstplan[$tag]['Mittagsbeginn'][$position]) AND empty($Dienstplan[$tag]['Mittagsende'][$position]) )
			{
					$Dienstplan[$tag]['Mittagsende'][$position]=date('H:i', strtotime('- '.$MittagMitarbeiter[$vk].' minutes', $Dienstplan[$tag]['Mittagsbeginn'][$position]));
			}
			elseif ( !empty($vk) AND empty($Dienstplan[$tag]['Mittagsbeginn'][$position]) AND !empty($Dienstplan[$tag]['Mittagsende'][$position]) )
			{
					$Dienstplan[$tag]['Mittagsbeginn'][$position]=date('H:i', strtotime('+ '.$MittagMitarbeiter[$vk].' minutes', $Dienstplan[$tag]['Mittagsende'][$position]));
			}
		}

	}
?>
</pre>
