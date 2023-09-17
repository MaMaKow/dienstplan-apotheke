/*
 * Copyright (C) 2023 Mandelkow
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
package Selenium.driver;

/**
 * This code has been written by ChatGPT.
 *
 * @author Mandelkow
 */
import java.io.File;

public class FileAvailabilityChecker {

    public static void main(String[] args) throws Exception {
        String filePath = "path/to/downloaded/file";
        File downloadedFile = new File(filePath);

        // Wait for the file to become available
        waitForFileAvailability(downloadedFile);

        // Proceed with further actions once the file is available
        if (downloadedFile.exists()) {
            // File is available.
            // Perform actions on the downloaded file
        } else {
            //File was not found.
        }
    }

    public static void waitForFileAvailability(File file) throws Exception {

        int maxAttempts = 10;
        int attempt = 0;
        int waitIntervalMillis = 1000;

        while (attempt < maxAttempts) {
            boolean fileExistence = file.exists();
            if (true == fileExistence) {
                return; // File is available, exit the loop
            }

            // File is not available yet, wait and try again
            try {
                Thread.sleep(waitIntervalMillis);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
            attempt++;
        }
        throw new Exception("File did not become available within the specified attempts.");
    }
}
