<?php
require 'default.php';
?>
<html>
<?php require 'head.php';?>
<body>
		<?php
require 'navigation.php';
echo "<div class=main-area>\n";
			require 'db-verbindung.php';
			//Hole eine Liste aller Mitarbeiter
			require 'db-lesen-mitarbeiter.php';
			$VKmax=max(array_keys($Mitarbeiter)); //Wir suchen die höchste VK-Nummer.
			//Hole eine Liste aller Mandanten (Filialen)
			require 'db-lesen-mandant.php';
			if(isset($_POST['submitAuswahlMitarbeiter']))
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
				create_cookie("auswahl_mitarbeiter", $auswahl_mitarbeiter, 30); //Diese Funktion wird von cookie-auswertung.php bereit gestellt. Sie muss vor dem ersten echo durchgeführt werden.
			}
			$vk=$auswahl_mitarbeiter;
			$abfrage="SELECT * FROM `Stunden`
				WHERE `VK` = ".$vk."
				ORDER BY `Aktualisierung` ASC
				";
			$ergebnis=mysqli_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
			$number_of_rows = mysqli_num_rows($ergebnis);
			$tablebody=""; $i=1;
			while ($row=mysqli_fetch_object($ergebnis))
			{
				$tablebody.= "\t\t\t<tr>\n";
				$tablebody.= "\t\t\t\t<td>";
				$tablebody.= "<a href=tag-out.php?datum=".date('Y-m-d', strtotime($row->Datum)).">".date('d.m.Y', strtotime($row->Datum))."</a>";
				$tablebody.= "</td>\n";
				$tablebody.= "\t\t\t\t<td>";
				$tablebody.= "$row->Grund";
				$tablebody.= "</td>\n";
				$tablebody.= "\t\t\t\t<td>";
				$tablebody.= "$row->Stunden";
				$tablebody.= "</td>\n";
				if($i == $number_of_rows)
				{
					$tablebody.= "\t\t\t\t<td id=saldoAlt>";
				}
				else
				{
					$tablebody.= "\t\t\t\t<td>";
				}
				$tablebody.= "$row->Saldo"; $saldo=$row->Saldo; //Wir tragen den Saldo mit uns fort.
				$tablebody.= "</td>\n";
				$tablebody.= "\n\t\t\t</tr>\n";
				$i++;
			}

			//Hier beginnt die Ausgabe
			echo "\t\t<form method=POST>\n";
			echo "\t\t\t<select name=auswahl_mitarbeiter class='no-print large' onChange=document.getElementById('submitAuswahlMitarbeiter').click()>\n";
			echo "\t\t\t\t<option value=$auswahl_mitarbeiter>".$auswahl_mitarbeiter." ".$Mitarbeiter[$auswahl_mitarbeiter]."</option>,\n";
			for ($vk=1; $vk<$VKmax+1; $vk++)
			{
				if(isset($Mitarbeiter[$vk]))
				{
					echo "\t\t\t\t<option value=$vk>".$vk." ".$Mitarbeiter[$vk]."</option>,\n";
				}
			}

				echo "\t\t\t</select><br><br>\n";
				//name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
				$submit_button="\t\t\t<input type=submit hidden value=Auswahl name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n";
				echo $submit_button;
				echo "\t\t\t<H1 class=only-print>".$Mitarbeiter[$auswahl_mitarbeiter]."</H1>\n";
				echo "\t\t\t<div class=no-print><br><a href=stunden-in.php?auswahl_mitarbeiter=$auswahl_mitarbeiter>[Bearbeiten]</a><br><br></div>\n";
				echo "\t\t<table border=1>\n";
				//Überschrift
				echo "\t\t\t<tr>\n".
				"\t\t\t\t<th>Datum</th>\n".
				"\t\t\t\t<th>Grund</th>\n".
				"\t\t\t\t<th>Stunden</th>\n".
				"\t\t\t\t<th>Saldo</th>\n".
				"\t\t\t</tr>\n";
//Ausgabe
			echo "$tablebody";
			echo "\t\t</table>\n";
			echo "\t</form>\n";
//			echo "<pre>"; var_dump($_POST); echo "</pre>";
			echo "</div>\n";
			require 'contact-form.php';
		?>
	</body>
</html>
