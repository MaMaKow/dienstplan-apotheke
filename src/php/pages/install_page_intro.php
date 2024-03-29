<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Introduction to PDR</title>
    </head>
    <body>
        <h1>Introduction</h1>

        <h2>Welcome to PDR!</h2>

        <p>Pharmacy Duty Roster (PDR) is a web application designed to streamline and manage duty schedules for pharmacies effectively. It provides an alternative to traditional methods like excel sheets, offering user-friendly features while covering all necessary aspects of duty roster management.</p>

        <p>PDR, initiated in 2015, aims to continuously improve based on user feedback. Your requests and wishes are valued contributions to its development, and it strives to meet your expectations.</p>

        <p>These installation pages will guide you through the installation process of PDR. For more detailed instructions, please refer to the installation guide.</p>

        <p>Please make sure to have at least PHP version 8.0 installed.</p>

        <!-- Language Selection Option -->
        <form action="install_page_welcome.php" method="get">
            <p>Select your preferred language:</p>
            <select name="language">
                <option value="en-GB">English</option>
                <option value="de-DE">German</option>
            </select>

            <input type="submit" id="InstallPageIntroFormButton" value="<?= gettext("Next") ?>">
        </form>
    </body>
</html>
