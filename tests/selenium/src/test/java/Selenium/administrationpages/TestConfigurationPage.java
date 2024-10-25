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

import org.testng.Assert;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestConfigurationPage extends Selenium.TestPage {

    @Test(enabled = true)
    public void testWriteConfiguration() {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception ex) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        /**
         * Go to page:
         */
        ConfigurationPage configurationPage = new ConfigurationPage(driver);
        /**
         * Set locales
         */
        try {
            configurationPage.setLocales("de_DE.utf8");
            /**
             * mailhog should sit on localhost in a docker container and wait
             * for mails. mailhog does not require authentication. It will
             * simply accept any mail. Make sure that your firewall prohibits
             * access to the mailhog port (default: 1025)
             *
             */
            configurationPage.setEmailMethod("smtp");
            configurationPage.setEmailSmtpHost("localhost");
            configurationPage.setEmailSmtpPort(1025);
            configurationPage.setEmailSmtpUsername("foo_username");
            configurationPage.setEmailSmtpPassphrase("foo_passphrase");

            configurationPage.submitForm();
            Assert.assertEquals(configurationPage.getLocales(), "de_DE.utf8");
        } catch (Exception exception) {
            System.out.println(exception.getMessage());
            System.out.println("driver.getCurrentUrl()");
            System.out.println(driver.getCurrentUrl());
            System.out.println("driver.getPageSource()");
            System.out.println(driver.getPageSource());
            Assert.fail();
        }
        /**
         * Set Name for Page
         */
        String applicationName = "Selenium Test Plan";
        configurationPage.setApplicationName(applicationName);
        configurationPage.submitForm();
        Assert.assertEquals(configurationPage.getApplicationName(), applicationName);

    }

    @Test(enabled = true, dependsOnMethods = {"testWriteConfiguration"})/*passed*/
    public void testReadInputFields() {
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
        ConfigurationPage configurationPage = new ConfigurationPage(driver);
        /**
         * Check the expected values:
         */
        Assert.assertEquals(configurationPage.getApplicationName(), "Selenium Test Plan");
        Assert.assertEquals(configurationPage.getDatabaseName(), propertyFile.getDatabaseName());
        /**
         * The password MUST NOT be visible!
         */
        Assert.assertEquals(configurationPage.getDatabasePassword(), "");
        /**
         * Contact email
         */
        Assert.assertEquals(configurationPage.getContactEmail(), propertyFile.getAdministratorEmail());
        /**
         * Language and encoding
         */
        Assert.assertEquals(configurationPage.getLanguage(), "Deutsch");
        Assert.assertEquals(configurationPage.getLocales(), "de_DE.utf8");
        Assert.assertEquals(configurationPage.getEncoding(), "UTF-8");
        /**
         * Error log verbosity:
         */
        Assert.assertEquals(configurationPage.getErrorReporting(), null);
        Assert.assertTrue(configurationPage.getErrorLogPath().contains("error.log"));
        /**
         * Approval:
         */
        Assert.assertEquals(configurationPage.getHideDisapproved(), false);
        /**
         * Sending emails:
         */
        Assert.assertEquals(configurationPage.getEmailMethod(), "smtp");
    }

}
