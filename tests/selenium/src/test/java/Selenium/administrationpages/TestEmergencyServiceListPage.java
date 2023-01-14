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
import Selenium.rosterpages.Workforce;
import java.time.LocalDate;
import java.time.Month;
import org.testng.annotations.Test;
import org.testng.Assert;

/**
 *
 * @author Mandelkow
 */
public class TestEmergencyServiceListPage extends TestPage {

    @Test(enabled = true)/*new*/
    public void testEmergencyService() {
        /**
         * Sign in:
         */
        super.signIn();
        /**
         * Go to page:
         */
        EmergencyServiceListPage emergencyServiceListPage = new EmergencyServiceListPage(driver);
        emergencyServiceListPage.selectYear("2021");
        emergencyServiceListPage.selectBranch(2);
        emergencyServiceListPage.selectYear("2019");
        emergencyServiceListPage.selectBranch(1);
        Assert.assertEquals(emergencyServiceListPage.getYear(), 2019);
        Assert.assertEquals(emergencyServiceListPage.getBranchId(), 1);
        /**
         * <p lang=de>Daten einfügen:</p>
         */
        Integer employeeKeyInsert = 4;
        Integer employeeKeyChange = 6;
        Workforce workforce = new Workforce();
        /**
         * <p lang=de>Nur Apotheker (und PI) können allein im Notdienst
         * eingesetzt werden.</p>
         */
        Assert.assertEquals("Apotheker", workforce.getEmployeeByKey(employeeKeyInsert).getProfession());
        Assert.assertEquals("Apotheker", workforce.getEmployeeByKey(employeeKeyChange).getProfession());
        LocalDate localDate = LocalDate.of(2019, Month.AUGUST, 8);
        emergencyServiceListPage = emergencyServiceListPage.addLineForDate(localDate);
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeKeyOnDate(localDate, employeeKeyInsert);
        /**
         * <p lang=de>Daten abfragen:</p>
         */
        Assert.assertEquals(emergencyServiceListPage.getEmployeeKeyOnDate(localDate), employeeKeyInsert);
        /**
         * <p lang=de>Daten ändern :</p>
         */
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeKeyOnDate(localDate, employeeKeyChange);
        Assert.assertEquals(emergencyServiceListPage.getEmployeeKeyOnDate(localDate), employeeKeyChange);
        /**
         * <p lang=de>Zeilen wieder entfernen</p>
         */
        emergencyServiceListPage = emergencyServiceListPage.doNotRemoveLineByDate(localDate);
        Assert.assertNotNull(emergencyServiceListPage.getEmployeeKeyOnDate(localDate));
        emergencyServiceListPage = emergencyServiceListPage.removeLineByDate(localDate);
        Assert.assertNull(emergencyServiceListPage.getEmployeeKeyOnDate(localDate));
    }
}
