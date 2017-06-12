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
require_once "../../default.php";
require_once "../../db-lesen-mitarbeiter.php";
?>
<form id="input_box_form" method="POST">
    <select name="employee_id" id="employee_id_select">
        <?php
        foreach ($Mitarbeiter as $employee_id => $last_name) {
            echo "\t\t<option id='employee_id_option_$employee_id' value=$employee_id>";
            echo "$last_name";
            echo "</option>\n";
        }
        ?>
    </select>
    <img src="" style="width: 0" alt="" 
         onerror="prefill_input_box_form(); this.parentNode.removeChild(this);"
         comment="This element is necessary to allow interaction of javascript with this element. After the execution, it is removed."
         />
    <input type="date" id="input_box_form_start_date" name="start_date">
    <input type="date" id="input_box_form_end_date" name="end_date">
    <input type="text" id="input_box_form_reason" name="reason" list='reasons'>
    <button type="submit" value="save" name="command" class="button_tight">Speichern</button>
    <button type="submit" value="delete" name="command" id="input_box_form_button_delete" class="button_tight">Löschen</button>
    <input type="hidden" id="employee_id_old" name="employee_id_old">
    <input type="hidden" id="input_box_form_start_date_old" name="start_date_old">
</form>
