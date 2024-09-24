/*
 * Copyright (C) 2024 Mandelkow
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

import static Selenium.HomePage.driver;
import java.time.Duration;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 */
public class BasePage {

    protected static WebDriver driver;
    protected final By userNameSpanBy = By.id("MenuListItemApplicationUsername");
    public final WebDriverWait waitShort = new WebDriverWait(driver, Duration.ofMillis(100));
    public final WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(3));
    public final WebDriverWait waitLong = new WebDriverWait(driver, Duration.ofSeconds(10));
    public final Logger logger;

    public BasePage() {
        this.logger = LogManager.getLogger(this.getClass());
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        wait.until(ExpectedConditions.presenceOfElementLocated(userNameSpanBy));
        return driver.findElement(userNameSpanBy).getText();
    }

    public void logWithDetails(String message) {
        StackTraceElement[] stackTrace = Thread.currentThread().getStackTrace();
        // Get the caller method, class, and line number (skip 0 and 1 to find caller)
        StackTraceElement caller = stackTrace[2];
        String className = caller.getClassName();
        String methodName = caller.getMethodName();
        int lineNumber = caller.getLineNumber();

        logger.debug("{} - {}:{} - {}", className, methodName, lineNumber, message);
    }

}
