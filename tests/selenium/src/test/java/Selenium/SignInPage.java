/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Selenium;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

/**
 *
 * @author Mandelkow
 *
 * Page Object encapsulates the Sign-in page.
 */
public class SignInPage {

    protected static WebDriver driver;

    // <input name="user_name" type="text" value="">
    private final By usernameBy = By.id("login_input_user_name");
    // <input name="password" type="password" value="">
    private final By passwordBy = By.id("login_input_user_password");
    // <input name="sign_in" type="submit" value="SignIn">
    private final By signinBy = By.id("login_button_submit");

    public SignInPage(WebDriver driver) {
        this.driver = driver;
    }

    /**
     * Login as valid user
     *
     * @param userName
     * @param password
     * @return HomePage object
     */
    public HomePage loginValidUser(String userName, String password) {
        System.out.println(usernameBy);
        WebDriverWait wait = new WebDriverWait(driver, 20);
        wait.until(ExpectedConditions.presenceOfElementLocated(By.id("login_button_submit")));
        driver.findElement(usernameBy).sendKeys(userName);
        driver.findElement(passwordBy).sendKeys(password);
        driver.findElement(signinBy).click();
        return new HomePage(driver);
    }
}
