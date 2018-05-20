<?php
/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require '../../default.php';
$referrer = filter_input(INPUT_GET, "referrer", FILTER_SANITIZE_STRING);

if (filter_has_var(INPUT_GET, 'session_escalation')) {
    $errorMessage = $session->escalate_session();
}
require "../../head.php";
if (isset($config['application_name'])) {
    $application_name = $config['application_name'];
} else {
    $application_name = 'PDR';
}


echo "<div class=centered_form_div>";
echo "<H1>" . $application_name . "</H1>\n";
?>

<form accept-charset='utf-8' action="?session_escalation=1&referrer=<?php echo $referrer; ?>" method="post">
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
