/*
 * Copyright (C) 2017 Mandelkow
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


"use strict";
function remove_form_div_on_escape(evt) {
    var input_box_data_div = document.getElementById('input_box_data_div');
    evt = evt || window.event;
    input_box_data_div.highlight_event = evt;
    if (evt.keyCode === 27) {
        remove_form_div();
    }
}
function remove_form_div() {
    var input_box_data_div = document.getElementById('input_box_data_div');
    var existing_div = document.getElementById('input_box_div');
    if (existing_div) {
        //Reset the global variables:
        delete input_box_data_div.dataset.highlight_absence_create_intermediate_date_unix;
        delete input_box_data_div.dataset.highlight_absence_create_from_date_unix;
        //Remove the formatting from the last selection:
        draw_style_highlight_absence_create();
        //Finally remove the div:
        existing_div.parentNode.removeChild(existing_div);
    }
}

function highlight_absence_create_start(evt) {
    var input_box_data_div = document.getElementById('input_box_data_div');
    var evt = evt || window.event;
    input_box_data_div.highlight_event = evt;
    var x = evt.clientX;
    var y = evt.clientY;
    var element_mouse_is_over = document.elementFromPoint(x, y);
    if ("input_box_div" === element_mouse_is_over.id) {
        /*
         * The form div is already/still there.
         * There is nothing to do here:
         */
        return false;
    }
    var date_unix_from = element_mouse_is_over.dataset.date_unix || element_mouse_is_over.parentNode.dataset.date_unix;
    if (!date_unix_from) {
        /*
         * date_unix_from is missing
         */
        return false;
    }
    var date_sql_from = element_mouse_is_over.dataset.date_sql || element_mouse_is_over.parentNode.dataset.date_sql;
    input_box_data_div.dataset.highlight_absence_create_from_date_unix = date_unix_from;
    input_box_data_div.dataset.highlight_absence_create_intermediate_date_unix = date_unix_from;
    /*
     * We start with input_box_data_div.dataset.date_range_min/max = date_unix_from;
     * These values will be adapted if additional days are selected.
     */
    input_box_data_div.dataset.date_range_min = date_unix_from;
    input_box_data_div.dataset.date_range_max = date_unix_from;

    input_box_data_div.dataset.highlight_absence_create_from_date_sql = date_sql_from;
    element_mouse_is_over.classList.add("highlight");
    delete input_box_data_div.dataset.highlight_absence_create_to_date_unix;
    draw_style_highlight_absence_create();
}

function highlight_absence_create_intermediate(evt) {
    var input_box_data_div = document.getElementById('input_box_data_div');

    evt = evt || window.event;
    input_box_data_div.highlight_event = evt;
    if (detect_left_button_press(evt)) { //Only if the left mouse button is pressed down
        var x = evt.clientX;
        var y = evt.clientY;
        var element_mouse_is_over = document.elementFromPoint(x, y);
        if (element_mouse_is_over.dataset.date_unix) {
            input_box_data_div.dataset.highlight_absence_create_intermediate_date_unix = element_mouse_is_over.dataset.date_unix;
        } else if (element_mouse_is_over.parentNode.dataset.date_unix) {
            input_box_data_div.dataset.highlight_absence_create_intermediate_date_unix = element_mouse_is_over.parentNode.dataset.date_unix;
        }
        draw_style_highlight_absence_create();
    }
}
function draw_style_highlight_absence_create() {
    var input_box_data_div = document.getElementById('input_box_data_div');
    var list_of_day_paragraphs = document.getElementsByClassName("day_paragraph");
    for (var i = 0; i < list_of_day_paragraphs.length; i++) {
        var date_unix_current = list_of_day_paragraphs[i].dataset.date_unix;
        input_box_data_div.dataset.date_range_min = Math.min(input_box_data_div.dataset.highlight_absence_create_intermediate_date_unix, input_box_data_div.dataset.highlight_absence_create_from_date_unix);
        input_box_data_div.dataset.date_range_max = Math.max(input_box_data_div.dataset.highlight_absence_create_intermediate_date_unix, input_box_data_div.dataset.highlight_absence_create_from_date_unix);
        if (date_unix_current <= input_box_data_div.dataset.date_range_max && date_unix_current >= input_box_data_div.dataset.date_range_min) {
            list_of_day_paragraphs[i].classList.add("highlight");
        } else {
            list_of_day_paragraphs[i].classList.remove("highlight");
        }

    }

}
function highlight_absence_create_end(evt) {
    var input_box_data_div = document.getElementById('input_box_data_div');
    evt = evt || window.event;
    input_box_data_div.highlight_event = evt;
    var x = evt.clientX;
    var y = evt.clientY;
    var element_mouse_is_over = document.elementFromPoint(x, y);
    if ("input_box_div" === element_mouse_is_over.id) {
        /*
         * The form div is already/still there.
         * There is nothing to do here:
         */
        return false;
    }
    if (element_mouse_is_over.dataset.date_sql) {
        var date_sql_to = element_mouse_is_over.dataset.date_sql;
    } else if (element_mouse_is_over.parentNode.dataset.date_unix) {
        var date_sql_to = element_mouse_is_over.parentNode.dataset.date_sql;
    }

    input_box_data_div.dataset.highlight_absence_create_to_date_sql = date_sql_to;
    insert_form_div("create");
}

function insert_form_div(edit_create) {
    var input_box_data_div = document.getElementById('input_box_data_div');
    var evt = evt || window.event || input_box_data_div.highlight_event;
    var x = evt.clientX;
    var y = evt.clientY;
    var element_mouse_is_over = document.elementFromPoint(x, y);
    if ("create" === edit_create && "SPAN" === element_mouse_is_over.tagName) {
        //Create mode firing together with edit mode -> abort!
        return false;
    }
    var existing_div = document.getElementById('input_box_div');
    if (existing_div) {
        return false; //Do not remove and rebuild when clicking inside the form.
    }
    var div = document.createElement('div');
    document.body.appendChild(div);
    div.id = 'input_box_div';
    div.className = 'input_box_div';
    fill_input_box_from_prototype(element_mouse_is_over);

    //Add a handler to BODY to catch [Esc] for closing the div.
    if (document.body.addEventListener) { // For all major browsers, except IE 8 and earlier
        document.body.addEventListener("keyup", remove_form_div_on_escape);
    } else if (x.attachEvent) { // For IE 8 and earlier versions
        document.body.attachEvent("keyup", remove_form_div_on_escape);
    }

}

function is_descendant(parent, child) {
    var node = child.parentNode;
    while (node !== null) {
        if (node === parent) {
            return true;
        }
        node = node.parentNode;
    }
    return false;
}
function detect_left_button_press(evt) {
    evt = evt || window.event;
    if ("buttons" in evt) {
        return evt.buttons === 1;
    }
    var button = evt.which || evt.button;
    return button === 1;
}

function fill_input_box_from_prototype(element_mouse_is_over) {
    var input_box_data_div = document.getElementById('input_box_data_div');
    /*
     * The employee_id is transfered to the php script collaborative-vacation-input-box.php via GET
     * It is necessary for the handling of session permissions.
     */
    var input_box_div = document.getElementById('input_box_div');
    var absence_details_json = element_mouse_is_over.dataset.absence_details;
    if (absence_details_json) {
        //edit mode:
        var filename = http_server_application_path + 'src/php/pages/collaborative-vacation-input-box.php?absence_details_json=' + absence_details_json;
    } else {
        //create mode:
        var highlight_details_json = JSON.stringify(input_box_data_div.dataset);
        var filename = http_server_application_path + 'src/php/pages/collaborative-vacation-input-box.php?highlight_details_json=' + highlight_details_json;
    }
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            input_box_div.innerHTML = xmlhttp.responseText;
        }
    };
    xmlhttp.open("GET", filename, true);
    xmlhttp.send();
}


/*
 * The div element inherits the onmousedown event from the td.
 * We do not want this behaviour.
 */
function stop_click_propagation(evt) {
    var evt = evt || window.event;
    if (evt.stopPropagation) {
        evt.stopPropagation();
    }
    if (evt.cancelBubble !== null) {
        evt.cancelBubble = true;
    }
    return false;
}
