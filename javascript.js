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
function rehide_mittag()  {
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
  var beginnId			= document.getElementById("beginn");
  var endeId			= document.getElementById("ende");
  var tageId			= document.getElementById("tage");

  //Wir entnehmen die vorhandenen Werte.
  var beginn			= new Date (beginnId.value);
  var ende			= new Date (endeId.value);
  if (beginn > ende) {alert('Das Ende liegt vor dem Startdatum'); }
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
  tageId.value 	= count;
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
function rehide_kommentar()  {
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
function toggle_show_administration ()
{
  var admin_div_id = document.getElementById('administration');
  if (admin_div_id.style.display == "block")
  {
      admin_div_id.style.display = "none";
  }
  else
  {
      admin_div_id.style.display = "block";
  }

}

//This function is used by stunden-in.php
function updatesaldo()
{
  //Wir lesen die Objekte aus dem HTML code.
  var stundenInputId		= document.getElementById("stunden");
  var stundenSaldoId		= document.getElementById("saldoAlt");
  var stundenSaldoNeuId		= document.getElementById("saldoNeu");

  //Wir entnehmen die vorhandenen Werte.
  if ( stundenSaldoId != null) { //For new Coworkers there is no value set. Therefore we start with 0.
    var stundenSaldoValue		= Number(stundenSaldoId.innerHTML);
  }else {
    var stundenSaldoValue		= 0;
  }
  var stundenInputArray		= stundenInputId.value.split(":");
  if (stundenInputArray[1]) //Wenn es einen Doppelpunkt gibt.
  {
//					document.write('Wir haben einen Doppelpunkt.');
    //Die Eingabe ist eine Zeit mit Doppelpunkt. Wir rechnen in einen float (Kommazahl) um.
    var stundenInputHour 		= Number(stundenInputArray[0]);
    var stundenInputMinute 		= Number(stundenInputArray[1]);
    var stundenInputSecond		= Number(stundenInputArray[2]);

    //Jetzt berechnen wir aus den Daten eine Summe. Dazu formen wir zunächst in ein gültiges Datum um.
    var stundenInputValue = 0;// Wir initialisieren den Input als Null und addieren dann Sekunden, Minuten und Stunden dazu.
    if(!isNaN(stundenInputSecond))
    {
      stundenInputValue		= stundenInputValue + stundenInputSecond/3600;
    }
    if(!isNaN(stundenInputMinute))
    {
      stundenInputValue		= stundenInputValue + stundenInputMinute/60;
    }
    if(!isNaN(stundenInputHour))
    {
      stundenInputValue		= stundenInputValue + stundenInputHour;
    }
    stundenInputId.value = stundenInputValue;
  }
  else
  {
    //Die Stunden sind eine Ganzzahl oder eine Kommazahl.
    //Wir entnehmen die vorhandenen Werte.
    //Wir brauchen die Kommazahl mit einem Punkt, nicht mit einem Komma.
    stundenInputId.value = stundenInputId.value.replace(/,/g, '.')
    var stundenInputValue		= Number(stundenInputId.value);
  }
  var ergebnis		 	= stundenInputValue + stundenSaldoValue;
  stundenSaldoNeuId.value 	= ergebnis;
}
