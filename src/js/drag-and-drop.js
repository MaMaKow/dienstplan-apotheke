/*
 * Copyright (C) 2016 Mandelkow
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


/* global bar_width_factor: is a factor generated within image_dienstplan.php It describes the width of one hour in pixels. */

var selectedElement = 0;
var currentX = 0;
var currentY = 0;
var currentMatrix = 0;
var firstX = 0;
var firstY = 0;

function roster_change_table_on_drag_of_bar_plot(evt, moveType) {
    if (!document.getElementById('roster_form') && !document.getElementById('principle_roster_form')) {
        /*
         * If there is no roster form, then there is nothing to change by moving around.
         */
        return;
    }


    if (moveType === 'single') {
        selectedElement = evt.target;
    } else if (moveType === 'group') {
        selectedElement = evt.target.parentNode;
    } else {
        console.log('Error: roster_change_table_on_drag_of_bar_plot() has to be called with a moveType of either "single" or "group"!' + evt + ", " + moveType);
    }
    firstX = evt.clientX;
    firstY = evt.clientY;
    currentX = evt.clientX;
    currentY = evt.clientY;
    selectedElement.setAttributeNS(null, "onmouseup", "deselectElement(evt)");
    selectedElement.classList.add("selected");
    if (selectedElement.firstChild) {
        selectedElement.firstChild.classList.add("selected");
    }
    selectedElement.parentNode.setAttributeNS(null, "onmousemove", "moveElement(evt)");
}

function moveElement(evt) {
    if (1 !== evt.buttons) {
        deselectElement(evt);
        return false;
    }
    var dx = (evt.clientX - currentX) * 0.8;
    selectedElement.x.baseVal.value += dx;
    currentX = evt.clientX;
    currentY = evt.clientY;
    return true;
}
function deselectElement(evt) {
    if (selectedElement !== 0) {
        var svg_element = selectedElement.parentNode.parentNode;
        var box_type = selectedElement.dataset.box_type;
        var line = selectedElement.dataset.line;
        var margin_before_bar = Number(svg_element.dataset.outer_margin_x) + Number(svg_element.dataset.inner_margin_x);
        var start_hour_float = Math.round((selectedElement.x.baseVal.value - margin_before_bar) / bar_width_factor * 2) / 2;
        selectedElement.x.baseVal.value = start_hour_float * bar_width_factor + margin_before_bar;
        var end_hour_float = (selectedElement.x.baseVal.value - margin_before_bar + selectedElement.width.baseVal.value) / bar_width_factor;
        var date_unix = selectedElement.dataset.date_unix;
        sync_from_bar_plot_to_roster_array_object(box_type, date_unix, line, start_hour_float, end_hour_float);
        selectedElement.parentNode.removeAttributeNS(null, "onmousemove");
        selectedElement.removeAttributeNS(null, "onmouseout");
        selectedElement.removeAttributeNS(null, "onmouseup");
        selectedElement.classList.remove("selected");
        if (selectedElement.firstChild) {
            selectedElement.firstChild.classList.remove("selected");
        }
        selectedElement = 0;
    }
}

function roster_change_bar_plot_on_change_of_table(input_object) {
    /*
     * Change the one directly changed column:
     */
    var date_unix = input_object.dataset.date_unix;
    var roster_row_iterator = input_object.dataset.roster_row_iterator;
    var roster_column_name = input_object.dataset.roster_column_name;
    var roster_item = Roster_array[date_unix][roster_row_iterator];
    roster_item[roster_column_name] = input_object.value;
    /*
     * Calculate the resulting information for the other columns:
     */
    var duty_start_object = new Date(roster_item['date_sql'] + 'T' + roster_item['duty_start_sql']);
    var duty_end_object = new Date(roster_item['date_sql'] + 'T' + roster_item['duty_end_sql']);
    var break_start_object = new Date(roster_item['date_sql'] + 'T' + roster_item['break_start_sql']);
    var break_end_object = new Date(roster_item['date_sql'] + 'T' + roster_item['break_end_sql']);
    var break_duration_integer = (break_end_object - break_start_object);
    var working_hours = (duty_end_object - duty_start_object - break_duration_integer) / 3600 / 1000;

    roster_item['working_hours'] = Math.round(working_hours * 4, 0) / 4;//round to quarter hours
    roster_item['working_seconds'] = working_hours * 3600;
    roster_item['break_duration'] = break_duration_integer / 1000;
    roster_item['duty_duration'] = (duty_end_object - duty_start_object) / 3600 / 1000;
    roster_item['duty_start_int'] = duty_start_object.getHours() * 3600 + duty_start_object.getMinutes() * 60;
    roster_item['duty_end_int'] = duty_end_object.getHours() * 3600 + duty_end_object.getMinutes() * 60;
    roster_item['break_start_int'] = break_start_object.getHours() * 3600 + break_start_object.getMinutes() * 60;
    roster_item['break_end_int'] = break_end_object.getHours() * 3600 + break_end_object.getMinutes() * 60;
    /*
     * sync the information to the bar plot:
     */
    sync_from_roster_array_object_to_bar_plot(roster_row_iterator, date_unix);
}

function sync_from_roster_array_object_to_bar_plot(roster_row_iterator, date_unix) {
    /*
     * duty start and duty end:
     */
    var roster_item = Roster_array[date_unix][roster_row_iterator];
    var bar_element_id = 'work_box_' + roster_row_iterator + '_' + date_unix;
    var bar_element = document.getElementById(bar_element_id);
    var svg_element = bar_element.parentNode.parentNode;
    var margin_before_bar = Number(svg_element.dataset.outer_margin_x) + Number(svg_element.dataset.inner_margin_x);
    var bar_width_factor = svg_element.dataset.bar_width_factor;
    var duty_start_object = new Date(roster_item['date_sql'] + 'T' + roster_item['duty_start_sql']);
    var duty_end_object = new Date(roster_item['date_sql'] + 'T' + roster_item['duty_end_sql']);
    var break_start_object = new Date(roster_item['date_sql'] + 'T' + roster_item['break_start_sql']);
    var break_end_object = new Date(roster_item['date_sql'] + 'T' + roster_item['break_end_sql']);
    /*var break_duration_integer = (break_end_object - break_start_object);*/
    var new_bar_x = (duty_start_object.getHours() + duty_start_object.getMinutes() / 60) * bar_width_factor + margin_before_bar;
    var new_bar_width = (
            (duty_end_object.getHours() + duty_end_object.getMinutes() / 60)
            -
            (duty_start_object.getHours() + duty_start_object.getMinutes() / 60)
            ) * bar_width_factor;
    bar_element.x.baseVal.value = new_bar_x;
    bar_element.width.baseVal.value = new_bar_width;
    /*
     * lunch break:
     */
    var break_box_id = 'break_box_' + roster_row_iterator + '_' + date_unix;
    var break_box_element = document.getElementById(break_box_id);
    var new_box_x = (break_start_object.getHours() + break_start_object.getMinutes() / 60) * bar_width_factor + margin_before_bar;
    var new_box_width = (
            (break_end_object.getHours() + break_end_object.getMinutes() / 60)
            -
            (break_start_object.getHours() + break_start_object.getMinutes() / 60)
            ) * bar_width_factor;
    break_box_element.x.baseVal.value = new_box_x;
    break_box_element.width.baseVal.value = new_box_width;

    var employee_name_text_element = bar_element.childNodes[0].childNodes[0];
    var working_hours_span = bar_element.childNodes[0].childNodes[1];

    employee_name_text_element.nodeValue = List_of_employee_names[roster_item['employee_id']];
    working_hours_span.innerText = roster_item['working_hours'];
}


function sync_from_bar_plot_to_roster_array_object(box_type, date_unix, roster_row_iterator, start_hour_float, end_hour_float) {
    var roster_item = Roster_array[date_unix][roster_row_iterator];
    switch (box_type) {
        case 'work_box':
            roster_item['duty_start_int'] = start_hour_float * 3600;
            roster_item['duty_end_int'] = end_hour_float * 3600;
            roster_item['duty_start_sql'] = format_time_int_to_string(start_hour_float * 3600);
            roster_item['duty_end_sql'] = format_time_int_to_string(end_hour_float * 3600);
            sync_from_roster_array_to_table(date_unix, roster_row_iterator, 'Dienstbeginn', roster_item['duty_start_sql'])
            sync_from_roster_array_to_table(date_unix, roster_row_iterator, 'Dienstende', roster_item['duty_end_sql'])
            break;
        case 'break_box':
            roster_item['break_start_int'] = start_hour_float * 3600;
            roster_item['break_start_sql'] = format_time_int_to_string(start_hour_float * 3600);
            roster_item['break_end_int'] = end_hour_float * 3600;
            roster_item['break_end_sql'] = format_time_int_to_string(end_hour_float * 3600);
            sync_from_roster_array_to_table(date_unix, roster_row_iterator, 'Mittagsbeginn', roster_item['break_start_sql'])
            sync_from_roster_array_to_table(date_unix, roster_row_iterator, 'Mittagsende', roster_item['break_end_sql'])
            break;
        default:
            console.log('Error: sync_from_bar_plot_to_roster_array_object() only works on the "break_box" and on the "work_box"!' + box_type);
            break;

    }
}

function sync_from_roster_array_to_table(date_unix, roster_row_iterator, column, value) {
    var input_element_id = "Dienstplan[" + date_unix + "][" + column + "][" + roster_row_iterator + "]";
    var input_element = document.getElementById(input_element_id);
    input_element.value = value;
}


/**
 * This function expects an integer representing the seconds since the start of the day.
 */
function format_time_int_to_string(time_int) {
    var hour_int = Math.floor(time_int / 3600);
    var minute_int = Math.floor(time_int % 3600 / 60);
    /*
     * The function pad() is defined in datepicker.js
     */
    var time_string = pad(hour_int, 2) + ':' + pad(minute_int, 2);
    return time_string;
}
