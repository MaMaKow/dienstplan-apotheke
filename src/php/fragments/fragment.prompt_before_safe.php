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
$session->exit_on_missing_privilege(sessions::PRIVILEGE_CREATE_ROSTER);
if (!filter_has_var(INPUT_POST, 'Roster')) {
    /*
     * No data has been sent.
     * We will redirect back so somewhere.
     */
    $url = PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/principle-roster-day.php";
    /*
     * The url is a wild guess.
     * It might be principle-roster-employee.php
     */
    if (!headers_sent()) {
        header("Status: 307 Temporary Redirect");
        header("Location: $url");
    } else {
        echo '<script type="javascript">document.location.href="' . $url . '";</script>';
    }
    exit();
}

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

function get_list_of_branch_ids(array $Roster) {
    $List_of_branch_ids = array();
    foreach ($Roster as $date_unix => $Roster_day_array) {
        foreach ($Roster_day_array as $roster_row_iterator => $roster_item) {
            if (NULL === $roster_item->branch_id) {
                continue;
            }
            $List_of_branch_ids[$roster_item->branch_id] = $roster_item->branch_id;
        }
    }
    return $List_of_branch_ids;
}

function build_difference_string(array $List_of_differences, array $List_of_deleted_roster_primary_keys, array $Principle_roster_new, array $Principle_roster_old) {
    /*
     *   TODO: Also mention the deletions inside $List_of_deleted_roster_primary_keys somewhere
     *  CAVE: Perhaps we should use another function for this. This function is already very crowded.
     */
    $difference_string = "";
    $Weekday_names = localization::get_weekday_names();
    foreach ($List_of_differences as $date_unix => $Employee_ids) {
        $workforce = new workforce(date('Y-m-d', $date_unix));
        foreach ($Employee_ids as $employee_id) {
            foreach ($Principle_roster_new[$date_unix] as $roster_row_iterator_new => $roster_item_new) {
                if ($roster_item_new->employee_id !== $employee_id) {
                    //throw new Exception('The employee_id does not match!');
                    continue;
                }
                foreach ($Principle_roster_old[$date_unix] as $roster_row_iterator_old => $roster_item_old) {
                    if ($roster_row_iterator_new !== $roster_row_iterator_old) {
                        /*
                         * TODO: Are there cases, where we should inspect these in more depth?
                         */
                        continue;
                    }

                    $difference_string .= "<div class='inline_block_element' style='vertical-align: top; margin-right: 1em;'>";
                    $difference_string .= $workforce->List_of_employees[$employee_id]->last_name . '<br>';
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
                        $network_of_branch_offices = new network_of_branch_offices;
                        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
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
$valid_from = user_input::get_variable_from_any_input('valid_from', FILTER_SANITIZE_STRING, NULL);
$List_of_employee_ids = get_list_of_employee_ids($Principle_roster_new);
$List_of_branch_ids = get_list_of_branch_ids($Principle_roster_new);

require '../../../head.php';
echo "<main>";
$date_start_object = new DateTime;
$date_start_object->setTimestamp(min(array_keys($Principle_roster_new)));
$date_end_object = new DateTime;
$date_end_object->setTimestamp(max(array_keys($Principle_roster_new)));
if (1 === count($List_of_employee_ids)) {
    $referrer_url = '../pages/principle-roster-employee.php';
    /*
     * TODO: Make this work for multiple employees:
     * Maybe make a foreach loop. Also try to encapsulate into some functions
     */
    $employee_id = current($List_of_employee_ids);
    $Principle_roster_old = principle_roster::read_current_principle_employee_roster_from_database($employee_id, clone $date_start_object, clone $date_end_object);
    $alternating_week_id = alternating_week::get_alternating_week_for_date($date_start_object);
    $earliest_allowed_valid_from = max(principle_roster::get_list_of_employee_change_dates($employee_id, $alternating_week_id));
} else if (1 === count($List_of_branch_ids)) {
    $referrer_url = '../pages/principle-roster-day.php';
    $branch_id = current($List_of_branch_ids);
    $Principle_roster_old = principle_roster::read_current_principle_roster_from_database($branch_id, clone $date_start_object, clone $date_end_object);
    $alternating_week_id = alternating_week::get_alternating_week_for_date($date_start_object);
    $earliest_allowed_valid_from = max(principle_roster_history::get_list_of_change_dates($alternating_week_id));
} else {
    throw new Exception('This case has not yet been implemented. You seem to have submitted multiple branches and multiple employees at the same time.');
}
$List_of_changes = user_input::get_changed_roster_employee_id_list($Principle_roster_new, $Principle_roster_old);
$List_of_deleted_roster_primary_keys = user_input::get_deleted_roster_primary_key_list($Principle_roster_new, $Principle_roster_old);
if (array() !== $List_of_changes or array() !== $List_of_deleted_roster_primary_keys) {
    /*
     * Something has changed between the last roster and the new roster.
     */
    /*
     * Parameters to be sent back to the principle roster page.
     * We use the session variable, so we do not have to trust user data from POST:
     */
    $_SESSION['Principle_roster_from_prompt'] = $Principle_roster_new;
    $_SESSION['List_of_changes'] = $List_of_changes;
    $_SESSION['List_of_deleted_roster_primary_keys'] = $List_of_deleted_roster_primary_keys;
    if (alternating_week::alternations_exist()) {
        /*
         * TODO: Create an option to take the changes to other alternations:
         */
        echo "<p>";
        echo sprintf(gettext('The %1$s will be changed.'), alternating_week::get_human_readable_string($alternating_week_id));
        echo "</p>";

        echo \build_difference_string($List_of_changes, $List_of_deleted_roster_primary_keys, $Principle_roster_new, $Principle_roster_old);
    }
    echo "<form id='principle_roster_prompt_before_safe' method='post' action='$referrer_url'>";
    echo "</form>";
    echo "<hr>";
    echo "<p>";
    echo gettext("When should the changes come into force?");
    echo "</p>";
    /*
     * valid from:
     */
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
echo "</main>";
