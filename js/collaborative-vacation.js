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
function insert_form_div(element_mouse_is_over) {
    var x = event.clientX;
    var y = event.clientY;
    var element_mouse_is_over = document.elementFromPoint(x, y);
    var existing_div = document.getElementById('input_box_div');
    if (existing_div) {
        if (!is_descendant(existing_div, element_mouse_is_over) && !is_descendant(element_mouse_is_over, existing_div)) {
            //console.log('Destroying ' + existing_div);
            //console.log(element_mouse_is_over + ' is not a child of ' + existing_div);
            existing_div.parentNode.removeChild(existing_div);
        } else {
            return false; //Do not remove and rebuild when clicking inside the form.
        }
    }
    //var prototype = document.getElementById('input_box_prototype');
    var div = document.createElement('div');
    element_mouse_is_over.appendChild(div);
    var rect = element_mouse_is_over.getBoundingClientRect();
    div.style.left = rect.left;
    div.style.top = rect.top;
    div.style.position = 'absolute';
    div.style.backgroundColor = 'inherit';
    div.id = 'input_box_div';
    div.className = 'input_box_div'
    fill_input_box_from_prototype(div);
}
function prefill_input_box_form() {
    var input_box_div = document.getElementById('input_box_div');
    var absence_details = JSON.parse(input_box_div.parentNode.attributes.absence_details.nodeValue);
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

function fill_input_box_from_prototype(div) {
    var secondary_element = document.getElementById(div.id);
    var filename = 'src/php/collaborative-vacation-input-box.php';
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            secondary_element.innerHTML = xmlhttp.responseText;
        }
    };
    xmlhttp.open("GET", filename, true);
    xmlhttp.send();
}

