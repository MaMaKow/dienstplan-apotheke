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

namespace PDR\Application\Installation;

/**
 * Description of installUtility
 *
 * @author Mandelkow
 */
class InstallUtility {

    private static $errorMessages;
    private $pdrFileSystemApplicationPath;
    private $pdrServerApplicationPath;

    public function __construct() {
        $this->pdrFileSystemApplicationPath = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . "/";
        $folderTreeDepthInChars = \strlen(\substr(\getcwd(), \strlen(__DIR__)));
        $this->pdrServerApplicationPath = \dirname(\dirname(\dirname(\substr(\dirname($_SERVER["SCRIPT_NAME"]), 0, \strlen(\dirname($_SERVER["SCRIPT_NAME"])) - $folderTreeDepthInChars)))) . "/";

        /**
         * Define an autoloader:
         */
        spl_autoload_register(function ($className) {
            $baseDir = $this->pdrFileSystemApplicationPath . '/src/php/classes/';
            $file = $baseDir . 'class.' . $className . '.php';
            if (file_exists($file)) {
                include_once $file;
            }
            /**
             * <p lang="de">
             * Wir wollen die Files der Klassen besser sortieren.
             * Der Autoloader muss so lange bis das abgeschlossen ist, beide Varianten beherrschen.
             * </p>
             */
            $file = $baseDir . str_replace('\\', '/', $className) . '.php';
            if (file_exists($file)) {
                include_once $file;
            }
        });

        self::$errorMessages = array();
    }

    public function getPdrFileSystemApplicationPath(): string {
        return $this->pdrFileSystemApplicationPath;
    }

    public function getPdrServerApplicationPath(): string {
        return $this->pdrServerApplicationPath;
    }

    public function addErrorMessage(string $message): void {
        self::$errorMessages[] = $message;
    }

    public function hasErrorMessages(): bool {
        if (empty(self::$errorMessages)) {
            return FALSE;
        }
        return TRUE;
    }

    public function buildErrorMessageDiv(): string {
        if (empty(self::$errorMessages)) {
            return FALSE;
        }
        $text_html = "<div id='error_message_div'>\n";
        foreach (self::$errorMessages as $errorMessage) {
            $text_html .= "<p>" . $errorMessage . "</p>\n";
        }
        $text_html .= "</div>\n";
        self::$errorMessages = array(); //Unsetting makes it possible to refill the array and build the new contents in another place.
        return $text_html;
    }

    public static function fancyImplode(array $inputArray, string $delimiter = ", "): string {
        /*
         * This also works for just one element in the array:
         */
        $last = array_pop($inputArray);
        return count($inputArray) ? implode($delimiter, $inputArray) . " " . gettext("and") . " " . $last : $last;
    }
}
