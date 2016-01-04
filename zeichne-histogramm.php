<?php

		//Wir zeichnen eine Kurve der Anzahl der Mitarbeiter.
		//Zunächst müssen wir festlegen, wie groß die Zeitsprünge im Diagramm werden.
		$zeitAbstand=5*60; //5 Minuten
		if (!isset($tag)) {$tag=0;} //Beim Aufruf aus tag-out wird kein Tag übergeben. Beim Aufruf aus der Auswertung, wird ein $tag übergeben.
		$faktorArbeitskraft=6; //Wie viele Packungen schafft ein Mitarbeiter pro Zeiteinheit (?halbe Stunde?)?
		if (!empty($Dienstplan[$tag]["Dienstbeginn"][0]))
		{
			$tagesBeginn=strtotime('8:00:00');
			$tagesEnde=strtotime('20:00:00');
//			$tagesBeginn=strtotime(min(array_filter(array_values($Dienstplan[$tag]["Dienstbeginn"]))));
//			$tagesEnde=strtotime(max(array_values($Dienstplan[$tag]["Dienstende"])));
			//Wenn die Funktion bereits aufgerufen wurde, ist dieser Wert bereits gesetzt.
			if(empty($Dienstzeiten[0]))
			{
				for ($dienstzeit=$tagesBeginn; $dienstzeit<=$tagesEnde; $dienstzeit=$dienstzeit+$zeitAbstand){$Dienstzeiten[]=$dienstzeit;}
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
		}

?>
