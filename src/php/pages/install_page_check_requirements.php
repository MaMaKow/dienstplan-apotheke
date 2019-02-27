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
require_once 'install_head.php';

$webserver_supports_https = $install->webserver_supports_https();
$database_driver_is_installed = $install->database_driver_is_installed();
$pdr_directories_are_writable = $install->pdr_directories_are_writable();
$php_extension_requirements_are_fulfilled = $install->php_extension_requirements_are_fulfilled();
$php_version_requirement_is_fulfilled = $install->php_version_requirement_is_fulfilled();

$all_requirements_are_satisfied = $webserver_supports_https and
        $database_driver_is_installed and
        $pdr_directories_are_writable and
        $php_extension_requirements_are_fulfilled and
        $php_version_requirement_is_fulfilled;
?>
<p>This page is meant to check if:</p>
<ul>
    <li> the webserver supports HTTPS
        <?php
        /*
         * Check if we are running on https:
         */
        if ($webserver_supports_https) {
            echo "<em class='install_info_postive'>passed</em>";
        } else {
            echo "<em class='install_info_negative'>failed</em>";
            echo $install->build_error_message_div();
        }
        ?>
    </li>
    <li> PHP supports connections to a supported database
        <?php
        /*
         * Check if there is any supported database driver available:
         */
        if ($database_driver_is_installed) {
            echo "<em class='install_info_postive'>passed</em>";
        } else {
            echo "<em class='install_info_negative'>failed</em>";
            echo $install->build_error_message_div();
        }
        ?>
    </li>
    <li> required PHP extensions are loaded
        <?php
        /*
         * Check if the PHP version is new enough to support the required features:
         */
        if ($php_extension_requirements_are_fulfilled) {
            echo "<em class='install_info_postive'>passed</em>";
        } else {
            echo "<em class='install_info_negative'>failed</em>";
            echo $install->build_error_message_div();
        }
        ?>
    </li>
    <li> required PHP version is running
        <?php
        /*
         * Check if the PHP version is new enough to support the required features:
         */
        if ($php_version_requirement_is_fulfilled) {
            echo "<em class='install_info_postive'>passed</em>";
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
        if ($pdr_directories_are_writable) {
            echo "<em class='install_info_postive'>passed</em>";
        } else {
            echo "<em class='install_info_negative'>failed</em>";
            echo $install->build_error_message_div();
        }
        ?>
    </li>
</ul>
<?php
if ($all_requirements_are_satisfied) {
    ?>
    <form action="install_page_database.php" method="post">
        <input type="submit" value="<?= gettext("Next") ?>">
    </form>
<?php } else { ?>
    <form action="install_page_check_requirements.php" method="post">
        <input type="submit" value="<?= gettext("Retry") ?>">
    </form>

<?php } ?>

