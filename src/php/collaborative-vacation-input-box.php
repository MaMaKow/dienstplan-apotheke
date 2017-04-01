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
?>
<form id="input_box_form">
    <select name="employee_id" id="employee_id_select">
<?php
        require_once "../../default.php";
        require_once "../../db-lesen-mitarbeiter.php";
        foreach ($Mitarbeiter as $employee_id => $last_name) {
            echo "\t\t<option id='employee_id_option_$employee_id' value=$employee_id>";
            echo "$last_name";
            echo "</option>\n";
        }
        ?>
    </select>
    <input type="date" name="start_date">
    <input type="date" name="end_date">
    <input type="submit">
</form>
