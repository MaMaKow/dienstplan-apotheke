<?php

//In the following lines we will define buttons for the use in other documents.

$rückwärts_button_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submitRückwärts'>
			<i class='icon-black'>
				<img src='img/backward.png' class='button-image' alt='Einen Tag rückwärts'>
			</i>
			<br>
			1 Tag rückwärts
		</button>";
$vorwärts_button_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submitVorwärts'>
			<i class='icon-black'>
				<img src='img/foreward.png' class='button-image' alt='Einen Tag vorwärts'>
			</i>
			<br>
			1 Tag vorwärts
		</button>";
$rückwärts_button_week_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submitWocheRückwärts'>
			<i class='icon-black'>
				<img src='img/backward.png' class='button-image' alt='Eine Woche rückwärts'>
			</i>
			<br>
			1 Woche rückwärts
		</button>";
$vorwärts_button_week_img = "
		<button type='submit' class='btn-primary no-print' value='' name='submitWocheVorwärts'>
			<i class='icon-black'>
				<img src='img/foreward.png' class='button-image' alt='Eine Woche vorwärts'>
			</i>
			<br>
			1 Woche vorwärts
		</button>";
$submit_button_img = "
		<button type='submit' id='submit_button_img' class='btn-primary btn-save no-print' value=Absenden name='submitDienstplan'>
		  <i class='icon-white'>
				<img src='img/save.png' class='button-image' alt='Speichern'>
			</i>
			<br>
			Speichern
		</button>";
// TODO: The button should be inactive when the approval already was done.
$submit_approval_button_img = "
		<button type='submit' class='btn-secondary no-print' value='Genehmigen' name='submit_approval'>
			<i class='icon-grey'>
				<img src='img/approve.png' class='button-image' alt='Genehmigen'>
			</i>
			<br>
			Genehmigen
		</button>";
$submit_disapproval_button_img = "
		<button type='submit' class='btn-secondary no-print' value='Ablehnen' name='submit_disapproval'>
			<i class='icon-grey'>
				<img src='img/disapprove.png' class='button-image' alt='Ablehnen'>
			</i>
			<br>
			Ablehnen
		</button>";

/*
 * Build a form to select an employee.
 * 
 * 
 * @param int $employee_id
 * @return string HTML element
 */
function build_select_employee($employee_id) {
    global $Mitarbeiter;
    $text = "\t\t<form method='POST' id='select_employee'>\n";
    $text .= "\t\t\t<select name=employee_id class='no-print large' onChange='document.getElementById(\"submitAuswahlMitarbeiter\").click()'>\n";
    foreach ($Mitarbeiter as $vk => $name) {
        if ($vk == $employee_id) {
            $text .= "\t\t\t\t<option value=$vk selected>" . $vk . " " . $Mitarbeiter[$vk] . "</option>\n";
        } else {
            $text .= "\t\t\t\t<option value=$vk>" . $vk . " " . $Mitarbeiter[$vk] . "</option>\n";
        }
    }
    $text .= "\t\t\t</select>\n";
    $text .= "\t\t\t<input hidden type=submit value=Auswahl name='submitAuswahlMitarbeiter' id='submitAuswahlMitarbeiter' class=no-print>\n";
    $text .= "\t\t</form>\n";
    $text .= "\t\t\t<H1 class='only-print'>" . $Mitarbeiter[$employee_id] . "</H1>\n";
    return $text;
}

/*
 * Build a form to select a branch.
 * 
 * Support for various branch clients.
 * 
 * @param int $mandant
 * @return string HTML element
 */
function build_select_branch($mandant, $date_sql) {
    global $Mandant;
    $text  = "\t\t\t<div id=mandantenformular_div>\n";
    $text .= "\t\t\t<form id=mandantenformular method=post>\n";
    $text .= "\t\t\t\t<input type=hidden name=datum value=" . $date_sql . ">\n";
    $text .= "\t\t\t\t<select class='no-print large' name=mandant onchange=this.form.submit()>\n";
//echo "\t\t\t\t<option value=".$mandant.">".$Mandant[$mandant]."</option>\n";
    foreach ($Mandant as $filiale => $name) {
        if ($filiale != $mandant) {
            $text .= "\t\t\t\t\t<option value=" . $filiale . ">" . $name . "</option>\n";
        } else {
            $text .= "\t\t\t\t\t<option value=" . $filiale . " selected>" . $name . "</option>\n";
        }
    }
    $text .= "\t\t\t\t</select>\n"
            . "\t\t\t</form>\n";
    $text .= "\t\t\t</div>\n";
    return $text;
}
