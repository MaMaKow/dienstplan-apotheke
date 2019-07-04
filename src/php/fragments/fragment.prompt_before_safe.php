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
require '../../../default.php';

function get_list_of_employee_ids(array $Roster) {
    $List_of_employee_ids = array();
    foreach ($Roster as $date_unix => $Roster_day_array) {
        foreach ($Roster_day_array as $roster_row_iterator => $roster_item) {
            if (NULL === $roster_item->employee_id) {
                continue;
            }
            $List_of_employee_ids[$roster_item->employee_id] = $roster_item->employee_id;
        }
    }
    return $List_of_employee_ids;
}

function build_difference_string(array $List_of_differences, array $Principle_roster_new, array $Principle_roster_old) {
    $difference_string = "";
    $Weekday_names = build_html_navigation_elements::get_weekday_names();
    foreach ($List_of_differences as $date_unix => $Employee_ids) {
        foreach ($Employee_ids as $employee_id) {
            foreach ($Principle_roster_new[$date_unix] as $roster_row_iterator_new => $roster_item_new) {
                if ($roster_item_new->employee_id !== $employee_id) {
                    throw new Exception('The employee_id does not match!');
                }
                foreach ($Principle_roster_old[$date_unix] as $roster_row_iterator_old => $roster_item_old) {
                    if ($roster_row_iterator_new !== $roster_row_iterator_old) {
                        /*
                         * TODO: Are there cases, where we should inspect these in more depth?
                         */
                        continue;
                    }

                    $difference_string .= "<div class='inline_block_element' style='vertical-align: top; margin-right: 1em;'>";
                    $difference_string .= $Weekday_names[$roster_item_new->date_object->format('N')]
                            . "<br>";
                    if ($roster_item_new->duty_start_sql !== $roster_item_old->duty_start_sql) {
                        $difference_string .= gettext("duty start")
                                . "<br>"
                                . $roster_item_old->duty_start_sql . "&nbsp;&rarr;&nbsp;" . $roster_item_new->duty_start_sql
                                . "<br>";
                    }
                    if ($roster_item_new->duty_end_sql !== $roster_item_old->duty_end_sql) {
                        $difference_string .= gettext("duty end")
                                . "<br>"
                                . $roster_item_old->duty_end_sql . "&nbsp;&rarr;&nbsp;" . $roster_item_new->duty_end_sql
                                . "<br>";
                    }
                    if ($roster_item_new->break_start_sql !== $roster_item_old->break_start_sql) {
                        $difference_string .= gettext("break start")
                                . "<br>"
                                . $roster_item_old->break_start_sql . "&nbsp;&rarr;&nbsp;" . $roster_item_new->break_start_sql
                                . "<br>";
                    }
                    if ($roster_item_new->break_end_sql !== $roster_item_old->break_end_sql) {
                        $difference_string .= gettext("break end")
                                . "<br>"
                                . $roster_item_old->break_end_sql . "&nbsp;&rarr;&nbsp;" . $roster_item_new->break_end_sql
                                . "<br>";
                    }
                    if ($roster_item_new->branch_id !== $roster_item_old->branch_id) {
                        $List_of_branch_objects = branch::get_list_of_branch_objects();
                        print_debug_variable($roster_item_old->branch_id, $roster_item_new->branch_id);
                        $difference_string .= gettext("branch")
                                . "<br>"
                                . $List_of_branch_objects[$roster_item_old->branch_id]->short_name
                                . "&nbsp;&rarr;&nbsp;"
                                . $List_of_branch_objects[$roster_item_new->branch_id]->short_name
                                . "<br>";
                    }
                    $difference_string .= "</div>";
                }
            }
        }
    }
    return $difference_string;
}

$Principle_roster_new = user_input::get_Roster_from_POST_secure();
if (FALSE !== $Principle_roster_new) {
    //print_debug_variable($_POST);
}
$List_of_employee_ids = get_list_of_employee_ids($Principle_roster_new);

require '../../../head.php';
echo "<main>";
if (1 === count($List_of_employee_ids)) {
    $employee_id = current($List_of_employee_ids);
    $date_start_object = new DateTime;
    $date_start_object->setTimestamp(min(array_keys($Principle_roster_new)));
    $date_end_object = new DateTime;
    $date_end_object->setTimestamp(max(array_keys($Principle_roster_new)));
    $Principle_roster_old = principle_roster::read_current_principle_employee_roster_from_database($employee_id, $date_start_object, $date_end_object);
    $List_of_differences = user_input::get_changed_roster_employee_id_list($Principle_roster_new, $Principle_roster_old);
    if (array() !== $List_of_differences) {
        /*
         * Something has changed between the last roster and the new roster.
         */
        if (alternating_week::alternations_exist()) {
            $alternation_id = alternating_week::get_alternating_week_for_date($date_start_object);
            /*
             * TODO: Create an option to take the changes nto other alternations:
              $List_of_principle_rosters = alternating_week::get_list_of_principle_rosters($employee_id);
              $Differences_between_principle_rosters = alternating_week::find_differences_between_principle_rosters($List_of_principle_rosters, $alternation_id);
              //print_debug_variable($Differences_between_principle_rosters);
              //$comparison_string = build_comparison_string($Differences_between_principle_rosters);
             */
            echo "<p>";
            echo sprintf(gettext('The %1s will be changed.'), alternating_week::get_human_readably_string($alternation_id));
            echo "</p>";

            echo build_difference_string($List_of_differences, $Principle_roster_new, $Principle_roster_old);
        }
        /*
         * Parameters to be sent back to the principle roster page.
         * TODO: Use the session variable maybe? So we do not have to trust user data from POST?
         */
        //$valid_from_from_post = user_input::get_variable_from_any_input('valid_from', FILTER_SANITIZE_STRING);
        $_SESSION['Principle_roster_from_prompt'] = $Principle_roster_new;
        $_SESSION['List_of_differences'] = $List_of_differences;
        echo "<form id='principle_roster_prompt_before_safe' method='post' action='../pages/principle-roster-employee.php'>";
        //echo "<input type=hidden name='valid_from' value=$valid_from_from_post>";
        echo "</form>";
        echo "<hr>";
        echo "<p>";
        echo gettext("When should the changes come into force?");
        echo "</p>";
        /*
         * valid from:
         */
        $earliest_allowed_valid_from = max(principle_roster::get_list_of_change_dates($employee_id, $alternation_id));
        $suggested_valid_from = max($earliest_allowed_valid_from, $date_start_object);
        $step = 7 * count(alternating_week::get_alternating_week_ids());
        echo "<input name='valid_from' type='date' form='principle_roster_prompt_before_safe' "
        //. "step='$step' min='" . $earliest_allowed_valid_from->format('Y-m-d') . "' "
        . "step='$step' "
        . "value='" . $suggested_valid_from->format('Y-m-d') . "'>";
        echo "<hr>";
        /*
         * buttons:
         */
        echo build_html_navigation_elements::build_button_submit('principle_roster_prompt_before_safe');
        echo build_html_navigation_elements::build_button_back();
    } else {
        echo "<p>";
        echo gettext('There are no changes.');
        echo " ";
        echo gettext('You will be sent <a href="javascript:history.back()">back</a>.');
        echo "</p>";
        echo "<script>setTimeout(function() {window.history.back();}, 10000);</script>";
    }
}

echo "</main>";
