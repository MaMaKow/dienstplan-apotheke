<?php
echo "runs<br>";
require_once "../classes/class.install.php";
$install = new install;
print_r($_SESSION);
$install->setup_mysql_database_tables();
if (filter_has_var(INPUT_POST, "user_name")) {
    $install->handle_user_input_administration();
}
require_once 'install_head.php'
?>
<h1>Administrator configuration</h1>

<form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <p>User name:<br>
        <input type="text" name="user_name" placeholder="Administrator username" required value="<?= $_SESSION["Config"]["user_name"] ? $_SESSION["Config"]["user_name"] : "" ?>" />
    </p>
    <p title="<?= gettext("Every user in the roster will be identified by a unique id.") ?>">
        Employee id:<br>
        <input type="text" name="employee_id" placeholder="Employee id" required value="<?= $_SESSION["Config"]["employee_id"] ? $_SESSION["Config"]["employee_id"] : "" ?>" />
    </p>
    <p>
        Contact email address:<br>
        <input type="email" name="email" placeholder="Contact email address:" required value="<?= $_SESSION["Config"]["email"] ? $_SESSION["Config"]["email"] : "" ?>" />
    </p>
    <p>
        Administrator password:<br>
        <input type="password" name="password" minlength="8" placeholder="Administrator password:" required />
        <br>
        <?= gettext("Please enter a password with a minimum length of 8 characters.") ?>
    </p>
    <p>
        Confirm administrator password:<br>
        <input type="password" name="password2" minlength="8" placeholder="Confirm administrator password:" required />
    </p>

    <input type="submit" />
</form>
<?php
if (isset($error_message)) {
    echo $error_message;
}
?>
</body>
</html>
