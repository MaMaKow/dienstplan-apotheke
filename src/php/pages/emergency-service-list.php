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

$network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
$List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, min(array_keys($List_of_branch_objects)));
$date_sql = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y', strtotime($date_sql)));
create_cookie('mandant', $branch_id, 30);
create_cookie('datum', $date_sql, 0.5);
create_cookie('year', $year, 0.5);
$workforce = new workforce();

$Emergency_services = array();
$Emergency_services['Datum'] = array();
handle_user_input();

/** public static */
function handle_user_input() {
    global $session;
    if (!$session->user_has_privilege('create_roster')) {
        return FALSE;
    }

    $date = user_input::get_variable_from_any_input('emergency_service_date', FILTER_SANITIZE_NUMBER_INT);
    $branch_id = user_input::get_variable_from_any_input('emergency_service_branch', FILTER_SANITIZE_NUMBER_INT);
    $employee_id = user_input::get_variable_from_any_input('emergency_service_employee', FILTER_SANITIZE_NUMBER_INT);
    if (NULL === $date) {
        return FALSE;
    }
    if (NULL === $branch_id) {
        return FALSE;
    }
    if (NULL === $employee_id) {
        return FALSE;
    }

    $sql_query = "UPDATE Notdienst SET `VK` = :employee_id WHERE `Datum` = :date AND Mandant = :branch_id";
    database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'branch_id' => $branch_id, 'date' => $date));
}

$sql_query = "SELECT * FROM Notdienst WHERE YEAR(Datum) = :year AND Mandant = :branch_id";
$result = database_wrapper::instance()->run($sql_query, array('year' => $year, 'branch_id' => $branch_id));
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $Emergency_services['VK'][] = $row->VK;
    $Emergency_services['Datum'][] = $row->Datum;
    $Emergency_services['Mandant'][] = $row->Mandant;
}
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
echo "<div id=main_area_centered>";
echo "<H1 class='left_float_pool size_medium'>";
echo "<span class='large'>" . gettext('emergency service') . "</span>";

echo build_html_navigation_elements::build_select_branch($branch_id, $List_of_branch_objects, $date_sql);
echo form_element_builder::build_html_select_year($year);
echo "</H1>";
?>
<table id="emergency_service_table" class="table_with_border">
    <tr><th><?= gettext('Date') ?></th><th><?= gettext('Name') ?></th><th class='replacement_td'><?= gettext('Replacement') ?></th></tr>
            <?php
            if (isset($Emergency_services)) {
                foreach ($Emergency_services['Datum'] as $key => $date_sql) {
                    $date_unix = strtotime($date_sql);
                    $is_holiday = holidays::is_holiday($date_unix);
                    $holiday_string = "";
                    $holiday_class = "";
                    $weekday = date('w', $date_unix);
                    switch ($weekday) {
                        case 6:
                            $holiday_class .= " saturday ";
                            break;
                        case 0:
                            $holiday_class .= " sunday ";
                            break;
                        default:
                            break;
                    }
                    if (FALSE !== $is_holiday) {
                        $holiday_string .= "<br>" . $is_holiday;
                        $holiday_class .= " holiday ";
                    }
                    echo "\n<tr><td class='$holiday_class'>" . strftime('%a %x', $date_unix) . $holiday_string . '</td>';
                    echo '<td>';
                    if ($session->user_has_privilege('create_roster')) {
                        echo pharmacy_emergency_service_builder::build_emergency_service_table_employee_select($Emergency_services['VK'][$key], $branch_id, $date_sql);
                    } else {
                        echo (isset($workforce->List_of_employees[$Emergency_services['VK'][$key]])) ? $workforce->List_of_employees[$Emergency_services['VK'][$key]]->last_name : "?";
                    }
                    echo '</td>';
                    echo "<td class='replacement_td'></td></tr>";
                }
            }
            ?>

</table>
</div><!-- id=main_area_centered-->
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
