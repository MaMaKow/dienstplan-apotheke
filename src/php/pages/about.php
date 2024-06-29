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
require '../../../default.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
?>
<main>
    <p><?= gettext('About') ?>:</p>
    <H1><?= gettext('Pharmacy Duty Roster') ?></H1>
    <p id="pdrVersionParagraph">Version: <span id="pdrVersionSpan">0.30.1</span></p>
    <p>
        License: Copyright © 2019, Martin Mandelkow
    </p>
    <p>
        This program is free software; you can redistribute it and/or modify it under the terms of the <a href="../../../LICENSE.md">GNU Affero General Public License</a> as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
        <br>
        <?= gettext('Pharmacy Duty Roster') ?> uses PHPMailer from the authors: Marcus Bointon, Jim Jagielski, Andy Prevostand and Brent R. Matzelle
        <br>
        Also, find  <a href="list_of_artwork.php">a list of included artwork</a>.

    </p>
    <p><a href="https://github.com/MaMaKow/dienstplan-apotheke">Download source code</a></p>
</main>
</body>
</html>
