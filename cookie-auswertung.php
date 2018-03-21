<?php

if (filter_has_var(INPUT_COOKIE, "datum")) {
    $datum = filter_input(INPUT_COOKIE, "datum", FILTER_SANITIZE_STRING);
}
if (filter_has_var(INPUT_COOKIE, "year")) {
    $year = filter_input(INPUT_COOKIE, "year", FILTER_SANITIZE_NUMBER_INT);
}
