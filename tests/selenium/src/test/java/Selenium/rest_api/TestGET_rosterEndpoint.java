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
import Selenium.Utilities.LogCollector;
import java.io.IOException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.util.HashMap;
import java.util.Map;
import org.testng.Assert;
import org.testng.SkipException;
import org.testng.annotations.Test;
import org.testng.asserts.SoftAssert;

/**
 *
 * @author Mandelkow
 */
public class TestGET_rosterEndpoint extends Selenium.TestPage {

    private PropertyFile propertyFile;
    private SoftAssert softAssert = new SoftAssert();

    @Test()
    public void testGetRoster() throws IOException, Exception {
        if (Selenium.TestPage.someTestHasFailed) {
            throw new SkipException("Some Test has failed. Skipping all the other methods.");
        }
        try {
            propertyFile = new PropertyFile();
            String testPageUrl = propertyFile.getTestPageUrl();
            LogCollector.debug("Check if we are logged in:");
            if (!POST_authenticateEndpoint.isAuthenticated()) {
                String userName = propertyFile.getPdrUserName();
                String userPassphrase = propertyFile.getPdrUserPassword();
                new POST_authenticateEndpoint(userName, userPassphrase, testPageUrl);
            }
            String dateStart = "2020-07-01";
            String dateEnd = "2020-07-03";
            String employeeFullName = "Albert Kremer";

            GET_rosterEndpoint rosterEndpoint = new GET_rosterEndpoint(testPageUrl, dateStart, dateEnd, employeeFullName);
            HashMap<LocalDate, HashMap> foundRoster = rosterEndpoint.getFoundRosterHashMap();
            if (null == foundRoster) {
                Assert.fail();
                return;
            }
            LogCollector.debug("Found roster for dates between " + dateStart + " and " + dateEnd + ":");
            foundRoster.forEach((localDate, action) -> {
                LogCollector.debug(localDate.format(DateTimeFormatter.ISO_DATE));
            });
            softAssert.assertNotEquals(foundRoster.size(), 0, "Found no roster. The foundRoster.size() is 0");
            for (Map.Entry<LocalDate, HashMap> rosterDayEntry : foundRoster.entrySet()) {
                LocalDate date = rosterDayEntry.getKey();
                HashMap<Integer, RosterItem> rosterDay = rosterDayEntry.getValue();
                for (RosterItem rosterItem : rosterDay.values()) {
                    LocalDate dateInItem = rosterItem.getLocalDate();
                    softAssert.assertEquals(date, dateInItem);
                    /**
                     * Calculate the number of weeks between the two dates
                     */
                    long weeksBetween = ChronoUnit.WEEKS.between(LocalDate.parse(dateStart), dateInItem);

                    /**
                     * Check if the dates are in the same week of the year
                     */
                    softAssert.assertEquals(weeksBetween, 0);
                    softAssert.assertEquals(rosterItem.getEmployeeFullName(), employeeFullName);
                }
            }
        } catch (Exception exception) {
            LogCollector.error(exception.getLocalizedMessage());
            exception.printStackTrace();
            Assert.fail();
            throw exception;
        }
        softAssert.assertAll();
    }

}
