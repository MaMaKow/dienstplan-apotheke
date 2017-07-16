<?php
if (filter_has_var(INPUT_GET, 'datum')) {
    $datum = filter_input(INPUT_GET, 'datum', FILTER_SANITIZE_STRING);
}
if (filter_has_var(INPUT_GET, 'mandant')) {
    $mandant = filter_input(INPUT_GET, 'mandant', FILTER_SANITIZE_NUMBER_INT);
}
if (filter_has_var(INPUT_GET, 'auswahl_mitarbeiter')) {
    $auswahl_mitarbeiter = filter_input(INPUT_GET, 'auswahl_mitarbeiter', FILTER_SANITIZE_NUMBER_INT);
}
