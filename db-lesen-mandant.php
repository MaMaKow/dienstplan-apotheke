<?php

//Get a list of branches:
$sql_query = 'SELECT *
	FROM `branch`
	;';
$result = mysqli_query_verbose($sql_query);
while ($row = mysqli_fetch_object($result)) {
    if ($row->short_name != "") {
        $Branch_name[$row->branch_id] = $row->name;
        $Branch_address[$row->branch_id] = $row->address;
        $Branch_short_name[$row->branch_id] = $row->short_name;
        $Branch_pep_id[$row->branch_id] = $row->PEP;
    }
}
