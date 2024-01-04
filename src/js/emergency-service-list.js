/*
 * Copyright (C) 2021 Martin Mandelkow <mandelkow@apotheke-schwerin.de>
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
 *
 * @param {type} element
 * @returns {undefined}
 */
function unhideButtonOnChange(element) {
    //console.log(element);
    //console.log(element.parentNode.parentNode);
    rowIndex = element.parentNode.parentNode.dataset.iterator;
    buttonSaveElement = document.getElementById("save_" + rowIndex);
    buttonDeleteElement = document.getElementById("delete_" + rowIndex);
    if (element.value !== element.defaultValue) {
        preventLeavingPage();
        buttonSaveElement.style.display = "inline";
        buttonDeleteElement.style.display = "none";
    } else {
        enableLeavingPage();
        buttonSaveElement.style.display = "none";
        buttonDeleteElement.style.display = "inline";
    }
}
function updateCommandAndSubmit(selectElement, rowNumber) {
    var form = selectElement.form;
    var commandValue = 'replace';

    // Using getElementById to directly access the element by ID
    var commandInput = document.getElementById('command_' + rowNumber);

    if (commandInput) {
        commandInput.value = commandValue;
        form.submit();
    }
}
