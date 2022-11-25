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


function roster_input_row_add(id) {
    /*
     * TODO: The new element does not yet work properly with the plot.
     *     Try to sync the information upon adding here to the plot.
     */
    var xml_http_request = new XMLHttpRequest();
    var buttonAddRowElement = id;
    var day_iterator = buttonAddRowElement.dataset.day_iterator;
    var roster_row_iterator = Number(buttonAddRowElement.dataset.roster_row_iterator);
    var maximum_number_of_rows = Number(buttonAddRowElement.dataset.maximum_number_of_rows);
    var branch_id = Number(buttonAddRowElement.dataset.branch_id);
    xml_http_request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            buttonAddRowElement.dataset.roster_row_iterator = Number(buttonAddRowElement.dataset.roster_row_iterator) + 1;
            buttonAddRowElement.dataset.maximum_number_of_rows = Number(buttonAddRowElement.dataset.maximum_number_of_rows) + 1;
            var newRow = document.createElement('tr');
            newRow.innerHTML = this.responseText;
            newRow.class += "insertedRow";
            newRow.dataset.roster_row_iterator = Number(roster_row_iterator) + 1;
            var buttonColumnInTableElement = buttonAddRowElement.parentNode;
            var buttonRowInTableElement = buttonColumnInTableElement.parentNode;
            var buttonTableElement = buttonRowInTableElement.parentNode;
            buttonTableElement.insertBefore(newRow, buttonRowInTableElement);
        }
    };
    var url = http_server_application_path + "src/php/fragments/fragment.add_roster_input_row.php?"
            + "day_iterator=" + String(day_iterator)
            + "&" + "roster_row_iterator=" + String(roster_row_iterator)
            + "&" + "maximum_number_of_rows=" + String(maximum_number_of_rows)
            + "&" + "branch_id=" + String(branch_id);

    xml_http_request.open("GET", url, true);
    xml_http_request.send();
}
