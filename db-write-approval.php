<?php
$tage=count($_POST['Dienstplan']);
foreach (array_keys($_POST['Dienstplan']) as $tag => $value) {
  $date = filter_var($_POST['Dienstplan'][$tag]['Datum'][0], FILTER_SANITIZE_STRING);
  /*
  if ($tage == 1) { $period="day"; }
  elseif ($tage > 1 and $tage <= 7){ $period="week";}
  elseif ($tage > 15 and $tage <= 31){ $period="month";}
  else {
  //unknown period of time.
  }
  */
  if (filter_has_var(INPUT_POST, 'submit_approval')) {
    $state="approved";
  } elseif (filter_has_var(INPUT_POST, 'submit_disapproval')) {
    $state="disapproved";
  } else {
    //no state is given.
    // TODO: This is an Exception. Should we fail fast and loud?
    die ("An Error has occurred during approval!");
  }
  //The variable $user is set within the default.php
  $abfrage="INSERT INTO `approval` (date, branch, state, user) values ('$date', '$mandant', '$state', '$user') ON DUPLICATE KEY UPDATE date='$date', branch='$mandant', state='$state', user='$user'";
  $ergebnis = mysqli_query_verbose($abfrage);
}
