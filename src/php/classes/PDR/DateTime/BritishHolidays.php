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
 * BritishHolidays
 *
 * This class calculates the public holidays in Great Britain.
 *
 * Public holidays vary between England, Scotland, Wales, and Northern Ireland.
 * Some holidays are UK-wide, while others are observed only in specific regions.
 *
 * Sources: UK Government official holiday information.
 */
class BritishHolidays {

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
        $newYear = new Holiday(new \DateTimeImmutable("$year-01-01"), "New Year's Day");
        $mayDay = new Holiday(new \DateTimeImmutable("$year-05-01"), "Early May Bank Holiday");
        $springBank = new Holiday(new \DateTimeImmutable("last Monday of May $year"), "Spring Bank Holiday");
        $summerBank = new Holiday(new \DateTimeImmutable("last Monday of August $year"), "Summer Bank Holiday");
        $christmas = new Holiday(new \DateTimeImmutable("$year-12-25"), "Christmas Day");
        $boxingDay = new Holiday(new \DateTimeImmutable("$year-12-26"), "Boxing Day");

        // Holidays dependent on Easter:
        $goodFriday = new Holiday((clone $easterDatetime)->sub(new \DateInterval("P2D")), "Good Friday");
        $easterMonday = new Holiday((clone $easterDatetime)->add(new \DateInterval("P1D")), "Easter Monday");

        // Add UK-wide holidays:
        $this->addHoliday($newYear);
        $this->addHoliday($goodFriday);
        $this->addHoliday($easterMonday);
        $this->addHoliday($mayDay);
        $this->addHoliday($springBank);
        $this->addHoliday($summerBank);
        $this->addHoliday($christmas);
        $this->addHoliday($boxingDay);

        // Regional differences:
        if ($regionCode === "GB-SCT") {
            $this->addHoliday(new Holiday(new \DateTimeImmutable("$year-01-02"), "2nd January Holiday"));
            $this->addHoliday(new Holiday(new \DateTimeImmutable("$year-11-30"), "St. Andrew's Day"));
        }
        if ($regionCode === "GB-NIR") {
            $this->addHoliday(new Holiday(new \DateTimeImmutable("$year-03-17"), "St. Patrick's Day"));
            $this->addHoliday(new Holiday(new \DateTimeImmutable("$year-07-12"), "Battle of the Boyne (Orangemen's Day)"));
        }
        if ($regionCode === "GB-WLS") {
            $this->addHoliday(new Holiday(new \DateTimeImmutable("$year-03-01"), "St. David's Day"));
        }
    }

    private function addHoliday(Holiday $holiday) {
        $this->listOfHolidays[$holiday->getDate()->format("Y-m-d")] = $holiday;
    }
}
