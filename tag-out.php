<?php
require 'default.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . "/src/php/classes/build_html_roster_views.php";

/*
 * @var $mandant int the id of the active branch.
 * CAVE: Be aware, that the PEP part has its own branch id, coming from the cash register program
 */
$mandant = 1; //First branch is allways the default.
/*
 * @var $tage int Number of days to show.
 * This page will show the roster of one single day.
 */
$tage = 1;
//Get a list of all branches:
require 'db-lesen-mandant.php';

require_once 'db-lesen-abwesenheit.php';
require_once 'image_dienstplan.php';
require_once 'image_histogramm.php';

$datum = date('Y-m-d'); //This value will be overridden, if COOKIE, GET or POST contain another value."
require 'cookie-auswertung.php';
require 'get-auswertung.php';
require 'post-auswertung.php';
$date_sql = $datum;
if (isset($mandant)) {
    create_cookie("mandant", $mandant, 30);
}
if (isset($datum)) {
    create_cookie("datum", $datum, 0.5);
}

//The following lines check for the state of approval.
//Duty rosters have to be approved by the leader, before the staff can view them.
unset($approval);
$sql_query = "SELECT state FROM `approval` WHERE date='$datum' AND branch='$mandant'";
$result = mysqli_query_verbose($sql_query);
while ($row = mysqli_fetch_object($result)) {
    $approval = $row->state;
}
if (isset($approval)) {
    if ($approval == "approved") {
        //Everything is fine.
    } elseif ($approval == "not_yet_approved") {
        $Warnmeldung[] = gettext("The roster has not been approved by the administration!");
    } elseif ($approval == "disapproved") {
        $Warnmeldung[] = gettext("The roster is still beeing revised!");
    }
} else {
    $approval = "not_yet_approved";
    $Warnmeldung[] = gettext("Missing data in table `approval`");
    // TODO: This is an Exception. It will occur when There is no approval, disapproval or other connected information in the approval table of the database.
    //That might espacially occur during the development stage of this feature.
}


//Get a list of all employees:
require 'db-lesen-mitarbeiter.php';
//Read the roster data from the database:
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/read_roster_array_from_db.php';
$Dienstplan = read_roster_array_from_db($datum, $tage, $mandant);
foreach ($Dienstplan as $day => $roster) {
    $max_vk_count_in_rooster_days = max($max_vk_count_in_rooster_days, count($roster["VK"]));
}
$VKmax = max(array_keys($List_of_employees)); //The highest given employee_id
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';


echo "\t\t<div id=main-area>\n";
echo "\t\t\t<a href='woche-out.php?datum=" . $datum . "'>" . gettext("calendar week") . strftime(' %V', strtotime($datum)) . "</a><br>\n";


echo build_warning_messages($Fehlermeldung, $Warnmeldung);
echo build_select_branch($mandant, $date_sql);
echo "<div id=navigation_form_div class=no-print>\n";
echo "\t\t\t<form id=navigation_form method=post>\n";
echo "$backward_button_img";
echo "$forward_button_img";
echo "<br><br>\n";
echo "\t\t\t\t<a href='tag-in.php?datum=" . htmlentities($datum) . "'>[" . gettext("Edit") . "]</a>\n";
echo "<br><br>\n";
echo "\t\t\t\t\t<input name='date_sql' type='date' id='date_chooser_input' class='datepicker' value='" . date('Y-m-d', strtotime($datum)) . "'>\n";
echo "\t\t\t\t\t<input type=submit name=tagesAuswahl value=Anzeigen>\n";
echo "\t\t\t</form>\n";
echo "\t\t\t\t</div>\n";
echo "\t\t\t\t<div id=roster_table_div>\n";
echo "\t\t\t\t<table id=roster_table>\n";
echo "\t\t\t\t\t<tr>\n";
for ($i = 0; $i < count($Dienstplan); $i++) { //$i will be zero, beacause this is just one day.//Datum
    $zeile = "";
    echo "\t\t\t\t\t\t<td>\n";
    $zeile .= "<input type=hidden name=Dienstplan[" . $i . "][Datum][0] value=" . $Dienstplan[$i]["Datum"][0] . ">\n";
    $zeile .= "<input type=hidden name=mandant value=" . htmlentities($mandant) . ">\n";
    $zeile .= strftime('%d.%m. ', strtotime($Dienstplan[$i]["Datum"][0]));
    echo $zeile;
    //Weekday
    $zeile = "";
    $zeile .= strftime('%A', strtotime($Dienstplan[$i]["Datum"][0]));
    echo $zeile;
    require 'db-lesen-feiertag.php';
    if (isset($feiertag)) {
        echo " " . $feiertag . " ";
    }
    $Abwesende = db_lesen_abwesenheit($datum);
    require 'db-lesen-notdienst.php';
    if (isset($notdienst['mandant'])) {
        echo "<br>NOTDIENST<br>";
        if (isset($List_of_employees[$notdienst['vk']])) {
            echo $List_of_employees[$notdienst['vk']];
        } else {
            echo "???";
        }
        echo " / " . $Branch_name[$notdienst['mandant']];
    }
    echo "</td>\n";
}
if ($approval == "approved" OR $config['hide_disapproved'] == false) {
    for ($j = 0; $j < $max_vk_count_in_rooster_days; $j++) {
        //TODO The following line will prevent planning on hollidays. The problem is, that we might work emergency service on hollidays. And if the service starts on the day before, then the programm does not know here. But we have to be here until 8:00 AM.
        //if(isset($feiertag) && !isset($notdienst)){break 1;}
        echo "\t\t\t\t\t</tr><tr>\n";
        for ($i = 0; $i < count($Dienstplan); $i++) {//Employees
            if (isset($Dienstplan[$i]["VK"][$j]) && isset($List_of_employees[$Dienstplan[$i]["VK"][$j]])) {
                $zeile = "\t\t\t\t\t\t<td>";
                $zeile .= "<b><a href='mitarbeiter-out.php?"
                        . "datum=" . htmlentities($Dienstplan[$i]["Datum"][0])
                        . "&employee_id=" . htmlentities($Dienstplan[$i]["VK"][$j]) . "'>";
                $zeile .= htmlentities($Dienstplan[$i]["VK"][$j]) . " " . htmlentities($List_of_employees[$Dienstplan[$i]["VK"][$j]]);
                $zeile .= "</a></b><span> ";
                if (isset($Dienstplan[$i]["VK"][$j])) {
                    //beginning of duty
                    $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
                    $zeile .= " - ";
                    //end of duty
                    $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Dienstende"][$j]));
                }
                if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0) {
                    $zeile .= "\t\t\t\t\t</span><span class=roster_table_lunch_break_span>\n";
                    $zeile .= " " . gettext("break") . ": ";
                    $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
                    $zeile .= " - ";
                    $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
                }
                $zeile .= "</span>\n\t\t\t\t\t\t</td>\n";
                echo $zeile;
            }
        }
    }
    echo "\t\t\t\t\t</tr>\n";

    echo "\t\t\t\t\t<tr><td></td></tr>\n";
    require_once 'schreiben-tabelle.php';

    function build_branch_table_rows($mandant, $number_of_days) {
        global $Branch_name;
        $table_html = "";

        foreach (array_keys($Branch_name) as $branch_id) {
            if ($mandant == $branch_id) {
                continue 1;
            }
            $Filialplan[$branch_id] = read_roster_array_from_db($datum, $number_of_days, $branch_id, '[' . $mandant . ']'); //This function gets the roster of the branches.
            if (!empty(array_column($Filialplan[$branch_id], 'VK'))) { //array_column searches all days for some employee (VK)
                $table_html .= "<tr><td><br></td></tr>";
                $table_html .= "</tbody><tbody><tr><td colspan=" . htmlentities($number_of_days) . ">" . $Branch_short_name[$mandant] . " in " . $Branch_short_name[$branch_id] . "</td></tr>";
                $table_html .= schreiben_tabelle($Filialplan[$branch_id], $branch_id);
            }
        }
        return $table_html;
    }

    echo build_branch_table_rows($mandant, $tage);
    echo "<tr><td><br></td></tr>";
    if (isset($Abwesende)) {
        echo build_absentees_row($Abwesende);
    }
}
echo "\t\t\t\t\t</table>\n";
echo "\t\t\t\t</div>\n";

if (($approval == "approved" OR $config['hide_disapproved'] !== TRUE) AND ! empty($Dienstplan[0]["Dienstbeginn"])) {
    echo "\t\t\t<div id=roster_image_div class=image>\n";
    echo draw_image_dienstplan($Dienstplan);
    echo "<br>\n";
    echo "<br>\n";
    echo draw_image_histogramm($Dienstplan);
    echo "\t\t\t</div>\n";
}

echo "\t\t</div>\n";

require 'contact-form.php';
?>
</body>
</html>
