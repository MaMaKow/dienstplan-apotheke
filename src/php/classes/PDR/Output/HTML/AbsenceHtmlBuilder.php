<?php

/*
 * Copyright (C) 2023 Mandelkow
 *
 * Dienstplan Apotheke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace PDR\Output\HTML;

/**
 * AbsenceHtmlBuilder class provides utility methods for generating HTML elements related to absences.
 *
 * This class includes methods to generate HTML code for selection menus used in the user interface
 * for approving absences and selecting reasons for absence entries. The generated HTML is designed
 * to be easily integrated into HTML forms for managing absence-related data.
 *
 * @package PDR\Output\HTML
 * @author Mandelkow
 */
class AbsenceHtmlBuilder {

    /**
     * Generates HTML code for a selection menu for approving absences.
     *
     * @param string $approvalSpecified The selected approval status.
     * @param string $htmlId The HTML ID of the selection menu.
     * @param string $htmlForm The HTML form ID to which the menu belongs.
     * @return string The generated HTML code for the selection menu.
     */
    public static function buildApprovalInputSelect(string $approvalSpecified, string $htmlId, string $htmlForm, \sessions $session): string {
        $disabledInputElementState = "disabled";
        if ($session->user_has_privilege(\sessions::PRIVILEGE_CREATE_ABSENCE)) {
            $disabledInputElementState = "";
        }

        $htmlText = "<select id='$htmlId' form='$htmlForm' name='approval' $disabledInputElementState>" . PHP_EOL;

        // Iterate through the list of approval states
        foreach (\PDR\Utility\AbsenceUtility::$ListOfApprovalStates as $approval) {
            if ($approval == $approvalSpecified) {
                // Mark the selected status
                $htmlText .= "<option value='$approval' selected>" . \localization::gettext($approval) . "</option>\n";
            } else {
                // Regular option without selection
                $htmlText .= "<option value='$approval'>" . \localization::gettext($approval) . "</option>\n";
            }
        }
        $htmlText .= "</select>\n";
        return $htmlText;
    }

    /**
     * Build a select element for easy input of absence entries.
     *
     * The list contains reasons of absence (like [de_DE] "Urlaub" or "Krankheit").
     * Those reasons can also be found in absence::$List_of_absence_reasons.
     *
     * @param int    $reasonSpecified The specified reason ID.
     * @param string $htmlId          The HTML ID attribute (optional).
     * @param string $htmlForm        The HTML form attribute (optional).
     *
     * @return string HTML select element.
     */
    public static function buildReasonInputSelect(int $reasonSpecified, string $htmlId = NULL, string $htmlForm = NULL, \sessions $session): string {
        $disabledInputElementState = "disabled";
        if ($session->user_has_privilege(\sessions::PRIVILEGE_CREATE_ABSENCE)) {
            $disabledInputElementState = "";
        }
        $htmlText = "<select id='$htmlId' form='$htmlForm' name='reason_id' $disabledInputElementState>" . PHP_EOL;
        foreach (\PDR\Utility\AbsenceUtility::$ListOfAbsenceReasons as $reasonId) {
            if ($reasonId === $reasonSpecified) {
                $htmlText .= "<option value='$reasonId' selected>" . htmlspecialchars(\PDR\Utility\AbsenceUtility::getReasonStringLocalized($reasonId)) . "</option>\n";
            } else {
                $htmlText .= "<option value='$reasonId'>" . htmlspecialchars(\PDR\Utility\AbsenceUtility::getReasonStringLocalized($reasonId)) . "</option>\n";
            }
        }
        $htmlText .= "</select>\n";
        return $htmlText;
    }

    /**
     * Builds an HTML button for submitting a delete command for a specific row.
     *
     * @param \stdClass $row The object representing the row for which the delete button is generated.
     *
     * @return string The HTML code for the delete button.
     */
    public static function buildButtonSubmitDelete(\stdClass $row): string {
        $buttonText = "<button type=submit id=delete_$row->start class='button-small delete_button no-print' title='Diese Zeile löschen' name=command value=delete onclick='return confirmDelete()'>\n"
                . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_delete_forever.svg' alt='Diese Zeile löschen'>\n"
                . "</button>\n";
        return $buttonText;
    }

    /**
     * Builds an HTML button for canceling the editing of a specific row.
     *
     * @param \stdClass $row The object representing the row for which the cancel edit button is generated.
     *
     * @return string The HTML code for the cancel edit button.
     */
    public static function buildButtonCancelEdit(\stdClass $row): string {
        $buttonText = "<button type=button id=cancel_$row->start class='button-small no-print' title='Bearbeitung abbrechen' onclick='return cancelEdit(\"$row->start\")' style='display: none; border-radius: 32px; background-color: transparent;'>\n"
                . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/backward.png' alt='Bearbeitung abbrechen'>\n"
                . "</button>\n";
        return $buttonText;
    }

    /**
     * Builds an HTML button for initiating the editing of a specific row.
     *
     * @param \stdClass $row The object representing the row for which the edit button is generated.
     *
     * @return string The HTML code for the edit button.
     */
    public static function buildButtonEdit(\stdClass $row): string {
        $buttonText = "<button type=button id=edit_$row->start class='button-small edit_button no-print' title='Diese Zeile bearbeiten' name=command onclick='showEdit(\"$row->start\")'>\n"
                . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_edit.svg' alt='Diese Zeile bearbeiten'>\n"
                . "</button>\n";
        return $buttonText;
    }

    /**
     * Builds an HTML button for submitting changes to a specific row.
     *
     * @param \stdClass $row The object representing the row for which the save button is generated.
     *
     * @return string The HTML code for the save button.
     */
    public static function buildButtonSubmitSave(\stdClass $row): string {
        $buttonText = "<button type='submit' id='save_$row->start' class='button-small no-print' title='Veränderungen dieser Zeile speichern' name='command' value='replace' style='display: none; border-radius: 32px;'>\n"
                . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_save.svg' alt='Veränderungen dieser Zeile speichern'>\n"
                . "</button>\n";
        return $buttonText;
    }

    /**
     * Builds a hidden input field for a cut start operation during overlapping absences.
     *
     * This method calculates the start without overlap, creates a new absence object without the overlap,
     * and returns an HTML hidden input field with the serialized JSON representation of the new absence.
     *
     * @param \PDR\Roster\Absence $overlappingAbsence The overlapping absence.
     * @param \PDR\Roster\Absence $absenceInRow The original absence for which the overlap is being cut.
     * @param \employee $employeeObject The employee object associated with the absences.
     *
     * @return string The HTML hidden input field containing the serialized JSON representation of the new absence.
     */
    private static function buildHiddenInputOverlapCutStart(\PDR\Roster\Absence $overlappingAbsence, \PDR\Roster\Absence $absenceInRow, \employee $employeeObject): string {
        $startWithoutOverlap = (clone $overlappingAbsence->getEnd())->add(new \DateInterval('P1D'));
        $days = \PDR\Utility\AbsenceUtility::calculateEmployeeAbsenceDays(clone $startWithoutOverlap, clone $absenceInRow->getEnd(), $employeeObject);

        $newAbsenceWithoutOverap = new \PDR\Roster\Absence(
                $absenceInRow->getEmployeeKey(),
                $startWithoutOverlap,
                $absenceInRow->getEnd(),
                $days,
                $absenceInRow->getReasonId(),
                $absenceInRow->getComment(),
                $absenceInRow->getApproval(),
                $absenceInRow->getUserName(),
                $absenceInRow->getTimeStamp()
        );
        $hiddenInput = "<input name='newAbsenceWithoutOverap' value='" . json_encode($newAbsenceWithoutOverap) . "' hidden=true>";
        return $hiddenInput;
    }

    /**
     * Builds a hidden input field for a cut end operation during overlapping absences.
     *
     * This method calculates the end without overlap, creates a new absence object without the overlap,
     * and returns an HTML hidden input field with the serialized JSON representation of the new absence.
     *
     * @param \PDR\Roster\Absence $overlappingAbsence The overlapping absence.
     * @param \PDR\Roster\Absence $absenceInRow The original absence for which the overlap is being cut.
     * @param \employee $employeeObject The employee object associated with the absences.
     *
     * @return string The HTML hidden input field containing the serialized JSON representation of the new absence.
     */
    private static function buildHiddenInputOverlapCutEnd(\PDR\Roster\Absence $overlappingAbsence, \PDR\Roster\Absence $absenceInRow, \employee $employeeObject): string {
        $endWithoutOverlap = (clone $overlappingAbsence->getStart())->sub(new \DateInterval('P1D'));
        $days = \PDR\Utility\AbsenceUtility::calculateEmployeeAbsenceDays(clone $absenceInRow->getStart(), clone $endWithoutOverlap, $employeeObject);
        $newAbsenceWithoutOverap = new \PDR\Roster\Absence(
                $absenceInRow->getEmployeeKey(),
                $absenceInRow->getStart(),
                $endWithoutOverlap,
                $days,
                $absenceInRow->getReasonId(),
                $absenceInRow->getComment(),
                $absenceInRow->getApproval(),
                $absenceInRow->getUserName(),
                $absenceInRow->getTimeStamp()
        );
        $hiddenInput = "<input name='newAbsenceWithoutOverap' value='" . json_encode($newAbsenceWithoutOverap) . " 'hidden=true>";
        return $hiddenInput;
    }

    /**
     * Creates an HTML hidden input field based on overlapping absences and the original absence.
     *
     * This method analyzes the relationship between the original absence ($absenceInRow) and an overlapping absence,
     * determining if the overlap should be cut at the start, end, or entirely. It delegates to specialized methods
     * for each case and returns the HTML hidden input field with the serialized JSON representation of the new absence
     * without the overlap.
     *
     * @param \PDR\Roster\Absence $overlappingAbsence The overlapping absence.
     * @param \PDR\Roster\Absence $absenceInRow The original absence for which the overlap is being handled.
     * @param \employee $employeeObject The employee object associated with the absences.
     *
     * @return string The HTML hidden input field containing the serialized JSON representation of the new absence.
     *
     * Overlapping Absence Cutting Scenarios:
     *
     * cut start:
     * ---------------XXXXXXXXXXXXXXXXXXXXXXX $absenceInRow
     * ##################-------------------- $overlappingAbsence
     * Description: If the overlapping absence starts before $absenceInRow and ends during it,
     * this scenario represents cutting the overlap at the start of $absenceInRow.
     *
     * cut end:
     * XXXXXXXXXXXXXXXXXXXXXXX--------------- $absenceInRow
     * --------------------################## $overlappingAbsence
     * Description: If the overlapping absence starts during $absenceInRow and ends after it,
     * this scenario represents cutting the overlap at the end of $absenceInRow.
     *
     * cut all:
     * ---------------XXXXXXXXXXXXXXXXXX----- $absenceInRow
     * ------------########################## $overlappingAbsence
     * Description: If the overlapping absence entirely encompasses $absenceInRow,
     * this scenario represents cutting the entire overlap.
     *
     * CAVE! Not handled. @todo possibly cut in two parts?:
     * ---------------XXXXXXXXXXXXXXXXXX----- $absenceInRow
     * -------------------##########--------- $overlappingAbsence
     * Description: This scenario is not handled.
     *
     */
    public static function buildHiddenInputOverlap(\PDR\Roster\Absence $overlappingAbsence, \PDR\Roster\Absence $absenceInRow, \employee $employeeObject): string {
        $hiddenInput = "";
        if ($overlappingAbsence->getStart() < $absenceInRow->getStart() and $overlappingAbsence->getEnd() <= $absenceInRow->getEnd()) {
            // Cut at the start
            $hiddenInput = self::buildHiddenInputOverlapCutStart($overlappingAbsence, $absenceInRow, $employeeObject);
        } elseif ($overlappingAbsence->getStart() >= $absenceInRow->getStart() and $overlappingAbsence->getEnd() > $absenceInRow->getEnd()) {
            // Cut at the end
            $hiddenInput = self::buildHiddenInputOverlapCutEnd($overlappingAbsence, $absenceInRow, $employeeObject);
        } elseif ($overlappingAbsence->getStart() <= $absenceInRow->getStart() and $overlappingAbsence->getEnd() >= $absenceInRow->getEnd()) {
            // Cut the entire overlap
            $hiddenInput = "<input name='newAbsenceWithoutOverap' value='' hidden=true>";
        }
        return $hiddenInput;
    }

    /**
     * Build information about overlapping absences.
     *
     * This method generates HTML information about overlapping absences
     * based on the provided collection of overlapping absences. If the collection
     * is empty, an empty string is returned.
     *
     * @param \PDR\Roster\AbsenceCollection $collectionOfOverlappingAbsences The collection of overlapping absences.
     * @return string HTML information about overlapping absences.
     */
    public static function buildInfoOverlap(\PDR\Roster\AbsenceCollection $collectionOfOverlappingAbsences): string {
        if ($collectionOfOverlappingAbsences->isEmpty()) {
            return "";
        }
        $info = "";
        foreach ($collectionOfOverlappingAbsences as $absence) {
            $info .= "<p class=absenceCollisionParagraph>\n"
                    . "<span class='hint'>" . gettext("collides with") . ": </span>\n"
                    . "<span class='hint'>" . $absence->getStart()->format("d.m.Y") . "</span>\n" //@todo: localization
                    . "<span class='hint'> - </span>\n"
                    . "<span class='hint'>" . $absence->getEnd()->format("d.m.Y") . "</span>\n" //@todo: localization
                    . "</p>\n";
        }
        return $info;
    }

    /**
     * Build a button to cut overlapping absences.
     *
     * This method generates HTML for a button to cut overlapping absences based on
     * the provided collection of overlapping absences, the absence in the row,
     * and the employee object. If the collection of overlapping absences is empty,
     * an empty string is returned.
     *
     * @param \PDR\Roster\AbsenceCollection $collectionOfOverlappingAbsences The collection of overlapping absences.
     * @param \PDR\Roster\Absence $absenceInRow The absence in the row.
     * @param \employee $employeeObject The employee object.
     * @return string HTML for the button to cut overlapping absences.
     */
    public static function buildButtonCutOverlap(\PDR\Roster\AbsenceCollection $collectionOfOverlappingAbsences, \PDR\Roster\Absence $absenceInRow, \employee $employeeObject): string {
        if ($collectionOfOverlappingAbsences->isEmpty()) {
            return "";
        }
        $button = "";
        foreach ($collectionOfOverlappingAbsences as $overlappingAbsence) {
            $hiddenInput = self::buildHiddenInputOverlap($overlappingAbsence, $absenceInRow, $employeeObject);
            $button .= "<p><button type='submit' id='overlap_" . $absenceInRow->getStart()->format("Y-m-d") . "' class='button-small no-print overlapCutButton' title='Diensen Eintrag kürzen' name='command' value='cutOverlap' style='border-radius: 32px;'>" . PHP_EOL
                    . "<img src='" . \PDR_HTTP_SERVER_APPLICATION_PATH . "img/md_cut.svg' alt='Diensen Eintrag kürzen'>" . PHP_EOL
                    . "<span class='hint'>" . gettext("cut the above entry") . "</span>" . PHP_EOL //@todo: localization
                    . "</button></p>" . PHP_EOL;
        }
        return $hiddenInput . PHP_EOL . $button;
    }
}
