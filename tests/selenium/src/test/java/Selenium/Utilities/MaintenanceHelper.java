/*
 * Copyright (C) 2026 Martin Mandelkow
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
package Selenium.Utilities;

import Selenium.PropertyFile;
import Selenium.rest_api.ApiHandler;
import java.io.IOException;
import java.net.http.HttpResponse;

/**
 *
 * @author Martin Mandelkow
 */
public class MaintenanceHelper {

    public static void runMaintenance() {
        PropertyFile propertyFile = new PropertyFile();
        String testPageUrl = propertyFile.getTestPageUrl();
        String payload = "forceMaintenance=true";
        String maintenanceEndpoint = testPageUrl + "src/php/background_maintenance.php";
        HttpResponse<String> response;
        try {
            response = ApiHandler.sendPostRequestAsForm(maintenanceEndpoint, payload);
            LogCollector.debug(response.body());
            if (response.body().contains("Done with background maintenance.")) {
                LogCollector.info("Maintenence is done.");
            }
        } catch (IOException | InterruptedException exception) {
            LogCollector.error(exception.getLocalizedMessage());
        }

    }
}
