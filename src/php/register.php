<?php

/* 
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require '../../default.php';
session_start();
?>
<!DOCTYPE html> 
<html> 
<head>
  <title>Registrierung</title> 
</head> 
<body>
 
<?php
$showFormular = true; //Variable ob das Registrierungsformular anezeigt werden soll
 
if(isset($_GET['register'])) {
 $error = false;
 $user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
 $password = $_POST['password'];
 $password2 = $_POST['password2'];
  
 if(strlen($password) == 0) {
 echo 'Bitte ein Passwort angeben<br>';
 $error = true;
 }
 if($password != $password2) {
 echo 'Die Passwörter müssen übereinstimmen<br>';
 $error = true;
 }
 
 //Überprüfe, dass die E-Mail-Adresse noch nicht registriert wurde
 if(!$error) { 
 $statement = $pdo->prepare("SELECT * FROM users WHERE user_name = :user_name");
 $result = $statement->execute(array('user_name' => $user_name));
 $user = $statement->fetch();
 
 if($user !== false) {
 echo 'Diese E-Mail-Adresse ist bereits vergeben<br>';
 $error = true;
 } 
 }
 
 //Keine Fehler, wir können den Nutzer registrieren
 if(!$error) { 
 $password_hash = password_hash($password, PASSWORD_DEFAULT);
 
 $statement = $pdo->prepare("INSERT INTO users (user_name, password) VALUES (:user_name, :password)");
 $result = $statement->execute(array('user_name' => $user_name, 'password' => $password_hash));
 
 if($result) { 
 echo 'Du wurdest erfolgreich registriert. <a href="login.php">Zum Login</a>';
 $showFormular = false;
 } else {
 echo 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
 }
 } 
}
 
if($showFormular) {
?>
 
<form action="?register=1" method="post">
Benutzername:<br>
<input type="user_name" size="40" maxlength="250" name="user_name"><br><br>
 
Dein Passwort:<br>
<input type="password" size="40" name="password"><br>
 
Passwort wiederholen:<br>
<input type="password" size="40" maxlength="250" name="password2"><br><br>
 
<input type="submit" value="Abschicken">
</form>
 
<?php
} //Ende von if($showFormular)
?>
 
</body>
</html>