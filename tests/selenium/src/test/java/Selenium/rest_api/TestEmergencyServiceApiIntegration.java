package Selenium.rest_api;

import Selenium.Employee;
import Selenium.administrationpages.EmergencyServiceListPage;
import Selenium.driver.Wrapper;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import java.net.http.HttpResponse;
import java.time.LocalDate;
import java.time.Month;
import org.testng.Assert;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Listeners;
import org.testng.annotations.Test;

@Listeners(Selenium.Utilities.Listener.class)
public class TestEmergencyServiceApiIntegration extends Selenium.TestPage {

    private final LocalDate testDate = LocalDate.of(2025, Month.OCTOBER, 15);
    private final String testEmployeeName = "Anabell Neuhaus";
    private final int testBranchId = 1;
    private EmergencyServiceListPage emergencyServiceListPage;

    @BeforeMethod(alwaysRun = true)
    public void setUpTest() {
        try {
            super.signIn();
        } catch (Exception e) {
            Assert.fail("Sign-in für Integrationstest fehlgeschlagen: " + e.getMessage());
        }
    }

    @Test(groups = "emptyInstance")
    public void testEmergencyServiceCreationAndApiRead() throws Exception {
        // 1. Workforce-Lookup vorab: Employee-Objekt zum Namen ermitteln
        Employee expectedEmployee = workforce.getEmployeeByFullName(testEmployeeName);
        Assert.assertNotNull(expectedEmployee, "Test-Mitarbeiter '" + testEmployeeName + "' existiert nicht in workforce.json");

        // 2. Notdienst-Zeile über die UI anlegen
        emergencyServiceListPage = new EmergencyServiceListPage(driver);
        emergencyServiceListPage.selectYear(testDate.getYear());
        emergencyServiceListPage.selectBranch(testBranchId);

        emergencyServiceListPage = emergencyServiceListPage.addLineForDate(testDate);
        emergencyServiceListPage = emergencyServiceListPage.setEmployeeNameOnDate(testDate, testEmployeeName);

        Assert.assertEquals(emergencyServiceListPage.getEmployeeFullNameOnDate(testDate), testEmployeeName);

        // 3. Notdienst-Daten über den ApiHandler abrufen
        String baseUrl = driver.getCurrentUrl().split("/src/")[0];
        String apiUrl = baseUrl + "/src/php/restful-api/emergency-services/" + testDate.getYear() + "/branches/" + testBranchId;

        HttpResponse<String> response = ApiHandler.sendAuthorizedGetRequest(apiUrl, null);
        Assert.assertEquals(response.statusCode(), 200, "API lieferte einen unerwarteten HTTP-Statuscode.");

        // 4. JSON via Gson parsen und mit dem Workforce-Employee abgleichen
        JsonArray emergencyList = JsonParser.parseString(response.body()).getAsJsonArray();
        boolean foundInApi = false;
        String formattedTestDate = testDate.format(Wrapper.DATE_TIME_FORMATTER_YEAR_MONTH_DAY);

        for (JsonElement element : emergencyList) {
            JsonObject entry = element.getAsJsonObject();

            if (entry.has("date") && formattedTestDate.equals(entry.get("date").getAsString())) {
                foundInApi = true;

                // Lookup-Vergleich: API-employeeKey gegen expectedEmployee.getEmployeeKey()
                int apiEmployeeKey = entry.get("employeeKey").getAsInt();
                Assert.assertEquals(
                        apiEmployeeKey,
                        expectedEmployee.getEmployeeKey(),
                        "Der employeeKey in der API stimmt nicht mit dem Mitarbeiter aus der Workforce überein."
                );

                Assert.assertEquals(
                        entry.get("branchId").getAsInt(),
                        testBranchId,
                        "Die Filial-ID in der API stimmt nicht überein."
                );
                break;
            }
        }

        Assert.assertTrue(
                foundInApi,
                "Der über die UI angelegte Notdienst am " + formattedTestDate + " wurde nicht in der API-Antwort gefunden."
        );
    }

    @AfterMethod(alwaysRun = true)
    public void tearDownTestData() {
        if (emergencyServiceListPage != null && emergencyServiceListPage.rowExistsOnDate(testDate)) {
            emergencyServiceListPage.removeLineByDate(testDate);
        }
    }
}
