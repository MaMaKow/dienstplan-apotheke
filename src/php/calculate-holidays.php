<?php

/*
 * Copyright (C) 2017 Mandelkow
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

/**
 * This function returns an array of timestamp corresponding to french holidays
 * Taken from http://php.net/manual/en/function.easter-date.php#96686
 * TODO: See https://github.com/citco/carbon/blob/master/src/Carbon.php for more ideas
 */
function get_holidays($year = null, $country = "de") {
    if ($year === null) {
        $year = intval(date("Y"));
    }

    $easter_date = easter_date($year); //Easter[en] = Ostersonntag[de] = Pâques[fr]
    $easter_day = date("j", $easter_date);
    $easter_month = date("n", $easter_date);
    $easter_year = date("Y", $easter_date);


    $French_holidays = array(
        // These days have a fixed date
        mktime(0, 0, 0, 1, 1, $year) => "Jour de l'An",
        mktime(0, 0, 0, 5, 1, $year) => "Fête du travail",
        mktime(0, 0, 0, 5, 8, $year) => "Fête de la Victoire",
        mktime(0, 0, 0, 7, 14, $year) => "Fête Nationale de la France",
        mktime(0, 0, 0, 8, 15, $year) => "Assomption",
        mktime(0, 0, 0, 11, 1, $year) => "Toussaint",
        mktime(0, 0, 0, 11, 11, $year) => "Armistice",
        mktime(0, 0, 0, 12, 25, $year) => "Noel",
        // These days have a date depending on easter
        mktime(0, 0, 0, $easter_month, $easter_day + 1, $easter_year) => "Lundi de Pâques",
        mktime(0, 0, 0, $easter_month, $easter_day + 39, $easter_year) => "Ascension",
        mktime(0, 0, 0, $easter_month, $easter_day + 49, $easter_year) => "Pentecôte",
        mktime(0, 0, 0, $easter_month, $easter_day + 50, $easter_year) => "Lundi de Pentecôte",
    );

    $German_holidays = array(
        // These days have a fixed date
        // This collection works for Mecklenburg-Vorpommern. PLease comment or uncomment entries fitting your specific land!
        mktime(0, 0, 0, 1, 1, $year) => "Neujahr",
        mktime(0, 0, 0, 5, 1, $year) => "Tag der Arbeit",
        mktime(0, 0, 0, 10, 3, $year) => "Tag der Deutschen Einheit",
        mktime(0, 0, 0, 10, 31, $year) => "Reformationstag",
        mktime(0, 0, 0, 12, 25, $year) => "1. Weihnachtsfeiertag",
        mktime(0, 0, 0, 12, 26, $year) => "2. Weihnachtsfeiertag",
        // These days have a date depending on easter
        //mktime(0, 0, 0, $easter_month, $easter_day - 48, $easter_year) => "Rosenmontag",
        //mktime(0, 0, 0, $easter_month, $easter_day - 46, $easter_year) => "Aschermittwoch",
        mktime(0, 0, 0, $easter_month, $easter_day - 2, $easter_year) => "Karfreitag",
        mktime(0, 0, 0, $easter_month, $easter_day, $easter_year) => "Ostersonntag",
        mktime(0, 0, 0, $easter_month, $easter_day + 1, $easter_year) => "Ostermontag",
        mktime(0, 0, 0, $easter_month, $easter_day + 39, $easter_year) => "Himmelfahrt",
        mktime(0, 0, 0, $easter_month, $easter_day + 49, $easter_year) => "Pfingstsonntag",
        mktime(0, 0, 0, $easter_month, $easter_day + 50, $easter_year) => "Pfingstmontag",
            //mktime(0, 0, 0, $easter_month, $easter_day + 60, $easter_year) => "Fronleichnam",
    );
    $British_holidays = array(
        // These days have a fixed date
        mktime(0, 0, 0, 1, 1, $year) => "New Year's Day",
        //mktime(0, 0, 0, 3, 17, $year) => "Saint Patrick's Day",
        //mktime(0, 0, 0, 5, 9, $year) => "Liberation Day",
        mktime(0, 0, 0, 12, 25, $year) => "Christmas Day", //TODO: ignores the fact that this holiday will be off on the next monday if it is on a weekend.
        mktime(0, 0, 0, 12, 26, $year) => "Boxing Day", //TODO: ignores the fact that this holiday will be off on the next tuesday if it is on a weekend.
        // These days have a date depending on easter
        mktime(0, 0, 0, $easter_month, $easter_day - 2, $easter_year) => "Good Friday",
        mktime(0, 0, 0, $easter_month, $easter_day, $easter_year) => "Easter",
        mktime(0, 0, 0, $easter_month, $easter_day + 1, $easter_year) => "Easter Monday",
        //These days are defined by weekdays in a month:
        strtotime("first Monday of May $year") => "Early May Bank Holiday",
        strtotime("last Monday of May $year") => "Spring Bank Holiday",
    );

    switch ($country) {
        case "de":
        case "Germany":
        case "Deutschland":
            $Holidays = $German_holidays;
            break;
        case "fr":
        case "France":
        case "Frankreich":
            $Holidays = $French_holidays;
            break;
        case "en":
        case "Vereinigtes Königreich":
        case "United Kingdom":
        case "United Kingdom of Great Britain and Northern Ireland":
            $Holidays = $British_holidays;
            break;
        default:
            return FALSE; //No holidays without a country
    }

    return $Holidays;
}

function is_holiday($date_unix) {
    $year = intval(date("Y", $date_unix));
    $Holidays = get_holidays($year); //TODO: The application is oblivious to countries right now. Use $Holidays = get_holidays($year, $country); if that values becomes available.
    foreach ($Holidays as $date => $holiday) {
        if (date('Y-d-m', $date_unix) === date('Y-d-m', $date)){
            return $holiday;
        }
    }
    return FALSE;
}

?>