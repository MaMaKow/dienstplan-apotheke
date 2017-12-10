<?php
require '../../default.php';
$referrer = filter_input(INPUT_GET, "referrer", FILTER_SANITIZE_STRING);

if (filter_has_var(INPUT_GET, 'session_escalation')) {
    $errorMessage = $session->escalate_session();
}
require "../../head.php";

echo "<div class=centered_form_div>";
echo "<H1>" . $config['application_name'] . "</H1>\n";
?>

<form action="?session_escalation=1&referrer=<?php echo $referrer; ?>" method="post">
    <input type="text" size="25" maxlength="250" name="user_name" placeholder="Benutzername"><br>
    <input type="password" size="25" name="user_password" placeholder="Passwort"><br>
    <input type="submit"><br>
    <?php
    if (!empty($errorMessage)) {
        echo $errorMessage;
    }
    ?>
</form> 
</div>
</body>
</html>