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


function update_overtime_balance()
{
    /*
     * Get the objects from the HTML:
     */
    var overtime_input_object = document.getElementById("stunden");
    var overtime_balance_old_object = document.getElementById("balance_old");
    var overtime_balance_new_object = document.getElementById("balance_new");
    /*
     * Get the existent values:
     */
    if (overtime_balance_old_object !== null) {
        var overtime_balance_value = Number(overtime_balance_old_object.dataset.balance);
    } else {
        /*
         * For new Coworkers there is no value set. Therefore we start with 0.
         */
        var overtime_balance_value = 0;
    }
    var stundenInputArray = overtime_input_object.value.split(":");
    if (stundenInputArray[1]) //Wenn es einen Doppelpunkt gibt.
    {
        /*
         * The input is a number formated with a colon (:).
         * We recalculate it as a float number of hours.
         */
        var stundenInputHour = Number(stundenInputArray[0]);
        var stundenInputMinute = Number(stundenInputArray[1]);
        var stundenInputSecond = Number(stundenInputArray[2]);
        //Jetzt berechnen wir aus den Daten eine Summe. Dazu formen wir zunächst in ein gültiges Datum um.
        var overtime_input_value = 0; // Wir initialisieren den Input als Null und addieren dann Sekunden, Minuten und Stunden dazu.
        if (!isNaN(stundenInputSecond))
        {
            overtime_input_value = overtime_input_value + stundenInputSecond / 3600;
        }
        if (!isNaN(stundenInputMinute))
        {
            overtime_input_value = overtime_input_value + stundenInputMinute / 60;
        }
        if (!isNaN(stundenInputHour))
        {
            overtime_input_value = overtime_input_value + stundenInputHour;
        }
        overtime_input_object.value = overtime_input_value;
    } else {
        /*
         * The hours are inserted as an integer or float value.
         * Commas are exchanged by decimal points.
         */
        overtime_input_object.value = overtime_input_object.value.replace(/,/g, '.');
        var overtime_input_value = Number(overtime_input_object.value);
    }
    overtime_balance_new_object.innerHTML = overtime_input_value + overtime_balance_value;
}

function overtime_input_validation() {
    console.log('validating overtime input');
    var user_sequence_warning_object = document.getElementById('user_sequence_warning');
    var date_input = document.getElementById('date_chooser_input').value;
    var date_of_last_entry = document.getElementById('date_of_last_entry').value;
    if (date_of_last_entry > date_input) {
        var message = gettext('The input date lies before the last existent date.');
        message += ' ';
        message += gettext('Are you sure, that the data is correct?');
        var result = confirm(message);
        user_sequence_warning_object.value = result;
        return result;
    }
    return true;
}
