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
$Roster = roster::read_roster_from_database($branch_id, $date_sql);
if ((filter_has_var(INPUT_POST, 'submit_approval') or filter_has_var(INPUT_POST, 'submit_disapproval')) && count($Roster) > 0 && $session->user_has_privilege('approve_roster')) {
    user_input::old_write_approval_to_database($branch_id, $Roster);
}
$Principle_roster = roster::read_principle_roster_from_database($branch_id, $date_sql);
if (TRUE === $Roster[$date_unix]['empty'] AND NULL !== $Principle_roster AND FALSE === $holiday) { //No plans on Saturday, Sunday and holidays.
    //Wir wollen eine automatische Dienstplanfindung beginnen.
    //Mal sehen, wie viel die Maschine selbst gestalten kann.
    $Fehlermeldung[] = "Kein Plan in der Datenbank, dies ist ein Vorschlag!";
    $Roster = $Principle_roster;
}
if ("7" !== date('N', $date_unix) and ! holidays::is_holiday($date_unix)) {
    $examine_roster = new examine_roster($Roster, $date_unix, $branch_id);
    $examine_roster->check_for_overlap($date_sql, $Fehlermeldung);
    $examine_roster->check_for_sufficient_employee_count($Fehlermeldung, 2);
    $examine_roster->check_for_sufficient_goods_receipt_count($Warnmeldung);
    $examine_roster->check_for_sufficient_qualified_pharmacist_count($Fehlermeldung);
}

if (FALSE !== pharmacy_emergency_service::having_emergency_service($date_sql)) {
    $Warnmeldung[] = "An den Notdienst denken!";
}




//end($List_of_employees); $VKmax=key($List_of_employees); reset($List_of_employees); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($List_of_employees));

//Wir schauen, on alle Anwesenden anwesend sind und alle Kranken und Siechenden im Urlaub.
examine_attendance::check_for_absent_employees($Roster, $Principle_roster, $Abwesende, $date_unix, $Warnmeldung);
examine_attendance::check_for_attendant_absentees($Roster, $date_sql, $Abwesende, $Fehlermeldung);



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
//TODO: This loop probably is not necessary. Is there any case where $i ist not 0?
$zeile = "";
echo "<td>";
$zeile .= "<input type=hidden name=datum value=" . $date_sql . ">";
$zeile .= "<input type=hidden name=mandant value=" . htmlentities($branch_id) . ">";
$zeile .= strftime('%d.%m. ', $date_unix);
echo $zeile;
//Wochentag
$zeile = "";
$zeile .= strftime('%A ', $date_unix);
echo $zeile;
if (FALSE !== $holiday) {
    echo " " . $holiday . " ";
}
$having_emergency_service = pharmacy_emergency_service::having_emergency_service($date_sql);
if (isset($having_emergency_service['branch_id'])) {
    if (isset($List_of_employees[$notdienst['employee_id']])) {
        echo "<br>NOTDIENST<br>" . $List_of_employees[$notdienst['employee_id']] . " / " . $List_of_branch_objects[$notdienst['branch_id']]->name;
    } else {
        echo "<br>NOTDIENST<br>??? / " . $List_of_branch_objects[$notdienst['branch_id']]->name;
    }
}
echo "</td>\n";
echo "</tr>\n";
$max_employee_count = roster::calculate_max_employee_count($Roster);
for ($table_input_row_iterator = 0; $table_input_row_iterator < $max_employee_count; $table_input_row_iterator++) {
    echo "<tr>\n";
    foreach (array_keys($Roster) as $day_iterator) {
        echo build_html_roster_views::build_roster_input_row($Roster, $day_iterator, $table_input_row_iterator, $max_employee_count, $date_unix, $branch_id);
    }
    echo "</tr>\n";
}


//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
echo build_html_roster_views::build_absentees_row($Abwesende);
echo "</table>\n";
echo "</form>\n";


if (!empty($Roster)) {
    echo "<div class=image>\n";
    require_once 'image_dienstplan.php';
    echo draw_image_dienstplan($Roster);
    echo "<br>\n";
    require_once 'image_histogramm.php';
    echo roster_image_histogramm::draw_image_histogramm($Roster, $branch_id, $examine_roster->Anwesende, $date_sql);
    echo "</div>\n";
}
echo "</div>";

require 'contact-form.php';

echo "</body>\n";
echo "</html>";
?>
