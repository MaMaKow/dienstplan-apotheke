<?php

/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * Build one table column for a weekly view
 *
 * used by: woche-out.php
 * @param $Absentees array expects an array of absent employees in the format array(employee_id => reason_for_absence)
 *
 * @return string HTML table column
 */

function build_absentees_column($Absentees) {
    global $List_of_employees;
    $text = "<td class='absentees_column'><b>" . gettext("Absentees") . "</b><br>";
    foreach ($Absentees as $employee_id => $reason) {
        $text .= $List_of_employees[$employee_id] . " (" . $reason . ")<br>";
    }
    $text .= "</td>\n";
    return $text;
}

/*
 * Build one table row for a daily view
 *
 * used by: tag-in.php
 * @param $Absentees array expects an array of absent employees in the format array((int)employee_id => (string)reason_for_absence)
 *
 * @return string HTML table row
 */

function build_absentees_row($Absentees) {
    $text = "<tr>";
    $text .= build_absentees_column($Absentees);
    $text .= "</tr>\n";
    return $text;
}

class build_html_roster_views {

    static function build_roster_input_row($Roster, $day_iterator, $roster_row_iterator, $maximum_number_of_rows, $date_unix) {
        //TODO: This function should be further atomized!
//Mitarbeiter
        $roster_input_row = "<td>\n";
        $roster_employee_id = $Roster[$day_iterator][$roster_row_iterator]->employee_id;
        $roster_input_row .= build_html_roster_views::build_roster_input_row_employee_select($roster_employee_id, $day_iterator, $roster_row_iterator, $maximum_number_of_rows);
        //Dienstbeginn
        $roster_input_row .= "<input type=hidden name=Dienstplan[" . $day_iterator . "][Datum][" . $roster_row_iterator . "] value=" . $Roster[$day_iterator][$roster_row_iterator]->date_sql_sql . ">\n";
        $roster_input_row .= "<input type=time size=5 class=Dienstplan_Dienstbeginn name=Dienstplan[" . $day_iterator . "][Dienstbeginn][" . $roster_row_iterator . "] id=Dienstplan[" . $day_iterator . "][Dienstbeginn][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 2 ) . " value='";
        $roster_input_row .= roster::get_duty_start_from_roster($Roster, $date_unix, $roster_row_iterator);
        //Dienstende
        $roster_input_row .= "'>\n bis \n<input type=time size=5 class=Dienstplan_Dienstende name=Dienstplan[" . $day_iterator . "][Dienstende][" . $roster_row_iterator . "] id=Dienstplan[" . $day_iterator . "][Dienstende][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 3 ) . " value='";
        $roster_input_row .= roster::get_duty_end_from_roster($Roster, $date_unix, $roster_row_iterator);
        $roster_input_row .= "'>\n";

        $roster_input_row .= "<br>\n";
        $roster_input_row .= " " . gettext("break") . ": <input type=time size=5 class=Dienstplan_Mittagbeginn name=Dienstplan[" . $day_iterator . "][Mittagsbeginn][" . $roster_row_iterator . "] id=Dienstplan[" . $day_iterator . "][Mittagsbeginn][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 4 ) . " value='";
        $roster_input_row .= roster::get_break_start_from_roster($Roster, $date_unix, $roster_row_iterator);

        $roster_input_row .= "'> bis <input type=time size=5 class=Dienstplan_Mittagsende name=Dienstplan[" . $day_iterator . "][Mittagsende][" . $roster_row_iterator . "] id=Dienstplan[" . $day_iterator . "][Mittagsende][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 5 ) . " value='";
        $roster_input_row .= roster::get_break_end_from_roster($Roster, $date_unix, $roster_row_iterator);
        $roster_input_row .= "'>";
        $comment = roster::get_comment_from_roster($Roster, $date_unix, $roster_row_iterator);
        $roster_input_row_comment_input_id = "roster_input_row_comment_input_" . $day_iterator . "_" . $roster_row_iterator;
        $roster_input_row_comment_input_link_div_show_id = $roster_input_row_comment_input_id . "_link_div_show";
        $roster_input_row_comment_input_link_div_hide_id = $roster_input_row_comment_input_id . "_link_div_hide";
        if (empty($comment)) {
            $roster_comment_visibility_style_display = "inline";
            $roster_uncomment_visibility_style_display = "none";
        } else {
            $roster_comment_visibility_style_display = "none";
            $roster_uncomment_visibility_style_display = "inline";
        }
        $roster_input_row .= "<div class='no-print kommentar_ersatz' style=display:$roster_comment_visibility_style_display id='$roster_input_row_comment_input_link_div_show_id'>"
                . "<a onclick='roster_input_row_comment_show($roster_input_row_comment_input_id, $roster_input_row_comment_input_link_div_show_id, $roster_input_row_comment_input_link_div_hide_id)' title='Kommentar anzeigen'>"
                . "K+"
                . "</a>"
                . "</div>\n";
        $roster_input_row .= "<div class='no-print kommentar_input' style=display:$roster_uncomment_visibility_style_display id=$roster_input_row_comment_input_link_div_hide_id>"
                . "<a onclick='roster_input_row_comment_hide($roster_input_row_comment_input_id, $roster_input_row_comment_input_link_div_show_id, $roster_input_row_comment_input_link_div_hide_id)' title='Kommentar ausblenden'>"
                . "K-"
                . "</a>"
                . "</div>\n";
        $roster_input_row .= "<br>"
                . "<div class=kommentar_input style=display:$roster_uncomment_visibility_style_display id=$roster_input_row_comment_input_id>"
                . "Kommentar: "
                . "<input type=text name=Dienstplan[" . $day_iterator . "][Kommentar][" . $roster_row_iterator . "] value='";
        $roster_input_row .= $comment;
        $roster_input_row .= "'>"
                . "</div>\n";
        $roster_input_row .= "</td>\n";
        return $roster_input_row;
    }

    function build_roster_input_row_employee_select($roster_employee_id, $day_iterator, $roster_row_iterator, $maximum_number_of_rows) {
        global $List_of_employees;
        $roster_input_row_employee_select = "<select name=Dienstplan[" . $day_iterator . "][VK][" . $roster_row_iterator . "] tabindex=" . (($day_iterator * $maximum_number_of_rows * 5) + ($roster_row_iterator * 5) + 1) . ">";
        $roster_input_row_employee_select .= "<option value=''>&nbsp;</option>"; // Es ist sinnvoll, auch eine leere Zeile zu besitzen, damit Mitarbeiter auch wieder gelöscht werden können.

        foreach ($List_of_employees as $employee_id => $last_name) {
            if ($roster_employee_id == $employee_id and NULL !== $roster_employee_id) {
                $roster_input_row_employee_select .= "<option value=$employee_id selected>" . $employee_id . " " . $last_name . "</option>";
            } else {
                $roster_input_row_employee_select .= "<option value=$employee_id>" . $employee_id . " " . $last_name . "</option>";
            }
        }

        $roster_input_row_employee_select .= "</select>\n";
        return $roster_input_row_employee_select;
    }

}
