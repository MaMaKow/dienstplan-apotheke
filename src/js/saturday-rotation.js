/*
 * Copyright (C) 2022 Mandelkow
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


function saturdayRotationTeamsAddTeam(clickedElement) {
    var rowElement = clickedElement.parentNode;
    var tableElement = rowElement.parentNode.parentNode;
    var team_id = Number.parseInt(tableElement.dataset.max_team_id) + 1;
    var branch_id = Number.parseInt(rowElement.dataset.branch_id);
    tableElement.dataset.max_team_id = team_id;
    var xml_http_request = new XMLHttpRequest();
    xml_http_request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            var rosterInputRowHtml = this.responseText;
            const newRowElement = document.createElement('tr');
            rowElement.parentNode.insertBefore(newRowElement, rowElement);
            newRowElement.outerHTML = rosterInputRowHtml;
        }
    };
    var url = http_server_application_path + "src/php/fragments/fragment.saturdayRotationTeamsAddTeam.php?"
            + "team_id=" + String(team_id)
            + "&" + "branch_id=" + String(branch_id);
    //TODO: Wir sollten auch das Datum korrekt übertragen!
    //+ "&" + "saturday_date_string=" + String(saturday_date);

    xml_http_request.open("GET", url, true);
    xml_http_request.send();
}

function saturdayRotationTeamsRemoveTeam(team_id, branch_id) {
    if (!confirmDelete()) {
        return false;
    }
    var filename = http_server_application_path + 'src/php/fragments/ajax.php?saturdayRotationTeamsRemoveTeamId=' + team_id + '&saturdayRotationTeamsRemoveBranchId=' + branch_id;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            window.location.reload();
        }
    };
    xmlhttp.open("GET", filename, true);
    xmlhttp.send();
}

function saturdayRotationTeamsAddEmployee(clickedElement) {
    var rowElement = clickedElement.parentNode.parentNode.parentNode.parentNode; //<tr><td><form><span><a>
    var team_id = Number.parseInt(rowElement.dataset.team_id);
    var branch_id = Number.parseInt(rowElement.dataset.branch_id);

    var xml_http_request = new XMLHttpRequest();
    xml_http_request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            var rosterInputRowEmployeeSelect = this.responseText;
            const newSpanElement = document.createElement('span');
            newSpanElement.innerHTML = rosterInputRowEmployeeSelect;
            clickedElement.parentNode.parentNode.insertBefore(newSpanElement, clickedElement.parentNode);
        }
    };
    var url = http_server_application_path + "src/php/fragments/fragment.saturdayRotationTeamsAddEmployee.php?"
            + "team_id=" + String(team_id)
            + "&" + "branch_id=" + String(branch_id);

    xml_http_request.open("GET", url, true);
    xml_http_request.send();
}
