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
     * @param string|null $htmlId The HTML ID of the selection menu (optional).
     * @param string|null $htmlForm The HTML form ID to which the menu belongs (optional).
     * @return string The generated HTML code for the selection menu.
     */
    public static function buildApprovalInputSelect(string $approvalSpecified, ?string $htmlId = NULL, ?string $htmlForm = NULL): string {
        $htmlText = "<select id='$htmlId' form='$htmlForm' class='absence_approval_input_select' name='approval'>\n";

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
    public static function buildReasonInputSelect(int $reasonSpecified, string $htmlId = NULL, string $htmlForm = NULL): string {
        $htmlText = "<select id='$htmlId' form='$htmlForm' class='absence_reason_input_select' name='reason_id'>\n";
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
}
