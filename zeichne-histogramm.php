<?php
		//Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
		//Zunächst müssen wir festlegen, wie groß die Zeitsprünge im Diagramm werden.
		$zeitAbstand=5*60; //5 Minuten
//		echo "<pre>";	var_export($Dienstplan);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
		if (!isset($tag)) {$tag=0;} //Beim Aufruf aus tag-out wird kein Tag übergeben. Beim Aufruf aus der Auswertung, wird ein $tag übergeben.
		if (!empty($Dienstplan[$tag]["Dienstbeginn"][0]))
		{
			$tagesBeginn=strtotime(min(array_filter(array_values($Dienstplan[$tag]["Dienstbeginn"]))));
			$tagesEnde=strtotime(max(array_values($Dienstplan[$tag]["Dienstende"])));
//			echo "Wir beginnen bei $tagesBeginn und enden nach Schritten von $zeitAbstand bei $tagesEnde.";
			for ($dienstzeit=$tagesBeginn; $dienstzeit<=$tagesEnde; $dienstzeit=$dienstzeit+$zeitAbstand){$Dienstzeiten[]=$dienstzeit;}
			$DienstEnden=array_map('strtotime', $Dienstplan[$tag]["Dienstende"]);
			$DienstBeginne=array_map('strtotime', $Dienstplan[$tag]["Dienstbeginn"]);
			$MittagsEnden=array_map('strtotime', $Dienstplan[$tag]["Mittagsende"]);
			$MittagsBeginne=array_map('strtotime', $Dienstplan[$tag]["Mittagsbeginn"]);
			$histogrammCSV="";
			foreach($Dienstzeiten as $zeit)
			{
				$gekommene=count(array_filter(array_filter($DienstBeginne, function($value) {global $zeit; return $value <= $zeit;}))); //Die Zahl der Mitarbeiter, die irgendwann heute angefangen haben.
//				$nichtgegangene=count(array_filter(array_filter($DienstEnden, function($value) {global $zeit; return $value > $zeit;}))); //Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
				$gegangene=count(array_filter(array_filter($DienstEnden, function($value) {global $zeit; return $value <= $zeit;}))); //Die Anzahl der Mitarbeiter, die noch nicht gegangen sind.
				$mittagende=count(array_filter(array_filter($MittagsBeginne, function($value) {global $zeit; return $value <= $zeit;})));
				$gemittagte=count(array_filter(array_filter($MittagsEnden, function($value) {global $zeit; return $value <= $zeit;})));
				$mittagende=$mittagende-$gemittagte;
//				$anwesende=min($gekommene, $nichtgegangene);
				$anwesende=$gekommene-$gegangene;
				$anwesende=$anwesende-$mittagende;
				$histogrammCSV.=date('H:i', $zeit).", ".$anwesende."\n";
				//echo date('H:i', $zeit)."\t$gekommene\t$gegangene\t$mittagende\t$anwesende<br>\n";
			}

			$filename = "tmp/Histogramm.csv";
			$myfile = fopen($filename, "w") or die("Unable to open file!");
			fwrite($myfile, $histogrammCSV);
			fclose($myfile);
			$histogrammCSV="";
	
			//Und jetzt erraten wir noch die geschätzen Packungen, die wir an diesem Tag pro Zeit abverkaufen.
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
				$erwartungCSV.=$Wochentag['uhrzeit'][$key].", ".$tageszeitmedian*$faktorTagimmonat*$faktorMonatimjahr."\n";
			}
			
			$filename = "tmp/Erwartung.csv";
			$myfile = fopen($filename, "w") or die("Unable to open file!");
			fwrite($myfile, $erwartungCSV);
			fclose($myfile);
			$erwartungCSV="";
//			echo "<pre>";	var_export($Erwartung);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
//				echo "<pre>";	var_export($anwesende);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
			$command=('./Histogramm_image.sh '.escapeshellcmd("m".$mandant."_".$datum));
			exec($command, $kommandoErgebnis);
			//debug DEBUG to do: Die Dateien im tmp/ könnten wir anschließend alle wieder löschen.
			//debug DEBUG to do: EinEindeutige Unique Namen! Wenn gleichtzeitig mehrere Mitarbeiter zugreifen, werden mehrere Dateien mit dem gleichen Namen erzeugt. Das kann zu Fehlern führen.
			//echo "<pre>";	var_export($kommandoErgebnis);    	echo "</pre>"; // Hier kann der aus der Datenbank gelesene Datensatz zu Debugging-Zwecken angesehen werden.
		}
		else
		{
//			echo "<br>Kein Dienstplan gefunden beim Zeichnen des Histogramms.<br>\n";
		}

?>
