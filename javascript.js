"use strict";
//This function is called by grundplan-vk-in.php
function unhide_mittag() {
    var mittags_input = document.getElementsByClassName("mittags_input")
    for (var i = 0; i < mittags_input.length; i++) {
        mittags_input[i].style.display = "inline";
    }
    var mittags_ersatz = document.getElementsByClassName("mittags_ersatz")
    for (var i = 0; i < mittags_ersatz.length; i++) {
        mittags_ersatz[i].style.display = "none";
    }
    //document.getElementById("mittagspause").style.display = "inline";
    //document.getElementById("mittagspause").type = "text";
}
//This function is called by grundplan-vk-in.php
function rehide_mittag() {
    var mittags_input = document.getElementsByClassName("mittags_input")
    for (var i = 0; i < mittags_input.length; i++) {
        mittags_input[i].style.display = "none";
    }
    var mittags_ersatz = document.getElementsByClassName("mittags_ersatz")
    for (var i = 0; i < mittags_ersatz.length; i++) {
        mittags_ersatz[i].style.display = "inline";
    }
}


//This function is called by abwesenheit-in.php
function confirmDelete(link)
{
//TODO: Do we need the argument for this function?
    var r = confirm("Diesen Datensatz wirklich löschen?");
    return r;
}
//This function is called by abwesenheit-in.php
function leavePage()
{
    window.location.replace("https://www.google.de"); //Wechselt automatisch heraus aus der Eingabemaske.
}
//This function is called by abwesenheit-in.php
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
//This function is called by abwesenheit-in.php
function checkUpdateTage()
{
//Wir lesen die Objekte aus dem HTML code.
    var beginn_Id = document.getElementById("beginn");
    var ende_Id = document.getElementById("ende");
    var tage_Id = document.getElementById("tage");
    var warning_message_tr_Id = document.getElementById("warning_message_tr");
    var warning_message_td_Id = document.getElementById("warning_message_td");
    //Wir entnehmen die vorhandenen Werte.
    var beginn = new Date(beginn_Id.value);
    var ende = new Date(ende_Id.value);
    if (beginn > ende) {
        warning_message_tr_Id.style.display = "table-row";
        warning_message_td_Id.innerHTML = "Das Ende liegt vor dem Startdatum!";
        //alert('Das Ende liegt vor dem Startdatum');
    } else {
        warning_message_tr_Id.style.display = "none";
    }

}
//This function is called by tag-in.php
function unhide_kommentar() {
    var kommentar_input = document.getElementsByClassName("kommentar_input")
    for (var i = 0; i < kommentar_input.length; i++) {
        kommentar_input[i].style.display = "inline";
    }
    var kommentar_ersatz = document.getElementsByClassName("kommentar_ersatz")
    for (var i = 0; i < kommentar_ersatz.length; i++) {
        kommentar_ersatz[i].style.display = "none";
    }
}
//This function is called by tag-in.php
function rehide_kommentar() {
    var kommentar_input = document.getElementsByClassName("kommentar_input")
    for (var i = 0; i < kommentar_input.length; i++) {
        kommentar_input[i].style.display = "none";
    }
    var kommentar_ersatz = document.getElementsByClassName("kommentar_ersatz")
    for (var i = 0; i < kommentar_ersatz.length; i++) {
        kommentar_ersatz[i].style.display = "inline";
    }
}
//This function is called by navigation.php
function toggle_show_administration()
{
    var admin_div_id = document.getElementById('administration');
    if (admin_div_id.style.display == "block")
    {
        admin_div_id.style.display = "none";
    } else
    {
        admin_div_id.style.display = "block";
    }

}

//This function is used by stunden-in.php
function updatesaldo()
{
//Wir lesen die Objekte aus dem HTML code.
    var stundenInputId = document.getElementById("stunden");
    var stundenSaldoId = document.getElementById("saldoAlt");
    var stundenSaldoNeuId = document.getElementById("saldoNeu");
    //Wir entnehmen die vorhandenen Werte.
    if (stundenSaldoId != null) { //For new Coworkers there is no value set. Therefore we start with 0.
        var stundenSaldoValue = Number(stundenSaldoId.innerHTML);
    } else {
        var stundenSaldoValue = 0;
    }
    var stundenInputArray = stundenInputId.value.split(":");
    if (stundenInputArray[1]) //Wenn es einen Doppelpunkt gibt.
    {
//					document.write('Wir haben einen Doppelpunkt.');
//Die Eingabe ist eine Zeit mit Doppelpunkt. Wir rechnen in einen float (Kommazahl) um.
        var stundenInputHour = Number(stundenInputArray[0]);
        var stundenInputMinute = Number(stundenInputArray[1]);
        var stundenInputSecond = Number(stundenInputArray[2]);
        //Jetzt berechnen wir aus den Daten eine Summe. Dazu formen wir zunächst in ein gültiges Datum um.
        var stundenInputValue = 0; // Wir initialisieren den Input als Null und addieren dann Sekunden, Minuten und Stunden dazu.
        if (!isNaN(stundenInputSecond))
        {
            stundenInputValue = stundenInputValue + stundenInputSecond / 3600;
        }
        if (!isNaN(stundenInputMinute))
        {
            stundenInputValue = stundenInputValue + stundenInputMinute / 60;
        }
        if (!isNaN(stundenInputHour))
        {
            stundenInputValue = stundenInputValue + stundenInputHour;
        }
        stundenInputId.value = stundenInputValue;
    } else
    {
//Die Stunden sind eine Ganzzahl oder eine Kommazahl.
//Wir entnehmen die vorhandenen Werte.
//Wir brauchen die Kommazahl mit einem Punkt, nicht mit einem Komma.
        stundenInputId.value = stundenInputId.value.replace(/,/g, '.')
        var stundenInputValue = Number(stundenInputId.value);
    }
    var ergebnis = stundenInputValue + stundenSaldoValue;
    stundenSaldoNeuId.innerHTML = ergebnis;
}

//The following function is used by install.php
function compare_passwords() {
    var first_pass = document.getElementById('first_pass').value;
    var second_pass = document.getElementById('second_pass').value;
    if (first_pass == second_pass && first_pass != "") {
//document.getElementById('clear_pass').value = 'same';
        document.getElementById('disapprove_pass_img').style.display = 'none';
        document.getElementById('approve_pass_img').style.display = "block";
    } else if (second_pass != "") {
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
    var filename = document.getElementById("filename").value;
    var targetfilename = document.getElementById("targetfilename").value;
//    document.getElementById("xmlhttpresult").innerHTML = "<div class=warningmsg><p>working on: " + filename+"</p></div>";
    document.getElementById("xmlhttpresult").innerHTML = "<p>working on: " + filename + "</p>";
    var xml_http_request = new XMLHttpRequest();
    xml_http_request.onreadystatechange = function () {
//        if (this.readyState == 4 && this.status == 200) {
        if (this.readyState >= 3 && this.status == 200) {
            document.getElementById("xmlhttpresult").innerHTML = this.responseText;
        }
    };
    xml_http_request.open("GET", "pep.php?filename=" + targetfilename, true);
    xml_http_request.send();
}
function reset_update_pep() {
    document.getElementById("xmlhttpresult").innerHTML = "";
    document.getElementById("javascriptmessage").innerHTML = "";
    document.getElementById("phpscriptmessages").innerHTML = "";
}

function showEdit(beginn) {
    document.getElementById('save_' + beginn).style.display = 'inline';
    document.getElementById('beginn_in_' + beginn).style.display = 'inline';
    document.getElementById('beginn_in_' + beginn).className += 'datepicker'
    document.getElementById('beginn_out_' + beginn).style.display = 'none';
    document.getElementById('ende_in_' + beginn).style.display = 'inline';
    document.getElementById('ende_in_' + beginn).className += 'datepicker'
    document.getElementById('ende_out_' + beginn).style.display = 'none';
    document.getElementById('grund_in_' + beginn).style.display = 'inline';
    document.getElementById('grund_out_' + beginn).style.display = 'none';
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
    document.getElementById('beginn_in_' + beginn).style.display = 'none';
    document.getElementById('beginn_in_' + beginn).classList.remove('datepicker');
    document.getElementById('beginn_out_' + beginn).style.display = 'inline';
    document.getElementById('ende_in_' + beginn).style.display = 'none';
    document.getElementById('ende_in_' + beginn).classList.remove('datepicker');
    document.getElementById('ende_out_' + beginn).style.display = 'inline';
    document.getElementById('grund_in_' + beginn).style.display = 'none';
    document.getElementById('grund_out_' + beginn).style.display = 'inline';
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

function gettext(string_to_translate, object, callback_function) {
    var filename = get_php_script_folder() + 'gettext.php?string_to_translate=' + string_to_translate;
    var xml_http_request = new XMLHttpRequest();

    /*
     * Default values:
     */
    if (undefined === callback_function) {
        callback_function = set_value;
    }
    /*
     * Input error handling:
     */
    if (typeof object !== "object") {
        console.log("Error:" + object + " is not an object.");
        return false;
    }
    if (typeof callback_function === "function") {
        xml_http_request.callback = callback_function; // add a callback to the xml_http_request
    } else {
        console.log("Error:" + callback_function + " is not a function.");
        return false;
    }

    xml_http_request.onreadystatechange = function ()
    {
        //target found and request complete
        if (this.status === 200 && this.readyState === 4) {
            //send result to the callback function
            this.callback(object, this.responseText);
        } else if (this.readyState === 4) {
            console.log("Error while translating " + string_to_translate + " Status " + xml_http_request.status);
            this.callback(object, string_to_translate);
        }
    };
    xml_http_request.open("GET", filename, true);
    xml_http_request.send();
}
//callback function specific for gettext
function set_value(object, value)
{
    object.value = value;
}




/*
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














