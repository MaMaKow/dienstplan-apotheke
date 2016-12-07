/* 
 * Copyright (C) 2016 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


  /* global bar_width_factor: is a factor generated within image_dienstplan.php It describes the width of one hour in pixels. */

var selectedElement = 0;
  var currentX = 0;
  var currentY = 0;
  var currentMatrix = 0;

  function selectElement(evt, moveType) {
    var url = window.location.pathname;
    var filename = url.substring(url.lastIndexOf('/')+1);
    if (filename !== 'tag-in.php') {return;} //will stop execution in all other interfaces.

    if (moveType === 'single'){
	selectedElement = evt.target;
    } else if (moveType === 'group'){
 	selectedElement =  evt.target.parentNode;
    } else {
        console.log('Error: selectElement() has to be called with a moveType of either "single" or "group"!' + evt + ", " + moveType);
    }
    var firstX = evt.clientX;
    currentX = evt.clientX;
    currentY = evt.clientY;
    console.log(selectedElement);
    currentMatrix = selectedElement.getAttributeNS(null, "transform").slice(7,-1).split(' ');
    
      for(var i=0; i<currentMatrix.length; i++) {
        currentMatrix[i] = parseFloat(currentMatrix[i]);
      }
    //selectedElement.setAttributeNS(null, "onmouseout", "deselectElement(evt)");
    selectedElement.setAttributeNS(null, "onmouseup", "deselectElement(evt)");
    selectedElement.setAttributeNS(null, "onmousemove", "moveElement(evt)");
    
  }
  
  function moveElement(evt){

    dx = (evt.clientX - currentX) * 0.8;
  currentMatrix[4] += dx;
  newMatrix = "matrix(" + currentMatrix.join(' ') + ")";
  
      
  selectedElement.setAttributeNS(null, "transform", newMatrix);
  currentX = evt.clientX;
  currentY = evt.clientY;
}
function deselectElement(evt){

  if(selectedElement !== 0){
    currentMatrix[4] = Math.round(currentMatrix[4]/bar_width_factor*2)*(bar_width_factor/2);
    var diff_hour = (Math.round(currentMatrix[4]/bar_width_factor*2)/2).valueOf();
    newMatrix = "matrix(" + currentMatrix.join(' ') + ")";
    selectedElement.setAttributeNS(null, "transform", newMatrix);
    var rect_id = selectedElement.id;
    var line = rect_id.substring(rect_id.lastIndexOf('_')+1);
    var column = rect_id.substring(0,rect_id.lastIndexOf('_'));
    if (column === "break_box"){
        syncToTable(line, 'Mittagsbeginn', diff_hour);
        syncToTable(line, 'Mittagsende', diff_hour);
    } else if (column === "work_box"){
        syncToTable(line, 'Dienstbeginn', diff_hour);
        syncToTable(line, 'Dienstende', diff_hour);
    } else {
        console.log('Error: deselectElement() only works on the "break_box" and on the "work_box"!' + evt + ", " + column);
    }
    selectedElement.removeAttributeNS(null, "onmousemove");
    selectedElement.removeAttributeNS(null, "onmouseout");
    selectedElement.removeAttributeNS(null, "onmouseup");
    selectedElement = 0;
  }
}

function syncToTable(line, column, diff_hour){
    console.log('syncToTable with line: ' + line + ' column: ' + column + ' and diff_hour: ' + diff_hour);
    var input_id = document.getElementById('Dienstplan[0][' + column + '][' + line + ']');
//    var previous_time = input_id.value;
    var previous_time = input_id.defaultValue;

    var previous_hour = parseFloat(previous_time.substring(0, previous_time.lastIndexOf(':')));
    var previous_minute = parseFloat(previous_time.substring(previous_time.lastIndexOf(':')+1));
    var previous_minute_float = parseFloat(previous_minute/60);
    var previous_hour_float = previous_hour + previous_minute_float;
    var new_time = (previous_hour + (previous_minute/60) + diff_hour);
    var new_time_minute = (new_time % 1) * 60;
    var new_time_hour = Math.floor(new_time);
    var new_time_minute_string =  ('0'+new_time_minute).slice(-2); //Add a fronting 0 to reach the HH:MM format, which is required.
    var new_time_hour_string =  ('0'+new_time_hour).slice(-2);
    var new_time_string = new_time_hour_string + ":" + new_time_minute_string;
    input_id.value = new_time_string;
    console.log(new_time_string); //debugging
    }