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
function selectElement(evt, moveType) {
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
        console.log('Error: selectElement() has to be called with a moveType of either "single" or "group"!' + evt + ", " + moveType);
    }
    firstX = evt.clientX;
    firstY = evt.clientY;
    currentX = evt.clientX;
    currentY = evt.clientY;
    currentMatrix = selectedElement.getAttributeNS(null, "transform").slice(7, -1).split(' ');
    for (var i = 0; i < currentMatrix.length; i++) {
        currentMatrix[i] = parseFloat(currentMatrix[i]);
    }
    //selectedElement.setAttributeNS(null, "onmouseout", "deselectElement(evt)");
    selectedElement.setAttributeNS(null, "onmouseup", "deselectElement(evt)");
    console.log(selectedElement.firstChild);
    selectedElement.firstChild.classList.add("selected");
    console.log(selectedElement.firstChild.style);
    selectedElement.parentNode.setAttributeNS(null, "onmousemove", "moveElement(evt)");
}

function moveElement(evt) {
    if (1 !== evt.buttons) {
        deselectElement(evt);
        return false;
    }
    dx = (evt.clientX - currentX) * 0.8;
    currentMatrix[4] += dx;
    newMatrix = "matrix(" + currentMatrix.join(' ') + ")";
    selectedElement.setAttributeNS(null, "transform", newMatrix);
    currentX = evt.clientX;
    currentY = evt.clientY;
}
function deselectElement(evt) {
    if (selectedElement !== 0) {
        currentMatrix[4] = Math.round(currentMatrix[4] / bar_width_factor * 2) * (bar_width_factor / 2);
        var diff_hour = (Math.round(currentMatrix[4] / bar_width_factor * 2) / 2).valueOf();
        newMatrix = "matrix(" + currentMatrix.join(' ') + ")";
        selectedElement.setAttributeNS(null, "transform", newMatrix);
        var rect_id = selectedElement.id;
        var line = rect_id.substring(rect_id.lastIndexOf('_') + 1);
        var column = rect_id.substring(0, rect_id.lastIndexOf('_'));
        if (column === "break_box") {
            syncToTable(line, 'Dienstplan_Mittagbeginn', diff_hour);
            syncToTable(line, 'Dienstplan_Mittagsende', diff_hour);
        } else if (column === "work_box") {
            syncToTable(line, 'Dienstplan_Dienstbeginn', diff_hour);
            syncToTable(line, 'Dienstplan_Dienstende', diff_hour);
        } else {
            console.log('Error: deselectElement() only works on the "break_box" and on the "work_box"!' + evt + ", " + column);
        }
        selectedElement.parentNode.removeAttributeNS(null, "onmousemove");
        selectedElement.removeAttributeNS(null, "onmouseout");
        selectedElement.removeAttributeNS(null, "onmouseup");
        selectedElement.firstChild.classList.remove("selected");
        selectedElement = 0;
    }
}

function syncToTable(line, column, diff_hour) {
    var List_of_input_elements = document.getElementsByClassName(column);
    var input_id = List_of_input_elements[line];
//    var previous_time = input_id.value;
    var previous_time = input_id.defaultValue;
    var previous_hour = parseFloat(previous_time.substring(0, previous_time.lastIndexOf(':')));
    var previous_minute = parseFloat(previous_time.substring(previous_time.lastIndexOf(':') + 1));
    var previous_minute_float = parseFloat(previous_minute / 60);
    var previous_hour_float = previous_hour + previous_minute_float;
    var new_time = (previous_hour + (previous_minute / 60) + diff_hour);
    var new_time_minute = (new_time % 1) * 60;
    var new_time_hour = Math.floor(new_time);
    var new_time_minute_string = ('0' + new_time_minute).slice(-2); //Add a fronting 0 to reach the HH:MM format, which is required.
    var new_time_hour_string = ('0' + new_time_hour).slice(-2);
    var new_time_string = new_time_hour_string + ":" + new_time_minute_string;
    input_id.value = new_time_string;
}
