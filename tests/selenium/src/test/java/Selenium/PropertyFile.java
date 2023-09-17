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

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
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
public class PropertyFile {

    private Properties properties;
    private final String propertyFilePath = "Configuration.properties";

    public PropertyFile() {
        FileInputStream fileInputStream = null;
        try {
            properties = new Properties();
            fileInputStream = new FileInputStream(propertyFilePath);
            properties.load(fileInputStream);
            fileInputStream.close();
        } catch (FileNotFoundException ex) {
            Logger.getLogger(PropertyFile.class.getName()).log(Level.SEVERE, null, ex);
        } catch (IOException ex) {
            Logger.getLogger(PropertyFile.class.getName()).log(Level.SEVERE, null, ex);
        } finally {
            try {
                if (null != fileInputStream) {
                    fileInputStream.close();
                }
            } catch (IOException ex) {
                Logger.getLogger(PropertyFile.class.getName()).log(Level.SEVERE, null, ex);
            }
        }

    }

    public void setTestPageUrl(String testPageUrl) {
        setProperty("testPageUrl", testPageUrl);
    }

    public String getTestPageUrl() {
        String testPageUrl = properties.getProperty("testPageUrl");
        if (null != testPageUrl) {
            return testPageUrl;
        }
        throw new RuntimeException("testPageUrl not specified in the Configuration.properties file.");
    }

    public String getRealTestPageUrl() {
        String testPageUrl = properties.getProperty("testRealPageUrl");
        if (null != testPageUrl) {
            return testPageUrl;
        }
        throw new RuntimeException("testPageUrl not specified in the Configuration.properties file.");
    }

    public String getRealUsername() {
        String testRealUsername = properties.getProperty("testRealUsername");
        if (null != testRealUsername) {
            return testRealUsername;
        }
        throw new RuntimeException("testRealUsername not specified in the Configuration.properties file.");
    }

    public String getRealPassword() {
        String testRealPassword = properties.getProperty("testRealPassword");
        if (null != testRealPassword) {
            return testRealPassword;
        }
        throw new RuntimeException("testRealPassword not specified in the Configuration.properties file.");
    }

    private void setProperty(String propertyName, String propertyValue) {
        FileOutputStream fileOutputStream = null;
        try {
            properties.setProperty(propertyName, propertyValue);
            fileOutputStream = new FileOutputStream(propertyFilePath);
            properties.store(fileOutputStream, "some random comment");
        } catch (FileNotFoundException ex) {
            Logger.getLogger(PropertyFile.class.getName()).log(Level.SEVERE, null, ex);
        } catch (IOException ex) {
            Logger.getLogger(PropertyFile.class.getName()).log(Level.SEVERE, null, ex);
        } finally {
            try {
                fileOutputStream.close();
            } catch (IOException ex) {
                Logger.getLogger(PropertyFile.class.getName()).log(Level.SEVERE, null, ex);
            }
        }
    }

    public String getDriverPath() {
        String driverPath = properties.getProperty("driverPath");
        if (null != driverPath) {
            return driverPath;
        }
        throw new RuntimeException("driverPath not specified in the Configuration.properties file.");
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
        String urlPageTest = getTestPageUrl();
        //String urlPageTest = properties.getProperty("urlPageTest");
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
