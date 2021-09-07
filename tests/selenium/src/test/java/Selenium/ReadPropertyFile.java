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
package Selenium;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.Properties;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author Mandelkow
 * @see
 * https://medium.com/@sonaldwivedi/how-to-read-config-properties-file-in-java-6a501dc96b25
 * https://www.toolsqa.com/selenium-cucumber-framework/read-configurations-from-property-file/
 */
public class ReadPropertyFile {

    private Properties properties;
    private final String propertyFilePath = "src/test/Configuration.properties";

    public ReadPropertyFile() {
        properties = new Properties();
        try {
            FileInputStream fileInputStream = new FileInputStream(propertyFilePath);
            properties.load(fileInputStream);
        } catch (FileNotFoundException exception) {
            Logger.getLogger(ReadPropertyFile.class.getName()).log(Level.SEVERE, null, exception);
        } catch (IOException exception) {
            Logger.getLogger(ReadPropertyFile.class.getName()).log(Level.SEVERE, null, exception);
        }

    }

    public String getPdrUserPassword() {
        String pdrUserPassword = properties.getProperty("pdrUserPassword");
        if (null != pdrUserPassword) {
            return pdrUserPassword;
        }
        throw new RuntimeException("pdrUserPassword not specified in the Configuration.properties file.");
    }

    public String getPdrUserName() {
        String pdrUserName = properties.getProperty("pdrUserName");
        if (null != pdrUserName) {
            return pdrUserName;
        }
        throw new RuntimeException("pdrUserName not specified in the Configuration.properties file.");
    }

    public String getUrlPageTest() {
        String urlPageTest = properties.getProperty("urlPageTest");
        if (null != urlPageTest) {
            return urlPageTest;
        }
        throw new RuntimeException("urlPageTest not specified in the Configuration.properties file.");
    }

    public String getUrlInstallTest() {
        String urlInstallTest = properties.getProperty("urlInstallTest");
        if (null != urlInstallTest) {
            return urlInstallTest;
        }
        throw new RuntimeException("urlInstallTest not specified in the Configuration.properties file.");
    }

    public String getDatabaseUserName() {
        String property = properties.getProperty("databaseUserName");
        if (null != property) {
            return property;
        }
        return null;
    }

    public String getDatabasePassword() {
        String property = properties.getProperty("databasePassword");
        if (null != property) {
            return property;
        }
        return null;
    }

    public String getDatabaseName() {
        String property = properties.getProperty("databaseName");
        if (null != property) {
            return property;
        }
        return null;
    }

    public String getAdministratorUserName() {
        return getPdrUserName();
    }

    public String getAdministratorLastName() {
        String property = properties.getProperty("administratorLastName");
        if (null != property) {
            return property;
        }
        return getPdrUserName();
    }

    public String getAdministratorFirstName() {
        String property = properties.getProperty("administratorFirstName");
        if (null != property) {
            return property;
        }
        return getPdrUserName();
    }

    public String getAdministratorEmployeeId() {
        String property = properties.getProperty("administratorEmployeeId");
        if (null != property) {
            return property;
        }
        return null;
    }

    public String getAdministratorEmail() {
        String property = properties.getProperty("administratorEmail");
        if (null != property) {
            return property;
        }
        return null;
    }

    public String getAdministratorPassword() {
        return getPdrUserPassword();
    }

}
