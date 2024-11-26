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
package Selenium.signin;

import Selenium.User;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

/**
 *
 * @author Mandelkow
 */
public class RegisterPage extends Selenium.BasePage {

    private final By userNameBy = By.xpath("/html/body/div/form/input[@name='user_name']");
    private final By userEmailBy = By.xpath("/html/body/div/form/input[@name='email']");
    private final By userPassphraseBy = By.xpath("/html/body/div/form/input[@name='password']");
    private final By repeatPassphraseBy = By.xpath("/html/body/div/form/input[@name='password2']");
    private final By mathProblemBy = By.xpath("/html/body/div/form/label");
    private final By mathProblemSolutionInputBy = By.xpath("//*[@id=\"mathProblemSolution\"]");
    private final By submitButtonBy = By.xpath("/html/body/div/form/input[@type='submit']");

    private final WebDriver driver;

    public RegisterPage(WebDriver driver) {
        super(driver);  // Call to BasePage constructor
        this.driver = driver;
    }

    public void registerUser(User user) {
        logWithDetails("register user");
        String userName = user.getUserName();
        String userEmail = user.getUserEmail();
        String passphrase = user.getPassphrase();
        try {
            WebElement userNameElement = driver.findElement(userNameBy);
            userNameElement.clear();
            userNameElement.sendKeys(userName);
        } catch (Exception exception) {
            logger.error(driver.getCurrentUrl());
            logger.error(driver.getPageSource());
            throw exception;
        }
        WebElement userEmailElement = driver.findElement(userEmailBy);
        userEmailElement.clear();
        userEmailElement.sendKeys(userEmail);
        WebElement userPassphraseElement = driver.findElement(userPassphraseBy);
        userPassphraseElement.clear();
        userPassphraseElement.sendKeys(passphrase);
        WebElement repeatPassphraseElement = driver.findElement(repeatPassphraseBy);
        repeatPassphraseElement.clear();
        repeatPassphraseElement.sendKeys(passphrase);
        WebElement mathProblemElement = driver.findElement(mathProblemBy);
        WebElement submitButtonElement = driver.findElement(submitButtonBy);
        /**
         * Solve the math problem:
         */
        String mathProblemString = mathProblemElement.getText();
        // Extract the numbers from the math problem string, assuming format "What does X + Y equal?"
        String[] mathProblemParts = mathProblemString.split("\\D+");  // Split by non-digit characters

        int mathProblemSummand1 = Integer.parseInt(mathProblemParts[1]); // The first number
        int mathProblemSummand2 = Integer.parseInt(mathProblemParts[2]); // The second number

        String mathProblemSolution = String.valueOf(mathProblemSummand1 + mathProblemSummand2);
        WebElement mathProblemSolutionElement = driver.findElement(mathProblemSolutionInputBy);
        mathProblemSolutionElement.clear();
        mathProblemSolutionElement.sendKeys(mathProblemSolution);
        /**
         * Submit:
         */
        submitButtonElement.click();
    }
}
