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

/**
 * This class handles the session management, login, logout and permissions of users.
 *
 * @author Mandelkow
 */
class sessions {

    public function __construct() {
        session_start();
        if (!empty($_SESSION['escalated']) AND TRUE === $_SESSION['escalated']) {
            if (5 <= ++$_SESSION['escalated_count']) {
                $this->close_escalated_session();
            }
        }

        /*
         * Interpret $_SERVER values:
         */
        $request_uri = filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);
        $http_host = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);
        $https = filter_input(INPUT_SERVER, "HTTPS", FILTER_SANITIZE_STRING);
        $script_name = filter_input(INPUT_SERVER, "SCRIPT_NAME", FILTER_SANITIZE_STRING);

        /*
         * TODO: On a production server the max-age value should probably be set to one year:
         * header("strict-transport-security: max-age=31536000");
         * for now we present a value of one minute while writing and debugging the code.
         */
        header("strict-transport-security: max-age=60");
        /* Force HTTPS:
         * We make an exception for localhost. If data is not sent through the net, there is no absolute need for HTTPS.
         * People are still free to use it on their own. Administrators are able to force it in Apache (or any other web server).
         */
        if ("localhost" != $http_host AND ( empty($https) OR $https != "on")) {
            header("Location: https://" . $http_host . $request_uri);
            die("<p>Dieses Programm erfordert die Nutzung von <a title='Article about HTTPS on german Wikipedia' href='https://de.wikipedia.org/w/index.php?title=HTTPS'>HTTPS</a>. Nur so kann die Übertragung von sensiblen Daten geschützt werden.</p>\n");
        }

        /*
         * Force a new visitor to identify as a user (=login):
         * The redirect obviously is not necessary on the login-page and on the register-page.
         */
        if (!isset($_SESSION['user_id']) and 'login.php' !== basename($script_name) and 'register.php' !== basename($script_name)) {
            /*
             * Test if the current file is on the top level or deeper in the second level:
             */
            if (strpos(pathinfo($script_name, PATHINFO_DIRNAME), 'src/php')) {
                $location = "login.php";
            } else {
                $location = "src/php/login.php";
            }
            header("Location:" . $location . "?referrer=" . $request_uri);
            die('<p>Bitte zuerst <a href="' . $location . '?referrer=' . $request_uri . '">einloggen</a></p>');
        }
        $this->keep_alive();
    }

    private function keep_alive() {
        /*
         * e dot mortoray at ecircle dot com
         * There is a nuance we found with session timing out although the user is still active in the session.  The problem has to do with never modifying the session variable. 
         * The GC will clear the session data files based on their last modification time.  Thus if you never modify the session, you simply read from it, then the GC will eventually clean up. 
         * To prevent this you need to ensure that your session is modified within the GC delete time.  You can accomplish this like below. 
         */
        if (!isset($_SESSION['last_access']) || (time() - $_SESSION['last_access']) > 60) {
            $_SESSION['last_access'] = time();
        }
    }

    private function read_Privileges_from_database() {
        global $pdo;
        $statement = $pdo->prepare("SELECT * FROM users_privileges WHERE `user_id` = :user_id");
        $statement->execute(array('user_id' => $_SESSION['user_id']));
        while ($privilege_data = $statement->fetch()) {
            $Privileges[$privilege_data[privilege]] = TRUE;
        }
        $_SESSION['Privileges'] = $Privileges;
        return;
    }

    /*
     * Check if the logged-in user has a specefied permission
     * 
     * @return boolean TRUE for exisiting permission, FALSE for missing permission.
     */

    public function user_has_privilege($privilege) {
        if (empty($_SESSION['Privileges'])) {
            /*
             * Privileges are read only once per session.
             * If permissions are revoked or granted during a session, this will take effect only after a logout(=session_destroy()).
             */
            $this->read_Privileges_from_database();
        }
        if (TRUE === $_SESSION['Privileges'][$privilege]) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function exit_on_missing_privilege($privilege) {
        if (!$this->user_has_privilege($privilege)) {
            $request_uri = filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);
            $escalation_authentication = get_script_folder() . "session-escalation-login.php?referrer=" . $request_uri;
            echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Dienstplänen fehlt. Bitte wenden Sie sich an einen Administrator. <a href=$escalation_authentication>&rarr;Rechte erweitern</a>"]);
            exit();
        }
    }

    public function login($user_name = NULL, $user_password = NULL, $redirect = TRUE) {
        global $pdo;
        /*
         * Interpret POST data:
         */
        if (NULL === $user_name) {
            $user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_STRING);
        }
        if (NULL === $user_password) {
            $user_password = filter_input(INPUT_POST, 'user_password', FILTER_SANITIZE_STRING);
        }
        if (empty($user_password) OR empty($user_name)) {
            exit("No login credentials were given.\n");
        }
        /*
         * Get user data:
         */
        $statement = $pdo->prepare("SELECT * FROM users WHERE `user_name` = :user_name AND `status` = 'active'");
        $result = $statement->execute(array('user_name' => $user_name));
        $user = $statement->fetch();

        /*
         * Check for multiple failed login attempts
         * If a user has tried to login 3 times in a row, he is blocked for 5 minutes.
         * The number of failed attempts is reset to 0 on every successfull login.
         */
        if (3 <= $user['failed_login_attempts'] and strtotime('-5min') <= strtotime($user['failed_login_attempt_time'])) {
            $errorMessage .= "<p>Zu viele ungültige Anmeldeversuche. Der Benutzer wird für 5 Minuten gesperrt.</p>";
            return $errorMessage;
        }

        //Check the password:
        if ($user !== false && password_verify($user_password, $user['password'])) {
            //Fill $_SESSION data on success:
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['user_name'];
            //Reset failed_login_attempts
            $statement = $pdo->prepare("UPDATE users SET failed_login_attempt_time = NOW(), failed_login_attempts = 0 WHERE `user_name` = :user_name");
            $result = $statement->execute(array('user_name' => $user['user_name']));

            if (TRUE === $redirect) {
                $referrer = filter_input(INPUT_GET, "referrer", FILTER_SANITIZE_STRING);
                if (!empty($referrer)) {
                    header("Location:" . $referrer);
                } else {
                    header("Location:" . get_root_folder());
                }
            }
        } else {
            //Register failed_login_attempts
            $statement = $pdo->prepare("UPDATE users SET failed_login_attempt_time = NOW(), failed_login_attempts = IFNULL(failed_login_attempts, 0)+1 WHERE `user_name` = :user_name");
            $result = $statement->execute(array('user_name' => $user['user_name']));
            $errorMessage .= "<p>Benutzername oder Passwort war ungültig</p>\n";
            return $errorMessage;
        }
        return;
    }

    public function escalate_session() {
        session_start();
        $_SESSION['before_escalation'] = $_SESSION;
        $this->login(NULL, NULL, TRUE);
        $_SESSION['escalated'] = TRUE;
        $_SESSION['escalated_count'] = 0;
    }

    public function close_escalated_session() {
        $_Session_temp = $_SESSION['before_escalation'];
        session_destroy();
        session_start();
        $_SESSION = $_Session_temp;
    }

    public function build_escalation_div() {
        if (TRUE === $_SESSION['escalated']) {
            return "<div id=escalation_div></div>";
        }
    }

    public function logout() {
        if (TRUE === $_SESSION['escalated']) {
            $this->close_escalated_session();
            $referrer = filter_input(INPUT_GET, "referrer", FILTER_SANITIZE_STRING);
            if (!empty($referrer)) {
                header("Location:" . $referrer);
            } else {
                header("Location:" . get_root_folder());
            }
        } else {

            if (session_start() and session_destroy()) {
                echo "Logout erfolgreich";
            }
            header("Location: " . get_script_folder() . "login.php");
        }
    }

    public function build_logout_button() {
        $request_uri = filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);
        $text_html = '<a href="' . get_root_folder() . 'src/php/logout.php?referrer=' . $request_uri . '" title="Benutzer abmelden">Logout</a>';

        return $text_html;
    }

}
