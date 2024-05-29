<?php
/*
 * This file is part of nearly every page.
 * But DO NOT include it inside head.php!
 * It is not part and should not be part of e.g. install.php
 */
/**
 * @todo <p lang=de>
 * Das Menü sollte auf Mobilgeräten mit Touch funktionieren.
 * </p>
 */
?>
<nav id="nav" class="no_print">
    <ul id="navigation">
        <li id="MenuListItemRoster">
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-day-read.php>
                <?= gettext("Roster") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_today-24px.svg class="inline-image" alt="day-icon" title="Show roster views">
            </a>
            <ul>
                <li>
                    <a id="MenuLinkToRosterDayEdit" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-day-edit.php>
                        <?= gettext("Daily input") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_edit.svg class="inline-image" alt="edit-icon" title="Edit">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToRosterDayRead" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-day-read.php>
                        <?= gettext("Daily output") ?>
                    </a>
                </li>
                <li>
                    <a>&nbsp;</a>
                </li>
                <li>
                    <a id="MenuLinkToRosterWeekTable" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-week-table.php>
                        <?= gettext("Weekly table") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_view_week.svg class="inline-image" alt="week-icon" title="Show week">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToRosterWeekImages" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-week-images.php>
                        <?= gettext("Weekly images") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/week_1.svg class="inline-image" alt="week-icon" title="Show week roster images">
                    </a>
                </li>
                <li>
                    <a>&nbsp;</a>
                </li>
                <li>
                    <a id="MenuLinkToRosterEmployee" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-employee-table.php>
                        <?= gettext("Roster employee") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/employee_2.svg class="inline-image" alt="employee-icon" title="Show employee">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToRosterHoursList" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/marginal-employment-hours-list.php>
                        <?= gettext("Roster hours list") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_lists.svg" class="inline-image" alt="lists-icon" title="<?= gettext("Roster hours list") ?>">
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemOvertime">
            <span><?= gettext("Overtime") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/watch_overtime.svg class="inline-image" alt="overtime-icon" title="Show overtime">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToOvertimeEdit" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/overtime-edit.php>
                        <?= gettext("Overtime input") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_edit.svg class="inline-image" alt="edit-icon" title="Edit">
                    </a>
                    <a id="MenuLinkToOvertimeRead" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/overtime-read.php>
                        <?= gettext("Overtime output") ?>
                    </a>
                    <a id="MenuLinkToOvertimeOverview" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/overtime-overview.php>
                        <?= gettext("Overtime overview") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_lists.svg" class="inline-image" alt="event_repeat-icon" title="<?= gettext("Overtime overview") ?>">
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemAbsence">
            <span><?= gettext("Absence") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_card_travel-24px.svg class="inline-image" alt="absence-icon" title="Show absence">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToAbsenceEdit" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/absence-edit.php title="Urlaub, Krankheit, Abwesenheit">
                        <?= gettext("Absence input") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_edit.svg class="inline-image" alt="edit-icon" title="Edit">
                    </a>
                    <a id="MenuLinkToAbsenceYear" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/collaborative-vacation-year.php title="Urlaub, Krankheit, Abwesenheit">
                        <?= gettext("Absence annual plan") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_edit.svg class="inline-image" alt="edit-icon" title="Edit">
                    </a>
                    <a id="MenuLinkToAbsenceMonth" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/collaborative-vacation-month.php title="Urlaub, Krankheit, Abwesenheit">
                        <?= gettext("Absence monthly plan") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_edit.svg class="inline-image" alt="edit-icon" title="Edit">
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_date_range-24px.svg class="inline-image" alt="month-icon" title="Edit month">
                    </a>
                    <a id="MenuLinkToAbsenceOverview" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/remaining-vacation-overview.php title="Remaining Vacation Overview">
                        <?= gettext("Remaining Vacation Overview") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_card_travel-24px.svg class="inline-image" alt="overview-icon" title="Remaining Vacation Overview">
                    </a>
                    <a id="MenuLinkToSickNoteTracking" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/sick-note-tracking.php title="Remaining Vacation Overview">
                        <?= gettext("Sick note tracking") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_sick.svg class="inline-image" alt="sick-icon" title="Sick note tracking">
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemPrincipleRoster">
            <span><?= gettext("Principle Roster") ?>
                <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_event_repeat.svg" class="inline-image" alt="edit-icon" title="Edit">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToPrincipleRosterEmployee" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/principle-roster-employee.php>
                        <?= gettext("Principle roster employee") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_event_repeat.svg" class="inline-image" alt="edit-icon" title="Edit">
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_person.svg" class="inline-image" alt="edit-icon" title="Edit">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToPrincipleRosterDay" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/principle-roster-day.php">
                        <?= gettext("Principle roster daily") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_event_repeat.svg" class="inline-image" alt="edit-icon" title="Edit principle roster">
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_today-24px.svg" class="inline-image" alt="edit-icon" title="Edit principle roster">
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemAdministration">
            <span>
                <?= gettext("Administration") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_settings.svg class="inline-image" alt="settings-icon" title="Show settings">
            </span>
            <ul>
                <li>
                    <a id="MenuLinkToAttendanceList" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/attendance-list.php>
                        <?= gettext("Attendance list") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_work_alert.svg" class="inline-image" alt="work_alert-icon" title="Attendance list">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToSaturdayList" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/saturday-list.php>
                        <?= gettext("Saturday list") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_lists.svg" class="inline-image" alt="event_repeat-icon" title="<?= gettext("Saturday list") ?>">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToSaturdayRotationTeams" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/saturday-rotation-teams.php>
                        <?= gettext("Saturday rotation teams") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_event_repeat.svg" class="inline-image" alt="event_repeat-icon" title="<?= gettext("Saturday rotation teams") ?>">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToEmergencyServiceList" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/emergency-service-list.php>
                        <?= gettext("Emergency service list") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_emergency.svg" class="inline-image" alt="emergency-icon" title="<?= gettext("Emergency service list") ?>">
                    </a>
                </li>
                <li>
                    <a>&nbsp;</a></li>
                <li>
                    <a id="MenuLinkToPharmacyUploadPep" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/upload-pep.php>
                        <?= gettext("Upload deployment planning") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_upload_file.svg" class="inline-image" alt="upload-icon" title="<?= gettext("Upload deployment planning") ?>">
                    </a>
                </li>
                <li>
                    <a>&nbsp;</a></li>
                <li>
                    <a id="MenuLinkToManageEmployee" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/human-resource-management.php>
                        <?= gettext("Employee management") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_person.svg" class="inline-image" alt="employee-icon" title="<?= gettext("Employee management") ?>">
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/employee_2.svg" class="inline-image" alt="employee-icon" title="<?= gettext("Employee management") ?>">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToManageBranch" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/branch-management.php>
                        <?= gettext("Branch management") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_account_tree.svg" class="inline-image" alt="employee-icon" title="<?= gettext("Branch management") ?>">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToManageUser" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/user-management.php>
                        <?= gettext("User management") ?>
                        <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/user_1.svg" class="inline-image" alt="employee-icon" title="<?= gettext("User management") ?>">
                    </a>
                </li>
                <li>
                    <a>&nbsp;</a>
                </li>
                <li>
                    <a id="MenuLinkToConfiguration" href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/configuration.php>
                        <?= gettext('Configuration') ?><img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_settings.svg class="inline-image" alt="configuration-icon" title="Configuration">
                    </a>
                </li>
            </ul>
        </li>
        <li id="MenuListItemApplication">
            <span id="MenuListItemApplicationUsername">
                <?= $_SESSION['user_object']->user_name; ?>
            </span>
            <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_face_3.svg class="inline-image" alt="user-icon" title="Show user">
            <ul>
                <li>
                    <a id="MenuLinkToManageAccount" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/user-page.php">
                        <?= gettext('Account page'); ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/user_1.svg class="inline-image" alt="user-icon" title="Show user">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToApplicationAbout" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/about.php">
                        <?= gettext('About'); ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/information.svg class="inline-image" alt="information" title="About the application">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToApplicationManual" href="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>docs/documentation_de.pdf">
                        <?= gettext('Manual'); ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_description.svg class="inline-image" alt="Documentation" title="Show documentation">
                    </a>
                </li>
                <li>
                    <a id="MenuLinkToLogout" href='<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/logout.php'>
                        <?= gettext('Logout') ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_logout.svg class="inline-image" alt="Logout" title="Logout">
                    </a>
                </li>
            </ul>

        </li>
    </ul>
</nav>
