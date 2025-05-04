/*
 * Copyright (C) 2025 Mandelkow
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
package Selenium.Utilities;

/**
 *
 * @author Mandelkow
 */
import Selenium.Utilities.LogCollector;
import org.testng.Assert;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.util.List;

/**
 * Unit tests for the class LogCollector
 *
 * @author Mandelkow
 */
public class LogCollectorTest {

    @BeforeMethod
    public void setUp() {
        // Clear log messages before each test to ensure no interference
        LogCollector.clear();
    }

    @Test
    public void testAddMessage() {
        // Add a message using LogCollector
        LogCollector.debug("Test debug message");

        // Retrieve log messages
        List<String> messages = (List<String>) LogCollector.getMessages();

        // Assert that the message was added
        Assert.assertEquals(messages.size(), 1, "Log should contain 1 message.");
        Assert.assertEquals(messages.get(0), "DEBUG Test debug message", "Message content mismatch.");
    }

    @Test
    public void testClearMessages() {
        // Add a message and then clear
        LogCollector.debug("Test message before clear");
        LogCollector.clear();

        // Assert that the messages have been cleared
        List<String> messages = (List<String>) LogCollector.getMessages();
        Assert.assertTrue(messages.isEmpty(), "Messages should be cleared.");
    }

    @Test
    public void testThreadLocalBehavior() throws InterruptedException {
        // Add messages in the main thread
        LogCollector.debug("Main thread message");

        // Create a new thread to add messages in it
        Thread newThread = new Thread(() -> LogCollector.debug("New thread message"));
        newThread.start();
        newThread.join(); // Wait for the new thread to finish

        // Assert that main thread has its own messages
        List<String> mainThreadMessages = (List<String>) LogCollector.getMessages();
        Assert.assertTrue(mainThreadMessages.contains("DEBUG Main thread message"), "Main thread should have its message.");

        // Assert that the new thread's message does not appear in the main thread's log
        List<String> threadMessages = (List<String>) LogCollector.getMessages();
        Assert.assertFalse(threadMessages.contains("DEBUG New thread message"), "New thread message should not appear in main thread.");
    }

    @Test
    public void testMessageFormatting() {
        // Add a message
        LogCollector.debug("Formatted message test");

        // Retrieve the message and check if it's correctly formatted
        List<String> messages = (List<String>) LogCollector.getMessages();
        Assert.assertTrue(messages.get(0).startsWith("DEBUG "), "Message should start with log level.");
        Assert.assertTrue(messages.get(0).contains("Formatted message test"), "Message content mismatch.");
    }
}
