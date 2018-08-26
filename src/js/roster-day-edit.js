/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
    var xml_http_request = new XMLHttpRequest();
    var target_id = id;
    var day_iterator = target_id.dataset.day_iterator;
    var roster_row_iterator = target_id.dataset.roster_row_iterator;
    var maximum_number_of_rows = target_id.dataset.maximum_number_of_rows;
    var branch_id = target_id.dataset.branch_id;
    xml_http_request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            target_id.dataset.roster_row_iterator = Number(target_id.dataset.roster_row_iterator) + 1;
            target_id.dataset.maximum_number_of_rows = Number(target_id.dataset.maximum_number_of_rows) + 1;
            var new_row = document.createElement('tr');
            new_row.innerHTML = this.responseText;
            target_id.parentNode.insertBefore(new_row, target_id);
        }
    };
    var url = http_server_application_path + "src/php/fragments/fragment.add_roster_input_row.php?"
            + "day_iterator=" + day_iterator
            + "&" + "roster_row_iterator=" + roster_row_iterator
            + "&" + "maximum_number_of_rows=" + maximum_number_of_rows
            + "&" + "branch_id=" + branch_id;
    //console.log(url);
    xml_http_request.open("GET", url, true);
    xml_http_request.send();

}
