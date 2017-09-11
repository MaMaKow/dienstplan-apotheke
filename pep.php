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
//TODO: Does this work without login? Will the session management quit the upload?
set_time_limit(0); //Do not stop execution even if we take a LONG time to finish.
ignore_user_abort(true);

function read_file_write_db($filename) {
    global $pdo;
    echo 'Working on input file.<br>\n';
    $handle = fopen($filename, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $hash = hash('sha265', $line); //The hash is stored binary in the database.
            $line_string = str_replace(array("\r\n", "\n", "\r"), '', $line); //remove CR LF from the 
            list($date, $time, $sales_value, $sales_count, $foo, $branch) = explode(';', $line_string);
            if (!is_valid_date($date) OR ! is_valid_date($time) OR ! is_numeric($sales_count) OR ! is_numeric($branch)) {
                continue;
            }
            $sql_date = date('Y-m-d', strtotime($date));
            $statement = $pdo->prepare("INSERT IGNORE INTO pep (hash, Datum, Zeit, Anzahl, Mandant) VALUES (:hash, :sql_date, :time, :sales_count, :branch)");
            $statement->execute(array('hash' => $hash, 'sql_date' => $sql_date, 'time' => $time, 'sales_count' => $sales_count, 'branch' => $branch));
        }
        echo 'Finished processing.<br>';
        fclose($handle);
        if (unlink($filename)) { //delete the file
            error_log('The input file was deleted.');
            echo 'The input file was deleted.<br>';
        } else {
            error_log('Error while deleting input file!');
            echo 'Error while deleting input file!<br>';
        }
    } else {
        error_log(error_get_last());
        error_log('Error while opening input file!');
    }
}

foreach (glob("upload/*_pep") as $filename) {
    error_log("pep.php is working on $filename");
    read_file_write_db($filename);
}

$abfrage = "UPDATE `Dienstplan` set Mittagsbeginn = null WHERE Mittagsbeginn = '00:00:00'";
$ergebnis = mysqli_query_verbose($abfrage);
$abfrage = "UPDATE `Dienstplan` set Mittagsende = null WHERE Mittagsende = '00:00:00'";
$ergebnis = mysqli_query_verbose($abfrage);


$abfrage = "DROP TABLE IF EXISTS `pep_weekday_time`;";
$ergebnis = mysqli_query_verbose($abfrage);

$abfrage = "
   CREATE TABLE IF NOT EXISTS `pep_weekday_time` (
  `Uhrzeit` time NOT NULL,
  `Wochentag` int(11) NOT NULL COMMENT '0=Monday',
  `Mittelwert` float DEFAULT NULL,
  `Mandant` int(11) NOT NULL,
  PRIMARY KEY (`Uhrzeit`,`Wochentag`,`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
";
$ergebnis = mysqli_query_verbose($abfrage);

$abfrage = "DELETE FROM `pep` WHERE DAY(`Datum`) = '24' AND MONTH(`Datum`) = '12';";
$ergebnis = mysqli_query_verbose($abfrage);

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
$ergebnis = mysqli_query_verbose($abfrage);

$abfrage = "DROP TABLE IF EXISTS `pep_month_day`;";
$ergebnis = mysqli_query_verbose($abfrage);

$abfrage = "
CREATE TABLE IF NOT EXISTS `pep_month_day` (
  `day` int(11) NOT NULL,
  `factor` float NOT NULL,
  `branch` int(11) NOT NULL,
  PRIMARY KEY (`day`,`branch`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
";
$ergebnis = mysqli_query_verbose($abfrage);

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
$ergebnis = mysqli_query_verbose($abfrage);

$abfrage = "
    DROP TABLE IF EXISTS `pep_year_month`;
";
$ergebnis = mysqli_query_verbose($abfrage);

$abfrage = "
CREATE TABLE IF NOT EXISTS `pep_year_month` (
  `month` int(11) NOT NULL,
  `factor` float NOT NULL,
  `branch` int(11) NOT NULL,
  PRIMARY KEY (`month`, `branch`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
";
$ergebnis = mysqli_query_verbose($abfrage);

$abfrage = "    
    INSERT INTO `pep_year_month`
        SELECT MONTH(Datum),
            SUM(Anzahl)/COUNT(DISTINCT Datum)/(SELECT SUM(Anzahl)/COUNT(DISTINCT Datum) FROM `pep`),
            `Mandant`
        FROM `pep`
        GROUP BY MONTH(Datum), `Mandant`
    ;";
//TODO: The above code gives a factor of about 0.2 for our smaller pharmacy. We have to check if that is a double factor together with the others!
$ergebnis = mysqli_query_verbose($abfrage);
