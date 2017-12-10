<!--
This file is part of nearly every page. But DO NOT include it inside head.php! It is not part and should not be part of e.g. install.php!
-->
<nav id="nav" class="no-print">
    <ul id="navigation">
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>woche-out.php><?= gettext("Weekly view") ?></a>
            <ul>
                <li>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>woche-in.php><?= gettext("Weekly view input") ?></a>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>woche-out.php><?= gettext("Weekly view output") ?></a>
                </li>
            </ul>
        </li>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>tag-out.php><?= gettext("Daily view") ?></a>
            <ul>
                <li>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>tag-in.php><?= gettext("Daily input") ?></a>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>tag-out.php><?= gettext("Daily output") ?></a>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>grundplan-tag-in.php><?= gettext("Principle roster daily") ?></a>
                </li>
            </ul>
        </li>
        <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>mitarbeiter-out.php><?= gettext("Employee") ?></a>
            <ul>
                <li>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>mitarbeiter-out.php><?= gettext("Roster employee") ?></a>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>grundplan-vk-in.php><?= gettext("Principle roster employee") ?></a>
                    <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>human-resource-management-in.php><?= gettext("Human resource management") ?></a>
                </li>
        </li>
    </ul>
    <li>
        <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>stunden-out.php><?= gettext("Overtime") ?></a>
        <ul>
            <li>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>stunden-in.php><?= gettext("Overtime input") ?></a>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>stunden-out.php><?= gettext("Overtime output") ?></a>
            </li>
        </ul>
    </li>
    <li>
        <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence") ?></a>
        <ul>
            <li>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>abwesenheit-in.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence input") ?></a>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence output") ?></a>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>collaborative-vacation-in.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence annual plan") ?></a>
                <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>collaborative-vacation-month-in.php title="Urlaub, Krankheit, Abwesenheit"><?= gettext("Absence monthly plan") ?></a>
            </li>
        </ul>
    </li>
    <li><a>
            <?= gettext("Administration") ?>
            <img src=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>img/settings.png class="inline-image" alt="settings-button" title="Show settings">
        </a>
        <ul>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>anwesenheitsliste-out.php><?= gettext("Attendance list") ?></a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>grundplan-tag-in.php><?= gettext("Principle roster daily") ?></a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>grundplan-vk-in.php><?= gettext("Principle roster employee") ?></a></li>
            <li><a href=/phpmyadmin>PhpMyAdmin</a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>upload-in.php><?= gettext("Upload deployment planning") ?></a></li>
            <li><a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>human-resource-management-in.php><?= gettext("Human resource management") ?></a></li>
        </ul>
    </li>
    <li>
        <a><?= $_SESSION['user_name']; ?>&nbsp;</a>
        <ul>
            <li>
                <?= $session->build_logout_button(); ?>
            </li>
        </ul>

    </li>
</ul>
</nav>
