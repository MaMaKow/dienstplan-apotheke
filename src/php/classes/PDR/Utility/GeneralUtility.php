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

namespace PDR\Utility;

/**
 * General utility functions
 *
 * @author Mandelkow
 */
abstract class GeneralUtility {

    /**
     * Test if PHP is running on a Windows machine.
     *
     * @return boolean True if Operating system is Windows.
     */
    public static function runningOnWindows() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }
        return false;
    }
}
