<?php

		//Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
		//Zunächst müssen wir festlegen, wie groß die Zeitsprünge im Diagramm werden.
		$zeitAbstand=5*60; //5 Minuten
		if (!isset($tag)) {$tag=0;} //Beim Aufruf aus tag-out wird kein Tag übergeben. Beim Aufruf aus der Auswertung, wird ein $tag übergeben.
		$faktorArbeitskraft=6; //Wie viele Packungen schafft ein Mitarbeiter pro Zeiteinheit (?halbe Stunde?)?
		if (!empty($Dienstplan[$tag]["Dienstbeginn"]))
		{
			//Im folgenden Suchen wir die Approbierten, die heute anwesend sind. Sie werden im $ApprobiertenPlan gespeichert.
			$Spalten=array_keys($Dienstplan[$tag]);
			foreach($Dienstplan[$tag]["VK"] as $key => $value)
			{
				if ( array_search($value, array_keys($ApprobierteMitarbeiter)) !== false )
				{
					foreach($Spalten as $spalte)
					{
						$ApprobiertenDienstplan[$tag][$spalte][$key]=$Dienstplan[$tag][$spalte][$key];
					}
				}
			}
//			echo "<pre>";	var_export($ApprobiertenDienstplan);    	echo "</pre>"; 
			if( date('N', strtotime($datum)) < 6 )
			{
				//On mondays and saturdays the day starts 
				//DEBUG debug in a future version this should be read from a database with all the single opening and closing times.
				$tagesBeginn=strtotime('8:00:00');
				$tagesEnde=strtotime('20:00:00');
			}
			elseif( date('N', strtotime($datum)) == 6 ) //saturday
			{
				$tagesBeginn=strtotime('9:00:00');
				$tagesEnde=strtotime('18:00:00');
			}
			elseif( date('N', strtotime($datum)) == 7 ) //sunday
			{
				$tagesBeginn=strtotime('12:00:00');
				$tagesEnde=strtotime('18:00:00');
			}
			//Für den Fall, dass auch außerhalb der üblichen Zeiten jemand anwesend ist (Notdienst, Late-Night,...)
			$tagesBeginn=min($tagesBeginn, strtotime(min(array_filter(array_values($Dienstplan[$tag]["Dienstbeginn"])))));
			$tagesEnde=max($tagesEnde, strtotime(max(array_values($Dienstplan[$tag]["Dienstende"]))));
			//Wenn die Funktion bereits aufgerufen wurde, ist dieser Wert bereits gesetzt.
			if(empty($Dienstzeiten[0]))
			{
				for ($dienstzeit=$tagesBeginn; $dienstzeit<=$tagesEnde; $dienstzeit=$dienstzeit+$zeitAbstand){$Dienstzeiten[]=$dienstzeit;}
			}
			if ( isset($ApprobiertenDienstplan) )
			{
				$ApprobiertenDienstEnden=array_map('strtotime', $ApprobiertenDienstplan[$tag]["Dienstende"]);
				$ApprobiertenDienstBeginne=array_map('strtotime', $ApprobiertenDienstplan[$tag]["Dienstbeginn"]);
				$ApprobiertenMittagsEnden=array_map('strtotime', $ApprobiertenDienstplan[$tag]["Mittagsende"]);
				$ApprobiertenMittagsBeginne=array_map('strtotime', $ApprobiertenDienstplan[$tag]["Mittagsbeginn"]);
			}
			$DienstEnden=array_map('strtotime', $Dienstplan[$tag]["Dienstende"]);
			$DienstBeginne=array_map('strtotime', $Dienstplan[$tag]["Dienstbeginn"]);
			$MittagsEnden=array_map('strtotime', $Dienstplan[$tag]["Mittagsende"]);
			$MittagsBeginne=array_map('strtotime', $Dienstplan[$tag]["Mittagsbeginn"]);
			$histogrammCSV="";
			foreach($Dienstzeiten as $zeit)
			{
				//Die folgende Umschreibung von $zeit auf eine globale $dienstzeit ist notwendig, um innerhalb der array-filter Funtion darauf per global zugreifen zu können.
				global $dienstzeit;
				$dienstzeit=$zeit;
				if ( isset($ApprobiertenDienstplan) )
				{
					//Wir zählen die Approbierten Mitarbeiter
					//Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
					$approbiertenGekommene=count(array_filter(array_filter($ApprobiertenDienstBeginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;}))); 
					//Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
					$approbiertenGegangene=count(array_filter(array_filter($ApprobiertenDienstEnden, function($value) {global $dienstzeit; return $value <= $dienstzeit;}))); 
					$approbiertenMittagende=count(array_filter(array_filter($ApprobiertenMittagsBeginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					$approbiertenGemittagte=count(array_filter(array_filter($ApprobiertenMittagsEnden, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
					$approbiertenMittagende=$approbiertenMittagende-$approbiertenGemittagte;
					$approbiertenAnwesende=$approbiertenGekommene-$approbiertenGegangene;
					$approbiertenAnwesende=$approbiertenAnwesende-$approbiertenMittagende;
					$ApprobiertenAnwesende[$dienstzeit]=$approbiertenAnwesende;
				}
				//Und jetzt noch mal für alle Mitarbeiter
				$gekommene=count(array_filter(array_filter($DienstBeginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;}))); //Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
				$gegangene=count(array_filter(array_filter($DienstEnden, function($value) {global $dienstzeit; return $value <= $dienstzeit;}))); //Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
				$mittagende=count(array_filter(array_filter($MittagsBeginne, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
				$gemittagte=count(array_filter(array_filter($MittagsEnden, function($value) {global $dienstzeit; return $value <= $dienstzeit;})));
				$mittagende=$mittagende-$gemittagte;
				$anwesende=$gekommene-$gegangene;
				$anwesende=$anwesende-$mittagende;
				$Anwesende[$dienstzeit]=$anwesende;
				$histogrammCSV.=date('H:i', $dienstzeit).", ".$anwesende."\n";
//				echo date('H:i', $dienstzeit)."\t$gekommene\t$gegangene\t$mittagende\t$anwesende<br>\n";
			}

			if(!isset($histogrammNoPrint))
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
				$faktorMonatimjahr=$Monatimjahr['median'][date('n', strtotime($datum))-1]/$durchschnittsmonat;
				$lines=file('./pep/pep_monatstag.csv');
				foreach($lines as $key => $value)
				{
					list($Tagimmonat['tag'][], $Tagimmonat['min'][], $Tagimmonat['median'][], $Tagimmonat['max'][])=explode(", ", $value);
				}
				$durchschnittstag=calculate_percentile($Tagimmonat['median'],50);
		
				$faktorTagimmonat=$Tagimmonat['median'][date('n', strtotime($datum))-1]/$durchschnittstag;
				$datei="./pep/pep_wochentag";
				$datei.=date("w", strtotime($datum))+1;
				$datei.=".csv";
				$lines=file($datei);
				foreach($lines as $key => $value)
				{
					list($Wochentag['uhrzeit'][], $Wochentag['min'][], $Wochentag['median'][], $Wochentag['max'][])=explode(", ", $value);
				}
				$erwartungCSV="";
				foreach($Wochentag['median'] as $key => $tageszeitmedian)
				{
					$Erwartung['uhrzeit'][]=$Wochentag['uhrzeit'][$key];
					$Erwartung['packungen'][]=$tageszeitmedian*$faktorTagimmonat*$faktorMonatimjahr;
					$SollAnwesende[strtotime($Wochentag['uhrzeit'][$key])]=round($tageszeitmedian*$faktorTagimmonat*$faktorMonatimjahr/$faktorArbeitskraft+1,0);
					$erwartungCSV.=$Wochentag['uhrzeit'][$key].", ".$tageszeitmedian*$faktorTagimmonat*$faktorMonatimjahr."\n";
				}
				
				if(!isset($histogrammNoPrint))
				{
					$filename = "tmp/Erwartung.csv";
					$myfile = fopen($filename, "w") or die("Unable to open file!");
					fwrite($myfile, $erwartungCSV);
					fclose($myfile);
					$erwartungCSV="";
				}
			}
//			echo "<pre>";	var_export($Erwartung);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
			if(!isset($histogrammNoPrint))
			{
				$command=('./Histogramm_image.sh '.escapeshellcmd("m".$mandant."_".$datum));
				exec($command, $kommandoErgebnis);			
			//debug DEBUG to do: Die Dateien im tmp/ könnten wir anschließend alle wieder löschen.
			//debug DEBUG to do: EinEindeutige Unique Namen! Wenn gleichtzeitig mehrere Mitarbeiter zugreifen, werden mehrere Dateien mit dem gleichen Namen erzeugt. Das kann zu Fehlern führen.
			}
			else
			{
				unset($histogrammNoPrint);
			}
		}
		else
		{
			echo "<br>Kein Dienstplan gefunden beim Zeichnen des Histogramms.<br>\n";
//			echo "<pre>";	var_export($Dienstplan);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
		}

?>
