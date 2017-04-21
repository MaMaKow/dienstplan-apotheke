<?php

		//Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
		//Zunächst müssen wir festlegen, wie groß die Zeitsprünge im Diagramm werden.
		$zeit_abstand=5*60; //5 Minuten
		if (!isset($tag)) {$tag=0;} //Beim Aufruf aus tag-out wird kein Tag übergeben. Beim Aufruf aus der Auswertung, wird ein $tag übergeben.
		$faktor_arbeitskraft=6; //Wie viele Packungen schafft ein Mitarbeiter pro Zeiteinheit (?halbe Stunde?)?
		if (!empty($Dienstplan[$tag]["Dienstbeginn"]))
		{
			$abfrage="SELECT * FROM Öffnungszeiten WHERE Wochentag = ".date('N', strtotime($datum))." AND Mandant = ".$mandant;
			$ergebnis = mysqli_query_verbose($abfrage);
			$row = mysqli_fetch_object($ergebnis);
			if (!empty($row->Beginn) and !empty($row->Ende)) {
				$tages_beginn=strtotime($row->Beginn);
				$tages_ende=strtotime($row->Ende);
			} else {
				//echo ("Es wurden keine Öffnungszeiten hinterlegt. Bitte konfigurieren Sie den Mandanten.<br>\n");
				$tages_beginn=strtotime("0:00");
				$tages_ende=strtotime("24:00");
			}


			//Für den Fall, dass auch außerhalb der üblichen Zeiten jemand anwesend ist (Notdienst, Late-Night,...)
			//$tages_beginn=min($tages_beginn, strtotime(min(array_filter(array_values($Dienstplan[$tag]["Dienstbeginn"])))));
			//$tages_ende=max($tages_ende, strtotime(max(array_values($Dienstplan[$tag]["Dienstende"]))));

			//Wenn die Funktion bereits aufgerufen wurde, ist dieser Wert bereits gesetzt.
			if(empty($Changing_times[0]))
			{
                                $Changing_times = calculate_changing_times($Dienstplan);
			}
			$Dienst_enden=array_map('strtotime', $Dienstplan[$tag]["Dienstende"]);
			$Dienst_beginne=array_map('strtotime', $Dienstplan[$tag]["Dienstbeginn"]);
			$Mittags_enden=array_map('strtotime', $Dienstplan[$tag]["Mittagsende"]);
			$Mittags_beginne=array_map('strtotime', $Dienstplan[$tag]["Mittagsbeginn"]);
			$histogrammCSV="";
			foreach($Changing_times as $zeit)
			{
				//Die folgende Umschreibung von $zeit auf eine globale $dienstzeit ist notwendig, um innerhalb der array-filter Funtion darauf per global zugreifen zu können.
				global $unix_time;
				$unix_time = strtotime($zeit);
				$gekommene=count(array_filter(array_filter($Dienst_beginne, function($value) {global $unix_time; return $value <= $unix_time;}))); //Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
				$gegangene=count(array_filter(array_filter($Dienst_enden, function($value) {global $unix_time; return $value <= $unix_time;}))); //Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
				$mittagende=count(array_filter(array_filter($Mittags_beginne, function($value) {global $unix_time; return $value <= $unix_time;})));
				$gemittagte=count(array_filter(array_filter($Mittags_enden, function($value) {global $unix_time; return $value <= $unix_time;})));
				$mittagende=$mittagende-$gemittagte;
				$anwesende=$gekommene-$gegangene;
				$anwesende=$anwesende-$mittagende;
				$Anwesende[$unix_time]=$anwesende;
				$histogrammCSV.=date('H:i', $unix_time).", ".$anwesende."\n";
//				echo date('H:i', $dienstzeit)."\t$gekommene\t$gegangene\t$mittagende\t$anwesende<br>\n";
			}

			if(!isset($histogramm_no_print))
			{
				$filename = "tmp/Histogramm.csv";
				$myfile = fopen($filename, "w") or die(" Unable to open file $filename!");
				fwrite($myfile, $histogrammCSV);
				fclose($myfile);
				$histogrammCSV="";
			}
			//Und jetzt erraten wir noch die geschätzen Packungen, die wir an diesem Tag pro Zeit abverkaufen.
			//Falls diese Funktion bereits zuvor aufgerufen wurde, haben wir den Wert schon.
			if(empty($Erwartung['uhrzeit'][0]))
			{

				$lines=file('./pep/pep_monatimjahr.csv');
				foreach($lines as $key => $value)
				{
					list($Monatimjahr['monat'][], $Monatimjahr['min'][], $Monatimjahr['median'][], $Monatimjahr['max'][])=explode(", ", $value);
				}
				$durchschnittsmonat=calculate_percentile($Monatimjahr['median'],50);
				$faktor_monatimjahr=$Monatimjahr['median'][date('n', strtotime($datum))-1]/$durchschnittsmonat;
				$lines=file('./pep/pep_monatstag.csv');
				foreach($lines as $key => $value)
				{
					list($Tagimmonat['tag'][], $Tagimmonat['min'][], $Tagimmonat['median'][], $Tagimmonat['max'][])=explode(", ", $value);
				}
				$durchschnittstag=calculate_percentile($Tagimmonat['median'],50);

				$faktor_tagimmonat=$Tagimmonat['median'][date('n', strtotime($datum))-1]/$durchschnittstag;

				$abfrage="SELECT * FROM pep_weekday_time WHERE Wochentag = ".(date('w', strtotime($datum))+1)." AND Mandant = $mandant ";
				$ergebnis = mysqli_query_verbose($abfrage);
				while ($row = mysqli_fetch_object($ergebnis)) {
						$Wochentag['uhrzeit'][]=$row->Uhrzeit;
						//$Wochentag['median'][]=$row->Median;
						$Wochentag['mittelwert'][]=$row->Mittelwert;
				}
				$erwartungCSV="";
				if (isset($Wochentag['mittelwert'])) {
					foreach($Wochentag['mittelwert'] as $key => $tageszeitmittelwert)
					{
						$Erwartung['uhrzeit'][]=$Wochentag['uhrzeit'][$key];
						$Erwartung['packungen'][]=$tageszeitmittelwert*$faktor_tagimmonat*$faktor_monatimjahr;
						$Soll_anwesende[strtotime($Wochentag['uhrzeit'][$key])]=round($tageszeitmittelwert*$faktor_tagimmonat*$faktor_monatimjahr/$faktor_arbeitskraft+1,0);
						$erwartungCSV.=$Wochentag['uhrzeit'][$key].", ".$tageszeitmittelwert*$faktor_tagimmonat*$faktor_monatimjahr."\n";
					}
				} else {
					echo ("Es sind keine Daten zu Abverkäufen an diesem Tag bekannt.<br>\n");

				}


				if(!isset($histogramm_no_print))
				{
					$filename = "tmp/Erwartung.csv";
					$myfile = fopen($filename, "w") or die(" Unable to open file $filename!");
					fwrite($myfile, $erwartungCSV);
					fclose($myfile);
					$erwartungCSV="";
				}
			}
			if(!isset($histogramm_no_print))
			{
				$command=('./Histogramm_image.sh '.escapeshellcmd("m".$mandant."_".$datum));
				exec($command, $kommando_ergebnis);
				//echo "<pre>";	var_export($kommando_ergebnis);    	echo "</pre>";
			//debug DEBUG to do: Die Dateien im tmp/ könnten wir anschließend alle wieder löschen.
			//debug DEBUG to do: EinEindeutige Unique Namen! Wenn gleichtzeitig mehrere Mitarbeiter zugreifen, werden mehrere Dateien mit dem gleichen Namen erzeugt. Das kann zu Fehlern führen.
			}
			else
			{
				unset($histogramm_no_print);
			}
		}
		else
		{
			echo "<br>Kein Dienstplan gefunden beim Zeichnen des Histogramms.<br>\n";
		}

?>
