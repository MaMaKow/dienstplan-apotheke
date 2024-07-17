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

require_once '../classes/PDR/Application/Installation/InstallUtility.php';
require_once '../classes/class.localization.php';
require_once '../classes/PDR/Utility/GeneralUtility.php';
$installUtility = new \PDR\Application\Installation\InstallUtility();
$languageInput = filter_input(INPUT_GET, "language", FILTER_SANITIZE_SPECIAL_CHARS);
$languageBCP47 = localization::standardizeLanguageCode($languageInput);
localization::initialize_gettext($languageBCP47);
if ("" != filter_input(INPUT_POST, "InstallPageCheckRequirementsFormButton", FILTER_SANITIZE_SPECIAL_CHARS)) {
    /*
     * https://de.wikipedia.org/wiki/Post/Redirect/Get
     */
    header("Location: install_page_database.php?language=" . $languageBCP47);
}

$installConfiguration = new \PDR\Application\Installation\InstallConfiguration();
$systemRequirementsValidator = new \PDR\Application\Installation\SystemRequirementsValidator($installUtility, $installConfiguration);
$webserver_supports_https = $systemRequirementsValidator->webserverSupportsHttps($installConfiguration->getPdrFileSystemApplicationPath());
$databaseDriverIsInstalled = $systemRequirementsValidator->databaseDriverIsInstalled();
$pdrDirectoriesAreWritable = $systemRequirementsValidator->pdrDirectoriesAreWritable();
$pdrSecretDirectoriesAreNotVisible = $systemRequirementsValidator->pdrSecretDirectoriesAreNotVisible();
$phpExtensionRequirementsAreFulfilled = $systemRequirementsValidator->phpExtensionRequirementsAreFulfilled();
$phpVersionRequirementIsFulfilled = $systemRequirementsValidator->phpVersionRequirementIsFulfilled();

$allRequirementsAreSatisfied = $databaseDriverIsInstalled &&
        $pdrDirectoriesAreWritable &&
        $pdrSecretDirectoriesAreNotVisible &&
        $phpExtensionRequirementsAreFulfilled &&
        $phpVersionRequirementIsFulfilled;
require_once 'install_head.php';
?>
<p><?= gettext("This page is meant to check if:") ?></p>
<ul>
    <li> <?= gettext("the webserver supports HTTPS") ?>
        <?php
        /*
         * Check if we are running on https:
         */
        if ($webserver_supports_https) {
            echo "<em class = 'install_info_postive'>" . gettext("passed") . "</em>";
        } else {
            echo "<em class = 'install_info_negative'>" . gettext("failed") . "</em>";
            echo $installUtility->buildErrorMessageDiv();
        }
        ?>
    </li>
    <li> <?= gettext("PHP supports connections to a supported database") ?>
        <?php
        /*
         * Check if there is any supported database driver available:
         */
        if ($databaseDriverIsInstalled) {
            echo "<em class = 'install_info_postive'>" . gettext("passed") . "</em>";
        } else {
            echo "<em class = 'install_info_negative'>" . gettext("failed") . "</em>";
            echo $installUtility->buildErrorMessageDiv();
        }
        ?>
    </li>
    <li> <?= gettext("required PHP extensions are loaded") ?>
        <?php
        /*
         * Check if the PHP version is new enough to support the required features:
         */
        if ($phpExtensionRequirementsAreFulfilled) {
            echo "<em class = 'install_info_postive'>" . gettext("passed") . "</em>";
        } else {
            echo "<em class = 'install_info_negative'>" . gettext("failed") . "</em>";
            echo $installUtility->buildErrorMessageDiv();
        }
        ?>
    </li>
    <li> <?= gettext("required PHP version is running") ?>
        <?php
        /*
         * Check if the PHP version is new enough to support the required features:
         */
        if ($phpVersionRequirementIsFulfilled) {
            echo "<em class = 'install_info_postive'>" . gettext("passed") . "</em>";
        } else {
            echo "<em class = 'install_info_negative'>" . gettext("failed") . "</em>";
            echo $installUtility->buildErrorMessageDiv();
        }
        ?>
    </li>
    <li> <?= gettext("directories (e.g. upload) are writable by the program") ?>
        <?php
        /*
         * Check if there is write access to all write-necessary directories:
         */
        if ($pdrDirectoriesAreWritable) {
            echo "<em class = 'install_info_postive'>" . gettext("passed") . "</em>";
        } else {
            echo "<em class = 'install_info_negative'>" . gettext("failed") . "</em>";
            echo $installUtility->buildErrorMessageDiv();
        }
        ?>
    </li>
    <li> <?= gettext("secret directories (e.g. config) are not visible on the web") ?>
        <?php
        /*
         * Check if there is a 403 forbidden error when trying to access hidden folders:
         */
        if ($pdrSecretDirectoriesAreNotVisible) {
            echo "<em class = 'install_info_postive'>" . gettext("passed") . "</em>";
        } else {
            echo "<em class = 'install_info_negative'>" . gettext("failed") . "</em>";
            /**
             * @TODO 1. Test if this is an apache server!
             * @TODO 2. gettext()
             */
            echo "\n<br>You may want to add the following lines to the bottom of the apache configuration file:";
            echo "<pre>&lt;
    Directory " . PDR_FILE_SYSTEM_APPLICATION_PATH . " & gt;
    AllowOverride Limit Options
    &lt;
    /Directory&gt;
    </pre>
    ";
            echo $installUtility->buildErrorMessageDiv();
        }
        ?>
    </li>
</ul>
<?php
if (true === $allRequirementsAreSatisfied) {
    ?>
    <form action="install_page_check_requirements.php?language=<?= $languageBCP47 ?>" method="post">
        <input type="submit" id="InstallPageCheckRequirementsFormButton" name="InstallPageCheckRequirementsFormButton" value="<?= gettext("Next") ?>">
    </form>
<?php } else { ?>
    <form action="install_page_check_requirements.php" method="post">
        <input type="submit" value="<?= gettext("Retry") ?>">
    </form>

<?php } ?>

