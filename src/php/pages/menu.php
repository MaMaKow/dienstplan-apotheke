<?php
/*
 * This file is part of nearly every page.
 * But DO NOT include it inside head.php!
 * It is not part and should not be part of e.g. install.php
 */
/**
 * @todo <p lang=de>
 * Das Menü sollte einmal komplett umgebaut werden.
 *   - Es sollte schlanker werden,
 *   - Es sollte auf Mobilgeräten mit Touch funktionieren.
 * </p>
 */
?>
<nav id="nav" class="no_print">
    <ul id="navigation">
        <li id="MenuListItemWeek">
            <span><?= gettext("Weekly view") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_view_week-24px.svg class="inline-image" alt="week-button" title="Show week">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToRosterWeekTable" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-week-table.php>
                        <?= gettext("Weekly table") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_view_week-24px.svg class="inline-image" alt="week-button" title="Show week">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToRosterWeekImages" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-week-images.php>
                        <?= gettext("Weekly images") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/week_1.svg class="inline-image" alt="week-button" title="Show week roster images">
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemDay">
            <span><?= gettext("Daily view") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_today-24px.svg class="inline-image" alt="day-button" title="Show day">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToRosterDayEdit" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-day-edit.php>
                        <?= gettext("Daily input") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                    <a id="MenuLinkToRosterDayRead" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-day-read.php>
                        <?= gettext("Daily output") ?>
                    </a>
                    <a id="MenuLinkToPrincipleRosterDay" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/principle-roster-day.php>
                        <?= gettext("Principle roster daily") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemEmployee">
            <span><?= gettext("Employee") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/employee_2.svg class="inline-image" alt="employee-button" title="Show employee">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToRosterEmployee" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-employee-table.php>
                        <?= gettext("Roster employee") ?>
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToPrincipleRosterEmployee" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/principle-roster-employee.php>
                        <?= gettext("Principle roster employee") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToRosterHoursList" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/marginal-employment-hours-list.php>
                        <?= gettext("Roster hours list") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/employee_2.svg class="inline-image" alt="employee-button" title="Show employee">
                    </a>
                </li>

            </ul>
        </li>
        <li id="MenuListItemOvertime">
            <span><?= gettext("Overtime") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/watch_overtime.svg class="inline-image" alt="overtime-button" title="Show overtime">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToOvertimeEdit" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/overtime-edit.php>
                        <?= gettext("Overtime input") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                    <a id="MenuLinkToOvertimeRead" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/overtime-read.php>
                        <?= gettext("Overtime output") ?>
                    </a>
                    <a id="MenuLinkToOvertimeOverview" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/overtime-overview.php>
                        <?= gettext("Overtime overview") ?>
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemAbsence">
            <span><?= gettext("Absence") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_card_travel-24px.svg class="inline-image" alt="absence-button" title="Show absence">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToAbsenceEdit" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/absence-edit.php title="Urlaub, Krankheit, Abwesenheit">
                        <?= gettext("Absence input") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                    <a id="MenuLinkToAbsenceRead" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/absence-read.php title="Urlaub, Krankheit, Abwesenheit">
                        <?= gettext("Absence output") ?>
                    </a>
                    <a id="MenuLinkToAbsenceYear" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/collaborative-vacation-year.php title="Urlaub, Krankheit, Abwesenheit">
                        <?= gettext("Absence annual plan") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                    <a id="MenuLinkToAbsenceMonth" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/collaborative-vacation-month.php title="Urlaub, Krankheit, Abwesenheit">
                        <?= gettext("Absence monthly plan") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_date_range-24px.svg class="inline-image" alt="month-button" title="Edit month">
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemAdministration">
            <span>
                <?= gettext("Administration") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_ic_settings_24px.svg class="inline-image" alt="settings-button" title="Show settings">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToAttendanceList" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/attendance-list.php>
                        <?= gettext("Attendance list") ?>
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToSaturdayList" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/saturday-list.php>
                        <?= gettext("Saturday list") ?>
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToEmergencyServiceList" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/emergency-service-list.php>
                        <?= gettext("Emergency service list") ?>
                    </a>
                </li>
                <li>
                    <a>&nbsp;</a></li>
                <li>
                    <a id="MenuLinkToPharmacyUploadPep" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/upload-pep.php>
                        <?= gettext("Upload deployment planning") ?>
                    </a>
                </li>
                <li>
                    <a>&nbsp;</a></li>
                <li>
                    <a id="MenuLinkToManageEmployee" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/human-resource-management.php>
                        <?= gettext("Employee management") ?>
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToManageBranch" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/branch-management.php>
                        <?= gettext("Branch management") ?>
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToManageUser" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/user-management.php>
                        <?= gettext("User management") ?>
                    </a>
                </li>
                <li>
                    <a>&nbsp;</a>
                </li>
                <li>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/configuration.php>
                        <?= gettext('Configuration') ?><img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_ic_settings_24px.svg class="inline-image" alt="configuration-button" title="Configuration">
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemApplication">
            <span id="MenuListItemApplicationUsername">
                <?= $_SESSION['user_object']->user_name; ?>
            </span>
            <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/user_1.svg class="inline-image" alt="user-button" title="Show user">
            <ul>
                <li>
                    <a id="MenuLinkToManageAccount" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/user-page.php">
                        <?= gettext('Account page'); ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/user_1.svg class="inline-image" alt="user-button" title="Show user">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToApplicationAbout" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/about.php">
                    <!--TODO: <p lang=de>
                        Dieses Element sollte in einen Bereich "application" umziehen.
                        Der lohnt sich aber erst, wenn auch die Dokumentation (Anleitung) im Menü ist.
                    </p>-->
                        <?= gettext('About'); ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/information.svg class="inline-image" alt="information" title="Show user">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToLogout" href='<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/logout.php'>
                        <?= gettext('Logout') ?>
                    </a>
                </li>
            </ul>

        </li>
    </ul>
</nav>
