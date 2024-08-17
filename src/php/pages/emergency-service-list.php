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
\PDR\Utility\GeneralUtility::createCookie('mandant', $branchId, 30);
\PDR\Utility\GeneralUtility::createCookie('datum', $dateSql, 0.5);
\PDR\Utility\GeneralUtility::createCookie('year', $year, 0.5);
$workforce = new workforce($year . "-01-01", $year . "-12-31");

\PDR\Input\EmergencyServiceInputHandler::handleUserInput($session);
if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted
    $location = \PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/emergency-service-list.php' . "?year=$year&branch_id=$branchId";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}

$configuration = new \PDR\Application\configuration();
$locale = $configuration->getLanguage();
$dateFormatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE);
$dateFormatter->setPattern('EEE');

$listOfEmergencyServicesInYear = PDR\Database\EmergencyServiceDatabaseHandler::getListOfEmergencyServicesInYear($year, $branchId);

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
echo "<div id=mainAreaCentered>";
echo "<H1 class='left-float-pool size-medium'>";
echo "<span class='large'>" . gettext('emergency service') . "</span>";

echo build_html_navigation_elements::build_select_branch($branchId, $ListOfBranchObjects, $dateSql);
echo form_element_builder::build_html_select_year($year);
echo "</H1>";
$userDialog = new user_dialog();
$userDialog->build_messages();
?>
<table id="emergencyServiceTable" class="table-with-border">
    <tr><th><?= gettext('Date') ?></th><th><?= gettext('Weekday') ?></th><th><?= gettext('Name') ?></th><th class='replacement-td'><?= gettext('Replacement') ?></th></tr>
    <?php
    if (array() !== $listOfEmergencyServicesInYear) {
        foreach ($listOfEmergencyServicesInYear as $emergencyServiceIndex => $emergencyService) {
            $dateObject = $emergencyService->getDateObject();
            $dateSql = $dateObject->format("Y-m-d");
            $dateUnix = $dateObject->getTimestamp();
            $isHoliday = holidays::is_holiday($dateObject);
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
                $dateString = $dateFormatter->format($dateUnix);
                echo "\n<td class='$holidayHtmlClass'>" . $dateString . "&nbsp;" . $holidayString . "</td>";
                /**
                 * Employee:
                 */
                echo "<td>\n";
                echo pharmacy_emergency_service_builder::build_emergency_service_table_employee_select($emergencyService->getEmployeeKey(), $branchId, $dateSql, $emergencyServiceIndex);
                echo "</td>\n";
                /**
                 * Buttons:
                 */
                echo "<td>\n";
                echo "<button type='submit' id='save_$emergencyServiceIndex' class='button-small no-print' onClick='enableLeavingPage();' title='" . gettext("Save changes to this line") . "' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
                . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_save.svg' alt='" . gettext("Save changes to this line") . "'>\n"
                . "</button>\n";
                echo "<button type='submit' id='delete_$emergencyServiceIndex' class='button-small no-print' onClick='enableLeavingPage(); return confirmDelete();' title='" . gettext("Remove this line") . "' name='command' value='delete' style='border-radius: 32px; background-color: transparent;'>\n"
                . "<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_delete_forever.svg' alt='" . gettext("Remove this line") . "'>\n"
                . "</button>\n";
                echo "</td>\n";
            } else {
                $dateString = $dateObject->format("d.m.Y");
                echo "\n<td>" . $dateString . "</td>\n";
                echo "<td>\n";
                echo (isset($workforce->List_of_employees[$emergencyService->getEmployee_key()])) ? $workforce->List_of_employees[$emergencyService->getEmployee_key()]->last_name : "?";
                echo "</td>\n";
            }
            echo "<td class='replacement-td'></td>\n</form>\n</tr>\n";
        }
    }
    if ($session->user_has_privilege('create_roster')) {
        echo "\n<tr class='no-print'>";
        echo "\n<td>" . gettext("Add line") . "</td><td colspan=2></td>";
        echo "\n</tr>";
        echo "\n<tr class='no-print'><form method='post'>";
        echo "\n<td><input type='date' id='add_new_line_date' name='emergency_service_date' value='' min='$year-01-01' max='$year-12-31'></td>";
        echo "\n<td><input type='submit' id='add_new_line_submit' value='" . gettext("Add line") . "'></td>";
        echo "\n<td><input type='hidden' name=emergency_service_branch value='$branchId'></td>";
        echo "\n</form></tr>";
    }
    ?>

</table>
</div><!-- id=mainAreaCentered-->
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
