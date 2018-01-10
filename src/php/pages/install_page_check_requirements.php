
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
require_once 'install_head.php'
?>
<p>This page is meant to check if:</p>
<ul>
    <li> php supports connections to a supported database
        <?php
        /*
         * Check if there is write access to all write-necessary directories:
         */
        if ($install->database_driver_is_installed()) {
            echo "<em class='install_info_postive'>done</em>";
        } else {
            echo "<em class='install_info_negative'>failed</em>";
            echo $install->build_error_message_div();
        }
        ?>
    </li>
    <li> directories (i.e. upload) are writable by the program
        <?php
        /*
         * Check if there is write access to all write-necessary directories:
         */
        if ($install->pdr_directories_are_writable()) {
            echo "<em class='install_info_postive'>done</em>";
        } else {
            echo "<em class='install_info_negative'>failed</em>";
            echo $install->build_error_message_div();
        }
        ?>
    </li>
</ul>
<?php
if ($install->database_driver_is_installed() and $install->pdr_directories_are_writable()) { //Should the result be cached in a variable in the above code? Would this be a significant difference?
    ?>
    <form action="install_page_database.php" method="post">
        <input type="submit" value="<?= gettext("Next") ?>">
    </form>
<?php } ?>

