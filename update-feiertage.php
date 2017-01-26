<html>
	<body>
		<?php
		//Dieses Script soll eine Liste der Feiertage aus einer Internet Quelle laden und in der Datenbank hinterlegen.
		require 'default.php';
			$bundesland='MV';
			for ($i=0; $i<4; $i++)
			{
				$Feiertage=array();
				$jahr=date('Y')+$i;
				echo "Wir suchen die Feiertage für $jahr.";
				$FeiertageCSV=file('http://www.feiertage.net/csvfile.php?state='.$bundesland.'&year='.$jahr.'&type=csv');
				foreach($FeiertageCSV as $key => $feiertag)
				{
					$Feiertage[]=explode(';', $feiertag);
					if (strtotime($Feiertage[$key][0])) //Gültiges Datumsformat (Die erste Zeile enthält nämlich nur die Überschriften)
					{
					$abfrage="INSERT IGNORE INTO `Feiertage` (Name, Datum) 
						VALUES ('".$Feiertage[$key][1]."', '".date('Y-m-d', strtotime($Feiertage[$key][0]))."')";  
					$ergebnis = mysqli_query_verbose($abfrage);
					}
//					echo "<pre>"; var_export($Feiertage); echo "</pre>";
				}
			}
		?>
	</body>
</html>
