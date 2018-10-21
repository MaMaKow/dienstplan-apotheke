<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * Description of class
 *
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class form_element_builder {

    public static function build_checkbox_switch(string $name, bool $checked = FALSE) {
        assert(is_bool($checked));
        if ($checked === FALSE) {
            $checked_string = '';
        }
        if ($checked === TRUE) {
            $checked_string = 'checked';
        }

        $checkbox_switch_html = <<<EOT
<!-- Rectangular switch -->
<label class="switch">
    <input type="checkbox" name="$name" $checked_string>
    <span class="slider"></span>
    <span class="text"></span>
</label>
<!-- /Rectangular switch -->
EOT;
        return $checkbox_switch_html;
    }

}
