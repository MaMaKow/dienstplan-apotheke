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
import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

/**
 *
 * @author Mandelkow
 */
public class AboutPage extends BasePage {

    private final By pdrVersionSpanBy = By.id("pdrVersionSpan");

    public AboutPage(WebDriver driver) {
        super(driver);
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToApplicationAbout);
    }

    public String getVersion() {
        driver = Selenium.driver.Wrapper.getDriver();
        WebElement pdrVersionSpanElement = driver.findElement(pdrVersionSpanBy);
        return pdrVersionSpanElement.getText();
    }

    public String getVersionStringShould() {
        String[] commandPwd = {"pwd"};
        String[] commandGit = {"git", "describe", "--abbrev=0", "--tags"};
        try {
            // Set working directory if necessary:
            ProcessBuilder builderPwd = new ProcessBuilder(commandPwd);
            Process processPwd = builderPwd.start();
            processPwd.waitFor();
            try (BufferedReader input = new BufferedReader(new InputStreamReader(processPwd.getInputStream()))) {
                String line = input.readLine();
                LogCollector.debug("pwd output: " + line);
            }
            // ProcessBuilder builder = new ProcessBuilder(command).directory(new File("path/to/git/repo"));
            ProcessBuilder builder = new ProcessBuilder(commandGit);

            LogCollector.debug("Starting git process");
            Process process = builder.start();

            // Wait for the process to complete before reading output
            process.waitFor();
            int exitCode = process.exitValue();
            LogCollector.debug("Git process exited with code: " + exitCode);

            //Capture Errors:
            try (BufferedReader errorInput = new BufferedReader(
                    new InputStreamReader(process.getErrorStream()))) {
                String errorLine;
                while ((errorLine = errorInput.readLine()) != null) {
                    LogCollector.error("Git error: " + errorLine);
                }
            }

            // Capture the output:
            try (BufferedReader input = new BufferedReader(new InputStreamReader(process.getInputStream()))) {
                String line = input.readLine();
                LogCollector.debug("Git version output: " + line);
                return line;  // Expected version tag
            }

        } catch (IOException | InterruptedException ex) {
            LogCollector.error("Error executing git command: " + ex.getMessage());
        }

        LogCollector.debug("Returning null due to command failure");
        return null;
    }
}
