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
package Selenium.overtimepages;

import Selenium.MenuFragment;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 */
public class OvertimeOverviewPage {

    protected static WebDriver driver;

    public OvertimeOverviewPage(WebDriver driver) {
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException(
                    "This is not a logged in state," + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToOvertimeOverview);
    }

    /**
     * Get user_name (span tag)
     *
     * We only need this in order to check, if we are logged in.
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        final By userNameSpanBy = By.id("MenuListItemApplicationUsername");
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));

        return driver.findElement(userNameSpanBy).getText();
    }

    public Float getBalanceByEmployeeName(String nameString) {
        By employeeBalanceColumnBy = By.xpath("/html/body/table/tbody/tr/td[contains(text(), '" + nameString + "')]/parent::tr/td[2]");
        WebElement employeeBalanceColumnElement = driver.findElement(employeeBalanceColumnBy);
        return Float.valueOf(employeeBalanceColumnElement.getText());
    }

}
