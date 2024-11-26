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
package Selenium.overtimepages;

import Selenium.HomePage;
import Selenium.LogoutPage;
import Selenium.Overtime;
import Selenium.User;
import Selenium.UserRegistry;
import Selenium.signin.SignInPage;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.time.LocalDate;
import java.time.Month;
import java.util.Base64;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.CloseableHttpClient;
import org.apache.http.impl.client.HttpClients;
import org.apache.http.util.EntityUtils;
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
public class TestOvertimeEmployeePage extends Selenium.TestPage {

    @Test()
    public void testDisplay() {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);

        /**
         * Move to specific year:
         */
        LocalDate localDate0 = LocalDate.of(2019, Month.JANUARY, 2);
        LocalDate localDate1 = LocalDate.of(2019, Month.MARCH, 3);
        LocalDate localDate2 = LocalDate.of(2019, Month.JULY, 5);
        LocalDate localDate3 = LocalDate.of(2019, Month.DECEMBER, 24);
        overtimeEmployeePage.selectYear(localDate0.getYear());
        overtimeEmployeePage.selectEmployee(7);
        /**
         * Create new overtime:
         */
        overtimeEmployeePage.addNewOvertime(localDate0, 8, "Foo");
        overtimeEmployeePage.addNewOvertime(localDate1, 0.5f, "FloatFoo");
        overtimeEmployeePage.addNewOvertime(localDate2, -8, "NoFoo");
        overtimeEmployeePage.addNewOvertime(localDate3, 1, "Bar");
        overtimeEmployeePage.addNewOvertime(localDate3, 99, "Error"); //Should not get inserted
        /**
         * Find the newly created overtime:
         */
        Overtime overtime;
        try {
            overtime = overtimeEmployeePage.getOvertimeByLocalDate(localDate0);
            softAssert.assertEquals(overtime.getBalance(), (float) 8f);
            softAssert.assertEquals(overtime.getHours(), (float) 8f);
            softAssert.assertEquals(overtime.getReason(), "Foo");
            overtime = overtimeEmployeePage.getOvertimeByLocalDate(localDate1);
            softAssert.assertEquals(overtime.getBalance(), (float) 8.5f);
            softAssert.assertEquals(overtime.getHours(), (float) 0.5f);
            softAssert.assertEquals(overtime.getReason(), "FloatFoo");
            overtime = overtimeEmployeePage.getOvertimeByLocalDate(localDate2);
            softAssert.assertEquals(overtime.getBalance(), (float) 0.5f);
            softAssert.assertEquals(overtime.getHours(), (float) -8.0f);
            softAssert.assertEquals(overtime.getReason(), "NoFoo");
            overtime = overtimeEmployeePage.getOvertimeByLocalDate(localDate3);
            softAssert.assertEquals(overtime.getBalance(), (float) 1.5f);
            softAssert.assertEquals(overtime.getHours(), (float) 1.0f);
            softAssert.assertEquals(overtime.getReason(), "Bar");
        } catch (Exception exception) {
            logger.error(exception.getMessage());
            Assert.fail();
        }
        /**
         * remove the created overtime:
         */
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate0);
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate1);
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate2);
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate3);
        softAssert.assertAll();
    }

    @Test(dependsOnMethods = {"testDisplay"})
    public void testDeleteBySimpleUser() throws IOException {
        LogoutPage logoutPage = new LogoutPage();
        logoutPage.logout();
        UserRegistry userRegistry = new UserRegistry();
        User employeeUser = userRegistry.getUserByName("EmployeeUser");
        SignInPage signInPage = new SignInPage(driver);
        try {
            HomePage menuPage = signInPage.loginValidUser(employeeUser.getUserName(), employeeUser.getPassphrase());
            Assert.assertEquals(menuPage.getUserNameText(), employeeUser.getUserName());
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }

        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);
        LocalDate localDate = LocalDate.of(2020, Month.NOVEMBER, 24);// Tuesday 24.11.2020
        overtimeEmployeePage.selectYear(localDate.getYear());
        overtimeEmployeePage.selectEmployee(7); //TODO: Which employee should we choose?
        /**
         * Create new overtime:
         */
        overtimeEmployeePage.addNewOvertime(localDate, 8, "Foo");
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate);
        logoutPage = new LogoutPage();
        logoutPage.logout();

        /**
         * @todo Now test if there has been an email to the administrator about
         * deleted overtimes. Make sure, that selenium_test_user does not have
         * admin privileges. Or use a less privileged user to make the
         * deletions.
         */
        // Fetch emails from MailHog API or Mailtrap API
        String mailHogApiUrl = "http://localhost:8025/api/v2/messages";
        try (CloseableHttpClient httpClient = HttpClients.createDefault()) {
            HttpGet request = new HttpGet(mailHogApiUrl);
            String responseBody = EntityUtils.toString(httpClient.execute(request).getEntity());
            JsonObject jsonObject = JsonParser.parseString(responseBody).getAsJsonObject();
            String base64Body = jsonObject.get("items").getAsJsonArray()
                    .get(0).getAsJsonObject()
                    .get("Content").getAsJsonObject()
                    .get("Body").getAsString();
            // Remove all line breaks and spaces from the Base64 string
            base64Body = base64Body.replaceAll("\\s+", "");  // This will remove spaces, tabs, and line breaks
            byte[] decodedBytes = Base64.getDecoder().decode(base64Body);
            String decodedBody = new String(decodedBytes, StandardCharsets.UTF_8);

            /**
             * Assert that the decoded email body contains expected content
             */
            Assert.assertTrue(decodedBody.contains("Der Benutzer EmployeeUser hat folgenden Überstunden Eintrag gelöscht:"));
            Assert.assertTrue(decodedBody.contains("Teammitglied: Albert Krüger"));
            Assert.assertTrue(decodedBody.contains("Datum: 24.11.2020"));
            Assert.assertTrue(decodedBody.contains("Stunden: 8"));
        }

    }

    @Test(dependsOnMethods = {"testDisplay"})
    public void testEditBySimpleUser() throws IOException {
        LogoutPage logoutPage = new LogoutPage();
        logoutPage.logout();
        UserRegistry userRegistry = new UserRegistry();
        User employeeUser = userRegistry.getUserByName("EmployeeUser");
        SignInPage signInPage = new SignInPage(driver);
        try {
            HomePage menuPage = signInPage.loginValidUser(employeeUser.getUserName(), employeeUser.getPassphrase());
            Assert.assertEquals(menuPage.getUserNameText(), employeeUser.getUserName());
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }

        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);
        LocalDate localDate = LocalDate.of(2020, Month.NOVEMBER, 25);// Wednesday 25.11.2020
        overtimeEmployeePage.selectYear(localDate.getYear());
        overtimeEmployeePage.selectEmployee(7); //TODO: Which employee should we choose?

        /**
         * Create new overtime:
         */
        LocalDate dateNew = LocalDate.of(2020, Month.NOVEMBER, 26);
        float hoursNew = -6;
        String reasonNew = "Baz";
        overtimeEmployeePage.addNewOvertime(localDate, 7, "Bar");
        overtimeEmployeePage.editOvertimeByLocalDate(localDate, dateNew, hoursNew, reasonNew);

        /**
         * Now test if there has been an email to the administrator about
         * deleted overtimes.
         */
        // Fetch emails from MailHog API or Mailtrap API
        String mailHogApiUrl = "http://localhost:8025/api/v2/messages";
        try (CloseableHttpClient httpClient = HttpClients.createDefault()) {
            HttpGet request = new HttpGet(mailHogApiUrl);
            String responseBody = EntityUtils.toString(httpClient.execute(request).getEntity());
            JsonObject jsonObject = JsonParser.parseString(responseBody).getAsJsonObject();
            String base64Body = jsonObject.get("items").getAsJsonArray()
                    .get(0).getAsJsonObject()
                    .get("Content").getAsJsonObject()
                    .get("Body").getAsString();
            // Remove all line breaks and spaces from the Base64 string
            base64Body = base64Body.replaceAll("\\s+", "");  // This will remove spaces, tabs, and line breaks
            byte[] decodedBytes = Base64.getDecoder().decode(base64Body);
            String decodedBody = new String(decodedBytes, StandardCharsets.UTF_8);

            /**
             * Before Assertions, remove the overtime entry:
             */
            overtimeEmployeePage.removeOvertimeByLocalDate(dateNew);
            logoutPage = new LogoutPage();
            logoutPage.logout();

            /**
             * Assert that the decoded email body contains expected content
             */
            //logger.debug(decodedBody);
            // Replace non-printable characters with visible markers for logging
            String expected = "Der Account EmployeeUser hat folgenden Überstundeneintrag geändert:\n"
                    + "Mitarbeitende: Albert Krüger\n"
                    + "Datum: 25.11.2020\n"
                    + "Stunden: 7\n"
                    + "Grund: Bar\n"
                    + "\n"
                    + "zu den neuen Werten:\n"
                    + "Datum: 26.11.2020\n"
                    + "Stunden: -6\n"
                    + "Grund: Baz\n";
            /**
             * String visibleExpected = expected
             * .replace(" ", "[SPACE] ")
             * .replace("\n", "[NEWLINE]\n")
             * .replace("\r", "[CR]\r")
             * .replace("\t", "[TAB]\t");
             *
             * String visibleActual = decodedBody
             * .replace(" ", "[SPACE] ")
             * .replace("\n", "[NEWLINE]\n")
             * .replace("\r", "[CR]\r")
             * .replace("\t", "[TAB]\t");
             *
             */
            // Log the transformed strings for comparison
            //logger.debug("Expected: " + visibleExpected);
            //logger.debug("Actual:   " + visibleActual);
            Assert.assertEquals(decodedBody, expected);
        }

    }
}
