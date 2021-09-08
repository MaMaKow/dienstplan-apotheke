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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
require '../../../default.php';

$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_object']->employee_id);
create_cookie('employee_id', $employee_id, 30);
$workforce = new workforce();
/**
 * @todo <p lang="en">
 * This page is too slow.
 * Find out why!
 * Make it better, increase the speed!
 * </p>
 *
 */
if (filter_has_var(INPUT_POST, 'submit_roster')) {
    if (!$session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) {
        return FALSE;
    }

    if (isset($_SESSION['Principle_roster_from_prompt'])) {
        $Principle_roster_new = $_SESSION['Principle_roster_from_prompt'];
        $List_of_deleted_roster_primary_keys = $_SESSION['List_of_deleted_roster_primary_keys'];
        $List_of_changes = $_SESSION['List_of_changes'];
        unset($_SESSION['Principle_roster_from_prompt']);
        unset($_SESSION['List_of_deleted_roster_primary_keys']);
        unset($_SESSION['List_of_changes']);
        $valid_from_input = new DateTime(filter_input(INPUT_POST, 'valid_from', FILTER_SANITIZE_STRING));
        /*
         * Find a correct date for the change.
         * It should be the first monday in the relevant alternating_week_id week, after the given date.
         */
        $some_date_from_input = (new DateTime())->setTimestamp(min(array_keys($Principle_roster_new))); //This should probably be a monday.
        $valid_from = ( new alternating_week(
                        alternating_week::get_alternating_week_for_date($some_date_from_input))
                )->get_monday_date_for_alternating_week(clone $valid_from_input);
        principle_roster::insert_changed_entries_into_database($Principle_roster_new, $List_of_changes);
        principle_roster::invalidate_removed_entries_in_database($List_of_deleted_roster_primary_keys, $valid_from->format('Y-m-d'));
    }
}


//Produziere die Ausgabe
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
?>
<a name=top></a>
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_roster');
$html_text = '';
$html_text .= "<div id=main-area>\n";
//TODO: find out how to respect the lunch breaks!
$html_text .= build_html_navigation_elements::build_select_employee($employee_id, $workforce->List_of_employees);

function build_change_principle_roster_employee_form(int $alternating_week_id, string $date_minimum = NULL, int $employee_id, bool $hide_this_form) {
    $alternating_week = new alternating_week($alternating_week_id);
    if (NULL !== $date_minimum) {
        $valid_from_object = new DateTime($date_minimum);
        $valid_from_string_sql = $valid_from_object->format('Y-m-d');
        $valid_from_string_human = $valid_from_object->format('d.m.Y');
    } else {
        $valid_from_object = workforce::get_first_start_of_employment($employee_id);
        $valid_from_string_sql = $valid_from_object->format('Y-m-d');
        $valid_from_string_human = gettext('Start of employment');
    }
    $pseudo_date_start_object = $alternating_week->get_monday_date_for_alternating_week(clone $valid_from_object);
    $pseudo_date_end_object = clone $pseudo_date_start_object;
    $pseudo_date_end_object->add(new DateInterval('P6D'));

    $Principle_employee_roster = principle_roster::read_current_principle_employee_roster_from_database($employee_id, clone $pseudo_date_start_object, clone $pseudo_date_end_object);
    /*
     * TODO: We might stop using a transfer for the lunch breaks.
     *   This means, that the breaks will not magically appear in the principle-roster-employee.php
     *   They will however probably be automaticcaly be inserted in the daily view.
     *   This might bring up some confusion. Should I give up calculating probably lunch_breaks alltogether?
     *     If so, should there be a warning/notice if the law wants a break to be given?
     */
    $workforce = new workforce($pseudo_date_start_object->format('Y-m-d'), $pseudo_date_end_object->format('Y-m-d'));
    $branch_id = $workforce->List_of_employees[$employee_id]->principle_branch_id;
    /*
     * @todo Take care to write a warning if lunch breaks are not given.
     */
    $form_id = 'change_principle_roster_employee_form_' . $alternating_week_id . '_' . $pseudo_date_start_object->format('Y-m-d');
    $html_text = '';
    $form_is_hidden_string = '';
    if (TRUE === $hide_this_form) {
        $form_is_hidden_string = "hidden";
    }
    $html_text .= "<form method='POST' id='$form_id' class='change_principle_roster_employee_form $form_is_hidden_string' action='../fragments/fragment.prompt_before_safe.php'>";
    $html_text .= "<input type=hidden name='valid_from' value='" . $valid_from_string_sql . "'>";
    $html_text .= build_html_navigation_elements::build_button_submit($form_id);
    if (alternating_week::alternations_exist()) {
        $monday_date = clone $alternating_week->get_monday_date_for_alternating_week();
        $sunday_date = clone $monday_date;
        $sunday_date->add(new DateInterval('P6D'));
        $alternating_week_id_string = '<div class="inline_block_element"><p>'
                . alternating_week::get_human_readable_string($alternating_week_id)
                . '<br> '
                . gettext('valid from') . ' ' . $valid_from_string_human
                . '<br> '
                . gettext('e.g.') . ' '
                . gettext('calendar week') . ' ' . $monday_date->format('W')
                . '<br> '
                . $monday_date->format('d.m.Y') . ' - ' . $sunday_date->format('d.m.Y')
                . '</p></div>';
        $html_text .= $alternating_week_id_string;
    } else {
        $alternating_week_id_string = '<div class="inline_block_element"><p>'
                . gettext('valid from') . ' ' . $valid_from_string_human
                . '</p></div>';
        $html_text .= $alternating_week_id_string;
    }
    $html_text .= "<script> "
            . " var Roster_array = " . json_encode($Principle_employee_roster) . ";\n"
            . " var List_of_employee_names = " . json_encode($workforce->get_list_of_employee_names()) . ";\n"
            . "</script>\n";

    $html_text .= "<table>\n";
    $html_text .= "<thead>\n";
    $html_text .= "<tr>\n";
    $Weekday_names = localization::get_weekday_names();
    foreach ($Weekday_names as $weekday_name) {
        //Wochentag
        $html_text .= "<td width=10%>";
        $html_text .= $weekday_name;
        $html_text .= "</td>\n";
    }
    $max_employee_count = roster::calculate_max_employee_count($Principle_employee_roster);
    $html_text .= "<tbody>\n";
    for ($table_input_row_iterator = 0; $table_input_row_iterator < $max_employee_count; $table_input_row_iterator++) {
        $html_text .= "<tr>\n";
        foreach (array_keys($Principle_employee_roster) as $day_iterator) {
            $html_text .= build_html_roster_views::build_roster_input_row($Principle_employee_roster, $day_iterator, $table_input_row_iterator, $max_employee_count, $branch_id, array('add_select_branch', 'add_hidden_employee' => $employee_id));
        }
        $html_text .= "</tr>\n";
    }
    $html_text .= "</tr>\n";
    /*
     * TODO: Write JavaScript Code to allow adding more rows to the form
      echo "<tr>";
      foreach (array_keys($Principle_roster) as $date_unix) {
      //TODO: Write Javascript for adding an entry:
      echo "<td id='add_entry_$wochentag'><p><a href='#' onclick='alert(\"Sorry, this feature is not yet implemented.\");add_entry_to_change_principle_roster_employee_form()'>" . gettext("Add row") . "</a></p></td>";
      }
      echo "</tr>";
     *
     */
    $html_text .= "</tbody>\n";
    $html_text .= "<tfoot>\n";
    $html_text .= "<tr>\n";
    $html_text .= "<td colspan=7>\n";

    $html_text .= build_roster_table_working_hours($Principle_employee_roster, $workforce);
    $html_text .= "</td>\n";
    $html_text .= "</tr>\n";
    $html_text .= "</tfoot>\n";
    $html_text .= "</table>\n";
    $html_text .= "</form>";
    return $html_text;
}

function build_roster_table_working_hours(array $Roster_array, workforce $workforce) {
    $html_text = '';
    $html_text .= gettext("Hours per week") . "&nbsp;";
    $List_of_working_hours = calculate_list_of_working_hours($Roster_array);
    foreach ($List_of_working_hours as $employee_id => $working_hours) {
        $html_text .= array_sum($working_hours);
        $html_text .= ' / ';
        $html_text .= $workforce->List_of_employees[$employee_id]->working_week_hours;
        if ($workforce->List_of_employees[$employee_id]->working_week_hours != array_sum($working_hours)) {
            $difference = round(array_sum($working_hours) - $workforce->List_of_employees[$employee_id]->working_week_hours, 2);
            $html_text .= " <b>( " . $difference . " )</b>";
        }
    }
    return $html_text;
}

function calculate_list_of_working_hours($Roster_array) {
    $List_of_working_hours = array();
    foreach ($Roster_array as $Principle_employee_roster_day_array) {
        foreach ($Principle_employee_roster_day_array as $roster_object) {
            $List_of_working_hours[$roster_object->employee_id][] = $roster_object->working_hours;
        }
    }
    ksort($List_of_working_hours);
    return $List_of_working_hours;
}

foreach (alternating_week::get_alternating_week_ids() as $alternating_week_id) {
    $List_of_change_dates = principle_roster::get_list_of_employee_change_dates($employee_id, $alternating_week_id);
    $html_text .= "<div class=principle_roster_alternation_container>";
    if (1 !== count($List_of_change_dates)) {
        $html_text .= "<label for='toggle_$alternating_week_id' id='toggle_label_$alternating_week_id'>";
        $html_text .= gettext('Show older versions?') . "</label>";
        $html_text .= "<input type='checkbox' class='toggle_switch' id='toggle_$alternating_week_id'/>";
    }
    foreach ($List_of_change_dates as $valid_from_string) {
        /*
          @todo CAVE!Die Einträge, die NULL sein sollten, sind statt dessen mit dem aktuellen Datum versehen!
         *
         */
        $hide_this_form = TRUE;
        if ($valid_from_string === max($List_of_change_dates)) {
            /*
             * This is the actually relevant and current principle roster for this alternation.
             * All the others are just history.
             *  CAVE: This might be a change far in the future.
             *   Actually one of the history entries might be for today and the next months.
             *   TODO: How do we deal with sudden changes before the last valid_until?
             */
            $hide_this_form = FALSE;
        }
        $html_text .= build_change_principle_roster_employee_form($alternating_week_id, $valid_from_string, $employee_id, $hide_this_form);
    }
    $html_text .= "</div>";
}


/*
 * TODO: Where do we put the plots?
 *   Do we present any plots? Will they be on a separate page?
  //$roster_image_bar_plot = new roster_image_bar_plot($Principle_employee_roster);
  //$html_text .= $roster_image_bar_plot->svg_string;
 */
$html_text .= "</div><!-- id=main-area -->\n";
echo $html_text;


require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
