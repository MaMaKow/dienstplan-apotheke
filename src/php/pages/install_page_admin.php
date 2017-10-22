<?php
if (filter_has_var(INPUT_POST, "user_name")) {
    require_once "../classes/class.install.php";
    install::handle_user_input_administration();
}
require_once 'install_head.php'
?>
<h1>Administrator configuration</h1>

<form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <p>User name:<br>
        <input type="text" name="user_name" pattern=".{3,20}" placeholder="Administrator username" required />
        <br><?= gettext("Please enter a username between 3 and 20 characters in length.") ?>
    </p>
    <p title="<?= gettext("Every user in the roster will be identified by a unique id.") ?>">
        Employee id:<br>
        <input type="text" name="employee_id" placeholder="Employee id" required />
    </p>
    <p>
        Contact email address:<br>
        <input type="email" name="email" placeholder="Contact email address:" required />
    </p>
    <p>
        Administrator password:<br>
        <input type="password" name="password" minlength="8" placeholder="Administrator password:" required />
    </p>
    <p>
        Confirm administrator password:<br>
        <input type="password" name="password2" minlength="8" placeholder="Confirm administrator password:" required />
    </p>
    <p>
        Please enter a password between 8 and 30 characters in length.
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