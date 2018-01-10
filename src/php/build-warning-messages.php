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

/**
 * Build the output of errors and warnings.
 * 
 * We display the errors, which we collected in $Fehlermeldung and $Warnmeldung
 * The errors are assembled in a div "error_container".
 * 
 * @param array $Fehlermeldung An array of strings of errors.
 * @param array $Warnmeldung An array of strings of warnings.
 * 
 * @return string HTML code with error containers.
 */
function build_warning_messages($Fehlermeldung, $Warnmeldung) {
    if (isset($Fehlermeldung) or isset($Warnmeldung)) {
        $text_html = "\t\t\t<div class='error_container no-print'>\n";
        if (isset($Fehlermeldung)) {
            $text_html .= "\t\t\t\t<div class=errormsg>\n";
            foreach ($Fehlermeldung as $fehler) {
                $text_html .= "\t\t\t\t\t<H1>" . $fehler . "</H1>\n";
            }
            $text_html .= "\t\t\t\t</div>\n";
        }
        if (isset($Warnmeldung)) {
            $text_html .= "\t\t\t\t<div class=warningmsg>\n";
            foreach ($Warnmeldung as $warnung) {
                $text_html .= "\t\t\t\t\t<H2>" . $warnung . "</H2>\n";
            }
            $text_html .= "\t\t\t\t</div>\n";
        }
        $text_html .= "\t\t\t</div>\n";
        if (!empty($text_html)) {
            return $text_html;
        } else {
            return FALSE;
        }
    }
    return "";
}
