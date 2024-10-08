# TODO

## Errors

### Failure of functions

#### Roster view

Drag and drop is somehow defective. - newy inserted employees do not show up in the plot. - get rid of transform

### Logical errors

The following line should not be shown on saturdays and sundays, if Monday until Friday are set. "The are no opening times stored inside the database for this weekday. Please configure the opening times!"

There is an error message: "No compatible database driver found. Please install one of the following database management systems and the corresponding PHP driver!" But there is no list given.

### Design errors

fragment.principle-roster-day-history.php does not allways result in the chosen date. For example when the rotation weekdoes not match the chosen week. Perhaps more care should be given to the possible change dates?

collaborative-vacation-year.php weird style

Change the navigation menu On mobile touch devices, some menu items are not reachable.

collaborative-vacation.css might contain some rules that do not match anything anymore. Please clean up.

Nutzen Sie "Formular zurücksetzen" um Daten für einen neuen Mandanten einzugeben funktioniert nicht, ist auch doof

## Feature requests

### Core

Encapsulate class workforce

remove class email stub or make it a helper for PHPMailer

perhaps build a real absence class with real absence objects.

Checkout: backup_employee_data_update Ich habe einen weiteren Trigger hinzugefügt und wieder gelöscht. Das ist aber nicht das eigentliche Ziel. Ziel ist es, eine große employee Tabelle zu haben, in der alles drinnen steht. PS: Archiv könnte als SQL-Backup geführt werden. Aber es sollten nur die Inhalte, die länger als ein Monat alt sind, drin stehen, und dann unveränderlich,...

Mitarbeiter selbst tauschen -> Modul bauen

* Notdienst Eingabe-Maske
  * use an API to get the data for the emergency services


* database Ware-Termine / Ware-PEP
* PSR-4: Die Klassen können mal sortiert und in Ordner gepackt werden. Bei der Gelegenheit kann man direkt mal in Richtung PSR-4 denken. https://www.php-fig.org/psr/psr-4/

  * \\PDR\\Pharmacy\\Branch.php \
  * \PDR\\Pharmacy\\NetworkOfBranchOffices.php
  * \\PDR\\Workforce\\Absence.php
  * \\PDR\\Workforce\\Overtime.php
  * \PDR\\Workforce\\Employee.php
  * \\PDR\\Workforce\\Workforce.php
  * \\PDR\\Workforce\\EmployeeManagement.php instead of \PDR\\Workforce\\HumanResourceManagement.php
  * \\PDR\\Roster\\Roster.php (Eine ganze Woche/Monat/beliebiger Bereich)

  *  \\PDR\\Roster\\RosterDayArray.php (alle Items aus einem Tag)
  *  \\PDR\\Roster\\RosterItem.php
  *  \\PDR\\Roster\\RosterItemEmpty.php
  *  \\PDR\\Roster\\AlternatingWeek.php
  *  \\PDR\\Roster\\ExamineAttendance.php
  *  \\PDR\\Roster\\RosterApproval.php
  *  \\PDR\\Roster\\RosterHeadcount.php
  *  \\PDR\\Roster\\ExamineRoster.php
  *  \\PDR\\Roster\\PrincipleRoster.php
  *  \\PDR\\Roster\\PrincipleRosterItem.php
  *  \\PDR\\Roster\\PrincipleRosterHistory.php
  *  \\PDR\\Roster\\RosterLogicException.php
  *  \\PDR\\Roster\\SaturdayRotation.php
  *  \\PDR\\Roster\\TaskRotation.php
  * \\PDR\\DateTime\\Holidays.php
  *  \\PDR\\DateTime\\GeneralCalculations.php
  *  \\PDR\\DateTime\\ValidFrom.php
  * \\PDR\\Input\\UserInput.php
  * \\PDR\\Output\\HTML\\NavigationElements.php
  *  \\PDR\\Output\\HTML\\FormElements.php
  *  \\PDR\\Output\\HTML\\RosterViews.php
  *  \\PDR\\Output\\HTML\\CollaborativeVacation.php
  *  \\PDR\\Output\\HTML\\PharmacyEmergencyService.php = class.pharmacy_emergency_service_builder.php
  *  \\PDR\\Output\\HTML\\UserDialog.php
  *  \\PDR\\Output\\ICalendar.php
  *  \\PDR\\Output\\Email\\Email.php
  *  \\PDR\\Output\\Email\\UserDialogEmail.php
  *  \\PDR\\Output\\Image\\RosterBarPlot.php
  *  \\PDR\\Output\\Image\\RosterHistogramm.php
  * \\PDR\\Application\\Configuration.php
  *  \\PDR\\Application\\DatabaseWrapper.php
  *  \\PDR\\Application\\UpdateDatabase.php
  *  \\PDR\\Application\\Diff.php
  *  \\PDR\\Application\\Install.php
  *  \\PDR\\Application\\Users\\User.php
  *  \\PDR\\Application\\Users\\HaveIBeenPwned.php
  *  \\PDR\\Application\\Users\\Sessions.php
  *  \\PDR\\Application\\Localization.php
  *  \\PDR\\Application\\Maintenance.php
  *  \\PDR\\Application\\TestHtaccess.php

#### Rewrite database table `Dienstplan`

* make a table `roster` with a surrogate primary key.
* align with the RFC for iCalendar data.
* make duty_start, duty_end and perhaps the break DateTime objects.
  * So they can span more than one single day (e.g. 22:00 on Monday until 08:00 on Tuesday).

### Web

Use smudge / clean in git to Insert the version in about.php Version: 0.14.1

Restructure the menu:

* Dienstpläne
* Grundpläne
* Abwesenheiten
* Überstunden

Edit existing overtime in overtime-edit.php inside the table edit option for overtime

human-resource-management.php is missing a feature to delete (archive) an employee

Test password strength upon registration

register_approve.php merge register_approve.php with user-management.php Make this a list of all the users and their status. register_approve.php; Make it something to work with.

Insert information about emergency service for saturday-list on with emergency service on fridays, saturdays or sundays.

Alle Stunden und Abwesenheiten mit aktuellstem Datum zuerst. Abwesenheit mit Filter für Jahr und Reason (Checkbox zur Multi-Auswahl (Javascript?))

filter for absence filter option for years and for specific reasons e.g. Vacation

### API

Eine API benutzen um Ferien im aktuellen Bundesland zu lesen. Diese sollten in einer Datenbanktabelle zwischengespeichert werden. z.B. https://ferien-api.de/api/v1/holidays/BY/2021 z.B. https://openholidaysapi.org/SchoolHolidays?countryIsoCode=DE&subdivisionCode=DE-BY&languageIsoCode=DE&validFrom=2023-01-01&validTo=2024-12-31

Build an API for android apps

* http://restcookbook.com/
* https://restfulapi.net/resource-naming/
* https://shareurcodes.com/blog/creating%20a%20simple%20rest%20api%20in%20php
  * use a secure token for login
  * build an android app

#### API development

##### Domain semantics

The API covers at least:

* roster,
* absence,
* and overtime

##### Architecture style

* event-driven (no),
* URI CRUD-based (yes?)
* and/or a Hypermedia API (yes)?
  * http://stateless.co/hal_specification.html

##### Style guide

* media type (JSON),
* the kind of authentication,
* paginate results,
* naming conventions,
* URI formatting

##### Apache

Tell the webserver how to serve the API paths with .htaccess:

```
RewriteRule ^(.*)$ index.php?handler=$1 [QSA,L]
```

##### Classes

class.api_response.php

class.api_request.php

##### Pages / end points

api.php

### Documentation

write more documenation about the "webdav" api also include microsoft outlook or thunderbird lightning? Deutsche Bilder für die deutsche Dokumentation

collaborative-vacation is not colloborative-vacation, correct this in the doc image files.

### Other

Move all the database interaction into the respective classes

Write an updater That includes a webservice, which holds the current master state and an API to answer update queries.

Employees should allways have a branch, which they belong to (or NULL). Branches in the employee table have to exist in the branches table too!

Is PDR_ONE_DAY_IN_SECONDS obsolete allready?

Do opening times and principle roster work on single-branch setups?

Find a cool name?

* schemist
* scalendar
* Intention-to-treat
* FrontOffice
* ... or something german?
  * ApoPlan
  * Schichthekia
  * Apotheke-Mit-Planer
  * Personalplaner
  * Kollegium
  * Masterplan
  * Kalkül, Kalkülator
  * Zeit-Plan
  * Arbeitsplan
  * Lebens-Zeit-Plan

Organize arrays in classes as collection class: https://www.sitepoint.com/collection-classes-in-php/

* Gesetz zum Elterngeld und zur Elternzeit (Bundeselterngeld- und Elternzeitgesetz - BEEG)
* § 17 Abs. 1
* Der Arbeitgeber kann den Erholungsurlaub, der dem Arbeitnehmer oder der Arbeitnehmerin für das Urlaubsjahr zusteht,
* für jeden vollen Kalendermonat der Elternzeit um ein Zwölftel kürzen.
* Dies gilt nicht, wenn der Arbeitnehmer oder die Arbeitnehmerin während der Elternzeit bei seinem oder ihrem Arbeitgeber Teilzeitarbeit leistet.
*
* This is facultative and to be decided by the employer.
* Es könnte eine Datenbanktabelle geben, die überwacht, ob jeder Mitarbeiter den Urlaub so wie vereinbart genommen hat.
* Dort könnten dann Vereinbarungen der Leitung mit dem Mitarbeiter vermerkt werden.

PEP data

task_rotaion rezeptur mit einem kleinen Häkchen, ob die ganze Woche gemeint ist. Oder mit einer Abfrage per Javascript onChange

Ende der Beschäftigung sollte eine Information beinhalten. Ist das der letzte Arbeitstag(ja), oder der erste ohne(nein)? Ist das über das ganze Programm einheitlich abgebildet?

give focus to the input, created for new lines

readability, increase the size of the roster plot in the week view

In der Datenbank befindet sich kein Dienstplan. Dies ist ein Vorschlag.

Am Samstag in der kleinen Filiale

Es wäre schön, wenn man Pläne als Spielwiese ablegen könnte. Dann kann man schon mal etwas vorzeigen, durchspielen und dann später aktivieren.

Samstags-Rotation mit Edit-Funktion im Sams-Tag oder in der Übersicht GUI für die Samstags-Rotation (ähnlich wie Rezeptur?)

On update of date input has to be changed!

Inaktive Benutzer funktioniert nicht!

Grundplan Tagesansicht

$workforce mit der gesamten zukünftigen Workforce zusammen um zukünftige Mitarbeiter einzuplanen

Grundplan Tagesansicht Mit Gültigkeitszeitraum von bis

Maintenance Planung einer Kündigung Überstunden archivieren, Überstunden nullen Account deaktivieren Ausführung zum Zeitpunkt der Kündigung Vorher Prüfung, ob Kündigung weiter besteht Auslösen über GUI Alte Mitarbeiter archivieren nach Kündigung

collaborative-vacation-year.php Ein Filter für Personen. Der Filter sollte auch ausgestellt werden können.

Resturlaub als automatischen Vorschlag

Überschneidungen in der Abwesenheit finden und warnen

Es wurden bisher 27 Urlaubstage genommen. vielleicht besser: Es wurden bisher 27 Urlaubstage und 3 Resturlaubstage genommen. Dem Mitarbeiter stehen 28+4 von 28+4 Urlaubstagen im Jahr 2018 zu. Es wurden bisher 21+4 Urlaubstage genommen. Es wurden bereits 2 Resturlaubstage im Jahr 2019 beantragt. Es stehen noch 5 Urlaubstage zur Verfügung. Werte in Klammern sind dann Resturlaubstage, wenn vorhanden (>0).

Mindestanzahl Mitarbeiter konfigurierbar nach Zeit!

Kommentare werden im Grundplan nicht angezeigt.

Grundplan mit größer oder größer gleich? Wird der erste Tag als geltender Tag mitgerechnet?

Arbeitsstunden je Arbeitswoche abschaffen

Spätschichtzähler für die Woche und Anzeige als Warnung?

Fehler beim Löschen von einem Mitarbeiter, der zwei mal eingetragen ist. Das Löschen funktioniert nicht, wenn jemand zwei mal eingetragen ist.

Benutzer löschen

Urlaub Jahresansicht, Ansicht für jeden, Edit nur für Rechteinhaber

POEDIT Fehler: The break starts, before it ends. Employee id: 2 Start of duty: 02:30

No login credentials were given -> gettext -> hübsch?

Print: Hintergrund weiß Email: Datum: 05.12.2019 Ihr Dienstplan wurde geändert. Sie arbeiten zu folgenden Zeiten: Apotheke am Marienplatz Beginn und Ende des Dienstes: Von 09:00 bis 17:30 Start und Ende der Pause: Von 12:00 bis 12:30 Die Mails kommen immer zwei mal. Einam würde ja reichen.

Strg + S umdeuten auf this.form.submit mit Bestimmung des gerade aktiven Formulars

Berechnung der Tage beim Urlaub prüfen! 19.01.2019-23.01.2019 sind 3 Tage, nicht 5, oder? Was ist mit 24.01. bis 29.01.2019?

Fehlermeldung erzeugen, wenn Urlaub nach Ende der Beschäftigung eingetragen wird. Ich wollte VK1 Urlaub in 2020 eintragen. Aber der Input wurde silent ignoriert. Das lag daran, dass Schepi ab 2019 das "Ende der Beschäftigung" erreicht hatte.

Der Grundplan in der Tagesansicht sollte vermutlich immer die aktuellsten Pläne der Zukunft anzeigen. Vergangenheit kann man dann manuell ansehen. Beim Speichern springt der Grundplan hinter das gerade gespeicherte zurück. Das ist sehr verwirrend.

/collaborative-vacation-month.php kaputt? Er löscht mir Frau 8, wenn ich Frau 4 eintrage und umgekehrt. Ein Problem mit cookies? Wenn in der Monatsansicht eine Abwesenheit eingetragen wird, und dann eine zweite, dann überschreibt die zweite Eintragung die erste. Eine dritte zerstört die zweite

"Es gibt keine Änderungen. Sie werden zurück gesendet." Wenn ein Mitarbeiter aus dem Grundplan gelöscht wird.

Die Logik im Grundplan ist kaputt. Wenn jemand aufhört an einem tag zu arbeiten, dann wird dadurch kein neuer Datums-Punkt gesetzt. Frau 21 wird ab März nur noch Dienstags und Donnerstags arbeiten. Das ist im GUI nicht zu hinterlegen. Man kann auch wenn man manuell in der Datenbank die Einträge mit einem Enddatum versetzt nicht im GUI sehen, dass es so ist. Ich glaube, dass die Grundplan-Klasse nur Daten mit neuem Anfangsdatum als valide annimmt. Neue Enddaten werden ignoriert.

Ich hätte gerne die Option, für jeden Mitarbeiter festzulegen, wie die Überstunden berechnet werden. Standard = VK5 / VK16 Spezial = VK8 +4 -4 = 0 Halbrichtig = -1,5 == 0

Dienstplan Mitarbeiteransicht mit Kommentar für Personen mit Bearbeiterprivileg

play around with docker Post/Redirect/Get

userDialogContactFormDiv

#### das Kontaktformular ist aus dem Script register.php erreichbar.

Dadurch kommt es mitunter zu Kommunikation aus scannern, die die website untersuchen.

Vielleicht sollte ich für register.php auch ein rate-limiting einsetzen. und für vergessene Passwörter auch. Da kamen am 04.10.2022 sehr viele Emails von einem bot, der den Dienstplan tiefgehend untersucht hat.


Write selenium tests for user-management.php
Write selenium tests for maintenance
    Create data for an employee in the past; end the employment of the employee; logout; login; make sure, that the data is gone.
    How can we force maintenance to happen during the testing period?

Logviewer für das error.log in die / in eine Administraor-Ansicht einbauen.

Löschen von Usern inklusive Löschen von Benutzerberechtigungen

Get rid of PDR_ONE_DAY_IN_SECONDS!

In marginal-employment-hours-list.php Feiertage mit Einbauen
Datei umbenennen z.B. employmee-hours-list.php
Hochformat für den Druck

In overtime-edit.php die Möglichkeit einbauen, einzelne Überstunden zu ändern.

Write/Update tests for user-management.php

get rid of: "global $config;", use "$configuration = new \PDR\Application\configuration();" instead

#### Build a docker image for the dienstplan-apotheke PHP application.
This docker image could be used for testing.
It should exist in different versions of PHP.
These versions should include PHP 5.6 only to see, if the installation fails gracefully.
5.6, 7.0, 7.4, 8.0
current stable and current beta
https://docs.docker.com/language/php/containerize/

#### Menü Eintrag für Listen getrennt von Administration
### Überstunden
#### Überstunden eintragen aus der Wochenansicht Mitarbeiter
Wochenstunden > Ist, Soll, Diff > <button>Eintragen</button>
#### Überstunden Eingabe:
beim allerersten Eintrag meckert das Programm.
#### Überstundenberechnung für Weihnachten korrekt durchrechnen
und Silvester
#### Fehler auffangen beim Versenden von leeren Werten
./src/php/pages/overtime-edit.php
#### Sonntagsarbeit
PTA erhalten an Sonntagen 85% Zuschlag auf ihre Stunden
#### Feiertage
https://www.pharmazeutische-zeitung.de/wie-werden-feiertage-richtig-abgerechnet-146396/
... dass für Feiertage die Zeit gutgeschrieben werden muss, die man ansonsten normalerweise gearbeitet hätte (§ 2 Entgeltfortzahlungsgesetz). ...
#### class law_and_order {

//This class should be used to check plans for adherence to legal requirements

    public function check_maximum_working_hours($date_sql) {
        /*
         * Germany
         * Arbeitszeitgesetz (ArbZG)
         * § 3 Arbeitszeit der Arbeitnehmer
         * Die werktägliche Arbeitszeit der Arbeitnehmer darf acht Stunden nicht überschreiten.
         * Sie kann auf bis zu zehn Stunden nur verlängert werden,
         *  wenn innerhalb von sechs Kalendermonaten oder innerhalb von 24 Wochen im Durchschnitt acht Stunden werktäglich nicht überschritten werden.
         */
  private function build_error_message_maximum_working_hours($average_working_hours, $employee_id) {
        global $Mitarbeiter;
        $error_message = $Mitarbeiter[$employee_id] . " arbeitet im Durchschnitt " . $average_working_hours 
                . " das ist ein Verstoß gegen <a href='http://www.gesetze-im-internet.de/arbzg/__3.html'>§3 ArbZG</a>!";
        if (!function_exists(build_warning_messages)) {
            require_once 'src/php/build-warning-messages.php';
        }
        return build_warning_messages($error_message);
    }	    }
