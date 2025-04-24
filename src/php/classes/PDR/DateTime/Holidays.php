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

class Holidays {

    /**
     * @var array $Holidays is an array in the format array(string (date in Y-m-d) => PDR\DateTime\Holiday)
     */
    private $holidays = array();

    /**
     * @var int $year the year, which the unix timestamps in the $holidays variable refer to.
     */
    private $year;

    public function __construct(int $year) {
        $configuration = new \PDR\Application\configuration();
        $countryCode = $configuration->getCountryCode();
        $stateCode = $configuration->getStateCode();
        $this->holidays = $this->getHolidays($year, $countryCode, $stateCode);
        $this->year = $year;
    }

    /**
     * Returns an associative array of holidays, keyed by date (Y-m-d).
     *
     * @param int $year An integer representing the holiday year.
     * @param string $country ISO 3166 ALPHA-2 country code (e.g. "DE").
     * @param string $state ISO 3166-2 state code (e.g. "DE-MV").
     * @return array<string, PDR\DateTime\Holiday>
     */
    private function getHolidays(int $year, string $country = "DE", string $state = "DE-MV"): array {
        switch ($country) {
            case "DE":
                $germanHolidaysProvider = new \PDR\DateTime\GermanHolidays($year, $state);
                $holidays = $germanHolidaysProvider->getListOfHolidays();
                break;
            case "FR":
                $frenchHolidaysProvider = new \PDR\DateTime\FrenchHolidays($year, $state);
                $holidays = $frenchHolidaysProvider->getListOfHolidays();
                break;
            case "EN":
                $britishHolidaysProvider = new \PDR\DateTime\BritishHolidays($year, $state);
                $holidays = $britishHolidaysProvider->getListOfHolidays();
                break;
            default:
                throw new \Exception("This country is not supported yet.");
        }
        return $holidays;
    }

    /**
     * Test if a day is a holiday.
     *
     * This function returns FALSE if a day is not a holiday.
     * This function returns TRUE on a holiday.
     * @param DateTime $dateObject
     *
     * @return boolean.
     */
    public function isHoliday(\DateTime $dateObject): bool {
        if (intval($dateObject->format('Y')) !== $this->year) {
            throw new \Exception("Date is not within the loaded holiday year.");
        }
        return isset($this->holidays[$dateObject->format('Y-m-d')]);
    }

    public function getHolidayOnDate(\DateTime $dateObject): \PDR\DateTime\Holiday {
        if (intval($dateObject->format('Y')) !== $this->year) {
            throw new \Exception("Date is not within the loaded holiday year.");
        }
        $dateString = $dateObject->format('Y-m-d');
        if (isset($this->holidays[$dateString])) {
            return $this->holidays[$dateString];
        }
        throw new \Exception("There is no holiday found on this date.");
    }
}
