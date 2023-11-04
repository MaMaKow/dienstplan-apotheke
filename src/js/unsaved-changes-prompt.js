/*
 * Copyright (C) 2023 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

// Variable to track if changes have been made.
let changesMadeInForm = false;

// Function to enable the unsaved changes prompt.
function enableUnsavedChangesPrompt(formElement) {
    // Add an event listener to the window to display the confirmation message.
    window.addEventListener('beforeunload', function (e) {
        if (changesMadeInForm) {
            e.returnValue = "You have unsaved changes. Are you sure you want to leave?";
        }
    });

    // Add an event listener for form submission to reset the changesMade variable.
    var form = formElement;
    if (form) {
        form.addEventListener('submit', function () {
            console.log("I saw some submit");
            changesMadeInForm = false;
        });
    }
}
