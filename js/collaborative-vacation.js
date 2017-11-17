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


"use strict";
function remove_form_div_on_escape(evt) {
    //console.log("remove_form_div_on_escape");
    evt = evt || window.event;
    window.highlight_event = evt;
    if (evt.keyCode == 27) {
        remove_form_div()
    }
}
function remove_form_div() {
    //console.log("remove_form_div");
    var existing_div = document.getElementById('input_box_div');
    if (existing_div) {
        //Reset the global variables:
        delete window.highlight_absence_create_intermediate_date_unix;
        delete window.highlight_absence_create_from_date_unix;
        //Remove the formatting from the last selection:
        draw_style_highlight_absence_create();
        //Finally remove the div:
        existing_div.parentNode.removeChild(existing_div);
    }
}

function highlight_absence_create_start(evt) {
    var evt = evt || window.event;
    window.highlight_event = evt;
    var x = evt.clientX;
    var y = evt.clientY;
    //console.log(evt);
    var element_mouse_is_over = document.elementFromPoint(x, y);
    if ("input_box_div" === element_mouse_is_over.id) {
        /*
         * The form div is already/still there.
         * There is nothing to do here:
         */
        return false;
    }
    var date_unix_from_attribute = element_mouse_is_over.attributes.date_unix || element_mouse_is_over.parentNode.attributes.date_unix;
    if (!date_unix_from_attribute) {
        return false;
    }
    //console.log("highlight_absence_create_start");
    var date_unix_from = date_unix_from_attribute.nodeValue;
    var date_sql_from_attribute = element_mouse_is_over.attributes.date_sql || element_mouse_is_over.parentNode.attributes.date_sql;
    var date_sql_from = date_sql_from_attribute.nodeValue;
    window.highlight_absence_create_from_date_unix = date_unix_from;
    window.highlight_absence_create_from_date_sql = date_sql_from;
    element_mouse_is_over.classList.add("highlight");
    //element_mouse_is_over.style.background = "linear-gradient(180deg, #00ABE7 0, #0081AF 100%), #B4B4B4";
    delete window.highlight_absence_create_intermediate_date_unix;
    delete window.highlight_absence_create_to_date_unix;
    draw_style_highlight_absence_create();
}

function highlight_absence_create_intermediate(evt) {
    evt = evt || window.event;
    window.highlight_event = evt;
    if (1 == detect_left_button_press(evt)) { //Only if the left mouse button is pressed down
        //console.log("highlight_absence_create_intermediate");
        var x = evt.clientX;
        var y = evt.clientY;
        var element_mouse_is_over = document.elementFromPoint(x, y);
        if (element_mouse_is_over.attributes.date_unix) {
            var date_unix_intermediate = element_mouse_is_over.attributes.date_unix.nodeValue;
        } else if (element_mouse_is_over.parentNode.attributes.date_unix) {
            var date_unix_intermediate = element_mouse_is_over.parentNode.attributes.date_unix.nodeValue;
        }
        window.highlight_absence_create_intermediate_date_unix = date_unix_intermediate;
        draw_style_highlight_absence_create();
    }
}
function draw_style_highlight_absence_create() {
    //console.log("draw_style_highlight_absence_create");
    var list_of_day_paragraphs = document.getElementsByClassName("day_paragraph");
    for (var i = 0; i < list_of_day_paragraphs.length; i++) {
        var date_unix_current = list_of_day_paragraphs[i].attributes.date_unix.nodeValue;
        var date_range_min = Math.min(window.highlight_absence_create_intermediate_date_unix, window.highlight_absence_create_from_date_unix);
        var date_range_max = Math.max(window.highlight_absence_create_intermediate_date_unix, window.highlight_absence_create_from_date_unix);
        if (date_unix_current <= date_range_max && date_unix_current >= date_range_min) {
            list_of_day_paragraphs[i].classList.add("highlight");
        } else {
            list_of_day_paragraphs[i].classList.remove("highlight");
        }

    }

}
function highlight_absence_create_end(evt) {
    evt = evt || window.event;
    window.highlight_event = evt;
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
    //console.log("highlight_absence_create_end");
    //var date_sql_from = window.highlight_absence_create_from_date_sql;
    if (element_mouse_is_over.attributes.date_sql) {
        var date_sql_to = element_mouse_is_over.attributes.date_sql.nodeValue;
    } else if (element_mouse_is_over.parentNode.attributes.date_unix) {
        var date_sql_to = element_mouse_is_over.parentNode.attributes.date_sql.nodeValue;
    }

    window.highlight_absence_create_to_date_sql = date_sql_to;
    insert_form_div("create");
}

function insert_form_div(edit_create) {
    //console.log("insert_form_div");
    var evt = evt || window.event || window.highlight_event;
    var x = evt.clientX;
    var y = evt.clientY;
    var element_mouse_is_over = document.elementFromPoint(x, y);
    if ("create" === edit_create && "SPAN" === element_mouse_is_over.tagName) {
        //Create mode firing together with edit mode -> abort!
        return false;
    }
    var existing_div = document.getElementById('input_box_div');
    if (existing_div) {
        if ("HTML" !== element_mouse_is_over.tagName && !is_descendant(existing_div, element_mouse_is_over)) {
            existing_div.parentNode.removeChild(existing_div);
        } else {
            return false; //Do not remove and rebuild when clicking inside the form.
        }
    }
    var div = document.createElement('div');
    element_mouse_is_over.appendChild(div);
    //var rect = element_mouse_is_over.getBoundingClientRect();
    //div.style.left = rect.left;
    //div.style.top = rect.top;
    //div.style.position = 'absolute';
    if ("create" === edit_create) {
        //div.style.backgroundColor will be taken from the CSS file.
    } else {
        //div.style.backgroundColor will reflect the profession of the absent employee:
        div.style.backgroundColor = 'inherit';
    }
    div.id = 'input_box_div';
    div.className = 'input_box_div';
    div.onmousedown = stop_click_propagation(event);
    div.onmouseup = stop_click_propagation(event);
    fill_input_box_from_prototype(div);
}
function prefill_input_box_form() {
    //console.log("prefill_input_box_form");
    var input_box_div = document.getElementById('input_box_div');
    var absence_details_json = input_box_div.parentNode.attributes.absence_details;
    if (absence_details_json) {
        //Obviously only exists in edit mode:
        var absence_details = JSON.parse(absence_details_json.nodeValue);
        var employee_id_select = document.getElementById('employee_id_select');
        var employee_id_options = employee_id_select.options;
        for (var i = 0; i < employee_id_options.length; i++) {
            if (absence_details.employee_id == employee_id_options[i].value) {
                employee_id_options[i].selected = true;
            }
        }
        document.getElementById('input_box_form_start_date').value = absence_details.start;
        document.getElementById('input_box_form_end_date').value = absence_details.end;
        document.getElementById('input_box_form_reason').value = absence_details.reason;
        //In order to remove the old entry we need the former values
        document.getElementById('input_box_form_start_date_old').value = absence_details.start;
        document.getElementById('employee_id_old').value = absence_details.employee_id;
    } else if (window.highlight_absence_create_from_date_sql && window.highlight_absence_create_to_date_sql) {
        if (document.getElementById("input_box_form_button_delete")) {
            document.getElementById("input_box_form_button_delete").style.display = "none";
        }
        var employee_id_select = document.getElementById('employee_id_select');
        var employee_id_options = employee_id_select.options;
        for (var i = 0; i < employee_id_options.length; i++) {
            if (employee_id == employee_id_options[i].value) {
                employee_id_options[i].selected = true;
            }
        }
        var to_date_sql = window.highlight_absence_create_to_date_sql;
        var from_date_sql = window.highlight_absence_create_from_date_sql;
        var to_date_unix = Date.parse(to_date_sql);
        var from_date_unix = Date.parse(from_date_sql);
        if (from_date_unix > to_date_unix) {
            document.getElementById('input_box_form_start_date').value = to_date_sql;
            document.getElementById('input_box_form_end_date').value = from_date_sql;
        } else {
            document.getElementById('input_box_form_start_date').value = from_date_sql;
            document.getElementById('input_box_form_end_date').value = to_date_sql;
        }
        //gettext("Vacation", document.getElementById('input_box_form_reason'), set_value);
        gettext("Vacation", document.getElementById('input_box_form_reason'));
        //In order to remove the old entry we need the former values
        //TODO: Check if this works for
        //$query = "DELETE FROM absence WHERE `employee_id` = '$employee_id_old' AND `start` = '$start_date_old_string'";

        document.getElementById('input_box_form_start_date_old').value = "null";
        document.getElementById('employee_id_old').value = "null";

    }
    //Add a handler to BODY to catch [Esc] for closing the div.
    if (document.body.addEventListener) { // For all major browsers, except IE 8 and earlier
        document.body.addEventListener("keyup", remove_form_div_on_escape);
    } else if (x.attachEvent) { // For IE 8 and earlier versions
        document.body.attachEvent("keyup", remove_form_div_on_escape);
    }
}
function is_descendant(parent, child) {
    //console.log("is_descendant");
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
        return evt.buttons == 1;
    }
    var button = evt.which || evt.button;
    return button == 1;
}

function fill_input_box_from_prototype(div) {
    //console.log("fill_input_box_from_prototype");
    var secondary_element = document.getElementById(div.id);

    /*
     * This following part is relevant only to the edit mode. ->
     * The employee_id is transfered to the php script collaborative-vacation-input-box.php via GET
     * It is necessary for the handling of session permissions.
     */
    var input_box_div = document.getElementById('input_box_div');
    var absence_details_json = input_box_div.parentNode.attributes.absence_details;
    if (absence_details_json) {
        //Obviously only exists in edit mode:
        var absence_details = JSON.parse(absence_details_json.nodeValue);
        employee_id = absence_details.employee_id;
        var filename = get_php_script_folder() + 'pages/collaborative-vacation-input-box.php?employee_id=' + employee_id;
    } else {
        var filename = get_php_script_folder() + 'pages/collaborative-vacation-input-box.php';
    }
    /*
     * <- This previous part is relevant only to the edit mode.
     */

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            secondary_element.innerHTML = xmlhttp.responseText;
        }
    };
    xmlhttp.open("GET", filename, true);
    xmlhttp.send();
}

function get_php_script_folder() {
    //console.log("get_php_script_folder");
    var url = window.location.pathname;
    var php_script_folder;
    if (url.indexOf('\\src\\php') !== -1 || url.indexOf('/src/php') !== -1) {
        php_script_folder = './';
    } else {
        php_script_folder = './src/php/';
    }
    return php_script_folder;
}

/*
 * The div element inherits the onmousedown event from the td.
 * We do not want this behaviour.
 */
function stop_click_propagation(evt) {
    //console.log("stop_click_propagation");
    var evt = evt || window.event;
    if (evt.stopPropagation) {
        evt.stopPropagation();
    }
    if (evt.cancelBubble != null) {
        evt.cancelBubble = true;
    }
    //console.log("stopping mouse propagation")
    return false;
}
