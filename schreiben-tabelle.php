<?php

function schreiben_tabelle($Dienstplan, $branch) {
    global $Mitarbeiter;
    global $config;
    global $Warnmeldung, $Fehlermeldung, $Overlay_message;
    $table_html = "";
    foreach ($Dienstplan as $Dienstplantag) {
        if (isset($Dienstplantag['VK'])) {
            $Plan_anzahl[] = (count($Dienstplantag['VK']));
        } else {
            $Plan_anzahl[] = 0;
        }
    }
    $plan_anzahl = max($Plan_anzahl); //Die Anzahl der Zeilen der Tabelle richtet sich nach dem Tag mit den meisten Einträgen.

    require_once 'plane-tag-grundplan.php';
    $roster_first_day_key = min(array_keys($Dienstplan));
    $roster_first_row_key = min(array_keys($Dienstplan[$roster_first_day_key]['Datum']));
    $roster_number_of_days = count(array_keys($Dienstplan));
    $date_sql = $Dienstplan[$roster_first_day_key]["Datum"][$roster_first_row_key];
    $Principle_roster = get_principle_roster($date_sql, $branch, $roster_first_day_key, $roster_number_of_days);

    for ($j = 0; $j < $plan_anzahl; $j++) {
        if (isset($feiertag) && !isset($notdienst)) {
            break 1;
        }
        $table_html .= "\t\t\t\t<tr>\n";
        for ($i = 0; $i < count($Dienstplan); $i++) {//Mitarbeiter
            //The following lines check for the state of approval.
            //Duty rosters have to be approved by the leader, before the staff can view them.
            $date_sql = $Dienstplan[$i]["Datum"][0];
            unset($approval);
            $abfrage = "SELECT state FROM `approval` WHERE date='$date_sql' AND branch='$branch'";
            $ergebnis = mysqli_query_verbose($abfrage);
            while ($row = mysqli_fetch_object($ergebnis)) {
                $approval = $row->state;
            }
            if (isset($approval)) {
                if ($approval == "approved") {
                    //Everything is fine.
                } elseif ($approval == "not_yet_approved" and TRUE === $config['hide_disapproved']) {
                    $Overlay_message[] = gettext("The roster has not been approved by the administration!");
                } elseif ($approval == "disapproved" and TRUE === $config['hide_disapproved']) {
                    $Overlay_message[] = gettext("The roster is still beeing revised!");
                }
            } else {
                $approval = "not_yet_approved";
                if (TRUE === $config['hide_disapproved']) {
                    $Overlay_message[] = gettext("Missing data in table `approval`");
                    // TODO: This is an Exception. It will occur when There is no approval, disapproval or other connected information in the approval table of the database.
                    //That might espacially occur during the development stage of this feature.
                }
            }
            $table_html .= "\t\t\t\t\t<td>";
            if ($approval == "approved" OR $config['hide_disapproved'] == false) {
                $zeile = "";
                if (isset($Dienstplan[$i]["VK"][$j]) && isset($Mitarbeiter[$Dienstplan[$i]["VK"][$j]])) {
                    $key_in_principle_roster = array_search($Dienstplan[$i]["VK"][$j], $Principle_roster[$i]["VK"]);
                    if (
                            FALSE !== $key_in_principle_roster
                            and
                            strtotime($Dienstplan[$i]["Dienstbeginn"][$j]) === strtotime($Principle_roster[$i]["Dienstbeginn"][$key_in_principle_roster])
                            and
                            strtotime($Dienstplan[$i]["Dienstende"][$j]) === strtotime($Principle_roster[$i]["Dienstende"][$key_in_principle_roster])
                    ) {
                        $emphasis_start = ""; //No emphasis
                        $emphasis_end = ""; //No emphasis
                    } else {
                        $emphasis_start = "<strong>"; //Significant emphasis
                        $emphasis_end = "</strong>"; //Significant emphasis
                    }
                    $zeile.="$emphasis_start<b><a href='mitarbeiter-out.php?"
                            . "datum=" . htmlentities($Dienstplan[$i]["Datum"][0])
                            . "&employee_id=" . htmlentities($Dienstplan[$i]["VK"][$j]) . "'>";
                    $zeile.=$Mitarbeiter[$Dienstplan[$i]["VK"][$j]];
                    $zeile.="</a></b> / ";
                    $zeile.=$Dienstplan[$i]["Stunden"][$j];
                    $zeile.=" ";
                }
                //Dienstbeginn
                $zeile.=" <br> ";
                if (isset($Dienstplan[$i]["VK"][$j])) {
                    $zeile.=strftime('%H:%M', strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
                }
                //Dienstende
                if (isset($Dienstplan[$i]["VK"][$j])) {
                    $zeile.=" - ";
                    $zeile.=strftime('%H:%M', strtotime($Dienstplan[$i]["Dienstende"][$j]));
                }
                $zeile.="";
                $table_html .= $zeile;
                //	Mittagspause
                $zeile = "";
                $table_html .= "\t\t\t\t<br>\n";
                if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0) {
                    $zeile.=" Pause: ";
                    $zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
                }
                if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0) {
                    $zeile.=" - ";
                    $zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
                }
                $zeile.="$emphasis_end";
                $table_html .= $zeile;
            }
            $table_html .= "</td>\n";
        }
    }
    $table_html .= "\t\t\t\t</tr>\n";
    return $table_html;
}
