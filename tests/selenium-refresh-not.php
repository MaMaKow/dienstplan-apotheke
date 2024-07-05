<?php

/*
 * Dieses Script soll eine frische Applikation bereitstellen.
 * Aufgaben:
 *   - Alte Datenbanken entfernen
 *   - Alte Ordner entfernen
 *   - Neue Ordner aus der Nextcloud holen
 */
echo "<pre>\n";
$output = array();
echo "exec: Holen\n";
/**
 * Actually do not do anything.
 * There is another version of this script, that fetches fresh data from the
 *   nextcloud to get the newest files under development.
 * This file here is used in the testing stage within a docker container.
 * The container is built with a fresh database on every startup.
 */
echo "</pre>\n";

echo "<span id=span_done>done</span>\n";
