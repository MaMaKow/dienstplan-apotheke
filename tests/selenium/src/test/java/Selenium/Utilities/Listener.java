/*
 * Copyright (C) 2025 Mandelkow
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
package Selenium.Utilities;

import java.util.ArrayList;
import java.util.List;
import org.testng.*;

public class Listener implements ITestListener {

    // This belongs to ITestListener and will execute before the whole Test starts
    @Override
    public void onStart(ITestContext testContext) {
        Reporter.log("About to begin executing Class " + testContext.getName(), true);
    }

    // This belongs to ITestListener and will execute, once the whole Test is finished
    @Override
    public void onFinish(ITestContext testContext) {
        Reporter.log("About to end executing Class " + testContext.getName(), true);
    }

    // This belongs to ITestListener and will execute before each test method
    @Override
    public void onTestStart(ITestResult testResult) {
        Reporter.log("Testcase " + testResult.getName() + " started successfully", true);
        LogCollector.clear(); // Clear logs at the start of each test
    }

    // This belongs to ITestListener and will execute only in the event of a successful test method
    @Override
    public void onTestSuccess(ITestResult testResult) {
        Reporter.log("Testcase " + testResult.getName() + " passed successfully", true);
        LogCollector.clear(); // Discard logs on success
    }

    // This belongs to ITestListener and will execute only in the event of a fail test
    @Override
    public void onTestFailure(ITestResult testResult) {
        Reporter.log("Testcase " + testResult.getName() + " failed", true);
        for (String message : LogCollector.getMessages()) {
            Reporter.log(message, true);
        }
        LogCollector.clear(); // Clear logs after reporting
    }

    // This belongs to ITestListener and will execute only in the event of the skipped test method
    @Override
    public void onTestSkipped(ITestResult testResult) {
        Reporter.log("Testcase " + testResult.getName() + " got skipped", true);
        LogCollector.clear(); // Discard logs on skip
    }

    @Override
    public void onTestFailedButWithinSuccessPercentage(ITestResult testResult) {
        Reporter.log("Test Case Partially Successful: " + testResult.getName(), true);
    }
}
