<?php
	require 'default.php';
	//Hole eine Liste aller Mitarbeiter
	require 'db-lesen-mitarbeiter.php';
	$VKmax=max(array_keys($Mitarbeiter)); //Wir suchen die höchste VK-Nummer.
	//Hole eine Liste aller Mandanten (Filialen)
	require 'db-lesen-mandant.php';
	if(filter_has_var(INPUT_POST, 'auswahl_mitarbeiter'))
	{
		$auswahl_mitarbeiter = filter_input(INPUT_POST, 'auswahl_mitarbeiter', FILTER_SANITIZE_NUMBER_INT);
	}
	elseif(isset($_GET['auswahl_mitarbeiter']))
	{
		$auswahl_mitarbeiter = filter_input(INPUT_GET, 'auswahl_mitarbeiter', FILTER_SANITIZE_NUMBER_INT);
	}
	elseif(isset($_COOKIE['auswahl_mitarbeiter']))
	{
		$auswahl_mitarbeiter = filter_input(INPUT_COOKIE, 'auswahl_mitarbeiter', FILTER_SANITIZE_NUMBER_INT);
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
	$abfrage="SELECT * FROM `absence`
		WHERE `employee_id` = ".$vk."
		ORDER BY `start` ASC
		";
	$ergebnis=  mysqli_query_verbose($abfrage);
	$number_of_rows = mysqli_num_rows($ergebnis);
	$tablebody=""; $i=1;
	while ($row=mysqli_fetch_object($ergebnis))
	{
		$tablebody.= "\t\t\t<tr>\n";
		$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
		$tablebody.= date('d.m.Y', strtotime($row->start));
		$tablebody.= "\n\t\t\t\t</td>\n";
		$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
		$tablebody.= date('d.m.Y', strtotime($row->end));
		$tablebody.= "\n\t\t\t\t</td>\n";
		if($i == $number_of_rows)
		{
                        //TODO: This whole part might be unnecessary. We might remove it with some testing.
			$tablebody.= "\t\t\t\t<td id=letzterGrund>\n\t\t\t\t\t";
		}
		else
		{
			$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
		}
		$tablebody.= "$row->reason";
		$tablebody.= "\n\t\t\t\t</td>\n";
		$tablebody.= "\t\t\t\t<td>\n\t\t\t\t\t";
		$tablebody.= "$row->days";
		$tablebody.= "\n\t\t\t\t</td>\n";
		$tablebody.= "\n\t\t\t</tr>\n";
		$i++;
	}
require 'head.php';
require 'navigation.php';
require 'src/html/menu.html';
//Hier beginnt die Ausgabe
echo "\t\t<div id=main-area>\n";

echo build_select_employee($auswahl_mitarbeiter);

echo "<a class=no-print href='abwesenheit-in.php?auswahl_mitarbeiter=$auswahl_mitarbeiter'><br>[Bearbeiten]</a>";
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
			require 'contact-form.php';
		?>

	</body>
</html>
