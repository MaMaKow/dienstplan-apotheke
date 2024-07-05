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
echo exec("bash /var/www/html/nextcloud/data/Martin/files/Dokumente/Freizeit/Programmierung/git/dienstplan-apotheke/tests/test_application_refresh_from_nextcloud.sh", $output);
echo "\n";
var_export($output);
echo "</pre>\n";

echo "<span id=span_done>done</span>\n";
