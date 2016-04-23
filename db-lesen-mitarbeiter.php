<?php
//Hole eine Liste aller Mitarbeiter
if (isset($datum)) {
    if (is_numeric($datum) && (int) $datum == $datum) {
        $sql_datum = date('Y-m-d', $datum);
    } else {
        $sql_datum = date('Y-m-d', strtotime($datum));
    }
} else {
    $sql_datum = '';
}
unset($Mandanten_mitarbeiter);
$abfrage = 'SELECT *
	FROM `Mitarbeiter`
	WHERE  `Beschäftigungsende` > "'.$sql_datum.'" OR `Beschäftigungsende` IS NULL
	ORDER BY `VK` ASC
	;';
$ergebnis = mysqli_query($verbindungi, $abfrage) or die("Error: $abfrage <br>".mysqli_error($verbindungi));
while ($row = mysqli_fetch_object($ergebnis)) {
    if ($row->Nachname != '') {
        $Mitarbeiter[$row->VK] = $row->Nachname;
        $Stunden_mitarbeiter[$row->VK] = $row->Arbeitswochenstunden;
        $Mittag_mitarbeiter[$row->VK] = $row->Mittag;
        $Ausbildung_mitarbeiter[$row->VK] = $row->Ausbildung;
        if (isset($mandant) && $row->Mandant == $mandant && $row->Stunden > 10) {
            //Welche Mitarbeiter sind immer da?

            $Mandanten_mitarbeiter[$row->VK] = $row->Nachname;
        }
        if ($row->Ausbildung == 'Apotheker' || $row->Ausbildung == 'PI') {
            //Wer ist ausreichend approbiert??

            $Approbierte_mitarbeiter[$row->VK] = $row->Nachname;
        }
        if ($row->Wareneingang == true) {
            //Wer kann einen Wareneingang machen?

            $Wareneingang_Mitarbeiter[$row->VK] = $row->Nachname;
        }
    }
}
