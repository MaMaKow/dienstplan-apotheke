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

import Selenium.Absence;
import Selenium.Employee;
import Selenium.PropertyFile;
import Selenium.Utilities.LogCollector;
import Selenium.absencepages.AbsenceEmployeePage;
import java.io.IOException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;
import org.testng.Assert;
import org.testng.SkipException;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import org.testng.asserts.SoftAssert;

/**
 *
 * @author Mandelkow
 */
public class TestGET_absenceEndpoint extends Selenium.TestPage {

    private PropertyFile propertyFile;
    private SoftAssert softAssert = new SoftAssert();
    private String testPageUrl;

    @BeforeClass
    public void setUp() throws IOException, Exception {
        propertyFile = new PropertyFile();
        testPageUrl = propertyFile.getTestPageUrl();

        // Ensure we are authenticated before running tests
        if (!POST_authenticateEndpoint.isAuthenticated()) {
            String userName = propertyFile.getPdrUserName();
            String userPassphrase = propertyFile.getPdrUserPassword();
            new POST_authenticateEndpoint(userName, userPassphrase, testPageUrl);
        }
    }

    @Test()
    public void testGetAllAbsencesCurrentYear() throws IOException, Exception {
        if (Selenium.TestPage.someTestHasFailed) {
            throw new SkipException("Some Test has failed. Skipping all the other methods.");
        }

        int currentYear = LocalDate.now().getYear();
        Employee testEmployee = workforce.getEmployeeByFullName("Albert Krüger");
        int testEmployeeKey = testEmployee.getEmployeeKey();

        // Create unique test data
        String testStartDate = "10.05." + currentYear;
        String testEndDate = "12.05." + currentYear;
        String testComment = "API Test All Absences - " + System.currentTimeMillis();

        AbsenceEmployeePage absenceEmployeePage = null;

        try {
            LogCollector.debug("Setting up test data: Creating absence for testing GET all absences");

            // Setup: Create test absence
            super.signIn(); // Ensure we're signed in for web operations
            absenceEmployeePage = new AbsenceEmployeePage();
            absenceEmployeePage = absenceEmployeePage.goToYear(currentYear);
            absenceEmployeePage = absenceEmployeePage.goToEmployee(testEmployeeKey);

            absenceEmployeePage = absenceEmployeePage.createNewAbsence(
                    testStartDate,
                    testEndDate,
                    Absence.REASON_SICKNESS,
                    testComment,
                    "approved"
            );

            // Verify creation was successful
            softAssert.assertTrue(absenceEmployeePage.getUserDialogErrors().isEmpty(),
                    "Test data creation should not have errors");

            LogCollector.debug("Test data created successfully");

            // Now test the API endpoint
            LogCollector.debug("Testing GET /absences (current year)");

            GET_absenceEndpoint absenceEndpoint = GET_absenceEndpoint.forCurrentYear(testPageUrl);
            List<Absence> foundAbsences = absenceEndpoint.getAbsences();

            softAssert.assertNotNull(foundAbsences, "Absence list should not be null");
            LogCollector.debug("Found " + foundAbsences.size() + " absences for current year");

            // Verify we have at least our test absence
            softAssert.assertTrue(foundAbsences.size() >= 1,
                    "Should find at least our test absence");

            // Track if we found our specific test data
            boolean testAbsenceFound = false;
            Absence testAbsence = null;

            for (Absence absence : foundAbsences) {
                // Verify all absences are from current year
                softAssert.assertEquals(absence.getStartDate().getYear(), currentYear,
                        "Start date should be from current year");
                softAssert.assertEquals(absence.getEndDate().getYear(), currentYear,
                        "End date should be from current year");

                // Basic data validation
                softAssert.assertTrue(absence.getEmployeeKey() > 0, "Employee key should be positive");
                softAssert.assertNotNull(absence.getReasonString(), "Reason string should not be null");
                softAssert.assertTrue(absence.getReasonId() > 0, "Reason ID should be positive");
                softAssert.assertFalse(absence.getStartDate().isAfter(absence.getEndDate()),
                        "Start date should not be after end date");

                // Check if this is our test absence
                if (absence.getCommentString() != null
                        && absence.getCommentString().equals(testComment)) {
                    testAbsenceFound = true;
                    testAbsence = absence;
                    LogCollector.debug("Found our test absence in the results");
                }
            }

            // Verify our specific test data was found
            softAssert.assertTrue(testAbsenceFound,
                    "Should find the test absence we just created in the GET all absences result");

            if (testAbsence != null) {
                // Detailed verification of our test absence
                softAssert.assertEquals(testAbsence.getEmployeeKey(), testEmployeeKey,
                        "Test absence should have correct employee key");
                softAssert.assertEquals(testAbsence.getStartDate(),
                        LocalDate.parse(testStartDate, DateTimeFormatter.ofPattern("dd.MM.yyyy")),
                        "Test absence should have correct start date");
                softAssert.assertEquals(testAbsence.getEndDate(),
                        LocalDate.parse(testEndDate, DateTimeFormatter.ofPattern("dd.MM.yyyy")),
                        "Test absence should have correct end date");
                softAssert.assertEquals(testAbsence.getReasonString(),
                        Absence.absenceReasonsMap.get(Absence.REASON_SICKNESS),
                        "Test absence should have correct reason");

                LogCollector.debug("Test absence verification completed successfully");
            }

        } catch (Exception exception) {
            LogCollector.error(exception.getLocalizedMessage());
            exception.printStackTrace();
            softAssert.fail("Test failed: " + exception.getMessage());
            throw exception;
        } finally {
            // Cleanup: Always try to delete test data, even if test failed
            if (absenceEmployeePage != null) {
                try {
                    LogCollector.debug("Cleaning up test data");
                    absenceEmployeePage.deleteExistingAbsence(testStartDate);
                    LogCollector.debug("Test data cleanup completed");
                } catch (Exception cleanupException) {
                    LogCollector.warn("Failed to cleanup test data: " + cleanupException.getMessage());
                    // Don't fail the test because of cleanup issues, just log it
                }
            }
        }

        softAssert.assertAll();
    }

    @Test()
    public void testGetAllAbsencesSpecificYear() throws IOException, Exception {
        if (Selenium.TestPage.someTestHasFailed) {
            throw new SkipException("Some Test has failed. Skipping all the other methods.");
        }

        int testYear = 2023;
        Employee testEmployee = workforce.getEmployeeByFullName("Albert Krüger");
        int testEmployeeKey = testEmployee.getEmployeeKey();

        // Create unique test data for specific year
        String testStartDate = "20.08." + testYear;
        String testEndDate = "22.08." + testYear;
        String testComment = "API Test Specific Year - " + System.currentTimeMillis();

        AbsenceEmployeePage absenceEmployeePage = null;

        try {
            LogCollector.debug("Setting up test data: Creating absence for year " + testYear);

            // Setup: Create test absence for specific year
            super.signIn(); // Ensure we're signed in for web operations
            absenceEmployeePage = new AbsenceEmployeePage();
            absenceEmployeePage = absenceEmployeePage.goToYear(testYear);
            absenceEmployeePage = absenceEmployeePage.goToEmployee(testEmployeeKey);

            absenceEmployeePage = absenceEmployeePage.createNewAbsence(
                    testStartDate,
                    testEndDate,
                    Absence.REASON_PAID_LEAVE_OF_ABSENCE,
                    testComment,
                    "approved"
            );

            // Verify creation was successful
            softAssert.assertTrue(absenceEmployeePage.getUserDialogErrors().isEmpty(),
                    "Test data creation should not have errors");

            LogCollector.debug("Test data created successfully for year " + testYear);

            // Now test the API endpoint
            LogCollector.debug("Testing GET /absences/" + testYear);

            GET_absenceEndpoint absenceEndpoint = GET_absenceEndpoint.forYear(testPageUrl, testYear);
            List<Absence> foundAbsences = absenceEndpoint.getAbsences();

            softAssert.assertNotNull(foundAbsences, "Absence list should not be null");
            LogCollector.debug("Found " + foundAbsences.size() + " absences for year " + testYear);

            // Verify we have at least our test absence
            softAssert.assertTrue(foundAbsences.size() >= 1,
                    "Should find at least our test absence");

            // Track if we found our specific test data
            boolean testAbsenceFound = false;
            Absence testAbsence = null;

            for (Absence absence : foundAbsences) {
                // Verify all absences are from specified year
                softAssert.assertEquals(absence.getStartDate().getYear(), testYear,
                        "Start date should be from year " + testYear);
                softAssert.assertEquals(absence.getEndDate().getYear(), testYear,
                        "End date should be from year " + testYear);

                // Basic data validation
                softAssert.assertTrue(absence.getEmployeeKey() > 0, "Employee key should be positive");
                softAssert.assertNotNull(absence.getReasonString(), "Reason string should not be null");
                softAssert.assertTrue(absence.getReasonId() > 0, "Reason ID should be positive");
                softAssert.assertFalse(absence.getStartDate().isAfter(absence.getEndDate()),
                        "Start date should not be after end date");

                // Log sample data
                LogCollector.debug("Absence: Employee " + absence.getEmployeeKey()
                        + " from " + absence.getStartDate().format(DateTimeFormatter.ISO_LOCAL_DATE)
                        + " to " + absence.getEndDate().format(DateTimeFormatter.ISO_LOCAL_DATE)
                        + " (" + absence.getReasonString() + ")");

                // Check if this is our test absence
                if (absence.getCommentString() != null
                        && absence.getCommentString().equals(testComment)) {
                    testAbsenceFound = true;
                    testAbsence = absence;
                    LogCollector.debug("Found our test absence in the results");
                }
            }

            // Verify our specific test data was found
            softAssert.assertTrue(testAbsenceFound,
                    "Should find the test absence we just created in the specific year results");

            if (testAbsence != null) {
                // Detailed verification of our test absence
                softAssert.assertEquals(testAbsence.getEmployeeKey(), testEmployeeKey,
                        "Test absence should have correct employee key");
                softAssert.assertEquals(testAbsence.getStartDate(),
                        LocalDate.parse(testStartDate, DateTimeFormatter.ofPattern("dd.MM.yyyy")),
                        "Test absence should have correct start date");
                softAssert.assertEquals(testAbsence.getEndDate(),
                        LocalDate.parse(testEndDate, DateTimeFormatter.ofPattern("dd.MM.yyyy")),
                        "Test absence should have correct end date");
                softAssert.assertEquals(testAbsence.getReasonString(),
                        Absence.absenceReasonsMap.get(Absence.REASON_PAID_LEAVE_OF_ABSENCE),
                        "Test absence should have correct reason");
                softAssert.assertEquals(testAbsence.getapprovalString(), "approved",
                        "Test absence should have correct approval status");

                LogCollector.debug("Test absence verification completed successfully");
            }

        } catch (Exception exception) {
            LogCollector.error(exception.getLocalizedMessage());
            exception.printStackTrace();
            softAssert.fail("Test failed: " + exception.getMessage());
            throw exception;
        } finally {
            // Cleanup: Always try to delete test data, even if test failed
            if (absenceEmployeePage != null) {
                try {
                    LogCollector.debug("Cleaning up test data");
                    absenceEmployeePage.deleteExistingAbsence(testStartDate);
                    LogCollector.debug("Test data cleanup completed");
                } catch (Exception cleanupException) {
                    LogCollector.warn("Failed to cleanup test data: " + cleanupException.getMessage());
                    // Don't fail the test because of cleanup issues, just log it
                }
            }
        }

        softAssert.assertAll();
    }

    @Test()
    public void testGetEmployeeAbsencesCurrentYear() throws IOException, Exception {
        if (Selenium.TestPage.someTestHasFailed) {
            throw new SkipException("Some Test has failed. Skipping all the other methods.");
        }

        Employee employee = workforce.getEmployeeByFullName("Albert Krüger");
        int testEmployeeKey = employee.getEmployeeKey();
        int currentYear = LocalDate.now().getYear();

        // Create unique test data for this test
        String testStartDate = "15.06." + currentYear;  // Use current year
        String testEndDate = "17.06." + currentYear;
        String testComment = "API Test Vacation - " + System.currentTimeMillis(); // Unique identifier

        AbsenceEmployeePage absenceEmployeePage = null;

        try {
            LogCollector.debug("Setting up test data: Creating absence for employee " + testEmployeeKey);

            // Setup: Create test absence
            super.signIn(); // Ensure we're signed in for web operations
            absenceEmployeePage = new AbsenceEmployeePage();
            absenceEmployeePage = absenceEmployeePage.goToYear(currentYear);
            absenceEmployeePage = absenceEmployeePage.goToEmployee(testEmployeeKey);

            absenceEmployeePage = absenceEmployeePage.createNewAbsence(
                    testStartDate,
                    testEndDate,
                    Absence.REASON_VACATION,
                    testComment,
                    "approved"
            );

            // Verify creation was successful
            Assert.assertTrue(absenceEmployeePage.getUserDialogErrors().isEmpty(),
                    "Test data creation should not have errors");

            LogCollector.debug("Test data created successfully");

            // Now test the API endpoint
            LogCollector.debug("Testing GET /employees/" + testEmployeeKey + "/absences");

            GET_absenceEndpoint absenceEndpoint = GET_absenceEndpoint.forEmployee(testPageUrl, employee);
            List<Absence> foundAbsences = absenceEndpoint.getAbsences();

            softAssert.assertNotNull(foundAbsences, "Absence list should not be null");
            LogCollector.debug("Found " + foundAbsences.size() + " absences for employee " + employee.getFullName() + " in current year");

            // Verify our test absence is found
            boolean testAbsenceFound = false;
            Absence testAbsence = null;

            for (Absence absence : foundAbsences) {
                // Basic data validation
                softAssert.assertTrue(absence.getEmployeeKey() > 0, "Employee key should be positive");
                softAssert.assertNotNull(absence.getReasonString(), "Reason string should not be null");
                softAssert.assertTrue(absence.getReasonId() > 0, "Reason ID should be positive");
                softAssert.assertFalse(absence.getStartDate().isAfter(absence.getEndDate()),
                        "Start date should not be after end date");
                // Verify all absences belong to the specified employee
                softAssert.assertEquals(absence.getEmployeeKey(), testEmployeeKey,
                        "All absences should belong to employee " + testEmployeeKey);

                // Verify all absences are from current year
                softAssert.assertEquals(absence.getStartDate().getYear(), currentYear,
                        "Start date should be from current year");
                softAssert.assertEquals(absence.getEndDate().getYear(), currentYear,
                        "End date should be from current year");

                // Check if this is our test absence
                if (absence.getCommentString() != null
                        && absence.getCommentString().equals(testComment)) {
                    testAbsenceFound = true;
                    testAbsence = absence;
                }
            }

            // Verify our specific test data
            softAssert.assertTrue(testAbsenceFound,
                    "Should find the test absence we just created");

            if (testAbsence != null) {
                softAssert.assertEquals(testAbsence.getStartDate(),
                        LocalDate.parse(testStartDate, DateTimeFormatter.ofPattern("dd.MM.yyyy")),
                        "Start date should match");
                softAssert.assertEquals(testAbsence.getEndDate(),
                        LocalDate.parse(testEndDate, DateTimeFormatter.ofPattern("dd.MM.yyyy")),
                        "End date should match");
                softAssert.assertEquals(testAbsence.getReasonString(), "Urlaub",
                        "Reason should be vacation");
                softAssert.assertEquals(testAbsence.getapprovalString(), "approved",
                        "Approval status should match");
            }

        } catch (Exception exception) {
            LogCollector.error(exception.getLocalizedMessage());
            exception.printStackTrace();
            Assert.fail("Test failed: " + exception.getMessage());
            throw exception;
        } finally {
            // Cleanup: Always try to delete test data, even if test failed
            if (absenceEmployeePage != null) {
                try {
                    LogCollector.debug("Cleaning up test data");
                    absenceEmployeePage.deleteExistingAbsence(testStartDate);
                    LogCollector.debug("Test data cleanup completed");
                } catch (Exception cleanupException) {
                    LogCollector.warn("Failed to cleanup test data: " + cleanupException.getMessage());
                    // Don't fail the test because of cleanup issues, just log it
                }
            }
        }

        softAssert.assertAll();
    }

    @Test()
    public void testGetEmployeeAbsencesSpecificYear() throws IOException, Exception {
        if (Selenium.TestPage.someTestHasFailed) {
            throw new SkipException("Some Test has failed. Skipping all the other methods.");
        }

        Employee employee = workforce.getEmployeeByFullName("Albert Krüger");
        int testEmployeeKey = employee.getEmployeeKey();
        int testYear = 2020;

        // Create unique test data for specific employee and year
        String testStartDate = "15.09." + testYear;
        String testEndDate = "18.09." + testYear;
        String testComment = "API Test Employee Specific Year - " + System.currentTimeMillis();

        AbsenceEmployeePage absenceEmployeePage = null;

        try {
            LogCollector.debug("Setting up test data: Creating absence for employee " + testEmployeeKey + " in year " + testYear);

            // Setup: Create test absence for specific employee and year
            super.signIn(); // Ensure we're signed in for web operations
            absenceEmployeePage = new AbsenceEmployeePage();
            absenceEmployeePage = absenceEmployeePage.goToYear(testYear);
            absenceEmployeePage = absenceEmployeePage.goToEmployee(testEmployeeKey);

            absenceEmployeePage = absenceEmployeePage.createNewAbsence(
                    testStartDate,
                    testEndDate,
                    Absence.REASON_SICKNESS_OF_CHILD,
                    testComment,
                    "approved"
            );

            // Verify creation was successful
            softAssert.assertTrue(absenceEmployeePage.getUserDialogErrors().isEmpty(),
                    "Test data creation should not have errors");

            LogCollector.debug("Test data created successfully for employee " + testEmployeeKey + " in year " + testYear);

            // Now test the API endpoint
            LogCollector.debug("Testing GET /employees/" + testEmployeeKey + "/absences/" + testYear);

            GET_absenceEndpoint absenceEndpoint = GET_absenceEndpoint.forEmployeeAndYear(testPageUrl, employee, testYear);
            List<Absence> foundAbsences = absenceEndpoint.getAbsences();

            softAssert.assertNotNull(foundAbsences, "Absence list should not be null");
            LogCollector.debug("Found " + foundAbsences.size() + " absences for employee " + testEmployeeKey + " in year " + testYear);

            // Verify we have at least our test absence
            softAssert.assertTrue(foundAbsences.size() >= 1,
                    "Should find at least our test absence");

            // Track if we found our specific test data
            boolean testAbsenceFound = false;
            Absence testAbsence = null;

            for (Absence absence : foundAbsences) {
                // Verify all absences belong to the specified employee
                softAssert.assertEquals(absence.getEmployeeKey(), testEmployeeKey,
                        "All absences should belong to employee " + testEmployeeKey);

                // Verify all absences are from specified year
                softAssert.assertEquals(absence.getStartDate().getYear(), testYear,
                        "Start date should be from year " + testYear);
                softAssert.assertEquals(absence.getEndDate().getYear(), testYear,
                        "End date should be from year " + testYear);

                // Basic data validation
                softAssert.assertTrue(absence.getEmployeeKey() > 0, "Employee key should be positive");
                softAssert.assertNotNull(absence.getReasonString(), "Reason string should not be null");
                softAssert.assertTrue(absence.getReasonId() > 0, "Reason ID should be positive");
                softAssert.assertFalse(absence.getStartDate().isAfter(absence.getEndDate()),
                        "Start date should not be after end date");

                // Log detailed absence information
                LogCollector.debug("Employee " + testEmployeeKey + " absence: "
                        + absence.getStartDate().format(DateTimeFormatter.ISO_LOCAL_DATE)
                        + " to " + absence.getEndDate().format(DateTimeFormatter.ISO_LOCAL_DATE)
                        + " (" + absence.getReasonId() + ")"
                        + (absence.getReasonString() != null ? " - Reason: " + absence.getReasonString() : "")
                        + (absence.getCommentString() != null ? " - Comment: " + absence.getCommentString() : ""));

                // Check if this is our test absence
                if (absence.getCommentString() != null
                        && absence.getCommentString().equals(testComment)) {
                    testAbsenceFound = true;
                    testAbsence = absence;
                    LogCollector.debug("Found our test absence in the results");
                }
            }

            // Verify our specific test data was found
            softAssert.assertTrue(testAbsenceFound,
                    "Should find the test absence we just created for employee " + testEmployeeKey + " in year " + testYear);

            if (testAbsence != null) {
                // Detailed verification of our test absence
                softAssert.assertEquals(testAbsence.getEmployeeKey(), testEmployeeKey,
                        "Test absence should have correct employee key");
                softAssert.assertEquals(testAbsence.getStartDate(),
                        LocalDate.parse(testStartDate, DateTimeFormatter.ofPattern("dd.MM.yyyy")),
                        "Test absence should have correct start date");
                softAssert.assertEquals(testAbsence.getEndDate(),
                        LocalDate.parse(testEndDate, DateTimeFormatter.ofPattern("dd.MM.yyyy")),
                        "Test absence should have correct end date");
                softAssert.assertEquals(testAbsence.getReasonString(),
                        Absence.absenceReasonsMap.get(Absence.REASON_SICKNESS_OF_CHILD),
                        "Test absence should have correct reason");
                softAssert.assertEquals(testAbsence.getapprovalString(), "approved",
                        "Test absence should have correct approval status");
                softAssert.assertEquals(testAbsence.getCommentString(), testComment,
                        "Test absence should have correct comment");

                LogCollector.debug("Test absence verification completed successfully");
            }

        } catch (Exception exception) {
            LogCollector.error(exception.getLocalizedMessage());
            exception.printStackTrace();
            Assert.fail("Test failed: " + exception.getMessage());
            throw exception;
        } finally {
            // Cleanup: Always try to delete test data, even if test failed
            if (absenceEmployeePage != null) {
                try {
                    LogCollector.debug("Cleaning up test data");
                    absenceEmployeePage.deleteExistingAbsence(testStartDate);
                    LogCollector.debug("Test data cleanup completed");
                } catch (Exception cleanupException) {
                    LogCollector.warn("Failed to cleanup test data: " + cleanupException.getMessage());
                    // Don't fail the test because of cleanup issues, just log it
                }
            }
        }

        softAssert.assertAll();
    }

    @Test()
    public void testGetInvalidEmployeeAbsences() throws IOException, Exception {
        if (Selenium.TestPage.someTestHasFailed) {
            throw new SkipException("Some Test has failed. Skipping all the other methods.");
        }
        try {
            Employee employee = workforce.getEmployeeByFullName("Invalid Employee"); // Does not exist, returns null

            LogCollector.debug("Testing GET /employees/" + "invalid key" + "/absences (should handle gracefully)");

            // This should either return empty list or throw a meaningful exception
            GET_absenceEndpoint absenceEndpoint = GET_absenceEndpoint.forEmployee(testPageUrl, employee);
            List<Absence> foundAbsences = absenceEndpoint.getAbsences();

            // Should return empty list for non-existent employee, not null
            softAssert.assertNotNull(foundAbsences, "Even for invalid employee, list should not be null");
            softAssert.assertEquals(foundAbsences.size(), 0, "Should return empty list for non-existent employee");

            LogCollector.debug("Correctly handled non-existent employee " + "invalid employee");

        } catch (RuntimeException exception) {
            // If API returns error for invalid employee, that's also acceptable
            if (exception.getMessage().contains("API Error")) {
                LogCollector.debug("API correctly returned error for invalid employee: " + exception.getMessage());
                // This is expected behavior, so test passes
            } else {
                throw exception;
            }
        } catch (Exception exception) {
            LogCollector.error(exception.getLocalizedMessage());
            exception.printStackTrace();
            Assert.fail("Test failed: " + exception.getMessage());
            throw exception;
        }
        softAssert.assertAll();
    }
}
