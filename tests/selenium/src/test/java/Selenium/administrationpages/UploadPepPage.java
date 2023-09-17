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
package Selenium.administrationpages;

import Selenium.MenuFragment;
import com.google.common.base.CharMatcher;
import java.net.URISyntaxException;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.Arrays;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.openqa.selenium.By;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 */
public class UploadPepPage {

    protected static WebDriver driver;
    By user_name_spanBy = By.id("MenuListItemApplicationUsername");

    public UploadPepPage(WebDriver driver) {
        this.driver = driver;

        if (getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToPharmacyUploadPep);

    }

    public void uploadFile() {
        By selectFileBy = By.xpath("//*[@id=\"file_to_upload\"]");
        WebElement selectFileElement = driver.findElement(selectFileBy);
        //Path filePath = Paths.get("PepData.asy").toAbsolutePath();
        String filePathString = "/home/seluser/selenium/PepData.asy";
        selectFileElement.sendKeys(filePathString);
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        // <h1>Hello userName</h1>
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(user_name_spanBy));

        return driver.findElement(user_name_spanBy).getText();
    }

    public boolean expectationIsPresent() {
        WebElement expectationElement;
        String expectationString = "";
        WebDriverWait wait = new WebDriverWait(driver, 20);
        int attempts = 0;
        while (attempts < 5) {
            try {
                expectationElement = wait.until(ExpectedConditions.presenceOfElementLocated(By.xpath("//div[@id=\"expectation\"]")));
                expectationString = expectationElement.getAttribute("data-expectation");
                break;
            } catch (StaleElementReferenceException | NullPointerException exception) {
                System.err.println(exception.getMessage());
                System.err.println(Arrays.toString(exception.getStackTrace()));
            }
            attempts++;
        }

        /**
         * The expectation is filled with something other than "[]":
         */
        return !expectationString.equals("[]");
    }

    public boolean expectationIsPresentAfterWaiting(int maximumReloads) {
        for (int refreshCount = 0; refreshCount < maximumReloads; refreshCount++) {
            if (expectationIsPresent()) {
                return true;
            } else {
                try {
                    Thread.sleep(2000);
                    driver.navigate().refresh();
                } catch (InterruptedException ex) {
                    Logger.getLogger(UploadPepPage.class
                            .getName()).log(Level.SEVERE, null, ex);
                }
            }
        }
        return false;
    }

}
