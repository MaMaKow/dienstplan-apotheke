<?php

/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * A PDRRosterLogicException is a situation where the data stored in a roster is not fitting the real word.
 *   This might be the case if an employee starts working after the end of work (duty_start > duty_end).
 *   That is a possible case in the input data. And it is possible to store such values in the database.
 *   But it is not possible to end something before it was started.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class PDRRosterLogicException extends Exception {
    /*
     * This space is intentionally left blank.
     */
}
