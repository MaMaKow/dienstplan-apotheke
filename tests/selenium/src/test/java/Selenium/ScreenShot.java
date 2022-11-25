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

import java.io.File;
import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.apache.commons.io.FileUtils;
//import org.apache.commons.io.FileUtils;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;
import org.testng.ITestResult;

/**
 *
 * @author Mandelkow
 */
public class ScreenShot {

    WebDriver driver;

    public ScreenShot(ITestResult testResult) {
        takeScreenShot(testResult);
    }

    public void takeScreenShot(ITestResult testResult) {
        /*
         * TODO: <p lang=de>Leider werden die Bilder überschrieben.
         * Die verschiedenen Klassen haben Testnamen mit den gleichen methods.
         * Daher überschreiben die Bilder der einen Klasse die Bilder der anderen Klasse.</p>
         */
        driver = Selenium.driver.Wrapper.getDriver();
        File scrFile = ((TakesScreenshot) driver).getScreenshotAs(OutputType.FILE);
        try {
            FileUtils.copyFile(scrFile, new File(
                    "errorScreenshots\\"
                    + testResult.getTestClass().getName()
                    + "-"
                    + testResult.getMethod().getMethodName()
                    //+ "-"
                    //+ Arrays.toString(testResult.getParameters())
                    + ".jpg"));
        } catch (IOException exception) {
            Logger.getLogger(ScreenShot.class.getName()).log(Level.SEVERE, null, exception);
        }
    }

}
