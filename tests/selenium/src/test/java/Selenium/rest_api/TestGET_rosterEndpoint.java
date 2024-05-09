/*
 * Copyright (C) 2024 Mandelkow
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
package Selenium.rest_api;

import Selenium.PropertyFile;
import Selenium.RosterItem;
import java.io.IOException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.testng.Assert;
import org.testng.annotations.Test;
import org.testng.asserts.SoftAssert;

/**
 *
 * @author Mandelkow
 */
public class TestGET_rosterEndpoint {

    private PropertyFile propertyFile;
    private SoftAssert softAssert = new SoftAssert();

    @Test(enabled = true)
    public void testGetRoster() {
        LocalDate today = LocalDate.now();
        try {
            propertyFile = new PropertyFile();
            String testPageUrl = propertyFile.getRealTestPageUrl();
            if (!POST_authenticateEndpoint.isAuthenticated()) {
                String userName = propertyFile.getRealUsername();
                String userPassphrase = propertyFile.getRealPassword();
                new POST_authenticateEndpoint(userName, userPassphrase, testPageUrl);
            }
            GET_rosterEndpoint rosterEndpoint = new GET_rosterEndpoint(testPageUrl);
            HashMap<LocalDate, HashMap> foundRoster = rosterEndpoint.getFoundRosterHashMap();
            softAssert.assertNotEquals(foundRoster.size(), 0);
            for (Map.Entry<LocalDate, HashMap> rosterDayEntry : foundRoster.entrySet()) {
                LocalDate date = rosterDayEntry.getKey();
                HashMap<Integer, RosterItem> rosterDay = rosterDayEntry.getValue();
                for (RosterItem rosterItem : rosterDay.values()) {
                    LocalDate dateInItem = rosterItem.getLocalDate();
                    softAssert.assertEquals(date, dateInItem);
                    /**
                     * Calculate the number of weeks between the two dates
                     */
                    long weeksBetween = ChronoUnit.WEEKS.between(today, dateInItem);

                    /**
                     * Check if the dates are in the same week of the year
                     */
                    softAssert.assertEquals(weeksBetween, 0);
                    softAssert.assertEquals(rosterItem.getBranchId(), 1);
                    softAssert.assertEquals(rosterItem.getEmployeeKey(), (Integer) 55);

                }
            }
        } catch (IOException | InterruptedException exception) {
            exception.printStackTrace();
            Assert.fail();
        } catch (Exception exception) {
            Logger.getLogger(TestGET_rosterEndpoint.class.getName()).log(Level.SEVERE, null, exception);
            exception.printStackTrace();
            Assert.fail();
        }
        softAssert.assertAll();
    }

}
