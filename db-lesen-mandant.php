<?php

//Get a list of branches:
$sql_query = 'SELECT *
	FROM `branch`
	;';
$statement = $pdo->prepare($sql_query);
$statement->execute();
print_debug_variable($statement->ErrorInfo());

while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
    if ($row->short_name != "") {
        $Branch_name[$row->branch_id] = $row->name;
        $Branch_short_name[$row->branch_id] = $row->short_name;
        $Branch_address[$row->branch_id] = $row->address;
        $Branch_manager[$row->branch_id] = $row->manager;
        $Branch_pep_id[$row->branch_id] = $row->PEP;
    }
}
