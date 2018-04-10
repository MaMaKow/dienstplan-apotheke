<?php

//In the following lines we will define buttons for the use in other documents.

$backward_button_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submit_day_backward'>
			<i class='icon-black'>
				<img src='img/backward.png' class='button-image' alt='" . gettext("1 day backward") . "'>
			</i>
			<br>
			" . gettext("1 day backward") . "
		</button>";
$forward_button_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submit_day_forward'>
			<i class='icon-black'>
				<img src='img/foreward.png' class='button-image' alt='" . gettext("1 day forward") . "'>
			</i>
			<br>
			" . gettext("1 day forward") . "
		</button>";
$backward_button_week_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submit_week_backward'>
			<i class='icon-black'>
				<img src='img/backward.png' class='button-image' alt='" . gettext("1 week backward") . "'>
			</i>
			<br>
			" . gettext("1 week backward") . "
		</button>";
$forward_button_week_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submit_week_forward'>
			<i class='icon-black'>
				<img src='img/foreward.png' class='button-image' alt='" . gettext("1 week forward") . "'>
			</i>
			<br>
			" . gettext("1 week forward") . "
		</button>";
$submit_button_img = "
		<button type='submit' id='submit_button_img' class='btn-primary btn-save no-print' value=Absenden name='submit_roster'>
		  <i class='icon-white'>
				<img src='img/save.png' class='button-image' alt='" . gettext("Save") . "'>
			</i>
			<br>
			" . gettext("Save") . "
		</button>";
// TODO: The button should be inactive when the approval already was done.
$submit_approval_button_img = "
		<button type='submit' class='btn-secondary no-print' value='approve' name='submit_approval'>
			<i class='icon-grey'>
				<img src='img/approve.png' class='button-image' alt='" . gettext("1 day forward") . "'>
			</i>
			<br>
			" . gettext("Approve") . "
		</button>";
$submit_disapproval_button_img = "
		<button type='submit' class='btn-secondary no-print' value='disapprove' name='submit_disapproval'>
			<i class='icon-grey'>
				<img src='img/disapprove.png' class='button-image' alt='" . gettext("Disapprove") . "'>
			</i>
			<br>
			" . gettext("Disapprove") . "
		</button>";

/*
 * Build a form to select an employee.
 *
 *
 * @param int $employee_id
 * @return string HTML element
 */

function build_select_employee($employee_id, $Employee_id_list) {
    $text = "<!--employee select form-->\n";
    $text .= "<form method='POST' id='select_employee'>\n";
    $text .= "<select name=employee_id class='no-print large' onChange='document.getElementById(\"submit_select_employee\").click()'>\n";
    foreach ($Employee_id_list as $vk => $employee_last_name) {
        if ($vk == $employee_id) {
            $text .= "<option value=$vk selected>" . $vk . " " . $employee_last_name . "</option>\n";
        } else {
            $text .= "<option value=$vk>" . $vk . " " . $employee_last_name . "</option>\n";
        }
    }
    $text .= "</select>\n";
    $text .= "<input hidden type=submit value=select_employee name='submit_select_employee' id='submit_select_employee' class=no-print>\n";
    $text .= "</form>\n";
    $text .= "<H1 class='only-print'>" . $Employee_id_list[$employee_id] . "</H1>\n";
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

function build_select_branch($current_branch_id, $date_sql) {
    $current_branch_id = (int) $current_branch_id;
    global $List_of_branch_objects;
    $text = "<!--branch select form-->\n";
    $text .= "<div id=mandantenformular_div>\n";
    $text .= "<form id=mandantenformular method=post>\n";
    $text .= "<input type=hidden name=datum value=" . $date_sql . ">\n";
    $text .= "<select id=branch_form_select class='no-print large' name=mandant onchange=this.form.submit()>\n";
    foreach ($List_of_branch_objects as $branch_id => $branch_object) {
        if ($branch_id != $current_branch_id) {
            $text .= "<option value=" . $branch_id . ">" . $branch_object->name . "</option>\n";
        } else {
            $text .= "<option value=" . $branch_id . " selected>" . $branch_object->name . "</option>\n";
        }
    }
    $text .= "</select>\n"
            . "</form>\n";
    $text .= "</div>\n";
    $text .= "<!--/branch select form-->\n";
    return $text;
}
