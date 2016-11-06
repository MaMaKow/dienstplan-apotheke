<?php
        /**
         * @param string $cookie_name Name of the cookie.
         * @param mixed $cookie_value Value to be stored inside the cookie.
         * @param int $days The number of days until expiration.
         * @return null
        */
	function create_cookie($cookie_name, $cookie_value, $days=7)
	{
		if ( isset($cookie_name) AND isset($cookie_value) )
		{
			setcookie($cookie_name, $cookie_value, time() + (86400 * $days), "/"); // 86400 = 1 day
		}
	}

    /**
     * 
     * @param string $time_string
     * @return float time in hours
     */
    function time_from_text_to_int($time_string) {
        list($hour, $minute, $second) = explode(":", $time_string);
        $time_float = $hour + $minute / 60 + $second / 3600;
        return $time_float;
    }
        
	/**
         * @param array $arr An array of numbers.
         * @param int $percentile The number of the percentile (usually an integer between 0 and 100).
         * @return float The nth percentile of $arr
        */
        function calculate_percentile($arr, $percentile) {
	    sort($arr);
	    $count = count($arr); //total numbers in array
	    $middleval = floor(($count-1)*$percentile/100); // find the middle value, or the lowest middle value
	    if($count % 2) { // odd number, middle is the median
	        $median = $arr[$middleval];
	    } else { // even number, calculate avg of 2 medians
	        $low = $arr[$middleval];
	        $high = $arr[$middleval+1];
	        $median = (($low+$high)/2);
	    }
	    return $median;
	}

        /**
         * 
         * @global array $Mandanten_mitarbeiter
         * @param array $Dienstplan
         * @return int
         */
	function calculate_VKcount ($Dienstplan) {
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

        /**
         * 
         * @param mixed $data
         * @return mixed
         */
	function sanitize_user_input($data) {
	  $clean_data = htmlspecialchars(stripslashes(trim($data)));
	  return $clean_data;
	}
        
/**
 * 
 * @param array $Dienstplan
 * @return array A list of tie points where the number of employees might change.
 */
function calculate_changing_times($Dienstplan) {
    $Changing_times = array_merge_recursive($Dienstplan[0]['Dienstbeginn'], $Dienstplan[0]['Dienstende'], $Dienstplan[0]['Mittagsbeginn'], $Dienstplan[0]['Mittagsende']);
    sort($Changing_times);
    $Changing_times = array_unique($Changing_times);
    return $Changing_times;
}

function hex2rgb($hexstring) {
   $hex = str_replace("#", "", $hexstring);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   return implode(",", $rgb); // returns the rgb values separated by commas
   //return $rgb; // returns an array with the rgb values
}