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
 * TODO: Provide a static variable $instance or save the session object in the $_SESSION array!
 * @author Martin Mandelkow
 */
class sessions {

    const PRIVILEGE_ADMINISTRATION = 'administration';
    const PRIVILEGE_CREATE_EMPLOYEE = 'create_employee';
    const PRIVILEGE_CREATE_ROSTER = 'create_roster';
    const PRIVILEGE_APPROVE_ROSTER = 'approve_roster';
    const PRIVILEGE_CREATE_OVERTIME = 'create_overtime';
    const PRIVILEGE_CREATE_ABSENCE = 'create_absence';
    const PRIVILEGE_REQUEST_OWN_ABSENCE = 'request_own_absence';

    public static $Pdr_list_of_privileges = array(
        self::PRIVILEGE_ADMINISTRATION,
        self::PRIVILEGE_CREATE_EMPLOYEE,
        self::PRIVILEGE_CREATE_ROSTER,
        self::PRIVILEGE_APPROVE_ROSTER,
        self::PRIVILEGE_CREATE_OVERTIME,
        self::PRIVILEGE_CREATE_ABSENCE,
        self::PRIVILEGE_REQUEST_OWN_ABSENCE,
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
        if (isset($_SESSION['number_of_times_redirected'])) {
            $_SESSION['number_of_times_redirected'] = 0;
        }
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
        $script_name = filter_input(INPUT_SERVER, "SCRIPT_NAME", FILTER_SANITIZE_STRING);

        if ("localhost" != $http_host AND "" != $http_host) {
            header("strict-transport-security: max-age=31536000");
        }
        /**
         * Force HTTPS:
         * We make an exception for localhost. If data is not sent through the net, there is no absolute need for HTTPS.
         * People are still free to use it on their own. Administrators are able to force it in Apache (or any other web server).
         */
        if ("localhost" != $http_host AND "" != $http_host) {
            self::force_https();
        }

        /**
         * Force a new visitor to identify as a user (=login):
         * The redirect obviously is not necessary on the login-page and on the register-page.
         */
        if (!isset($_SESSION['user_object']->employee_id) and ! in_array(basename($script_name), array('login.php', 'register.php', 'webdav.php', 'lost_password.php', 'reset_lost_password.php'))) {
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
        $Privileges = array();
        $sql_query = "SELECT * FROM users_privileges WHERE `employee_id` = :employee_id";
        $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $_SESSION['user_object']->employee_id));
        while ($privilege_data = $result->fetch(PDO::FETCH_ASSOC)) {
            $Privileges[$privilege_data['privilege']] = TRUE;
        }
        $_SESSION['Privileges'] = $Privileges;
        return;
    }

    /**
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
        $user_dialog = new user_dialog();
        if (!$this->user_has_privilege($privilege)) {
            $request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
            $message = gettext('You are missing the necessary permission to use this page.')
                    . ' ' . gettext('Please contact the administrator if you feel this is an error.')
                    . ' ("' . pdr_gettext(str_replace('_', ' ', $privilege))
                    . '" ' . gettext('is required for') . ' ' . basename($request_uri) . ')';
            $user_dialog->add_message($message, E_USER_ERROR);
            echo $user_dialog->build_messages();
            exit();
        }
    }

    public function login($user_name = NULL, $user_password = NULL, $redirect = TRUE) {
        global $pdo;
        $user_dialog = new user_dialog;
        /*
         * TODO: Use user_dialog for the error messages
         * user_dialog->add_message($text);
         * user_dialog->build_messages();
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
        $result = database_wrapper::instance()->run("SELECT `employee_id` FROM `users` WHERE `user_name` = :user_name AND `status` = 'active'", array('user_name' => $user_name));
        $user = NULL;
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $user = new user($row->employee_id);
        }

        /*
         * Check for multiple failed login attempts
         * If a user has tried to login 3 times in a row, he is blocked for 5 minutes.
         * The number of failed attempts is reset to 0 on every successfull login.
         */
        if (3 <= $user->failed_login_attempts and strtotime('-5min') <= strtotime($user->failed_login_attempt_time)) {
            $errorMessage .= "<p>Zu viele ungültige Anmeldeversuche. Der Benutzer wird für 5 Minuten gesperrt.</p>";
            $user_dialog->add_message($errorMessage, E_USER_ERROR, TRUE);
            return $errorMessage;
        }

        /*
         * Check the password:
         */
        if (NULL !== $user && $user->password_verify($user_password)) {
            /*
             * Fill $_SESSION data on success:
             */
            session_regenerate_id(); //To prevent session fixation attacks we regenerate the session id right before setting up login details.
            $_SESSION['user_object'] = $user;
            /*
             * Reset failed_login_attempts
             */
            $user->reset_failed_login_attempts();

            /*
             * Start another PHP process to do maintenance tasks:
             */
            $command = get_php_binary() . ' ' . PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/background_maintenance.php' . ' > ' . PDR_FILE_SYSTEM_APPLICATION_PATH . 'maintenance.log 2>&1 &';
            exec($command);


            if (TRUE === $redirect) {
                $referrer = filter_input(INPUT_GET, "referrer", FILTER_SANITIZE_STRING);
                if (!isset($_SESSION['number_of_times_redirected'])) {
                    $_SESSION['number_of_times_redirected'] = 0;
                }
                if (!empty($referrer)) {
                    if ($_SESSION['number_of_times_redirected'] < 3) {
                        $_SESSION['number_of_times_redirected'] ++;
                        header("Location:" . $referrer);
                    }
                } else {
                    if ($_SESSION['number_of_times_redirected'] < 3) {
                        $_SESSION['number_of_times_redirected'] ++;

                        header("Location:" . PDR_HTTP_SERVER_APPLICATION_PATH);
                    }
                }
            }
            return TRUE;
        } else {
            /*
             * Register failed_login_attempts
             */
            $user->register_failed_login_attempt();
            $errorMessage .= "<p>Benutzername oder Passwort war ungültig</p>\n";
            $user_dialog->add_message($errorMessage, E_USER_ERROR, TRUE);
            return $errorMessage;
        }
        return FALSE;
    }

    public static function logout() {
        if (session_destroy()) {
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
        $user_dialog = new user_dialog();
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
            $user_dialog->add_message($message, E_USER_NOTICE);
        } else {
            $message = gettext("An error occured while sending the mail. I am sorry.");
            $user_dialog->add_message($message, E_USER_NOTICE);
        }
    }

    public function write_lost_password_token_to_database($employee_id, $token) {
        if (!is_null($employee_id) and ! is_null($token)) {
            database_wrapper::instance()->run("DELETE FROM `users_lost_password_token` WHERE `time_created` <= NOW() - INTERVAL 1 DAY");
            $sql_query = "INSERT INTO `users_lost_password_token` (`employee_id`, `token`) VALUES (:employee_id, UNHEX(:token))";
            database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id, 'token' => $token));
            return TRUE;
        }
        return FALSE;
    }

    private static function force_https() {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            if (!isset($_SESSION['number_of_times_redirected'])) {
                $_SESSION['number_of_times_redirected'] = 0;
            }
            $https_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            if (!headers_sent() and ( $_SESSION['number_of_times_redirected'] ) < 3) {
                $_SESSION['number_of_times_redirected'] ++;
                header("Status: 301 Moved Permanently");
                header("Location: $https_url");
                die("<p>Dieses Programm erfordert die Nutzung von "
                        . "<a title='Article about HTTPS on german Wikipedia' href='https://de.wikipedia.org/w/index.php?title=HTTPS'>HTTPS</a>."
                        . " Nur so kann die Übertragung von sensiblen Daten geschützt werden.</p>\n");
            } elseif (( $_SESSION['number_of_times_redirected'] ) < 3) {
                $_SESSION['number_of_times_redirected'] ++;
                die('<script type="javascript">document.location.href="' . $https_url . '";</script>');
            } else {
                die("<p>Dieses Programm erfordert die Nutzung von "
                        . "<a title='Article about HTTPS on german Wikipedia' href='https://de.wikipedia.org/w/index.php?title=HTTPS'>HTTPS</a>."
                        . " Nur so kann die Übertragung von sensiblen Daten geschützt werden.</p>\n");
            }
        }
    }

}
