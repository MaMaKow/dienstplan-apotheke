/*
 * Copyright (C) 2021 Martin Mandelkow <netbeans@martin-mandelkow.de>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
package Selenium.principlerosterpages;

import Selenium.PrincipleRoster;
import Selenium.PrincipleRosterDay;
import Selenium.PrincipleRosterItem;
import Selenium.rosterpages.Workforce;
import java.text.ParseException;
import java.time.DayOfWeek;
import java.time.LocalTime;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
public class TestDayPage extends Selenium.TestPage {

    @Test(dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})
    public void testDateNavigation() throws Exception {
        /**
         * Sign in:
         */
        super.signIn();
        DayPage dayPage = new DayPage(driver);

        /**
         * Move to specific month:
         */
        dayPage.goToBranch(2);
        dayPage.goToBranch(1);
        dayPage.goToAlternation(1);
        dayPage.goToAlternation(0);
        dayPage.goToWeekday(1);
        dayPage.goToWeekday(7);
        dayPage.goToWeekday(5);
        Assert.assertEquals(5, dayPage.getWeekdayIndex());
        Assert.assertEquals(0, dayPage.getAlternationId());
        Assert.assertEquals(1, dayPage.getBranchId());
    }

    @Test(dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation", "testRosterChangeDragAndDrop"})
    public void testRosterDisplay() throws Exception {
        /**
         * Sign in:
         */
        super.signIn();
        DayPage dayPage = new DayPage(driver);

        /**
         * Move to specific principle roster:
         */
        int branchId = 1;
        int alternationId = 0;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        int employeeKey = 4; //TODO: Hier könnte eine for-Schleife stehen. Die geht dann alle employees in principleRosterMonday durch.
        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        PrincipleRosterDay principleRosterMonday = principleRoster.getPrincipleRosterDay(dayOfWeek);
        PrincipleRosterItem principleRosterItem = principleRosterMonday.getPrincipleRosterItemByEmployeeKey(employeeKey);
        /**
         * @todo Read workforce and networkOfBranchOffices only once per suite.
         * Inject it to the tests.
         */
        Workforce workforce = new Workforce();

        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);
        PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeKey(employeeKey);
        softAssert.assertEquals(rosterItemRead.getEmployeeLastName(workforce), principleRosterItem.getEmployeeLastName(workforce));
        softAssert.assertEquals(rosterItemRead.getBranchId(), principleRosterItem.getBranchId());
        softAssert.assertEquals(rosterItemRead.getDutyStart(), principleRosterItem.getDutyStart());
        softAssert.assertEquals(rosterItemRead.getDutyEnd(), principleRosterItem.getDutyEnd());
        softAssert.assertEquals(rosterItemRead.getBreakStart(), principleRosterItem.getBreakStart());
        softAssert.assertEquals(rosterItemRead.getBreakEnd(), principleRosterItem.getBreakEnd());
        //Assert.assertEquals("", rosterItem.getComment(),); //TODO: Kommentare
        softAssert.assertAll();
    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})/*new*/
    public void testRosterChange() throws Exception {
        /**
         * Sign in:
         */
        super.signIn();
        DayPage dayPage = new DayPage(driver);

        /**
         * Move to specific month:
         */
        int branchId = 1;
        int alternationId = 0;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        int employeeKey = 4;

        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);

        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        PrincipleRosterDay principleRosterMonday = principleRoster.getPrincipleRosterDay(dayOfWeek);
        PrincipleRosterItem principleRosterItem = principleRosterMonday.getPrincipleRosterItemByEmployeeKey(employeeKey);
        /**
         * principleRosterItemChanged holds the values, into which the
         * principleRosterItem will be changed.
         */
        int employeeKeyChanged = 13;
        PrincipleRosterItem principleRosterItemChanged = new PrincipleRosterItem(employeeKeyChanged, dayOfWeek, LocalTime.of(11, 05), LocalTime.of(16, 10), LocalTime.of(12, 35), LocalTime.of(13, 10), null, branchId);

        dayPage.changeRosterInputDutyStart(employeeKey, principleRosterItemChanged.getDutyStart());
        dayPage.changeRosterInputDutyEnd(employeeKey, principleRosterItemChanged.getDutyEnd());
        dayPage.changeRosterInputBreakStart(employeeKey, principleRosterItemChanged.getBreakStart());
        dayPage.changeRosterInputBreakEnd(employeeKey, principleRosterItemChanged.getBreakEnd());
        dayPage.changeRosterInputEmployee(employeeKey, principleRosterItemChanged.getEmployeeKey());
        dayPage.rosterFormSubmit();
        PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeKey(employeeKeyChanged);
        softAssert.assertEquals(rosterItemRead.getDutyStart(), principleRosterItemChanged.getDutyStart());
        softAssert.assertEquals(rosterItemRead.getDutyEnd(), principleRosterItemChanged.getDutyEnd());
        softAssert.assertEquals(rosterItemRead.getBreakStart(), principleRosterItemChanged.getBreakStart());
        softAssert.assertEquals(rosterItemRead.getBreakEnd(), principleRosterItemChanged.getBreakEnd());
        softAssert.assertEquals(rosterItemRead.getEmployeeKey(), principleRosterItemChanged.getEmployeeKey());

        /**
         * Revert the changes for the next test:
         */
        dayPage.changeRosterInputDutyStart(employeeKeyChanged, principleRosterItem.getDutyStart());
        dayPage.changeRosterInputDutyEnd(employeeKeyChanged, principleRosterItem.getDutyEnd());
        dayPage.changeRosterInputBreakStart(employeeKeyChanged, principleRosterItem.getBreakStart());
        dayPage.changeRosterInputBreakEnd(employeeKeyChanged, principleRosterItem.getBreakEnd());
        dayPage.changeRosterInputEmployee(employeeKeyChanged, employeeKey);
        dayPage.rosterFormSubmit();
        softAssert.assertAll();
    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate"})/*new*/
    public void testRosterCopyAlternation() {
        /**
         * Sign in:
         */
        super.signIn();
        DayPage dayPage = new DayPage(driver);

        /**
         * Copy the alternation:
         */
        dayPage.copyAlternation();
    }

    @Test()
    public void testRosterCreate() throws Exception {
        /**
         * Sign in:
         */
        super.signIn();
        DayPage dayPage = new DayPage(driver);
        Workforce workforce = new Workforce();

        /**
         * Move to specific branch:
         */
        int branchId = 1;
        dayPage.goToBranch(branchId);
        /**
         * alternationId MUST be 0! There is no other alternation yet. Therefore
         * there is no SELECT element for it yet.
         *
         */
        int alternationId = 0;
        dayPage.goToAlternation(alternationId);

        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        // Iterate through all weekdays
        for (DayOfWeek dayOfWeek : principleRoster.getAllWeekdays()) {
            PrincipleRosterDay principleRosterDay = principleRoster.getPrincipleRosterDay(dayOfWeek);

            dayPage.goToWeekday(dayOfWeek);
            dayPage.addRosterRow();
            for (PrincipleRosterItem principleRosterItem : principleRosterDay.getlistOfPrincipleRosterItems().values()) {
                dayPage.createNewRosterItem(principleRosterItem);
            }
            for (PrincipleRosterItem principleRosterItem : principleRosterDay.getlistOfPrincipleRosterItems().values()) {
                PrincipleRosterItem principleRosterItemRead = dayPage.getRosterItemByEmployeeKey(principleRosterItem.getEmployeeKey());
                softAssert.assertEquals(principleRosterItemRead.getDutyStart(), principleRosterItem.getDutyStart());
                softAssert.assertEquals(principleRosterItemRead.getEmployeeLastName(workforce), principleRosterItem.getEmployeeLastName(workforce));
                softAssert.assertEquals(principleRosterItemRead.getDutyEnd(), principleRosterItem.getDutyEnd());
                softAssert.assertEquals(principleRosterItemRead.getBreakStart(), principleRosterItem.getBreakStart());
                softAssert.assertEquals(principleRosterItemRead.getBreakEnd(), principleRosterItem.getBreakEnd());
                softAssert.assertAll();
            }
        }
    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation", "testRosterChangeDragAndDrop"})/*new*/
    public void testRosterChangePlotErrors() throws ParseException, Exception {
        /**
         * Sign in:
         */
        super.signIn();
        DayPage dayPage = new DayPage(driver);
        Workforce workforce = new Workforce();

        /**
         * Move to specific month:
         */
        int branchId = 1;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        int employeeKey = 4; //TODO: Hier könnte eine for-Schleife stehen. Die geht dann alle employees in principleRosterMonday durch.
        int alternationId = 0;
        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        PrincipleRosterDay principleRosterDay = principleRoster.getPrincipleRosterDay(dayOfWeek);
        PrincipleRosterItem principleRosterItem = principleRosterDay.getPrincipleRosterItemByEmployeeKey(employeeKey);

        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);

        /**
         * <p lang=de>
         * Teste die Reaktion auf fehlenden Dienststart
         * </p>
         */
        dayPage.changeRosterInputDutyStart(employeeKey, null);
        dayPage.rosterFormSubmit();
        PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeKey(employeeKey);
        softAssert.assertEquals(rosterItemRead.getDutyStart(), principleRosterItem.getDutyStart());
        softAssert.assertEquals(rosterItemRead.getEmployeeLastName(workforce), principleRosterItem.getEmployeeLastName(workforce));
        softAssert.assertAll();
    }

    @Test(enabled = true, dependsOnMethods = {"testRosterCreate", "testRosterCopyAlternation"})/*new*/
    public void testRosterChangeDragAndDrop() throws Exception {
        /**
         * Sign in:
         */
        super.signIn();
        DayPage dayPage = new DayPage(driver);
        Workforce workforce = new Workforce();

        /**
         * Move to specific month:
         */
        int branchId = 1;
        DayOfWeek dayOfWeek = DayOfWeek.MONDAY;
        int employeeKey = 4; //TODO: Hier könnte eine for-Schleife stehen. Die geht dann alle employees in principleRosterMonday durch.
        int alternationId = 0;

        dayPage.goToBranch(branchId);
        dayPage.goToAlternation(alternationId);
        dayPage.goToWeekday(dayOfWeek);
        PrincipleRoster principleRoster = new PrincipleRoster(branchId, alternationId);
        PrincipleRosterDay principleRosterDay = principleRoster.getPrincipleRosterDay(dayOfWeek);
        PrincipleRosterItem principleRosterItem = principleRosterDay.getPrincipleRosterItemByEmployeeKey(employeeKey);
        try {
            int dutyOffset = 90;
            int breakOffset = 120;
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), employeeKey, dutyOffset, "duty");
            dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), employeeKey, breakOffset, "break");
            dayPage.rosterFormSubmit();
            PrincipleRosterItem rosterItemRead = dayPage.getRosterItemByEmployeeKey(employeeKey);
            /**
             * <p lang=en>CAVE: rosterItem is read after the first dragAndDrop.
             * Those changes are reverted afterwards. The Assertions work on the
             * firstly changed values.</p>
             */
            softAssert.assertEquals(rosterItemRead.getEmployeeLastName(workforce), principleRosterItem.getEmployeeLastName(workforce));
            softAssert.assertEquals(rosterItemRead.getDutyStart(), principleRosterItem.getDutyStart().plusMinutes(dutyOffset));
            softAssert.assertEquals(rosterItemRead.getDutyEnd(), principleRosterItem.getDutyEnd().plusMinutes(dutyOffset));
            softAssert.assertEquals(rosterItemRead.getBreakStart(), principleRosterItem.getBreakStart().plusMinutes(breakOffset));
            softAssert.assertEquals(rosterItemRead.getBreakEnd(), principleRosterItem.getBreakEnd().plusMinutes(breakOffset));
            /**
             * Revert the changes for the next test:
             */
            dayPage.changeRosterInputDutyStart(employeeKey, principleRosterItem.getDutyStart());
            dayPage.changeRosterInputDutyEnd(employeeKey, principleRosterItem.getDutyEnd());
            //dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), employeeKey, -dutyOffset, "duty");
            dayPage.changeRosterInputBreakStart(employeeKey, principleRosterItem.getBreakStart());
            dayPage.changeRosterInputBreakEnd(employeeKey, principleRosterItem.getBreakEnd());
            //dayPage.changeRosterByDragAndDrop(dayPage.getUnixTime(), employeeKey, -breakOffset, "break");
            dayPage.rosterFormSubmit();
            softAssert.assertAll();
        } catch (ParseException ex) {
            Logger.getLogger(TestDayPage.class.getName()).log(Level.SEVERE, null, ex);
        } catch (InterruptedException ex) {
            Logger.getLogger(TestDayPage.class.getName()).log(Level.SEVERE, null, ex);
        }

    }
}
