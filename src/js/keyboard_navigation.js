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
document.onkeydown = function (evt) {
    keyboard_navigation_main(evt);
};

function keyboard_navigation_main(evt) {
    evt = evt || window.event;

    if (evt.keyCode === 27) {
        /*
         * The escape key is pressed.
         */
        /*
         * Hide the contact form if present:
         */
        hide_contact_form();
        /*
         * Remove the form in collaborative-vacation
         */
        remove_form_div();

    }

    if (evt.ctrlKey && evt.keyCode == 37 && !evt.shiftKey) {
        /*
         * The control key and the left arrow key are pressed.
         */
        keyboard_navigation_move_backward();
        return false;
    }
    if (evt.ctrlKey && evt.keyCode == 39 && !evt.shiftKey) {
        /*
         * The control key and the right arrow key are pressed.
         */
        keyboard_navigation_move_forward();
        return false;
    }
    if (evt.ctrlKey && evt.keyCode == 83 && !evt.shiftKey) {
        /*
         * The control key and the s key are pressed.
         */
        keyboard_navigation_submit_roster();
        if (evt.preventDefault)
        {
            evt.preventDefault();
            evt.stopPropagation();
        }
        return false;
    }
    //console.log(evt);
    return false;
}

function keyboard_navigation_move_backward() {
    var button = document.getElementById('button_day_backward');
    if (button) {
        button.click();
        return false;
    }
    var button = document.getElementById('button_week_backward');
    if (button) {
        button.click();
        return false;
    }
}
function keyboard_navigation_move_forward() {
    var button = document.getElementById('button_day_forward');
    if (button) {
        button.click();
        return false;
    }
    var button = document.getElementById('button_week_forward');
    if (button) {
        button.click();
        return false;
    }
}

function keyboard_navigation_submit_roster() {
    var button = document.getElementById('submit_button');
    if (button) {
        button.click();
        return false;
    }
}
