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

import Selenium.Employee;
import Selenium.PropertyFile;
import java.net.MalformedURLException;
import java.net.URL;
import java.nio.file.Paths;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.concurrent.TimeUnit;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.firefox.FirefoxOptions;
import org.openqa.selenium.firefox.FirefoxProfile;
import org.openqa.selenium.logging.LogEntries;
import org.openqa.selenium.logging.LogEntry;
import org.openqa.selenium.logging.LogType;
import org.openqa.selenium.remote.DesiredCapabilities;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.ui.Select;

/**
 *
 * @author Mandelkow
 */
public class Wrapper {

    protected static WebDriver driver;
    public static final DateTimeFormatter DATE_TIME_FORMATTER_DAY_MONTH_YEAR = DateTimeFormatter.ofPattern("dd.MM.yyyy", Locale.GERMANY);
    public static final DateTimeFormatter DATE_TIME_FORMATTER_YEAR_MONTH_DAY = DateTimeFormatter.ofPattern("yyyy-MM-dd", Locale.GERMANY);

    public Wrapper() {
        LocalDateTime timerStart = LocalDateTime.now();
        // driver = createLocalChromeWebDriver();
        driver = createRemoteWebDriver();
        driver.manage().timeouts().implicitlyWait(10, TimeUnit.SECONDS);
        LocalDateTime timerEnd = LocalDateTime.now();
        long timeToCreate = java.time.Duration.between(timerStart, timerEnd).toMillis();
        System.out.println("Time to create WebDriver: " + timeToCreate + " milliseconds");
    }

    public static WebDriver getDriver() {
        if (null == driver || driver.toString().contains("(null)")) {
            new Wrapper();
        }
        return driver;
    }

    public static WebDriver createNewDriver() {
        new Wrapper();
        return driver;
    }

    private WebDriver createRemoteWebDriver() {
        FirefoxOptions options = new FirefoxOptions();
        try {
            driver = new RemoteWebDriver(new URL("http://localhost:4444"), options);
        } catch (MalformedURLException ex) {
            Logger.getLogger(Wrapper.class.getName()).log(Level.SEVERE, null, ex);
        }
        return driver;
    }

    private WebDriver createLocalChromeWebDriver() {
        PropertyFile propertyFile = new PropertyFile();
        System.setProperty("webdriver.chrome.driver", propertyFile.getDriverPath());
        ChromeOptions options = new ChromeOptions();
        options.addArguments("ignore-certificate-errors");
        /*
         * Setting headless argument
         * Recommended port for headless mode is 9222.
         * https://stackoverflow.com/a/58045991/2323627
         */
        //options.addArguments("--headless");
        //options.addArguments("--remote-debugging-port=9222");
        options.addArguments("window-size=1920,1080");
        options.addArguments("--start-maximized");
        options.addArguments("--lang=de-DE");
        //Dateien herunterladen statt anzeigen:
        HashMap<String, Object> chromePrefs = new HashMap<String, Object>();
        chromePrefs.put("profile.default_content_settings.popups", 0);
        String downloadFilepath = Paths.get("").toAbsolutePath().toString();
        chromePrefs.put("download.default_directory", downloadFilepath); // Bypass default download directory in Chrome
        //chromePrefs.put("safebrowsing.enabled", "false"); // Bypass warning message, keep file anyway (for .exe, .jar, etc.)
        //chromePrefs.put("plugins.always_open_pdf_externally", true);

        options.setExperimentalOption("prefs", chromePrefs);

        driver = new ChromeDriver(options);
        return driver;
    }

    /**
     * Currently, the code uses element.getAttribute("value") to get text from
     * text box.This can also be replaced with element.getText() based on the
     * type of text box.
     * https://medium.com/@hanikhan18/tackling-issues-related-to-missing-characters-in-selenium-sendkeys-on-ie-42846d90d97b
     *
     * @throws Exception if the text could not be correctly written.
     * @param element
     * @param stringToEnter
     */
    public static void CustomSendKeysIE(WebElement element, String stringToEnter) throws Exception {
        //Convert String to be entered to a character array
        char[] charsToEnter = stringToEnter.toCharArray();

        //Iterate characters in the character array over a loop
        for (int i = 0; i < charsToEnter.length; i++) {
            try {
                //Adding a sleep of 500 milliseconds to handle any sync issues
                Thread.sleep(50);
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
                        System.err.println("Throw Exception");
                        throw new Exception("Error while trying to type text.");
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
                        System.err.println("Throw Exception");
                        //throw new InterruptedException("Bam");
                    }
                }
            }
        }
    }

    /**
     * Fills a date input field with the provided LocalDate value using
     * JavaScript execution.
     *
     * This method takes a WebElement representing a date input field and a
     * LocalDate object containing the date to be filled into the input field.
     * It utilizes JavaScript execution to directly set the value of the input
     * field to the formatted date string. The date is formatted according to
     * the specified format using the
     * Employee.DATE_TIME_FORMATTER_YEAR_MONTH_DAY formatter.
     *
     * @param dateInputElement The WebElement representing the date input field
     * to be filled.
     * @param localDate The LocalDate object containing the date to be set in
     * the input field.
     * @since 2023-08-08
     */
    public static void fillDateInput(WebElement dateInputElement, LocalDate localDate) {
        JavascriptExecutor jsExecutor = (JavascriptExecutor) driver;
        String dateString = localDate.format(DATE_TIME_FORMATTER_YEAR_MONTH_DAY);
        jsExecutor.executeScript("arguments[0].value = arguments[1];", dateInputElement, dateString);
    }

    /**
     * Fills a date input field with the provided date string using JavaScript
     * execution.
     *
     * This method takes a WebElement representing a date input field and a
     * date string in the format specified by
     * Employee.DATE_TIME_FORMATTER_DAY_MONTH_YEAR. The date string is parsed
     * into a LocalDate object, and then the fillDateInput method is called to
     * populate the input field using the parsed date.
     *
     * @param dateInputElement The WebElement representing the date input field
     * to be filled.
     * @param localDateString The date string to be parsed and set in the input field.
     * @since 2023-08-08
     */
    public static void fillDateInput(WebElement dateInputElement, String localDateString) {
        LocalDate localDate = LocalDate.parse(localDateString, DATE_TIME_FORMATTER_DAY_MONTH_YEAR);
        fillDateInput(dateInputElement, localDate);
    }

    public static void printBrowserConsole() {
        System.err.println("printBrowserConsole():");
        /**
         * Get log from console.log() inside the browser: Mentioning type of Log
         */
        LogEntries logEntries = driver.manage().logs().get(LogType.BROWSER);
        // Retrieving all log
        List<LogEntry> logs = logEntries.getAll();
        // Print one by one
        for (LogEntry entry : logs) {
            System.err.println(entry);
        }

    }

    public static By getByFromElement(WebElement element) {
        By by = null;
        //[[ChromeDriver: chrome on XP (d85e7e220b2ec51b7faf42210816285e)] -> xpath: //input[@title='Search']]
        String[] pathVariables = (element.toString().split("->")[1].replaceFirst("(?s)(.*)\\]", "$1" + "")).split(":");

        String selector = pathVariables[0].trim();
        String value = pathVariables[1].trim();

        switch (selector) {
            case "id":
                by = By.id(value);
                break;
            case "className":
                by = By.className(value);
                break;
            case "tagName":
                by = By.tagName(value);
                break;
            case "xpath":
                by = By.xpath(value);
                break;
            case "cssSelector":
                by = By.cssSelector(value);
                break;
            case "linkText":
                by = By.linkText(value);
                break;
            case "name":
                by = By.name(value);
                break;
            case "partialLinkText":
                by = By.partialLinkText(value);
                break;
            default:
                throw new IllegalStateException("locator : " + selector + " not found!!!");
        }
        return by;
    }

    /**
     * Checks if a specific text is present among the options within a Select element.
     *
     * @param select The Select element to examine for the presence of the specified text.
     * @param optionText The text to be checked for existence within the options of the Select element.
     * @return true if the specified text is found among the options, false otherwise.
     */
    public static boolean isOptionTextPresent(Select select, String optionText) {
        // Retrieve the list of options from the Select element
        List<WebElement> options = select.getOptions();

        // Iterate through the options to find a match with the specified text
        for (WebElement option : options) {
            // Check if the text of the current option matches the specified text
            if (option.getText().equals(optionText)) {
                // Return true if a match is found
                return true;
            }
        }
        // Return false if the specified text is not found among the options
        return false;
    }

    public static void ringTheBell() {
        // Using Unicode escape sequence for the bell character
        String bell = "\u0007";
        // Printing the bell sound
        System.out.println(bell + "Bell sound!");
    }

    public static void printStackTrace() {
        // Get the stack trace
        StackTraceElement[] stackTrace = Thread.currentThread().getStackTrace();

        // Print the stack trace elements
        for (StackTraceElement element : stackTrace) {
            System.out.println(element);
        }
    }
}
