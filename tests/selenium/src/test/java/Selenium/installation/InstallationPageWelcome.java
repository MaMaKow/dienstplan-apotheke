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
package Selenium.installation;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

/**
 *
 * @author Mandelkow
 */
public class InstallationPageWelcome {

    By InstallPageWelcomeFormButtonBy;

    WebDriver driver;
    WebElement InstallPageWelcomeFormButtonElement;

    public void moveToRequirementsPage() {
        driver = Selenium.driver.Wrapper.getDriver();
        InstallPageWelcomeFormButtonBy = By.id("InstallPageWelcomeFormButton");
        InstallPageWelcomeFormButtonElement = driver.findElement(InstallPageWelcomeFormButtonBy);
        InstallPageWelcomeFormButtonElement.click();
    }
}
