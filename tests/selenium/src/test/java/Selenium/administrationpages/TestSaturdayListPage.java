/*
 * Copyright (C) 2021 Mandelkow
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
package Selenium.administrationpages;

import Selenium.TestPage;
import java.time.LocalDate;
import org.testng.annotations.Test;
import org.testng.Assert;

/**
 *
 * @author Mandelkow
 */
public class TestSaturdayListPage extends TestPage {

    @Test(enabled = true)
    public void testSaturdayListPage() {
        /**
         * Sign in:
         */
        super.signIn();
        /**
         * Go to page:
         */
        int year = 2021;
        int branchId = 1;
        SaturdayListPage saturdayListPage = new SaturdayListPage(driver);
        saturdayListPage.selectYear(year);
        Assert.assertEquals(saturdayListPage.getYear(), year);
        saturdayListPage.selectBranch(branchId);
        Assert.assertEquals(saturdayListPage.getBranchId(), branchId);
        /**
         * <p lang=de>
         * Der 03. October 2026 ist ein Feiertag. Weil an diesem Feiertag kein
         * Samstagsteam arbeitet, muss nach dem Feiertag das Team arbeiten, dass
         * sonst am 03.10. dran gewesen wäre.
         * </p>
         */
        LocalDate saturdayDate = LocalDate.of(2026, 9, 26);
        int firstFoundTeamId = saturdayListPage.getTeamIdOnDate(saturdayDate);
        //Assert that the found team number is in the expected range:
        Assert.assertTrue(firstFoundTeamId < SaturdayRotationTeam.getSaturdayTeamsSize());

        saturdayDate = LocalDate.of(2026, 10, 3);
        Assert.assertEquals(saturdayListPage.teamIdOnDateIsMissing(saturdayDate), true);

        saturdayDate = LocalDate.of(2026, 10, 10);
        Assert.assertEquals(saturdayListPage.getTeamIdOnDate(saturdayDate), getNextTeamId(firstFoundTeamId));
    }

    /**
     * Gets the next team ID, considering a circular rotation.
     * If the current team ID is within the valid range (0 to SaturdayRotationTeam.getSaturdayTeamsSize() - 1),
     * returns the next team ID. If the current team ID is the maximum possible value, wraps around to 0.
     *
     * @param currentTeamId The current team ID.
     * @return The next team ID.
     */
    private int getNextTeamId(int currentTeamId) {
        // Check if the current team ID is below the maximum:
        if (SaturdayRotationTeam.getSaturdayTeamsSize() >= currentTeamId) {
            // Return the next team ID
            return ++currentTeamId;
        }
        // If the current team ID is at the maximum possible value, wrap around to 0
        return 0;
    }
}
