<?php
require '../../default.php';
$referrer = filter_input(INPUT_GET, "referrer", FILTER_SANITIZE_STRING);

if (filter_has_var(INPUT_GET, 'login')) {
    $user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    //$referrer = $_GET['referrer'];

    $statement = $pdo->prepare("SELECT * FROM users WHERE `user_name` = :user_name AND `status` = 'active'");
    $result = $statement->execute(array('user_name' => $user_name));
    $user = $statement->fetch();


    //Überprüfung des Passworts
    if ($user !== false && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['user_name'];
        if (!empty($referrer)) {
            header("Location:" . $referrer);
        } else {
            header("Location:" . get_root_folder());
        }
    } else {
        /*
         * TODO: If there are frequent failed attempts we should slow down and eventually block the login.
         * $_SESSION['failed_login_attempts']++;
         * We should use a mysql table for that pupose, as sessio data can be manipulated or deleted easily.
         */
        $errorMessage = "Benutzername oder Passwort war ungültig<br>";
    }
}
require "../../head.php";

echo "<div class=centered_form_div>";
echo "<H1>" . $config['application_name'] . "</H1>\n";
?>

<form action="?login=1&referrer=<?php echo $referrer; ?>" method="post">
    <input type="text" size="25" maxlength="250" name="user_name" placeholder="Benutzername"><br>
    <input type="password" size="25" name="password" placeholder="Passwort"><br>
    <input type="submit"><br>
    <?php
    if (isset($errorMessage)) {
        echo $errorMessage;
    }
    ?>
</form> 
</div>
</body>
</html>