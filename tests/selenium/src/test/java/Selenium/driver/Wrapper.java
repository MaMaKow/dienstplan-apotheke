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
package Selenium.driver;

import java.net.MalformedURLException;
import java.net.URL;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.remote.DesiredCapabilities;
import org.openqa.selenium.remote.RemoteWebDriver;

/**
 *
 * @author Mandelkow
 */
public class Wrapper {

    protected static WebDriver driver;

    public Wrapper() {
        try {
            //driver = createLocalChromeWebDriver();
            driver = createRemoteWebDriver();
        }
        catch (MalformedURLException ex) {
            Logger.getLogger(Wrapper.class.getName()).log(Level.SEVERE, null, ex);
        }
    }

    public static WebDriver getDriver() {
        if (null == driver) {
            new Wrapper();
        }
        return driver;
    }

    public static WebDriver createNewDriver() {
        new Wrapper();
        return driver;
    }

    private WebDriver createRemoteWebDriver() throws MalformedURLException {
        DesiredCapabilities capabilities = DesiredCapabilities.firefox();

        driver = new RemoteWebDriver(new URL("http://docker.martin-mandelkow.de:4444/wd/hub"), capabilities);
        return driver;
    }

    private WebDriver createLocalChromeWebDriver() {
        System.setProperty("webdriver.chrome.driver", "C:\\Program Files\\chromedriver_89_win32\\chromedriver.exe");
        ChromeOptions options = new ChromeOptions();
        options.addArguments("ignore-certificate-errors");
        // Setting headless argument
        options.addArguments("--headless");
        options.addArguments("window-size=1920,1080");
        options.addArguments("--start-maximized");
        driver = new ChromeDriver(options);
        return driver;
    }

}
