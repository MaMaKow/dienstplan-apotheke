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


    /**
     * @todo Cange the signature of this function! movetype can be (safely) removed.
     * We now have everything inside of groups.
     */
    selectedElement = evt.target.parentNode;
    firstX = evt.clientX;
    firstY = evt.clientY;
    currentX = evt.clientX;
    currentY = evt.clientY;
    selectedElement.setAttributeNS(null, "onmouseup", "deselectElement(evt)");
    selectedElement.classList.add("selected");
    if (selectedElement.firstChild) {
        if (selectedElement.firstChild.classList) { // TODO: The number of working hours is a child of the text. When the drag and drop occurs on the number, it will not have a child with a classlist.
            selectedElement.firstChild.classList.add("selected");
        }
    }
    selectedElement.parentNode.setAttributeNS(null, "onmousemove", "moveElement(evt)");
}

function moveElement(evt) {
    if (1 !== evt.buttons) {
        deselectElement(evt);
        return false;
    }
    var dx = (evt.clientX - currentX) * 0.8;
    let rectElement = selectedElement.children[0];
    let textElement = selectedElement.children[1];
    rectElement.x.baseVal.value += dx;
    textElement.x.baseVal.value += dx;
    currentX = evt.clientX;
    currentY = evt.clientY;
    return true;
}
function deselectElement(evt) {
    var result = null;
    if (selectedElement !== 0) {
        let svg_element = selectedElement.parentNode.parentNode;
        let box_type = selectedElement.dataset.box_type;
        let date_unix = selectedElement.dataset.date_unix;
        let line = selectedElement.dataset.line;
        let rectElement = selectedElement.children[0];

        let bar_width_factor = svg_element.dataset.bar_width_factor;

        let margin_before_bar = Number(svg_element.dataset.outer_margin_x) + Number(svg_element.dataset.inner_margin_x);
        let start_hour_float = Math.round((rectElement.x.baseVal.value - margin_before_bar) / bar_width_factor * 2) / 2;
        rectElement.x.baseVal.value = start_hour_float * bar_width_factor + margin_before_bar;
        let end_hour_float = (rectElement.x.baseVal.value - margin_before_bar + rectElement.width.baseVal.value) / bar_width_factor;
        if (start_hour_float < 0 || end_hour_float >= 24) {
            /**
             * <p lang=de>
             * Momentan kann das Programm nur Werte zwischen 0:00 und 23:59 Uhr verarbeiten.
             * Wenn der Balken weiter vor oder zurück verschoben wird, so werden fehlerhafte Daten (null für duty_start oder duty_end) übertragen.
             * Auf Serverseite (PHP) führt das zum Verwerfen des Eintrages. Der Mitarbeiter wird aus dem Dienstplan gelöscht.
             * </p>
             */
            writeErrorToUserDialogContainer("Achtung beim Verschieben! Es sind nur Werte zwischen 0:00 und 23:59 Uhr erlaubt.", "dragAndDropOutOfRangeError");
            sync_from_roster_array_object_to_bar_plot(line, date_unix);
            result = false;
        } else {
            /**
             * <p lang=de>Der Input ist jetzt korrekt.
             * Vielleicht war er vorher mal kaputt.
             * Wir entfernen eventuelle Fehlermeldungen.
             * </p>
             */
            removeErrorFromUserDialogContainer("dragAndDropOutOfRangeError");
            /**
             * <p lang=de>Jetzt können wir den Dienstplan in der Tabelle ändern:</p>
             */
            sync_from_bar_plot_to_roster_array_object(box_type, date_unix, line, start_hour_float, end_hour_float);
            result = true;
        }
        selectedElement.parentNode.removeAttributeNS(null, "onmousemove");
        selectedElement.removeAttributeNS(null, "onmouseout");
        selectedElement.removeAttributeNS(null, "onmouseup");
        selectedElement.classList.remove("selected");
        if (selectedElement.firstChild) {
            if (selectedElement.firstChild.classList) {
                selectedElement.firstChild.classList.remove("selected");
            }
        }
        selectedElement = 0;
        return result;
    }
}

function roster_change_bar_plot_on_change_of_table(input_object) {
    var input_object_parent = input_object.parentNode;
    if (input_object_parent.nodeName !== "TD") {
        input_object_parent = input_object.parentNode.parentNode;
    }
    /*
     * Change the one directly changed column:
     */
    var date_unix = input_object.dataset.date_unix;
    var date_object = new Date();
    date_object.setTime(date_unix * 1000);
    var branch_id = input_object_parent.dataset.branch_id;
    var date_sql = input_object_parent.dataset.date_sql;
    var roster_row_iterator = input_object_parent.dataset.roster_row_iterator;
    var roster_column_name = input_object.dataset.roster_column_name;
    try {
        var roster_item = Roster_array[date_unix][roster_row_iterator];
    } catch (e) {
        if (!roster_item) {
            /*
             * Initialize the object with zero values:
             */
            Roster_array[date_unix][roster_row_iterator] = {
                branch_id: branch_id,
                comment: null,
                //date_sql: date_object.getFullYear() + "-" + date_object.getMonth() + "-" + date_object.getDate(),
                date_sql: date_sql,
                date_unix: date_unix,
                date_object: date_object,
                break_duration: 0,
                break_start_int: 0,
                break_end_int: 0,
                duty_duration: 0,
                duty_start_int: 0,
                duty_end_int: 0,
                duty_start_sql: "0:00",
                duty_end_sql: "0:00",
                break_start_sql: "0:00",
                break_end_sql: "0:00",
                employee_key: 0,
                working_hours: 0,
                working_seconds: 0
            };
            Roster_array[date_unix][roster_row_iterator][roster_column_name] = Number(input_object.value);
            roster_item = Roster_array[date_unix][roster_row_iterator];
        }
    }

    roster_item[roster_column_name] = input_object.value;
    /*
     * Calculate the resulting information for the other columns:
     */
    var duty_start_object = new Date(roster_item['date_sql'] + ' ' + roster_item['duty_start_sql']);
    var duty_end_object = new Date(roster_item['date_sql'] + ' ' + roster_item['duty_end_sql']);
    var break_start_object = new Date(roster_item['date_sql'] + ' ' + roster_item['break_start_sql']);
    var break_end_object = new Date(roster_item['date_sql'] + ' ' + roster_item['break_end_sql']);
    if (isValidDate(break_end_object) && isValidDate(break_start_object)) {
        var break_duration_integer = (break_end_object - break_start_object);
    } else {
        var break_duration_integer = 0;
    }
    var working_hours = (duty_end_object - duty_start_object - break_duration_integer) / 3600 / 1000;

    roster_item['working_hours'] = Math.round(working_hours * 4, 0) / 4;//round to quarter hours
    roster_item['working_seconds'] = working_hours * 3600;
    roster_item['break_duration'] = break_duration_integer / 1000;
    roster_item['duty_duration'] = (duty_end_object - duty_start_object) / 3600 / 1000;
    roster_item['duty_start_int'] = duty_start_object.getHours() * 3600 + duty_start_object.getMinutes() * 60;
    roster_item['duty_end_int'] = duty_end_object.getHours() * 3600 + duty_end_object.getMinutes() * 60;
    roster_item['break_start_int'] = break_start_object.getHours() * 3600 + break_start_object.getMinutes() * 60;
    roster_item['break_end_int'] = break_end_object.getHours() * 3600 + break_end_object.getMinutes() * 60;

    /**
     * Update working hours span:
     */
    var working_hours_span = document.getElementById("Dienstplan[" + date_unix + "][working_hours_span][" + roster_row_iterator + "]");
    working_hours_span.innerHTML = working_hours.toFixed(2);
    /**
     * sync the information to the bar plot:
     */
    sync_from_roster_array_object_to_bar_plot(roster_row_iterator, date_unix);
}

function sync_from_roster_array_object_to_bar_plot(roster_row_iterator, date_unix) {
    /*
     * duty start and duty end:
     */
    roster_row_iterator = Number(roster_row_iterator);
    var roster_item = Roster_array[date_unix][roster_row_iterator];
    var bar_element_id = 'work_box_' + roster_row_iterator + '_' + date_unix;
    var bar_element = document.getElementById(bar_element_id);

    if (!bar_element) {
        /*
         * This bar does not exist yet.
         * Most probably a new row has been inserted in the roster in the table.
         */
        var parent_of_bar_elements = document.getElementById('svg_img_g_' + date_unix);
        bar_element = create_new_bar_element(date_unix, roster_row_iterator, bar_element_id, parent_of_bar_elements);
        parent_of_bar_elements.appendChild(bar_element);
    }
    var svg_element = bar_element.parentNode.parentNode;
    var margin_before_bar = Number(svg_element.dataset.outer_margin_x) + Number(svg_element.dataset.inner_margin_x);
    var bar_width_factor = svg_element.dataset.bar_width_factor;
    var duty_start_object = new Date(roster_item['date_sql'] + ' ' + roster_item['duty_start_sql']);
    var duty_end_object = new Date(roster_item['date_sql'] + ' ' + roster_item['duty_end_sql']);
    /*
     * TODO: Insert error handling here. When the employe is changed to null and the break is edited, there is an error, which does not recover just by again adding the employee.
     */
    var break_start_object = new Date(roster_item['date_sql'] + ' ' + roster_item['break_start_sql']);
    var break_end_object = new Date(roster_item['date_sql'] + ' ' + roster_item['break_end_sql']);
    /*var break_duration_integer = (break_end_object - break_start_object);*/
    var new_bar_x = (duty_start_object.getHours() + duty_start_object.getMinutes() / 60) * bar_width_factor + margin_before_bar;
    var new_bar_width = (
            (duty_end_object.getHours() + duty_end_object.getMinutes() / 60)
            -
            (duty_start_object.getHours() + duty_start_object.getMinutes() / 60)
            ) * bar_width_factor;

    bar_element.setAttributeNS(null, 'x', new_bar_x);
    bar_element.setAttributeNS(null, 'width', new_bar_width);

    /*
     * lunch break:
     */
    if (isValidDate(break_start_object) && isValidDate(break_end_object)) {
        var new_box_x = (break_start_object.getHours() + break_start_object.getMinutes() / 60) * bar_width_factor + margin_before_bar;
        var new_box_width = (
                (break_end_object.getHours() + break_end_object.getMinutes() / 60)
                -
                (break_start_object.getHours() + break_start_object.getMinutes() / 60)
                ) * bar_width_factor;
        var break_box_id = 'break_box_' + roster_row_iterator + '_' + date_unix;
        var break_box_element = document.getElementById(break_box_id);
        if (!break_box_element) {
            /*
             * TODO: Also insert break boxes, if they are missing.
             */
            var parent_of_bar_elements = document.getElementById('svg_img_g_' + date_unix);
            break_box_element = create_new_break_rect(date_unix, new_box_x, new_box_width, roster_row_iterator, break_box_id, parent_of_bar_elements);
            parent_of_bar_elements.appendChild(break_box_element);
        }
        break_box_element.x.baseVal.value = new_box_x;
        break_box_element.width.baseVal.value = new_box_width;
    }
    var employee_name_p_element = bar_element.childNodes[0];
    var employee_name_text_element = bar_element.childNodes[0].childNodes[0];
    var working_hours_span = bar_element.childNodes[0].childNodes[1];
    employee_name_text_element.nodeValue = List_of_employee_names[roster_item['employee_key']];
    employee_name_p_element.setAttributeNS(null, 'class', List_of_employee_professions[roster_item['employee_key']]);
    working_hours_span.innerText = roster_item['working_hours'];
}

function create_new_bar_element(date_unix, roster_row_iterator, bar_element_id, parent_of_bar_elements) {
    var svg_element = parent_of_bar_elements.parentNode;
    var outer_margin_y = Number(svg_element.dataset.outer_margin_y);
    var inner_margin_y = Number(svg_element.dataset.inner_margin_y);
    var bar_height = Number(svg_element.dataset.bar_height);

    var new_foreignObject = document.createElementNS('http://www.w3.org/2000/svg', 'foreignObject');
    /*
     * Calculate the value of y:
     * The formula is exactly the same as in class.roster_image_bar_plot.php
     */
    var y_pos = outer_margin_y + (inner_margin_y * (roster_row_iterator + 1)) + (bar_height * roster_row_iterator);
    /*
     * Assign all the known values and methods to the new object:
     */
    new_foreignObject.setAttributeNS(null, 'id', bar_element_id);
    new_foreignObject.setAttributeNS(null, 'height', bar_height);
    new_foreignObject.setAttributeNS(null, 'y', y_pos);
    new_foreignObject.dataset.date_unix = date_unix;
    new_foreignObject.dataset.line = roster_row_iterator;
    new_foreignObject.dataset.box_type = 'work_box';
    new_foreignObject.setAttributeNS(null, 'onmousedown', 'roster_change_table_on_drag_of_bar_plot(evt, "group")');
    /*
     * Add a paragraph (p) to the foreignObject:
     * @type create_new_bar_element_p.new_p_element|Element
     */
    var new_p_element = create_new_bar_element_p();
    new_foreignObject.appendChild(new_p_element);

    /*
     * Return the new foreignObject as the new bar element:
     * @returns {new_foreignObject|Element}
     */
    return new_foreignObject;
}

function create_new_bar_element_p() {
    var new_p_element = document.createElementNS('http://www.w3.org/1999/xhtml', 'p');
    new_p_element.setAttributeNS(null, 'class', 'PTA');//TODO: Add the actual class for the specific employee!
    var new_text_node = document.createTextNode('');
    new_p_element.appendChild(new_text_node);
    var new_span_in_p = document.createElementNS('http://www.w3.org/1999/xhtml', 'span');
    new_span_in_p.innerHTML = "";
    new_p_element.appendChild(new_span_in_p);
    return new_p_element;
}

function create_new_break_rect(date_unix, new_box_x, new_box_width, roster_row_iterator, break_box_id, parent_of_bar_elements) {
    var svg_element = parent_of_bar_elements.parentNode;
    var bar_height = Number(svg_element.dataset.bar_height);
    var outer_margin_y = Number(svg_element.dataset.outer_margin_y);
    var inner_margin_y = Number(svg_element.dataset.inner_margin_y);

    /*
     * Calculate the value of y:
     * The formula is exactly the same as in class.roster_image_bar_plot.php
     */
    var y_pos = outer_margin_y + (inner_margin_y * (roster_row_iterator + 1)) + (bar_height * roster_row_iterator);

    var new_rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    new_rect.setAttributeNS(null, 'id', break_box_id);
    new_rect.setAttributeNS(null, 'x', new_box_x);
    new_rect.setAttributeNS(null, 'y', y_pos);
    new_rect.setAttributeNS(null, 'height', bar_height);
    new_rect.setAttributeNS(null, 'width', new_box_width);
    new_rect.setAttributeNS(null, 'onmousedown', 'roster_change_table_on_drag_of_bar_plot(evt, \"single\")');
    new_rect.dataset.box_type = 'break_box';
    new_rect.dataset.line = roster_row_iterator;
    new_rect.dataset.date_unix = date_unix;
    return new_rect;
}
function sync_from_bar_plot_to_roster_array_object(box_type, date_unix, roster_row_iterator, start_hour_float, end_hour_float) {
    var roster_item = Roster_array[date_unix][roster_row_iterator];
    switch (box_type) {
        case 'work_box':
            roster_item['duty_start_int'] = start_hour_float * 3600;
            roster_item['duty_end_int'] = end_hour_float * 3600;
            roster_item['duty_start_sql'] = format_time_int_to_string(start_hour_float * 3600);
            roster_item['duty_end_sql'] = format_time_int_to_string(end_hour_float * 3600);
            sync_from_roster_array_to_table(date_unix, roster_row_iterator, 'Dienstbeginn', roster_item['duty_start_sql']);
            sync_from_roster_array_to_table(date_unix, roster_row_iterator, 'Dienstende', roster_item['duty_end_sql']);
            break;
        case 'break_box':
            roster_item['break_start_int'] = start_hour_float * 3600;
            roster_item['break_start_sql'] = format_time_int_to_string(start_hour_float * 3600);
            roster_item['break_end_int'] = end_hour_float * 3600;
            roster_item['break_end_sql'] = format_time_int_to_string(end_hour_float * 3600);
            sync_from_roster_array_to_table(date_unix, roster_row_iterator, 'Mittagsbeginn', roster_item['break_start_sql']);
            sync_from_roster_array_to_table(date_unix, roster_row_iterator, 'Mittagsende', roster_item['break_end_sql']);
            break;
        default:
            console.error('Error: sync_from_bar_plot_to_roster_array_object() only works on the "break_box" and on the "work_box"!' + box_type);
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
