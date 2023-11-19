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
 * The purpose of this file is to be called in the background to do stuff once in a while.
 * It is called upon login() of any user for example.
 * It is the responsibility of the classes to check if there is work to do and how much.
 */
chdir(dirname(__DIR__, 2));
require_once 'bootstrap.php';
new update_database();
new maintenance();
//new auto_upgrader();
