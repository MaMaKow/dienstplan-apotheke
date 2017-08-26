<?php
require '../../default.php';
$referrer = filter_input(INPUT_GET, "referrer", FILTER_SANITIZE_STRING);

if (filter_has_var(INPUT_GET, 'login')) {
    $errorMessage = $session->login();
}
require "../../head.php";

echo "<div class=centered_form_div>";
echo "<H1>" . $config['application_name'] . "</H1>\n";
?>

<form action="?login=1&referrer=<?php echo $referrer; ?>" method="post">
    <input type="text" size="25" maxlength="250" name="user_name" placeholder="Benutzername"><br>
    <input type="password" size="25" name="user_password" placeholder="Passwort"><br>
    <input type="submit"><br>
    <?php
    if (!empty($errorMessage)) {
        echo $errorMessage;
    }
    ?>
</form> 
<p><a href="register.php">Neues Benutzerkonto anlegen</a></p>
</div>
</body>
</html>
