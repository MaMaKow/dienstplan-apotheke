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

abstract class build_html_roster_views {
    /*
     * Build one table row for a daily view
     *
     * used by: tag-in.php
     * @param $Absentees array expects an array of absent employees in the format array((int)employee_id => (string)reason_for_absence)
     *
     * @return string HTML table row
     */

    public static function build_absentees_row($Absentees) {
        if (NULL === $Absentees) {
            return FALSE;
        }
        $text = "<tr>";
        $text .= build_html_roster_views::build_absentees_column($Absentees);
        $text .= "</tr>\n";
        return $text;
    }

    /*
     * Build one table column for a weekly view
     *
     * used by: woche-out.php
     * @param $Absentees array expects an array of absent employees in the format array(employee_id => reason_for_absence)
     *
     * @return string HTML table column
     */

    public static function build_absentees_column($Absentees) {
        global $List_of_employees;
        $text = "<td class='absentees_column'><b>" . gettext("Absentees") . "</b><br>";
        foreach ($Absentees as $employee_id => $reason) {
            $text .= $List_of_employees[$employee_id] . " (" . $reason . ")<br>";
        }
        $text .= "</td>\n";
        return $text;
    }

    public static function build_roster_input_row($Roster, $day_iterator, $roster_row_iterator, $maximum_number_of_rows, $date_unix, $branch_id) {
        if (!isset($Roster[$date_unix]) or ! isset($Roster[$day_iterator][$roster_row_iterator])) {
            /*
             * Insert a prefilled pseudo roster_item.
             * It contains a valid date and branch.
             */
            $Roster[$day_iterator][$roster_row_iterator] = new roster_item(date('Y-m-d', $date_unix), NULL, $branch_id, NULL, NULL, NULL, NULL);
        }
        $roster_employee_id = $Roster[$day_iterator][$roster_row_iterator]->employee_id;
        //employee input:
        $roster_input_row = "<td>\n";
        $roster_input_row .= "<input type=hidden name=Roster[" . $date_unix . "][" . $roster_row_iterator . "][date_sql] value=" . $Roster[$day_iterator][$roster_row_iterator]->date_sql . ">\n";
        $roster_input_row .= "<input type=hidden name=Roster[" . $date_unix . "][" . $roster_row_iterator . "][branch_id] value=" . $Roster[$day_iterator][$roster_row_iterator]->branch_id . ">\n";
        $roster_input_row .= build_html_roster_views::build_roster_input_row_employee_select($roster_employee_id, $date_unix, $roster_row_iterator, $maximum_number_of_rows);
        //start of duty:
        $roster_input_row .= "<input type=time size=5 class=Dienstplan_Dienstbeginn name=Roster[" . $date_unix . "][" . $roster_row_iterator . "][duty_start_sql] id=Dienstplan[" . $day_iterator . "][Dienstbeginn][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 2 ) . " value='";
        $roster_input_row .= roster::get_duty_start_from_roster($Roster, $date_unix, $roster_row_iterator);
        $roster_input_row .= "'>\n ";

        $roster_input_row .= gettext("to");

        //end of duty:
        $roster_input_row .= " <input type=time size=5 class=Dienstplan_Dienstende name=Roster[" . $date_unix . "][" . $roster_row_iterator . "][duty_end_sql] id=Dienstplan[" . $day_iterator . "][Dienstende][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 3 ) . " value='";
        $roster_input_row .= roster::get_duty_end_from_roster($Roster, $date_unix, $roster_row_iterator);
        $roster_input_row .= "'>\n";

        $roster_input_row .= "<br>\n";

        //start of break:
        $roster_input_row .= " " . gettext("break") . ": <input type=time size=5 class=Dienstplan_Mittagbeginn name=Roster[" . $date_unix . "][" . $roster_row_iterator . "][break_start_sql] id=Dienstplan[" . $day_iterator . "][Mittagsbeginn][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 4 ) . " value='";
        $roster_input_row .= roster::get_break_start_from_roster($Roster, $date_unix, $roster_row_iterator);

        $roster_input_row .= "'> ";
        $roster_input_row .= gettext("to");
        //end of break:
        $roster_input_row .= " <input type=time size=5 class=Dienstplan_Mittagsende name=Roster[" . $date_unix . "][" . $roster_row_iterator . "][break_end_sql] id=Dienstplan[" . $day_iterator . "][Mittagsende][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 5 ) . " value='";
        $roster_input_row .= roster::get_break_end_from_roster($Roster, $date_unix, $roster_row_iterator);
        $roster_input_row .= "'>";
        $roster_input_row .= build_html_roster_views::build_roster_input_row_comment($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "</td>\n";
        return $roster_input_row;
    }

    private static function build_roster_input_row_employee_select($roster_employee_id, $date_unix, $roster_row_iterator, $maximum_number_of_rows) {
        global $List_of_employees;
        $roster_input_row_employee_select = "<select name=Roster[" . $date_unix . "][" . $roster_row_iterator . "][employee_id] tabindex=" . (($date_unix * $maximum_number_of_rows * 5) + ($roster_row_iterator * 5) + 1) . ">";
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

    private static function build_roster_input_row_comment($Roster, $day_iterator, $roster_row_iterator) {
        $roster_input_row_comment_html = "";
        $comment = roster::get_comment_from_roster($Roster, $day_iterator, $roster_row_iterator);
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
        $roster_input_row_comment_html .= "<div class='no-print' style=display:$roster_comment_visibility_style_display id='$roster_input_row_comment_input_link_div_show_id'>"
                . "<a onclick='roster_input_row_comment_show($roster_input_row_comment_input_id, $roster_input_row_comment_input_link_div_show_id, $roster_input_row_comment_input_link_div_hide_id)' title='Kommentar anzeigen'>"
                . "K+</a></div>\n";
        $roster_input_row_comment_html .= "<div class='no-print' style=display:$roster_uncomment_visibility_style_display id=$roster_input_row_comment_input_link_div_hide_id>"
                . "<a onclick='roster_input_row_comment_hide($roster_input_row_comment_input_id, $roster_input_row_comment_input_link_div_show_id, $roster_input_row_comment_input_link_div_hide_id)' title='Kommentar ausblenden'>"
                . "K-</a></div>\n";
        $roster_input_row_comment_html .= "<br>"
                . "<div style=display:$roster_uncomment_visibility_style_display id=$roster_input_row_comment_input_id>"
                . gettext("Comment") . ":&nbsp<input type=text name=Roster[$day_iterator][$roster_row_iterator][comment] value='$comment'></div>\n";
        return $roster_input_row_comment_html;
    }

}
