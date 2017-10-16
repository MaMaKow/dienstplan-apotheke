<?php
//Die Variable $datum muss hierzu bereits mit dem korrekten Wert gefüllt sein.
//Der Zugang zu Datenbank muss bereits bestehen.
	unset ($feiertag);
	$sql_datum=date('Y-m-d', strtotime($datum));
	$sql_query="SELECT * 
		FROM `Feiertage` 
		WHERE `Datum` = '$sql_datum';"; 
	$result=  mysqli_query_verbose($sql_query);
	while($row = mysqli_fetch_object($result))
	{
		$feiertag=$row->Name;
	}
