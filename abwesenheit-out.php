<?php
	require 'default.php';
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
		create_cookie("auswahl_mitarbeiter", $auswahl_mitarbeiter, 30); //Diese Funktion wird von cookie-auswertung.php bereit gestellt. Sie muss vor dem ersten echo durchgeführt werden.
	}
	$vk=$auswahl_mitarbeiter;
	$abfrage="SELECT * FROM `Abwesenheit`
		WHERE `VK` = ".$vk."
		ORDER BY `Beginn` ASC
		";
	$ergebnis=  mysqli_query_verbose($abfrage);
	$number_of_rows = mysqli_num_rows($ergebnis);
	$tablebody=""; $i=1;
	while ($row=mysqli_fetch_object($ergebnis))
	{
		$tablebody.= "\t\t\t<tr>\n";
		$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
		$tablebody.= date('d.m.Y', strtotime($row->Beginn));
		$tablebody.= "\n\t\t\t\t</td>\n";
		$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
		$tablebody.= date('d.m.Y', strtotime($row->Ende));
		$tablebody.= "\n\t\t\t\t</td>\n";
		if($i == $number_of_rows)
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
	$ergebnis=  mysqli_query_verbose($abfrage);
	$datalist= "<datalist id='gruende'>\n";
	while($row = mysqli_fetch_object($ergebnis))
	{
		$datalist.= "\t<option value='$row->Grund'>\n";
	}
	$datalist.= "</datalist>\n";
require 'head.php';
require 'navigation.php';
//Hier beginnt die Ausgabe
echo "\t\t<div id=main-area>\n";
echo "\t\t<form method=POST>\n";
echo "\t\t\t<select name=auswahl_mitarbeiter class='no-print large' onChange='document.getElementById(\"submitAuswahlMitarbeiter\").click()'>\n";
for ($vk=1; $vk<$VKmax+1; $vk++)
{
	if(isset($Mitarbeiter[$vk]))
	{
		if ($vk == $auswahl_mitarbeiter) {
			echo "\t\t\t\t<option value=$vk selected>".$vk." ".$Mitarbeiter[$vk]."</option>\n";
		} else {
			echo "\t\t\t\t<option value=$vk>".$vk." ".$Mitarbeiter[$vk]."</option>\n";
		}

	}
}
echo "\t\t\t</select>\n";
$submit_button="\t\t\t<input hidden type=submit value=Auswahl name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n"; echo $submit_button; //name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
echo "\t\t\t<H1>".$Mitarbeiter[$auswahl_mitarbeiter]."</H1>\n";
echo "<a class=no-print href='abwesenheit-in.php?auswahl_mitarbeiter=$auswahl_mitarbeiter'>[Bearbeiten]</a>";
			echo "\t\t<table>\n";
//Überschrift
			echo "\t\t\t<tr>\n
				\t\t\t\t<th>\n
				\t\t\t\t\tBeginn\n
				\t\t\t\t</th>\n
				\t\t\t\t<th>\n
				\t\t\t\t\tEnde\n
				\t\t\t\t</th>\n
				\t\t\t\t<th>\n
				\t\t\t\t\tGrund\n
				\t\t\t\t</th>\n
				\t\t\t\t<th>\n
				\t\t\t\t\tTage\n
				\t\t\t\t</th>\n
				\t\t\t</tr>\n";
//Ausgabe
			echo "$tablebody";
			echo "\t\t</table>\n";
			echo "\t</form>";
			echo "\t\t</div>\n";
//		echo "<pre>"; var_dump($_POST); echo "</pre>";
			require 'contact-form.php';
		?>

	</body>
</html>
