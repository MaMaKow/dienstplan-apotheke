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

import Selenium.Employee;
import Selenium.HomePage;
import Selenium.LogoutPage;
import Selenium.Overtime;
import Selenium.PropertyFile;
import Selenium.User;
import Selenium.UserRegistry;
import Selenium.Utilities.EmailParser;
import Selenium.Utilities.LogCollector;
import static Selenium.driver.Wrapper.DATE_TIME_FORMATTER_DAY_MONTH_YEAR;
import Selenium.rest_api.ApiHandler;
import Selenium.signin.SignInPage;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import java.io.IOException;
import java.net.http.HttpResponse;
import java.nio.charset.StandardCharsets;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.Month;
import java.time.format.DateTimeFormatter;
import java.time.temporal.TemporalAdjusters;
import java.util.Base64;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.CloseableHttpClient;
import org.apache.http.impl.client.HttpClients;
import org.apache.http.util.EntityUtils;
import org.testng.Assert;
import org.testng.annotations.Listeners;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
/**
 *
 * @author Martin Mandelkow <netbeans@martin-mandelkow.de>
 */
@Listeners(Selenium.Utilities.Listener.class)
public class TestOvertimeEmployeePage extends Selenium.TestPage {

    @Test(enabled = true)
    public void testDisplay() {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            LogCollector.error("Sign in failed.");
            Assert.fail();
        }
        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);

        /**
         * Move to specific year:
         */
        LocalDate localDate0 = LocalDate.of(2024, Month.JANUARY, 2);
        LocalDate localDate1 = LocalDate.of(2024, Month.MARCH, 3);
        LocalDate localDate2 = LocalDate.of(2024, Month.JULY, 5);
        LocalDate localDate3 = LocalDate.of(2024, Month.DECEMBER, 24);

        Employee employee = workforce.getEmployeeByFullName("Franziska Hartmann");
        overtimeEmployeePage.selectYear(localDate0.getYear());
        overtimeEmployeePage.selectEmployee(employee.getEmployeeKey());
        /**
         * Create new overtime:
         */
        overtimeEmployeePage.addNewOvertime(localDate0, 8, "Foo" + employee.getFullName());
        overtimeEmployeePage.addNewOvertime(localDate1, 0.5f, "FloatFoo" + employee.getFullName());
        overtimeEmployeePage.addNewOvertime(localDate2, -8, "NoFoo" + employee.getFullName());
        overtimeEmployeePage.addNewOvertime(localDate3, 1, "Bar" + employee.getFullName());
        overtimeEmployeePage.addNewOvertime(localDate3, 99, "Error" + employee.getFullName()); // Should not get
                                                                                               // inserted
        /**
         * Find the newly created overtime:
         */
        Overtime overtime;
        try {
            overtime = overtimeEmployeePage.getOvertimeByLocalDate(localDate0);
            softAssert.assertEquals(overtime.getBalance(), (float) 8f);
            softAssert.assertEquals(overtime.getHours(), (float) 8f);
            softAssert.assertEquals(overtime.getReason(), "Foo" + employee.getFullName());
            overtime = overtimeEmployeePage.getOvertimeByLocalDate(localDate1);
            softAssert.assertEquals(overtime.getBalance(), (float) 8.5f);
            softAssert.assertEquals(overtime.getHours(), (float) 0.5f);
            softAssert.assertEquals(overtime.getReason(), "FloatFoo" + employee.getFullName());
            overtime = overtimeEmployeePage.getOvertimeByLocalDate(localDate2);
            softAssert.assertEquals(overtime.getBalance(), (float) 0.5f);
            softAssert.assertEquals(overtime.getHours(), (float) -8.0f);
            softAssert.assertEquals(overtime.getReason(), "NoFoo" + employee.getFullName());
            overtime = overtimeEmployeePage.getOvertimeByLocalDate(localDate3);
            softAssert.assertEquals(overtime.getBalance(), (float) 1.5f);
            softAssert.assertEquals(overtime.getHours(), (float) 1.0f);
            softAssert.assertEquals(overtime.getReason(), "Bar" + employee.getFullName());
        } catch (Exception exception) {
            LogCollector.error(exception.getMessage());
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

    @Test(dependsOnMethods = { "testDisplay" })
    public void testDeleteBySimpleUser() throws IOException {
        int currentYear = LocalDate.now().getYear();
        LogCollector.debug("testDeleteBySimpleUser");
        LogoutPage logoutPage = new LogoutPage();
        logoutPage.logout();
        User employeeUser = UserRegistry.getUserByName("EmployeeUser");
        SignInPage signInPage = new SignInPage(driver);
        try {
            HomePage menuPage = signInPage.loginValidUser(employeeUser.getUserName(), employeeUser.getPassphrase());
            Assert.assertEquals(menuPage.getUserNameText(), employeeUser.getUserName());
        } catch (Exception exception) {
            LogCollector.error("Sign in failed.");
            Assert.fail();
        }

        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);
        LocalDate localDate = LocalDate.of(currentYear, Month.NOVEMBER, 24);
        String localDateFormatted = localDate.format(DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        overtimeEmployeePage.selectYear(localDate.getYear());
        Employee employee = workforce.getEmployeeByFullName("Elisabeth Lehmann");
        overtimeEmployeePage.selectEmployee(employee.getEmployeeKey());
        /**
         * Create new overtime:
         */
        overtimeEmployeePage.addNewOvertime(localDate, 8, "Foo " + employee.getFullName());
        try {
            overtimeEmployeePage.getOvertimeByLocalDate(localDate);
        } catch (Exception e) {
            Assert.fail("Error while trying to find the created overtime.");
        }
        overtimeEmployeePage.removeOvertimeByLocalDate(localDate);
        Assert.assertThrows(Exception.class,
                () -> {
                    /**
                     * There should not be an overtime left on that date.
                     */
                    LogCollector.debug("There should not be an overtime left on " + localDateFormatted);
                    LogCollector.debug("There should now be an exception being thrown.");
                    overtimeEmployeePage.getOvertimeByLocalDate(localDate);
                });

        logoutPage = new LogoutPage();
        logoutPage.logout();

        /**
         * @todo Now test if there has been an email to the administrator about
         *       deleted overtimes. Make sure, that selenium_test_user does not have
         *       admin privileges. Or use a less privileged user to make the
         *       deletions.
         */
        // Fetch emails from MailHog API or Mailtrap API
        String mailHogApiUrl = "http://localhost:8025/api/v2/messages";
        try (CloseableHttpClient httpClient = HttpClients.createDefault()) {
            HttpGet request = new HttpGet(mailHogApiUrl);
            String responseBody = EntityUtils.toString(httpClient.execute(request).getEntity());
            LogCollector.debug(responseBody);
            JsonObject jsonObject = JsonParser.parseString(responseBody).getAsJsonObject();
            JsonArray listOfEmails = jsonObject.get("items").getAsJsonArray();
            LogCollector.debug("List of emails:");
            LogCollector.debug(listOfEmails.toString());
            if (listOfEmails.isEmpty()) {
                Assert.fail("Keine E-Mail in MailHog gefunden. "
                        + "Die Anwendung hat keine Änderungs-Benachrichtigung gesendet. Oder der Zugriff auf Mailhog ist gestört.");
            }

            for (JsonElement currentEmail : listOfEmails) {
                EmailParser emailParser = new EmailParser(currentEmail.toString());
                String subject = emailParser.getSubject();
                LogCollector.info("Subject: " + subject);
                if (null == subject || subject.isEmpty()) {
                    LogCollector.warn("Subject is missing or null in email");
                    continue;
                }
                if (!"PDR: Ein Überstundeneintrag wurde gelöscht.".equals(subject)) {
                    continue;
                }
                String base64Body = jsonObject.get("items").getAsJsonArray()
                        .get(0).getAsJsonObject()
                        .get("Content").getAsJsonObject()
                        .get("Body").getAsString();
                // Remove all line breaks and spaces from the Base64 string
                base64Body = base64Body.replaceAll("\\s+", ""); // This will remove spaces, tabs, and line breaks
                byte[] decodedBytes = Base64.getDecoder().decode(base64Body);
                String decodedBody = new String(decodedBytes, StandardCharsets.UTF_8);

                /**
                 * Assert that the decoded email body contains expected content
                 */
                LogCollector.debug(decodedBody);
                // Split the email content into lines
                String[] emailLines = decodedBody.split("\\r?\\n");
                // Expected content for each line
                String[] expectedLines = {
                        "Der Account EmployeeUser hat folgenden Überstundeneintrag gelöscht:",
                        "Mitarbeitende: " + employee.getFullName(),
                        "Datum: " + localDateFormatted,
                        "Stunden: 8",
                        "Grund: Foo" + " " + employee.getFullName()
                };
                // Ensure the email contains the correct number of lines
                softAssert.assertEquals(emailLines.length, expectedLines.length,
                        "Unexpected number of lines in the email.");

                // Compare each line
                for (int i = 0; i < expectedLines.length; i++) {
                    softAssert.assertEquals(emailLines[i].trim(), expectedLines[i],
                            "Mismatch at line " + (i + 1) + " = " + emailLines[i]);
                }
                softAssert.assertAll();
            }
        }

    }

    @Test(dependsOnMethods = { "testDisplay", "testDeleteBySimpleUser" })
    public void testEditBySimpleUser() throws IOException {
        int currentYear = LocalDate.now().getYear();
        LogCollector.debug("testEditBySimpleUser");
        LogoutPage logoutPage = new LogoutPage();
        logoutPage.logout();
        UserRegistry userRegistry = new UserRegistry();
        User employeeUser = userRegistry.getUserByName("EmployeeUser");
        SignInPage signInPage = new SignInPage(driver);
        try {
            HomePage menuPage = signInPage.loginValidUser(employeeUser.getUserName(), employeeUser.getPassphrase());
            Assert.assertEquals(menuPage.getUserNameText(), employeeUser.getUserName());
        } catch (Exception exception) {
            LogCollector.error("Sign in failed.");
            Assert.fail();
        }

        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);
        LocalDate localDate = LocalDate.of(currentYear, Month.NOVEMBER, 25);
        overtimeEmployeePage.selectYear(localDate.getYear());
        Employee employee = workforce.getEmployeeByFullName("Albert Krüger");
        overtimeEmployeePage.selectEmployee(employee.getEmployeeKey());

        /**
         * Create new overtime:
         */
        LocalDate dateNew = LocalDate.of(currentYear, Month.NOVEMBER, 26);
        float hoursNew = -6;
        String reasonNew = "Baz";

        LogCollector.debug("addNewOvertime");
        overtimeEmployeePage.addNewOvertime(localDate, 7, "Bar");

        // Verify the entry was NOT created in a far-future year (expected behavior)
        try {
            overtimeEmployeePage.selectYear(currentYear + 4);
            overtimeEmployeePage.getOvertimeByLocalDate(localDate);
            LogCollector.error("Der Eintrag wurde gefunden.");
        } catch (Exception ex) {
            LogCollector.error("Der Eintrag wurde nicht erstellt.");
        }

        LogCollector.debug("before editOvertimeByLocalDate");
        overtimeEmployeePage.editOvertimeByLocalDate(localDate, dateNew, hoursNew, reasonNew);
        LogCollector.debug("after editOvertimeByLocalDate");

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
            JsonArray items = jsonObject.get("items").getAsJsonArray();

            // Cleanup BEFORE assertions so DB is always clean even on test failure
            overtimeEmployeePage.removeOvertimeByLocalDate(dateNew);
            logoutPage = new LogoutPage();
            logoutPage.logout();

            // Guard: fail clearly if no email arrived instead of cryptic
            // IndexOutOfBoundsException
            if (items.isEmpty()) {
                Assert.fail("Keine E-Mail in MailHog gefunden. "
                        + "Die Anwendung hat keine Änderungs-Benachrichtigung gesendet.");
            }

            String base64Body = items.get(0).getAsJsonObject()
                    .get("Content").getAsJsonObject()
                    .get("Body").getAsString();
            base64Body = base64Body.replaceAll("\\s+", "");
            byte[] decodedBytes = Base64.getDecoder().decode(base64Body);
            String decodedBody = new String(decodedBytes, StandardCharsets.UTF_8);

            String[] emailLines = decodedBody.split("\\r?\\n");
            String[] expectedLines = {
                    "Der Account EmployeeUser hat folgenden Überstundeneintrag geändert:",
                    "Mitarbeitende: Albert Krüger",
                    "Datum: 25.11." + currentYear,
                    "Stunden: 7",
                    "Grund: Bar",
                    "",
                    "zu den neuen Werten:",
                    "Datum: 26.11." + currentYear,
                    "Stunden: -6",
                    "Grund: Baz"
            };
            for (int i = 0; i < expectedLines.length; i++) {
                softAssert.assertEquals(emailLines[i].trim(), expectedLines[i],
                        "Mismatch at line " + (i + 1) + " = " + emailLines[i]);
            }
            softAssert.assertAll();
        }
    }

    /**
     * Ich möchte einen edge-case testen. Das Problem: Alte Überstundeneinträge
     * werden nach 3 Jahren gelöscht. So kann ein Überstundeneintrag folgende
     * Daten besitzen: employee=25; date=01.01.2020; Stunden=1,5; Saldo=35 Der
     * Saldo ergibt sich aus der Summe von Einträgen, die vor dem 01.01.2020
     * lagen. Diese Einträge sind aber ab dem 01.01.2024 nicht mehr verfügbar.
     *
     * Der Fehler: Dabei kam es zu fehlerhafter Berechnung der Saldo Stunden.
     * Alle Stunden vor dem Löschdatum wurden einfach ignoriert. Der Saldo
     * begann bei 0.
     *
     * Das Löschen findet erst beim Login statt. Es findet beim login aber auch
     * nur statt, wenn es nicht bereits einmal innerhalb von 24 Stunden
     * ausgelöst wurde. Der Test muss also sicherstellen, dass Daten aus der
     * Vergangenheit gelöscht werden. Dann kann getestet werden, ob der Saldo
     * korrekt erfasst und berechnet wird.
     */
    @Test(dependsOnMethods = { "testDisplay", "testDeleteBySimpleUser", "testEditBySimpleUser" }, enabled = true)
    // @Test(dependsOnMethods = {}, enabled = true)
    public void testRecalculateBalances() throws IOException, Exception {
        /**
         * Zunächst brauchen wir einen Mitarbeiter, der bereits vor sechs Jahren
         * oder früher existiert hat: z.B. Alexandra Probst (Pharmazieingenieur)
         */
        String employeeFullName = "Alexandra Probst";
        Employee employee = workforce.getEmployeeByFullName(employeeFullName);
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            LogCollector.error("Sign in failed.");
            Assert.fail();
        }
        OvertimeEmployeePage overtimeEmployeePage = new OvertimeEmployeePage(driver);

        /**
         * Calculate dates dynamically based on current date to ensure test
         * remains valid. PHP shows years from (now - 6 years) to (now + 2
         * years). We create entries at (now - 5 years) which will be old enough
         * to be deleted after 3 years, and entries from (now - 4 years) to now
         * which should remain.
         */
        LocalDate now = LocalDate.now();
        int oldYear = now.getYear() - 5; // Will be deleted (older than 3 years, outside 6-year window soon)
        int year1 = now.getYear() - 4; // Should remain
        int year2 = now.getYear() - 3; // Should remain
        int year3 = now.getYear() - 2; // Should remain
        int year4 = now.getYear() - 1; // Should remain
        int currentYear = now.getYear(); // Should remain

        LocalDate localDate0 = LocalDate.of(oldYear, Month.JANUARY, 3);
        LocalDate localDate1 = LocalDate.of(oldYear, Month.MARCH, 3);
        LocalDate localDate2 = LocalDate.of(oldYear, Month.JULY, 5);
        LocalDate localDate3 = LocalDate.of(oldYear, Month.DECEMBER, 1);
        LocalDate localDate4 = LocalDate.of(year1, Month.DECEMBER, 1);
        LocalDate localDate5 = LocalDate.of(year2, Month.DECEMBER, 1);
        LocalDate localDate6 = LocalDate.of(year3, Month.DECEMBER, 1);
        LocalDate localDate7 = LocalDate.of(year4, Month.DECEMBER, 1);
        LocalDate localDate8 = LocalDate.of(currentYear, Month.JANUARY, 15);

        LogCollector.info("Testing with old year (to be deleted): " + oldYear);
        LogCollector.info("Testing with recent years (to remain): " + year1 + " to " + currentYear);

        overtimeEmployeePage.selectYearTry(localDate0.getYear());
        overtimeEmployeePage.selectEmployee(employee.getEmployeeKey());
        /**
         * Create new overtime:
         */
        Overtime foundOvertime;
        overtimeEmployeePage.addNewOvertime(localDate0, 8, "Foo0" + employee.getFullName());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate0);
        Assert.assertEquals(foundOvertime.getBalance(), (float) 8f);
        Assert.assertEquals(foundOvertime.getHours(), (float) 8f);
        Assert.assertEquals(foundOvertime.getReason(), "Foo0" + employee.getFullName());

        overtimeEmployeePage.addNewOvertime(localDate1, 0.5f, "Foo1" + employee.getFullName());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate1);
        Assert.assertEquals(foundOvertime.getBalance(), (float) 8.5f);
        Assert.assertEquals(foundOvertime.getHours(), (float) 0.5f);
        Assert.assertEquals(foundOvertime.getReason(), "Foo1" + employee.getFullName());

        overtimeEmployeePage.addNewOvertime(localDate2, 2, "Foo2" + employee.getFullName());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate2);
        Assert.assertEquals(foundOvertime.getBalance(), (float) 10.5f);
        Assert.assertEquals(foundOvertime.getHours(), (float) 2f);
        Assert.assertEquals(foundOvertime.getReason(), "Foo2" + employee.getFullName());

        overtimeEmployeePage.addNewOvertime(localDate3, 1, "Foo3" + employee.getFullName());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate3);
        Assert.assertEquals(foundOvertime.getBalance(), (float) 11.5f);
        Assert.assertEquals(foundOvertime.getHours(), (float) 1);
        Assert.assertEquals(foundOvertime.getReason(), "Foo3" + employee.getFullName());

        overtimeEmployeePage.addNewOvertime(localDate4, 6, "Foo4" + employee.getFullName());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate4);
        Assert.assertEquals(foundOvertime.getBalance(), (float) 17.5f);
        Assert.assertEquals(foundOvertime.getHours(), (float) 6f);
        Assert.assertEquals(foundOvertime.getReason(), "Foo4" + employee.getFullName());

        overtimeEmployeePage.addNewOvertime(localDate5, 7, "Foo5" + employee.getFullName());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate5);
        Assert.assertEquals(foundOvertime.getBalance(), (float) 24.5f);
        Assert.assertEquals(foundOvertime.getHours(), (float) 7f);
        Assert.assertEquals(foundOvertime.getReason(), "Foo5" + employee.getFullName());

        overtimeEmployeePage.addNewOvertime(localDate6, 8, "Foo6" + employee.getFullName());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate6);
        Assert.assertEquals(foundOvertime.getBalance(), (float) 32.5f);
        Assert.assertEquals(foundOvertime.getHours(), (float) 8f);
        Assert.assertEquals(foundOvertime.getReason(), "Foo6" + employee.getFullName());

        overtimeEmployeePage.addNewOvertime(localDate7, 9, "Foo7" + employee.getFullName());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate7);
        Assert.assertEquals(foundOvertime.getBalance(), (float) 41.5f);
        Assert.assertEquals(foundOvertime.getHours(), (float) 9f);
        Assert.assertEquals(foundOvertime.getReason(), "Foo7" + employee.getFullName());

        overtimeEmployeePage.addNewOvertime(localDate8, 10, "Foo8" + employee.getFullName());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate8);
        Assert.assertEquals(foundOvertime.getBalance(), (float) 51.5f);
        Assert.assertEquals(foundOvertime.getHours(), (float) 10f);
        Assert.assertEquals(foundOvertime.getReason(), "Foo8" + employee.getFullName());

        /*
        
         */
        /**
         * Jetzt müssen wir eine maintenance triggern. Anschließend müssen wir
         * zwei Dinge testen: 1. Die alten Überstundeneinträge aus vor fünf
         * Jahren sind gelöscht. 2. Die neuen Überstundeneinträge haben den
         * korrekten Saldo.
         */
        LogoutPage logoutPage = new LogoutPage();
        SignInPage signInPage = logoutPage.logout();
        try {
            super.signIn();
            /**
             * Beim Login wird die maintenance Klasse aufgerufen. Ob aber
             * tatsächlich aufgeräumt wird, hängt davon ab, ob in den
             * vergangenen 24 Stunden bereits einmal aufgeräumt wurde. Daher
             * rufen wir manuell die background_maintenance.php auf und
             * verwenden dabei forceMaintenance = true
             */
        } catch (Exception exception) {
            LogCollector.error("Sign in failed.");
            Assert.fail();
        }
        propertyFile = new PropertyFile();
        String testPageUrl = propertyFile.getTestPageUrl();
        String payload = "forceMaintenance=true";
        String maintenanceEndpoint = testPageUrl + "src/php/background_maintenance.php";
        HttpResponse<String> response = ApiHandler.sendPostRequestAsForm(maintenanceEndpoint, payload);
        LogCollector.debug(response.body());
        if (response.body().contains("Done with background maintenance.")) {
            LogCollector.info("Maintenence is done.");
        }
        overtimeEmployeePage = new OvertimeEmployeePage(driver);
        overtimeEmployeePage.selectYearTry(localDate0.getYear());
        overtimeEmployeePage.selectEmployee(employee.getEmployeeKey());
        try {
            foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate0);
            softAssert.assertTrue(false, "Einträge im Jahr vor fünf Jahren sollten nicht mehr gefunden werden.");
            LogCollector.error(foundOvertime.getLocalDate().format(DateTimeFormatter.ISO_DATE));
            LogCollector.error(String.valueOf(foundOvertime.getBalance()));
            LogCollector.error(foundOvertime.getReason());
            Assert.fail("Einträge im Jahr vor fünf Jahren sollten nicht mehr gefunden werden.");
        } catch (Exception exception) {
            /**
             * Wir sollten direkt nach dem getOvertimeByLocalDate() hier landen.
             * Denn der Eintrag sollte nicht mehr existieren. Somit schlägt die
             * Suche fehl.
             */
            softAssert.assertTrue(true);
            Assert.assertTrue(true);
        }
        // try {
        /**
         * Obwohl die alten Überstundeneinträge gelöscht wurden, sollte der
         * Saldo hier korrekt sein.
         */
        overtimeEmployeePage.selectYearTry(localDate8.getYear());
        overtimeEmployeePage.selectEmployee(employee.getEmployeeKey());
        foundOvertime = overtimeEmployeePage.getOvertimeByLocalDate(localDate8);
        softAssert.assertEquals(foundOvertime.getBalance(), (float) 51.5f, "Balance did not match");
        Assert.assertEquals(foundOvertime.getBalance(), (float) 51.5f, "Balance did not match");
        softAssert.assertEquals(foundOvertime.getHours(), (float) 10f, "Current Hours did not match");
        Assert.assertEquals(foundOvertime.getHours(), (float) 10f, "Current Hours did not match");
        softAssert.assertEquals(foundOvertime.getReason(), "Foo8" + employee.getFullName(), "Reason did not match");
        Assert.assertEquals(foundOvertime.getReason(), "Foo8" + employee.getFullName(), "Reason did not match");
        /**
         * remove the created overtime:
         */
        for (int i = 0; i <= 8; i++) {
            try {
                overtimeEmployeePage.removeOvertimeByLocalDate(localDate8);
                overtimeEmployeePage.removeOvertimeByLocalDate(localDate7);
                overtimeEmployeePage.removeOvertimeByLocalDate(localDate6);
                overtimeEmployeePage.removeOvertimeByLocalDate(localDate5);
                overtimeEmployeePage.removeOvertimeByLocalDate(localDate3);
                overtimeEmployeePage.removeOvertimeByLocalDate(localDate4);
                overtimeEmployeePage.removeOvertimeByLocalDate(localDate2);
                overtimeEmployeePage.removeOvertimeByLocalDate(localDate1);
                overtimeEmployeePage.removeOvertimeByLocalDate(localDate0);
            } catch (Exception e) {
                /**
                 * Some of these entries might not exist anymore. That does not
                 * matter. We are just cleaning up.
                 */
            }
        }
        softAssert.assertAll();
    }

}
