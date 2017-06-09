<?php
require '../../default.php';
$referrer = filter_input(INPUT_GET, "referrer", FILTER_SANITIZE_STRING);

if (isset($_GET['login'])) {
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];
    //$referrer = $_GET['referrer'];

    $statement = $pdo->prepare("SELECT * FROM users WHERE `user_name` = :user_name AND `status` = 'active'");
    $result = $statement->execute(array('user_name' => $user_name));
    $user = $statement->fetch();


    //Überprüfung des Passworts
    if ($user !== false && password_verify($password, $user['password'])) {
        $_SESSION['userid'] = $user['id'];
        $_SESSION['user_name'] = $user['user_name'];
        if (!empty($referrer)) {
            header("Location:" . $referrer);
        } else {
            header("Location:" . get_root_folder());
        }
    } else {
        $errorMessage = "Benutzername oder Passwort war ungültig<br>";
    }
}
require "../../head.php";

echo "<div class=centered_form_div>";
echo "<H1>" . $config['application_name'] . "</H1>\n";
?>

<form action="?login=1&referrer=<?php echo $referrer; ?>" method="post">
    <input type="text" size="40" maxlength="250" name="user_name" placeholder="Benutzername"><br>
    <input type="password" size="40" name="password" placeholder="Passwort"><br>
    <input type="submit"><br>
    <?php
    if (isset($errorMessage)) {
        echo $errorMessage;
    }
    ?>
</form> 
</body>
</html>