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


/**
 * Displays the element "contact_form".
 *
 * @returns void
 */
function unhide_contact_form() {
    document.getElementById("user_dialog_contact_form_div").style.display = "inline";
}
function hide_contact_form() {
    document.getElementById("user_dialog_contact_form_div").style.display = "none";
}

function writeErrorToUserDialogContainer(errorString, errorId = null) {
    if (document.getElementById(errorId)) {
        /**
         * This exact error allready exists. We will not create it again.
         */
        return null;
    }
    /**
     * Create paragraph:
     */
    var p = document.createElement("p");
    p.innerText = errorString;
    /**
     * Create div to contain the paragraph:
     */
    var div = document.createElement("div");
    div.appendChild(p);
    div.classList.add("error");
    div.id = errorId;
    /**
     *
     * Add div to the user_dialog_container:
     */
    var listOfUserDialogContainers = document.getElementsByClassName("user_dialog_container");
    var userDialogContainer = listOfUserDialogContainers[0];
    userDialogContainer.appendChild(div);
}

function removeErrorFromUserDialogContainer(errorId) {
    var errorElement = document.getElementById(errorId);
    if (null === errorElement) {
        /**
         * This error div does not exist. There is nothing to be removed.
         */
        return false;
    }
    /**
     * <p lang=en>
     * This function does not delete every element by id.
     * Only direct children of user_dialog_container are removed.
     * </p>
     */
    var listOfUserDialogContainers = document.getElementsByClassName("user_dialog_container");
    var userDialogContainer = listOfUserDialogContainers[0];
    userDialogContainer.removeChild(errorElement);
}
