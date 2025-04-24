<?php

/*
 * Copyright (C) 2025 Mandelkow
 *
 * Dienstplan Apotheke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace PDR\DateTime;

/**
 * FrenchHolidays
 *
 * This class calculates the public holidays in France.
 *
 * Most holidays in metropolitan France are uniform nationwide. However,
 * in the Alsace-Moselle region (e.g. region codes "FR-Alsace" or "FR-Moselle"),
 * two additional holidays are observed:
 * - Vendredi Saint (Good Friday)
 * - Saint Étienne (the day after Christmas)
 *
 * Sources: French official holiday information.
 */
class FrenchHolidays {

    private $listOfHolidays = array();

    public function __construct(int $year, string $regionCode) {
        $this->calculateHolidays($year, $regionCode);
    }

    public function getListOfHolidays(): array {
        return $this->listOfHolidays;
    }

    private function calculateHolidays(int $year, string $regionCode): void {
        $easterTimestamp = easter_date($year);
        $easterDatetime = new \DateTime();
        $easterDatetime->setTimestamp($easterTimestamp);

        // Fixed-date holidays:
        $jourDeLan = new Holiday(new \DateTimeImmutable("$year-01-01"), "Jour de l'An");
        $feteDuTravail = new Holiday(new \DateTimeImmutable("$year-05-01"), "Fête du Travail");
        $victoire1945 = new Holiday(new \DateTimeImmutable("$year-05-08"), "Fête de la Victoire 1945");
        $feteNationale = new Holiday(new \DateTimeImmutable("$year-07-14"), "Fête Nationale");
        $assomption = new Holiday(new \DateTimeImmutable("$year-08-15"), "Assomption");
        $laToussaint = new Holiday(new \DateTimeImmutable("$year-11-01"), "La Toussaint");
        $armistice = new Holiday(new \DateTimeImmutable("$year-11-11"), "Armistice de 1918");
        $noel = new Holiday(new \DateTimeImmutable("$year-12-25"), "Noël");

        // Holidays dependent on Easter:
        $lundiDePaques = new Holiday((clone $easterDatetime)->add(new \DateInterval("P1D")), "Lundi de Pâques");
        $ascension = new Holiday((clone $easterDatetime)->add(new \DateInterval("P39D")), "Ascension");
        $lundiDePentecote = new Holiday((clone $easterDatetime)->add(new \DateInterval("P50D")), "Lundi de Pentecôte");

        // Add national holidays:
        $this->addHoliday($jourDeLan);
        $this->addHoliday($lundiDePaques);
        $this->addHoliday($feteDuTravail);
        $this->addHoliday($victoire1945);
        $this->addHoliday($ascension);
        $this->addHoliday($lundiDePentecote);
        $this->addHoliday($feteNationale);
        $this->addHoliday($assomption);
        $this->addHoliday($laToussaint);
        $this->addHoliday($armistice);
        $this->addHoliday($noel);

        /**
         * Regional differences: Additional holidays in Alsace-Moselle.
         * CAVE: Die aktuellen Ländercodes nach ISO 3166-2:FR sind weiter gefasst.
         * FR-A und FR-57 sind beide Teil des größeren Grand Est (FR-GES).
         * "Saint Étienne" und "Vendredi Saint" können also mit dem offiziellen ISO 3166-2:FR nicht definiert werden.
         * Sie wurden daher in der Konfiguration zusätzlich ergänzt.
         */
        if (in_array($regionCode, array("FR-A", "FR-57"))) {
            $vendrediSaint = new Holiday((clone $easterDatetime)->sub(new \DateInterval("P2D")), "Vendredi Saint");
            $saintEtienne = new Holiday(new \DateTimeImmutable("$year-12-26"), "Saint Étienne");
            $this->addHoliday($vendrediSaint);
            $this->addHoliday($saintEtienne);
        }
    }

    private function addHoliday(Holiday $holiday) {
        $this->listOfHolidays[$holiday->getDate()->format("Y-m-d")] = $holiday;
    }
}
