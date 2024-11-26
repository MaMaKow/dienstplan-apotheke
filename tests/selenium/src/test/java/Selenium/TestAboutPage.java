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
package Selenium;

import org.testng.Assert;
import org.testng.annotations.Test;

/**
 *
 * @author Mandelkow
 */
public class TestAboutPage extends TestPage {

    @Test(enabled = true)
    public void testGetVersion() {
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        AboutPage aboutPage = new AboutPage(driver);
        String versionString = aboutPage.getVersion();
        String versionStringShould = aboutPage.getVersionStringShould();
        Assert.assertEquals(versionString, versionStringShould);
    }
}
