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
require_once "../classes/class.install.php";
$install = new install;
if (filter_has_var(INPUT_POST, "database_user")) {
    if (FALSE === $install->handle_user_input_database()) {
        $install->Error_message[] = gettext("There was an error while trying to create the database.");
        $install->Error_message[] = gettext("Please see the error log for details!");
    }
}
require_once 'install_head.php';
$install->build_error_message_div();
?>
<H1>Database configuration</H1>

<form accept-charset='utf-8' method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <p>
        <LABEL for="database_management_system">Database type (DBMS):</LABEL><br>
        <select name="database_management_system" id="database_management_system">
            <option value="mysql">MySQL</option>
        </select>
    </p><p>
        <LABEL for="database_host">Database server hostname:</LABEL><br>
        <input type="text" id="database_host" name="database_host" value="<?= $_SESSION["Config"]["database_host"] ? $_SESSION["Config"]["database_host"] : "localhost" ?>" />
        <BR>
    </p><p>

        <LABEL for="database_port">Database server port:</LABEL><br>
        <input type="text" id="database_port" name="database_port" value="<?= $_SESSION["Config"]["database_port"] ? $_SESSION["Config"]["database_port"] : "" ?>" /><!--standard value 3306-->
        <br><span class="hint">Leave this blank unless you know the server operates on a non-standard port.<span>
            </p><p>

            <LABEL for="database_user">Database username:</LABEL><br>
            <input type="text" id="database_user" name="database_user" value="<?= $_SESSION["Config"]["database_user"] ? $_SESSION["Config"]["database_user"] : "" ?>" />
        </p><p>

            <LABEL for="database_password">Database password:</LABEL><br>
            <input type="password" id="database_password" name="database_password" value="" />
        </p><p>

            <LABEL for="database_name">Database name:</LABEL><br>
            <input type="text" id="database_name" name="database_name" value="<?= $_SESSION["Config"]["database_name"] ? $_SESSION["Config"]["database_name"] : "pharmacy_duty_roster" ?>" />
        </p><p>
            <?php
            echo $install->build_error_message_div();
            ?>
        </p><p>
            <input type="submit" id="InstallPageDatabaseFormButton" />
        </p>
