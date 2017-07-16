<?php

if (filter_has_var(INPUT_COOKIE, "auswahl_mitarbeiter")) {
    $auswahl_mitarbeiter = filter_input(INPUT_COOKIE, "auswahl_mitarbeiter", FILTER_SANITIZE_NUMBER_INT);
}
if (filter_has_var(INPUT_COOKIE, "mandant")) {
    $mandant = filter_input(INPUT_COOKIE, "mandant", FILTER_SANITIZE_NUMBER_INT);
}
if (filter_has_var(INPUT_COOKIE, "datum")) {
    $datum = filter_input(INPUT_COOKIE, "datum", FILTER_SANITIZE_STRING);
}
if (filter_has_var(INPUT_COOKIE, "year")) {
    $year = filter_input(INPUT_COOKIE, "year", FILTER_SANITIZE_NUMBER_INT);
}
