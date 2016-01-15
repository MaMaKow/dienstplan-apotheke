<?php
require 'default.php';
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
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
				var stundenSaldoValue		= Number(stundenSaldoId.innerHTML);
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
				}
				else
				{
					//Die Stunden sind eine Ganzzahl oder eine Kommazahl.
					//Wir entnehmen die vorhandenen Werte.
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
			if(isset($_POST['auswahlMitarbeiter']))
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

			//Wir löschen Datensätze, wenn dies befohlen wird.
			if(isset($_GET['command']) and isset($_GET['vk']) and isset($_GET['datum']))
			{
				if($_GET['command'] == "delete")
				{
					$auswahlMitarbeiter=$_GET['vk'];
					$abfrage="DELETE FROM `Stunden`
						WHERE `VK` = ".$_GET['vk']." AND `Datum` = '".$_GET['datum']."'";
					//echo "$abfrage";
					$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
				}
			}
			//Wir fügen neue Datensätze ein, wenn ALLE Daten übermittelt werden. (Leere Daten klappen vielleicht auch.)
			if(isset($_POST['submitStunden']) and isset($_POST['auswahlMitarbeiter']) and isset($_POST['datum']) and isset($_POST['stunden']) and isset($_POST['saldo']) and isset($_POST['grund']))
			{
					$abfrage="INSERT INTO `Stunden`
						(VK, Datum, Stunden, Saldo, Grund) 	
						VALUES (".$_POST['auswahlMitarbeiter'].", '". $_POST['datum']."', ". $_POST['stunden'].", ". $_POST['saldo'].", '". $_POST['grund']."')";
					$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
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
				$tablebody.= date('d.m.Y', strtotime($row->Datum))." <a align=right href=javascript:void(); title='Diesen Datensatz löschen' onClick=confirmDelete('?command=delete&vk=$row->VK&datum=$row->Datum')>[x]</a>";
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
echo "<a href=stunden-out.php?auswahlMitarbeiter=$auswahlMitarbeiter>[Lesen]</a>";
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
		?>
	</body>
</html>
