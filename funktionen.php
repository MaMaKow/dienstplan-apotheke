<?php
function calculate_percentile($arr,$perc) {
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
?>
