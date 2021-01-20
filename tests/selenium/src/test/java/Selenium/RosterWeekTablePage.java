package Selenium;

import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.Select;

/**
 *
 * @author Mandelkow Page Object encapsulates the Home Page
 */
public class RosterWeekTablePage {

    protected static WebDriver driver;

    private final By userNameSpanBy = By.id("menu_listitem_user_username");
    private final By dateChooserInputBy = By.id("date_chooser_input");
    private final By buttonWeekBackwardBy = By.id("button_week_backward");
    private final By buttonWeekForwardBy = By.id("button_week_forward");
    private final By branchFormSelectBy = By.id("branch_form_select");

    public RosterWeekTablePage(WebDriver driver) {
        this.driver = driver;

        if (this.getUserNameText().isEmpty()) {
            throw new IllegalStateException("This is not a logged in state,"
                    + " current page is: " + driver.getCurrentUrl());
        }
        System.out.println("before navigation with menu");
        MenuFragment.navigateTo(driver, MenuFragment.MenuLinkToRosterWeekTable);
        System.out.println("after navigation with menu");
    }

    /**
     * Get user_name (span tag)
     *
     * @return String user_name text
     */
    public String getUserNameText() {
        return driver.findElement(userNameSpanBy).getText();
    }

    public RosterWeekTablePage manageProfile() {
        // Page encapsulation to manage profile functionality
        return new RosterWeekTablePage(driver);
    }

    public RosterWeekTablePage goToDate(String date) {
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        dateChooserInput.sendKeys(date);
        dateChooserInput.sendKeys(Keys.ENTER);
        return new RosterWeekTablePage(driver);
    }

    public String getDate() {
        WebElement dateChooserInput = driver.findElement(dateChooserInputBy);
        String date_value = dateChooserInput.getAttribute("value");
        return date_value;
    }

    public RosterWeekTablePage moveWeekBackward() {
        WebElement button_week_backward = driver.findElement(buttonWeekBackwardBy);
        button_week_backward.click();
        return new RosterWeekTablePage(driver);
    }

    public RosterWeekTablePage moveWeekForward() {
        WebElement button_week_forward = driver.findElement(buttonWeekForwardBy);
        button_week_forward.click();
        return new RosterWeekTablePage(driver);
    }

    public RosterWeekTablePage selectBranch(int branchId) {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        branchFormSelect.selectByValue(String.valueOf(branchId));
        return new RosterWeekTablePage(driver);
    }

    public int getBranch() {
        Select branchFormSelect = new Select(driver.findElement(branchFormSelectBy));
        int branchId = Integer.parseInt(branchFormSelect.getFirstSelectedOption().getAttribute("value"));
        return branchId;
    }

}
