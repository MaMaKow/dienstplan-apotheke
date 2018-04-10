<!--
This file is part of nearly every page. But DO NOT include it inside head.php! It is not part and should not be part of e.g. install.php!
-->
<nav id="nav" class="no-print">
    <ul id="navigation">
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>woche-out.php><?= gettext("Weekly view") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/week_2.svg class="inline-image" alt="week-button" title="Show week">
            </a>
        </li>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>tag-out.php><?= gettext("Daily view") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/day.svg class="inline-image" alt="day-button" title="Show day">
            </a>
            <ul>
                <li>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>tag-in.php><?= gettext("Daily input") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>tag-out.php><?= gettext("Daily output") ?></a>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>grundplan-tag-in.php><?= gettext("Principle roster daily") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                </li>
            </ul>
        </li>
        <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>mitarbeiter-out.php><?= gettext("Employee") ?>
                <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/employee_2.svg class="inline-image" alt="employee-button" title="Show employee">
            </a>
            <ul>
                <li>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>mitarbeiter-out.php><?= gettext("Roster employee") ?></a>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>grundplan-vk-in.php><?= gettext("Principle roster employee") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>human-resource-management-in.php><?= gettext("Human resource management") ?>
                        <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    </a>
                </li>
        </li>
    </ul>
    <li>
        <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>stunden-out.php><?= gettext("Overtime") ?>
            <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/watch_overtime.svg class="inline-image" alt="overtime-button" title="Show overtime">
        </a>
        <ul>
            <li>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>stunden-in.php><?= gettext("Overtime input") ?>
                    <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                </a>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>stunden-out.php><?= gettext("Overtime output") ?></a>
            </li>
        </ul>
    </li>
    <li>
        <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence") ?>
            <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/absence.svg class="inline-image" alt="absence-button" title="Show absence">
        </a>
        <ul>
            <li>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>abwesenheit-in.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence input") ?>
                    <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                </a>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence output") ?></a>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>collaborative-vacation-in.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence annual plan") ?>
                    <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                </a>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>collaborative-vacation-month-in.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence monthly plan") ?>
                    <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/pencil-pictogram.svg class="inline-image" alt="edit-button" title="Edit">
                    <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/month_1.svg class="inline-image" alt="month-button" title="Edit month">
                </a>
            </li>
        </ul>
    </li>
    <li><a>
            <?= gettext("Administration") ?>
            <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/settings.png class="inline-image" alt="settings-button" title="Show settings">
        </a>
        <ul>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>anwesenheitsliste-out.php><?= gettext("Attendance list") ?></a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/marginal-employment-hours-list.php><?= gettext("Marginal employment hours list") ?>
                    <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/employee_2.svg class="inline-image" alt="employee-button" title="Show employee">
                </a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>grundplan-tag-in.php><?= gettext("Principle roster daily") ?></a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>grundplan-vk-in.php><?= gettext("Principle roster employee") ?></a></li>
            <li><a href=/phpmyadmin>PhpMyAdmin</a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>upload-in.php><?= gettext("Upload deployment planning") ?></a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>human-resource-management-in.php><?= gettext("Human resource management") ?></a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>user-management-in.php><?= gettext("User management") ?></a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/branch-management.php><?= gettext("Branch management") ?></a></li>
        </ul>
    </li>
    <li>
        <a><?= $_SESSION['user_name']; ?>&nbsp;
            <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/user_1.svg class="inline-image" alt="user-button" title="Show user">
        </a>
        <ul>
            <li>
                <?= $session->build_logout_button(); ?>
            </li>
        </ul>

    </li>
</ul>
</nav>
