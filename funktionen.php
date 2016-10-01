<?php
	function create_cookie(string $cookie_name, $cookie_value, int $days=7)
	{
		if ( isset($cookie_name) AND isset($cookie_value) )
		{
			setcookie($cookie_name, $cookie_value, time() + (86400 * $days), "/"); // 86400 = 1 day
		}
	}

	function calculate_percentile(array $arr, int $perc) {
	    sort($arr);
	    $count = count($arr); //total numbers in array
	    $middleval = floor(($count-1)*$perc/100); // find the middle value, or the lowest middle value
	    if($count % 2) { // odd number, middle is the median
	        $median = $arr[$middleval];
	    } else { // even number, calculate avg of 2 medians
	        $low = $arr[$middleval];
	        $high = $arr[$middleval+1];
	        $median = (($low+$high)/2);
	    }
	    return $median;
	}

	function calculate_VKcount (array $Dienstplan) {
		global $Mandanten_mitarbeiter;
		foreach($Dienstplan as $key => $Dienstplantag)
		{
			if(isset($Dienstplantag['VK']))
			{
				$Plan_anzahl[]=(count($Dienstplantag['VK']));
			}
			else
			{
				$Plan_anzahl[]=0;
			}
		}
		$plan_anzahl=max($Plan_anzahl); //Die Anzahl der Zeilen der Tabelle richtet sich nach dem Tag mit den meisten Einträgen.
		$VKcount=max($plan_anzahl+1, count($Mandanten_mitarbeiter)); //Die Anzahl der Mitarbeiter. Es können ja nicht mehr Leute arbeiten, als Mitarbeiter vorhanden sind.
		return $VKcount;
	}

	function sanitize_user_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}
