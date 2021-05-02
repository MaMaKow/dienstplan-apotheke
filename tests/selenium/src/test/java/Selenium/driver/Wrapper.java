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
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
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
        driver = createLocalChromeWebDriver();
        /*
        try {
            //driver = createRemoteWebDriver();
        } catch (MalformedURLException ex) {
            Logger.getLogger(Wrapper.class.getName()).log(Level.SEVERE, null, ex);
        }
         */
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
        //options.addArguments("--headless");
        options.addArguments("window-size=1920,1080");
        options.addArguments("--start-maximized");
        driver = new ChromeDriver(options);
        return driver;
    }

    /**
     * Currently, the code uses element.getAttribute("value") to get text from
     * text box.This can also be replaced with element.getText() based on the
     * type of text box.
     * https://medium.com/@hanikhan18/tackling-issues-related-to-missing-characters-in-selenium-sendkeys-on-ie-42846d90d97b
     *
     * @param element
     * @param stringToEnter
     */
    public static void CustomSendKeysIE(WebElement element, String stringToEnter) /*throws InterruptedException*/ {
        //Convert String to be entered to a character array
        char[] charsToEnter = stringToEnter.toCharArray();

        //Iterate characters in the character array over a loop
        for (int i = 0; i < charsToEnter.length; i++) {
            try {
                //Adding a sleep of 500 milliseconds to handle any sync issues
                Thread.sleep(100);
            } catch (InterruptedException ex) {
                Logger.getLogger(Wrapper.class.getName()).log(Level.SEVERE, null, ex);
            }

            //Send character at index i
            element.sendKeys(String.valueOf(charsToEnter[i]).toString());

            //Check if entered character(s) match with the substring
            if (!element.getAttribute("value").equals(stringToEnter.substring(0, i + 1))) {

                //Enter the character again if text length does not match(Indicates that a character was skipped OR not typed)
                if (!(element.getAttribute("value").length() == (i + 1))) {
                    element.sendKeys(String.valueOf(charsToEnter[i]));

                    //Check if entered character(s) match with the substring
                    if (!element.getAttribute("value").equals(stringToEnter.substring(0, i + 1))) {

                        //use throws or similar statement to throw an exception instead of syso based on your test case requirement
                        System.out.println("Throw Exception");
                    }
                } //If text length matches, it indicates that an invalid character was entered
                else if (element.getAttribute("value").length() == (i + 1)) {

                    //Send BACK_SPACE to the text box
                    element.sendKeys(Keys.BACK_SPACE);

                    //send the character at index i again
                    element.sendKeys(String.valueOf(charsToEnter[i]));

                    //Check if entered character(s) match with the substring
                    if (!element.getAttribute("value").equals(stringToEnter.substring(0, i + 1))) {

                        //use throws or similar statement to throw an exception instead of syso based on your test case requirement
                        System.out.println("Throw Exception");
                        //throw new InterruptedException("Bam");
                    }
                }
            }
        }
    }

}
