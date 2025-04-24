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
 * Description of GermanHolidays
 * Details wurden entnommen aus: https://www.dgb.de/service/ratgeber/feiertage/
 * @author Mandelkow
 */
class GermanHolidays {

    /**
     *
     * @var array An array in the format date string (Y-m-d) => PDR\DateTime\Holiday
     */
    private $listOfHolidays = array();

    public function __construct(int $year, string $stateCode = "DE-MV") {
        $this->calculateHolidays($year, $stateCode);
    }

    public function getListOfHolidays(): array {
        return $this->listOfHolidays;
    }

    private function calculateHolidays(int $year, string $stateCode): void {
        $easterTimestamp = easter_date($year);
        $easterDatetime = new \DateTime();
        $easterDatetime->setTimestamp($easterTimestamp);
        // These days have a fixed date
        $neujahr = new Holiday(new \DateTimeImmutable("01.01.$year"), "Neujahr");
        $tagDerArbeit = new Holiday(new \DateTimeImmutable("01.05.$year"), "Tag der Arbeit");
        $tagDerDeutschenEinheit = new Holiday(new \DateTimeImmutable("03.10.$year"), "Tag der Deutschen Einheit");
        $ersterWeihnachtsfeiertag = new Holiday(new \DateTimeImmutable("25.12.$year"), "1. Weihnachtsfeiertag");
        $zweiterWeihnachtsfeiertag = new Holiday(new \DateTimeImmutable("26.12.$year"), "2. Weihnachtsfeiertag");
        $reformationstag = new Holiday(new \DateTimeImmutable("31.10.$year"), "Reformationstag");
        $heiligeDreiKönige = new Holiday(new \DateTimeImmutable("06.01.$year"), "Heilige Drei Könige");
        $internationalerFrauentag = new Holiday(new \DateTimeImmutable("08.03.$year"), "Internationaler Frauentag");
        $befreiungVomNationalsozialismus = new Holiday(new \DateTimeImmutable("08.05.$year"), "Jahrestag der Befreiung vom Nationalsozialismus");
        $volksaufstand = new Holiday(new \DateTimeImmutable("17.06.$year"), "Volksaufstand vom 17. Juni 1953");
        $mariäHimmelfahrt = new Holiday(new \DateTimeImmutable("15.08.$year"), "Mariä Himmelfahrt");
        $weltkindertag = new Holiday(new \DateTimeImmutable("20.09.$year"), "Weltkindertag");
        $allerheiligen = new Holiday(new \DateTimeImmutable("01.11.$year"), "Allerheiligen");

        // These days have a date depending on easter
        $rosenmontag = new Holiday((clone $easterDatetime)->sub(new \DateInterval("P48D")), "Rosenmontag");
        $aschermittwoch = new Holiday((clone $easterDatetime)->sub(new \DateInterval("P46D")), "Aschermittwoch");
        $karfreitag = new Holiday((clone $easterDatetime)->sub(new \DateInterval("P2D")), "Karfreitag");
        $ostersonntag = new Holiday((clone $easterDatetime), "Ostersonntag");
        $ostermontag = new Holiday((clone $easterDatetime)->add(new \DateInterval("P1D")), "Ostermontag");
        $himmelfahrt = new Holiday((clone $easterDatetime)->add(new \DateInterval("P39D")), "Himmelfahrt");
        $pfingstsonntag = new Holiday((clone $easterDatetime)->add(new \DateInterval("P49D")), "Pfingstsonntag");
        $pfingstmontag = new Holiday((clone $easterDatetime)->add(new \DateInterval("P50D")), "Pfingstmontag");
        $fronleichnam = new Holiday((clone $easterDatetime)->add(new \DateInterval("P60D")), "Fronleichnam"); //In Sachsen nur teilweise, In Thüringen nur teilweise. Findet hier keine Beachtung.
        // This hoiday depends on the position towards advent
        $bussUndBettag = new Holiday($this->getBußUndBettag($year), "Buß und Bettag");

        /**
         * Add all the common holidays:
         */
        $this->addHoliday($neujahr);
        $this->addHoliday($tagDerArbeit);
        $this->addHoliday($tagDerDeutschenEinheit);
        $this->addHoliday($ersterWeihnachtsfeiertag);
        $this->addHoliday($zweiterWeihnachtsfeiertag);
        $this->addHoliday($karfreitag);
        $this->addHoliday($ostermontag);
        $this->addHoliday($himmelfahrt);
        $this->addHoliday($pfingstmontag);
        /**
         * Add some state specific holidays
         */
        switch ($stateCode) {
            case "DE-BE":
                $this->addHoliday($internationalerFrauentag);
                if ($year == 2025) {
                    /**
                     * <lang=de>Ausschließlich im Jahr 2025 zum 80. Jubiläum</p>
                     */
                    $this->addHoliday($befreiungVomNationalsozialismus);
                }
                if ($year == 2028) {
                    /**
                     * <lang=de>Ausschließlich im Jahr 2028 zum 75. Jubiläum</p>
                     */
                    $this->addHoliday($volksaufstand);
                }
                break;
            case "DE-MV":
                $this->addHoliday($reformationstag);
                if ($year >= 2023) {
                    /**
                     * <lang=de>Ab dem Jahr 2023 ist der internationale Frauentag am 08. März in Mecklenburg Vorpommern ein Feiertag.</p>
                     */
                    $this->addHoliday($internationalerFrauentag);
                }

                break;
            case "DE-SN":
                $this->addHoliday($bussUndBettag);
                $this->addHoliday($reformationstag);
                break;
            case "DE-HB":
            case "DE-HH":
            case "DE-NI":
            case "DE-SH":
                $this->addHoliday($reformationstag);
                break;
            case "DE-BW":
                $this->addHoliday($heiligeDreiKönige);
                $this->addHoliday($fronleichnam);
                $this->addHoliday($allerheiligen);
                break;
            case "DE-BY":
                $this->addHoliday($allerheiligen);
                $this->addHoliday($heiligeDreiKönige);
                $this->addHoliday($fronleichnam);
                $this->addHoliday($mariäHimmelfahrt); // Betrifft die meisten Gemeinden, aber nicht alle (1700 ja, 350 nein)
                break;
            case "DE-NW":
            case "DE-RP":
                $this->addHoliday($allerheiligen);
                $this->addHoliday($fronleichnam);
                break;
            case "DE-HE":
                $this->addHoliday($fronleichnam);
                break;
            case "DE-SL":
                $this->addHoliday($allerheiligen);
                $this->addHoliday($fronleichnam);
                $this->addHoliday($mariäHimmelfahrt);
                break;
            case "DE-ST":
                $this->addHoliday($heiligeDreiKönige);
                $this->addHoliday($reformationstag);
                break;
            case "DE-BB":
                $this->addHoliday($ostersonntag); //Einzig das Land Brandenburg behandelt den Ostersonntag explizit als gesetzlichen Feiertag.
                $this->addHoliday($pfingstsonntag); //Einzig das Land Brandenburg behandelt den Pfingstsonntag explizit als gesetzlichen Feiertag.
                $this->addHoliday($reformationstag);
                break;
            case "DE-TH":
                $this->addHoliday($weltkindertag);
                $this->addHoliday($reformationstag);
                break;
        }
    }

    private function addHoliday(Holiday $holiday) {
        $this->listOfHolidays[$holiday->getDate()->format("Y-m-d")] = $holiday;
    }

    private function getBußUndBettag(int $year): \DateTime {
        // 23. November des gegebenen Jahres
        $date = new \DateTime("$year-11-23");

        // Zurückgehen bis zum letzten Mittwoch
        while ($date->format('N') !== '3') { // 'N' gibt den Wochentag zurück (1=Montag, ..., 7=Sonntag)
            $date->modify('-1 day');
        }

        return $date;
    }
}
