<?php

#Diese Seite wird den kompletten Dienstplan einer Woche  anzeigen.
require "default.php";
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . "db-lesen-abwesenheit.php";
/*
 * TODO: Seems not to work on 26.03.2018, with emergency service on the first day with an empty roster for the whole week.
 */
$tage = 7; //Dies ist eine Wochenansicht mit Wochenende

$date_sql_user_input = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$datum = general_calculations::get_first_day_of_week($date_sql_user_input);
create_cookie('datum', $datum, 1);
$date_sql = $datum;

$mandant = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min($List_of_branch_objects));
create_cookie('mandant', $mandant, 30);

if ((filter_has_var(INPUT_POST, 'submit_approval') or filter_has_var(INPUT_POST, 'submit_disapproval')) && count($Dienstplan) > 0 && $session->user_has_privilege('approve_roster')) {
    user_input::old_write_approval_to_database($mandant);
}
if (filter_has_var(INPUT_POST, 'Dienstplan')) {
    $Dienstplan = user_input::old_get_Roster_from_POST_secure();
    if (filter_has_var(INPUT_POST, 'submit_roster') && $session->user_has_privilege('create_roster') && count($Dienstplan) > 0) {
        user_input::old_roster_write_user_input_to_database();
    }
}
//Hole eine Liste aller Mitarbeiter
require 'db-lesen-mitarbeiter.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/read_roster_array_from_db.php';
$Dienstplan = read_roster_array_from_db($datum, $tage, $mandant);


//end($List_of_employees); $VKmax=key($List_of_employees); reset($List_of_employees); //Wir suchen nach der höchsten VK-Nummer VKmax.
$VKmax = max(array_keys($List_of_employees));
$VKcount = calculate_VKcount($Dienstplan);




//Produziere die Ausgabe
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('create_roster')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Dienstplänen fehlt. Bitte wenden Sie sich an einen Administrator."]);
    //die("Die notwendige Berechtigung zum Erstellen von Dienstplänen fehlt. Bitte wenden Sie sich an einen Administrator.");
    die();
}

echo gettext("calendar week") . strftime(' %V', strtotime($datum)) . "<br>\n";
//Support for various branch clients.
echo build_select_branch($mandant, $date_sql);
echo "<form id=myform method=post>\n";
echo "<div class=no-print>";
echo $backward_button_week_img;
echo $forward_button_week_img;
echo "$submit_button_img";
echo "<br><br>\n";
echo "<div id=wochenAuswahl><input name=woche type=date id=date_chooser_input class='datepicker' value=" . date('Y-m-d', strtotime($datum)) . ">";
echo "<input type=submit name=wochenAuswahl value=Anzeigen></div>";
echo "<br><br>";
if ($session->user_has_privilege('approve_roster')) {
// TODO: The button should be inactive when the approval already was done.
    echo "$submit_approval_button_img";
    echo "$submit_disapproval_button_img";
    echo "<br><br>\n";
}
echo "\t\t\t\t<a href='woche-out.php?datum=" . $datum . "' class=no-print>[" . gettext("Read") . "]</a>\n";
echo "<br><br>\n";
echo "</div>";

echo "\t<table>\n";
echo "\t\t\t\t\t<thead>\n";
echo "\t\t\t<tr>\n";
for ($i = 0; $i < count($Dienstplan); $i++) {//Datum
    $datum = ($Dienstplan[$i]['Datum'][0]);
    $date_unix = strtotime($datum);
    $zeile = "";
    echo "\t\t\t\t<td><a href='tag-in.php?datum=" . $Dienstplan[$i]["Datum"][0] . "'>";
    $zeile .= "<input type=hidden size=2 name=Dienstplan[" . $i . "][Datum][0] value=" . $Dienstplan[$i]["Datum"][0] . ">";
    $zeile .= strftime('%d.%m.', strtotime($Dienstplan[$i]["Datum"][0]));
    echo $zeile;
    $holiday = holidays::is_holiday($date_unix);
    if (FALSE !== $holiday) {
        echo " " . $holiday . " ";
    }
    require 'db-lesen-notdienst.php';
    if (isset($notdienst)) {
        echo "<br> NOTDIENST ";
    }

    echo "<br>\n"; //Wochentag
    $zeile = "";
    $zeile .= strftime('%A', strtotime($Dienstplan[$i]["Datum"][0]));
    echo $zeile;
    echo "</a></td>\n";
}
echo "\t\t\t\t\t</tr></thead><tbody><tr>";

for ($j = 0; $j < $VKcount; $j++) {
    if (FALSE !== $holiday && !isset($notdienst)) {
        break 1;
    }
    echo "\t\t\t</tr><tr>\n";
    for ($i = 0; $i < count($Dienstplan); $i++) {//Mitarbeiter
        $zeile = "";
        echo "\t\t\t\t<td>";
        $zeile .= "<select name=Dienstplan[" . $i . "][VK][" . $j . "] tabindex=" . (($i * $VKcount * 5) + ($j * 5) + 1) . "><option>";
        $zeile .= "</option>";
        foreach ($List_of_employees as $k => $mitarbeiter) {
            if (isset($Dienstplan[$i]["VK"][$j])) {
                if (isset($List_of_employees[$k]) and $Dienstplan[$i]["VK"][$j] != $k) { //Dieser Ausdruck dient nur dazu, dass der vorgesehene  Mitarbeiter nicht zwei mal in der Liste auftaucht.
                    $zeile .= "<option value=" . $k . ">" . $k . " " . $List_of_employees[$k] . "</option>";
                } else {
                    $zeile .= "<option value=" . $k . " selected>" . $k . " " . $List_of_employees[$k] . "</option>";
                }
            } elseif (isset($List_of_employees[$k])) {
                $zeile .= "<option value=" . $k . ">" . $k . " " . $List_of_employees[$k] . "</option>";
            }
        }
        $zeile .= "</select>";
        //Dienstbeginn
        $zeile .= " <input type=time size=1 name=Dienstplan[" . $i . "][Dienstbeginn][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 2 ) . " value='";
        if (isset($Dienstplan[$i]["VK"][$j])) {
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
        }
        $zeile .= "'> bis <input type=time size=1 name=Dienstplan[" . $i . "][Dienstende][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 3 ) . " value='";
        //Dienstende
        if (isset($Dienstplan[$i]["VK"][$j])) {
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Dienstende"][$j]));
        }
        $zeile .= "'>";
        echo $zeile;

        echo "\t\t\t\t</td>\n";
    }
    echo "\t\t\t</tr><tr>\n";
    for ($i = 0; $i < count($Dienstplan); $i++) {//Mittagspause
        $zeile = "";
        echo "\t\t\t\t<td>";
        $zeile .= "<div class='no-print kommentar_ersatz' style=display:inline><a onclick=unhide_kommentar() title='Kommentar anzeigen'>K+</a></div>";
        $zeile .= "<div class='no-print kommentar_input' style=display:none><a onclick=rehide_kommentar() title='Kommentar ausblenden'>K-</a></div>";
        $zeile .= " " . gettext("break") . ": <input type=time size=1 name=Dienstplan[" . $i . "][Mittagsbeginn][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 4 ) . " value='";
        if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0) {
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
        }
        $zeile .= "'> bis <input type=time size=1 name=Dienstplan[" . $i . "][Mittagsende][" . $j . "] tabindex=" . ($i * $VKcount * 5 + $j * 5 + 5 ) . " value='";
        if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0) {
            $zeile .= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
        }
        $zeile .= "'>";
        $zeile .= "<div class=kommentar_input style=display:none><br>Kommentar: <input type=text name=Dienstplan[" . $i . "][Kommentar][" . $j . "] value=\"";
        if (isset($Dienstplan[$i]["Kommentar"][$j])) {
            $zeile .= $Dienstplan[$i]["Kommentar"][$j];
        }
        $zeile .= "\"></div>";

        echo $zeile;
        echo "</td>";
    }
}
echo "\t\t\t</tr>\n";
echo "\t\t\t\t\t</tbody>\n";
//echo "\t\t\t\t</div>\n";
echo "\t\t\t\t\t<tfoot>"
//. "<tr class=page-break></tr>"
 . "\n";

//Wir werfen einen Blick in den Urlaubsplan und schauen, ob alle da sind.
echo "\t\t\t<tr>\n";
for ($i = 0; $i < count($Dienstplan); $i++) {
    $Abwesende = db_lesen_abwesenheit($datum);
    echo build_html_roster_views::build_absentees_column($Abwesende);
}
echo "\t\t</tr>\n";

echo "\t</table>\n";
echo "</form>\n";

//Hier beginnt die Fehlerausgabe. Es werden alle Fehler angezeigt, die wir in $Fehlermeldung gesammelt haben.

echo build_warning_messages($Fehlermeldung, $Warnmeldung);

require 'contact-form.php';
echo "</body>";
