<?php

/*
 * Copyright (C) 2017 Mandelkow
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

/* Die Konfiguration könnte über die Datei default.php eingelesen werden. */
$config = array(
    'database_name' => "Apotheke", //Name der MySQL-Datenbank
    'database_user' => "apotheke", //Name des Datenbankbenutzers
    'application_name' => "Dienstplan Apotheke", //Für den Title des HTML HEAD
    'LC_TIME' => "de_DE.utf8",
    'mb_internal_encoding' => "UTF-8",
    'error_reporting' => E_ALL,
    'hide_disapproved' => false
);


//Diese Datei kann später mit folgendem Befehl neu geschrieben werden:
//	file_put_contents('config/config.php', '<?php  $config =' . var_export($config, true) . ';');

