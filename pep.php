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
require 'default.php';
require 'db-verbindung.php';

$abfrage="
    TRUNCATE `pep_zeit_im_wochentag`;
    
    INSERT INTO `pep_zeit_im_wochentag`
        SELECT SEC_TO_TIME(round(TIME_TO_SEC(`Zeit`)/60/15)*15*60),
            WEEKDAY(Datum),
            sum(Anzahl)/COUNT(DISTINCT `Datum`),
            Mandant
        FROM `pep`
        GROUP BY round(TIME_TO_SEC(`Zeit`)/60/15)*15/60,
            WEEKDAY(Datum),
            Mandant
    ;
    
    TRUNCATE `pep_tag_im_monat`;
    
    INSERT INTO `pep_tag_im_monat`
        SELECT DAYOFMONTH(`Datum`),
            SUM(`Anzahl`)/COUNT(DISTINCT `Datum`)/(SELECT SUM(Anzahl)/COUNT(DISTINCT Datum) FROM `pep`),
            `Mandant`
        FROM `pep`
        GROUP BY DAYOFMONTH(`Datum`),
            `Mandant`
    ;
    
    TRUNCATE `pep_monat_im_jahr`;
    
    INSERT INTO `pep_monat_im_jahr`
        SELECT MONTH(Datum),
            SUM(Anzahl)/COUNT(DISTINCT Datum)/(SELECT SUM(Anzahl)/COUNT(DISTINCT Datum) FROM `pep`),
            `Mandant`
        FROM `pep`
        GROUP BY MONTH(Datum)
    ;";

$ergebnis = mysqli_multi_query($verbindungi, $abfrage) OR die ("Error: $abfrage <br>".mysqli_error($verbindungi));
