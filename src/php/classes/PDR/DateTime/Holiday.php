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
 * Class Holiday
 *
 * Represents a holiday with a specific date, name, and a flag indicating whether
 * it is considered a public holiday.
 *
 * This class encapsulates the details of a holiday, including its date,
 * the name of the holiday, and whether it is recognized as a public holiday.
 *
 * @package PDR\DateTime
 * @author Mandelkow
 */
class Holiday {

    /**
     * Constructs a new Holiday instance.
     *
     * @param \DateTimeInterface $date              The date of the holiday.
     * @param string             $name              The name of the holiday.
     * @param bool               $is_public_holiday Optional. Whether the holiday is a public holiday. Defaults to true.
     */
    public function __construct(
            private \DateTimeInterface $date,
            private string $name,
            private bool $is_public_holiday = true,
    ) {

    }

    /**
     * Returns the name of the holiday.
     *
     * @return string The holiday's name.
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Returns the date of the holiday.
     *
     * @return \DateTimeInterface The date of the holiday.
     */
    public function getDate(): \DateTimeInterface {
        return $this->date;
    }

    /**
     * Checks whether the holiday is a public holiday.
     *
     * @return bool True if the holiday is a public holiday, false otherwise.
     */
    public function isPublicHoliday(): bool {
        return $this->is_public_holiday;
    }
}
