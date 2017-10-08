<?php

//Get a list of employees:
/*
 * TODO:
 * This whole if-construct will become obsolete, once this is a function with a defined $date_sql.
 */
if (isset($date_sql)) {
    //$date_sql is already set.
} elseif (isset($date_unix)) {
    $date_sql = date('Y-m-d', $date_unix);
} elseif (isset($datum)) {
    if (is_numeric($datum) && (int) $datum == $datum) {
        $date_sql = date('Y-m-d', $datum);
    } else {
        $date_sql = date('Y-m-d', strtotime($datum));
    }
} else {
    $date_sql = '';
}
unset($Mandanten_mitarbeiter);
$sql_query = 'SELECT *
	FROM `employees`
	WHERE  `end_of_employment` > "' . $date_sql . '" OR `end_of_employment` IS NULL
	ORDER BY `id` ASC, ISNULL(`end_of_employment`) ASC, `end_of_employment` ASC
	;';
$result = mysqli_query_verbose($sql_query);
while ($row = mysqli_fetch_object($result)) {
    if ($row->last_name != '') {
        $List_of_employees[$row->id] = $row->last_name;
        $List_of_employee_working_week_hours[$row->id] = $row->working_week_hours;
        $List_of_employee_lunch_break_minutes[$row->id] = $row->lunch_break_minutes;
        $List_of_employee_professions[$row->id] = $row->profession;
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
