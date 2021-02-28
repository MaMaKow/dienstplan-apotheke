/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Selenium;

import org.junit.Assert;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

/**
 *
 * @author Mandelkow Page Object encapsulates the Home Page
 */
public class HomePage {

    protected static WebDriver driver;

    // <h1>Hello userName</h1>
    private By user_name_spanBy = By.id("MenuListItemApplicationUsername");

    public HomePage(WebDriver driver) {
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            //if (result.isEmpty()) {
            throw new IllegalStateException("This is not Home Page of logged in user,"
                    + " current page is: " + driver.getCurrentUrl());
        }
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        return driver.findElement(user_name_spanBy).getText();
    }

    public HomePage manageProfile() {
        // Page encapsulation to manage profile functionality
        return new HomePage(driver);
    }
}
