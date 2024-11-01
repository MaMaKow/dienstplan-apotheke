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
    const DAYS_IN_A_WEEK = 7;
    const NUMBER_OF_BUSINESS_DAYS = 5;
    const INPUT_ELEMENTS_IN_ROSTER_FORM = 7;

    /**
     * Build one table row for a daily view
     *
     * @param $absenceCollection array expects an array of absent employees in the format array((int)employee_key => (int)id_of_reason_for_absence)
     *
     * @return string HTML table row
     */
    public static function build_absentees_row(PDR\Roster\AbsenceCollection $absenceCollection): ?string {
        if (NULL === $absenceCollection) {
            return FALSE;
        }
        $text = "<tr>";
        $text .= build_html_roster_views::build_absentees_column($absenceCollection);
        $text .= "</tr>\n";
        return $text;
    }

    /**
     * Build one table column for a weekly view
     *
     * used by: src/php/pages/roster-week-table.php
     * @param $absenceCollection array expects an array of absent employees in the format array((int)employee_key => (int)id_of_reason_for_absence)
     *
     * @return string HTML table column
     * @todo Use dependency injection and provide $workforce as a parameter to the method.
     */
    public static function build_absentees_column(PDR\Roster\AbsenceCollection $absenceCollection): string {
        global $workforce;
        $text = "<td class='absentees-column'><b>" . gettext("Absentees") . "</b><br>";
        foreach ($absenceCollection as $absence) {

            $text .= $workforce->List_of_employees[$absence->getEmployeeKey()]->last_name;
            $text .= " (";
            $text .= \PDR\Utility\AbsenceUtility::getReasonStringLocalized($absence->getReasonId());
            $text .= ")<br>";
        }
        $text .= "</td>\n";
        return $text;
    }

    public static function build_roster_input_row($Roster, $day_iterator, $roster_row_iterator, $maximum_number_of_rows, $branch_id, $Options = array()) {
        $day_of_week = (int) date('N', $day_iterator);
        $date_object = DateTime::createFromFormat('U', $day_iterator);
        $alternation_id = alternating_week::get_alternating_week_for_date($date_object->sub(new DateInterval('P' . $date_object->format('w') . 'D')));
        $alternation_factor = $alternation_id;
        if (!isset($Roster[$day_iterator]) or !isset($Roster[$day_iterator][$roster_row_iterator])) {
            /*
             * Insert a prefilled pseudo roster_item.
             * It contains a valid date and branch.
             */
            $Roster[$day_iterator][$roster_row_iterator] = new roster_item_empty(date('Y-m-d', $day_iterator), $branch_id);
        }
        if (NULL === $Roster[$day_iterator][$roster_row_iterator]->employee_key and isset($Options['add_hidden_employee'])) {
            $Roster[$day_iterator][$roster_row_iterator]->employee_key = $Options['add_hidden_employee'];
        }
        $roster_input_row = "<td class='roster_input_row' "
                . " data-roster_row_iterator=" . $roster_row_iterator
                . " data-date_unix=" . $day_iterator
                . " data-date_sql=" . $Roster[$day_iterator][$roster_row_iterator]->date_sql
                . " data-branch_id=" . $Roster[$day_iterator][$roster_row_iterator]->branch_id
                . ">\n";
        $roster_input_row .= "<input type=hidden name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][date_sql] value=" . $Roster[$day_iterator][$roster_row_iterator]->date_sql . ">\n";
        if ($Roster[$day_iterator][$roster_row_iterator] instanceof principle_roster_item) {
            if (isset($Roster[$day_iterator][$roster_row_iterator]->primary_key)) {
                $roster_input_row .= "<input type=hidden name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][primary_key] value=" . $Roster[$day_iterator][$roster_row_iterator]->primary_key . ">\n";
            }
        }

        /*
         * employee input:
         */
        $roster_employee_key = $Roster[$day_iterator][$roster_row_iterator]->employee_key;
        $roster_input_row_employee = "<input type=hidden name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][employee_key] value=" . $Roster[$day_iterator][$roster_row_iterator]->employee_key . ">\n";
        if (in_array('add_select_employee', $Options)) {
            /*
             * Change $roster_input_row_branch from the above hidden input into a visible select element:
             */
            $roster_input_row_employee = "<span>";
            $roster_input_row_employee .= build_html_roster_views::buildRosterInputRowEmployeeSelect($roster_employee_key, $day_iterator, $roster_row_iterator, $maximum_number_of_rows);
            $roster_input_row_employee .= "</span>";
        }
        $roster_input_row .= $roster_input_row_employee;
        /*
         * start of duty:
         */
        $roster_input_row .= "<input type=time size=5 "
                . " data-date_unix='$day_iterator' "
                . " data-roster_row_iterator='$roster_row_iterator' "
                . " data-column='Dienstplan_Dienstbeginn' "
                . " data-roster_column_name='duty_start_sql' "
                . " onChange='roster_change_bar_plot_on_change_of_table(this)' "
                . " class=Dienstplan_Dienstbeginn "
                . " name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][duty_start_sql] "
                . " id=Dienstplan[" . $day_iterator . "][Dienstbeginn][" . $roster_row_iterator . "] "
                . " tabindex=" . (($day_of_week + ( ($alternation_factor * $maximum_number_of_rows + $roster_row_iterator) * self::DAYS_IN_A_WEEK )) * self::INPUT_ELEMENTS_IN_ROSTER_FORM + 2 )
                . " value='";
        $roster_input_row .= roster::get_duty_start_from_roster($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "'>\n ";

        $roster_input_row .= gettext("to");

        /*
         * end of duty:
         */
        $roster_input_row .= " <input type=time size=5 "
                . " data-date_unix='$day_iterator' "
                . " data-roster_row_iterator='$roster_row_iterator' "
                . " data-column='Dienstplan_Dienstende' "
                . " data-roster_column_name='duty_end_sql' "
                . " onChange='roster_change_bar_plot_on_change_of_table(this)' "
                . " class=Dienstplan_Dienstende "
                . " name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][duty_end_sql] "
                . " id=Dienstplan[" . $day_iterator . "][Dienstende][" . $roster_row_iterator . "] "
                . " tabindex=" . (($day_of_week + ( ($alternation_factor * $maximum_number_of_rows + $roster_row_iterator) * self::DAYS_IN_A_WEEK )) * self::INPUT_ELEMENTS_IN_ROSTER_FORM + 3 )
                . " value='";
        $roster_input_row .= roster::get_duty_end_from_roster($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "'>\n";
        /*
         * working hours:
         */
        $roster_input_row .= " <span class='working_hours_span' id=Dienstplan[" . $day_iterator . "][working_hours_span][" . $roster_row_iterator . "]>";
        $roster_input_row .= roster::get_working_hours_from_roster($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "</span><span class='unit_of_time'>&nbsp h</span>\n";
        $roster_input_row .= "<br>\n";

        /*
         * start of break:
         */
        $roster_input_row .= " " . gettext("break") . ": ";
        $roster_input_row .= "<input type=time size=5 "
                . " data-date_unix='$day_iterator' "
                . " data-roster_row_iterator='$roster_row_iterator' "
                . " data-column='Dienstplan_Mittagbeginn' "
                . " data-roster_column_name='break_start_sql' "
                . " onChange='roster_change_bar_plot_on_change_of_table(this)' "
                . " class=Dienstplan_Mittagbeginn "
                . " name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][break_start_sql] "
                . " id=Dienstplan[" . $day_iterator . "][Mittagsbeginn][" . $roster_row_iterator . "] "
                . " tabindex=" . (($day_of_week + ( ($alternation_factor * $maximum_number_of_rows + $roster_row_iterator) * self::DAYS_IN_A_WEEK )) * self::INPUT_ELEMENTS_IN_ROSTER_FORM + 4 )
                . " value='";
        $roster_input_row .= roster::get_break_start_from_roster($Roster, $day_iterator, $roster_row_iterator);
        $roster_input_row .= "'> ";
        $roster_input_row .= gettext("to");

        /*
         * end of break:
         */
        $roster_input_row .= " <input type=time size=5 "
                . " data-date_unix='$day_iterator' "
                . " data-roster_row_iterator='$roster_row_iterator' "
                . " data-column='Dienstplan_Mittagsende' "
                . " data-roster_column_name='break_end_sql' "
                . " onChange='roster_change_bar_plot_on_change_of_table(this)' "
                . " class=Dienstplan_Mittagsende "
                . " name=Roster[" . $day_iterator . "][" . $roster_row_iterator . "][break_end_sql] "
                . " id=Dienstplan[" . $day_iterator . "][Mittagsende][" . $roster_row_iterator . "] "
                . " tabindex=" . (($day_of_week + ( ($alternation_factor * $maximum_number_of_rows + $roster_row_iterator) * self::DAYS_IN_A_WEEK )) * self::INPUT_ELEMENTS_IN_ROSTER_FORM + 5)
                . " value='";
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
            $tabindex_branch_select = (($day_of_week + ( ($alternation_factor * $maximum_number_of_rows + $roster_row_iterator) * self::DAYS_IN_A_WEEK )) * self::INPUT_ELEMENTS_IN_ROSTER_FORM + 6 );
            $roster_input_row_branch .= self::build_roster_input_row_branch_select($roster_input_row_branch_id, $roster_input_row_branch_name, $tabindex_branch_select);
        }
        $roster_input_row .= $roster_input_row_branch;

        /*
         * comments:
         */
        $tabindex_branch_comment = (($day_of_week + ( ($alternation_factor * $maximum_number_of_rows + $roster_row_iterator) * self::DAYS_IN_A_WEEK )) * self::INPUT_ELEMENTS_IN_ROSTER_FORM + 7 );
        $roster_input_row .= build_html_roster_views::build_roster_input_row_comment($Roster, $day_iterator, $roster_row_iterator, $tabindex_branch_comment);
        $roster_input_row .= "</td>\n";
        return $roster_input_row;
    }

    public static function build_roster_input_row_add_row($day_iterator, $roster_row_iterator, $maximum_number_of_rows, $branch_id) {
        $id = "roster_input_row_add_row_target_" . $day_iterator . "_" . $roster_row_iterator;

        $roster_input_row_add_row = "<tr>\n";
        $roster_input_row_add_row .= "<td data-date_unix=$day_iterator>";

        $roster_input_row_add_row .= "<button type='button' id='$id' data-id=$id data-day_iterator=$day_iterator data-roster_row_iterator=$roster_row_iterator data-maximum_number_of_rows=$maximum_number_of_rows data-branch_id=$branch_id onclick='roster_input_row_add($id);'>";
        $roster_input_row_add_row .= "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_add.svg' class='roster-input-row-add-row-image' alt='Add one row'>";
        $roster_input_row_add_row .= "</button>\n";
        $roster_input_row_add_row .= "</td>\n";
        $roster_input_row_add_row .= "</tr>\n";
        return $roster_input_row_add_row;
    }

    private static function build_roster_input_row_branch_select($current_branch_id, $form_input_name, $tabindex) {
        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
        $branch_select = "";
        $branch_select .= "<select name='$form_input_name' ";
        $branch_select .= " tabindex='$tabindex' ";
        $branch_select .= ">\n";
        foreach ($List_of_branch_objects as $branch_id => $branch_object) {
            if ($branch_id != $current_branch_id) {
                $branch_select .= "<option value=" . $branch_id . ">" . $branch_object->getName() . "</option>\n";
            } else {
                $branch_select .= "<option value=" . $branch_id . " selected>" . $branch_object->getName() . "</option>\n";
            }
        }
        $branch_select .= "</select>\n";
        return $branch_select;
    }

    private static function buildRosterInputRowEmployeeSelect($rosterEmployeeKey, $dateUnix, $rosterRowIterator, $maximumNumberOfRows) {
        $dayOfWeek = (int) date('N', $dateUnix);
        $dateObject = DateTime::createFromFormat('U', $dateUnix);
        $dateEndWorkforce = (clone $dateObject)->add(new DateInterval('P1Y'));
        $alternationId = alternating_week::get_alternating_week_for_date($dateObject);
        $alternationFactor = $alternationId;
        $workforce = new workforce($dateObject->format('Y-m-d'), $dateEndWorkforce->format('Y-m-d'));
        $rosterInputRowEmployeeSelect = "<select "
                . " name=Roster[" . $dateUnix . "][" . $rosterRowIterator . "][employee_key] "
                . " tabindex=" . (($dayOfWeek + ( ($alternationFactor * $maximumNumberOfRows + $rosterRowIterator) * self::DAYS_IN_A_WEEK )) * self::INPUT_ELEMENTS_IN_ROSTER_FORM + 1)
                . " data-date_unix='$dateUnix' "
                . " data-roster_row_iterator='$rosterRowIterator' "
                . " data-roster_column_name='employee_key' "
                . " onChange='roster_change_bar_plot_on_change_of_table(this)' "
                . ">";
        /*
         * The empty option is necessary to enable the deletion of employees from the roster:
         */
        $rosterInputRowEmployeeSelect .= "<option value=''>&nbsp;</option>";
        if (isset($workforce->List_of_employees[$rosterEmployeeKey]->last_name) or !isset($rosterEmployeeKey)) {
            foreach ($workforce->List_of_employees as $employeeKey => $employeeObject) {
                if ($rosterEmployeeKey == $employeeKey and NULL !== $rosterEmployeeKey) {
                    $rosterInputRowEmployeeSelect .= "<option value=$employeeKey selected>" . $employeeObject->first_name . " " . $employeeObject->last_name . "</option>";
                } else {
                    $rosterInputRowEmployeeSelect .= "<option value=$employeeKey>" . $employeeObject->first_name . " " . $employeeObject->last_name . "</option>";
                }
            }
        } else {
            /*
             * Unknown employee, probably someone from the past.
             */
            $rosterInputRowEmployeeSelect .= "<option value=$rosterEmployeeKey selected>" . $rosterEmployeeKey . " " . gettext("Unknown employee") . "</option>";
        }

        $rosterInputRowEmployeeSelect .= "</select>\n";
        return $rosterInputRowEmployeeSelect;
    }

    private static function build_roster_input_row_comment($Roster, $day_iterator, $roster_row_iterator, $tabindex) {
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
                . gettext("Comment") . ":&nbsp;<input type=text name=Roster[$day_iterator][$roster_row_iterator][comment] value='$comment' tabindex='$tabindex'></div>\n";
        return $roster_input_row_comment_html;
    }

    public static function build_roster_readonly_branch_table_rows(array $Branch_roster, int $branch_id, string $date_sql_start, string $date_sql_end, $Options = NULL) {
        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();

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
            $table_html .= "<tr class='branch-roster-title-tr'><th colspan=";
            $table_html .= htmlspecialchars($number_of_days) . ">";
            $table_html .= $List_of_branch_objects[$branch_id]->getShortName();
            $table_html .= " in " . $List_of_branch_objects[$other_branch_id]->getShortName() . "</th></tr>";
            $table_html .= build_html_roster_views::build_roster_readonly_table($Branch_roster[$other_branch_id], $other_branch_id, $Options);
        }
        return $table_html;
    }

    public static function build_roster_read_only_table_head($Roster, $Options = array()) {
        $head_table_html = "";
        $head_table_html .= "<thead>\n";
        $head_table_html .= "<tr>\n";

        $configuration = new \PDR\Application\configuration();
        $locale = $configuration->getLanguage();
        $weekdayFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        $weekdayFormatter->setPattern('EEEE'); // 'EEEE' represents the full weekday name

        foreach (array_keys($Roster) as $date_unix) {//Datum
            $date_sql = date('Y-m-d', $date_unix);
            $head_table_html .= "<td>";
            $head_table_html .= "<a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/roster-day-read.php?datum=$date_sql'>";
            $weekday_string = $weekdayFormatter->format($date_unix);
            $head_table_html .= $weekday_string;
            $head_table_html .= " \n";
            $dateObject = new DateTime($date_sql);
            $dateString = $dateObject->format("d.m.");
            $head_table_html .= $dateString;
            $holiday = holidays::is_holiday($date_unix);
            if (FALSE !== $holiday) {
                $head_table_html .= "<br>$holiday";
            }
            $head_table_html .= "</a>";
            if (in_array(self::OPTION_SHOW_CALENDAR_WEEK, $Options)) {
                $weekNumber = $dateObject->format('W');
                $head_table_html .= "<br><a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/roster-week-table.php?datum=" . $date_sql . "'>" . gettext("calendar week") . $weekNumber . "</a>\n";
            }

            if (TRUE === PDR\Database\EmergencyServiceDatabaseHandler::isOurServiceDay($dateObject)) {
                $emergencyService = PDR\Database\EmergencyServiceDatabaseHandler::readEmergencyServiceOnDate($dateObject);
                $head_table_html .= "<br><em>" . gettext("EMERGENCY SERVICE") . "</em><br>";
                if (in_array(self::OPTION_SHOW_EMERGENCY_SERVICE_NAME, $Options)) {
                    $head_table_html .= $emergencyService->getEmployeeLastName();
                    $head_table_html .= " / " . $emergencyService->getBranchNameShort();
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
        $first_day = new DateTime;
        $last_day = new DateTime;
        $first_day->setTimestamp(min(array_keys($Roster)));
        $last_day->setTimestamp(max(array_keys($Roster)));
        $workforce = new workforce($first_day->format('Y-m-d'), $last_day->format('Y-m-d'));

        global $config;
        $table_html = "";
        $table_html .= "<tbody>";

        $max_employee_count = roster::calculate_max_employee_count($Roster);

        for ($table_row_iterator = 0; $table_row_iterator < $max_employee_count; $table_row_iterator++) {
            $table_html .= "<tr>\n";
            foreach (array_keys($Roster) as $date_unix) {
                $date_sql = date('Y-m-d', $date_unix);
                if (!isset($Roster[$date_unix][$table_row_iterator]) or NULL === $Roster[$date_unix][$table_row_iterator]->employee_key) {
                    $table_html .= "<td><!--No more data in roster--></td>\n";
                    continue;
                }
                $roster_item = $Roster[$date_unix][$table_row_iterator];
                /*
                 * The following lines check for the state of approval.
                 * Duty rosters have to be approved by the leader, before the staff can view them.
                 */
                $approval = roster_approval::get_approval($date_sql, $branch_id);
                if ("approved" !== $approval and TRUE == $config['hide_disapproved']) {
                    $table_html .= "<td><!--Hidden because not approved--></td>";
                    continue;
                }
                $table_html .= "<td>";
                $zeile = "";
                $zeile .= "<span class='employee-and-hours-and-duty-time'><span class='employee-and-hours'><b><a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/roster-employee-table.php?"
                        . "datum=" . htmlspecialchars($roster_item->date_sql)
                        . "&employee_key=" . htmlspecialchars($roster_item->employee_key)
                        . "' data-employee_key='" . htmlspecialchars($roster_item->employee_key)
                        . "' data-employeeFullName='" . htmlspecialchars($workforce->getEmployeeFullName($roster_item->employee_key))
                        . "' data-branch_id='" . htmlspecialchars($roster_item->branch_id)
                        . "' data-date_sql='" . htmlspecialchars($roster_item->date_sql)
                        . "'>";
                if (isset($workforce->List_of_employees[$roster_item->employee_key]->last_name)) {
                    $zeile .= $workforce->List_of_employees[$roster_item->employee_key]->last_name;
                } else {
                    $zeile .= gettext("Unknown employee") . ":" . $roster_item->employee_key;
                }
                $zeile .= "</a></b> / <span class='roster_working_hours'>";
                $zeile .= htmlspecialchars($roster_item->working_hours);
                $zeile .= "&nbsp;h</span><!-- roster_working_hours --></span><!-- employee-and-hours --> ";
                if (isset($Options['space_constraints']) and 'narrow' === $Options['space_constraints']) {
                    $zeile .= " <br> ";
                } else {
                    $zeile .= "<span class='horizontal-spacer'></span>";
                }
                /*
                 * start and end of duty
                 */
                $zeile .= "<span class='duty-time'>";
                $zeile .= self::build_roster_readonly_table_add_time($roster_item, 'duty_start_sql');
                $zeile .= " - ";
                $zeile .= self::build_roster_readonly_table_add_time($roster_item, 'duty_end_sql');
                if (!empty($roster_item->comment)) {
                    /*
                     * In case, there is a comment available, add a hint in form of a single letter.
                     * That single letter is the first letter of the word Comment (in the chosen language).
                     */
                    $zeile .= '&nbsp;' . '<sup>' . mb_substr(gettext('Comment'), 0, 1) . '</sup>';
                }
                $zeile .= "</span><!-- class='duty-time'--></span><!-- employee-and-hours-and-duty-time -->";
                /*
                 * start and end of break
                 */
                if (isset($Options['space_constraints']) and 'narrow' === $Options['space_constraints']) {
                    $zeile .= "<br>\n";
                } else {
                    $zeile .= "<span class='horizontal-spacer'></span>";
                }
                if ($roster_item->break_start_int > 0) {
                    $zeile .= "<span class='break-time'>";
                    $zeile .= " " . gettext("break") . ": ";
                    $zeile .= "<span class='time'>" . htmlspecialchars($roster_item->break_start_sql) . "</span>";
                    $zeile .= " - ";
                    $zeile .= "<span class='time'>" . htmlspecialchars($roster_item->break_end_sql) . "</span>";
                    $zeile .= "</span><!-- class='break-time' -->";
                }
                $table_html .= $zeile;
                $table_html .= "</td>\n";
            }
        }
        $table_html .= "</tr>\n";
        $table_html .= "</tbody>\n";

        return $table_html;
    }

    private static function build_roster_readonly_table_add_time($roster_item, $parameter) {
        if (self::equals_principle_roster($roster_item, $parameter)) {
            $html = "<span class='time'>" . htmlspecialchars($roster_item->$parameter) . "</span>";
        } else {
            $html = "<span class='time'><strong>" . htmlspecialchars($roster_item->$parameter) . "</strong></span>";
        }
        return $html;
    }

    public static function build_roster_readonly_employee_table($Roster, $branch_id) {
        if (array() === $Roster) {
            return FALSE;
        }
        $network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();

        global $config;
        $table_html = "";
        $table_html .= "<tbody>";

        $max_employee_count = roster::calculate_max_employee_count($Roster);
        $List_of_date_unix_in_roster = array_keys($Roster);
        $date_start_object = new DateTime();
        $date_start_object->setTimestamp(min($List_of_date_unix_in_roster));
        $date_end_object = new DateTime();
        $date_end_object->setTimestamp(max($List_of_date_unix_in_roster));

        for ($table_row_iterator = 0; $table_row_iterator < $max_employee_count; $table_row_iterator++) {
            $table_html .= "<tr>\n";
            foreach (array_keys($Roster) as $date_unix) {
                $date_sql = date('Y-m-d', $date_unix);
                if (!isset($Roster[$date_unix][$table_row_iterator]) or NULL === $Roster[$date_unix][$table_row_iterator]->employee_key) {
                    $table_html .= "<td><!--No more data in roster--></td>\n";
                    continue;
                }
                $roster_item = $Roster[$date_unix][$table_row_iterator];
                /*
                 * The following lines check for the state of approval.
                 * Duty rosters have to be approved by the leader, before the staff can view them.
                 */
                $approval = roster_approval::get_approval($date_sql, $branch_id);
                if ("approved" !== $approval and false != $config['hide_disapproved']) {
                    $table_html .= "<td><!--Hidden because not approved--></td>";
                    continue;
                }
                $table_html .= "<td class=roster_employee_table_cell>";
                $zeile = "";

                $zeile .= "<span class='duty-time'>";
                $zeile .= self::build_roster_readonly_table_add_time($roster_item, 'duty_start_sql');
                $zeile .= " - ";
                $zeile .= self::build_roster_readonly_table_add_time($roster_item, 'duty_end_sql');
                $zeile .= " / <span class='roster_working_hours'>";
                $zeile .= htmlspecialchars($roster_item->working_hours);
                $zeile .= "&nbsp;h</span><!-- roster_working_hours -->";

                if (!empty($roster_item->comment)) {
                    /*
                     * In case, there is a comment available, add a hint in form of a single letter.
                     * That single letter is the first letter of the word Comment (in the chosen language).
                     */
                    $zeile .= '&nbsp;' . '<sup>' . mb_substr(gettext('Comment'), 0, 1) . '</sup>';
                }
                $zeile .= "</span><!-- class='duty-time'--></span><!-- employee-and-hours-and-duty-time -->";
                $zeile .= "<br>\n";
                if ($roster_item->break_start_int > 0) {
                    $zeile .= "<span class='break-time'>";
                    $zeile .= " " . gettext("break") . ": ";
                    $zeile .= "<span class='time'>" . htmlspecialchars($roster_item->break_start_sql) . "</span>";
                    $zeile .= " - ";
                    $zeile .= "<span class='time'>" . htmlspecialchars($roster_item->break_end_sql) . "</span>";
                    $zeile .= "</span><!-- class='break-time' -->";
                }
                $zeile .= "<br>";
                $zeile .= "<span class='branch_name' data-branch_id='" . $roster_item->branch_id . "'>";
                $zeile .= htmlspecialchars($List_of_branch_objects[$roster_item->branch_id]->getShortName());
                $zeile .= "</span>";
                $table_html .= $zeile;
                $table_html .= "</td>\n";
            }
        }
        $table_html .= "</tr>\n";
        $table_html .= "</tbody>\n";

        return $table_html;
    }

    public static function build_roster_working_week_hours_div(\sessions $session, \DateTime $dateObject, array $WorkingWeekHoursHave, array $Working_week_hours_should, \Workforce $workforce, array $Options = NULL) {
        $weekHoursTableFormsHtml = "";

        if (array() === $WorkingWeekHoursHave) {
            return FALSE;
        }
        $weekHoursTableHtml = "<div id=weekHoursTableDiv>\n";
        $weekHoursTableHtml .= '<H2>' . gettext('Hours per week') . "</H2>\n";
        $weekHoursTableHtml .= "<table class='tight'>";
        $weekHoursTableHtml .= "<tr>";
        $weekHoursTableHtml .= "<th>" . gettext('Employee') . "</th>";
        $weekHoursTableHtml .= "<th>" . gettext('Actual') . "</th>";
        $weekHoursTableHtml .= "<th>" . gettext('Target') . "</th>";
        $weekHoursTableHtml .= "<th>" . gettext('Deviation') . "</th>";
        $weekHoursTableHtml .= "</tr>";
        foreach ($WorkingWeekHoursHave as $employeeKey => $workingHoursHave) {
            if (isset($Options['employee_key']) and (int) $employeeKey !== (int) $Options['employee_key']) {
                continue; /* Only the specified employees are shown. */
            }
            $weekHoursTableHtml .= "<tr>";
            $weekHoursTableHtml .= "<td>";
            if (isset($workforce->List_of_employees[$employeeKey]->last_name)) {
                $weekHoursTableHtml .= $workforce->List_of_employees[$employeeKey]->last_name;
            } else {
                $weekHoursTableHtml .= gettext("Unknown employee") . ":" . $employeeKey;
            }
            $weekHoursTableHtml .= "</td>";
            $weekHoursTableHtml .= "<td>" . round($workingHoursHave * 4, 0) / 4;
            $weekHoursTableHtml .= " </td><td> ";
            if (isset($Working_week_hours_should[$employeeKey])) {
                $workingHoursShould = $Working_week_hours_should[$employeeKey];
                $weekHoursTableHtml .= round($workingHoursShould, 1) . "\n";
                $difference = $workingHoursHave - $workingHoursShould;
            } else {
                $weekHoursTableHtml .= "???" . "\n";
                $difference = 0;
            }
            $weekHoursTableHtml .= "</td>\n";
            $weekHoursTableHtml .= "<td>\n";
            $weekHoursTableHtml .= "<b>" . (round($difference * 4, 0) / 4) . "</b>\n";
            $weekHoursTableHtml .= "</td>\n";
            if ($session->user_has_privilege(sessions::PRIVILEGE_CREATE_OVERTIME)) {
                $weekHoursTableHtml .= "<td>\n";
                $formId = "storeOvertimeData" . $employeeKey;
                $weekHoursTableHtml .= "<button class='button-small no-print' type=submit form='$formId' title='" . gettext("Save overtime") . "'><img src='../../../img/md_save.svg'></button>\n";
                $weekHoursTableHtml .= "</td>\n";
                $weekHoursTableFormsHtml .= "<form method='post' id=$formId>" . PHP_EOL; //prepare forms to be appended.
                $weekHoursTableFormsHtml .= "<input type='hidden' form='$formId' name='employeeKey' value='$employeeKey'>" . PHP_EOL;
                $weekHoursTableFormsHtml .= "<input type='hidden' form='$formId' name='workingHoursHave' value='$workingHoursHave'>" . PHP_EOL;
                $weekHoursTableFormsHtml .= "<input type='hidden' form='$formId' name='workingHoursShould' value='$workingHoursShould'>" . PHP_EOL;
                $weekHoursTableFormsHtml .= "<input type='hidden' form='$formId' name='difference' value='$difference'>" . PHP_EOL;
                $weekHoursTableFormsHtml .= "<input type='hidden' form='$formId' name='date' value=" . $dateObject->format("Y-m-d") . ">" . PHP_EOL;
                $weekHoursTableFormsHtml .= "</form>" . PHP_EOL;
            }
            $weekHoursTableHtml .= "</tr>\n";
        }
        $weekHoursTableHtml .= "</table>";
        $weekHoursTableHtml .= $weekHoursTableFormsHtml;

        $weekHoursTableHtml .= "</div>"; // id=weekHoursTableDiv
        return $weekHoursTableHtml;
    }

    private static function calculate_working_hours_employee_should(array $Roster, employee $employee_object) {
        $Working_hours_day_should = 0;
        foreach (array_keys($Roster) as $date_unix) {
            $date_sql = date('Y-m-d', $date_unix);
            $date_object = new DateTime;
            $date_object->setTimestamp($date_unix);
            $absenceCollection = PDR\Database\AbsenceDatabaseHandler::readAbsenteesOnDate($date_sql);
            $Working_hours_day_should += self::calculateWorkingHoursDayEmployeeShould($date_object, $employee_object, $absenceCollection);
        }
        return $Working_hours_day_should;
    }

    /**
     * Calculate the expected working hours for an employee on a given date.
     *
     * @param DateTime $dateObject - The date for which to calculate working hours.
     * @param employee $employeeObject - The employee for whom to calculate working hours.
     * @param PDR\Roster\AbsenceCollection $absenceCollection - Collection of absences for the employee.
     * @return float - The calculated working hours for the employee on the specified date.
     * @todo <p lang=de>Die Berechnung muss komplett umgestellt werden.
     *  Statt die Sollstunden herunterzurechnen, müssen die Iststunden hoch gerechnet werden.</p>
     */
    private static function calculateWorkingHoursDayEmployeeShould(DateTime $dateObject, employee $employeeObject, PDR\Roster\AbsenceCollection $absenceCollection): float {
        if ($absenceCollection->containsEmployeeKey($employeeObject->get_employee_key())) {
            /**
             * Those who are absent do not have to work.
             * Exception: Those who reduce overtime REASON_TAKEN_OVERTIME are credited with target hours.
             */
            /**
             * @var $List_of_non_respected_absence_reason_ids
             * @see absence::$List_of_absence_reasons for a full list of absence reason ids (paid and unpaid)
             */
            $ListOfNonRespectedAbsenceReasonIds = array(\PDR\Utility\AbsenceUtility::REASON_TAKEN_OVERTIME);

            if (!in_array(
                            $absenceCollection->getAbsenceByEmployeeKey($employeeObject->get_employee_key())->getReasonId(),
                            $ListOfNonRespectedAbsenceReasonIds)) {
                return 0;
            }
        }
        /**
         *  Check if it's a holiday; no work is required on holidays.
         */
        if (FALSE !== holidays::is_holiday($dateObject)) {
            return 0;
        }
        /**
         *  Check for a special case where the employee works only on specific days (e.g., Tue/Thu).
         *  TODO: Consider handling scenarios when a holiday falls on a Friday.
         *  Is it fair to treat such employees differently?
         */
        if (roster::is_empty_roster_day_array($employeeObject->get_principle_roster_on_date($dateObject))
                and !empty($employeeObject->working_week_days)) {
            return 0;
        }
        if (empty($employeeObject->working_week_days)) {
            /*
             * In case we do not know the exact working_week_days we guess is must be 5.
             * This happens, if there are no days in the principle roster for this employee.
             */
            return $employeeObject->working_week_hours / self::NUMBER_OF_BUSINESS_DAYS;
        }
        return $employeeObject->working_week_hours / $employeeObject->working_week_days;
    }

    public static function calculate_working_week_hours_should(array $Roster, workforce $workforce) {

        foreach ($workforce->List_of_employees as $employee_object) {
            $Working_hours_employee_should = self::calculate_working_hours_employee_should($Roster, $employee_object);
            $Working_week_hours_should[$employee_object->get_employee_key()] = $Working_hours_employee_should;
        }
        return $Working_week_hours_should;
    }

    public static function equals_principle_roster(roster_item $roster_item, string $parameter) {
        $workforce = new workforce($roster_item->date_sql);
        $employee_key = $roster_item->employee_key;
        if (!isset($workforce->List_of_employees[$employee_key])) {
            return FALSE;
        }
        $Principle_roster_on_date = $workforce->List_of_employees[$employee_key]->get_principle_roster_on_date($roster_item->date_object);
        if (null === $Principle_roster_on_date) {
            return FALSE;
        }
        foreach ($Principle_roster_on_date as $principle_roster_item) {
            if ($principle_roster_item->$parameter == $roster_item->$parameter) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
