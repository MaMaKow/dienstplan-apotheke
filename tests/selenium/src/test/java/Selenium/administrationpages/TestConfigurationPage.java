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
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestConfigurationPage extends TestPage {

    @Test(enabled = true)
    public void testWriteConfiguration() {
        /**
         * Sign in:
         */
        super.signIn();
        /**
         * Go to page:
         */
        ConfigurationPage configurationPage = new ConfigurationPage(driver);
        /**
         * Set locales
         */
        configurationPage.setLocales("de_DE.utf8");
        configurationPage.submitForm();
        Assert.assertEquals(configurationPage.getLocales(), "de_DE.utf8");

    }

    @Test(enabled = true, dependsOnMethods = {"testWriteConfiguration"})/*passed*/
    public void testReadInputFields() {
        /**
         * Sign in:
         */
        super.signIn();
        /**
         * Go to page:
         */
        ConfigurationPage configurationPage = new ConfigurationPage(driver);
        /**
         * Check the expected values:
         */
        Assert.assertEquals(configurationPage.getApplicationName(), "PDR");
        Assert.assertEquals(configurationPage.getDatabaseName(), "pdrTest");
        /**
         * The password MUST NOT be visible!
         */
        Assert.assertEquals(configurationPage.getDatabasePassword(), "");
        /**
         * Contact email
         */
        Assert.assertTrue(configurationPage.getContactEmail().contains("selenium@"));
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
        /**
         * Approval:
         */
        Assert.assertEquals(configurationPage.getHideDisapproved(), false);
        /**
         * Sending emails:
         */
        Assert.assertEquals(configurationPage.getEmailMethod(), "mail");
        /**
         * @todo: <p lang=de>Es fehen noch die Methoden zum Ändern der
         * Konfiguration.</p>
         */
    }

}
