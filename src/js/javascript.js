"use strict";
var http_server_application_path = get_http_server_application_path();
function gettext(string_to_translate) {
    var locale = document.getElementsByTagName("head")[0].lang;
    var translated_string = pdr_translations[locale][string_to_translate];
    if (translated_string) {
        console.log('"' + string_to_translate + '" found in "' + locale + '": ' + translated_string);
        return translated_string;
    } else {
        console.log('"' + string_to_translate + '" could not be translated into "' + locale + '". See existing translations below:');
        console.log(pdr_translations);
        query_webserver_without_response(http_server_application_path + "src/php/pages/maintenance_write_gettext_for_javascript.php");
        return string_to_translate;
    }
}
function get_http_server_application_path() {
    var javascript_folder_path_depth = -3;
    /*
     * This would be one way to get to the script path name:
     console.log((new Error).stack.split(':')[1].split('//')[1]);
     */
    /*
     * This is a way to get the script path name:
     */
    var scripts = document.getElementsByTagName('script');
    var script = scripts[scripts.length - 1].src;
    var http_server_application_path = script.split('/').slice(0, javascript_folder_path_depth).join('/') + '/';
    test_http_server_application_path(http_server_application_path);
    return http_server_application_path;
}
function test_http_server_application_path(http_server_application_path) {
    var xml_http_request = new XMLHttpRequest();
    xml_http_request.onreadystatechange = function () {
        if (this.status === 404) {
            console.log(http_server_application_path + "default.php not found.");
            console.log("There is a problem with get_http_server_application_path(). Please talk to a PDR developer");
            console.log(this);
            xml_http_request.onreadystatechange = "";
        }
    };
    xml_http_request.open("GET", http_server_application_path + "default.php", true);
    xml_http_request.send();
}

function query_webserver_without_response(url) {
    var xml_http_request = new XMLHttpRequest();
    xml_http_request.open("GET", url, true);
    xml_http_request.send();
}

//This function is called by grundplan-vk-in.php
function unhide_mittag() {
    var mittags_input = document.getElementsByClassName("mittags_input");
    for (var i = 0; i < mittags_input.length; i++) {
        mittags_input[i].style.display = "inline";
    }
    var mittags_ersatz = document.getElementsByClassName("mittags_ersatz");
    for (var i = 0; i < mittags_ersatz.length; i++) {
        mittags_ersatz[i].style.display = "none";
    }
//document.getElementById("mittagspause").style.display = "inline";
//document.getElementById("mittagspause").type = "text";
}
//This function is called by grundplan-vk-in.php
function rehide_mittag() {
    var mittags_input = document.getElementsByClassName("mittags_input");
    for (var i = 0; i < mittags_input.length; i++) {
        mittags_input[i].style.display = "none";
    }
    var mittags_ersatz = document.getElementsByClassName("mittags_ersatz");
    for (var i = 0; i < mittags_ersatz.length; i++) {
        mittags_ersatz[i].style.display = "inline";
    }
}


//This function is called by absence-edit.php
function confirmDelete()
{
    var r = confirm(gettext("Really delete this data set?"));
    return r;
}
//This function is called by absence-edit.php
function updateTage()
{
//Wir lesen die Objekte aus dem HTML code.
    var beginnId = document.getElementById("beginn");
    var endeId = document.getElementById("ende");
    var tageId = document.getElementById("tage");
    //Wir entnehmen die vorhandenen Werte.
    var beginn = new Date(beginnId.value);
    var ende = new Date(endeId.value);
    var start = new Date(beginn.getTime());
    var end = new Date(ende.getTime());
    var count = 0;
    while (start <= end)
    {
        if (start.getDay() != 0 && start.getDay() != 6)
        {
            count++;
        }
        start.setDate(start.getDate() + 1);
    }
    tageId.innerHTML = count;
}
//This function is called by absence-edit.php
function checkUpdateTage()
{
//Wir lesen die Objekte aus dem HTML code.
    var beginn_Id = document.getElementById("beginn");
    var ende_Id = document.getElementById("ende");
    //var tage_Id = document.getElementById("tage");
    var warning_message_tr_Id = document.getElementById("warning_message_tr");
    var warning_message_td_Id = document.getElementById("warning_message_td");
    //Wir entnehmen die vorhandenen Werte.
    var beginn = new Date(beginn_Id.value);
    var ende = new Date(ende_Id.value);
    if (beginn > ende) {
        warning_message_tr_Id.style.display = "table-row";
        warning_message_td_Id.innerHTML = gettext("The end date is before the start date.");
        //alert('Das Ende liegt vor dem Startdatum');
    } else {
        warning_message_tr_Id.style.display = "none";
    }

}
//This function is called by tag-in.php
function roster_input_row_comment_show(roster_input_row_comment_input_id, roster_input_row_comment_input_link_div_show_id, roster_input_row_comment_input_link_div_hide_id) {
    roster_input_row_comment_input_id.style.display = "inline";
    roster_input_row_comment_input_link_div_show_id.style.display = "none";
    roster_input_row_comment_input_link_div_hide_id.style.display = "inline";
}
//This function is called by tag-in.php
function roster_input_row_comment_hide(roster_input_row_comment_input_id, roster_input_row_comment_input_link_div_show_id, roster_input_row_comment_input_link_div_hide_id) {
    roster_input_row_comment_input_id.style.display = "none";
    roster_input_row_comment_input_link_div_show_id.style.display = "inline";
    roster_input_row_comment_input_link_div_hide_id.style.display = "none";
}



//The following function is used by install.php
function compare_passwords() {
    var first_pass = document.getElementById('first_pass').value;
    var second_pass = document.getElementById('second_pass').value;
    if (first_pass === second_pass && first_pass !== "") {
//document.getElementById('clear_pass').value = 'same';
        document.getElementById('disapprove_pass_img').style.display = 'none';
        document.getElementById('approve_pass_img').style.display = "block";
    } else if (second_pass !== "") {
//document.getElementById('clear_pass').value = 'different';
        document.getElementById('disapprove_pass_img').style.display = "block";
        document.getElementById('approve_pass_img').style.display = 'none';
    } else {
//document.getElementById('clear_pass').value = 'not yet';
        document.getElementById('disapprove_pass_img').style.display = 'none';
        document.getElementById('approve_pass_img').style.display = 'none';
    }

}
function update_pep() {
    if (!document.getElementById("filename")) {
        return 0;
    }
    console.log('update_pep');
    var filename = document.getElementById("filename").value;
    var targetfilename = document.getElementById("targetfilename").value;
    document.getElementById("xmlhttpresult").innerHTML = "<p>working on: " + filename + "</p>";
    var xml_http_request = new XMLHttpRequest();
    xml_http_request.onreadystatechange = function () {
        if (this.readyState >= 3 && this.status === 200) {
            //document.getElementById("xmlhttpresult").innerHTML = this.responseText;
        }
        document.getElementById("xmlhttpresult").innerHTML = this.responseText;
    };
    xml_http_request.open("GET", http_server_application_path + "pep.php?filename=" + targetfilename, true);
    console.log('opening pep.php?filename=' + targetfilename);
    xml_http_request.send();
}
function reset_update_pep() {
    console.log('reset_update_pep');
    /*
     document.getElementById("xmlhttpresult").innerHTML = "";
     document.getElementById("javascriptmessage").innerHTML = "";
     document.getElementById("phpscriptmessages").innerHTML = "";
     */
    console.log(document.getElementById("pep_upload_form"));
    document.getElementById("pep_upload_form").submit();
}

function showEdit(beginn) {
    document.getElementById('save_' + beginn).style.display = 'inline';
    document.getElementById('start_in_' + beginn).style.display = 'inline';
    document.getElementById('start_in_' + beginn).className += 'datepicker';
    document.getElementById('start_out_' + beginn).style.display = 'none';
    document.getElementById('end_in_' + beginn).style.display = 'inline';
    document.getElementById('end_in_' + beginn).className += 'datepicker';
    document.getElementById('end_out_' + beginn).style.display = 'none';
    document.getElementById('reason_in_' + beginn).style.display = 'inline';
    document.getElementById('comment_in_' + beginn).style.display = 'inline';
    document.getElementById('absence_in_' + beginn).style.display = 'inline';
    document.getElementById('reason_out_' + beginn).style.display = 'none';
    document.getElementById('comment_out_' + beginn).style.display = 'none';
    document.getElementById('absence_out_' + beginn).style.display = 'none';
    document.getElementById('edit_' + beginn).style.display = 'none';
    document.getElementById('delete_' + beginn).style.display = 'none';
    document.getElementById('cancel_' + beginn).style.display = 'inline';
    //Hide the submit button for new data:
    //This is not to confuse people when choosing the right button for submission of the data.
    document.getElementById('save_new').style.display = 'none';
    document.getElementById('input_line_new').style.display = 'none';
    datePickerInit();
    var list = document.getElementsByClassName('edit_button');
    var i;
    for (i = 0; i < list.length; i++) {
        list[i].style.display = 'none';
    }
    var list = document.getElementsByClassName('delete_button');
    var i;
    for (i = 0; i < list.length; i++) {
        list[i].style.display = 'none';
    }
}

function cancelEdit(beginn) {
    document.getElementById('save_' + beginn).style.display = 'none';
    document.getElementById('start_in_' + beginn).style.display = 'none';
    document.getElementById('start_in_' + beginn).classList.remove('datepicker');
    document.getElementById('start_out_' + beginn).style.display = 'inline';
    document.getElementById('end_in_' + beginn).style.display = 'none';
    document.getElementById('end_in_' + beginn).classList.remove('datepicker');
    document.getElementById('end_out_' + beginn).style.display = 'inline';
    document.getElementById('reason_in_' + beginn).style.display = 'none';
    document.getElementById('comment_in_' + beginn).style.display = 'none';
    document.getElementById('absence_in_' + beginn).style.display = 'none';
    document.getElementById('reason_out_' + beginn).style.display = 'inline';
    document.getElementById('comment_out_' + beginn).style.display = 'inline';
    document.getElementById('absence_out_' + beginn).style.display = 'inline';
    document.getElementById('edit_' + beginn).style.display = 'inline';
    document.getElementById('delete_' + beginn).style.display = 'inline';
    document.getElementById('cancel_' + beginn).style.display = 'none';
    //
    document.getElementById('save_new').style.display = 'inline';
    document.getElementById('input_line_new').style.display = 'table-row';
    var list = document.getElementsByClassName('edit_button');
    var i;
    for (i = 0; i < list.length; i++) {
        list[i].style.display = 'inline';
    }

    var list = document.getElementsByClassName('delete_button');
    var i;
    for (i = 0; i < list.length; i++) {
        list[i].style.display = 'inline';
    }


    var list = document.getElementsByClassName('datepickershow');
    console.log(list);
    var i;
    for (i = 0; i < list.length; i++) {
        console.log(list[i].parentElement);
        list[i].parentElement.removeChild(list[i]);
    }
    var list = document.getElementsByClassName('datepickershow');
    list[0].parentElement.removeChild(list[0]); //For some reason one datepicker survives the first deletion.
    return false;
}

/**
 * Clear all data from a html FORM element
 * This function is used by branch-management.php
 */
function clear_form(form_id) {
    console.log(form_id);
    var elements = form_id.elements;
    form_id.reset();
    for (i = 0; i < elements.length; i++) {

        var field_type = elements[i].type.toLowerCase();
        switch (field_type) {

            case "text":
            case "password":
            case "textarea":
            case "hidden":

                elements[i].defaultValue = "";
                break;
            case "radio":
            case "checkbox":
                if (elements[i].checked) {
                    elements[i].checked = false;
                }
                break;
            case "select-one":
            case "select-multi":
                elements[i].selectedIndex = -1;
                break;
            default:
                break;
        }
    }
}
