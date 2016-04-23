<?php
require 'default.php';
?>
<html>
	<head>
		<meta charset=UTF-8>
  		<link rel="stylesheet" type="text/css" href="style.css" media="all">
		<link rel="stylesheet" type="text/css" href="print.css" media="print">
		<script>"use strict";
			function confirmDelete(link)
			{
				var r = confirm("Diesen Datensatz wirklich löschen?");
				if (r == true)
				{
				//	alert("You pressed OK!");
				//	alert(link);
					window.location.replace(link); //Wechselt automatisch heraus aus der Eingabemaske.
				}
				else
				{
				//	alert("You pressed Cancel!");
					return false;
				}
			}
			function leavePage()
			{
				window.location.replace("https://www.google.de"); //Wechselt automatisch heraus aus der Eingabemaske.
			}
			window.setTimeout(leavePage, 900000); //Leave the page after x milliseconds of waiting. 900'000 = 15 Minutes.
			function updatesaldo()
			{
				//Wir lesen die Objekte aus dem HTML code.
				var stundenInputId		= document.getElementById("stunden");
				var stundenSaldoId		= document.getElementById("saldoAlt");
				var stundenSaldoNeuId		= document.getElementById("saldoNeu");

				//Wir entnehmen die vorhandenen Werte.
				if ( stundenSaldoId != null) { //For new Coworkers there is no value set. Therefore we start with 0.
					var stundenSaldoValue		= Number(stundenSaldoId.innerHTML);
				}else {
					var stundenSaldoValue		= 0;
				}
				var stundenInputArray		= stundenInputId.value.split(":");
				if (stundenInputArray[1]) //Wenn es einen Doppelpunkt gibt.
				{
//					document.write('Wir haben einen Doppelpunkt.');
					//Die Eingabe ist eine Zeit mit Doppelpunkt. Wir rechnen in einen float (Kommazahl) um.
					var stundenInputHour 		= Number(stundenInputArray[0]);
					var stundenInputMinute 		= Number(stundenInputArray[1]);
					var stundenInputSecond		= Number(stundenInputArray[2]);

					//Jetzt berechnen wir aus den Daten eine Summe. Dazu formen wir zunächst in ein gültiges Datum um.
					var stundenInputValue = 0;// Wir initialisieren den Input als Null und addieren dann Sekunden, Minuten und Stunden dazu.
					if(!isNaN(stundenInputSecond))
					{
						stundenInputValue		= stundenInputValue + stundenInputSecond/3600;
					}
					if(!isNaN(stundenInputMinute))
					{
						stundenInputValue		= stundenInputValue + stundenInputMinute/60;
					}
					if(!isNaN(stundenInputHour))
					{
						stundenInputValue		= stundenInputValue + stundenInputHour;
					}
					stundenInputId.value = stundenInputValue;
				}
				else
				{
					//Die Stunden sind eine Ganzzahl oder eine Kommazahl.
					//Wir entnehmen die vorhandenen Werte.
					//Wir brauchen die Kommazahl mit einem Punkt, nicht mit einem Komma.
					stundenInputId.value = stundenInputId.value.replace(/,/g, '.')
					var stundenInputValue		= Number(stundenInputId.value);
				}
				var ergebnis		 	= stundenInputValue + stundenSaldoValue;
				stundenSaldoNeuId.value 	= ergebnis;
			}
		</script>
	</head>
	<body>
		<?php
			require 'db-verbindung.php';
			//Hole eine Liste aller Mitarbeiter
			require 'db-lesen-mitarbeiter.php';
			$VKmax=max(array_keys($Mitarbeiter)); //Wir suchen die höchste VK-Nummer.
			//Hole eine Liste aller Mandanten (Filialen)
			require 'db-lesen-mandant.php';
			if(isset($_POST['auswahl_mitarbeiter']))
			{
				$auswahl_mitarbeiter=$_POST['auswahl_mitarbeiter'];
			}
			elseif(isset($_GET['auswahl_mitarbeiter']))
			{
				$auswahl_mitarbeiter=$_GET['auswahl_mitarbeiter'];
			}
			elseif(isset($_COOKIE['auswahl_mitarbeiter']))
			{
				$auswahl_mitarbeiter=$_COOKIE['auswahl_mitarbeiter'];
			}
			else
			{
					$auswahl_mitarbeiter=1;
			}
			if (isset($auswahl_mitarbeiter))
			{
				create_cookie("auswahl_mitarbeiter", $auswahl_mitarbeiter); //Diese Funktion wird von cookie-auswertung.php bereit gestellt. Sie muss vor dem ersten echo durchgeführt werden.
			}

			//Wir löschen Datensätze, wenn dies befohlen wird.
			if(isset($_GET['command']) and isset($_GET['vk']) and isset($_GET['datum']))
			{
				if($_GET['command'] == "delete")
				{
					$auswahl_mitarbeiter=$_GET['vk'];
					$abfrage="DELETE FROM `Stunden`
						WHERE `VK` = ".$_GET['vk']." AND `Datum` = '".$_GET['datum']."'";
					//echo "$abfrage";
					$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
				}
			}
			//Wir fügen neue Datensätze ein, wenn ALLE Daten übermittelt werden. (Leere Daten klappen vielleicht auch.)
			if(isset($_POST['submitStunden']) and isset($_POST['auswahl_mitarbeiter']) and isset($_POST['datum']) and isset($_POST['stunden']) and isset($_POST['saldo']) and isset($_POST['grund']))
			{
					$abfrage="INSERT INTO `Stunden`
						(VK, Datum, Stunden, Saldo, Grund)
						VALUES (".$_POST['auswahl_mitarbeiter'].", '". $_POST['datum']."', ". $_POST['stunden'].", ". $_POST['saldo'].", '". $_POST['grund']."')";
					$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
			}
			$vk=$auswahl_mitarbeiter;
			$abfrage="SELECT * FROM `Stunden`
				WHERE `VK` = ".$vk."
				ORDER BY `Aktualisierung` ASC
				LIMIT 10";
			$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
			$number_of_rows = mysqli_num_rows($ergebnis);
			$tablebody=""; $i=1;
			while ($row=mysqli_fetch_object($ergebnis))
			{
				$tablebody.= "\t\t\t<tr>\n";
				$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				$tablebody.= date('d.m.Y', strtotime($row->Datum))." <a align=right href=javascript:void(); title='Diesen Datensatz löschen' onClick=confirmDelete('?command=delete&vk=$row->VK&datum=$row->Datum')>[x]</a>";
				$tablebody.= "\n\t\t\t\t</td>\n";
				$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				$tablebody.= "$row->Grund";
				$tablebody.= "\n\t\t\t\t</td>\n";
				$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				$tablebody.= "$row->Stunden";
				$tablebody.= "\n\t\t\t\t</td>\n";
				if($i == $number_of_rows)
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

			if (empty($saldo)) {
				$saldo=0;
			}


//Hier beginnt die Ausgabe
require 'navigation.php';
echo "<div class=no-image>\n";
echo "\t\t<form method=POST>\n";
echo "\t\t\t<select name=auswahl_mitarbeiter class=no-print onChange=document.getElementById('submitAuswahlMitarbeiter').click()>\n";
echo "\t\t\t\t<option value=$auswahl_mitarbeiter>".$auswahl_mitarbeiter." ".$Mitarbeiter[$auswahl_mitarbeiter]."</option>,\n";
for ($vk=1; $vk<$VKmax+1; $vk++)
{
	if(isset($Mitarbeiter[$vk]))
	{
		echo "\t\t\t\t<option value=$vk>".$vk." ".$Mitarbeiter[$vk]."</option>,\n";
	}
}
echo "\t\t\t</select>\n";
$submit_button="\t\t\t<input type=submit value=Auswahl name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n"; echo $submit_button; //name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
echo "\t\t\t<H1>".$Mitarbeiter[$auswahl_mitarbeiter]."</H1>\n";
echo "<a href=stunden-out.php?auswahl_mitarbeiter=$auswahl_mitarbeiter>[Lesen]</a>";
			echo "\t\t<table border=1>\n";
//Überschrift
			echo "\t\t\t<tr>\n
				\t\t\t\t<th>\n
				\t\t\t\t\tDatum\n
				\t\t\t\t</th>\n
				\t\t\t\t<th>\n
				\t\t\t\t\tGrund\n
				\t\t\t\t</th>\n
				\t\t\t\t<th>\n
				\t\t\t\t\tStunden\n\t\t\t\t</td>\n
				\t\t\t\t<th>\n
				\t\t\t\t\tSaldo\n
				\t\t\t\t</th>\n
				\t\t\t</tr>\n";
//Ausgabe
			echo "$tablebody";
//Eingabe. Der Saldo wird natürlich berechnet.
			echo "\t\t\t<tr>\n";
/*
			echo "\t\t\t\t<td>\n\t\t\t\t\t";
			echo $Mitarbeiter[$row->VK];
			echo "\n\t\t\t\t</td>\n";
*/
			echo "\t\t\t\t<td>\n\t\t\t\t\t";
			echo "<input type=date value=".date('Y-m-d')." name=datum>";
			echo "\n\t\t\t\t</td>\n";
			echo "\t\t\t\t<td>\n\t\t\t\t\t";
			echo "<input type=text id=grund name=grund>";
			echo "\n\t\t\t\t</td>\n";
			echo "\t\t\t\t<td>\n\t\t\t\t\t";
			echo "<input type=text onchange=updatesaldo() id=stunden name=stunden value=>";
			echo "\n\t\t\t\t</td>\n";
			echo "\t\t\t\t<td>\n\t\t\t\t\t";
			echo "<input readonly type=text name=saldo id=saldoNeu value=".$saldo.">";
			echo "\n\t\t\t\t</td>\n";
			echo "\n\t\t\t</tr>\n";
			echo "\t\t</table>\n";
			echo "<input type=submit name=submitStunden value='Eintragen'>";
			echo "\t</form>";
//		echo "<pre>"; var_dump($_POST); echo "</pre>";
			echo "</div>\n";
		?>
	</body>
</html>
