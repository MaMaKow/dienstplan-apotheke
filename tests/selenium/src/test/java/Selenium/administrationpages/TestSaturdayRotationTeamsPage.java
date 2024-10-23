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

import Selenium.TestPage;
import java.util.HashMap;
import java.util.Map;
import org.testng.annotations.Test;
import org.testng.Assert;
import org.testng.asserts.SoftAssert;

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
public class TestSaturdayRotationTeamsPage extends TestPage {

    @Test(enabled = true)/*passed*/
    public void testBranchNavigation() {
        /**
         * Sign in:
         */
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        SaturdayRotationTeamsPage saturdayRotationTeamsPage = new SaturdayRotationTeamsPage(driver);
        saturdayRotationTeamsPage.selectBranch(2);
        Assert.assertEquals(saturdayRotationTeamsPage.getBranchId(), 2);
        saturdayRotationTeamsPage.selectBranch(1);
        Assert.assertEquals(saturdayRotationTeamsPage.getBranchId(), 1);
    }

    @Test(enabled = true)
    public void testAddingTeams() {
        /**
         * Sign in:
         */
        SoftAssert softAssert = new SoftAssert();
        try {
            super.signIn();
        } catch (Exception exception) {
            logger.error("Sign in failed.");
            Assert.fail();
        }
        SaturdayRotationTeamsPage saturdayRotationTeamsPage = new SaturdayRotationTeamsPage(driver);
        /**
         * <p lang=de>
         * Wenn man wollte, könnte man die saturdayTeamList in einer json Datei
         * verwalten und zum Lesen dieser Datei eine weitere Klasse
         * erschaffen.</p>
         */
        HashMap<Integer, SaturdayRotationTeam> saturdayTeamList = SaturdayRotationTeam.getSaturdayTeams();
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
            softAssert.assertTrue(saturdayRotationTeamFound.equalsTeam(saturdayRotationTeamShould), "Teams saturdayRotationTeamFound and saturdayRotationTeamShould differ.");
        }
        softAssert.assertAll();
    }

}
