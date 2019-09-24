/*
 * Copyright (C) 2019 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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

/*
 * One roster_item holds one set of information of one employee on one day.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
/*jshint esversion: 6 */

class roster_item {

set duty_start_int (variable_value) {
this.duty_start_int_value = variable_value;
        this.calculate_durations();
}
set duty_start_sql (variable_value) {
this.duty_start_sql_value = variable_value;
        this.calculate_durations();
}
set duty_end_int (variable_value) {
this.duty_end_int_value = variable_value;
        this.calculate_durations();
}
set duty_end_sql (variable_value) {
this.duty_end_sql_value = variable_value;
        this.calculate_durations();
}
set break_start_int (variable_value) {
this.break_start_int_value = variable_value;
        this.calculate_durations();
}
set break_start_sql (variable_value) {
this.break_start_sql_value = variable_value;
        this.calculate_durations();
}
set break_end_int (variable_value) {
this.break_end_int_value = variable_value;
        this.calculate_durations();
}
set break_end_sql (variable_value) {
this.break_end_sql_value = variable_value;
        this.calculate_durations();
}


get duty_start_int () {
return this.duty_start_int_value;
}
get duty_start_sql () {
return this.duty_start_sql_value;
        }
get duty_end_int () {
return this.duty_end_int_value;
        }
get duty_end_sql () {
return this.duty_end_sql_value;
        }
get break_start_int () {
return this.break_start_int_value;
        }
get break_start_sql () {
return this.break_start_sql_value;
        }
get break_end_int () {
return this.break_end_int_value;
        }
get break_end_sql () {
return this.break_end_sql_value;
        }


constructor(date_sql_value, employee_id, branch_id, duty_start, duty_end, break_start = null, break_end = null, comment = null) {

this.date_object = new Date(date_sql_value);
        this.date_sql_value = this.date_object.getFullYear() + "-" + this.date_object.getMonth() + "-" + this.date_object.getDate();
        this.date_unix = this.date_object.getTime() / 1000;
        this.employee_id = employee_id;
        this.branch_id = Number(branch_id);
        this.duty_start_sql_value = duty_start;
        this.duty_start_int_value = this.convert_time_to_seconds(duty_start);
        this.duty_end_sql_value = duty_end;
        this.duty_end_int_value = this.convert_time_to_seconds(duty_end);
        this.break_start_sql_value = break_start;
        this.break_start_int_value = this.convert_time_to_seconds(break_start);
        this.break_end_sql_value = break_end;
        this.break_end_int_value = this.convert_time_to_seconds(break_end);
        this.comment = comment;
        this.weekday = this.date_object.getDay(); //CAVE: Will this is fom 0-6 0 = Sunday
        this.calculate_durations();
}

convert_time_to_seconds(time_string = null) {
if (null === time_string) {
return null;
}
let time_array = time_string.split(':');
        if (3 === time_array.length){
let seconds = 3600 * time_array[0] + 60 * time_array[1] + time_array[2];
        return seconds;
}
if (2 === time_array.length){
let seconds = 3600 * time_array[0] + 60 * time_array[1];
        return seconds;
}
}

calculate_durations() {
console.log("calculate_durations");
        /*
         * TODO: This does not take into account, that emergency service is not calculated as full hours.
         * Emergeny service calculation might differ between states, federal states, or even employees with different contracts.
         */
        this.duty_duration = this.duty_end_int_value - this.duty_start_int_value;
        this.break_duration = this.break_end_int_value - this.break_start_int_value;
        this.working_seconds = (this.duty_duration - this.break_duration);
        this.working_hours = Math.round(this.working_seconds / 3600, 2);
}



}

/*
 let some_roster_item = new roster_item("2019-09-21", 5, 1, "10:00", "18:00", null, null, null);
 console.log(some_roster_item);
 */