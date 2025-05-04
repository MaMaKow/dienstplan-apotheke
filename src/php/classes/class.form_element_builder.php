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
 * Description of class
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class form_element_builder {

    public static function build_checkbox_switch(string $form_id, string $name, bool $checked = FALSE) {
        /*
         * TODO: This should probably better be a radio instead of a checkbox?
         *     In some way we have to make sure, that the same name is not used multiple times,..
         *     ... perhaps we have to add a value too.
         */
        assert(is_bool($checked));
        if ($checked === FALSE) {
            $checked_string = '';
        }
        if ($checked === TRUE) {
            $checked_string = 'checked="checked"';
        }

        $checkbox_switch_html = <<<EOT
<!-- Rectangular switch -->
<label class="switch">
    <input type="checkbox" form="$form_id" name="$name" $checked_string onchange="auto_submit_form(this.form)" class="auto-submit">
    <span class="slider"></span>
    <span class="text"></span>
</label>
<!-- /Rectangular switch -->
EOT;
        return $checkbox_switch_html;
    }

    /**
     *
     * @param int $current_year
     * @return string
     */
    public static function build_html_select_year(int $current_year) {

        $Years = \PDR\Utility\AbsenceUtility::getRosteringYears();
        $html_select_year = "";
        $html_select_year .= "<form id='select_year' class='inline-form' method=post>";
        $html_select_year .= "<select name=year class='large' onchange=this.form.submit()>";
        foreach ($Years as $year_number) {
            $html_select_year .= "<option value='$year_number'";
            if ($year_number == $current_year) {
                $html_select_year .= " SELECTED ";
            }
            $html_select_year .= ">$year_number</option>\n";
        }
        $html_select_year .= "</select>";
        $html_select_year .= "</form>";
        return $html_select_year;
    }

    /**
     *
     * @param int $current_month
     * @return string
     */
    public static function build_html_select_month(int $current_month) {
        $Months = localization::get_month_names();
        $html_select_month = "";
        $html_select_month .= "<form id='select_month' class='inline-form' method=post>";
        $html_select_month .= "<select name=month_number class='large' onchange=this.form.submit()>";
        foreach ($Months as $month_number => $month_name) {
            $html_select_month .= "<option value='$month_number'";
            if ($month_number == $current_month) {
                $html_select_month .= " SELECTED ";
            }
            $html_select_month .= ">$month_name</option>\n";
        }
        $html_select_month .= "</select>";
        $html_select_month .= "</form>";
        return $html_select_month;
    }
}
