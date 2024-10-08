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
require_once '../../../default.php';
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require_once PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
?>
<nav id="navigationTiles" class="no-print">
    <ul id="navigationListTiles">
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-week-table.php title="<?= gettext('Week') ?>"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_view_week.svg" class="image-tiles"></a>
        </li>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-day-read.php title="<?= gettext('Day') ?>"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_today-24px.svg" class="image-tiles"></a>
        </li>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/roster-employee-table.php title="<?= gettext('Employee') ?>"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/employee_2.svg" class="image-tiles"></a>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/overtime-read.php title="<?= gettext('Overtime') ?>"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/watch_overtime.svg" class="image-tiles"></a>
        </li>
        <li>
            <a href=<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>src/php/pages/absence-read.php title="<?= gettext('Absence') ?>"><img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_card_travel-24px.svg" class="image-tiles"></a>
        </li>
    </ul>
</nav>
