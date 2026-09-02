<?php

require_once '../../../default.php';
require_once '../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PDR\Database\AbsenceDatabaseHandler;
use PDR\Utility\GeneralUtility;
use PDR\Utility\RosterUtility;

use function Safe\error_log;

$date = user_input::get_variable_from_any_input('datum', FILTER_SANITIZE_NUMBER_INT, (new DateTime())->format('Y-m-d'));
$startDate = new DateTime($date);
$startDate->modify('monday this week');
$endDate = clone $startDate;
$endDate->add(new DateInterval('P6D'));
$workforce = new \PDR\Workforce\Workforce($startDate->format('Y-m-d'));
$employeeKey = user_input::get_variable_from_any_input('employee_key', FILTER_SANITIZE_NUMBER_INT, $workforce->getDefaultEmployeeKey());
$userDialog = new \user_dialog();
$userDialog->readMessagesFromSession();
if (isset($_POST) && !empty($_POST)) {
    // POST data has been submitted, Post/Redirect/Get
    $userDialog->storeMessagesInSession();
    $location = \PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/annual-working-time-account.php' . "?datum=" . $startDate->format('Y-m-d') . "&employee_key=$employeeKey";
    header('Location:' . $location);
    die("<p>Redirect to: <a href=$location>$location</a></p>");
}
if ('' === $employeeKey) {
    die('Fehler: Es konnte kein Mitarbeiter gefunden werden.');
}
try {
    $employeeObject = $workforce->getEmployeeObject($employeeKey);
} catch (Exception $exception) {
    error_log('Employee with key ' . $employeeKey . ' not found.');
    die('Fehler: Es konnte der Mitarbeiter nicht gefunden werden.');
}
$listOfAbsences = AbsenceDatabaseHandler::getAllAbsenceObjectsInPeriod($startDate, $endDate);
$rosterUtility = new RosterUtility();
$weeklyHours = $rosterUtility->calculateWorkingWeeklyHoursInTimeInterval($startDate, $endDate, $workforce, $listOfAbsences);
$inputDateHtmlString = build_html_navigation_elements::build_input_date($startDate->format('Y-m-d'));
$inputEmployeeHtmlString = build_html_navigation_elements::build_select_employee($employeeKey, $workforce->getListOfEmployees());
require_once '../classes/class.roster.php';
$roster = new Roster($startDate, $endDate, $employeeKey);
//$weekHoursShould = PDR\Utility\RosterUtility::calculateWorkingWeekHoursShould($roster, $workforce);
$arrayOfDaysOfRosterItems = $roster->array_of_days_of_roster_items ;
$weekData = [];
foreach ($arrayOfDaysOfRosterItems as $rosterDayArray) {
    $rosterDayDate = $rosterDayArray[0]->get_date_object();
    foreach ($rosterDayArray as $rosterItem) {
        $rosterHoursHave = $rosterItem->get_duty_duration();
        $absenceCollection = \PDR\Database\AbsenceDatabaseHandler::readAbsenteesOnDate($rosterItem->get_date_object()->format('Y-m-d'));
        $hoursShould = RosterUtility::calculateWorkingHoursDayEmployeeShould($rosterItem->get_date_object(), $employeeObject, $absenceCollection);
        $weekData[$rosterDayDate->format('Y-m-d')] = [
            'date' => $rosterDayDate,
            'should' => $hoursShould,
            'haveSeconds' => $rosterHoursHave,
            'night' => 'not implemented yet',
            'more41' => 'not implemented yet',
            'more51' => 'not implemented yet',
        ];
    }
}

$jahr_kw = $startDate->format('Y') . " / " . $startDate->format('W');
$mitarbeiter = $workforce->getEmployeeFullName($employeeKey);

require \PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require \PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';

echo $inputDateHtmlString;
echo $inputEmployeeHtmlString;

// 2. HTML-Inhalt mit eingebettetem CSS für präzisen Druck
$html = '
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.3; }
        .header { margin-bottom: 15px; position: relative; }
        .title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 5px; }
        .subtitle { font-size: 12px; text-align: center; margin-bottom: 15px; }
        .meta-info { font-size: 11px; margin-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: avoid; }
        th, td { border: 1px solid #000; padding: 6px 4px; text-align: center; vertical-align: middle; }
        th { background-color: #f5f5f5; font-weight: normal; font-size: 10px; }
        .bold { font-weight: bold; }
        .left { text-align: left; padding-left: 8px; }
        
        .footnote { font-size: 9px; text-align: justify; margin-top: 10px; border-top: 1px solid #ccc; padding-top: 5px; }
        .footer-line { font-size: 9px; margin-top: 30px; width: 100%; }
        .footer-left { float: left; }
        .footer-right { float: right; }
    </style>
</head>
<body>

    <div class="header">
        <div style="float: right; font-size: 10px;">Muster 28</div>
        <div style="font-size: 10px;">Textmuster/Vordrucke</div>
        <div class="title">Jahresarbeitszeitkonto</div>
        <div class="subtitle">Ausgleichszeitraum Jahr/KW: <strong>' . htmlspecialchars($jahr_kw) . '</strong></div>
        <div class="meta-info">Mitarbeiter: <strong>' . htmlspecialchars($mitarbeiter) . '</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">Tag</th>
                <th rowspan="2" style="width: 7%;">Datum</th>
                <th style="width: 14%;">Regelmäßige Arbeitszeit<br>(Beginn / Ende)</th>
                <th style="width: 14%;">Geleistete Stunden<br>abzgl. Pausen</th>
                <th rowspan="2" style="width: 12%;">Nacht-, Sonn-<br>und Feiertagsarbeit</th>
                <th colspan="2" style="width: 14%;">Mehrarbeit* ab</th>
                <th rowspan="2" style="width: 7%;">Soll-<br>Arbeitszeit<br>Woche</th>
                <th rowspan="2" style="width: 7%;">Ist-<br>Arbeitszeit<br>Woche</th>
                <th rowspan="2" style="width: 7%;" class="bold">Saldo<br>Woche</th>
                <th rowspan="2" style="width: 7%;" class="bold">Saldo<br>Jahr</th>
            </tr>
            <tr>
                <th>Soll-Arbeitszeit</th>
                <th>Ist-Arbeitszeit</th>
                <th>41. Std.</th>
                <th>51. Std.</th>
            </tr>
        </thead>
        <tbody>';

// Zeilen für die Wochentage generieren
foreach ($weekData as $day => $data) {
    $html .= '<tr>
        <td class="bold">' . htmlspecialchars($data['date']->format('D')) . '</td>
        <td>' . htmlspecialchars($data['date']->format('d.m.')) . '</td>
        <td>' . htmlspecialchars($data['should']) . '</td>
        <td>' . htmlspecialchars($data['haveSeconds'] / 3600) . '</td>
        <td>' . htmlspecialchars($data['night']) . '</td>
        <td>' . htmlspecialchars($data['more_41']) . '</td>
        <td>' . htmlspecialchars($data['mehr_51']) . '</td>';


    // Die Wochen- und Jahresfelder werden im Original-Formular meist nur einmal pro Block befüllt
    if ($day === 'Mo') {
        $html .= '<td rowspan="7">40:00</td><td rowspan="7">43:30</td><td rowspan="7" class="bold">+03:30</td><td rowspan="7" class="bold">+12:00</td>';
    }
    $html .= '</tr>';
}

// Zeile für die Abzeichnung
$html .= '<tr>
            <td class="bold left" colspan="7">Abzeichnung:</td>
            <td></td><td></td><td></td><td></td>
          </tr>';

$html .= '
        </tbody>
    </table>

    <div class="footnote">
        * Mehrarbeit wird zunächst zuschlagsfrei in das Arbeitszeitkonto eingestellt. Die Mehrarbeitszuschläge
        kommen erst im Rahmen des Ausgleichs des Arbeitszeitkontos ggf. zur Anwendung (§ 4 Abs. 4 S. 6 BRTV),
        so dass an dieser Stelle schon eine Differenzierung hinsichtlich der Mehrarbeit ab 41. bzw. 51. Stunde vorgenommen werden sollte.
    </div>

    <div class="footer-line">
        <span class="footer-left">Gestaltet nach Muster 28 aus Fichtel/Mettang, Bundesrahmentarifvertrag</span>
        <span class="footer-right">Teil D/S. 119</span>
    </div>

</body>
</html>';

// 3. Dompdf initialisieren und PDF generieren
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false); // True, falls wir externe Bilder/Logos laden wollen

echo "$html";

//$dompdf = new Dompdf($options);
//$dompdf->loadHtml($html);
//$dompdf->render();

// 4. PDF an den Browser ausgeben (Anzeigen statt direktem Download)
//$dompdf->stream("arbeitszeitkonto_" . str_replace(' ', '_', $jahr_kw) . ".pdf", ["Attachment" => false]);
