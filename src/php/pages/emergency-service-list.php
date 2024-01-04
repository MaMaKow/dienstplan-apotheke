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

$networkOfBranchOffices = new \PDR\Pharmacy\NetworkOfBranchOffices;
$ListOfBranchObjects = $networkOfBranchOffices->get_list_of_branch_objects();
$branchId = user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, $networkOfBranchOffices->get_main_branch_id());
$dateSql = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, date('Y-m-d'));
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y', strtotime($dateSql)));
create_cookie('mandant', $branchId, 30);
create_cookie('datum', $dateSql, 0.5);
create_cookie('year', $year, 0.5);
$workforce = new workforce($year . "-01-01", $year . "-12-31");

/**
 * @todo Create class \Database\EmergencyServiceDatabaseHandler and move the sql calls from here to there.
 */
/**
 * @todo Use a real object istead of an array:
 */
$EmergencyServices = array();
$EmergencyServices['Datum'] = array();
handle_user_input();

/** public static */
function handle_user_input() {
    global $session;
    if (!$session->user_has_privilege('create_roster')) {
        return FALSE;
    }
    $command = user_input::get_variable_from_any_input('command', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $dateNew = user_input::get_variable_from_any_input('emergency_service_date', FILTER_SANITIZE_NUMBER_INT);
    $dateOld = user_input::get_variable_from_any_input('emergency_service_date_old', FILTER_SANITIZE_NUMBER_INT);
    $branchId = user_input::get_variable_from_any_input('emergency_service_branch', FILTER_SANITIZE_NUMBER_INT);
    $employeeKey = user_input::get_variable_from_any_input('emergency_service_employee', FILTER_SANITIZE_NUMBER_INT);
    if ("" === $dateNew) {
        return FALSE;
    }
    if ("" === $branchId) {
        return FALSE;
    }
    if ("" === $employeeKey and "" === $dateOld) {
        /**
         * <p lang=de>Neuen Eintrag anlegen</p>
         */
        $sqlQueryInsert = "INSERT INTO Notdienst (`Datum`, `Mandant`) VALUES(:date_new, :branch_id)";
        try {
            database_wrapper::instance()->run($sqlQueryInsert, array(
                'branch_id' => $branchId,
                'date_new' => $dateNew,
            ));
        } catch (Exception $exception) {
            error_log($exception->getTraceAsString());
            $userDialog = new user_dialog();
            $userDialog->add_message($exception->getMessage(), E_USER_ERROR);
        }
    } else if ("replace" === $command) {
        /**
         * <p lang=de>Vorhandenen Eintrag ändern</p>
         */
        $sqlQueryUpdate = "UPDATE Notdienst SET `employee_key` = :employee_key, `Datum` = :date_new WHERE `Datum` = :date_old AND Mandant = :branch_id";
        database_wrapper::instance()->run($sqlQueryUpdate, array(
            'employee_key' => user_input::convert_post_empty_to_php_null($employeeKey),
            'branch_id' => $branchId,
            'date_new' => $dateNew,
            'date_old' => $dateOld,
        ));
    } else if ("delete" === $command) {
        /**
         * <p lang=de>Vorhandenen Eintrag löschen</p>
         */
        $sqlQueryDelete = "DELETE FROM Notdienst WHERE `Datum` = :date_old AND Mandant = :branch_id";
        database_wrapper::instance()->run($sqlQueryDelete, array(
            'branch_id' => $branchId,
            'date_old' => $dateOld,
        ));
    }
}

if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted
    $location = \PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/emergency-service-list.php' . "?year=$year&branch_id=$branchId";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}


$sqlQuerySelect = "SELECT * FROM Notdienst WHERE YEAR(Datum) = :year AND Mandant = :branch_id";
$result = database_wrapper::instance()->run($sqlQuerySelect, array('year' => $year, 'branch_id' => $branchId));
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $EmergencyServices['employee_key'][] = $row->employee_key;
    $EmergencyServices['Datum'][] = $row->Datum;
    $EmergencyServices['Mandant'][] = $row->Mandant;
}
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
echo "<div id=main_area_centered>";
echo "<H1 class='left_float_pool size_medium'>";
echo "<span class='large'>" . gettext('emergency service') . "</span>";

echo build_html_navigation_elements::build_select_branch($branchId, $ListOfBranchObjects, $dateSql);
echo form_element_builder::build_html_select_year($year);
echo "</H1>";
$userDialog = new user_dialog();
$userDialog->build_messages();
?>
<table id="emergency_service_table" class="table_with_border">
    <tr><th><?= gettext('Date') ?></th><th><?= gettext('Weekday') ?></th><th><?= gettext('Name') ?></th><th class='replacement_td'><?= gettext('Replacement') ?></th></tr>
    <?php
    if (isset($EmergencyServices)) {
        foreach ($EmergencyServices['Datum'] as $emergencyServiceIndex => $dateSql) {
            $dateUnix = strtotime($dateSql);
            $dateObject = new DateTime($dateSql);
            $isHoliday = holidays::is_holiday($dateUnix);
            $holidayString = "";
            $holidayHtmlClass = "";
            $weekday = date('w', $dateUnix);
            switch ($weekday) {
                case 6:
                    $holidayHtmlClass .= " saturday ";
                    break;
                case 0:
                    $holidayHtmlClass .= " sunday ";
                    break;
                default:
                    break;
            }
            if (FALSE !== $isHoliday) {
                $holidayString .= "<br>" . $isHoliday;
                $holidayHtmlClass .= " holiday ";
            }
            /**
             * @todo auto commit the form
             */
            echo "\n<tr data-iterator=$emergencyServiceIndex><form method='post'>";
            if ($session->user_has_privilege('create_roster')) {
                /**
                 * Date:
                 */
                echo "\n<td>\n"
                . "<input type='date' name='emergency_service_date' value='$dateSql' min='$year-01-01' max='$year-12-31' onChange='unhideButtonOnChange(this)'>"
                . "</td>\n";
                /**
                 * Weekday:
                 */
                $configuration = new \PDR\Application\configuration();
                $locale = $configuration->getLanguage();
                $dateFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
                $dateFormatter->setPattern('EEE');
                $dateString = $dateFormatter->format($dateUnix);
                echo "\n<td class='$holidayHtmlClass'>" . $dateString . "&nbsp;" . $holidayString . "</td>";
                /**
                 * Employee:
                 */
                echo "<td>\n";
                echo pharmacy_emergency_service_builder::build_emergency_service_table_employee_select($EmergencyServices['employee_key'][$emergencyServiceIndex], $branchId, $dateSql, $emergencyServiceIndex);
                echo "</td>\n";
                /**
                 * Buttons:
                 */
                echo "<td>\n";
                echo "<button type='submit' id='save_$emergencyServiceIndex' class='button_small no_print' onClick='enableLeavingPage();' title='" . gettext("Save changes to this line") . "' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
                . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_save.svg' alt='" . gettext("Save changes to this line") . "'>\n"
                . "</button>\n";
                echo "<button type='submit' id='delete_$emergencyServiceIndex' class='button_small no_print' onClick='enableLeavingPage(); return confirmDelete();' title='" . gettext("Remove this line") . "' name='command' value='delete' style='border-radius: 32px; background-color: transparent;'>\n"
                . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_delete_forever.svg' alt='" . gettext("Remove this line") . "'>\n"
                . "</button>\n";
                echo "</td>\n";
            } else {
                $dateString = $dateObject->format("d.m.Y");
                echo "\n<td>" . $dateString . "</td>\n";
                echo "<td>\n";
                echo (isset($workforce->List_of_employees[$EmergencyServices['employee_key'][$emergencyServiceIndex]])) ? $workforce->List_of_employees[$EmergencyServices['employee_key'][$emergencyServiceIndex]]->last_name : "?";
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
        echo "\n<td><input type='hidden' name=emergency_service_branch value='$branchId'></td>";
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
