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
import java.io.File;
import java.io.IOException;
import java.nio.file.StandardCopyOption;
import org.apache.commons.io.FileUtils;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;

/**
 *
 * @author Mandelkow
 */
public class ScreenShot {

    WebDriver driver;

    public ScreenShot() {
    }

    public void takeScreenShot(String packageName, String className, String methodName) {
        String fileName = "errorScreenshots" + File.separator
                + packageName
                + "-"
                + className
                + "-"
                + methodName
                + ".jpg";
        takeScreenShot(fileName);
    }

    public void takeScreenShot(String fileName) {
        driver = Selenium.driver.Wrapper.getDriver();
        File scrFile = ((TakesScreenshot) driver).getScreenshotAs(OutputType.FILE);
        try {
            FileUtils.copyFile(scrFile, new File("errorScreenshots" + File.separator + fileName), true, StandardCopyOption.REPLACE_EXISTING);
        } catch (IOException exception) {
            LogCollector.error(exception.getLocalizedMessage());
        }
    }

}
