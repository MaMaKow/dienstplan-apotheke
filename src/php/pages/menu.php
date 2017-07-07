<!--
This file is part of nearly every page. But DO NOT include it inside head.php! It is not part and should not be part of e.g. install.php!
-->
<nav id="nav" class="no-print">
    <ul id="navigation">
        <li>
            <a href=<?php echo get_root_folder();?>woche-out.php>Wochenansicht</a>
            <ul>
                <li>
                    <a href=<?php echo get_root_folder();?>woche-in.php>Wochenansicht Eingabe</a>
                    <a href=<?php echo get_root_folder();?>woche-out.php>Wochenansicht Ausgabe</a>
                </li>
            </ul>
        </li>
        <li>
            <a href=<?php echo get_root_folder();?>tag-out.php>Tagesansicht</a>            
            <ul>
                <li>
                    <a href=<?php echo get_root_folder();?>tag-in.php>Tagesansicht Eingabe</a>            
                    <a href=<?php echo get_root_folder();?>tag-out.php>Tagesansicht Ausgabe</a>
                    <a href=<?php echo get_root_folder();?>grundplan-tag-in.php>Grundplan Tagesansicht</a>
                </li>
            </ul>
        </li>
        <li><a href=<?php echo get_root_folder();?>mitarbeiter-out.php>Personenansicht</a>
            <ul>
                <li>
                    <a href=<?php echo get_root_folder();?>mitarbeiter-out.php>Dienstplan Mitarbeiteransicht</a>
                    <a href=<?php echo get_root_folder();?>grundplan-vk-in.php>Grundplan Mitarbeiteransicht</a>
                    <a href=<?php echo get_root_folder();?>human-resource-management-in.php>Mitarbeiterverwaltung</a>
                </li>
        </li>
    </ul>
    <li>
        <a href=<?php echo get_root_folder();?>stunden-out.php>Überstunden</a>
        <ul>
            <li>
                <a href=<?php echo get_root_folder();?>stunden-in.php>Überstunden Eingabe</a>
                <a href=<?php echo get_root_folder();?>stunden-out.php>Überstunden Ausgabe</a>
            </li>
        </ul>
    </li>
    <li>
        <a href=<?php echo get_root_folder();?>abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit</a>
        <ul>
            <li>
                <a href=<?php echo get_root_folder();?>abwesenheit-in.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit Eingabe</a>
                <a href=<?php echo get_root_folder();?>abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit Ausgabe</a>
                <a href=<?php echo get_root_folder();?>collaborative-vacation-in.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit Jahresplan</a>
            </li>
        </ul>
    </li>
    <li><a>
            Administration
            <img src=<?php echo get_root_folder();?>img/settings.png class="inline-image" alt="settings-button" title="Show settings">
        </a>
        <ul>
            <li><a href=<?php echo get_root_folder();?>anwesenheitsliste-out.php>Anwesenheitsliste</a></li>
            <li><a href=<?php echo get_root_folder();?>grundplan-tag-in.php>Grundplan Tagesansicht</a></li>
            <li><a href=<?php echo get_root_folder();?>grundplan-vk-in.php>Grundplan Mitarbeiteransicht</a></li>
            <li><a href=/phpmyadmin>PhpMyAdmin</a></li>
            <li><a href=<?php echo get_root_folder();?>upload-in.php>PEP-Upload</a></li>
            <li><a href=<?php echo get_root_folder();?>human-resource-management-in.php>Mitarbeiterverwaltung</a></li>
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