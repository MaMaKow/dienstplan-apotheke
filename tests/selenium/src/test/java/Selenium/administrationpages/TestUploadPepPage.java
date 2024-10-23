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
public class TestUploadPepPage extends TestPage {

    @Test(enabled = true)/*new*/
    public void testPEPFileUpload() {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        UploadPepPage uploadPepPage = new UploadPepPage(driver);
        /**
         * Find a file to upload:
         */
        uploadPepPage.uploadFile();
        /**
         * <p lang=de>Nach 5 Versuchen (=etwa 5 Sekunden) ist die expectation
         * vermutlich noch nicht fertig berechnet. Nach weiteren 30 Sekunden
         * sollte die Berechnung aber fertig sein.</p>
         */
        Assert.assertFalse(uploadPepPage.expectationIsPresentAfterWaiting(5));
        Assert.assertTrue(uploadPepPage.expectationIsPresentAfterWaiting(300));
    }
}
