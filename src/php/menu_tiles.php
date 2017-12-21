<?php
require_once '../../default.php';
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
?>
<nav id="nav_tiles" class="no-print">
    <ul id="navigation_tiles">
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>woche-out.php title="Woche"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH?>img/week_2.svg" class="image_tiles"></a>
        </li>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>tag-out.php title="Tag"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH?>img/day.svg" class="image_tiles"></a>            
        </li>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>mitarbeiter-out.php title="Mitarbeiter"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH?>img/employee_2.svg" class="image_tiles"></a>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>stunden-out.php title="Überstunden"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH?>img/watch_overtime.svg" class="image_tiles"></a>
        </li>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH?>abwesenheit-out.php title="Abwesenheit"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH?>img/absence.svg" class="image_tiles"></a>
        </li>
    </ul>
</nav>
