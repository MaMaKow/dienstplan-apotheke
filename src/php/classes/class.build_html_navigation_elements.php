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
abstract class build_html_navigation_elements {

    public static function build_button_day_backward($date_sql) {
        $backward_button_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submit_day_backward'>
			<i class='icon-black'>
				<img src='img/backward.png' class='button-image' alt='" . gettext("1 day backward") . "'>
			</i>
			<br>
			" . gettext("1 day backward") . "
		</button>";
        return $backward_button_img;
    }

    public static function build_button_day_forward($date_sql) {
        $forward_button_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submit_day_forward'>
			<i class='icon-black'>
				<img src='img/foreward.png' class='button-image' alt='" . gettext("1 day forward") . "'>
			</i>
			<br>
			" . gettext("1 day forward") . "
		</button>";
        return $forward_button_img;
    }

    public static function build_button_week_backward($date_sql) {
        $date_last_week_sql = general_calculations::get_first_day_of_week(date('Y-m-d', strtotime('last week', strtotime($date_sql))));
        $backward_button_week_img = "
            <form id=button_week_backward_form></form>
		<button type='submit' class='btn-primary no-print' value='$date_last_week_sql' name='datum' form='button_week_backward_form'>
			<i class='icon-black'>
				<img src='img/backward.png' class='button-image' alt='" . gettext("1 week backward") . "'>
			</i>
			<br>
			" . gettext("1 week backward") . "
		</button>";
        return $backward_button_week_img;
    }

    public static function build_button_week_forward($date_sql) {
        $date_next_week_sql = general_calculations::get_first_day_of_week(date('Y-m-d', strtotime('next week', strtotime($date_sql))));
        $forward_button_week_img = "
            <form id=button_week_forward_form></form>
		<button type='submit' class='btn-primary no-print' value='$date_next_week_sql' name='datum' form='button_week_forward_form'>
			<i class='icon-black'>
				<img src='img/foreward.png' class='button-image' alt='" . gettext("1 week forward") . "'>
			</i>
			<br>
			" . gettext("1 week forward") . "
		</button>";
        return $forward_button_week_img;
    }

    public static function build_button_submit() {
        $submit_button_img = "
		<button type='submit' id='submit_button_img' class='btn-primary btn-save no-print' value=Absenden name='submit_roster'>
		  <i class='icon-white'>
				<img src='img/save.png' class='button-image' alt='" . gettext("Save") . "'>
			</i>
			<br>
			" . gettext("Save") . "
		</button>";
        return $submit_button_img;
    }

    public static function build_button_approval() {
// TODO: The button should be inactive when the approval already was done.
        $submit_approval_button_img = "
		<button type='submit' class='btn-secondary no-print' value='approve' name='submit_approval'>
			<i class='icon-grey'>
				<img src='img/approve.png' class='button-image' alt='" . gettext("1 day forward") . "'>
			</i>
			<br>
			" . gettext("Approve") . "
		</button>";
    }

    public static function build_button_disapproval() {
        $submit_disapproval_button_img = "
		<button type='submit' class='btn-secondary no-print' value='disapprove' name='submit_disapproval'>
			<i class='icon-grey'>
				<img src='img/disapprove.png' class='button-image' alt='" . gettext("Disapprove") . "'>
			</i>
			<br>
			" . gettext("Disapprove") . "
		</button>";
        return $submit_disapproval_button_img;
    }

    /*
     * Build a form to select an employee.
     *
     *
     * @param int $employee_id
     * @return string HTML element
     */

    public static function build_select_employee($employee_id, $Employee_id_list) {
        $text = "<!--employee select form-->\n";
        $text .= "\t\t<form method='POST' id='select_employee'>\n";
        $text .= "\t\t\t<select name=employee_id class='no-print large' onChange='document.getElementById(\"submit_select_employee\").click()'>\n";
        foreach ($Employee_id_list as $vk => $employee_last_name) {
            if ($vk == $employee_id) {
                $text .= "\t\t\t\t<option value=$vk selected>" . $vk . " " . $employee_last_name . "</option>\n";
            } else {
                $text .= "\t\t\t\t<option value=$vk>" . $vk . " " . $employee_last_name . "</option>\n";
            }
        }
        $text .= "\t\t\t</select>\n";
        $text .= "\t\t\t<input hidden type=submit value=select_employee name='submit_select_employee' id='submit_select_employee' class=no-print>\n";
        $text .= "\t\t</form>\n";
        $text .= "\t\t\t<H1 class='only-print'>" . $Employee_id_list[$employee_id] . "</H1>\n";
        $text .= "<!--/employee select form-->\n";

        return $text;
    }

    /*
     * Build a form to select a branch.
     *
     * Support for various branch clients.
     *
     * @param int $current_branch
     * @return string HTML element
     */

    public static function build_select_branch($current_branch_id, $date_sql) {
        $current_branch_id = (int) $current_branch_id;
        global $List_of_branch_objects;
        $text = "<!--branch select form-->\n";
        $text .= "\t\t\t<div id=mandantenformular_div>\n";
        $text .= "\t\t\t<form id=mandantenformular method=post>\n";
        $text .= "\t\t\t\t<input type=hidden name=datum value=" . $date_sql . ">\n";
        $text .= "\t\t\t\t<select id=branch_form_select class='no-print large' name=mandant onchange=this.form.submit()>\n";
        foreach ($List_of_branch_objects as $branch_id => $branch_object) {
            if ($branch_id != $current_branch_id) {
                $text .= "\t\t\t\t\t<option value=" . $branch_id . ">" . $branch_object->name . "</option>\n";
            } else {
                $text .= "\t\t\t\t\t<option value=" . $branch_id . " selected>" . $branch_object->name . "</option>\n";
            }
        }
        $text .= "\t\t\t\t</select>\n"
                . "\t\t\t</form>\n";
        $text .= "\t\t\t</div>\n";
        $text .= "<!--/branch select form-->\n";
        return $text;
    }

}
