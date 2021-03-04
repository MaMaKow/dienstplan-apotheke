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

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;

/**
 *
 * @author Mandelkow
 */
public class Wrapper {

    protected static WebDriver driver;

    public Wrapper() {
        System.setProperty("webdriver.chrome.driver", "C:\\Program Files\\chromedriver_87_win32\\chromedriver.exe");
        ChromeOptions options = new ChromeOptions();
        options.addArguments("ignore-certificate-errors");
        // Setting headless argument
        options.addArguments("--headless");
        options.addArguments("window-size=1920,1080");
        options.addArguments("--start-maximized");
        driver = new ChromeDriver(options);
        //driver.manage().window().maximize();
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

}
