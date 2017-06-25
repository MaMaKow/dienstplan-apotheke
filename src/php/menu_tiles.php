<?php
require_once '../../default.php';
require_once get_root_folder() . 'head.php';
?>
<nav id="nav" class="no-print">
    <ul id="navigation_tiles">
        <li>
            <a href=woche-out.php><img src="<?php echo get_root_folder() ?>img/week_2.svg" class="image_tiles"></a>
        </li>
        <li>
            <a href=tag-out.php><img src="<?php echo get_root_folder() ?>img/day.svg" class="image_tiles"></a>            
        </li>
        <li><a href=mitarbeiter-out.php><img src="<?php echo get_root_folder() ?>img/employee_2.svg" class="image_tiles"></a>
    <li>
        <a href=stunden-out.php><img src="<?php echo get_root_folder() ?>img/watch_overtime.svg" class="image_tiles"></a>
    </li>
    <li>
        <a href=abwesenheit-out.php title="Abwesenheit"><img src="<?php echo get_root_folder() ?>img/absence.svg" class="image_tiles"></a>
    </li>
    <li><a>
            Administration
            <img src=img/settings.png class="inline-image" alt="settings-button" title="Show settings">
        </a>
        <ul>
            <li><a href=anwesenheitsliste-out.php>Anwesenheitsliste</a></li>
            <li><a href=grundplan-tag-in.php>Grundplan Tagesansicht</a></li>
            <li><a href=grundplan-vk-in.php>Grundplan Mitarbeiteransicht</a></li>
            <li><a href=/phpmyadmin>PhpMyAdmin</a></li>
            <li><a href=upload-in.php>PEP-Upload</a></li>
            <li><a href=human-resource-management-in.php>Mitarbeiterverwaltung</a></li>
        </ul>
    </li>
    <li>
        <a><?php echo $_SESSION['user_name']; ?>&nbsp;</a>
        <ul>
            <li>
                <a href=src/php/logout.php title="Benutzer abmelden">Logout</a>
            </li>
        </ul>

    </li>
</ul>
</nav>