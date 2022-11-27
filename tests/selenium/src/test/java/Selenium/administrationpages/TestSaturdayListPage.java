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
import java.util.Calendar;
import java.util.Locale;
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
        SaturdayListPage saturdayListPage = new SaturdayListPage(driver);
        saturdayListPage.selectYear("2021");
        saturdayListPage.selectBranch(1);
        Assert.assertEquals(saturdayListPage.getBranchId(), 1);
        /**
         * <p lang=de>
         * Der 03. October 2026 ist ein Feiertag. Weil an diesem Feiertag kein
         * Samstagsteam arbeitet, muss nach dem Feiertag das Team arbeiten, dass
         * sonst am 03.10. dran gewesen wäre.
         * </p>
         */
        Calendar saturdayCalendar = Calendar.getInstance(Locale.GERMANY);
        saturdayCalendar.set(2026, Calendar.SEPTEMBER, 26);
        Assert.assertEquals(saturdayListPage.getTeamIdOnDate(saturdayCalendar.getTime()), 0);
        saturdayCalendar.set(2026, Calendar.OCTOBER, 3);
        Assert.assertEquals(saturdayListPage.teamIdOnDateIsMissing(saturdayCalendar.getTime()), true);
        saturdayCalendar.set(2026, Calendar.OCTOBER, 10);
        Assert.assertEquals(saturdayListPage.getTeamIdOnDate(saturdayCalendar.getTime()), 1);
    }
}
