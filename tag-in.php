<?php

#Diese Seite wird den kompletten Dienstplan eines einzelnen Tages anzeigen.
require 'default.php';
$tage = 1; //Dies ist eine Tagesansicht für einen einzelnen Tag.
$tag = 0;


//$employee_id = user_input::get_variable_from_any_input("employee_id", FILTER_SANITIZE_NUMBER_INT);
//$year = user_input::get_variable_from_any_input("year", FILTER_SANITIZE_NUMBER_INT);
$branch_id = user_input::get_variable_from_any_input("mandant", FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
create_cookie("mandant", $branch_id, 30);

$date_sql = user_input::get_variable_from_any_input("datum", FILTER_SANITIZE_STRING, date('Y-m-d'));
create_cookie("datum", $date_sql, 0.5);
$date_unix = strtotime($date_sql);

if (filter_has_var(INPUT_POST, 'Roster')) {
    $Roster = user_input::get_Roster_from_POST_secure();
    if (filter_has_var(INPUT_POST, 'submit_roster') && $session->user_has_privilege('create_roster')) {
        user_input::old_roster_write_user_input_to_database($Roster, $branch_id);
    }
}

//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/read_roster_array_from_db.php';
require_once 'db-lesen-abwesenheit.php';
$Abwesende = db_lesen_abwesenheit($date_sql);
$holiday = holidays::is_holiday($date_unix);
$Dienstplan = read_roster_array_from_db($date_sql, $tage, $branch_id);
$Roster = roster::read_roster_from_database($branch_id, $date_sql);
if ((filter_has_var(INPUT_POST, 'submit_approval') or filter_has_var(INPUT_POST, 'submit_disapproval')) && count($Dienstplan) > 0 && $session->user_has_privilege('approve_roster')) {
    user_input::old_write_approval_to_database($branch_id, $Roster);
}
require_once 'plane-tag-grundplan.php';
$Principle_roster_old = get_principle_roster($date_sql, $branch_id, $tag, $tage);
$Principle_roster = roster::read_principle_roster_from_database($branch_id, $date_sql);
if (array_sum($Dienstplan[0]['VK']) <= 1 AND empty($Dienstplan[0]['VK'][0]) AND NULL !== $Principle_roster_old AND FALSE === $holiday) { //No plans on Saturday, Sunday and holidays.
    //Wir wollen eine automatische Dienstplanfindung beginnen.
    //Mal sehen, wie viel die Maschine selbst gestalten kann.
    $Fehlermeldung[] = "Kein Plan in der Datenbank, dies ist ein Vorschlag!";
    //sort_roster_array($Principle_roster_old);
    $Dienstplan = determine_lunch_breaks($Principle_roster_old, $tag);
    roster::determine_lunch_breaks($Principle_roster);
    $Roster = $Principle_roster;
}
if ((array_sum($Dienstplan[0]['VK']) > 1 OR ! empty($Dienstplan[0]['VK'][0]))
        and "7" !== date('N', $date_unix)
        and ! holidays::is_holiday($date_unix)) {
    require 'pruefe-dienstplan.php';
    examine_duty_roster($Roster, $date_unix, $branch_id);
}
$roster_first_key = min(array_keys($Dienstplan[$tag]['Datum']));

require 'db-lesen-notdienst.php';
if (isset($notdienst['mandant'])) {
    $Warnmeldung[] = "An den Notdienst denken!";
}




//Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
//$VKcount=count($List_of_employees);
$VKcount = calculate_VKcount($Dienstplan);

//end($List_of_employees); $VKmax=key($List_of_employees); reset($List_of_employees); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($List_of_employees));

//Wir schauen, on alle Anwesenden anwesend sind und alle Kranken und Siechenden im Urlaub.
require 'pruefe-abwesenheit.php';




//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_roster');

//Hier beginnt die Normale Ausgabe.
echo "<div id=main-area>\n";

//Here we put the output of errors and warnings. We display the errors, which we collected in $Fehlermeldung and $Warnmeldung:
echo build_warning_messages($Fehlermeldung, $Warnmeldung);

echo "" . strftime(gettext("calendar week") . ' %V', $date_unix) . "<br>";
echo "<div class=only-print><b>" . $List_of_branch_objects[$branch_id]->name . "</b></div><br>\n";
echo build_select_branch($branch_id, $date_sql);


echo "<div id=navigation_elements>";
echo build_html_navigation_elements::build_button_day_backward($date_unix);
echo build_html_navigation_elements::build_button_day_forward($date_unix);
echo build_html_navigation_elements::build_button_submit('roster_form');
if ($session->user_has_privilege('approve_roster')) {
    echo build_html_navigation_elements::build_button_approval();
    echo build_html_navigation_elements::build_button_disapproval();
}

echo "<a href='tag-out.php?datum=" . $date_sql . "'>[" . gettext("Read") . "]</a>\n";
echo "</div>\n";
echo build_html_navigation_elements::build_input_date($date_sql);
echo "<form id='roster_form' method=post>\n";
echo "<table>\n";
echo "<tr>\n";
for ($i = 0; $i < count($Dienstplan); $i++) {//Datum
    //TODO: This loop probably is not necessary. Is there any case where $i ist not 0?
    $zeile = "";
    echo "<td>";
    $zeile .= "<input type=hidden name=Dienstplan[" . $i . "][Datum][0] value=" . $Dienstplan[$i]["Datum"][$roster_first_key] . ">";
    $zeile .= "<input type=hidden name=mandant value=" . htmlentities($branch_id) . ">";
    $zeile .= strftime('%d.%m. ', strtotime($Dienstplan[$i]["Datum"][$roster_first_key]));
    echo $zeile;
//Wochentag
    $zeile = "";
    $zeile .= strftime('%A ', strtotime($Dienstplan[$i]["Datum"][$roster_first_key]));
    echo $zeile;
    if (FALSE !== $holiday) {
        echo " " . $holiday . " ";
    }
    require 'db-lesen-notdienst.php';
    if (isset($notdienst['mandant'])) {
        if (isset($List_of_employees[$notdienst['vk']])) {
            echo "<br>NOTDIENST<br>" . $List_of_employees[$notdienst['vk']] . " / " . $List_of_branch_objects[$notdienst['mandant']]->name;
        } else {
            echo "<br>NOTDIENST<br>??? / " . $List_of_branch_objects[$notdienst['mandant']]->name;
        }
    }
    echo "</td>\n";
}
echo "</tr>\n";
for ($j = 0; $j < $VKcount; $j++) {
    echo "<tr>\n";
    foreach (array_keys($Roster) as $day_iterator) {
        echo build_html_roster_views::build_roster_input_row($Roster, $day_iterator, $j, $VKcount, $date_unix, $branch_id);
    }
    echo "</tr>\n";
}


//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
if (isset($Abwesende)) {
    echo build_html_roster_views::build_absentees_row($Abwesende);
}
echo "</table>\n";
echo "</form>\n";


if (!empty($Dienstplan[0]["Dienstbeginn"])) {
    echo "<div class=image>\n";
    require_once 'image_dienstplan.php';
    $svg_image_dienstplan = draw_image_dienstplan($Dienstplan);
    echo $svg_image_dienstplan;
    echo "<br>\n";
    require_once 'image_histogramm.php';
    $svg_image_histogramm = roster_image_histogramm::draw_image_histogramm($Roster, $branch_id, $Anwesende, $date_sql);
    echo $svg_image_histogramm;
    echo "</div>\n";
}
echo "</div>";

require 'contact-form.php';

echo "</body>\n";
echo "</html>";
?>
