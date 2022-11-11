<?php

/*
 * Copyright (C) 2021 Mandelkow
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
define('PDR_FILE_SYSTEM_APPLICATION_PATH', dirname(__DIR__) . '/');
$sql_files = glob(PDR_FILE_SYSTEM_APPLICATION_PATH . "src/sql/*.sql");
$text_to_be_hashed = "";
foreach ($sql_files as $sql_file_name) {
    echo "Adding content of file " . basename($sql_file_name) . " to the database hash.\n";
    $text_to_be_hashed .= file_get_contents($sql_file_name);
}
$database_hash = sha1($text_to_be_hashed);
$database_version_hash_text = '<?php ' . PHP_EOL . PHP_EOL . "const PDR_DATABASE_VERSION_HASH = '" . $database_hash . "';" . PHP_EOL;
file_put_contents(PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/database_version_hash.php', $database_version_hash_text);
