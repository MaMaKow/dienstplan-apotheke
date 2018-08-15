/*
 * This part of the software was licensed under the MIT license by Chris Hulbert:
 * https://github.com/chrishulbert/datepicker
 * Copyright (c) 2015 Chris Hulbert
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/*
 * The locale of the browser is used to localize the date we are asking different language definitions as fallback mechanism.
 * The language is read from HTTP_ACCEPT_LANGUAGE by default.php and stored inside the HEAD by head.php
 */
var navigator_language = document.getElementsByTagName("HTML")[0].lang;
if (!navigator_language) {
    navigator_language = navigator.language || navigator.userLanguage || navigator.browserLanguage || navigator.systemLanguage;
}

/*
 * Adds leading zeros to numbers.
 *
 * @param float number
 * @param int size Number of characters in final string.
 * @returns {pad.number_string|String}
 */
function pad(number, size) {
    var number_string = number + "";
    while (number_string.length < size)
        number_string = "0" + number_string;
    return number_string;
}
/*
 * Converts a date into 'YYYY-MM-DD' format
 *
 * @param object date
 * @returns {String}
 */
function getDateString(date) {
    return date.getFullYear() + '-' + pad((date.getMonth() + 1), 2) + '-' + pad(date.getDate(), 2);
}

/*
 * Converts a date into 'July 2010' format
 *
 * @param object date
 * @returns {String}
 */
function getMonthYearString(date) {
    navigator_language_hyphen = navigator_language.replace('_', '-');
    month_string = date.toLocaleString(navigator_language_hyphen, {month: 'long'}); //"numeric", "2-digit", "narrow", "short" and "long"
    return month_string + ' ' + date.getFullYear();
    //console.log('getMonthYearString with date: ' + date);
    /*
     return ['January','February','March','April','May','June','July',
     'August','September','October','November','December'][date.getMonth()] +
     ' ' + date.getFullYear();
     */
}

// This is the function called when the user clicks any button
function chooseDate(e) {
    //console.log('chooseDate with e: ' + e);
    var targ; // Crossbrowser way to find the target (http://www.quirksmode.org/js/events_properties.html)
    if (!e)
        var e = window.event;
    if (e.target)
        targ = e.target;
    else if (e.srcElement)
        targ = e.srcElement;
    if (targ.nodeType == 3)
        targ = targ.parentNode; // defeat Safari bug

    var div = targ.parentNode.parentNode.parentNode.parentNode.parentNode; // Find the div
    var idOfTextbox = div.getAttribute('datepickertextbox'); // Get the textbox id which was saved in the div
    var textbox = document.getElementById(idOfTextbox); // Find the textbox now
    if (targ.value == '<' || targ.value == '>') { // Do they want the change the month?
        createCalendar(div, new Date(targ.getAttribute('date')));
        return;
    }
    textbox.value = targ.getAttribute('date'); // Set the selected date
    div.parentNode.removeChild(div); // Remove the dropdown box now
}

// Parse a date
function parseMyDate(d) {
    if (d == "")
        return new Date('NotADate'); // For Safari
    return new Date(d); //Works for dates formated in RFC 822 or ISO 8601 format
}

// This creates the calendar for a given month
function createCalendar(div, month) {
    //console.log('createCalendar with div: ' + div + ' and month: ' + month);
    var idOfTextbox = div.getAttribute('datepickertextbox'); // Get the textbox id which was saved in the div
    var textbox = document.getElementById(idOfTextbox); // Find the textbox now
    var tbl = document.createElement('table');
    var topRow = tbl.insertRow(-1);
    var td = topRow.insertCell(-1);
    var lastMonthBn = document.createElement('input');
    lastMonthBn.type = 'button'; // Have to immediately set the type due to IE
    td.appendChild(lastMonthBn);
    lastMonthBn.value = '<';
    lastMonthBn.onclick = chooseDate;
    lastMonthBn.setAttribute('date', new Date(month.getFullYear(), month.getMonth() - 1, 1, 0, 0, 0, 0).toString());
    var td = topRow.insertCell(-1);
    td.colSpan = 5;
    var mon = document.createElement('input');
    mon.type = 'text';
    td.appendChild(mon);
    mon.value = getMonthYearString(month);
    mon.size = 15;
    mon.disabled = 'disabled';
    var td = topRow.insertCell(-1);
    var nextMonthBn = document.createElement('input');
    nextMonthBn.type = 'button';
    td.appendChild(nextMonthBn);
    nextMonthBn.value = '>';
    nextMonthBn.onclick = chooseDate;
    nextMonthBn.setAttribute('date', new Date(month.getFullYear(), month.getMonth() + 1, 1, 0, 0, 0, 0).toString());
    var daysRow = tbl.insertRow(-1);
    var today = new Date();
    for (i = 0; i < 7; i++) {
        monday = new Date();
        monday.setDate(today.getDate() - today.getDay() + 1);
        tag = new Date();
        tag.setDate(monday.getDate() + i);

        weekday_string = tag.toLocaleString(navigator_language, {weekday: 'short'}); //narrow, short, long
        daysRow.insertCell(-1).innerHTML = weekday_string;
    }

    // Make the calendar
    var selected = parseMyDate(textbox.value); // Try parsing the date
    var today = new Date();
    date = new Date(month.getFullYear(), month.getMonth(), 1, 0, 0, 0, 0); // Starting at the 1st of the month
    var extras = (date.getDay() + 6) % 7; // How many days of the last month do we need to include?
    date.setDate(date.getDate() - extras); // Skip back to the previous monday
    while (1) { // Loop for each week
        var tr = tbl.insertRow(-1);
        for (i = 0; i < 7; i++) { // Loop each day of this week
            var td = tr.insertCell(-1);
            var inp = document.createElement('input');
            inp.type = 'button';
            td.appendChild(inp);
            inp.value = date.getDate();
            inp.onclick = chooseDate;
            inp.setAttribute('date', getDateString(date));
            if (date.getMonth() != month.getMonth()) {
                if (inp.className)
                    inp.className += ' ';
                inp.className += 'othermonth';
            }
            if (date.getDate() == today.getDate() && date.getMonth() == today.getMonth() && date.getFullYear() == today.getFullYear()) {
                if (inp.className)
                    inp.className += ' ';
                inp.className += 'today';
            }
            if (!isNaN(selected) && date.getDate() == selected.getDate() && date.getMonth() == selected.getMonth() && date.getFullYear() == selected.getFullYear()) {
                if (inp.className)
                    inp.className += ' ';
                inp.className += 'selected';
            }
            date.setDate(date.getDate() + 1); // Increment a day
        }
        // We are done if we've moved on to the next month
        if (date.getMonth() != month.getMonth()) {
            break;
        }
    }

    // At the end, we do a quick insert of the newly made table, hopefully to remove any chance of screen flicker
    if (div.hasChildNodes()) { // For flicking between months
        div.replaceChild(tbl, div.childNodes[0]);
    } else { // For creating the calendar when they first click the icon
        div.appendChild(tbl);
    }
}

// This is called when they click the icon next to the date inputbox
function showDatePicker(idOfTextbox) {
    //console.log('showDatePicker with idOfTextbox: ' + idOfTextbox);
    var textbox = document.getElementById(idOfTextbox);

    // See if the date picker is already there, if so, remove it and do not create a new datepicker.
    x = textbox.parentNode.getElementsByTagName('div');
    for (i = 0; i < x.length; i++) {
        if (x[i].getAttribute('class') === 'datepickerdropdown') {
            textbox.parentNode.removeChild(x[i]);
            return false; //Iteration will stop after the first (and only) div that is a 'datepickerdropdown'.
        }
    }

    // Grab the date, or use the current date if not valid
    var date = parseMyDate(textbox.value);
    if (isNaN(date))
        date = new Date();

    // Create the box
    var div = document.createElement('div');
    div.className = 'datepickerdropdown';
    div.setAttribute('datepickertextbox', idOfTextbox); // Remember the textbox id in the div
    createCalendar(div, date); // Create the calendar
    insertAfter(div, textbox); // Add the box to screen just after the textbox
    return false;
}

// Adds an item after an existing one
function insertAfter(newItem, existingItem) {
    //console.log('insertAfter with newItem: ' + newItem + ' and existingItem: ' + existingItem);
    if (existingItem.nextSibling) { // Find the next sibling, and add newItem before it
        existingItem.parentNode.insertBefore(newItem, existingItem.nextSibling);
    } else { // In case the existingItem has no sibling after itself, append it
        existingItem.parentNode.appendChild(newItem);
    }
}
//http://stackoverflow.com/questions/10193294/how-can-i-tell-if-a-browser-supports-input-type-date
function browserSupportsOwnDateInput() {
    var input = document.createElement('input');
    input.setAttribute('type', 'date');

    var notADateValue = 'not-a-date';
    input.setAttribute('value', notADateValue);

    return (input.value !== notADateValue);
}

// This is called when the page loads, it searches for inputs where the class is 'datepicker'
function datePickerInit() {
    if (browserSupportsOwnDateInput()) {
        return false;
    } //We do not need a datepicker. This browser can handle the task on its own.
    // Search for elements by class
    var allElements = document.getElementsByTagName("INPUT");
    for (i = 0; i < allElements.length; i++) {
        var className = allElements[i].className;
        if (!className) {
            continue;
        }
        if (className === 'datepicker' || className.indexOf('datepicker ') !== -1 || className.indexOf(' datepicker') !== -1) {
            // Found one! Now lets add a datepicker next to it
            var a = document.createElement('a');
            a.href = '#';
            a.className = "datepickershow"
            a.setAttribute('onclick', 'return showDatePicker("' + allElements[i].id + '")');
            var img = document.createElement('img');
            img.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAABGdBTUEAAK/INwWK6QAAABh0RVh0U29mdHdhcmUAUGFpbnQuTkVUIHYzLjM2qefiJQAAAdtJREFUOE+Vj+9PUnEUxvPvar3xja96Q1hGEKG0ubZqbfHCNqIVA4eYLAwFp0LYD4iIJEdeRGGZwDAEcUOn9oNIvPcGgjBQfHE69/YFihe1zs59du7d83nOuR0AcOq/CgEqWbaHDqaD+clF1rLAmija6MsZ5vb0s9nB1xm168s9x67y6Y7q2TaXjo8tVKjUTv7Zt61pAkwt/UA3zFwFuxysV2BKAuYeMAnBcBaGukDdCaozaLg5sUGAiQDLA3IIDIBfAfO34N118PaDRwYvRfBcCMrTaLg2liTAOEW3NjzpBZsMpqUwKQaLCMYvwGMhjArQIDfGCTDqy3EAX47lfVTnCo3qCnOzJ8IpW6pJR2IEGHn7/bBaR5MLO8y8CtPuKO2J0nMfGdKr+5uZ4kVdhAD6N99K1bo7ynB5vHpj3AZ0NxWBbs0KAbTur8VKfTbGeFcbkc1sfnBHuA1CzTIB7js/H5SPffFW3q9sau2PDdLhxkl3X+wiQCVYf4Jt3h1Itmb8iBvEusZJd2a2CuXjxXUWU5dSnAZ5/b0QkOobgMKWzh8eMcXaXr6aYSqfcuXtbAkdbS3RfSD/MGDfvGFO9ZuSfY/ilx/GLumi57Vhgfp9W597ECJA2/a/v/4ENLpYKsDo3kgAAAAASUVORK5CYII=';
            img.title = 'Show calendar';
            a.appendChild(img);
            insertAfter(a, allElements[i]);
        }
    }
}

// Hook myself into the page load event
if (window.addEventListener) { // W3C standard
    //console.log('window.addEventListener');
    window.addEventListener('load', datePickerInit, false);
} else if (window.attachEvent) { // Internet Explorer <= 8 and Opera <= 6.0
    //console.log('window.attachEvent');
    window.attachEvent('onload', datePickerInit);
}
