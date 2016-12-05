<?php

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
// This script should be called by another part of the program.
// It should run asynchronously.
// Therefore we might give that page time to render, before we execute this task.
//sleep(8);

require_once 'default.php';

/*
$abfrage = "SELECT max(Datum) as Datum FROM `pep`";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));
$row = mysqli_fetch_object($ergebnis);
$newest_pep_date = strtotime($row->Datum);
$today = time();
$seconds_since_last_update = $today - $newest_pep_date;
if ($seconds_since_last_update >= 60*60*24*30*3 ) {
    $Warnmeldung[] = "PEP Information veraltet. Bitte neue PEP-Datei <a href=upload-in.php>hochladen</a>!";
}

$abfrage = "SELECT create_time FROM INFORMATION_SCHEMA.TABLES
  WHERE table_schema = '" . $config['database_name'] . "'
  AND table_name = 'pep_year_month'";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));
$row = mysqli_fetch_object($ergebnis);
$last_pep_update = strtotime($row->create_time);
*/

$abfrage = "UPDATE dienstplan set Mittagsbeginn = null WHERE Mittagsbeginn = '00:00:00'";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));
$abfrage = "UPDATE dienstplan set Mittagsende = null WHERE Mittagsende = '00:00:00'";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));


$abfrage = "DROP TABLE IF EXISTS `pep_weekday_time`;
";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = "
   CREATE TABLE IF NOT EXISTS `pep_weekday_time` (
  `Uhrzeit` time NOT NULL,
  `Wochentag` int(11) NOT NULL COMMENT '0=Monday',
  `Mittelwert` float DEFAULT NULL,
  `Mandant` int(11) NOT NULL,
  PRIMARY KEY (`Uhrzeit`,`Wochentag`,`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = "
DELETE FROM `pep` WHERE DAY(`Datum`) = '24' AND MONTH(`Datum`) = '12';
";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = "

    
    INSERT INTO `pep_weekday_time`
        SELECT SEC_TO_TIME(round(TIME_TO_SEC(`Zeit`)/60/15)*15*60),
            WEEKDAY(Datum),
            sum(Anzahl)/COUNT(DISTINCT `Datum`),
            Mandant
        FROM `pep`
        GROUP BY round(TIME_TO_SEC(`Zeit`)/60/15)*15/60,
            WEEKDAY(Datum),
            Mandant
    ;
";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = "
DROP TABLE IF EXISTS `pep_month_day`;
";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = "
CREATE TABLE IF NOT EXISTS `pep_month_day` (
  `day` int(11) NOT NULL,
  `factor` float NOT NULL,
  `branch` int(11) NOT NULL,
  PRIMARY KEY (`day`,`branch`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = "


    INSERT INTO `pep_month_day`
        SELECT DAYOFMONTH(`Datum`),
            SUM(`Anzahl`)/COUNT(DISTINCT `Datum`)/(SELECT SUM(Anzahl)/COUNT(DISTINCT Datum) FROM `pep`),
            `Mandant`
        FROM `pep`
        GROUP BY DAYOFMONTH(`Datum`),
            `Mandant`
    ;
";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = "
    
DROP TABLE IF EXISTS `pep_year_month`;
";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = "
CREATE TABLE IF NOT EXISTS `pep_year_month` (
  `month` int(11) NOT NULL,
  `factor` float NOT NULL,
  `branch` int(11) NOT NULL,
  PRIMARY KEY (`month`, `branch`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
";
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));

$abfrage = "
    
    INSERT INTO `pep_year_month`
        SELECT MONTH(Datum),
            SUM(Anzahl)/COUNT(DISTINCT Datum)/(SELECT SUM(Anzahl)/COUNT(DISTINCT Datum) FROM `pep`),
            `Mandant`
        FROM `pep`
        GROUP BY MONTH(Datum), `Mandant`
    ;";
//TODO: The above code gives a factor of about 0.2 for our smaller pharmacy. We have to check if that is a double factor together with the others!
$ergebnis = mysqli_query($verbindungi, $abfrage) OR error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die ("Error: $abfrage <br>".mysqli_error($verbindungi));
