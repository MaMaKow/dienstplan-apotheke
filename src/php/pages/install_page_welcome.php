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

<del>Note: if you are installing using SQLite, you should enter the full path to your database file in the DSN field and leave the username and password fields blank. For security reasons, you should make sure that the database file is not stored in a location accessible from the web.</del>

<p>PDR supports the following databases:
<ul>
    <li>MySQL 3.23 or above (MySQLi supported)</li>
    <del>
        <li>PostgreSQL 8.3+</li>
        <li>SQLite 3.6.15+</li>
        <li>MS SQL Server 2000 or above (directly or via ODBC)</li>
        <li>MS SQL Server 2005 or above (native)</li>
        <li>Oracle</li>
    </del>

</ul>

</p>