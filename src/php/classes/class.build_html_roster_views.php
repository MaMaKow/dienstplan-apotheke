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

    const OPTION_SHOW_EMERGENCY_SERVICE_NAME = 'show_emergency_service_name';
    const OPTION_SHOW_CALENDAR_WEEK = 'show_calendar_week';

    /**
     * Build one table row for a daily view
     *
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

    /**
     * Build one table column for a weekly view
     *
     * used by: src/php/pages/roster-week-table.php
     * @param $Absentees array expects an array of absent employees in the format array(employee_id => reason_for_absence)
     *
     * @return string HTML table column
     */
    public static function build_absentees_column($Absentees) {
        global $workforce;
        $text = "<td class='absentees_column'><b>" . gettext("Absentees") . "</b><br>";
        foreach ($Absentees as $employee_id => $reason) {
            $text .= $workforce->List_of_employees[$employee_id]->last_name . " (" . pdr_gettext($reason) . ")<br>";
        }
        $text .= "</td>\n";
        return $text;
    }

    public static function build_roster_input_row($Roster, $day_iterator, $roster_row_iterator, $maximum_number_of_rows, $branch_id, $Options = array()) {
        if (!isset($Roster[$day_iterator]) or ! isset($Roster[$day_iterator][$roster_row_iterator])) {
            /*
             * Insert a prefilled pseudo roster_item.
             * It contains a valid date and branch.
             */
            $Roster[$day_iterator][$roster_row_iterator] = new roster_item_empty(date('Y-m-d', $day_iterator), $branch_id);
        }
        if (NULL === $Roster[$day_iterator][$roster_row_iterator]->employee_id and isset($Options['add_hidden_employee'])) {
            $Roster[$day_iterator][$roster_row_iterator]->employee_id = $Options['add_hidden_employee'];
        }
        $roster_input_row = "<td>\n";
        $roster_input_row .= "<input type=hidden name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][date_sql] value=" . $Roster[$day_iterator][$roster_row_iterator]->date_sql . ">\n";

        /*
         * employee input:
         */
        $roster_employee_id = $Roster[$day_iterator][$roster_row_iterator]->employee_id;
        $roster_input_row_employee = "<input type=hidden name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][employee_id] value=" . $Roster[$day_iterator][$roster_row_iterator]->employee_id . ">\n";
        if (in_array('add_select_employee', $Options)) {
            /*
             * Change $roster_input_row_branch from the above hidden input into a visible select element:
             */
            $roster_input_row_employee = "<span>";
            $roster_input_row_employee .= build_html_roster_views::build_roster_input_row_employee_select($roster_employee_id, $day_iterator, $roster_row_iterator, $maximum_number_of_rows);
            $roster_input_row_employee .= "</span>";
        }
        $roster_input_row .= $roster_input_row_employee;
        /*
         * start of duty:
         */
        $roster_input_row .= "<input type=time size=5 class=Dienstplan_Dienstbeginn name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][duty_start_sql] id=Dienstplan[" . $day_iterator . "][Dienstbeginn][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 2 ) . " value='";
        $roster_input_row .= roster::get_duty_start_from_roster($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "'>\n ";

        $roster_input_row .= gettext("to");

        /*
         * end of duty:
         */
        $roster_input_row .= " <input type=time size=5 class=Dienstplan_Dienstende name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][duty_end_sql] id=Dienstplan[" . $day_iterator . "][Dienstende][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 3 ) . " value='";
        $roster_input_row .= roster::get_duty_end_from_roster($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "'>\n";
        $roster_input_row .= "<br>\n";

        /*
         * start of break:
         */
        $roster_input_row .= " " . gettext("break") . ": <input type=time size=5 class=Dienstplan_Mittagbeginn name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][break_start_sql] id=Dienstplan[" . $day_iterator . "][Mittagsbeginn][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 4 ) . " value='";
        $roster_input_row .= roster::get_break_start_from_roster($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "'> ";
        $roster_input_row .= gettext("to");

        /*
         * end of break:
         */
        $roster_input_row .= " <input type=time size=5 class=Dienstplan_Mittagsende name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][break_end_sql] id=Dienstplan[" . $day_iterator . "][Mittagsende][" . $roster_row_iterator . "] tabindex=" . ($day_iterator * $maximum_number_of_rows * 5 + $roster_row_iterator * 5 + 5 ) . " value='";
        $roster_input_row .= roster::get_break_end_from_roster($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "'>";

        /*
         * branch input:
         */
        $roster_input_row_branch_name = "Roster[$day_iterator][$roster_row_iterator][branch_id]";
        $roster_input_row_branch_id = $Roster[$day_iterator][$roster_row_iterator]->branch_id;
        $roster_input_row_branch = "<input type=hidden name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][branch_id] value=" . $Roster[$day_iterator][$roster_row_iterator]->branch_id . ">\n";
        if (in_array('add_select_branch', $Options)) {
            /*
             * Change $roster_input_row_branch from the above hidden input into a visible select element:
             */
            $roster_input_row_branch = "<br>";
            $roster_input_row_branch .= self::build_roster_input_row_branch_select($roster_input_row_branch_id, $roster_input_row_branch_name);
        }
        $roster_input_row .= $roster_input_row_branch;

        /*
         * comments:
         */
        $roster_input_row .= build_html_roster_views::build_roster_input_row_comment($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "</td>\n";
        return $roster_input_row;
    }

    public static function build_roster_input_row_add_row($day_iterator, $roster_row_iterator, $maximum_number_of_rows, $branch_id) {
        $id = "roster_input_row_add_row_target_" . $day_iterator . "_" . $roster_row_iterator;

        $roster_input_row_add_row = "<tr id='$id' data-id=$id data-day_iterator=$day_iterator data-roster_row_iterator=$roster_row_iterator data-maximum_number_of_rows=$maximum_number_of_rows data-branch_id=$branch_id><td></td></tr>\n";
        $roster_input_row_add_row .= "<tr>\n";
        $roster_input_row_add_row .= "<td>";

        $roster_input_row_add_row .= "<button type='button' onclick='roster_input_row_add($id);'>";
        $roster_input_row_add_row .= "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/add.svg' class='roster_input_row_add_row_image' alt='Add one row'>";
        $roster_input_row_add_row .= "</button>\n";
        $roster_input_row_add_row .= "</td>\n";
        $roster_input_row_add_row .= "</tr>\n";
        return $roster_input_row_add_row;
    }

    private static function build_roster_input_row_branch_select($current_branch_id, $form_input_name) {
        global $List_of_branch_objects;
        /*
         * TODO: Build a select for branch.
         * Use it in the principle roster.
         */
        $branch_select = "";
        $branch_select .= "<select name='$form_input_name' >\n";
        foreach ($List_of_branch_objects as $branch_id => $branch_object) {
            if ($branch_id != $current_branch_id) {
                $branch_select .= "<option value=" . $branch_id . ">" . $branch_object->name . "</option>\n";
            } else {
                $branch_select .= "<option value=" . $branch_id . " selected>" . $branch_object->name . "</option>\n";
            }
        }
        $branch_select .= "</select>\n";
        return $branch_select;
    }

    private static function build_roster_input_row_employee_select($roster_employee_id, $date_unix, $roster_row_iterator, $maximum_number_of_rows) {
        global $workforce;
        if (NULL === $workforce) {
            $workforce = new workforce(date('Y-m-d', $date_unix));
        }
        $roster_input_row_employee_select = "<select name=Roster[" . $date_unix . "][" . $roster_row_iterator . "][employee_id] tabindex=" . (($date_unix * $maximum_number_of_rows * 5) + ($roster_row_iterator * 5) + 1) . ">";
        /*
         * The empty option is necessary to enable the deletion of employees from the roster:
         */
        $roster_input_row_employee_select .= "<option value=''>&nbsp;</option>";
        if (isset($workforce->List_of_employees[$roster_employee_id]->last_name) or ! isset($roster_employee_id)) {
            foreach ($workforce->List_of_employees as $employee_id => $employee_object) {
                if ($roster_employee_id == $employee_id and NULL !== $roster_employee_id) {
                    $roster_input_row_employee_select .= "<option value=$employee_id selected>" . $employee_id . " " . $employee_object->last_name . "</option>";
                } else {
                    $roster_input_row_employee_select .= "<option value=$employee_id>" . $employee_id . " " . $employee_object->last_name . "</option>";
                }
            }
        } else {
            /*
             * Unknown employee, probably someone from the past.
             */
            $roster_input_row_employee_select .= "<option value=$roster_employee_id selected>" . $roster_employee_id . " Unknown employee" . "</option>";
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
        $roster_input_row_comment_html .= "<div class='no_print' style=display:$roster_comment_visibility_style_display id='$roster_input_row_comment_input_link_div_show_id'>"
                . "<a onclick='roster_input_row_comment_show($roster_input_row_comment_input_id, $roster_input_row_comment_input_link_div_show_id, $roster_input_row_comment_input_link_div_hide_id)' title='Kommentar anzeigen'>"
                . "K+</a></div>\n";
        $roster_input_row_comment_html .= "<div class='no_print' style=display:$roster_uncomment_visibility_style_display id=$roster_input_row_comment_input_link_div_hide_id>"
                . "<a onclick='roster_input_row_comment_hide($roster_input_row_comment_input_id, $roster_input_row_comment_input_link_div_show_id, $roster_input_row_comment_input_link_div_hide_id)' title='Kommentar ausblenden'>"
                . "K-</a></div>\n";
        $roster_input_row_comment_html .= "<br>"
                . "<div style=display:$roster_uncomment_visibility_style_display id=$roster_input_row_comment_input_id>"
                . gettext("Comment") . ":&nbsp;<input type=text name=Roster[$day_iterator][$roster_row_iterator][comment] value='$comment'></div>\n";
        return $roster_input_row_comment_html;
    }

    public static function build_roster_readonly_branch_table_rows(array $Branch_roster, int $branch_id, string $date_sql_start, string $date_sql_end, $Options = NULL) {
        global $List_of_branch_objects;

        $date_start_object = new DateTime($date_sql_start);
        $date_end_object = new DateTime($date_sql_end);
        $interval_object = $date_end_object->diff($date_start_object, TRUE);
        $number_of_days = $interval_object->format('%d') + 1;
        $table_html = "";

        foreach (array_keys($List_of_branch_objects) as $other_branch_id) {
            if ($branch_id == $other_branch_id) {
                continue;
            }
            if (array() === $Branch_roster[$other_branch_id]) {
                continue;
            }
            $table_html .= "<tr class='branch_roster_title_tr'><th colspan=";
            $table_html .= htmlentities($number_of_days) . ">";
            $table_html .= $List_of_branch_objects[$branch_id]->short_name;
            $table_html .= " in " . $List_of_branch_objects[$other_branch_id]->short_name . "</th></tr>";
            $table_html .= build_html_roster_views::build_roster_readonly_table($Branch_roster[$other_branch_id], $other_branch_id, $Options);
        }
        return $table_html;
    }

    public static function build_roster_read_only_table_head($Roster, $Options = array()) {
        global $workforce, $List_of_branch_objects;
        $head_table_html = "";
        $head_table_html .= "<thead>\n";
        $head_table_html .= "<tr>\n";
        foreach (array_keys($Roster) as $date_unix) {//Datum
            $date_sql = date('Y-m-d', $date_unix);
            $head_table_html .= "<td>";
            $head_table_html .= "<a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/roster-day-read.php?datum=$date_sql'>";
            $head_table_html .= strftime('%A', $date_unix);
            $head_table_html .= " \n";
            $head_table_html .= strftime('%d.%m.', $date_unix);
            $holiday = holidays::is_holiday($date_unix);
            if (FALSE !== $holiday) {
                $head_table_html .= "<br>$holiday";
            }
            $head_table_html .= "</a>";
            if (in_array(self::OPTION_SHOW_CALENDAR_WEEK, $Options)) {
                $head_table_html .= "<br><a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/roster-week-table.php?datum=" . $date_sql . "'>" . gettext("calendar week") . strftime(' %V', strtotime($date_sql)) . "</a>\n";
            }

            $having_emergency_service = pharmacy_emergency_service::having_emergency_service($date_sql);
            if (FALSE !== $having_emergency_service) {
                $head_table_html .= "<br><em>" . gettext("EMERGENCY SERVICE") . "</em><br>";
                if (in_array(self::OPTION_SHOW_EMERGENCY_SERVICE_NAME, $Options)) {
                    if (isset($workforce->List_of_employees[$having_emergency_service['employee_id']])) {
                        $head_table_html .= $workforce->List_of_employees[$having_emergency_service['employee_id']]->last_name;
                    } else {
                        $head_table_html .= "???";
                    }
                    $head_table_html .= " / " . $List_of_branch_objects[$having_emergency_service['branch_id']]->name;
                }
            }
            $head_table_html .= "</td>\n";
        }
        $head_table_html .= "</tr></thead>";
        return $head_table_html;
    }

    public static function build_roster_readonly_table($Roster, $branch_id, $Options = NULL) {
        if (array() === $Roster) {
            return FALSE;
        }
        global $workforce;
        global $config;
        $table_html = "";
        $table_html .= "<tbody>";

        $max_employee_count = roster::calculate_max_employee_count($Roster);

        for ($table_row_iterator = 0; $table_row_iterator < $max_employee_count; $table_row_iterator++) {
            $table_html .= "<tr>\n";
            foreach (array_keys($Roster) as $date_unix) {
                $date_sql = date('Y-m-d', $date_unix);
                if (!isset($Roster[$date_unix][$table_row_iterator]) or NULL === $Roster[$date_unix][$table_row_iterator]->employee_id) {
                    $table_html .= "<td><!--No more data in roster--></td>\n";
                    continue;
                }
                $roster_object = $Roster[$date_unix][$table_row_iterator];
                /*
                 * The following lines check for the state of approval.
                 * Duty rosters have to be approved by the leader, before the staff can view them.
                 */
                $approval = build_html_roster_views::get_approval_from_database($date_sql, $branch_id);
                if ("approved" !== $approval and TRUE == $config['hide_disapproved']) {
                    $table_html .= "<td><!--Hidden because not approved--></td>";
                    continue;
                }
                $table_html .= "<td>";
                $zeile = "";

                $zeile .= "<span class='employee_and_hours_and_duty_time'><span class='employee_and_hours'><b><a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/roster-employee-table.php?"
                        . "datum=" . htmlentities($roster_object->date_sql)
                        . "&employee_id=" . htmlentities($roster_object->employee_id) . "'>";
                if (isset($workforce->List_of_employees[$roster_object->employee_id]->last_name)) {
                    $zeile .= $workforce->List_of_employees[$roster_object->employee_id]->last_name;
                } else {
                    $zeile .= "Unknown employee: " . $roster_object->employee_id;
                }
                $zeile .= "</a></b> / <span class='roster_working_hours'>";
                $zeile .= htmlentities($roster_object->working_hours);
                $zeile .= "&nbsp;h</span><!-- roster_working_hours --></span><!-- employee_and_hours --> ";
                if (isset($Options['space_constraints']) and 'narrow' === $Options['space_constraints']) {
                    $zeile .= " <br> ";
                } else {
                    $zeile .= "<span class='vertical_spacer'></span>";
                }
                /*
                 * start and end of duty
                 */
                $zeile .= "<span class='duty_time'>";
                $zeile .= self::build_roster_readonly_table_add_time($roster_object, 'duty_start_sql');
                $zeile .= " - ";
                $zeile .= self::build_roster_readonly_table_add_time($roster_object, 'duty_end_sql');
                if (!empty($roster_object->comment)) {
                    /*
                     * In case, there is a comment available, add a hint in form of a single letter.
                     * That single letter is the first letter of the word Comment (in the chosen language).
                     */
                    $zeile .= '&nbsp;' . '<sup>' . mb_substr(gettext('Comment'), 0, 1) . '</sup>';
                }
                $zeile .= "</span><!-- class='duty_time'--></span><!-- employee_and_hours_and_duty_time -->";
                /*
                 * start and end of break
                 */
                if (isset($Options['space_constraints']) and 'narrow' === $Options['space_constraints']) {
                    $zeile .= "<br>\n";
                } else {
                    $zeile .= "<span class='vertical_spacer'></span>";
                }
                if ($roster_object->break_start_int > 0) {
                    $zeile .= "<span class='break_time'>";
                    $zeile .= " " . gettext("break") . ": ";
                    $zeile .= "<span class='time'>" . htmlentities($roster_object->break_start_sql) . "</span>";
                    $zeile .= " - ";
                    $zeile .= "<span class='time'>" . htmlentities($roster_object->break_end_sql) . "</span>";
                    $zeile .= "</span><!-- class='break_time' -->";
                }
                $table_html .= $zeile;
                $table_html .= "</td>\n";
            }
        }
        $table_html .= "</tr>\n";
        $table_html .= "</tbody>\n";

        return $table_html;
    }

    private static function build_roster_readonly_table_add_time($roster_object, $parameter) {
        if (self::equals_principle_roster($roster_object, $parameter)) {
            $html = "<span class='time'>" . htmlentities($roster_object->$parameter) . "</span>";
        } else {
            $html = "<span class='time'><strong>" . htmlentities($roster_object->$parameter) . "</strong></span>";
        }
        return $html;
    }

    public static function build_roster_readonly_employee_table($Roster, $branch_id) {
        if (array() === $Roster) {
            return FALSE;
        }
        global $workforce, $List_of_branch_objects;
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
            $table_html .= "<tr>\n";
            foreach (array_keys($Roster) as $date_unix) {
                $date_sql = date('Y-m-d', $date_unix);
                if (!isset($Roster[$date_unix][$table_row_iterator]) or NULL === $Roster[$date_unix][$table_row_iterator]->employee_id) {
                    $table_html .= "<td><!--No more data in roster--></td>\n";
                    continue;
                }
                $roster_object = $Roster[$date_unix][$table_row_iterator];
                /*
                 * The following lines check for the state of approval.
                 * Duty rosters have to be approved by the leader, before the staff can view them.
                 */
                $approval = build_html_roster_views::get_approval_from_database($date_sql, $branch_id);
                if ("approved" !== $approval and false != $config['hide_disapproved']) {
                    $table_html .= "<td><!--Hidden because not approved--></td>";
                    continue;
                }
                $table_html .= "<td>";
                $zeile = "";

                if (isset($Changed_roster_employee_id_list[$date_unix]) and in_array($roster_object->employee_id, $Changed_roster_employee_id_list[$date_unix])) {
                    $emphasis_start = "<strong>"; //Significant emphasis
                    $emphasis_end = "</strong>"; //Significant emphasis
                } else {
                    $emphasis_start = ""; //No emphasis
                    $emphasis_end = ""; //No emphasis
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
        $table_html .= "</tbody>\n";

        return $table_html;
    }

    public static function get_approval_from_database($date_sql, $branch_id) {
        /*
         * TODO: This might be better placed in some other class.
         */
        $sql_query = "SELECT state FROM `approval` WHERE date = :date AND branch = :branch_id";
        $result = database_wrapper::instance()->run($sql_query, array('date' => $date_sql, 'branch_id' => $branch_id));
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $approval = $row->state;
            return $approval;
        }
        return FALSE;
    }

    public static function build_roster_working_hours_div($Working_hours_week_have, $Working_hours_week_should, $Options = NULL) {
        if (array() === $Working_hours_week_have) {
            return FALSE;
        }
        global $workforce, $List_of_employee_working_week_hours;
        $week_hours_table_html = "<div id=week_hours_table_div>\n";
        $week_hours_table_html .= '<H2>' . gettext('Hours per week') . "</H2>\n";
        $week_hours_table_html .= "<table class='tight'>";
        foreach ($Working_hours_week_have as $employee_id => $working_hours_have) {
            if (isset($Options['employee_id']) and $employee_id !== $Options['employee_id']) {
                continue; /* Only the specified employees is shown. */
            }
            $week_hours_table_html .= "<tr>";
            $week_hours_table_html .= "<td>";
            if (isset($workforce->List_of_employees[$employee_id]->last_name)) {
                $week_hours_table_html .= $workforce->List_of_employees[$employee_id]->last_name;
            } else {
                $week_hours_table_html .= "Unknown employee: " . $employee_id;
            }
            $week_hours_table_html .= "</td>";
            $week_hours_table_html .= "<td>" . round($working_hours_have * 4, 0) / 4;
            $week_hours_table_html .= " </td><td> ";
            if (isset($Working_hours_week_should[$employee_id])) {
                $week_hours_table_html .= round($Working_hours_week_should[$employee_id], 1) . "\n";
                $differenz = $working_hours_have - $Working_hours_week_should[$employee_id];
            } else {
                $week_hours_table_html .= $List_of_employee_working_week_hours[$employee_id] . "\n";
                $differenz = $working_hours_have - $List_of_employee_working_week_hours[$employee_id];
            }
            $week_hours_table_html .= "</td>\n";
            $week_hours_table_html .= "<td>\n";
            if (abs($differenz) >= 0.25) {
                $week_hours_table_html .= "<b>" . (round($differenz * 4, 0) / 4) . "</b>\n";
            }
            $week_hours_table_html .= "</td>\n";
            $week_hours_table_html .= "</tr>\n";
        }
        $week_hours_table_html .= "</table>";

        $week_hours_table_html .= "</div>"; // id=week_hours_table_div
        return $week_hours_table_html;
    }

    public static function calculate_working_hours_week_should($Roster) {
        global $workforce;
        foreach ($workforce->List_of_employees as $employee_object) {
            $Working_hours_week_should[$employee_object->employee_id] = $employee_object->working_week_hours;
        }
        foreach (array_keys($Roster) as $date_unix) {
            $date_sql = date('Y-m-d', $date_unix);
            $weekday = date('N', $date_unix);
            $holiday = holidays::is_holiday($date_unix);
            $Absentees = absence::read_absentees_from_database($date_sql);
            /**
             * @var $List_of_non_respected_absence_reasons
             * @see absence::$List_of_absence_reasons for a full list of absence reasons (paid and unpaid)
             */
            $List_of_non_respected_absence_reasons = array('unpaid leave of absence');

            /*
             * Substract days, which are holidays:
             */
            if (FALSE !== $holiday) {
                foreach ($workforce->List_of_employees as $employee_id => $employee_object) {
                    if (!empty($employee_object->Principle_roster[$weekday])) {
                        $number_of_working_days_per_week = count($employee_object->Principle_roster);
                        $Working_hours_week_should[$employee_id] -= $employee_object->working_week_hours / $number_of_working_days_per_week;
                    }
                }
            }
            /*
             * Substract days, which are respected absence_days:
             */
            foreach ($Absentees as $employee_id => $reason) {
                if (!in_array($reason, $List_of_non_respected_absence_reasons) and FALSE === $holiday and date('N', $date_unix) < 6) {
                    $Working_hours_week_should[$employee_id] -= $workforce->List_of_employees[$employee_id]->working_week_hours / 5;
                }
            }
        }
        return $Working_hours_week_should;
    }

    public static function equals_principle_roster($roster_object, $parameter) {
        global $workforce;
        $employee_id = $roster_object->employee_id;
        $weekday = $roster_object->weekday;
        if (!isset($workforce->List_of_employees[$employee_id]->Principle_roster[$weekday])) {
            return FALSE;
        }
        foreach ($workforce->List_of_employees[$employee_id]->Principle_roster[$weekday] as $principle_roster_item) {
            if ($principle_roster_item->$parameter == $roster_object->$parameter) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
