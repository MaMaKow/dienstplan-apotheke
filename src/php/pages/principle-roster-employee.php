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

$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_object']->employee_id);
create_cookie('employee_id', $employee_id, 30);
$workforce = new workforce();

if (filter_has_var(INPUT_POST, 'submit_roster')) {
    user_input::principle_employee_roster_write_user_input_to_database($employee_id);
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

function build_change_principle_roster_employee_form(alternating_week $alternating_week, int $employee_id) {

    $alternating_week_id = $alternating_week->get_alternating_week_id();

    $pseudo_date_start_object = $alternating_week->get_sunday_date_for_alternating_week();
    $pseudo_date_start_object->add(new DateInterval('P1D'));

    $pseudo_date_end_object = clone $pseudo_date_start_object;
    $pseudo_date_end_object->add(new DateInterval('P6D'));
    $workforce = new workforce($pseudo_date_start_object->format('Y-m-d'));
    $branch_id = $workforce->List_of_employees[$employee_id]->principle_branch_id;


    $Principle_employee_roster = principle_roster::read_principle_employee_roster_from_database($employee_id, $pseudo_date_start_object, $pseudo_date_end_object);
    $Principle_roster = principle_roster::read_principle_roster_from_database($branch_id, $pseudo_date_start_object, $pseudo_date_end_object);
    roster::transfer_lunch_breaks($Principle_employee_roster, $Principle_roster);
    unset($Principle_roster);

    $form_id = 'change_principle_roster_employee_form_' . $alternating_week_id;
    $html_text = '';
    $html_text .= "<form method='POST' id='$form_id' class='change_principle_roster_employee_form'>";
    $html_text .= build_html_navigation_elements::build_button_submit($form_id);
    if (alternating_week::alternations_exist()) {
        /*
         * TODO: Why does sunday_date return a monday? When and how is it changed to a monday?
         *   Which functions interfere with the values?
         */
        $monday_date = clone $alternating_week->get_sunday_date_for_alternating_week();
        $sunday_date = clone $monday_date;
        $sunday_date->add(new DateInterval('P6D'));
        $alternating_week_id_string = '<div class="inline_block_element"><p>'
                . chr(65 + $alternating_week_id) . '-' . gettext('Week')
                . '<br> '
                . gettext('e.g.') . ' '
                . gettext('calendar week') . ' ' . $monday_date->format('W')
                . '<br> '
                . $monday_date->format('d.m.Y') . ' - ' . $sunday_date->format('d.m.Y')
                . '</p></div>';
        $html_text .= $alternating_week_id_string;
    }
    $html_text .= "<table>\n";
    $html_text .= "<thead>\n";
    $html_text .= "<tr>\n";
    $Weekday_names = build_html_navigation_elements::get_weekday_names();
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
    $alternating_week = new alternating_week($alternating_week_id);
    $date_object = $alternating_week->get_sunday_date_for_alternating_week();
    $date_object->add(new DateInterval('P1D'));
}
foreach (alternating_week::get_alternating_week_ids() as $alternating_week_id) {
    error_log(__METHOD__);
    $alternating_week = new alternating_week($alternating_week_id);
    $html_text .= build_change_principle_roster_employee_form($alternating_week, $employee_id);
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
