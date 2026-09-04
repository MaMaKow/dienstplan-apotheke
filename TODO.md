# TODO

## Errors

## Feature requests

### Core

Mitarbeiter selbst tauschen -> Modul bauen

* PSR-4: Die Klassen können mal sortiert und in Ordner gepackt werden. Bei der Gelegenheit kann man direkt mal in Richtung PSR-4 denken. https://www.php-fig.org/psr/psr-4/


* * \\PDR\\Pharmacy\\Branch.php \
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

register_approve.php merge register_approve.php with user-management.php Make this a list of all the users and their status. register_approve.php; Make it something to work with.

Alle Stunden und Abwesenheiten mit aktuellstem Datum zuerst. Abwesenheit mit Filter für Jahr und Reason (Checkbox zur Multi-Auswahl (Javascript?))

filter for absence filter option for years and for specific reasons e.g. Vacation

#### Jahresarbeitszeitkonto Ansicht nach Muster 28
composer require dompdf/dompdf
annual-working-time-account.php

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

Logviewer für das error.log in die / in eine Administraor-Ansicht einbauen.

Löschen von Usern inklusive Löschen von Benutzerberechtigungen

Get rid of PDR_ONE_DAY_IN_SECONDS!

In marginal-employment-hours-list.php Feiertage mit Einbauen
Datei umbenennen z.B. employmee-hours-list.php
Hochformat für den Druck

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
#### Überstunden Eingabe:
beim allerersten Eintrag meckert das Programm.
#### Überstundenberechnung für Weihnachten korrekt durchrechnen
und Silvester plus Tests
#### Sonntagsarbeit
PTA erhalten an Sonntagen 85% Zuschlag auf ihre Stunden


#### Tests ergänzen für:
sick-note-tracking.php


### Compliance / Arbeitsrecht

#### Automatische Prüfung der Ruhezeit (§ 5 ArbZG)

Warnung erzeugen, wenn zwischen zwei Diensten weniger als 11 Stunden Ruhezeit liegen.

#### Prüfung auf aufeinanderfolgende Arbeitstage

Warnung bei zu vielen Arbeitstagen ohne freien Tag.

#### Prüfung von Minderjährigen

Sonderregeln für Auszubildende und Minderjährige nach JArbSchG berücksichtigen.

#### Mutterschutz

Automatische Warnungen bei Diensten, die gegen MuSchG verstoßen.

#### Prüfbericht

Einen Gesamtbericht erzeugen:
- ArbZG
- MuSchG
- JArbSchG
- interne Regeln

mit Ampelsystem (OK / Warnung / Fehler).
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

#### Urlaubstage müssen vermutlich float sein.

https://www.gesetze-im-internet.de/burlg/__5.html
"(2) Bruchteile von Urlaubstagen, die mindestens einen halben Tag ergeben, sind auf volle Urlaubstage aufzurunden."
Das bedeutet gleichzeitig, dass Bruchteile, die unter einen halben Tag ergeben auch als anteilige Tage zu gewähren sind.
Es wird NICHT abgerundet.
https://chatgpt.com/c/674ad677-abdc-8003-9644-885ef01138e5
Wir brauchen darüber hinaus zwei extra Tabellen für Urlaube und für Urlaubsanpassungen.
id, employee_id, year, leave_days_working_days, (leave_days_working_days_adjusted, conversion_rate_to_working_days, comments)
und
id, leave_entitlement_id, date, adjustment_type, adjustment_days_working_days, comments

### Dienstplanqualität

#### Fairness-Auswertung

Kennzahlen je Mitarbeiter:

- Anzahl Samstage
- Anzahl Spätschichten
- Anzahl Frühschichten
- Anzahl Notdienste
- Anzahl geteilte Dienste

mit Vergleich zum Teamdurchschnitt.

#### Konflikterkennung

Automatische Warnung wenn:

- mehrere Schlüsselpersonen gleichzeitig fehlen
- Filiale unter Mindestbesetzung fällt
- Notdienstbesetzung nicht ausreichend ist

#### Simulation

Dienstplan für einen Zeitraum simulieren, ohne Datenbankänderung.

### Abwesenheiten

#### Krankmeldung

Krankmeldungen mit:

- Beginn
- Ende
- AU vorhanden ja/nein
- Datum der AU

verwalten.

### Notdienst

#### Apothekennotdienst importieren

Import offizieller Notdienstpläne.

#### Notdienststatistik

Auswertung:

- Anzahl Notdienste pro Mitarbeiter
- Anzahl Notdienste pro Filiale
- Historie mehrerer Jahre

### Benachrichtigungen

#### Änderungszusammenfassung

Bei Dienstplanänderungen nur eine Sammelmail senden.

#### Benutzerbenachrichtigung konfigurieren

Benutzer kann wählen:

- E-Mail
- iCalendar
- keine Benachrichtigung

### Sicherheit

#### Audit Log

Protokollieren:

- Wer hat was geändert?
- Alter Wert
- Neuer Wert
- Zeitpunkt

#### Zwei-Faktor-Authentifizierung

TOTP-Unterstützung für Administratoren.

### Tests

#### Tests für Arbeitszeitgesetz

Unit-Tests für:

- Ruhezeiten
- Höchstarbeitszeit
- Feiertage
- Nachtarbeit

#### Regressionstests für bekannte Fehler

Für jeden behobenen Bug einen reproduzierbaren Testfall anlegen.
