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
        //options.addArguments("headless");
        driver = new ChromeDriver(options);

    }

    public static WebDriver getDriver() {
        System.out.println("getDriver");
        if (null == driver) {
            System.out.println("We need a new driver");
            new Wrapper();
        }
        System.out.println(driver);
        return driver;
    }

    public static WebDriver createNewDriver() {
        System.out.println("createDriver");
        System.out.println("We create a new driver");
        new Wrapper();
        System.out.println(driver);
        return driver;
    }

}
