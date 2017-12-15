<?php

$Dienstplan = filter_input(INPUT_POST, 'Dienstplan', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
$tage = count($Dienstplan);
foreach (array_keys($Dienstplan) as $tag => $value) {
    if (empty($Dienstplan[$tag]['Datum'][0])) {
        continue;
    }
    $date = $Dienstplan[$tag]['Datum'][0];
    if (filter_has_var(INPUT_POST, 'submit_approval')) {
        $state = "approved";
    } elseif (filter_has_var(INPUT_POST, 'submit_disapproval')) {
        $state = "disapproved";
    } else {
        //no state is given.
        // TODO: This is an Exception. Should we fail fast and loud?
        die("An Error has occurred during approval!");
    }
    //The variable $user is set within the default.php
    $sql_query = "INSERT INTO `approval` (date, branch, state, user) values ('$date', '$mandant', '$state', '$user') ON DUPLICATE KEY UPDATE date='$date', branch='$mandant', state='$state', user='$user'";
    $result = mysqli_query_verbose($sql_query);
}
