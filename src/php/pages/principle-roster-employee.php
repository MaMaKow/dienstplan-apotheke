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

$workforce = new workforce();
$employee_key = \user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->get_default_employee_key());
\PDR\Utility\GeneralUtility::createCookie('employee_key', $employee_key, 30);

if (filter_has_var(INPUT_POST, 'submit_roster')) {
    if (!$session->user_has_privilege(\sessions::PRIVILEGE_CREATE_ROSTER)) {
        return FALSE;
    }
    $Principle_roster_new = \user_input::get_principle_employee_roster_from_POST_secure();
    $last_item_in_roster = end($Principle_roster_new)[0];
    $first_item_in_roster = reset($Principle_roster_new)[0];
    $Principle_roster_old = \principle_roster::read_current_principle_employee_roster_from_database($employee_key, $first_item_in_roster->date_object, $last_item_in_roster->date_object);
    $List_of_changed_keys = \user_input::get_changed_principle_roster_primary_key_list($Principle_roster_new, $Principle_roster_old);
    $Inserted_principle_roster_item_list = \user_input::get_inserted_principle_roster_item_list($Principle_roster_new);
    $List_of_deleted_roster_primary_keys = \user_input::get_deleted_roster_primary_key_list($Principle_roster_new, $Principle_roster_old);
    \principle_roster::insert_changed_entries_into_database_by_key($Principle_roster_new, $List_of_changed_keys);
    \principle_roster::insert_new_entries_into_database($Inserted_principle_roster_item_list);
    \principle_roster::invalidate_removed_entries_in_database($List_of_deleted_roster_primary_keys);
}


//Produziere die Ausgabe
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
?>
<a name=top></a>
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('create_roster');
$html_text = '';
$html_text .= "<div id=mainArea>\n";
//TODO: find out how to respect the lunch breaks!
$html_text .= \build_html_navigation_elements::build_select_employee($employee_key, $workforce->List_of_employees);

function build_change_principle_roster_employee_form(int $alternating_week_id, int $employee_key) {
    $alternating_week = new alternating_week($alternating_week_id);
    $pseudo_date_start_object = $alternating_week->get_monday_date_for_alternating_week(new DateTime('Monday this week'));
    $pseudo_date_end_object = clone $pseudo_date_start_object;
    $pseudo_date_end_object->add(new DateInterval('P6D'));

    $Principle_employee_roster = \principle_roster::read_current_principle_employee_roster_from_database($employee_key, clone $pseudo_date_start_object, clone $pseudo_date_end_object);
    /*
     * TODO: We might stop using a transfer for the lunch breaks.
     *   This means, that the breaks will not magically appear in the principle-roster-employee.php
     *   They will however probably be automaticcaly be inserted in the daily view.
     *   This might bring up some confusion. Should I give up calculating probably lunch_breaks alltogether?
     *     If so, should there be a warning/notice if the law wants a break to be given?
     */
    $workforce = new workforce();
    if ($workforce->employee_exists($employee_key)) {
        $branch_id = $workforce->List_of_employees[$employee_key]->principle_branch_id;
    } else {
        $networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices();
        $branch_id = $networkOfBranchOffices->get_main_branch_id();
    }
    /*
     * @todo Take care to write a warning if lunch breaks are not given.
     */
    $form_id = 'change_principle_roster_employee_form_' . $alternating_week_id . '_' . $pseudo_date_start_object->format('Y-m-d');
    $html_text = '';

    $html_text .= "<form method='POST' id='$form_id' class='change-principle-roster-employee-form'>";
    $html_text .= \build_html_navigation_elements::build_button_submit($form_id);
    if (\alternating_week::alternations_exist()) {
        $monday_date = clone $alternating_week->get_monday_date_for_alternating_week(new DateTime('Monday this week'));
        $sunday_date = clone $monday_date;
        $sunday_date->add(new DateInterval('P6D'));
        $alternating_week_id_string = '<div class="inline-block-element"><p>'
                . \alternating_week::get_human_readable_string($alternating_week_id)
                . '<br> '
                . gettext('e.g.') . ' '
                . gettext('calendar week') . ' ' . $monday_date->format('W')
                . '<br> '
                . $monday_date->format('d.m.Y') . ' - ' . $sunday_date->format('d.m.Y')
                . '</p></div>';
        $html_text .= $alternating_week_id_string;
    }
    $html_text .= "<script> "
            . " var Roster_array = " . json_encode($Principle_employee_roster, JSON_UNESCAPED_UNICODE) . ";\n"
            . " var List_of_employee_names = " . json_encode($workforce->get_list_of_employee_names(), JSON_UNESCAPED_UNICODE) . ";\n"
            . "</script>\n";

    $html_text .= "<table>\n";
    $html_text .= "<thead>\n";
    $html_text .= "<tr>\n";
    $Weekday_names = \localization::get_weekday_names();
    foreach ($Weekday_names as $weekday_name) {
        //Wochentag
        $html_text .= "<td width=10%>";
        $html_text .= $weekday_name;
        $html_text .= "</td>\n";
    }
    $max_employee_count = \roster::calculate_max_employee_count($Principle_employee_roster);
    $html_text .= "<tbody>\n";
    for ($table_input_row_iterator = 0; $table_input_row_iterator < $max_employee_count; $table_input_row_iterator++) {
        $html_text .= "<tr data-roster_row_iterator='$table_input_row_iterator'>\n";
        foreach (array_keys($Principle_employee_roster) as $day_iterator) {
            $html_text .= \build_html_roster_views::build_roster_input_row($Principle_employee_roster, $day_iterator, $table_input_row_iterator, $max_employee_count, $branch_id, array('add_select_branch', 'add_hidden_employee' => $employee_key));
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

    $html_text .= build_roster_table_working_week_hours($Principle_employee_roster, $workforce);
    $html_text .= "</td>\n";
    $html_text .= "</tr>\n";
    $html_text .= "</tfoot>\n";
    $html_text .= "</table>\n";
    $html_text .= "</form>";
    return $html_text;
}

function build_roster_table_working_week_hours(array $Roster_array, workforce $workforce) {
    $html_text = '';
    $html_text .= gettext("Hours per week") . "&nbsp;";
    $List_of_working_week_hours = calculate_list_of_working_week_hours($Roster_array);
    foreach ($List_of_working_week_hours as $employee_key => $working_week_hours) {
        $html_text .= array_sum($working_week_hours);
        $html_text .= ' / ';
        $html_text .= $workforce->List_of_employees[$employee_key]->working_week_hours;
        if ($workforce->List_of_employees[$employee_key]->working_week_hours != array_sum($working_week_hours)) {
            $difference = round(array_sum($working_week_hours) - $workforce->List_of_employees[$employee_key]->working_week_hours, 2);
            $html_text .= " <b>( " . $difference . " )</b>";
        }
    }
    return $html_text;
}

function calculate_list_of_working_week_hours($Roster_array) {
    $List_of_working_week_hours = array();
    foreach ($Roster_array as $Principle_employee_roster_day_array) {
        foreach ($Principle_employee_roster_day_array as $roster_object) {
            $List_of_working_week_hours[$roster_object->employee_key][] = $roster_object->working_hours;
        }
    }
    ksort($List_of_working_week_hours);
    return $List_of_working_week_hours;
}

foreach (\alternating_week::get_alternating_week_ids() as $alternating_week_id) {
    $html_text .= "<div class=principle-roster-alternation-container>";
    $html_text .= build_change_principle_roster_employee_form($alternating_week_id, $employee_key);
    $html_text .= "</div>";
}


/*
 * TODO: Where do we put the plots?
 *   Do we present any plots? Will they be on a separate page?
  //$roster_image_bar_plot = new roster_image_bar_plot($Principle_employee_roster);
  //$html_text .= $roster_image_bar_plot->svg_string;
 */
$html_text .= "</div><!-- id=mainArea -->\n";
echo $html_text;

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
