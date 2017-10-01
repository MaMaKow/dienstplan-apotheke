<?php
require 'default.php';
    //Hole eine Liste aller Mitarbeiter
            require 'db-lesen-mitarbeiter.php';
            //Hole eine Liste aller Mandanten (Filialen)
            require 'db-lesen-mandant.php';
            if (filter_has_var(INPUT_POST, 'employee_id')) {
                $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
            } elseif (filter_has_var(INPUT_GET, 'employee_id')) {
                $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);
            } elseif (filter_has_var(INPUT_COOKIE, 'employee_id')) {
                $employee_id = filter_input(INPUT_COOKIE, 'employee_id', FILTER_VALIDATE_INT);
            } else {
                $employee_id = 1;
            }

            if (isset($employee_id)) {
                create_cookie('employee_id', $employee_id, 30);
            }

            //Wir löschen Datensätze, wenn dies befohlen wird.
            if ($command = filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING) and 'delete' === $command) {
                $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
                $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING);
                $abfrage = "DELETE FROM `absence`
                	WHERE `employee_id` = '$employee_id' AND `start` = '$beginn'";
                $ergebnis = mysqli_query_verbose($abfrage);
                $employee_id = $employee_id;
            }

            //We create new entries or edit old entries. (Empty values are not accepted.)
            if ((filter_has_var(INPUT_POST, 'submitStunden') or (filter_has_var(INPUT_POST, 'command') and 'replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING)))
                    and $beginn = filter_input(INPUT_POST, 'beginn', FILTER_SANITIZE_STRING)                    
                    and $ende = filter_input(INPUT_POST, 'ende', FILTER_SANITIZE_STRING)
                    and $grund = filter_input(INPUT_POST, 'grund', FILTER_SANITIZE_STRING)
                    ){
                $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
                if ($employee_id === FALSE){
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
                        $Feiertagsmeldung[] = htmlentities("$feiertag ($datum)<br>\n");
                        $tage--;
                    }
                }
                //var_export($tage);
                if ('replace' === filter_input(INPUT_POST, 'command', FILTER_SANITIZE_STRING)){
                    $beginn_old = filter_input(INPUT_POST, 'beginn_old', FILTER_SANITIZE_STRING);
                    $abfrage = "DELETE FROM `absence` WHERE `employee_id` = '$employee_id' AND `start` = '$beginn_old'"; 
                    //echo "$abfrage<br>\n";
                                    $ergebnis = mysqli_query_verbose($abfrage);
                }
                $approval = "approved"; //TODO: There will be a time to handle cases of non-approved holidays!
                $abfrage = "INSERT INTO `absence` "
                        . "(employee_id, start, end, days, reason, user, approval) "
                        . "VALUES ('$employee_id', '$beginn', '$ende', '$tage', '$grund', '$user', '$approval')";
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
            } 
            $employee_id = $employee_id;
            $abfrage = 'SELECT * FROM `absence`
				WHERE `employee_id` = '.$employee_id.'
				ORDER BY `start` ASC
				';
            $ergebnis = mysqli_query_verbose($abfrage);
            $number_of_rows = mysqli_num_rows($ergebnis);
            $tablebody = ''; $i = 1;
            while ($row = mysqli_fetch_object($ergebnis)) {
                $tablebody .= "\t\t\t<tr style='height: 1em;'>"
                        . "<form method=POST id='change_absence_entry_".$row->start."'>"
                        . "\n\t\t\t\t";
                $tablebody .= "\t\t\t\t<td>\n\t\t\t\t\t<div id=beginn_out_".$row->start.">";
                $tablebody .= date('d.m.Y', strtotime($row->start))."</div>";
                $tablebody .= "<input id=beginn_in_".$row->start." style='display: none;' type=date name='beginn' value=".date('Y-m-d', strtotime($row->start))." form='change_absence_entry_".$row->start."'> ";
                $tablebody .= "<input id=beginn_in_old_".$row->start." style='display: none;' type=date name='beginn_old' value=".date('Y-m-d', strtotime($row->start))." form='change_absence_entry_".$row->start."'> ";
                $tablebody .= "\n\t\t\t\t</td>\n";
                $tablebody .= "\t\t\t\t<td>\n\t\t\t\t\t<div id=ende_out_".$row->start." form='change_absence_entry_".$row->start."'>";
                $tablebody .= date('d.m.Y', strtotime($row->end))."</div>";
                $tablebody .= "<input id=ende_in_".$row->start." style='display: none;' type=date name='ende' value=".date('Y-m-d', strtotime($row->end))." form='change_absence_entry_".$row->start."'> ";
                $tablebody .= "\n\t\t\t\t</td>\n";
                if ($i == $number_of_rows) {
                    $tablebody .= "\t\t\t\t<td id=letzterGrund><div id=grund_out_".$row->start.">\n\t\t\t\t\t";
                } else {
                    $tablebody .= "\t\t\t\t<td>\n\t\t\t\t\t<div id=grund_out_".$row->start.">";
                }
                $tablebody .= "$row->reason"."</div>";
                $tablebody .= "<input id=grund_in_".$row->start." style='display: none;' list='reasons' type=text name='grund' value='".$row->reason."' form='change_absence_entry_".$row->start."'> ";
                $tablebody .= "\n\t\t\t\t</td>\n";
                $tablebody .= "\t\t\t\t<td>\n\t\t\t\t\t";
                $tablebody .= "$row->days";
                $tablebody .= "\n\t\t\t\t</td>\n";
                $tablebody .= "\t\t\t\t<td style='font-size: 1em; height: 1em'>\n"
                            . "\t\t\t\t\t<input hidden name='employee_id' value='$employee_id' form='change_absence_entry_".$row->start."'>\n"
                            . "\t\t\t\t\t<button type=submit id=delete_".$row->start." class='button_small delete_button' title='Diese Zeile löschen' name=command value=delete onclick='return confirmDelete()'>\n"
                                . "\t\t\t\t\t\t<img src='img/delete.png' alt='Diese Zeile löschen'>\n"
                            . "\t\t\t\t\t</button>\n"
                            . "\t\t\t\t\t<button type=button id=cancel_".$row->start." class='button_small' title='Bearbeitung abbrechen' onclick='return cancelEdit(\"".$row->start."\")' style='display: none; border-radius: 32px; background-color: transparent;'>\n"
                                . "\t\t\t\t\t\t<img src='img/delete.png' alt='Bearbeitung abbrechen'>\n"
                            . "\t\t\t\t\t</button>\n"
                            . "\t\t\t\t\t<button type=button id=edit_".$row->start." class='button_small edit_button' title='Diese Zeile bearbeiten' name=command onclick='showEdit(\"".$row->start."\")'>\n"
                                . "\t\t\t\t\t\t<img src='img/pencil-pictogram.svg' alt='Diese Zeile bearbeiten'>\n"
                            . "\t\t\t\t\t</button>\n"
                            . "\t\t\t\t\t<button type='submit' id='save_".$row->start."' class='button_small' title='Veränderungen dieser Zeile speichern' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
                                . "\t\t\t\t\t\t<img src='img/save.png' alt='Veränderungen dieser Zeile speichern'>\n"
                            . "\t\t\t\t\t</button>\n"
                        . "";
                $tablebody .= "\n\t\t\t\t</td>\n";
                $tablebody .= "\t\t\t</form>\n"
                        . "\t\t\t</tr>\n";
                ++$i;
            }
            $abfrage = "SELECT `reason` FROM `absence`  GROUP BY `reason` HAVING COUNT(*) > 3 ORDER BY `reason` ASC";
            $ergebnis = mysqli_query_verbose($abfrage);
            $datalist = "<datalist id='reasons'>\n";
            while ($row = mysqli_fetch_object($ergebnis)) {
                $datalist .= "\t<option value='$row->reason'>\n";
            }
            $datalist .= "</datalist>\n";

//Here beginns the output:
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
if(!$session->user_has_privilege('create_absence')){
    echo build_warning_messages("",["Die notwendige Berechtigung zum Erstellen von Abwesenheiten fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}

echo "<div id=main-area>\n";

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
echo build_select_employee($employee_id, $Mitarbeiter);

echo "<a class=no-print href='abwesenheit-out.php?employee_id=$employee_id'>[" . gettext("Read") . "]</a>";
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
                    . "\t\t\t\t\t<input type=hidden name=employee_id value=$employee_id form='new_absence_entry'>\n";
            echo "\t\t\t\t\t<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=beginn name=beginn value=".date("Y-m-d")." form='new_absence_entry'>";
            echo "\n\t\t\t\t</td>\n";
            echo "\t\t\t\t<td>\n\t\t\t\t\t";
            echo "<input type=date class=datepicker onchange=updateTage() onblur=checkUpdateTage() id=ende name=ende value=".date("Y-m-d")." form='new_absence_entry'>";
            echo "\n\t\t\t\t</td>\n";
            echo "\t\t\t\t<td>\n\t\t\t\t\t";
            echo "<input list='reasons' name=grund form='new_absence_entry'>";
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
            echo "</div>\n";
						require 'contact-form.php';
        ?>
	</body>
</html>
