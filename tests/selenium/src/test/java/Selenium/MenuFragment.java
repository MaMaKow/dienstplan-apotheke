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

    public static final By MenuListItemWeek = By.id("MenuListItemWeek");
    public static Map<By, By> menuMap = new HashMap<By, By>();

    public static void navigateTo(WebDriver driver, By target) {
        menuMap.put(MenuLinkToRosterWeekTable, MenuListItemWeek);
        menuMap.put(MenuLinkToRosterWeekImages, MenuListItemWeek);
        /*
        menuMap.put(MenuLinkToRosterDayEdit, );
    menuMap.put(MenuLinkToRosterDayRead, );
    menuMap.put(MenuLinkToPrincipleRosterDay, );
    menuMap.put(MenuLinkToRosterEmployee, );
    menuMap.put(MenuLinkToPrincipleRosterEmployee, );
    menuMap.put(MenuLinkToRosterHoursList, );
    menuMap.put(MenuLinkToOvertimeEdit, );
    menuMap.put(MenuLinkToOvertimeRead, );
    menuMap.put(MenuLinkToOvertimeOverview, );
    menuMap.put(MenuLinkToAbsenceEdit, );
    menuMap.put(MenuLinkToAbsenceRead, );
    menuMap.put(MenuLinkToAbsenceMonth, );
    menuMap.put(MenuLinkToAbsencYear, );
    menuMap.put(MenuLinkToAttendanceList, );
    menuMap.put(MenuLinkToSaturdayList, );
    menuMap.put(MenuLinkToEmergencyServiceList, );
    menuMap.put(MenuLinkToPharmacyUploadPep, );
    menuMap.put(MenuLinkToManageEmployee, );
    menuMap.put(MenuLinkToManageBranch, );
    menuMap.put(MenuLinkToManageUser, );
    menuMap.put(MenuLinkToManageAccount, );
    menuMap.put(MenuLinkToApplicationAbout, );
    menuMap.put(MenuLinkToLogout, );
         */
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
