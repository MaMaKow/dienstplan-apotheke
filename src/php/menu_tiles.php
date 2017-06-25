<?php
require_once '../../default.php';
require_once get_root_folder() . 'head.php';
?>
<nav id="nav_tiles" class="no-print">
    <ul id="navigation_tiles">
        <li>
            <a href=woche-out.php title="Woche"><img src="<?php echo get_root_folder() ?>img/week_2.svg" class="image_tiles"></a>
        </li>
        <li>
            <a href=tag-out.php title="Tag"><img src="<?php echo get_root_folder() ?>img/day.svg" class="image_tiles"></a>            
        </li>
        <li>
            <a href=mitarbeiter-out.php title="Mitarbeiter"><img src="<?php echo get_root_folder() ?>img/employee_2.svg" class="image_tiles"></a>
        <li>
            <a href=stunden-out.php title="Überstunden"><img src="<?php echo get_root_folder() ?>img/watch_overtime.svg" class="image_tiles"></a>
        </li>
        <li>
            <a href=abwesenheit-out.php title="Abwesenheit"><img src="<?php echo get_root_folder() ?>img/absence.svg" class="image_tiles"></a>
        </li>
    </ul>
</nav>