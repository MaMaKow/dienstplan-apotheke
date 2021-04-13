<h1>Welcome to Installation</h1>

<p>With this option, it is possible to install PDR onto your server.</p>

<p>In order to proceed, you will need your database settings. If you do not know your database settings, please contact your host and ask for them. You will not be able to continue without them. You need:
<ul>
    <li>The Database Type - the database you will be using.</li>
    <li>The Database server hostname or DSN - the address of the database server.</li>
    <li>The Database server port - the port of the database server (most of the time this is not needed).</li>
    <li>The Database name - the name of the database on the server.</li>
    <li>The Database username and Database password - the login data to access the database.</li>

</ul>

<p>PDR currently only supports the following database system:
<ul>
    <li>MySQL 3.23 or above (MySQLi supported)</li>
</ul>

</p>
<p>
    After you enter your root or administrator login for your database, the installer creates a special database user with privileges limited to the pdr database.
    Then pdr needs only the special pdr database user, and drops the root database login.
    This user is named pdr and then given a random password.
    The pdr database user and password are written into config.php
    <br>
    If the database does not exist yet, the installer tries to create it.
    If the pdr user can not be created, then the installer will fallback to using the given administrator user and password.
    This user might have more than the necessary privileges.
</p>
<form action="install_page_check_requirements.php" method="post">
    <input type="submit" id="InstallPageWelcomeFormButton" value="<?= gettext("Next") ?>">
</form>
