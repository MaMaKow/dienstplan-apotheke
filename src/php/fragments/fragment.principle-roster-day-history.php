<?php

/*
 * Copyright (C) 2019 Mandelkow <netbeans@martin-mandelkow.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once '../../../default.php';

$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT);
$branch_id = user_input::get_variable_from_any_input('branch_id', FILTER_SANITIZE_NUMBER_INT);
$alternating_week_id = user_input::get_variable_from_any_input('alternating_week_id', FILTER_SANITIZE_NUMBER_INT);
$weekday = user_input::get_variable_from_any_input('weekday', FILTER_SANITIZE_NUMBER_INT);

if (!filter_has_var(INPUT_POST, 'weekday')) {
    $url = PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/principle-roster-day.php";
    if (!headers_sent()) {
        header("Status: 307 Temporary Redirect");
        header("Location: $url");
    } else {
        echo '<script type="javascript">document.location.href="' . $url . '";</script>';
    }
    exit();
}

$List_of_history_dates = principle_roster_history::get_list_of_history_dates($weekday, $alternating_week_id, $branch_id);
echo "<HTML>";
echo "<HEAD>";
echo "<LINK rel='stylesheet' type='text/css' href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/css/style.css' media='all'>";
echo "<LINK rel='stylesheet' type='text/css' href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/css/form_and_input.css' media='all'>";
echo "</HEAD>";
echo "<BODY>";

echo "<p>" . gettext('Please choose a date!') . "</p>";
echo "<p class='hint'>" . gettext('Dies ist eine Liste von Daten. An jedem Datum wurde der Grundplan für diesen Wochentag für mindestens einen Mitarbeiter geändert. Klicken Sie auf eines der Daten um den Grundplan an diesem Tag zu betrachten.') . "</p>";

echo "<form id='principle_roster_history_form' action='../pages/principle-roster-day.php' method='POST'>";
echo "<fieldset>";
echo gettext('valid from') . ":<br>";
foreach ($List_of_history_dates as $history_date) {
    echo "<label>";
    echo "<input type='radio' name='chosen_history_date_valid_from' value='" . $history_date->format('Y-m-d') . "' onchange='this.form.submit();' >";
    echo $history_date->format('d.m.Y');
    echo "</label>";
    echo "<br>";
}
echo "</fieldset>";
echo "</form>";

echo "</BODY>";
echo "</HTML>";
