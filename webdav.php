<?php
//Wir erstellen eine umfassende Icalendar Datei (ICS). Diese kann dann von Kalenderprogrammen aboniert werden.
require 'default.php';
require 'db-lesen-mandant.php';
$tage = 30;
if (!isset($datum)) {
    //a date may be given by GET otherwise we use TODAY
    $datum = date('Y-m-d');
}
require 'db-lesen-mitarbeiter.php';
require 'get-auswertung.php';
if (!isset($auswahl_mitarbeiter)) {
    //TODO: This should be an exeption. But how are those communicated within webdav format?
    $auswahl_mitarbeiter = 1;
}
require 'db-lesen-woche-mitarbeiter.php';

require 'schreiben-ics.php';
$textICS = schreiben_ics($Dienstplan);
header('Content-type: text/Calendar');
header('Content-Disposition: attachment; filename="Calendar.ics"');
echo $textICS;
