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
                . gettext("Comment") . ":&nbsp;<input type=text name=Roster[$day_iterator][$roster_row_iterator][comment] value='$comment'></div>\n";
        return $roster_input_row_comment_html;
    }

    public static function build_roster_readonly_branch_table_rows($Branch_roster, $branch_id, $date_sql_start, $date_sql_end) {
        global $List_of_branch_objects;
        $date_unix_start = strtotime($date_sql_start);
        $date_unix_end = strtotime($date_sql_end);
        $number_of_days = ($date_unix_end - $date_unix_start) / PDR_ONE_DAY_IN_SECONDS;

        $table_html = "";

        foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
            if ($branch_id == $other_branch_id) {
                continue;
            }
            if (array() === $Branch_roster[$other_branch_id]) {
                continue;
            }
            $table_html .= "</tbody><tbody><tr class='branch_roster_title_tr'><th colspan=" . htmlentities($number_of_days) . ">" . $List_of_branch_objects[$branch_id]->short_name . " in " . $List_of_branch_objects[$other_branch_id]->short_name . "</th></tr>";
            $table_html .= build_html_roster_views::build_roster_readonly_table($Branch_roster[$other_branch_id], $other_branch_id);
        }
        return $table_html;
    }

    public static function build_roster_read_only_table_head($Roster) {
        $head_table_html = "";
        $head_table_html .= "<thead>\n";
        $head_table_html .= "<tr>\n";
        foreach (array_keys($Roster) as $date_unix) {//Datum
            $date_sql = date('Y-m-d', $date_unix);
            $head_table_html .= "<td>";
            $head_table_html .= "<a href='tag-out.php?datum=$date_sql'>";
            $head_table_html .= strftime('%A', $date_unix);
            $head_table_html .= " \n";
            $head_table_html .= strftime('%d.%m.', $date_unix);
            $holiday = holidays::is_holiday($date_unix);
            if (FALSE !== $holiday) {
                $head_table_html .= "<br>$holiday";
            }

            if (FALSE !== pharmacy_emergency_service::having_emergency_service($date_sql)) {
                $head_table_html .= "<br> <em>NOTDIENST</em> ";
            }
            $head_table_html .= "</a></td>\n";
        }
        $head_table_html .= "</tr></thead>";
        return $head_table_html;
    }

    public static function build_roster_readonly_table($Roster, $branch_id) {
        if (array() === $Roster) {
            return FALSE;
        }
        global $List_of_employees;
        global $config;
        $table_html = "";
        $table_html .= "<tbody>";

        $max_employee_count = roster::calculate_max_employee_count($Roster);
        $List_of_date_unix_in_roster = array_keys($Roster);
        $date_sql_start = date('Y-m-d', min($List_of_date_unix_in_roster));
        $date_sql_end = date('Y-m-d', max($List_of_date_unix_in_roster));
        $Principle_roster = roster::read_principle_roster_from_database($branch_id, $date_sql_start, $date_sql_end);
        /*
         * TODO:Maybe use the roster stored in the workforce->employee object instead
         */
        $Changed_roster_employee_id_list = user_input::get_changed_roster_employee_id_list($Roster, $Principle_roster);

        for ($table_row_iterator = 0; $table_row_iterator < $max_employee_count; $table_row_iterator++) {
            /*
             * if (isset($feiertag) && !isset($notdienst)) {
             * break 1;
             * }
             */
            $table_html .= "<tr>\n";
            foreach (array_keys($Roster) as $date_unix) {
                $date_sql = date('Y-m-d', $date_unix);
                if (!isset($Roster[$date_unix][$table_row_iterator]) or NULL === $Roster[$date_unix][$table_row_iterator]->employee_id) {
                    $table_html .= "<td></td>\n";
                    continue;
                }
                $roster_object = $Roster[$date_unix][$table_row_iterator];
                if (!isset($List_of_employees[$roster_object->employee_id])) {
//$List_of_employees[$roster_object->employee_id] = '?';
                }
                /*
                 * The following lines check for the state of approval.
                 * Duty rosters have to be approved by the leader, before the staff can view them.
                 */
                $approval = build_html_roster_views::get_approval_from_database($date_sql, $branch_id);
                if ("approved" !== $approval and false !== $config['hide_disapproved']) {
                    $table_html .= "<td></td>";
                    continue;
                }
                $table_html .= "<td>";
                $zeile = "";

                if (isset($Changed_roster_employee_id_list[$date_unix]) and in_array($roster_object->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    $emphasis_start = ""; //No emphasis
                    $emphasis_end = ""; //No emphasis
                } else {
                    $emphasis_start = "<strong>"; //Significant emphasis
                    $emphasis_end = "</strong>"; //Significant emphasis
                }
                $zeile .= "$emphasis_start<b><a href='mitarbeiter-out.php?"
                        . "datum=" . htmlentities($roster_object->date_sql)
                        . "&employee_id=" . htmlentities($roster_object->employee_id) . "'>";
                $zeile .= $List_of_employees[$roster_object->employee_id];
                $zeile .= "</a></b> / ";
                $zeile .= htmlentities($roster_object->working_hours);
                $zeile .= " ";
//Dienstbeginn
                $zeile .= " <br> ";
                $zeile .= htmlentities($roster_object->duty_start_sql);
//Dienstende
                $zeile .= " - ";
                $zeile .= htmlentities($roster_object->duty_end_sql);
//	Mittagspause
                $zeile .= "<br>\n";
                if ($roster_object->break_start_int > 0) {
                    $zeile .= " " . gettext("break") . ": ";
                    $zeile .= htmlentities($roster_object->break_start_sql);
                    $zeile .= " - ";
                    $zeile .= htmlentities($roster_object->break_end_sql);
                }
                $zeile .= "$emphasis_end";
                $table_html .= $zeile;
                $table_html .= "</td>\n";
            }
        }
        $table_html .= "</tr>\n";
        return $table_html;
    }

    public static function build_roster_readonly_employee_table($Roster, $branch_id) {
        if (array() === $Roster) {
            return FALSE;
        }
        global $List_of_employees, $List_of_branch_objects;
        global $config;
        $table_html = "";
        $table_html .= "<tbody>";

        $max_employee_count = roster::calculate_max_employee_count($Roster);
        $List_of_date_unix_in_roster = array_keys($Roster);
        $date_sql_start = date('Y-m-d', min($List_of_date_unix_in_roster));
        $date_sql_end = date('Y-m-d', max($List_of_date_unix_in_roster));
        $Principle_roster = roster::read_principle_roster_from_database($branch_id, $date_sql_start, $date_sql_end);
        $Changed_roster_employee_id_list = user_input::get_changed_roster_employee_id_list($Roster, $Principle_roster);

        for ($table_row_iterator = 0; $table_row_iterator < $max_employee_count; $table_row_iterator++) {
            /*
             * if (isset($feiertag) && !isset($notdienst)) {
             * break 1;
             * }
             */
            $table_html .= "<tr>\n";
            foreach (array_keys($Roster) as $date_unix) {
                $date_sql = date('Y-m-d', $date_unix);
                if (!isset($Roster[$date_unix][$table_row_iterator]) or NULL === $Roster[$date_unix][$table_row_iterator]->employee_id) {
                    $table_html .= "<td></td>\n";
                    continue;
                }
                $roster_object = $Roster[$date_unix][$table_row_iterator];
                if (!isset($List_of_employees[$roster_object->employee_id])) {

                }
                /*
                 * The following lines check for the state of approval.
                 * Duty rosters have to be approved by the leader, before the staff can view them.
                 */
                $approval = build_html_roster_views::get_approval_from_database($date_sql, $branch_id);
                if ("approved" !== $approval and false !== $config['hide_disapproved']) {
                    $table_html .= "<td></td>";
                    continue;
                }
                $table_html .= "<td>";
                $zeile = "";

                if (isset($Changed_roster_employee_id_list[$date_unix]) and in_array($roster_object->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    $emphasis_start = ""; //No emphasis
                    $emphasis_end = ""; //No emphasis
                } else {
                    $emphasis_start = "<strong>"; //Significant emphasis
                    $emphasis_end = "</strong>"; //Significant emphasis
                }
                $zeile .= "$emphasis_start";
                $zeile .= htmlentities($roster_object->duty_start_sql);
                $zeile .= " - ";
                $zeile .= htmlentities($roster_object->duty_end_sql);
                $zeile .= " / ";
                $zeile .= htmlentities($roster_object->working_hours);
                $zeile .= "&nbsp;h";
                $zeile .= "<br>\n";
                if ($roster_object->break_start_int > 0) {
                    $zeile .= " " . gettext("break") . ": ";
                    $zeile .= htmlentities($roster_object->break_start_sql);
                    $zeile .= " - ";
                    $zeile .= htmlentities($roster_object->break_end_sql);
                }
                $zeile .= "$emphasis_end";
                $zeile .= "<br>";
                $zeile .= htmlentities($List_of_branch_objects[$roster_object->branch_id]->short_name);
                $table_html .= $zeile;
                $table_html .= "</td>\n";
            }
        }
        $table_html .= "</tr>\n";
        return $table_html;
    }

    private static function get_approval_from_database($date_sql, $branch_id) {
        /*
         * TODO: This might be better placed in some other class.
         */
        $sql_query = "SELECT state FROM `approval` WHERE date='$date_sql' AND branch='$branch_id'";
        $result = mysqli_query_verbose($sql_query);
        while ($row = mysqli_fetch_object($result)) {
            $approval = $row->state;
            return $approval;
        }
        return FALSE;
    }

    public static function build_roster_working_hours_div($Working_hours_week_have, $Working_hours_week_should, $Options = NULL) {
        global $List_of_employees, $List_of_employee_working_week_hours, $Mandanten_mitarbeiter;
        $week_hours_table_html = "<div id=week_hours_table_div>\n";
        $week_hours_table_html .= "<H2>Wochenstunden</H2>\n";
        $week_hours_table_html .= "<p>\n";
        foreach ($Working_hours_week_have as $employee_id => $working_hours_have) {
            if (isset($Mandanten_mitarbeiter) and FALSE === array_key_exists($employee_id, $Mandanten_mitarbeiter)) {
                /*
                 * TODO: Make this an optional paramater.
                 */
                continue; /* Only employees who belong to the branch are shown. */
            }
            if (isset($Options['employee_id']) and $employee_id !== $Options['employee_id']) {
                continue; /* Only the specified employees is shown. */
            }
            $week_hours_table_html .= "<span>" . $List_of_employees[$employee_id] . " " . round($working_hours_have, 2);
            $week_hours_table_html .= " / ";
            if (isset($Working_hours_week_should[$employee_id])) {
                $week_hours_table_html .= round($Working_hours_week_should[$employee_id], 1) . "\n";
                $differenz = $working_hours_have - $Working_hours_week_should[$employee_id];
            } else {
                $week_hours_table_html .= $List_of_employee_working_week_hours[$employee_id] . "\n";
                $differenz = $working_hours_have - $List_of_employee_working_week_hours[$employee_id];
            }
            if (abs($differenz) >= 0.25) {
                $week_hours_table_html .= " <b>( " . $differenz . " )</b>\n";
            }

            $week_hours_table_html .= "</span>\n";
        }
        $week_hours_table_html .= "</p>\n";
        $week_hours_table_html .= "</div>"; // id=week_hours_table_div
        return $week_hours_table_html;
    }

    public static function calculate_working_hours_week_should($Roster) {
        global $Mandanten_mitarbeiter, $List_of_employee_working_week_hours;
        $Working_hours_week_should = $List_of_employee_working_week_hours;
        foreach (array_keys($Roster) as $date_unix) {
            $date_sql = date('Y-m-d', $date_unix);
            $holiday = holidays::is_holiday($date_unix);
            $Absentees = db_lesen_abwesenheit($date_sql);
            /*
             * TODO: This list has to be carfully chosen:
             * Also there should be a SET in the database.
             */
            $List_of_respected_absence_reasons = array('Krank', 'Krank mit Kind', 'Vacation', 'Freistellung', 'Elternzeit', 'Urlaub', 'Fortbildung', 'Resturlaub', 'Sonderurlaub', 'Kur');
            $List_of_non_respected_absence_reasons = array('Freistellung_unbezahlt', 'Fortbildung_unbezahlt', 'Überstunden Abbau');

            /* Substract days, which are holidays: */
            /*
             * TODO: date('N', $date_unix) < 6 should be substituted for a call to the principle roster.
             * It might be a good idea to save that roster in an employee_item object.
             * That would make querying it much easier.
             */
            if (FALSE !== $holiday and date('N', $date_unix) < 6) {
                foreach (array_keys($Mandanten_mitarbeiter) as $employee_id) {
                    $Working_hours_week_should[$employee_id] -= $List_of_employee_working_week_hours[$employee_id] / 5;
                }
            }
            /* Substract days, which are respected absence_days: */
            foreach ($Absentees as $employee_id => $reason) {
                if (in_array($reason, $List_of_respected_absence_reasons) and FALSE === $holiday and date('N', $date_unix) < 6) {
                    $Working_hours_week_should[$employee_id] -= $List_of_employee_working_week_hours[$employee_id] / 5;
                }
            }
        }
        return $Working_hours_week_should;
    }

}
