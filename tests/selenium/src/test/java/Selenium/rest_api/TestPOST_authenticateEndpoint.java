/*
 * Copyright (C) 2023 Mandelkow
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
import java.io.IOException;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.logging.log4j.message.ReusableMessageFactory;
import org.testng.Assert;
import org.testng.ITestResult;
import org.testng.SkipException;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

/**
 * @todo <p lang=de>Die Klasse muss noch geteilt werden. Die Seitenspezifischen
 * Teile wandern in die Klasse POST_authenticatePage.java verschoben. Die Teile,
 * die von anderen API Seiten geteilt werden, wandern in eine TestApiPage.java
 * Klasse.
 * </p>
 * @author Mandelkow
 */
public class TestPOST_authenticateEndpoint extends Selenium.TestPage {

    @BeforeMethod
    public void setUpMethod(ITestResult result) {
        if (true == Selenium.TestPage.someTestHasFailed) {
            throw new SkipException("Some Test has failed. Skipping all the other methods.");
        }
        // Print the name of the class and the currently executing test method to the log file
        packageName = this.getClass().getPackageName();
        className = this.getClass().getSimpleName();
        methodName = result.getMethod().getMethodName();
        System.err.println("Package: " + packageName + ", Class: " + className + ", Method: " + methodName);
    }

    private PropertyFile propertyFile;
    public Logger logger;

    @Test(enabled = true)
    public void testLogin() {
        this.logger = LogManager.getLogger(this.getClass(), ReusableMessageFactory.INSTANCE);
        propertyFile = new PropertyFile();
        try {
            // Authentication endpoint on real page:
            String testPageUrl = propertyFile.getTestPageUrl();

            // Define authentication payload:
            String userName = propertyFile.getPdrUserName();
            String userPassphrase = propertyFile.getPdrUserPassword();

            // Try authentication with wrong credentials:
            logger.debug("Try authentication with wrong credentials:");
            POST_authenticateEndpoint authenticateEndpoint = new POST_authenticateEndpoint(userName, userPassphrase + "foo", testPageUrl);
            Assert.assertFalse(authenticateEndpoint.isAuthenticated(), "Login with wrong credentials should have failed, but did not.");
            // Try authentication with empty credentials:
            logger.debug("Try authentication with empty credentials; passphrase:");
            authenticateEndpoint = new POST_authenticateEndpoint(userName, "", testPageUrl);
            Assert.assertFalse(authenticateEndpoint.isAuthenticated(), "Login with empty passphrase should have failed, but did not.");
            logger.debug("Try authentication with empty credentials; username and passphrase:");
            authenticateEndpoint = new POST_authenticateEndpoint("", "", testPageUrl);
            Assert.assertFalse(authenticateEndpoint.isAuthenticated(), "Login with empty credentials should have failed, but did not.");

            // Try authentication with correct credentials:
            logger.debug("Try authentication with correct credentials:");
            authenticateEndpoint = new POST_authenticateEndpoint(userName, userPassphrase, testPageUrl);
            Assert.assertTrue(authenticateEndpoint.isAuthenticated(), "Endpoint is not authenticated. API login failed.");

        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
            Assert.fail();
        }
    }

}
