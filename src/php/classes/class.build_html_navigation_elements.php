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
 * This class contains functions needed to build various navigation elements, e.g. buttons and selects.
 *
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
abstract class build_html_navigation_elements {

    static $List_of_allowed_input_names = array(
        'employee_id',
        'datum',
    );

    private static function build_referrer_from_array($Name_value_array) {
        if (NULL !== $Name_value_array) {
            $referrer = '?';
            foreach ($Name_value_array as $name => $value) {
                if (in_array($name, self::$List_of_allowed_input_names)) {
                    $referrer .= $name . '=' . htmlentities($value) . '&';
                }
            }
            return rtrim($referrer, '&');
        }
        return FALSE;
    }

    public static function build_button_open_readonly_version($url, $Name_value_array) {
        $referrer = self::build_referrer_from_array($Name_value_array);
        $button_img = "<form class='inline_form' action='" . PDR_HTTP_SERVER_APPLICATION_PATH . $url . $referrer . "' method='get'>
		<button type='submit' class='btn-primary no_print'>
			<i class='icon-black'>
				<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/read-icon.svg' class='button-image' alt='" . gettext("Read") . "'>
			</i>
			<br>
			" . gettext("Read") . "
		</button>
            </form>\n";
        return $button_img;
    }

    public static function build_button_open_edit_version($url, $Name_value_array) {
        $referrer = self::build_referrer_from_array($Name_value_array);
        $button_img = "
            <form class='inline_form' action='" . PDR_HTTP_SERVER_APPLICATION_PATH . $url . $referrer . "' method='get'>
		<button type='submit' class='btn-primary no_print'>
			<i class='icon-black'>
				<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/edit-icon.svg' class='button-image' alt='" . gettext("Read") . "'>
			</i>
			<br>
			" . gettext("Edit") . "
		</button>
            </form>";
        return $button_img;
    }

    public static function build_button_day_backward($date_unix) {
        $yesterday_date_string = general_calculations::yesterday_date_string($date_unix);
        $backward_button_img = "
            <form class='inline_form' id='button_day_backward_form'>
		<button type='submit' class='btn-primary no_print' value='$yesterday_date_string' name='datum' id='button_day_backward' title='" . gettext('Ctrl + &#8678;') . "'>
			<i class='icon-black'>
				<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/backward.png' class='button-image' alt='" . gettext("1 day backward") . "'>
			</i>
			<br>
			" . gettext("1 day backward") . "
		</button>
            </form>";
        return $backward_button_img;
    }

    public static function build_button_day_forward($date_unix) {
        $tomorow_date_string = general_calculations::tomorow_date_string($date_unix);
        $forward_button_img = "
            <form class='inline_form' id='button_day_forward_form'>
		<button type='submit' class='btn-primary no_print' value='$tomorow_date_string' name='datum' id='button_day_forward' title='" . gettext('Ctrl + &#8680;') . "'>
			<i class='icon-black'>
				<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/forward.png' class='button-image' alt='" . gettext("1 day forward") . "'>
			</i>
			<br>
			" . gettext("1 day forward") . "
		</button>
            </form>";
        return $forward_button_img;
    }

    public static function build_button_week_backward($date_sql) {
        $date_last_week_sql = general_calculations::get_first_day_of_week(date('Y-m-d', strtotime('last week', strtotime($date_sql))));
        $backward_button_week_img = "
            <form class='inline_form'>
		<button type='submit' class='btn-primary no_print' value='$date_last_week_sql' name='datum' id='button_week_backward' title='" . gettext('Ctrl + &larr;') . "'>
			<i class='icon-black'>
				<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/backward.png' class='button-image' alt='" . gettext("1 week backward") . "'>
			</i>
			<br>
			" . gettext("1 week backward") . "
		</button>
            </form>";
        return $backward_button_week_img;
    }

    public static function build_button_week_forward($date_sql) {
        $date_next_week_sql = general_calculations::get_first_day_of_week(date('Y-m-d', strtotime('next week', strtotime($date_sql))));
        $forward_button_week_img = "
            <form class='inline_form'>
		<button type='submit' class='btn-primary no_print' value='$date_next_week_sql' name='datum' id='button_week_forward' title='" . gettext('Ctrl + &rarr;') . "'>
			<i class='icon-black'>
				<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/forward.png' class='button-image' alt='" . gettext("1 week forward") . "'>
			</i>
			<br>
			" . gettext("1 week forward") . "
		</button>
            </form>";
        return $forward_button_week_img;
    }

    public static function build_button_link_download_ics_file(string $date_sql, int $employee_id) {
        $button_html = "<form class='inline_form' action='" . PDR_HTTP_SERVER_APPLICATION_PATH . "webdav.php' method='get' id='download_ics_file_form'>"
                . "<input type='hidden' name='date_string' value='$date_sql' form='download_ics_file_form'>"
                . "<input type='hidden' name='employee_id' value='$employee_id' form='download_ics_file_form'>"
                . " <button type='submit' class='btn-primary no_print' "
                . " title='" . gettext("Download iCalendar file") . "'>"
                . " <img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/download.png' style='width:32px' alt='Download ics Kalender Datei'>"
                . " <br>"
                . gettext("iCalendar File")
                . " </button></form>\n";
        return $button_html;
    }

    public static function build_button_submit($form_id) {
        global $session;
        if (!$session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) {
            return NULL;
        }
        $submit_button = "
        <button type='submit' id='submit_button' class='btn-primary btn-save no_print' value=Absenden name='submit_roster' form='$form_id'>
                <i class='icon-white'>
                <img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/save.png' class='button-image' alt='" . gettext("Save") . "' >
                </i>
                <br>
                " . gettext("Save") . "
                </button>";
        return $submit_button;
    }

    public static function build_button_back() {
        $back_button = "
        <button id='back_button' class='btn-primary btn-save no_print' onClick='javascript:history.back()'>
                <i class='icon-white'>
                <img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/backward.png' class='button-image' alt='" . gettext("Back") . "' >
                </i>
                <br>
                " . gettext("Back") . "
                </button>";
        return $back_button;
    }

    public static function build_button_approval($approval) {
        if ('approved' === $approval) {
            $disabled = 'disabled';
        } else {
            $disabled = '';
        }
        $submit_approval_button_img = "
        <form method=post class='inline_form'>
                <button type='submit' class='btn-secondary no_print' value='approve' name='submit_approval' $disabled>
                <i class='icon-grey'>
                <img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/approve.png' class='button-image' alt='" . gettext('Approve') . "' >
                </i>
                <br>
                " . gettext('Approve') . "
                </button>
                </form>
                ";
        return $submit_approval_button_img;
    }

    public static function build_button_disapproval($approval) {
        if ('disapproved' === $approval) {
            $disabled = 'disabled';
        } else {
            $disabled = '';
        }
        $submit_disapproval_button_img = "
        <form method=post class='inline_form'>
                <button type='submit' class='btn-secondary no_print' value='disapprove' name='submit_disapproval' $disabled>
                <i class='icon-grey'>
                <img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/disapprove.png' class='button-image' alt='" . gettext("Disapprove") . "' >
                </i>
                <br>
                " . gettext("Disapprove") . "
                </button>
                </form>";
        return $submit_disapproval_button_img;
    }

    /**
     * Build a form to select an employee.
     *
     *
     * @param int $employee_id
     * @return string HTML element
     */
    public static function build_select_employee($employee_id, $Employee_object_list) {
        $text = "<!-- employee select form-->\n";
        $text .= "<form method='POST' id='select_employee' class='inline_form'>\n";
        $text .= "<select name=employee_id class='large' onChange='document.getElementById(\"submit_select_employee\").click()'>\n";
        foreach ($Employee_object_list as $employee_object) {
            if ($employee_object->employee_id == $employee_id) {
                $text .= "<option value=$employee_object->employee_id selected>" . $employee_object->employee_id . " " . $employee_object->last_name . "</option>\n";
            } else {
                $text .= "<option value=$employee_object->employee_id>" . $employee_object->employee_id . " " . $employee_object->last_name . "</option>\n";
            }
        }
        $text .= "</select>\n";
        $text .= "<input hidden type=submit value=select_employee name='submit_select_employee' id='submit_select_employee' class=no_print>\n";
        $text .= "</form>\n";
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

    public static function build_select_branch(int $current_branch_id, string $date_sql = NULL) {
        /*
         * TODO: Is it possible to leave out the date_sql?
         * Branch management will send NULL. Does this interrupt any cookies?
         */
        $List_of_branch_objects = branch::get_list_of_branch_objects();
        if (1 === count($List_of_branch_objects)) {
            return FALSE;
        }
        $text = "<!-- branch select form-->\n";
        $text .= "<div id='branch_form_div' class='inline_element'>\n";
        $text .= "<form id=branch_form method=post>\n";
        $text .= "<input type=hidden name=datum value=" . $date_sql . ">\n";
        $text .= "<select id=branch_form_select class='large' name=mandant onchange=this.form.submit()>\n";
        foreach ($List_of_branch_objects as $branch_id => $branch_object) {
            if ($branch_id != $current_branch_id) {
                $text .= "<option value=" . $branch_id . ">" . $branch_object->name . "</option>\n";
            } else {
                $text .= "<option value=" . $branch_id . " selected>" . $branch_object->name . "</option>\n";
            }
        }
        $text .= "</select>\n"
                . " </form>\n";
        $text .= "</div>\n";
        $text .= "<!--/branch select form-->\n";
        return $text;
    }

    public static function build_select_weekday($weekday_selected) {
        $Weekday_names = build_html_navigation_elements::get_weekday_names();

        $html = '';
        $html .= "<form id='week_day_form' method=post>";
        $html .= "<select class='large' name=weekday onchange=this.form.submit()>\n";
        foreach ($Weekday_names as $weekday_temp => $weekday_name) {
            if ($weekday_temp != $weekday_selected) {
                $html .= "<option value='$weekday_temp'>$weekday_name</option>\n";
            } else {
                $html .= "<option value='$weekday_temp' selected>$weekday_name</option>\n";
            }
        }
        $html .= "</select></form>\n";
        return $html;
    }

    public static function build_button_show_principle_roster_history(int $alternating_week_id, int $employee_id, int $weekday, int $branch_id, DateTime $date_object) {
        $List_of_history_dates = principle_roster_history::get_list_of_history_dates($weekday, $alternating_week_id, $branch_id);
        if (2 > count($List_of_history_dates)) {
            return NULL;
        }
        $class_string = '';
        $title_string = '';
        if ($date_object < max($List_of_history_dates)) {
            $class_string = ' attention ';
            $title_string = sprintf(
                    gettext("Dieser Grundplan gilt ab dem %2\$s.\nDies ist nicht die jüngste Version des Grundplanes.\nDer aktuellste Grundplan beginnt am %1\$s."), max($List_of_history_dates)->format('d.m.Y'), $date_object->format('d.m.Y'));
        }
        $button_img = "<form class='inline_form' action='../fragments/fragment.principle-roster-day-history.php' method='post' id='show_principle_roster_history'>
            <input type='hidden' form='show_principle_roster_history' name='alternating_week_id' value=$alternating_week_id>
            <input type='hidden' form='show_principle_roster_history' name='employee_id' value=$employee_id>
            <input type='hidden' form='show_principle_roster_history' name='weekday' value=$weekday>
            <input type='hidden' form='show_principle_roster_history' name='branch_id' value=$branch_id>
		<button type='submit' class='btn-primary no_print $class_string' title='$title_string'>
			<i>
				<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/history.svg' class='button-image' alt='" . gettext("principle roster history") . "'>
			</i>
			<br>
			" . gettext("History") . "
		</button>
            </form>\n";
        return $button_img;
    }

    public static function build_button_principle_roster_delete($alternating_week_id) {
        global $session;
        if (!$session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) {
            return NULL;
        }
        if (!alternating_week::alternations_exist()) {
            return NULL;
        }
        $button_img = "<form class='inline_form' action='' method='post' id='principle_roster_delete'>
            <input type='hidden' form='principle_roster_delete' name='principle_roster_delete' value=$alternating_week_id>
		<button type='submit' class='btn-primary no_print'>
			<i>
				<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/delete.svg' class='button-image' alt='" . gettext("Delete") . "'>
			</i>
			<br>
			" . gettext("Delete") . "
		</button>
            </form>\n";
        return $button_img;
    }

    public static function build_button_principle_roster_copy($alternating_week_id) {
        global $session;
        if (!$session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) {
            return NULL;
        }
        $button_img = "<form class='inline_form' action='' method='post' id='principle_roster_copy_form'>
            <input type='hidden' form='principle_roster_copy_form' name='principle_roster_copy_from' value=$alternating_week_id>
		<button type='submit' class='btn-primary no_print'>
			<i>
				<img src='" . PDR_HTTP_SERVER_APPLICATION_PATH . "img/copy.svg' class='button-image' alt='" . gettext("Copy") . "'>
			</i>
			<br>
			" . gettext("Copy") . "
		</button>
            </form>\n";
        return $button_img;
    }

    public static function build_select_alternating_week(int $alternating_week_id, int $weekday, DateTime $date = NULL) {
        if (!alternating_week::alternations_exist()) {
            return NULL;
        }
        $html = '';
        $html .= "<form id='alternating_week_form' method=post>";
        $html .= "<select class='large' name=alternating_week_id onchange=this.form.submit()>\n";
        $Alternating_week_ids = alternating_week::get_alternating_week_ids();
        foreach ($Alternating_week_ids as $alternating_week_id_current) {
            $alternating_week = new alternating_week($alternating_week_id_current);
            $example_monday = $alternating_week->get_monday_date_for_alternating_week($date);
            $example_date = clone $example_monday;
            if ($weekday > 1) {
                $example_date = $example_monday->add(new DateInterval('P' . ($weekday - 1) . 'D'));
            }
            $alternating_week_id_string = alternating_week::get_human_readably_string($alternating_week_id_current) . ': ' . $example_date->format('d.m.Y');
            if ($alternating_week_id != $alternating_week_id_current) {
                $html .= "<option value='$alternating_week_id_current'>$alternating_week_id_string</option>\n";
            } else {
                $html .= "<option value='$alternating_week_id_current' selected>$alternating_week_id_string</option>\n";
            }
        }
        $html .= "</select></form>\n";
        return $html;
    }

    public static function get_weekday_names() {
        /*
         * TODO: Move this function to somewhere more general!
         * It should go into the localization class, once that it exists.
         */
        $Weekday_names = array(
            1 => gettext('Monday'),
            2 => gettext('Tuesday'),
            3 => gettext('Wednesday'),
            4 => gettext('Thursday'),
            5 => gettext('Friday'),
            6 => gettext('Saturday'),
            7 => gettext('Sunday'),
        );
        return $Weekday_names;
    }

    public static function build_input_date($date_sql) {
        $text = "";
        $text .= "<div id=date_chooser_div>\n";
        $text .= "<form id=date_chooser_form method=post>\n";
        $text .= "<input name=datum type=date id=date_chooser_input class='datepicker' value='$date_sql' onblur='this.form.submit()'>\n";
        $text .= "<input type=submit name=tagesAuswahl value=Anzeigen>\n";
        $text .= "</form>\n";
        $text .= "</div>\n";
        return $text;
    }

}
