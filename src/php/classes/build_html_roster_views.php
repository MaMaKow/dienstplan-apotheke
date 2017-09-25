<?php

/*
 * Copyright (C) 2017 Mandelkow
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

/*
 * Build one table column for a weekly view
 *
 * used by: woche-out.php
 * @param $Absentees array expects an array of absent employees in the format array(employee_id => reason_for_absence)
 *
 * @return string HTML table column
 */

function build_absentees_column($Absentees) {
    global $Mitarbeiter;
    $text = "\t\t<td class='absentees_column'><b>" . gettext("Absentees") . "</b><br>";
    foreach ($Absentees as $employee_id => $reason) {
        $text.= $Mitarbeiter[$employee_id] . " (" . $reason . ")<br>";
    }
    $text .= "</td>\n";
    return $text;
}

/*
 * Build one table row for a daily view
 *
 * used by: tag-in.php
 * @param $Absentees array expects an array of absent employees in the format array(employee_id => reason_for_absence)
 *
 * @return string HTML table row
 */

function build_absentees_row($Absentees) {
    $text = "\t\t<tr>";
    $text .= build_absentees_column($Absentees);
    $text .= "</tr>\n";
    return $text;
}
