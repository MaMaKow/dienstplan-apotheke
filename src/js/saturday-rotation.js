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
    tableElement.dataset.max_team_id = team_id;
    //TODO: Wir müssen das Element direkt per xhtmlrequest holen!

    var rosterInputRowEmployeeSelect = "<select "
            + " name=Saturday_rotation_team[" + team_id + "][][employee_id] "
            + " data-team_id='' "
            + " onChange='this.form.submit();' "
            + ">"
            + "<option value=''>&nbsp;</option>";
    var selectOptions = "";
    for (var employeeId in workforce.List_of_employees) {
        employeeObject = workforce.List_of_employees[employeeId];
        selectOptions += "<option value=" + employeeObject.employee_id + ">";
        selectOptions += employeeObject.employee_id + " " + employeeObject.last_name;
        selectOptions += "</option>";
    }
    rosterInputRowEmployeeSelect += selectOptions;
    rosterInputRowEmployeeSelect += "</select>\n";
    var rosterInputRowForm = '<form method="POST"><span>' + rosterInputRowEmployeeSelect + '</span></form>';
    spanHtml = '<td>&nbsp;</td><td>' + team_id + '</td><td>' + rosterInputRowForm + '</td>';
    const newRowElement = document.createElement('tr');
    newRowElement.innerHTML = spanHtml;

    rowElement.parentNode.insertBefore(newRowElement, rowElement);

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
