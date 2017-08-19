<!--
This file is part of nearly every page. But DO NOT include it inside head.php! It is not part and should not be part of e.g. install.php!
-->
<nav id="nav" class="no-print">
    <ul id="navigation">
        <li>
            <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>woche-out.php>Wochenansicht</a>
            <ul>
                <li>
                    <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>woche-in.php>Wochenansicht Eingabe</a>
                    <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>woche-out.php>Wochenansicht Ausgabe</a>
                </li>
            </ul>
        </li>
        <li>
            <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>tag-out.php>Tagesansicht</a>            
            <ul>
                <li>
                    <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>tag-in.php>Tagesansicht Eingabe</a>            
                    <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>tag-out.php>Tagesansicht Ausgabe</a>
                    <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>grundplan-tag-in.php>Grundplan Tagesansicht</a>
                </li>
            </ul>
        </li>
        <li><a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>mitarbeiter-out.php>Personenansicht</a>
            <ul>
                <li>
                    <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>mitarbeiter-out.php>Dienstplan Mitarbeiteransicht</a>
                    <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>grundplan-vk-in.php>Grundplan Mitarbeiteransicht</a>
                    <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>human-resource-management-in.php>Mitarbeiterverwaltung</a>
                </li>
        </li>
    </ul>
    <li>
        <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>stunden-out.php>Überstunden</a>
        <ul>
            <li>
                <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>stunden-in.php>Überstunden Eingabe</a>
                <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>stunden-out.php>Überstunden Ausgabe</a>
            </li>
        </ul>
    </li>
    <li>
        <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit</a>
        <ul>
            <li>
                <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>abwesenheit-in.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit Eingabe</a>
                <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit Ausgabe</a>
                <a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>collaborative-vacation-in.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit Jahresplan</a>
            </li>
        </ul>
    </li>
    <li><a>
            Administration
            <img src=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>img/settings.png class="inline-image" alt="settings-button" title="Show settings">
        </a>
        <ul>
            <li><a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>anwesenheitsliste-out.php>Anwesenheitsliste</a></li>
            <li><a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>grundplan-tag-in.php>Grundplan Tagesansicht</a></li>
            <li><a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>grundplan-vk-in.php>Grundplan Mitarbeiteransicht</a></li>
            <li><a href=/phpmyadmin>PhpMyAdmin</a></li>
            <li><a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>upload-in.php>PEP-Upload</a></li>
            <li><a href=<?php echo PDR_HTTP_SERVER_APPLICATION_PATH . '/'?>human-resource-management-in.php>Mitarbeiterverwaltung</a></li>
        </ul>
    </li>
    <li>
        <a><?php echo $_SESSION['user_name']; ?>&nbsp;</a>
                <ul>
            <li>
                <?php echo $session->build_logout_button();?>
            </li>
        </ul>

    </li>
</ul>
</nav>