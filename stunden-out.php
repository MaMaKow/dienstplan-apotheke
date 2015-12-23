<?php
require 'default.php';
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<?php
			require 'db-verbindung.php';
			//Hole eine Liste aller Mitarbeiter
			require 'db-lesen-mitarbeiter.php';
			$VKmax=max(array_keys($Mitarbeiter)); //Wir suchen die höchste VK-Nummer.
			//Hole eine Liste aller Mandanten (Filialen)
			require 'db-lesen-mandant.php';
			if(isset($_POST['submitAuswahlMitarbeiter']))
			{
				$auswahlMitarbeiter=$_POST['auswahlMitarbeiter'];
			}
			elseif(isset($_GET['auswahlMitarbeiter']))
			{
				$auswahlMitarbeiter=$_GET['auswahlMitarbeiter'];
			}
			else
			{
				$auswahlMitarbeiter=1;
			}
			$vk=$auswahlMitarbeiter;
			$abfrage="SELECT * FROM `Stunden`
				WHERE `VK` = ".$vk."
				ORDER BY `Aktualisierung` ASC
				LIMIT 10";
			$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
			$numberOfRows = mysqli_num_rows($ergebnis);
			$tablebody=""; $i=1;
			while ($row=mysqli_fetch_object($ergebnis))
			{
				$tablebody.= "\t\t\t<tr>\n";
				$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				$tablebody.= "$row->Datum";
				$tablebody.= "\n\t\t\t\t</td>\n";
				$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				$tablebody.= "$row->Grund";
				$tablebody.= "\n\t\t\t\t</td>\n";
				$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				$tablebody.= "$row->Stunden";
				$tablebody.= "\n\t\t\t\t</td>\n";
				if($i == $numberOfRows)
				{
					$tablebody.= "\t\t\t\t<td id=saldoAlt>\n\t\t\t\t\t";
				}
				else
				{
					$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				}
				$tablebody.= "$row->Saldo"; $saldo=$row->Saldo; //Wir tragen den Saldo mit uns fort.
				$tablebody.= "\n\t\t\t\t</td>\n";
				$tablebody.= "\n\t\t\t</tr>\n";
				$i++;
			}


//Hier beginnt die Ausgabe
echo "\t\t<form method=POST>\n";
echo "\t\t\t<select name=auswahlMitarbeiter class=no-print onChange=document.getElementById('submitAuswahlMitarbeiter').click()>\n";
echo "\t\t\t\t<option value=$auswahlMitarbeiter>".$auswahlMitarbeiter." ".$Mitarbeiter[$auswahlMitarbeiter]."</option>,\n";
for ($vk=1; $vk<$VKmax+1; $vk++)
{
	if(isset($Mitarbeiter[$vk]))
	{
		echo "\t\t\t\t<option value=$vk>".$vk." ".$Mitarbeiter[$vk]."</option>,\n";
	}
}
echo "\t\t\t</select>\n";
$submitButton="\t\t\t<input type=submit value=Auswahl name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n"; echo $submitButton; //name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
echo "\t\t\t<H1>".$Mitarbeiter[$auswahlMitarbeiter]."</H1>\n";
echo "<a class=no-print href=stunden-in.php?auswahlMitarbeiter=$auswahlMitarbeiter>[Bearbeiten]</a>";
			echo "\t\t<table border=1>\n";
//Überschrift
			echo "\t\t\t<tr>\n
				\t\t\t\t<td>\n
				\t\t\t\t\tDatum\n
				\t\t\t\t</td>\n
				\t\t\t\t<td>\n
				\t\t\t\t\tGrund\n
				\t\t\t\t</td>\n
				\t\t\t\t<td>\n
				\t\t\t\t\tStunden\n\t\t\t\t</td>\n
				\t\t\t\t<td>\n
				\t\t\t\t\tSaldo\n
				\t\t\t\t</td>\n
				\t\t\t</tr>\n";
//Ausgabe 
			echo "$tablebody";
//		echo "<pre>"; var_dump($_POST); echo "</pre>";
		?>
	</body>
</html>
