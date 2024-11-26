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
        String[] command = {"git", "describe", "--abbrev=0", "--tags"};
        try {
            // Set working directory if necessary:
            // ProcessBuilder builder = new ProcessBuilder(command).directory(new File("path/to/git/repo"));
            ProcessBuilder builder = new ProcessBuilder(command);

            logger.debug("Starting git process");
            Process process = builder.start();

            // Wait for the process to complete before reading output
            process.waitFor();

            // Capture the output
            try (BufferedReader input = new BufferedReader(new InputStreamReader(process.getInputStream()))) {
                String line = input.readLine();
                logger.debug("Git version output: " + line);
                return line;  // Expected version tag
            }

        } catch (IOException | InterruptedException ex) {
            logger.error("Error executing git command: " + ex.getMessage());
        }

        logger.debug("Returning null due to command failure");
        return null;
    }
}
