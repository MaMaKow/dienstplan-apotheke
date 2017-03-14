<?php
//Hole eine Liste aller Mitarbeiter
if (isset($datum)) {
    if (is_numeric($datum) && (int) $datum == $datum) {
        $sql_datum = date('Y-m-d', $datum);
    } else {
        $sql_datum = date('Y-m-d', strtotime($datum));
    }
} else {
    $sql_datum = '';
}
unset($Mandanten_mitarbeiter);
$abfrage = 'SELECT *
	FROM `employees`
	WHERE  `end_of_employment` > "'.$sql_datum.'" OR `end_of_employment` IS NULL
	ORDER BY `id` ASC, ISNULL(`end_of_employment`) ASC, `end_of_employment` ASC
	;';
//echo "$abfrage<br>\n";
$ergebnis = mysqli_query_verbose($abfrage);
while ($row = mysqli_fetch_object($ergebnis)) {
    if ($row->last_name != '') {
        $Mitarbeiter[$row->id] = $row->last_name;
        $Stunden_mitarbeiter[$row->id] = $row->working_week_hours;
        $Mittag_mitarbeiter[$row->id] = $row->lunch_break_minutes;
        $Ausbildung_mitarbeiter[$row->id] = $row->profession;
        if (isset($mandant) && $row->branch == $mandant && $row->working_hours > 10) {
            //Welche Mitarbeiter sind immer da?
            //TODO: Where do we need this? Is it a better choice to use the Grundplan there?
            $Mandanten_mitarbeiter[$row->id] = $row->last_name;
        }
        if ($row->profession == 'Apotheker' || $row->profession == 'PI') {
            //Wer ist ausreichend approbiert??

            $Approbierte_mitarbeiter[$row->id] = $row->last_name;
        }
        if ($row->goods_receipt == true) {
            //Who is ble to book goods inward?

            $Wareneingang_Mitarbeiter[$row->id] = $row->last_name;
        }
        if (isset($mandant) && $row->branch == $mandant && $row->compounding == true) {
            //Who is working in the formulation area?

            $Rezeptur_Mitarbeiter[$row->id] = $row->last_name;
        }
    } else {
        echo "ACHTUNG ACHTUNG ES GIBT KEINEN NACHNAMEN!<br>\n";
    }
}
//print_debug_variable(["\$mandant", $mandant, "\$Rezeptur_Mitarbeiter", $Rezeptur_Mitarbeiter]);