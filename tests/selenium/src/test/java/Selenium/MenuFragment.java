package Selenium;

import java.util.HashMap;
import java.util.Map;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;

/**
 *
 * @author Mandelkow
 */
public class MenuFragment {

    /**
	 * By objects for the menu links
	 */
	public static final By MenuLinkToRosterWeekTable = By.id("MenuLinkToRosterWeekTable");
    public static final By MenuLinkToRosterWeekImages = By.id("MenuLinkToRosterWeekImages");
    public static final By MenuLinkToRosterDayEdit = By.id("MenuLinkToRosterDayEdit");
    public static final By MenuLinkToRosterDayRead = By.id("MenuLinkToRosterDayRead");
    public static final By MenuLinkToPrincipleRosterDay = By.id("MenuLinkToPrincipleRosterDay");
    public static final By MenuLinkToRosterEmployee = By.id("MenuLinkToRosterEmployee");
    public static final By MenuLinkToPrincipleRosterEmployee = By.id("MenuLinkToPrincipleRosterEmployee");
    public static final By MenuLinkToRosterHoursList = By.id("MenuLinkToRosterHoursList");
    public static final By MenuLinkToOvertimeEdit = By.id("MenuLinkToOvertimeEdit");
    public static final By MenuLinkToOvertimeRead = By.id("MenuLinkToOvertimeRead");
    public static final By MenuLinkToOvertimeOverview = By.id("MenuLinkToOvertimeOverview");
    public static final By MenuLinkToAbsenceEdit = By.id("MenuLinkToAbsenceEdit");
    public static final By MenuLinkToAbsenceRead = By.id("MenuLinkToAbsenceRead");
    public static final By MenuLinkToAbsenceMonth = By.id("MenuLinkToAbsenceMonth");
    public static final By MenuLinkToAbsencYear = By.id("MenuLinkToAbsenceYear");
    public static final By MenuLinkToAttendanceList = By.id("MenuLinkToAttendanceList");
    public static final By MenuLinkToSaturdayList = By.id("MenuLinkToSaturdayList");
    public static final By MenuLinkToEmergencyServiceList = By.id("MenuLinkToEmergencyServiceList");
    public static final By MenuLinkToPharmacyUploadPep = By.id("MenuLinkToPharmacyUploadPep");
    public static final By MenuLinkToManageEmployee = By.id("MenuLinkToManageEmployee");
    public static final By MenuLinkToManageBranch = By.id("MenuLinkToManageBranch");
    public static final By MenuLinkToManageUser = By.id("MenuLinkToManageUser");
    public static final By MenuLinkToManageAccount = By.id("MenuLinkToManageAccount");
    public static final By MenuLinkToApplicationAbout = By.id("MenuLinkToApplicationAbout");
    public static final By MenuLinkToLogout = By.id("MenuLinkToLogout");

    /**
	 * By objects for the menu list items (=headings)
	 */
    public static final By MenuListItemWeek = By.id("MenuListItemWeek");
    public static final By MenuListItemDay = By.id("MenuListItemDay"); //TODO: Implement in the menu.php
    public static final By MenuListItemEmployee = By.id("MenuListItemEmployee"); //TODO: Implement in the menu.php
    public static final By MenuListItemOvertime = By.id("MenuListItemOvertime"); //TODO: Implement in the menu.php
    public static final By MenuListItemAbsence = By.id("MenuListItemAbsence"); //TODO: Implement in the menu.php
    public static final By MenuListItemAdministration = By.id("MenuListItemAdministration"); //TODO: Implement in the menu.php
    public static final By MenuListItemApplication = By.id("MenuListItemApplication"); //TODO: Implement in the menu.php
    public static Map<By, By> menuMap = new HashMap<By, By>();

    public static void navigateTo(WebDriver driver, By target) {
        menuMap.put(MenuLinkToRosterWeekTable, MenuListItemWeek);
        menuMap.put(MenuLinkToRosterWeekImages, MenuListItemWeek);
        menuMap.put(MenuLinkToRosterDayEdit, MenuListItemDay);
        menuMap.put(MenuLinkToRosterDayRead, MenuListItemDay);
        menuMap.put(MenuLinkToPrincipleRosterDay, MenuListItemDay);
        menuMap.put(MenuLinkToRosterEmployee, MenuListItemEmployee);
        menuMap.put(MenuLinkToPrincipleRosterEmployee, MenuListItemEmployee);
        menuMap.put(MenuLinkToRosterHoursList, MenuListItemEmployee);
        menuMap.put(MenuLinkToOvertimeEdit, MenuListItemOvertime);
        menuMap.put(MenuLinkToOvertimeRead, )MenuListItemOvertime;
        menuMap.put(MenuLinkToOvertimeOverview, MenuListItemOvertime);
        menuMap.put(MenuLinkToAbsenceEdit, MenuListItemAbsence);
        menuMap.put(MenuLinkToAbsenceRead, MenuListItemAbsence);
        menuMap.put(MenuLinkToAbsenceMonth, MenuListItemAbsence);
        menuMap.put(MenuLinkToAbsencYear, MenuListItemAbsence);
        menuMap.put(MenuLinkToAttendanceList, MenuListItemAdministration);
        menuMap.put(MenuLinkToSaturdayList, MenuListItemAdministration);
        menuMap.put(MenuLinkToEmergencyServiceList, MenuListItemAdministration);
        menuMap.put(MenuLinkToPharmacyUploadPep, MenuListItemAdministration);
        menuMap.put(MenuLinkToManageEmployee, MenuListItemAdministration);
        menuMap.put(MenuLinkToManageBranch, MenuListItemAdministration);
        menuMap.put(MenuLinkToManageUser, MenuListItemAdministration);
        menuMap.put(MenuLinkToManageAccount, MenuListItemApplication);
        menuMap.put(MenuLinkToApplicationAbout, MenuListItemApplication);
        menuMap.put(MenuLinkToLogout, MenuListItemApplication);

        /**
         * TODO: Mit der Map von oben im Folgenden das richtige Item zum hovern
         * auswählen...
         */
        WebElement linkElement = driver.findElement(target);
        Actions actions = new Actions(driver);
        /**
         * <p lang=de>
         * Das Element steht im Menü über dem gewünschten Element. Um das
         * Element im Menü überhaupt zu sehen, muss zunächst einmal das
         * übergeordnete Element gehovert werden.
         * </p>
         */
        By menuListItemBy = menuMap.get(target);
        WebElement menuListItem = driver.findElement(menuListItemBy);
        actions.moveToElement(menuListItem).perform();
        linkElement.click();

    }
}
