<?php
require 'default.php';
    //Hole eine Liste aller Mitarbeiter
            require 'db-lesen-mitarbeiter.php';
            //$VKmax = max(array_keys($Mitarbeiter)); //Wir suchen die höchste VK-Nummer.
            //Hole eine Liste aller Mandanten (Filialen)
            require 'db-lesen-mandant.php';
            if (isset($_POST['auswahl_mitarbeiter'])) {
                $auswahl_mitarbeiter = filter_input(INPUT_POST, 'auswahl_mitarbeiter', FILTER_VALIDATE_INT);
            } elseif (isset($_GET['auswahl_mitarbeiter'])) {
                $auswahl_mitarbeiter = filter_input(INPUT_GET, 'auswahl_mitarbeiter', FILTER_VALIDATE_INT);
            } elseif (isset($_COOKIE['auswahl_mitarbeiter'])) {
                $auswahl_mitarbeiter = filter_input(INPUT_COOKIE, 'auswahl_mitarbeiter', FILTER_VALIDATE_INT);
            } else {
                $auswahl_mitarbeiter = 1;
            }

            if (isset($auswahl_mitarbeiter)) {
                create_cookie('auswahl_mitarbeiter', $auswahl_mitarbeiter, 30);
            }

            //Wir löschen Datensätze, wenn dies befohlen wird.
            if ($command = filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING) and 'delete' === $command) {
                //$Loeschen = filter_input(INPUT_POST, 'loeschen', FILTER_REQUIRE_ARRAY);
//                foreach ($Loeschen as $vk => $Beginne) {
  //                  foreach ($Beginne as $beginn => $X) {
                $vk = filter_input(INPUT_POST, 'auswahl_mitarbeiter', FILTER_VALIDATE_INT);
                $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING);
                $abfrage = "DELETE FROM `Abwesenheit`
                	WHERE `VK` = '$vk' AND `Beginn` = '$beginn'";
              //  		echo "$abfrage";
                $ergebnis = mysqli_query_verbose($abfrage);
    //                }
      //          }
                $auswahl_mitarbeiter = $vk;
            }

            //We create new entries or edit old entries. (Empty values are not accepted.)
            if ((isset($_POST['submitStunden']) or (isset($_POST['command']) and 'replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING)))
                    and $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING)                    
                    and $ende = filter_input(INPUT_POST, 'ende', FILTER_SANITIZE_STRING)
                    and $grund = filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_STRING)
                    ){
                $auswahl_mitarbeiter = filter_input(INPUT_POST, 'auswahl_mitarbeiter', FILTER_VALIDATE_INT);
                if ($auswahl_mitarbeiter === FALSE){
                    return FALSE;
                }
                $tage = 0;
                for ($tag = strtotime($beginn); $tag <= strtotime($ende); $tag = strtotime('+1 day', strtotime($datum))) {
                    $datum = date('Y-m-d', $tag);
                    if (date('w', strtotime($datum)) < 6 and date('w', strtotime($datum)) > 0) {
                        $tage++;
                    }
                   
                    //Now the holidays which are not on a weekend are substracted.
                    require 'db-lesen-feiertag.php';
                    if (isset($feiertag) and date('w', strtotime($datum)) < 6 and date('w', strtotime($datum)) > 0) {
                        $Feiertagsmeldung[] = "$feiertag ($datum)<br>\n";
                        $tage--;
                    }
                }
                //var_export($tage);
                if ('replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING)){
                    $beginn_old = filter_input(INPUT_POST, 'beginn_old', FILTER_SANITIZE_STRING);
                    $abfrage = "DELETE FROM `Abwesenheit` WHERE `VK` = '$auswahl_mitarbeiter' AND `Beginn` = '$beginn_old'"; 
                    //echo "$abfrage<br>\n";
                                    $ergebnis = mysqli_query_verbose($abfrage);
                }
                $abfrage = "INSERT INTO `Abwesenheit` "
                        . "(VK, Beginn, Ende, Tage, Grund) "
                        . "VALUES (".$auswahl_mitarbeiter.", '".$beginn."', '".$ende."', '".$tage."', '".$grund."')";
                //echo "$abfrage<br>\n";
		if( !($ergebnis = mysqli_query($verbindungi, $abfrage)) ) {
			$error_string = mysqli_error($verbindungi);
			if (strpos($error_string, 'Duplicate') !== false){
				$Fehlermeldung[] = "<b>An diesem Datum existiert bereits ein Eintrag!</b>\n Die Daten wurden daher nicht in die Datenbank eingefügt.";
			} else {
				//Are there other errors, that we should handle?
                                error_log("Error: $abfrage <br>".mysqli_error($verbindungi));
				die("Error: $abfrage <br>".mysqli_error($verbindungi));
			}
		}
            } else {
                print_debug_variable(["No insert", filter_input(INPUT_POST, 'auswahl_mitarbeiter', FILTER_VALIDATE_INT)]);
            }
            $vk = $auswahl_mitarbeiter;
            $abfrage = 'SELECT * FROM `Abwesenheit`
				WHERE `VK` = '.$vk.'
				ORDER BY `Beginn` ASC
				';
            $ergebnis = mysqli_query_verbose($abfrage);
            $number_of_rows = mysqli_num_rows($ergebnis);
            $tablebody = ''; $i = 1;
            while ($row = mysqli_fetch_object($ergebnis)) {
                $tablebody .= "\t\t\t<tr style='height: 1em;'>"
                        . "<form method=POST id='change_absence_entry_".$row->Beginn."'>"
                        . "\n\t\t\t\t";
                $tablebody .= "\t\t\t\t<td>\n\t\t\t\t\t<div id=beginn_out_".$row->Beginn.">";
                $tablebody .= date('d.m.Y', strtotime($row->Beginn))."</div>";
                $tablebody .= "<input id=beginn_in_".$row->Beginn." style='display: none;' type=date name='beginn' value=".date('Y-m-d', strtotime($row->Beginn))." form='change_absence_entry_".$row->Beginn."'> ";
                $tablebody .= "<input id=beginn_in_old_".$row->Beginn." style='display: none;' type=date name='beginn_old' value=".date('Y-m-d', strtotime($row->Beginn))." form='change_absence_entry_".$row->Beginn."'> ";
                $tablebody .= "\n\t\t\t\t</td>\n";
                $tablebody .= "\t\t\t\t<td>\n\t\t\t\t\t<div id=ende_out_".$row->Beginn." form='change_absence_entry_".$row->Beginn."'>";
                $tablebody .= date('d.m.Y', strtotime($row->Ende))."</div>";
                $tablebody .= "<input id=ende_in_".$row->Beginn." style='display: none;' type=date name='ende' value=".date('Y-m-d', strtotime($row->Ende))." form='change_absence_entry_".$row->Beginn."'> ";
                $tablebody .= "\n\t\t\t\t</td>\n";
                if ($i == $number_of_rows) {
                    $tablebody .= "\t\t\t\t<td id=letzterGrund><div id=grund_out_".$row->Beginn.">\n\t\t\t\t\t";
                } else {
                    $tablebody .= "\t\t\t\t<td>\n\t\t\t\t\t<div id=grund_out_".$row->Beginn.">";
                }
                $tablebody .= "$row->Grund"."</div>";
                $tablebody .= "<input id=grund_in_".$row->Beginn." style='display: none;' list='gruende' type=text name='grund' value='".$row->Grund."' form='change_absence_entry_".$row->Beginn."'> ";
                $tablebody .= "\n\t\t\t\t</td>\n";
                $tablebody .= "\t\t\t\t<td>\n\t\t\t\t\t";
                $tablebody .= "$row->Tage";
                $tablebody .= "\n\t\t\t\t</td>\n";
                $tablebody .= "\t\t\t\t<td style='font-size: 1em; height: 1em'>\n"
                            . "\t\t\t\t\t<input hidden name='auswahl_mitarbeiter' value='$vk' form='change_absence_entry_".$row->Beginn."'>\n"
                            . "\t\t\t\t\t<button type=submit id=delete_".$row->Beginn." class='button_small delete_button' title='Diese Zeile löschen' name=command value=delete onclick='return confirmDelete()'>\n"
                                . "\t\t\t\t\t\t<img src='img/delete.png' alt='Diese Zeile löschen'>\n"
                            . "\t\t\t\t\t</button>\n"
                            . "\t\t\t\t\t<button type=button id=cancel_".$row->Beginn." class='button_small' title='Bearbeitung abbrechen' onclick='return cancelEdit(\"".$row->Beginn."\")' style='display: none; border-radius: 32px; background-color: transparent;'>\n"
                                . "\t\t\t\t\t\t<img src='img/delete.png' alt='Bearbeitung abbrechen'>\n"
                            . "\t\t\t\t\t</button>\n"
                            . "\t\t\t\t\t<button type=button id=edit_".$row->Beginn." class='button_small edit_button' title='Diese Zeile bearbeiten' name=command onclick='showEdit(\"".$row->Beginn."\")'>\n"
                                . "\t\t\t\t\t\t<img src='img/pencil-pictogram.svg' alt='Diese Zeile bearbeiten'>\n"
                            . "\t\t\t\t\t</button>\n"
                            . "\t\t\t\t\t<button type='submit' id='save_".$row->Beginn."' class='button_small' title='Veränderungen dieser Zeile speichern' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
                                . "\t\t\t\t\t\t<img src='img/save.png' alt='Veränderungen dieser Zeile speichern'>\n"
                            . "\t\t\t\t\t</button>\n"
                        . "";
                $tablebody .= "\n\t\t\t\t</td>\n";
                $tablebody .= "\t\t\t</form>\n"
                        . "\t\t\t</tr>\n";
                ++$i;
            }
            $abfrage = 'SELECT DISTINCT `Grund` FROM `Abwesenheit` ORDER BY `Grund` ASC';
            $ergebnis = mysqli_query_verbose($abfrage);
            $datalist = "<datalist id='gruende'>\n";
            while ($row = mysqli_fetch_object($ergebnis)) {
                $datalist .= "\t<option value='$row->Grund'>\n";
            }
            $datalist .= "</datalist>\n";

//Here beginns the output:
require 'head.php';
require 'navigation.php';

echo "<div id=main-area>\n";

require_once 'src/php/build-warning-messages.php';
echo build_warning_messages($Fehlermeldung, $Warnmeldung);

if (isset($Feiertagsmeldung)) {
    echo "\t\t<div class=error_container>\n";
    echo "\t\t\t<div class=warningmsg>\n<H3>Die folgenden Feiertage werden nicht auf die Abwesenheit angerechnet:</H3>";
    foreach ($Feiertagsmeldung as $feiertag) {
        echo "\t\t\t\t<p>" . $feiertag . "</p>\n";
    }
    echo "\t\t\t</div>\n";
    echo "\t\t</div>\n";
}
echo "\t\t<form method=POST>\n";
echo "\t\t\t<select name=auswahl_mitarbeiter class='no-print large' onChange='document.getElementById(\"submitAuswahlMitarbeiter\").click()'>\n";
foreach ($Mitarbeiter as $vk => $name)
{
	if($vk == $auswahl_mitarbeiter)
	{
		echo "\t\t\t\t<option value=$vk selected>".$vk." ".$Mitarbeiter[$vk]."</option>\n";
	}
	else {
		echo "\t\t\t\t<option value=$vk>".$vk." ".$Mitarbeiter[$vk]."</option>\n";
	}
}
echo "\t\t\t</select>\n";
$submit_button = "\t\t\t<input hidden type=submit value=Auswahl name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n"; echo $submit_button; //name ist für die $_POST-Variable relevant. Die id wird für den onChange-Event im select benötigt.
echo "\t\t</form>\n";
echo "\t\t\t<H1 class='only-print'>".$Mitarbeiter[$auswahl_mitarbeiter]."</H1>\n";
echo "<a class=no-print href='abwesenheit-out.php?auswahl_mitarbeiter=$auswahl_mitarbeiter'>[Lesen]</a>";
echo "\t\t\n";
            echo "\t\t<table id=absence_table>\n";
//Überschrift
            echo "\t\t\t<thead>\n"
                ."\t\t\t<tr>\n"
                ."\t\t\t\t<th>\n"
                ."\t\t\t\t\tBeginn\n"
                ."\t\t\t\t</th>\n"
                ."\t\t\t\t<th>\n"
                ."\t\t\t\t\tEnde\n"
                ."\t\t\t\t</th>\n"
                ."\t\t\t\t<th>\n"
                ."\t\t\t\t\tGrund\n"
                ."\t\t\t\t</th>\n"
                ."\t\t\t\t<th>\n"
                ."\t\t\t\t\tTage\n"
                ."\t\t\t\t</th>\n"
                ."\t\t\t\t<th>\n"
                . ""
                ."\t\t\t\t</th>\n"
                ."\t\t\t</tr>\n"
                ."\t\t\t</thead>\n";
//Ausgabe
            echo "\t\t\t<tbody>\n"
                . "$tablebody"
                . "\t\t\t</tbody>\n";
            //echo "\t\t</form>\n";
//Eingabe. Der Saldo wird natürlich berechnet.
            echo "\t\t\t<tfoot>"
                . "\t\t\n";
            echo "";
            echo "\t\t\t<tr class=no-print id=input_line_new>\n"
                    . "\t\t\t<form method=POST id='new_absence_entry'>\n";
            echo "\t\t\t\t<td>\n\t\t\t\t\t"
                    . "\t\t\t\t\t<input type=hidden name=auswahl_mitarbeiter value=$auswahl_mitarbeiter form='new_absence_entry'>\n";
            echo "\t\t\t\t\t<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=beginn name=beginn value=".date("Y-m-d")." form='new_absence_entry'>";
            echo "\n\t\t\t\t</td>\n";
            echo "\t\t\t\t<td>\n\t\t\t\t\t";
            echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=ende name=ende value=".date("Y-m-d")." form='new_absence_entry'>";
            echo "\n\t\t\t\t</td>\n";
            echo "\t\t\t\t<td>\n\t\t\t\t\t";
            echo "<input list='gruende' name=grund form='new_absence_entry'>";
            echo "$datalist";
            echo "\n\t\t\t\t</td>\n";
            echo "\t\t\t\t<td id=tage title='Feiertage werden anschließend automatisch vom Server abgezogen.'>\n\t\t\t\t\t";
            echo "1";
            echo "\n\t\t\t\t</td>\n";
            echo "\t\t\t\t<td>\n";
            echo "";
            echo "\n\t\t\t\t</td>\n";
            echo "\n\t\t\t</tr>\n";
            echo "\n\t\t\t<tr style='display: none; background-color: #BDE682;' id=warning_message_tr>\n";
            echo "\t\t\t\t<td id=warning_message_td colspan='5'>\n\t\t\t\t\t";
            echo "\n\t\t\t\t</td>\n";
            echo "\t\t\t</form>\n"
                . "\t\t\t</tr>\n"
                . "\t\t\t</tfoot>";
            echo "\t\t</table>\n";
            echo "<input type=submit id=save_new class=no-print name=submitStunden value='Eintragen' form='new_absence_entry'>";
//echo "<pre>"; var_dump($_POST); echo "</pre>";
            echo "</div>\n";
						require 'contact-form.php';
        ?>
	</body>
</html>
