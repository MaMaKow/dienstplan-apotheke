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

import Selenium.LogoutPage;
import Selenium.RealData.RealWorkforce;
import Selenium.Utilities.LogCollector;
import java.time.LocalDate;
import java.time.Month;
import java.time.Year;
import java.time.format.DateTimeFormatter;
import org.testng.Assert;
import org.testng.annotations.Listeners;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
@Listeners(Selenium.Utilities.Listener.class)
public class TestEmergencyServiceListPage extends Selenium.TestPage {

    public TestEmergencyServiceListPage() {
        super();
    }

    @Test(groups = "emptyInstance")
    public void testEmergencyService() {
        LocalDate localDate = LocalDate.of(2024, Month.AUGUST, 8);
        Year someOtherYear = Year.of(2021);
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        /**
         * Go to page:
         */
        EmergencyServiceListPage emergencyServiceListPage = new EmergencyServiceListPage(driver);
        emergencyServiceListPage.selectYear(someOtherYear.getValue());
        emergencyServiceListPage.selectBranch(2);
        Assert.assertEquals(emergencyServiceListPage.getYear(), someOtherYear.getValue());
        Assert.assertEquals(emergencyServiceListPage.getBranchId(), 2);
        emergencyServiceListPage.selectYear(localDate.getYear());
        emergencyServiceListPage.selectBranch(1);
        Assert.assertEquals(emergencyServiceListPage.getYear(), localDate.getYear());
        Assert.assertEquals(emergencyServiceListPage.getBranchId(), 1);
        /**
         * <p lang=de>Daten einfügen:</p>
         */
        String employeeNameInsert = "Anabell Neuhaus";
        String employeeNameChange = "Albert Baumann";
        /**
         * <p lang=de>Nur Apotheker (und PI) können allein im Notdienst
         * eingesetzt werden.</p>
         */
        Assert.assertEquals("Apotheker", workforce.getEmployeeByFullName(employeeNameInsert).getProfession());
        Assert.assertEquals("Apotheker", workforce.getEmployeeByFullName(employeeNameChange).getProfession());
        emergencyServiceListPage = emergencyServiceListPage.addLineForDate(localDate);
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeNameOnDate(localDate, employeeNameInsert);
        /**
         * <p lang=de>Daten abfragen:</p>
         */
        Assert.assertEquals(emergencyServiceListPage.getEmployeeFullNameOnDate(localDate), employeeNameInsert);
        /**
         * <p lang=de>Daten ändern :</p>
         */
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeNameOnDate(localDate, employeeNameChange);
        Assert.assertEquals(emergencyServiceListPage.getEmployeeFullNameOnDate(localDate), employeeNameChange);
        /**
         * <p lang=de>Zeilen wieder entfernen</p>
         */
        emergencyServiceListPage = emergencyServiceListPage.doNotRemoveLineByDate(localDate);
        Assert.assertNotNull(emergencyServiceListPage.getEmployeeKeyOnDate(localDate));
        emergencyServiceListPage = emergencyServiceListPage.removeLineByDate(localDate);
        Assert.assertNull(emergencyServiceListPage.getEmployeeKeyOnDate(localDate));
    }

    @Test(groups = "realWorldInstance")
    public void testRealEmergencyServiceList() {
        try {
            LogoutPage logoutPage = new LogoutPage();
            logoutPage.logout();
            super.realSignIn();
        } catch (Exception exception) {
            exception.printStackTrace();
            LogCollector.error(exception.getStackTrace().toString());
            LogCollector.error(exception.getMessage());
            logger.error("Sign in failed to real test page.");
            Assert.fail("Sign in failed to real test page.");
        }

        int branchId = 1;
        Integer employeeKeyInsert = 55;
        Integer employeeKeyChange = 7;
        LocalDate localDate = LocalDate.of(2024, Month.DECEMBER, 26);

        /**
         * Get Workforce data:
         */
        WorkforceManagementPage workforceManagementPage = new WorkforceManagementPage(driver);
        RealWorkforce realWorkforce = workforceManagementPage.getAllEmployeesRealWorkforce();

        EmergencyServiceListPage emergencyServiceListPage = new EmergencyServiceListPage(driver);
        LogCollector.debug("going to call method emergencyServiceListPage.selectYear()");
        emergencyServiceListPage.selectYear(String.valueOf(localDate.getYear()));
        LogCollector.debug("Called method emergencyServiceListPage.selectYear()");
        emergencyServiceListPage.selectBranch(branchId);
        Assert.assertEquals(emergencyServiceListPage.getYear(), localDate.getYear());
        Assert.assertEquals(emergencyServiceListPage.getBranchId(), branchId);
        /**
         * <p lang=de>Nur Apotheker (und PI) können allein im Notdienst
         * eingesetzt werden.</p>
         */
        Assert.assertEquals("Apotheker", realWorkforce.getEmployeeByRealKey(employeeKeyInsert).getProfession());
        Assert.assertEquals("Apotheker", realWorkforce.getEmployeeByRealKey(employeeKeyChange).getProfession());
        /**
         * <p lang=de>Daten einfügen:</p>
         */
        LogCollector.debug("driver.getCurrentUrl(): " + driver.getCurrentUrl());
        LogCollector.debug("localDate: " + localDate.format(DateTimeFormatter.ISO_DATE));
        boolean rowExistsOnDate = emergencyServiceListPage.rowExistsOnDate(localDate);
        this.softAssert.assertFalse(emergencyServiceListPage.rowExistsOnDate(localDate));
        emergencyServiceListPage = emergencyServiceListPage.addLineForDate(localDate);
        this.softAssert.assertNull(emergencyServiceListPage.getEmployeeKeyOnDate(localDate));
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeKeyOnDate(localDate, employeeKeyInsert);
        /**
         * <p lang=de>Daten abfragen:</p>
         */
        this.softAssert.assertEquals(emergencyServiceListPage.getEmployeeKeyOnDate(localDate), employeeKeyInsert);
        /**
         * <p lang=de>Daten ändern :</p>
         */
        try {
            emergencyServiceListPage = emergencyServiceListPage.setEmployeeKeyOnDate(localDate, employeeKeyChange);
        } catch (Exception exception) {
            exception.printStackTrace();
            System.out.println(exception.getMessage());
        }
        softAssert.assertEquals(emergencyServiceListPage.getEmployeeKeyOnDate(localDate), employeeKeyChange);
        /**
         * <p lang=de>Zeilen wieder entfernen</p>
         */
        emergencyServiceListPage = emergencyServiceListPage.doNotRemoveLineByDate(localDate);
        softAssert.assertTrue(emergencyServiceListPage.rowExistsOnDate(localDate));
        softAssert.assertNotNull(emergencyServiceListPage.getEmployeeKeyOnDate(localDate));
        emergencyServiceListPage = emergencyServiceListPage.removeLineByDate(localDate);
        softAssert.assertNull(emergencyServiceListPage.getEmployeeKeyOnDate(localDate));
        softAssert.assertAll();
    }
}
