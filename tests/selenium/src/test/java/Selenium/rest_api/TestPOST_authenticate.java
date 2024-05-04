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
import Selenium.TestPage;
import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import org.testng.Assert;
import org.testng.annotations.Test;

/**
 * @todo <p lang=de>Die Klasse muss noch geteilt werden. Die Seitenspezifischen
 * Teile wandern in die Klasse POST_authenticatePage.java verschoben.
 * Die Teile, die von anderen API Seiten geteilt werden, wandern in eine TestApiPage.java Klasse.
 * </p>
 * @author Mandelkow
 */
public class TestPOST_authenticate {

    private PropertyFile propertyFile;

    @Test(enabled = true)
    public void testLogin() {
        propertyFile = new PropertyFile();
        try {
            // Authentication endpoint on real page:
            String realTestPageUrl = propertyFile.getRealTestPageUrl();

            // Define authentication payload:
            String userName = propertyFile.getRealUsername();
            String userPassphrase = propertyFile.getRealPassword();

            // Try authentication with wrong credentials:
            POST_authenticate authenticatePage = new POST_authenticate(userName, userPassphrase + "foo", realTestPageUrl);
            Assert.assertFalse(authenticatePage.isAuthenticated());
            // Try authentication with empty credentials:
            authenticatePage = new POST_authenticate(userName, "", realTestPageUrl);
            Assert.assertFalse(authenticatePage.isAuthenticated());
            authenticatePage = new POST_authenticate("", "", realTestPageUrl);
            Assert.assertFalse(authenticatePage.isAuthenticated());

            // Try authentication with correct credentials:
            authenticatePage = new POST_authenticate(userName, userPassphrase, realTestPageUrl);
            Assert.assertTrue(authenticatePage.isAuthenticated());

        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
        }

    }

}
