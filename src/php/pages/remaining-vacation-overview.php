<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
$year = user_input::get_variable_from_any_input('year', FILTER_SANITIZE_NUMBER_INT, date('Y'));
\PDR\Utility\GeneralUtility::createCookie('year', $year, 1);
if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted; PRG
    $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/remaining-vacation-overview.php' . "?year=$year";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';

echo \form_element_builder::build_html_select_year($year);

$vacationPageBuilder = new \PDR\Output\HTML\vacationPageBuilder;
$table = $vacationPageBuilder->build_overview_table($year);
echo $table;
