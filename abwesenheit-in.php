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
			function updateTage() 
			{
				//Wir lesen die Objekte aus dem HTML code.
				var beginnId			= document.getElementById("beginn");
				var endeId			= document.getElementById("ende");
				var tageId			= document.getElementById("tage");
				
				//Wir entnehmen die vorhandenen Werte.
				var beginn			= new Date (beginnId.value);
				var ende			= new Date (endeId.value);
				if (beginn > ende) {alert('Das Ende liegt vor dem Startdatum'); }
				var start = new Date(beginn.getTime());
				var end = new Date(ende.getTime());
				var count = 0;
				while (start <= end) 
				{
					if (start.getDay() != 0 && start.getDay() != 6)
					{
						count++;
					}
					start.setDate(start.getDate() + 1);
				}
				tageId.value 	= count;
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
			if(isset($_GET['command']) and isset($_GET['vk']) and isset($_GET['beginn']))
			{
				$auswahlMitarbeiter=$_GET['vk']; //Wir übernehmen den VK aus dem GET Anteil, weil wir keine POST-Variablen bekommen. Daher heißt der übergebene Parameter hier auch vk und nicht auswahlMitarbeiter. 
				if($_GET['command'] == "delete")
				{
					$abfrage="DELETE FROM `Abwesenheit`
						WHERE `VK` = ".$_GET['vk']." AND `Beginn` = '".$_GET['beginn']."'";
//					echo "$abfrage";
					$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
				}
			}
			//Wir fügen neue Datensätze ein, wenn ALLE Daten übermittelt werden. (Leere Daten klappen vielleicht auch.)
			if(isset($_POST['submitStunden']) and isset($_POST['auswahlMitarbeiter']) and isset($_POST['beginn']) and isset($_POST['ende']) and isset($_POST['tage']) and isset($_POST['grund']))
			{
				for ($tag=strtotime($_POST['beginn']); $tag<=strtotime($_POST['ende']); $tag=strtotime('+1 day', strtotime($datum)))
				{
					$datum=date('Y-m-d', $tag);
//					echo "$datum<br>\n";
					require "db-lesen-feiertag.php";
					if (!empty($feiertag))
					{
						echo "$feiertag ist ein Feiertag ($datum).<br>\n";
						--$_POST['tage'];
					}
				}
				$abfrage="INSERT INTO `Abwesenheit`
					(VK, Beginn, Ende, Tage, Grund) 	
					VALUES (".$_POST['auswahlMitarbeiter'].", '". $_POST['beginn']."', '". $_POST['ende']."', '". $_POST['tage']."', '". $_POST['grund']."')";
//				echo "$abfrage";
				$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
			}
			$vk=$auswahlMitarbeiter;
			$abfrage="SELECT * FROM `Abwesenheit`
				WHERE `VK` = ".$vk."
				ORDER BY `Beginn` ASC
				LIMIT 10";
			$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
			$numberOfRows = mysqli_num_rows($ergebnis);
			$tablebody=""; $i=1;
			while ($row=mysqli_fetch_object($ergebnis))
			{
				$tablebody.= "\t\t\t<tr>\n";
				$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				$tablebody.= "$row->Beginn <a class=no-print align=right href=javascript:void(); title='Diesen Datensatz löschen' onClick=confirmDelete('?command=delete&vk=$row->VK&beginn=$row->Beginn')>[x]</a>";
				$tablebody.= "\n\t\t\t\t</td>\n";
				$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				$tablebody.= "$row->Ende";
				$tablebody.= "\n\t\t\t\t</td>\n";
				if($i == $numberOfRows)
				{
					$tablebody.= "\t\t\t\t<td id=letzterGrund>\n\t\t\t\t\t";
				}
				else
				{
					$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				}
				$tablebody.= "$row->Grund";
				$tablebody.= "\n\t\t\t\t</td>\n";
				$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
				$tablebody.= "$row->Tage";
				$tablebody.= "\n\t\t\t\t</td>\n";
				$tablebody.= "\n\t\t\t</tr>\n";
				$i++;
			}
			$abfrage='SELECT DISTINCT `Grund` FROM `Abwesenheit` ORDER BY `Grund` ASC';
			$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
			$datalist= "<datalist id='gruende'>\n";
			while($row = mysqli_fetch_object($ergebnis))
			{
				$datalist.= "\t<option value='$row->Grund'>\n";
			}
			$datalist.= "</datalist>\n";


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
echo "<a class=no-print href=abwesenheit-out.php?auswahlMitarbeiter=$auswahlMitarbeiter>[Lesen]</a>";
			echo "\t\t<table border=1>\n";
//Überschrift
			echo "\t\t\t<tr>\n
				\t\t\t\t<td>\n
				\t\t\t\t\tBeginn\n
				\t\t\t\t</td>\n
				\t\t\t\t<td>\n
				\t\t\t\t\tEnde\n
				\t\t\t\t</td>\n
				\t\t\t\t<td>\n
				\t\t\t\t\tGrund\n
				\t\t\t\t</td>\n
				\t\t\t\t<td>\n
				\t\t\t\t\tTage\n
				\t\t\t\t</td>\n
				\t\t\t</tr>\n";
//Ausgabe 
			echo "$tablebody";
//Eingabe. Der Saldo wird natürlich berechnet.
			echo "\t\t\t<tr class=no-print>\n";
			echo "\t\t\t\t<td>\n\t\t\t\t\t";
			echo "<input type=date onchange=updateTage() id=beginn name=beginn>";
			echo "\n\t\t\t\t</td>\n";
			echo "\t\t\t\t<td>\n\t\t\t\t\t";
			echo "<input type=date onchange=updateTage() id=ende name=ende>";
			echo "\n\t\t\t\t</td>\n";
			echo "\t\t\t\t<td>\n\t\t\t\t\t";
			echo "<input list='gruende' name=grund>";
			echo "$datalist";
			echo "\n\t\t\t\t</td>\n";
			echo "\t\t\t\t<td>\n\t\t\t\t\t";
			echo "<input readonly type=number id=tage name=tage title='Feiertage werden erst im nächsten Schritt abgezogen.'>";
			echo "\n\t\t\t\t</td>\n";
			echo "\n\t\t\t</tr>\n";
			echo "\t\t</table>\n";
			echo "<input type=submit class=no-print name=submitStunden value='Eintragen'>";
			echo "\t</form>";
//		echo "<pre>"; var_dump($_POST); echo "</pre>";
		?>
	</body>
</html>
