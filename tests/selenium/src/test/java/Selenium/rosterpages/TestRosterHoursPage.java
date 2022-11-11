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
package Selenium.rosterpages;

import Selenium.Absence;
import Selenium.Employee;
import Selenium.MenuFragment;
import Selenium.PropertyFile;
import Selenium.Roster;
import Selenium.RosterItem;
import Selenium.ScreenShot;
import Selenium.absencepages.AbsenceEmployeePage;
import Selenium.signin.SignInPage;
import java.time.LocalDate;
import java.time.Month;
import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.Locale;
import java.util.Optional;
import org.testng.annotations.Test;
import org.testng.Assert;

import org.openqa.selenium.WebDriver;
import static org.testng.Assert.assertEquals;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;

/**
 *
 * @author Mandelkow
 */
public class TestRosterHoursPage {

    WebDriver driver;

    @Test(enabled = true)/*failed*/
    public void testDateNavigation() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = propertyFile.getPdrUserPassword();
        String pdr_user_name = propertyFile.getPdrUserName();
        signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);
        Assert.assertEquals(rosterHoursPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        Workforce workforce = new Workforce();
        Optional<Employee> firstEmployeeOptional = workforce.getListOfEmployees().values().stream().findFirst();
        if (firstEmployeeOptional.isEmpty()) {
            throw new Exception("No employee was found in the workforce. There has to be at least one employee!");
        }
        String firstEmployeeLastName = firstEmployeeOptional.get().getLastName();
        rosterHoursPage.selectMonth("Juni");
        rosterHoursPage.selectYear("2020");
        rosterHoursPage.selectEmployee(firstEmployeeLastName);
        Assert.assertEquals("Juni", rosterHoursPage.getMonth());
        Assert.assertEquals("2020", rosterHoursPage.getYear());
        Assert.assertEquals(firstEmployeeLastName, rosterHoursPage.getEmployeeName());
    }

    @Test(enabled = true)/*failed*/
    public void testRosterDispay() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = propertyFile.getPdrUserPassword();
        String pdr_user_name = propertyFile.getPdrUserName();
        signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);
        Assert.assertEquals(rosterHoursPage.getUserNameText(), pdr_user_name);

        /**
         * Move to specific month:
         */
        /*

        Workforce workforce = new Workforce();
        Optional<Employee> firstEmployeeOptional = workforce.getListOfEmployees().values().stream().findFirst();
        if (firstEmployeeOptional.isEmpty()) {
            throw new Exception("No employee was found in the workforce. There has to be at least one employee!");
        }
        int firstEmployeeId = firstEmployeeOptional.get().getEmployeeId();
         */
 /*
        Test if the correct roster information is displayed:
         */
        Roster roster = new Roster();
        HashMap<LocalDate, HashMap> listOfRosterDays = roster.getListOfRosterDays();
        Optional<HashMap> firstRosterDayOptional = listOfRosterDays.values().stream().findFirst();
        if (firstRosterDayOptional.isEmpty()) {
            throw new Exception("No roster day was found in the roster. There has to be at least one roster day!");
        }
        Optional<RosterItem> firstRosterItemOptional = firstRosterDayOptional.get().values().stream().findFirst();
        if (firstRosterItemOptional.isEmpty()) {
            throw new Exception("No roster item was found in the roster day. There has to be at least one roster item!");
        }
        RosterItem firstRosterItem = firstRosterItemOptional.get();
        int employeeId = firstRosterItem.getEmployeeId();

        for (HashMap<Integer, RosterItem> rosterDay : listOfRosterDays.values()) {
            LocalDate dayInRoster = rosterDay.values().stream().findFirst().get().getLocalDate();
            RosterItem rosterItem = roster.getRosterItemByEmployeeId(dayInRoster, employeeId);
            if (null == rosterItem) {
                /**
                 * <p lang=de>Wir haben weiter oben abgesichert, dass es
                 * mindestens ein roster item gibt. Wir können daher hier ohne
                 * Bedenken die Schleife abkürzen und weitergehen. Das passiert
                 * immer dann, wenn es an einem Tag einen roster gibt, an dem
                 * dieser eine Employee nicht eingepant ist.</p>
                 */
                continue;
            }
            LocalDate rosterLocalDate = rosterItem.getLocalDate();
            /**
             * Go to page:
             */
            rosterHoursPage.selectMonth(rosterLocalDate.format(DateTimeFormatter.ofPattern("MMMM", Locale.GERMANY)));
            rosterHoursPage.selectYear(rosterLocalDate.format(DateTimeFormatter.ofPattern("yyyy", Locale.GERMANY)));
            rosterHoursPage.selectEmployee(rosterItem.getEmployeeName());
            RosterItem foundRosterItem = rosterHoursPage.getRosterOnDate(rosterLocalDate);
            /**
             * Test if the values match:
             */
            Assert.assertEquals(foundRosterItem.getLocalDate(), rosterLocalDate);
            Assert.assertEquals(foundRosterItem.getDutyStart(), rosterItem.getDutyStart());
            Assert.assertEquals(foundRosterItem.getDutyEnd(), rosterItem.getDutyEnd());
        }
    }

    public void testAbsenceDispay() throws Exception {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_password = propertyFile.getPdrUserPassword();
        String pdr_user_name = propertyFile.getPdrUserName();
        signInPage.loginValidUser(pdr_user_name, pdr_user_password);
        RosterHoursPage rosterHoursPage = new RosterHoursPage(driver);
        Assert.assertEquals(rosterHoursPage.getUserNameText(), pdr_user_name);

        /**
         * Test if absence information is displayed:
         *
         * @todo If absences will ever be written to from a json data file, use
         * that instead of hardcoding the values here!
         */
        Workforce workforce = new Workforce();
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToAbsenceEdit);
        AbsenceEmployeePage absenceEmployeePage = new AbsenceEmployeePage();

        absenceEmployeePage = absenceEmployeePage.goToYear(2020);
        absenceEmployeePage = absenceEmployeePage.goToEmployee(5);
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("01.07.2020", "01.07.2020", 8, "Foo comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("02.07.2020", "02.07.2020", 8, "Bar comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("03.07.2020", "03.07.2020", 8, "Baz comment", "not_yet_approved"); // 1 = Urlaub
        absenceEmployeePage = absenceEmployeePage.createNewAbsence("01.07.2020", "01.07.2020", 8, "123 comment", "not_yet_approved"); // 1 = Urlaub
        Absence currentAbsence;
        currentAbsence = absenceEmployeePage.getExistingAbsence("01.07.2020", 5);
        assertEquals(currentAbsence.getCommentString(), "Foo comment");
        assertEquals(currentAbsence.getDurationString(), "1");
        assertEquals(currentAbsence.getEmployeeId(), 5);
        assertEquals(currentAbsence.getStartDateString(), "01.07.2020");
        assertEquals(currentAbsence.getEndDateString(), "01.07.2020");

        rosterHoursPage = new RosterHoursPage(driver);
        rosterHoursPage.selectEmployee(workforce.getEmployeeNameById(5));
        rosterHoursPage.selectMonth("Juli");
        rosterHoursPage.selectYear("2020");

        String absenceString = rosterHoursPage.getAbsenceStringOnLocalDate(LocalDate.of(2020, Month.JULY, 1));
        Assert.assertEquals(absenceString, "Elternzeit");
    }

    @BeforeMethod
    public void setUp() {
        Selenium.driver.Wrapper.createNewDriver();
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        if (testResult.getStatus() != ITestResult.FAILURE) {
            driver.quit();
        }
    }

}
