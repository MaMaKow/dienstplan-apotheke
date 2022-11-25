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
package Selenium.administrationpages;

import Selenium.PropertyFile;
import Selenium.ScreenShot;
import Selenium.signin.SignInPage;
import java.util.HashMap;
import java.util.Map;
import org.testng.annotations.Test;
import org.testng.Assert;

import org.openqa.selenium.WebDriver;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;

/**
 *
 * @author Mandelkow
 * @CAVE: This test will stop working after 2026-08-28!
 * <p lang=de>
 * Der Samstagsplan füllt nur in die Zukunft sicher mit Daten. Samstage in der
 * Vergangenheit werden nicht immer befüllt. Daher wird irgendwann im Jahr 2026
 * der Samstagsplan für 2026 nicht mehr korrekt passen.
 * </p>
 */
public class TestSaturdayRotationTeamsPage {

    WebDriver driver;

    @Test(enabled = true)/*passed*/
    public void testBranchNavigation() {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_name = propertyFile.getPdrUserName();
        signInPage.loginValidUser();
        SaturdayRotationTeamsPage saturdayRotationTeamsPage = new SaturdayRotationTeamsPage(driver);
        Assert.assertEquals(saturdayRotationTeamsPage.getUserNameText(), pdr_user_name);
        saturdayRotationTeamsPage.selectBranch(2);
        Assert.assertEquals(saturdayRotationTeamsPage.getBranchId(), 2);
        saturdayRotationTeamsPage.selectBranch(1);
        Assert.assertEquals(saturdayRotationTeamsPage.getBranchId(), 1);
    }

    @Test(enabled = true)
    public void testAddingTeams() {
        driver = Selenium.driver.Wrapper.getDriver();
        PropertyFile propertyFile = new PropertyFile();
        String urlPageTest = propertyFile.getUrlPageTest();
        driver.get(urlPageTest);

        /**
         * Sign in:
         */
        SignInPage signInPage = new SignInPage(driver);
        String pdr_user_name = propertyFile.getPdrUserName();
        signInPage.loginValidUser();
        SaturdayRotationTeamsPage saturdayRotationTeamsPage = new SaturdayRotationTeamsPage(driver);
        Assert.assertEquals(saturdayRotationTeamsPage.getUserNameText(), pdr_user_name);

        /**
         * <p lang=de>
         * Wenn man wollte, könnte man die saturdayTeamList in einer json Datei
         * verwalten und zum Lesen dieser Datei eine weitere Klasse
         * erschaffen.</p>
         */
        HashMap<Integer, SaturdayRotationTeam> saturdayTeamList = new HashMap<>();
        SaturdayRotationTeam saturdayRotationTeam0 = new SaturdayRotationTeam(null, new int[]{3, 10});
        SaturdayRotationTeam saturdayRotationTeam1 = new SaturdayRotationTeam(null, new int[]{1, 13});
        SaturdayRotationTeam saturdayRotationTeam2 = new SaturdayRotationTeam(null, new int[]{5, 12});
        SaturdayRotationTeam saturdayRotationTeam3 = new SaturdayRotationTeam(null, new int[]{2, 6});
        SaturdayRotationTeam saturdayRotationTeam4 = new SaturdayRotationTeam(null, new int[]{14, 7});
        saturdayTeamList.put(0, saturdayRotationTeam0);
        saturdayTeamList.put(1, saturdayRotationTeam1);
        saturdayTeamList.put(2, saturdayRotationTeam2);
        saturdayTeamList.put(3, saturdayRotationTeam3);
        saturdayTeamList.put(4, saturdayRotationTeam4);
        for (Map.Entry<Integer, SaturdayRotationTeam> saturdayTeamEntry : saturdayTeamList.entrySet()) {
            SaturdayRotationTeam saturdayRotationTeamShould = saturdayTeamEntry.getValue();

            /**
             * <p lang=de>CAVE: saturdayTeamList hat einen Index. Der ist nicht
             * zwingend auch die finale teamId. Wir haben keinen Einfluss
             * darauf, dass diese TeamId auch angelegt wird. Statt
             * HashMap<Integer, SaturdayRotationTeam> könnte man ein Set ohne
             * Index verwenden? Aber es muss die Sortierung fest stehen!
             * </p>
             */
            saturdayRotationTeamsPage.addTeam(saturdayRotationTeamShould);
            SaturdayRotationTeam saturdayRotationTeamFound = saturdayRotationTeamsPage.getTeamById(saturdayRotationTeamShould.getTeamId());
            Assert.assertEquals(saturdayRotationTeamFound.getListOfTeamMembers(), saturdayRotationTeamShould.getListOfTeamMembers());
        }

    }

    @BeforeMethod
    public void setUp() {
        Selenium.driver.Wrapper.createNewDriver();
    }

    @AfterMethod
    public void tearDown(ITestResult testResult) {
        driver = Selenium.driver.Wrapper.getDriver();
        new ScreenShot(testResult);
        if (testResult.getStatus() != ITestResult.FAILURE) {
            driver.quit();
        }
    }

}
