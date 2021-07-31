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

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

/**
 *
 * @author Mandelkow
 */
public class AboutPage {

    private WebDriver driver;

    private final By pdrVersionSpanBy = By.id("pdrVersionSpan");

    public String getVersion() {
        driver = Selenium.driver.Wrapper.getDriver();
        WebElement pdrVersionSpanElement = driver.findElement(pdrVersionSpanBy);
        return pdrVersionSpanElement.getText();
    }
}
