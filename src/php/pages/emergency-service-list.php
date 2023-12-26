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
$branch_id = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, $network_of_branch_offices->get_main_branch_id());
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
    $command = user_input::get_variable_from_any_input('command', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $date_new = user_input::get_variable_from_any_input('emergency_service_date', FILTER_SANITIZE_NUMBER_INT);
    $date_old = user_input::get_variable_from_any_input('emergency_service_date_old', FILTER_SANITIZE_NUMBER_INT);
    $branch_id = user_input::get_variable_from_any_input('emergency_service_branch', FILTER_SANITIZE_NUMBER_INT);
    $employee_key = user_input::get_variable_from_any_input('emergency_service_employee', FILTER_SANITIZE_NUMBER_INT);
    if ("" === $date_new) {
        return FALSE;
    }
    if ("" === $branch_id) {
        return FALSE;
    }
    if ("" === $employee_key and "" === $date_old) {
        /**
         * <p lang=de>Neuen Eintrag anlegen</p>
         */
        $sql_query = "INSERT INTO Notdienst (`Datum`, `Mandant`) VALUES(:date_new, :branch_id)";
        try {
            database_wrapper::instance()->run($sql_query, array(
                'branch_id' => $branch_id,
                'date_new' => $date_new,
            ));
        } catch (Exception $exception) {
            error_log($exception->getTraceAsString());
            $user_dialog = new user_dialog();
            $user_dialog->add_message($exception->getMessage(), E_USER_ERROR);
        }
    } else if ("replace" === $command) {
        /**
         * <p lang=de>Vorhandenen Eintrag ändern</p>
         */
        $sql_query = "UPDATE Notdienst SET `employee_key` = :employee_key, `Datum` = :date_new WHERE `Datum` = :date_old AND Mandant = :branch_id";
        database_wrapper::instance()->run($sql_query, array(
            'employee_key' => user_input::convert_post_empty_to_php_null($employee_key),
            'branch_id' => $branch_id,
            'date_new' => $date_new,
            'date_old' => $date_old,
        ));
    } else if ("delete" === $command) {
        /**
         * <p lang=de>Vorhandenen Eintrag löschen</p>
         */
        $sql_query = "DELETE FROM Notdienst WHERE `Datum` = :date_old AND Mandant = :branch_id";
        database_wrapper::instance()->run($sql_query, array(
            'branch_id' => $branch_id,
            'date_old' => $date_old,
        ));
    }
}

$sql_query = "SELECT * FROM Notdienst WHERE YEAR(Datum) = :year AND Mandant = :branch_id";
$result = database_wrapper::instance()->run($sql_query, array('year' => $year, 'branch_id' => $branch_id));
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $Emergency_services['employee_key'][] = $row->employee_key;
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
$user_dialog = new user_dialog();
$user_dialog->build_messages();
?>
<table id="emergency_service_table" class="table_with_border">
    <tr><th><?= gettext('Date') ?></th><th><?= gettext('Weekday') ?></th><th><?= gettext('Name') ?></th><th class='replacement_td'><?= gettext('Replacement') ?></th></tr>
    <?php
    if (isset($Emergency_services)) {
        foreach ($Emergency_services['Datum'] as $key => $date_sql) {
            $date_unix = strtotime($date_sql);
            $dateObject = new DateTime($date_sql);
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
            echo "\n<tr data-iterator=$key><form method='post'>";
            if ($session->user_has_privilege('create_roster')) {
                /**
                 * Date:
                 */
                echo "\n<td>\n"
                . "<input type='date' name='emergency_service_date' value='$date_sql' min='$year-01-01' max='$year-12-31' onChange='unhideButtonOnChange(this)'>"
                . "</td>\n";
                /**
                 * Weekday:
                 */
                $configuration = new \PDR\Application\configuration();
                $locale = $configuration->getLanguage();
                $dateFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
                $dateFormatter->setPattern('EEE');
                $dateString = $dateFormatter->format($date_unix);
                echo "\n<td class='$holiday_class'>" . $dateString . "&nbsp;" . $holiday_string . "</td>";
                /**
                 * Employee:
                 */
                echo "<td>\n";
                echo pharmacy_emergency_service_builder::build_emergency_service_table_employee_select($Emergency_services['employee_key'][$key], $branch_id, $date_sql);
                echo "</td>\n";
                /**
                 * Buttons:
                 */
                echo "<td>\n";
                echo "<button type='submit' id='save_$key' class='button_small no_print' onClick='enableLeavingPage();' title='" . gettext("Save changes to this line") . "' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
                . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_save.svg' alt='" . gettext("Save changes to this line") . "'>\n"
                . "</button>\n";
                echo "<button type='submit' id='delete_$key' class='button_small no_print' onClick='enableLeavingPage(); return confirmDelete();' title='" . gettext("Remove this line") . "' name='command' value='delete' style='border-radius: 32px; background-color: transparent;'>\n"
                . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_delete_forever.svg' alt='" . gettext("Remove this line") . "'>\n"
                . "</button>\n";
                echo "</td>\n";
            } else {
                $dateString = $dateObject->format("d.m.Y");
                echo "\n<td>" . $dateString . "</td>\n";
                echo "<td>\n";
                echo (isset($workforce->List_of_employees[$Emergency_services['employee_key'][$key]])) ? $workforce->List_of_employees[$Emergency_services['employee_key'][$key]]->last_name : "?";
                echo "</td>\n";
            }
            echo "<td class='replacement_td'></td>\n</form>\n</tr>\n";
        }
    }
    if ($session->user_has_privilege('create_roster')) {
        echo "\n<tr class='no_print'>";
        echo "\n<td>" . gettext("Add line") . "</td><td colspan=2></td>";
        echo "\n</tr>";
        echo "\n<tr class='no_print'><form method='post'>";
        echo "\n<td><input type='date' id='add_new_line_date' name='emergency_service_date' value='' min='$year-01-01' max='$year-12-31'></td>";
        echo "\n<td><input type='submit' id='add_new_line_submit' value='" . gettext("Add line") . "'></td>";
        echo "\n<td><input type='hidden' name=emergency_service_branch value='$branch_id'></td>";
        echo "\n</form></tr>";
    }
    ?>

</table>
</div><!-- id=main_area_centered-->
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
