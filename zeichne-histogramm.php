<?php

		//Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
		//Zunächst müssen wir festlegen, wie groß die Zeitsprünge im Diagramm werden.
		$zeit_abstand=5*60; //5 Minuten
		if (!isset($tag)) {$tag=0;} //Beim Aufruf aus tag-out wird kein Tag übergeben. Beim Aufruf aus der Auswertung, wird ein $tag übergeben.
		$faktor_arbeitskraft=6; //Wie viele Packungen schafft ein Mitarbeiter pro Zeiteinheit (?halbe Stunde?)?
		if (!empty($Dienstplan[$tag]["Dienstbeginn"]))
		{
			//Im folgenden Suchen wir die Approbierten, die heute anwesend sind. Sie werden im $Approbierten_plan gespeichert.
			$Spalten=array_keys($Dienstplan[$tag]);
			foreach($Dienstplan[$tag]["VK"] as $key => $value)
			{
				if ( array_search($value, array_keys($Approbierte_mitarbeiter)) !== false )
				{
					foreach($Spalten as $spalte)
					{
						$Approbierten_dienstplan[$tag][$spalte][$key]=$Dienstplan[$tag][$spalte][$key];
					}
				}
			}
			foreach($Dienstplan[$tag]["VK"] as $key => $value)
			{
				if ( array_search($value, array_keys($Wareneingang_Mitarbeiter)) !== false )
				{
					foreach($Spalten as $spalte)
					{
						$Wareneingang_dienstplan[$tag][$spalte][$key]=$Dienstplan[$tag][$spalte][$key];
					}
				}
			}

			$abfrage="SELECT * FROM Öffnungszeiten WHERE Wochentag = ".date('N', strtotime($datum))." AND Mandant = ".$mandant;
			$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
			$row = mysqli_fetch_object($ergebnis);
			if (!empty($row->Beginn) and !empty($row->Ende)) {
				$tages_beginn=strtotime($row->Beginn);
				$tages_ende=strtotime($row->Ende);
			} else {
				die ("Es wurden keine Öffnungszeiten hinterlegt. Bitte konfigurieren Sie den Mandanten.");
			}


			//Für den Fall, dass auch außerhalb der üblichen Zeiten jemand anwesend ist (Notdienst, Late-Night,...)
			$tages_beginn=min($tages_beginn, strtotime(min(array_filter(array_values($Dienstplan[$tag]["Dienstbeginn"])))));
			$tages_ende=max($tages_ende, strtotime(max(array_values($Dienstplan[$tag]["Dienstende"]))));
			//Wenn die Funktion bereits aufgerufen wurde, ist dieser Wert bereits gesetzt.
			if(empty($Dienstzeiten[0]))
			{
				for ($dienstzeit=$tages_beginn; $dienstzeit<=$tages_ende; $dienstzeit=$dienstzeit+$zeit_abstand){$Dienstzeiten[]=$dienstzeit;}
			}
			if ( isset($Approbierten_dienstplan) )
			{
				$Approbierten_dienst_enden=array_map('strtotime', $Approbierten_dienstplan[$tag]["Dienstende"]);
				$Approbierten_dienst_beginne=array_map('strtotime', $Approbierten_dienstplan[$tag]["Dienstbeginn"]);
				$Approbierten_mittags_enden=array_map('strtotime', $Approbierten_dienstplan[$tag]["Mittagsende"]);
				$Approbierten_mittags_beginne=array_map('strtotime', $Approbierten_dienstplan[$tag]["Mittagsbeginn"]);
			}
			if ( isset($Wareneingang_dienstplan) )
			{
				$Wareneingang_Dienst_Enden=array_map('strtotime', $Wareneingang_dienstplan[$tag]["Dienstende"]);
				$Wareneingang_Dienst_Beginne=array_map('strtotime', $Wareneingang_dienstplan[$tag]["Dienstbeginn"]);
				$Wareneingang_Mittags_Enden=array_map('strtotime', $Wareneingang_dienstplan[$tag]["Mittagsende"]);
				$Wareneingang_Mittags_Beginne=array_map('strtotime', $Wareneingang_dienstplan[$tag]["Mittagsbeginn"]);
			}
			$Dienst_enden=array_map('strtotime', $Dienstplan[$tag]["Dienstende"]);
			$Dienst_beginne=array_map('strtotime', $Dienstplan[$tag]["Dienstbeginn"]);
			$Mittags_enden=array_map('strtotime', $Dienstplan[$tag]["Mittagsende"]);
			$Mittags_beginne=array_map('strtotime', $Dienstplan[$tag]["Mittagsbeginn"]);
			$histogrammCSV="";
			foreach($Dienstzeiten as $zeit)
			{
				//Die folgende Umschreibung von $zeit auf eine globale $dienstzeit ist notwendig, um innerhalb der array-filter Funtion darauf per global zugreifen zu können.
				global $dienstzeit;
				$dienstzeit=$zeit;
				if ( isset($Wareneingang_dienstplan) )
				{
					//Wir zählen die Approbierten Mitarbeiter
					//Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
					$wareneingang_Gekommene=count(array_filter(array_filter($Wareneingang_Dienst_Beginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					//Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
					$wareneingang_Gegangene=count(array_filter(array_filter($Wareneingang_Dienst_Enden, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					$wareneingang_Mittagende=count(array_filter(array_filter($Wareneingang_Mittags_Beginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					$wareneingang_Gemittagte=count(array_filter(array_filter($Wareneingang_Mittags_Enden, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					$wareneingang_Mittagende=$wareneingang_Mittagende-$wareneingang_Gemittagte;
					$wareneingang_Anwesende=$wareneingang_Gekommene-$wareneingang_Gegangene;
					$wareneingang_Anwesende=$wareneingang_Anwesende-$wareneingang_Mittagende;
					$Wareneingang_Anwesende[$dienstzeit]=$wareneingang_Anwesende;
				}
				if ( isset($Approbierten_dienstplan) )
				{
					//Wir zählen die Approbierten Mitarbeiter
					//Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
					$approbierten_gekommene=count(array_filter(array_filter($Approbierten_dienst_beginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					//Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
					$approbierten_gegangene=count(array_filter(array_filter($Approbierten_dienst_enden, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					$approbierten_mittagende=count(array_filter(array_filter($Approbierten_mittags_beginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					$approbierten_gemittagte=count(array_filter(array_filter($Approbierten_mittags_enden, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					$approbierten_mittagende=$approbierten_mittagende-$approbierten_gemittagte;
					$approbierten_anwesende=$approbierten_gekommene-$approbierten_gegangene;
					$approbierten_anwesende=$approbierten_anwesende-$approbierten_mittagende;
					$Approbierten_anwesende[$dienstzeit]=$approbierten_anwesende;
				}
				//Und jetzt noch mal für alle Mitarbeiter
				$gekommene=count(array_filter(array_filter($Dienst_beginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;}))); //Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
				$gegangene=count(array_filter(array_filter($Dienst_enden, function($value) {global $dienstzeit; return $value <= $dienstzeit;}))); //Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
				$mittagende=count(array_filter(array_filter($Mittags_beginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
				$gemittagte=count(array_filter(array_filter($Mittags_enden, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
				$mittagende=$mittagende-$gemittagte;
				$anwesende=$gekommene-$gegangene;
				$anwesende=$anwesende-$mittagende;
				$Anwesende[$dienstzeit]=$anwesende;
				$histogrammCSV.=date('H:i', $dienstzeit).", ".$anwesende."\n";
//				echo date('H:i', $dienstzeit)."\t$gekommene\t$gegangene\t$mittagende\t$anwesende<br>\n";
			}

			if(!isset($histogramm_no_print))
			{
				$filename = "tmp/Histogramm.csv";
				$myfile = fopen($filename, "w") or die("Unable to open file!");
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

				$abfrage="SELECT * FROM pep_zeit_im_wochentag WHERE Wochentag = ".(date('w', strtotime($datum))+1)." AND Mandant = $mandant ";
				$ergebnis = mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
				while ($row = mysqli_fetch_object($ergebnis)) {
						$Wochentag['uhrzeit'][]=$row->Uhrzeit;
						//$Wochentag['median'][]=$row->Median;
						$Wochentag['mittelwert'][]=$row->Mittelwert;
				}
				$erwartungCSV="";
				foreach($Wochentag['mittelwert'] as $key => $tageszeitmittelwert)
				{
					$Erwartung['uhrzeit'][]=$Wochentag['uhrzeit'][$key];
					$Erwartung['packungen'][]=$tageszeitmittelwert*$faktor_tagimmonat*$faktor_monatimjahr;
					$Soll_anwesende[strtotime($Wochentag['uhrzeit'][$key])]=round($tageszeitmittelwert*$faktor_tagimmonat*$faktor_monatimjahr/$faktor_arbeitskraft+1,0);
					$erwartungCSV.=$Wochentag['uhrzeit'][$key].", ".$tageszeitmittelwert*$faktor_tagimmonat*$faktor_monatimjahr."\n";
				}

				if(!isset($histogramm_no_print))
				{
					$filename = "tmp/Erwartung.csv";
					$myfile = fopen($filename, "w") or die("Unable to open file!");
					fwrite($myfile, $erwartungCSV);
					fclose($myfile);
					$erwartungCSV="";
				}
			}
//			echo "<pre>";	var_export($Erwartung);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
			if(!isset($histogramm_no_print))
			{
				$command=('./Histogramm_image.sh '.escapeshellcmd("m".$mandant."_".$datum));
				exec($command, $kommando_ergebnis);
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
//			echo "<pre>";	var_export($Dienstplan);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
		}

?>
