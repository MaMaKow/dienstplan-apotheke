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

/**
 * This class handles the session management, login, logout and permissions of users.
 *
 * @author Mandelkow
 */
class sessions {

    public static $Pdr_list_of_privileges = array(
        'administration',
        'create_employee',
        'create_roster',
        'approve_roster',
        'create_overtime',
        'create_absence',
        'request_own_absence',
    );

    /**
     * poEdit and gettext are not willing to include words, that are not in the source files.
     * Therefore we randomly include some words here, which are necessary.
     * Used in function build_checkbox_permission() in user-management.php
     * Used in session->exit_on_missing_privilege()
     */
    private function gettext_fake() {
        return TRUE;
        gettext('administration');
        gettext('create employee');
        gettext('create roster');
        gettext('approve roster');
        gettext('create overtime');
        gettext('create absence');
        gettext('request own absence');
    }

    public function __construct() {
        ini_set('session.use_strict_mode', '1'); //Do not allow non-initiaized sessions in order to prevent session fixation.
        global $config;
        /*
         * In case there are several instances of the program on the same machine,
         * we need a specific identifier for the different instances.
         * Therefore we define a specific session_name:
         */
        session_name('PDR' . md5($config["session_secret"]));
        session_start();

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
        if ("localhost" != $http_host AND "" != $http_host) {
            header("strict-transport-security: max-age=31536000");
        }
        /* Force HTTPS:
         * We make an exception for localhost. If data is not sent through the net, there is no absolute need for HTTPS.
         * People are still free to use it on their own. Administrators are able to force it in Apache (or any other web server).
         */
        if ("localhost" != $http_host AND "" != $http_host AND ( empty($https) OR $https != "on")) {
            header("Location: https://" . $http_host . $request_uri);
            die("<p>Dieses Programm erfordert die Nutzung von "
                    . "<a title='Article about HTTPS on german Wikipedia' href='https://de.wikipedia.org/w/index.php?title=HTTPS'>HTTPS</a>."
                    . " Nur so kann die Übertragung von sensiblen Daten geschützt werden.</p>\n");
        }

        /*
         * Force a new visitor to identify as a user (=login):
         * The redirect obviously is not necessary on the login-page and on the register-page.
         */
        if (!isset($_SESSION['user_employee_id']) and ! in_array(basename($script_name), array('login.php', 'register.php', 'webdav.php', 'lost_password.php', 'reset_lost_password.php'))) {
            $location = PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/login.php";
            header("Location:" . $location . "?referrer=" . $request_uri);
            die('<p>Bitte zuerst <a href="' . $location . '?referrer=' . $request_uri . '">einloggen</a></p>' . PHP_EOL);
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
        if (!isset($_SESSION['last_regenerate_id']) || (time() - $_SESSION['last_regenerate_id']) > 15 * 60) {
            session_regenerate_id(); //To prevent session fixation attacks we regenerate the session id every once in a while.
            $_SESSION['regenerate_id'] = time();
        }
    }

    private function read_Privileges_from_database() {
        $sql_query = "SELECT * FROM users_privileges WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $_SESSION['user_employee_id']));
        while ($privilege_data = $result->fetch(PDO::FETCH_ASSOC)) {
            $Privileges[$privilege_data['privilege']] = TRUE;
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
        if (isset($_SESSION['Privileges'][$privilege]) and TRUE === $_SESSION['Privileges'][$privilege]) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function exit_on_missing_privilege($privilege) {
        if (!$this->user_has_privilege($privilege)) {
            $request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
            $message = gettext('You are missing the necessary permission to use this page.')
                    . ' ' . gettext('Please contact the administrator if you feel this is an error.')
                    . ' ("' . pdr_gettext(str_replace('_', ' ', $privilege))
                    . '" ' . gettext('is required for') . ' ' . basename($request_uri) . ')';
            user_dialog::add_message($message, E_USER_ERROR);
            echo user_dialog::build_messages();
            exit();
        }
    }

    public function login($user_name = NULL, $user_password = NULL, $redirect = TRUE) {
        global $pdo;
        /*
         * TODO: Use user_dialog for the error messages
         * user_dialog::add_message($text);
         * user_dialog::build_messages();
         */
        $errorMessage = "";
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
        $result = database_wrapper::instance()->run("SELECT * FROM users WHERE `user_name` = :user_name AND `status` = 'active'", array('user_name' => $user_name));
        $user = $result->fetch(PDO::FETCH_ASSOC);

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
            session_regenerate_id(); //To prevent session fixation attacks we regenerate the session id right before setting up login details.
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_employee_id'] = $user['employee_id'];
            $_SESSION['user_email'] = $user['email'];
            //Reset failed_login_attempts
            $sql_query = "UPDATE users "
                    . " SET failed_login_attempt_time = NOW(), "
                    . " failed_login_attempts = 0 "
                    . " WHERE `user_name` = :user_name";
            $result = database_wrapper::instance()->run($sql_query, array('user_name' => $user['user_name']));

            /*
             * Start another PHP process to do maintenance tasks:
             */
            $command = get_php_binary() . ' ' . PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/background_maintenance.php' . ' > ' . PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log 2>&1 &';
            exec($command);


            if (TRUE === $redirect) {
                $referrer = filter_input(INPUT_GET, "referrer", FILTER_SANITIZE_STRING);
                if (!empty($referrer)) {
                    header("Location:" . $referrer);
                } else {
                    header("Location:" . PDR_HTTP_SERVER_APPLICATION_PATH);
                }
            } else {
                return TRUE;
            }
        } else {
            //Register failed_login_attempts
            $sql_query = "UPDATE users"
                    . " SET failed_login_attempt_time = NOW(),"
                    . " failed_login_attempts = IFNULL(failed_login_attempts, 0)+1"
                    . " WHERE `user_name` = :user_name";
            $result = database_wrapper::instance()->run($sql_query, array('user_name' => $user['user_name']));
            $errorMessage .= "<p>Benutzername oder Passwort war ungültig</p>\n";
            return $errorMessage;
        }
        return FALSE;
    }

    public static function logout() {
        if (session_start() and session_destroy()) {
            echo "Logout erfolgreich";
        }
        header("Location: " . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/login.php");
    }

    public function build_logout_button() {
        $request_uri = filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);
        $text_html = "<a href='" . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/logout.php'>" . gettext('Logout') . '</a>';
        return $text_html;
    }

    function send_mail_about_lost_password($employee_id, $user_name, $recipient, $token) {
        global $config;
        if (isset($config['application_name'])) {
            $application_name = $config['application_name'];
        } else {
            $application_name = 'PDR';
        }

        $message_subject = quoted_printable_encode(gettext('Lost password'));
        $message_text = quoted_printable_encode("<HTML><BODY>"
                . gettext("Dear User,\n\n in order to set a new password for")
                . " '"
                . $application_name
                . "' "
                . gettext("user name") . ": " . $user_name . ", "
                . gettext("please visit")
                . " <a href='"
                . "https://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/reset_lost_password.php?employee_id=$employee_id&token=$token'>"
                . gettext("this address")
                . ".</a>"
                . gettext("Your token is valid for 24 hours.")
                . "</BODY></HTML>");
        $headers = 'From: ' . $config['contact_email'] . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: quoted-printable";

        $sent_result = mail($recipient, $message_subject, $message_text, $headers);
        if ($sent_result) {
            $message = gettext("The mail was successfully sent. Thank you!");
            user_dialog::add_message($message, E_USER_NOTICE);
        } else {
            $message = gettext("An error occured while sending the mail. I am sorry.");
            user_dialog::add_message($message, E_USER_NOTICE);
        }
    }

    function write_lost_password_token_to_database($employee_id, $token) {
        if (!is_null($employee_id) and ! is_null($token)) {
            database_wrapper::instance()->run("DELETE FROM `users_lost_password_token` WHERE `time_created` <= NOW() - INTERVAL 1 DAY");
            $sql_query = "INSERT INTO `users_lost_password_token` (`employee_id`, `token`) VALUES (:employee_id, UNHEX(:token))";
            database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'token' => $token));
            return TRUE;
        }
        return FALSE;
    }

}
