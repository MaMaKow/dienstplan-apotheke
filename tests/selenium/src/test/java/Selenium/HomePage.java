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

import Selenium.Utilities.LogCollector;
import org.openqa.selenium.WebDriver;

/**
 *
 * @author Mandelkow Page Object encapsulates the Home Page
 */
public class HomePage extends Selenium.BasePage {

    public HomePage(WebDriver driver) {
        super(driver);  // Call to BasePage constructor
        LogCollector.debug("new HomePage after super(driver)");
        this.driver = driver;

        LogCollector.debug("new HomePage before this.getUserNameText().isEmpty():");
        if (this.getUserNameText().isEmpty()) {
            LogCollector.debug("true == this.getUserNameText().isEmpty()");
            LogCollector.debug("going to throw an exception:");
            //if (result.isEmpty()) {
            throw new IllegalStateException("This is not Home Page of logged in user,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        LogCollector.debug("new HomePage done with constructor.");
    }

    public HomePage manageProfile() {
        // Page encapsulation to manage profile functionality
        return new HomePage(driver);
    }
}
