<?php

/*
 * Copyright (C) 2024 Mandelkow
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
 * Description of DateTimeUtility
 *
 * @author Mandelkow
 */
abstract class DateTimeUtility {

    /**
     * Convert time string to hours using DateTime class
     *
     * @param string $timeString
     * @return float time in hours
     */
    public static function timeFromTextToFloat(string $timeString) {
        $time = \DateTime::createFromFormat('H:i:s', $timeString);
        $hour = (float) $time->format('H');
        $minute = (float) $time->format('i');
        $second = (float) $time->format('s');
        $time_float = $hour + $minute / 60 + $second / 3600;
        return $time_float;
    }

    public static function formatReadableDateObject(\DateTime $dateObject): string {
        $configuration = new \PDR\Application\configuration();
        $formatter = new \IntlDateFormatter($configuration->getLC_TIME(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
        $formatter->setPattern('dd.MM.yyyy');
        return $formatter->format($dateObject);
    }
}
